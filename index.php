<?php
session_start();
// 1. Tampilan awal halaman landing page jika belum login
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: landingpage.php");
    exit();
}

// 2. Jika sudah login, cek role untuk mengarahkan ke halaman utama masing-masing aktor
if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
} elseif ($_SESSION['role'] === 'mekanik') {
    header("Location: transaksi/pengerjaan.php"); // Langsung diarahkan ke modul tugas mekanik
    exit();
} elseif ($_SESSION['role'] === 'pelanggan') {
    // Jika nanti ada fitur dashboard khusus pelanggan, arahkan ke sana
    header("Location: pelanggan/dashboard.php"); 
    exit();
} else {
    // Pengaman jika role tidak dikenali
    session_destroy();
    header("Location: auth/login.php");
    exit();
}
?>