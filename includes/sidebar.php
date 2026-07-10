<?php
// includes/sidebar.php
function getSidebarRole() {
    $sessionRole = strtolower(trim((string)($_SESSION['role'] ?? '')));
    $currentPath = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');

    if (preg_match('#/admin/#', $currentPath)) {
        return 'admin';
    }

    if (preg_match('#/mekanik/#', $currentPath)) {
        return 'mekanik';
    }

    if (preg_match('#/pelanggan/#', $currentPath)) {
        return 'pelanggan';
    }

    return in_array($sessionRole, ['admin', 'mekanik', 'pelanggan'], true) ? $sessionRole : '';
}

$role = getSidebarRole();
$page = basename($_SERVER['PHP_SELF']);

function isActive($p) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    if (is_array($p)) {
        return in_array($currentPage, $p, true) ? 'active' : '';
    }
    return $currentPage == $p ? 'active' : '';
}
?>

<div class="sidebar-panel" id="sidebarPanel">
    <div class="brand-section">
        <div class="brand-title"><i class="bi bi-lightning-charge-fill"></i>SIBEO<span>.</span></div>
        <div class="brand-subtitle"><?php echo strtoupper($role); ?> PANEL</div>
    </div>

    <div class="menu-container">
        <!-- MENU ADMIN -->
        <?php if($role == 'admin'): ?>
            <div class="section-header">UTAMA</div>
            <a href="../admin/dashboard.php" class="nav-link <?= isActive('dashboard.php') ?>"><i class="bi bi-speedometer2"></i>Dashboard</a>
            <div class="section-header">Data Master</div>
            <a href="../admin/pelanggan.php" class="nav-link <?= isActive('pelanggan.php') ?>"><i class="bi bi-people-fill"></i>Pelanggan</a>
            <a href="../admin/suku_cadang.php" class="nav-link <?= isActive('suku_cadang.php') ?>"><i class="bi bi-box-seam-fill"></i>Suku Cadang</a>
            <a href="../admin/mekanik.php" class="nav-link <?= isActive('mekanik.php') ?>"><i class="bi bi-tools"></i>Mekanik</a>
            <a href="../admin/paket_layanan.php" class="nav-link <?= isActive('paket_layanan.php') ?>"><i class="bi bi-tags-fill"></i>Paket Layanan</a>
            <a href="../admin/alat_kerja.php" class="nav-link <?= isActive('alat_kerja.php') ?>"><i class="bi bi-wrench-adjustable-circle-fill"></i>Alat Kerja</a>
            <a href="../admin/stall.php" class="nav-link <?= isActive('stall.php') ?>"><i class="bi bi-house-gear-fill"></i>Data Stall</a>
            <div class="section-header">OPERASIONAL</div>
            <a href="../admin/pengadaan.php" class="nav-link <?= isActive('pengadaan.php') ?>"><i class="bi bi-cart-plus-fill"></i>Pengadaan Stok</a>
            <a href="../admin/booking.php" class="nav-link <?= isActive('booking.php') ?>"><i class="bi bi-calendar-check-fill"></i>Transaksi Booking</a>
            <a href="../admin/laporan.php" class="nav-link <?= isActive('laporan.php') ?>"><i class="bi bi-graph-up-arrow"></i>Laporan Pelayanan</a>
            <a href="../admin/laporan_sparepart.php" class="nav-link <?= isActive('laporan_sparepart.php') ?>"><i class="bi bi-box-seam"></i>Laporan Sparepart</a>

        
        <!-- MENU MEKANIK -->
        <?php elseif($role == 'mekanik'): ?>
            <div class="section-header">WORKSPACE</div>
            <a href="../mekanik/dashboard.php" class="nav-link <?= isActive('dashboard.php') ?>"><i class="bi bi-speedometer2"></i>Dashboard</a>
            <a href="../mekanik/pengerjaan.php" class="nav-link <?= isActive('pengerjaan.php') ?>"><i class="bi bi-tools"></i>Tugas Pengerjaan</a>
            <a href="../mekanik/peminjaman_alat.php" class="nav-link <?= isActive('peminjaman_alat.php') ?>"><i class="bi bi-wrench-adjustable-circle-fill"></i>Pinjam Alat</a>

        <!-- MENU PELANGGAN -->
        <?php elseif($role == 'pelanggan'): ?>
            <div class="section-header">PORTAL</div>
            <a href="../pelanggan/dashboard.php" class="nav-link <?= isActive('dashboard.php') ?>"><i class="bi bi-grid"></i>Dashboard</a>
            <a href="../pelanggan/booking.php" class="nav-link <?= isActive('booking.php') ?>"><i class="bi bi-calendar-plus"></i>Booking Servis</a>
            <a href="../pelanggan/kendaraan.php" class="nav-link <?= isActive('kendaraan.php') ?>"><i class="bi bi-car-front"></i>Kendaraan</a>
            <a href="../pelanggan/riwayat_servis.php" class="nav-link <?= isActive('riwayat_servis.php') ?>"><i class="bi bi-clock-history"></i>Riwayat</a>
        <?php endif; ?>
    </div>

    <div class="logout-box">
        <button type="button" class="nav-link logout-btn w-100 text-start border-0 bg-transparent" onclick="event.preventDefault(); openLogoutModal();">
            <i class="bi bi-power"></i> Log Out
        </button>
    </div>
</div>

<script>
function openLogoutModal() {
    var modalElement = document.getElementById('logoutModal');
    if (modalElement && window.bootstrap && bootstrap.Modal) {
        var modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else if (confirm('Yakin ingin keluar dari akun ini?')) {
        window.location.href = '../auth/logout.php';
    }
}
</script>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="logoutModalLabel">Keluar dari akun?</h5>
                    <p class="mb-0 small text-muted">Anda akan keluar dari sesi saat ini.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3" style="font-size: 42px; color: #ef4444;">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <p class="mb-0 text-secondary">Yakin ingin meninggalkan aplikasi?</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                <a href="../auth/logout.php" class="btn btn-danger px-4">Ya, Keluar</a>
            </div>
        </div>
    </div>
</div>
