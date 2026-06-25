<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-3 col-lg-2 sidebar p-4 d-flex flex-column justify-content-between">
    <div class="top-group">
        <div class="brand-section pt-2">
            <h4 class="fw-bold mb-0 text-white">SIBEO <span class="badge bg-primary ms-1" style="font-size: 10px;">ADMIN</span></h4>
            <div class="small text-white-50 mt-1" style="font-size: 11px;">Mode Pengembangan (No Session)</div>
            <hr class="opacity-25 text-white my-3">
        </div>

        <div class="menu-section">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="../admin/dashboard.php">
                        <i class="fa-solid fa-chart-pie me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'pelanggan.php') ? 'active' : ''; ?>" href="../admin/pelanggan.php">
                        <i class="fa-solid fa-users me-2"></i> Data Pelanggan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'suku_cadang.php') ? 'active' : ''; ?>" href="../admin/suku_cadang.php">
                        <i class="fa-solid fa-box me-2"></i> Suku Cadang (Sparepart)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'alat_kerja.php') ? 'active' : ''; ?>" href="../admin/alat_kerja.php">
                        <i class="fa-solid fa-screwdriver-wrench me-2"></i> Alat Kerja
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'booking.php') ? 'active' : ''; ?>" href="../transaksi/booking.php">
                        <i class="fa-solid fa-calendar-check me-2"></i> Booking Servis
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'pembayaran.php') ? 'active' : ''; ?>" href="../transaksi/pembayaran.php">
                        <i class="fa-solid fa-receipt me-2"></i> Kasir & Nota
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="logout-section mt-4">
        <hr class="opacity-25 text-white my-3">
        <a class="nav-link text-danger fw-bold" href="../index.php">
            <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Awal
        </a>
    </div>
</div>

<div class="col-md-9 col-lg-10 main-content">