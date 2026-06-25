<?php
session_start();
include "../config/koneksi.php";

if (isset($_GET['id'])) {
    $id_paket = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    $query_hapus = mysqli_query($koneksi, "DELETE FROM tbl_paket_layanan WHERE id_paket = '$id_paket'");
    
    if ($query_hapus) {
        echo "<script>alert('Paket layanan berhasil dihapus!'); window.location='paket_layanan.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus paket: " . mysqli_error($koneksi) . "'); window.location='paket_layanan.php';</script>";
    }
} else {
    header("Location: paket_layanan.php");
}
?>