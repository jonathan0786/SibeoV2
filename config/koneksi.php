<?php
$koneksi = mysqli_connect("localhost", "root", "", "sibeo");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>