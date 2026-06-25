<?php
session_start();
include "../config/koneksi.php";

if (isset($_GET['id'])) {
    $id_mekanik = mysqli_real_escape_string($koneksi, $_GET['id']);

    $query_hapus = mysqli_query($koneksi, "DELETE FROM tbl_mekanik WHERE id_mekanik = '$id_mekanik'");

    if ($query_hapus) {
        echo "<script>alert('Data mekanik berhasil dihapus!'); window.location='mekanik.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data mekanik: " . mysqli_error($koneksi) . "'); window.location='mekanik.php';</script>";
    }
} else {
    header("Location: mekanik.php");
    exit;
}
?>