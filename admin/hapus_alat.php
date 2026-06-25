<?php
session_start();
include "../config/koneksi.php";

if (isset($_GET['id'])) {
    $id_alat = mysqli_real_escape_string($koneksi, $_GET['id']);

    $query_hapus = mysqli_query($koneksi, "DELETE FROM tbl_alat_kerja WHERE id_alat = '$id_alat'");

    if ($query_hapus) {
        echo "<script>
                alert('Data alat kerja berhasil dihapus!');
                window.location='alat_kerja.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data: " . mysqli_error($koneksi) . "');
                window.location='alat_kerja.php';
              </script>";
    }
} else {
    header("Location: alat_kerja.php");
    exit;
}
?>