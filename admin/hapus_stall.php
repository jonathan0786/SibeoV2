<?php
session_start();
include "../config/koneksi.php";

if (isset($_GET['id'])) {
    $id_stall = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Eksekusi hapus data stall
    $query_hapus = mysqli_query($koneksi, "DELETE FROM tbl_stall WHERE id_stall = '$id_stall'");
    
    if ($query_hapus) {
        echo "<script>alert('Lajur stall berhasil dihapus dari sistem!'); window.location='stall.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data stall: " . mysqli_error($koneksi) . "'); window.location='stall.php';</script>";
    }
} else {
    header("Location: stall.php");
}
?>