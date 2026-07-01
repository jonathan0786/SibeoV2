<?php
include "../config/koneksi.php";

if (isset($_POST['konfirmasi_booking'])) {
    $id_booking = $_POST['id_booking'];
    $id_mekanik = $_POST['id_mekanik'];
    $id_stall   = $_POST['id_stall'];
    $jam_mulai  = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    // 1. Pindahkan data ke tbl_pengerjaan (Fase 4 SDD)
    $query = "INSERT INTO tbl_pengerjaan (id_booking, id_mekanik, id_stall, status, waktu_mulai, waktu_selesai) 
              VALUES ('$id_booking', '$id_mekanik', '$id_stall', 'Proses', '$jam_mulai', '$jam_selesai')";
    
    if (mysqli_query($koneksi, $query)) {
        // 2. Update status di tbl_booking agar tidak Pending lagi
        mysqli_query($koneksi, "UPDATE tbl_booking SET status = 'Proses' WHERE id_booking = '$id_booking'");
        header("Location: booking.php?status=sukses");
    } else {
        header("Location: booking.php?status=gagal");
    }
}
?>