<?php
session_start();
include "../config/koneksi.php";

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query_hapus = mysqli_query($koneksi, "DELETE FROM tbl_suku_cadang WHERE id_suku_cadang = '$id'");

if ($query_hapus) {
    echo "<script>alert('Komponen berhasil dihapus dari sistem gudang!'); window.location='suku_cadang.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus komponen!'); window.location='suku_cadang.php';</script>";
}
?>