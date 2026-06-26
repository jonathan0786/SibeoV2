<?php
session_start();
include "../config/koneksi.php";

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // 1. PROSES VERIFIKASI / KONFIRMASI AWAL
    if ($action == 'konfirmasi' && isset($_GET['id'])) {
        $id_booking = mysqli_real_escape_string($koneksi, $_GET['id']);
        $update = mysqli_query($koneksi, "UPDATE tbl_booking SET status = 'konfirmasi' WHERE id_booking = '$id_booking'");
        
        if ($update) {
            echo "<script>alert('Booking diverifikasi!'); window.location.href='booking.php';</script>";
        } else {
            echo "<script>alert('Gagal!'); window.location.href='booking.php';</script>";
        }
    }

    // 2. SETUP DATA OPERASIONAL (MASUK KE PROSES SERVIS)
    if ($action == 'assign' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_booking = mysqli_real_escape_string($koneksi, $_POST['id_booking']);
        $id_mekanik = mysqli_real_escape_string($koneksi, $_POST['id_mekanik']);
        $id_stall = mysqli_real_escape_string($koneksi, $_POST['id_stall']);
        $jam_mulai = mysqli_real_escape_string($koneksi, $_POST['jam_mulai']);
        $jam_selesai = mysqli_real_escape_string($koneksi, $_POST['jam_selesai']);
        
        $update = mysqli_query($koneksi, "UPDATE tbl_booking SET 
            id_mekanik = '$id_mekanik', 
            id_stall = '$id_stall', 
            jam_mulai = '$jam_mulai', 
            jam_selesai = '$jam_selesai', 
            status = 'proses' 
            WHERE id_booking = '$id_booking'");
        
        if ($update) {
            echo "<script>alert('Penugasan sukses! Motor sedang diproses di stall.'); window.location.href='booking.php';</script>";
        } else {
            echo "<script>alert('Gagal setup operasional!'); window.location.href='booking.php';</script>";
        }
    }

    // 3. SELESAI SERVIS
    if ($action == 'selesai' && isset($_GET['id'])) {
        $id_booking = mysqli_real_escape_string($koneksi, $_GET['id']);
        $update = mysqli_query($koneksi, "UPDATE tbl_booking SET status = 'selesai' WHERE id_booking = '$id_booking'");
        
        if ($update) {
            echo "<script>alert('Servis motor dinyatakan selesai!'); window.location.href='booking.php';</script>";
        } else {
            echo "<script>alert('Gagal update status selesai!'); window.location.href='booking.php';</script>";
        }
    }
} else {
    header("Location: booking.php");
    exit();
}
?>