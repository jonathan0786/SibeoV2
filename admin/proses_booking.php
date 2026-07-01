<?php
session_start();
include "../config/koneksi.php";

function redirect_booking($status = '', $message = '') {
    $url = 'booking.php';
    if ($status !== '') {
        $url .= '?status=' . urlencode($status) . '&message=' . urlencode($message);
    }
    header("Location: " . $url);
    exit;
}

function valid_id($id) {
    return isset($id) && is_numeric($id) && (int)$id > 0;
}

if (!isset($koneksi) || !$koneksi) {
    die('Koneksi database tidak tersedia. Periksa file ../config/koneksi.php');
}

if (isset($_POST['konfirmasi_penugasan'])) {
    $id_booking = $_POST['id_booking'] ?? null;
    $id_mekanik = $_POST['id_mekanik'] ?? null;
    $id_stall = $_POST['id_stall'] ?? null;

    if (!valid_id($id_booking) || !valid_id($id_mekanik) || !valid_id($id_stall)) {
        redirect_booking('error', 'Booking, mekanik, dan stall wajib dipilih.');
    }

    $id_booking = (int)$id_booking;
    $id_mekanik = (int)$id_mekanik;
    $id_stall = (int)$id_stall;

    $stmt = mysqli_prepare($koneksi, "
        SELECT id_booking, status
        FROM tbl_booking
        WHERE id_booking = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, 'i', $id_booking);
    mysqli_stmt_execute($stmt);
    $booking_result = mysqli_stmt_get_result($stmt);
    $booking = mysqli_fetch_assoc($booking_result);
    mysqli_stmt_close($stmt);

    if (!$booking) {
        redirect_booking('error', 'Booking tidak ditemukan.');
    }

    if (!in_array(strtolower($booking['status']), ['menunggu', 'pending', 'konfirmasi'], true)) {
        redirect_booking('error', 'Booking ini tidak bisa dikonfirmasi ulang.');
    }

    $stmt = mysqli_prepare($koneksi, "
        SELECT id_mekanik
        FROM tbl_mekanik
        WHERE id_mekanik = ?
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, 'i', $id_mekanik);
    mysqli_stmt_execute($stmt);
    $mekanik_result = mysqli_stmt_get_result($stmt);
    $mekanik_valid = mysqli_num_rows($mekanik_result) > 0;
    mysqli_stmt_close($stmt);

    if (!$mekanik_valid) {
        redirect_booking('error', 'Mekanik tidak ditemukan.');
    }

    $stmt = mysqli_prepare($koneksi, "
        SELECT id_stall
        FROM tbl_stall
        WHERE id_stall = ? AND status = 'tersedia'
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, 'i', $id_stall);
    mysqli_stmt_execute($stmt);
    $stall_result = mysqli_stmt_get_result($stmt);
    $stall_valid = mysqli_num_rows($stall_result) > 0;
    mysqli_stmt_close($stmt);

    if (!$stall_valid) {
        redirect_booking('error', 'Stall tidak tersedia atau tidak ditemukan.');
    }

    mysqli_begin_transaction($koneksi);

    try {
        $stmt = mysqli_prepare($koneksi, "
            UPDATE tbl_booking
            SET id_mekanik = ?, id_stall = ?, status = 'konfirmasi'
            WHERE id_booking = ?
        ");
        mysqli_stmt_bind_param($stmt, 'iii', $id_mekanik, $id_stall, $id_booking);
        $update_booking = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($koneksi, "
            UPDATE tbl_stall
            SET status = 'terpakai'
            WHERE id_stall = ?
        ");
        mysqli_stmt_bind_param($stmt, 'i', $id_stall);
        $update_stall = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$update_booking || !$update_stall) {
            throw new Exception('Gagal menyimpan data penugasan.');
        }

        mysqli_commit($koneksi);
        redirect_booking('success', 'Booking berhasil dikonfirmasi. Mekanik dan stall sudah ditugaskan.');
    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        redirect_booking('error', 'Gagal menyimpan konfirmasi booking.');
    }
}

if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $id_booking = $_GET['id'];

    if (!valid_id($id_booking)) {
        redirect_booking('error', 'ID booking tidak valid.');
    }

    $id_booking = (int)$id_booking;

    if ($action === 'konfirmasi') {
        redirect_booking('error', 'Gunakan popup konfirmasi untuk memilih mekanik dan stall.');
    }

    if ($action === 'mulai') {
        $stmt = mysqli_prepare($koneksi, "
            SELECT id_booking, id_mekanik, id_stall, status
            FROM tbl_booking
            WHERE id_booking = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, 'i', $id_booking);
        mysqli_stmt_execute($stmt);
        $booking_result = mysqli_stmt_get_result($stmt);
        $booking = mysqli_fetch_assoc($booking_result);
        mysqli_stmt_close($stmt);

        if (!$booking) {
            redirect_booking('error', 'Booking tidak ditemukan.');
        }

        if (strtolower($booking['status']) !== 'konfirmasi') {
            redirect_booking('error', 'Booking harus berstatus konfirmasi sebelum dimulai.');
        }

        if (empty($booking['id_mekanik']) || empty($booking['id_stall'])) {
            redirect_booking('error', 'Lengkapi mekanik dan stall sebelum memulai pengerjaan.');
        }

        $id_mekanik = (int)$booking['id_mekanik'];

        mysqli_begin_transaction($koneksi);

        try {
            $stmt = mysqli_prepare($koneksi, "
                UPDATE tbl_booking
                SET status = 'proses'
                WHERE id_booking = ?
            ");
            mysqli_stmt_bind_param($stmt, 'i', $id_booking);
            $update_booking = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($koneksi, "
                INSERT INTO tbl_pengerjaan (id_booking, id_mekanik, status, waktu_mulai)
                VALUES (?, ?, 'dimulai', NOW())
            ");
            mysqli_stmt_bind_param($stmt, 'ii', $id_booking, $id_mekanik);
            $insert_pengerjaan = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if (!$update_booking || !$insert_pengerjaan) {
                throw new Exception('Gagal memulai pengerjaan.');
            }

            mysqli_commit($koneksi);
            redirect_booking('success', 'Pengerjaan servis berhasil dimulai.');
        } catch (Throwable $e) {
            mysqli_rollback($koneksi);
            redirect_booking('error', 'Gagal memulai pengerjaan servis.');
        }
    }

    if ($action === 'selesai') {
        $stmt = mysqli_prepare($koneksi, "
            SELECT id_booking, id_stall, status
            FROM tbl_booking
            WHERE id_booking = ?
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, 'i', $id_booking);
        mysqli_stmt_execute($stmt);
        $booking_result = mysqli_stmt_get_result($stmt);
        $booking = mysqli_fetch_assoc($booking_result);
        mysqli_stmt_close($stmt);

        if (!$booking) {
            redirect_booking('error', 'Booking tidak ditemukan.');
        }

        if (strtolower($booking['status']) !== 'proses') {
            redirect_booking('error', 'Booking belum dalam proses pengerjaan.');
        }

        $id_stall = !empty($booking['id_stall']) ? (int)$booking['id_stall'] : null;

        mysqli_begin_transaction($koneksi);

        try {
            $stmt = mysqli_prepare($koneksi, "
                UPDATE tbl_booking
                SET status = 'selesai'
                WHERE id_booking = ?
            ");
            mysqli_stmt_bind_param($stmt, 'i', $id_booking);
            $update_booking = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($koneksi, "
                UPDATE tbl_pengerjaan
                SET status = 'selesai', waktu_selesai = NOW()
                WHERE id_booking = ?
                ORDER BY id_pengerjaan DESC
                LIMIT 1
            ");
            mysqli_stmt_bind_param($stmt, 'i', $id_booking);
            $update_pengerjaan = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $update_stall = true;
            if ($id_stall !== null) {
                $stmt = mysqli_prepare($koneksi, "
                    UPDATE tbl_stall
                    SET status = 'tersedia'
                    WHERE id_stall = ?
                ");
                mysqli_stmt_bind_param($stmt, 'i', $id_stall);
                $update_stall = mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            if (!$update_booking || !$update_pengerjaan || !$update_stall) {
                throw new Exception('Gagal menyelesaikan servis.');
            }

            mysqli_commit($koneksi);
            redirect_booking('success', 'Servis berhasil diselesaikan. Stall kembali tersedia.');
        } catch (Throwable $e) {
            mysqli_rollback($koneksi);
            redirect_booking('error', 'Gagal menyelesaikan servis.');
        }
    }

    redirect_booking('error', 'Aksi tidak dikenali.');
}

redirect_booking();
