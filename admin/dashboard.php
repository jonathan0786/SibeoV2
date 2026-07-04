<?php
session_start();
include "../config/koneksi.php";

$current_page = basename($_SERVER['PHP_SELF']);

// ==========================================
// HITUNG TOTAL DATA UNTUK KOTAK STATISTIK
// ==========================================
$total_pelanggan   = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_pelanggan FROM tbl_pelanggan"));
$total_suku_cadang = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_suku_cadang FROM tbl_suku_cadang"));
$total_mekanik     = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_mekanik FROM tbl_mekanik"));
$total_alat        = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_alat FROM tbl_alat_kerja"));

// Menghitung status ketersediaan Stall
$total_stall       = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_stall FROM tbl_stall"));
$stall_tersedia    = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_stall FROM tbl_stall WHERE LOWER(status)='tersedia'"));
$stall_terisi      = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_stall FROM tbl_stall WHERE LOWER(status)='terisi'"));
$stall_maintenance = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_stall FROM tbl_stall WHERE LOWER(status)='maintenance'"));

// Mengambil 5 aktivitas pendaftaran pelanggan terbaru sebagai log sistem singkat
$query_recent_cust = mysqli_query($koneksi, "SELECT nama_lengkap, nomor_pelanggan, created_at FROM tbl_pelanggan ORDER BY id_pelanggan DESC LIMIT 5");

function safe_text($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --bg-body: #f4f6f9;
            --sidebar-bg: #1e293b;
            --sidebar-color: #94a3b8;
            --sidebar-active: #3b82f6;
            --text-dark: #0f172a;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }

        * { font-family: 'Plus Jakarta Sans', sans-serif !important; }
        body { background-color: var(--bg-body); color: #334155; overflow-x: hidden; }
        .layout-wrapper { display: flex; min-height: 100vh; }
        
        /* SIDEBAR PANEL STYLE */
        .sidebar-panel { 
            width: 280px; background: var(--sidebar-bg); flex-shrink: 0; 
            display: flex; flex-direction: column; justify-content: space-between; 
            padding: 30px 20px; box-shadow: 10px 0 30px rgba(15, 23, 42, 0.05);
            position: sticky; top: 0; height: 100vh;
        }
        .brand-section { padding: 0 12px 25px 12px; border-bottom: 1px solid rgba(255,255,255,0.06); flex-shrink: 0; }
        .brand-title { font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px; }
        .brand-title span { color: var(--sidebar-active); }
        .brand-subtitle { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 4px; }

        .menu-container { overflow-y: auto; flex-grow: 1; padding-right: 8px; margin-top: 20px; }
        .menu-container::-webkit-scrollbar { width: 5px; }
        .menu-container::-webkit-scrollbar-track { background: transparent; }
        .menu-container::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        .menu-container::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.25); }

        .section-header { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px 12px 8px 12px; }
        .sidebar-panel .nav-link { color: var(--sidebar-color); font-size: 14px; font-weight: 500; padding: 12px 16px; display: flex; align-items: center; text-decoration: none; border-radius: 12px; margin-bottom: 4px; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-panel .nav-link i { width: 24px; font-size: 16px; margin-right: 12px; text-align: center; }
        .sidebar-panel .nav-link:hover { color: #ffffff; background: rgba(255, 255, 255, 0.04); }
        .sidebar-panel .nav-link.active { background: var(--sidebar-active); color: #ffffff; font-weight: 600; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.35); }

        .logout-box { padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); flex-shrink: 0; }
        .logout-btn { color: #f87171 !important; font-weight: 600 !important; background: rgba(239, 68, 68, 0.05); }
        .logout-btn:hover { background: #ef4444 !important; color: #ffffff !important; }
        
        /* MAIN CANVAS */
        .main-canvas { flex-grow: 1; padding: 40px 50px; max-width: calc(100% - 280px); }
        
        /* WIDGET CARD DASHBOARD */
        .widget-stat-card {
            background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 24px; box-shadow: var(--card-shadow); display: flex;
            align-items: center; justify-content: space-between; transition: transform 0.2s ease;
        }
        .widget-stat-card:hover { transform: translateY(-4px); }
        .widget-info-title { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
        .widget-info-value { font-size: 28px; font-weight: 800; color: var(--text-dark); margin: 6px 0 0 0; }
        .widget-icon-box { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        
        /* COLOR UTILS FOR WIDGET */
        .icon-blue { background: rgba(59, 130, 246, 0.08); color: #3b82f6; }
        .icon-purple { background: rgba(147, 51, 234, 0.08); color: #9333ea; }
        .icon-orange { background: rgba(249, 115, 22, 0.08); color: #f97316; }
        .icon-teal { background: rgba(20, 184, 166, 0.08); color: #14b8a6; }

        .data-card-premium { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: var(--card-shadow); overflow: hidden; }
        .data-card-header { padding: 24px; background: #ffffff; border-bottom: 1px solid #f1f5f9; }
        .data-card-title { font-size: 16px; font-weight: 700; color: var(--text-dark); margin: 0; }

        .progress-stall { height: 10px; border-radius: 20px; background-color: #f1f5f9; overflow: hidden; }
        .recent-item { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid #f8fafc; }
        .recent-item:last-child { border-bottom: none; }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <div class="sidebar-panel">
        <div class="brand-section">
            <div class="brand-title"><i class="bi bi-lightning-charge-fill"></i>SIBEO<span>.</span></div>
            <div class="brand-subtitle">WORKSHOP PANEL v2</div>
        </div>

        <div class="menu-container">
            <div class="section-header">UTAMA</div>
            <a href="dashboard.php" class="nav-link <?= $current_page=='dashboard.php'?'active':'' ?>"><i class="bi bi-speedometer2"></i>Dashboard</a>

            <div class="section-header">6 DATA MASTER</div>
            <a href="pelanggan.php" class="nav-link <?= $current_page=='pelanggan.php'?'active':'' ?>"><i class="bi bi-people-fill"></i>Pelanggan</a>
            <a href="suku_cadang.php" class="nav-link <?= $current_page=='suku_cadang.php'?'active':'' ?>"><i class="bi bi-box-seam-fill"></i>Suku Cadang</a>
            <a href="mekanik.php" class="nav-link <?= $current_page=='mekanik.php'?'active':'' ?>"><i class="bi bi-tools"></i>Mekanik</a>
            <a href="paket_layanan.php" class="nav-link <?= $current_page=='paket_layanan.php'?'active':'' ?>"><i class="bi bi-tags-fill"></i>Paket Layanan</a>
            <a href="alat_kerja.php" class="nav-link <?= $current_page=='alat_kerja.php'?'active':'' ?>"><i class="bi bi-wrench-adjustable-circle-fill"></i>Alat Kerja</a>
            <a href="stall.php" class="nav-link <?= $current_page=='stall.php'?'active':'' ?>"><i class="bi bi-house-gear-fill"></i>Data Stall</a>

            <div class="section-header">OPERASIONAL</div>
            <a href="booking.php" class="nav-link <?= $current_page=='booking.php'?'active':'' ?>"><i class="bi bi-calendar-check-fill"></i>Transaksi Booking</a>
            <a href="laporan.php" class="nav-link <?= $current_page=='laporan.php'?'active':'' ?>"><i class="bi bi-graph-up-arrow"></i>Laporan Pelayanan</a>
        </div>

        <div class="logout-box">
            <a href="../auth/logout.php" class="nav-link logout-btn" onclick="return confirm('Keluar dari aplikasi SIBEO?')">
                <i class="bi bi-power"></i>Log Out
            </a>
        </div>
    </div>

    <div class="main-canvas">
        <div class="mb-5">
            <h3 class="fw-bold text-dark m-0">Selamat Datang, Administrator 👋</h3>
            <p class="text-muted small m-0 mt-1">Berikut adalah ringkasan operasional dan data master bengkel SIBEO hari ini.</p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="widget-stat-card">
                    <div>
                        <p class="widget-info-title">Pelanggan</p>
                        <h3 class="widget-info-value"><?= $total_pelanggan; ?></h3>
                    </div>
                    <div class="widget-icon-box icon-blue">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="widget-stat-card">
                    <div>
                        <p class="widget-info-title">Suku Cadang</p>
                        <h3 class="widget-info-value"><?= $total_suku_cadang; ?></h3>
                    </div>
                    <div class="widget-icon-box icon-purple">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="widget-stat-card">
                    <div>
                        <p class="widget-info-title">Mekanik Aktif</p>
                        <h3 class="widget-info-value"><?= $total_mekanik; ?></h3>
                    </div>
                    <div class="widget-icon-box icon-orange">
                        <i class="bi bi-tools"></i>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="widget-stat-card">
                    <div>
                        <p class="widget-info-title">Alat Kerja</p>
                        <h3 class="widget-info-value"><?= $total_alat; ?></h3>
                    </div>
                    <div class="widget-icon-box icon-teal">
                        <i class="bi bi-wrench-adjustable-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="data-card-premium h-100">
                    <div class="data-card-header">
                        <h5 class="data-card-title"><i class="bi bi-house-gear-fill text-primary me-2"></i>Status Okupansi Stall Pelayanan</h5>
                    </div>
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="small fw-bold text-secondary">Total Kapasitas Stall</span>
                            <span class="badge bg-light text-dark fw-bold border px-2.5 py-1"><?= $total_stall; ?> Stall Terdaftar</span>
                        </div>
                        
                        <div class="progress-stall d-flex mb-4">
                            <?php if($total_stall > 0): ?>
                                <div class="progress-bar bg-success" style="width: <?= ($stall_tersedia/$total_stall)*100; ?>%"></div>
                                <div class="progress-bar bg-danger" style="width: <?= ($stall_terisi/$total_stall)*100; ?>%"></div>
                                <div class="progress-bar bg-warning" style="width: <?= ($stall_maintenance/$total_stall)*100; ?>%"></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="small fw-semibold text-dark"><i class="bi bi-circle-fill text-success me-2"></i>Tersedia (Kosong)</span>
                                <span class="fw-bold text-dark"><?= $stall_tersedia; ?> Stall</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                                <span class="small fw-semibold text-dark"><i class="bi bi-circle-fill text-danger me-2"></i>Sedang Terisi Servis</span>
                                <span class="fw-bold text-dark"><?= $stall_terisi; ?> Stall</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small fw-semibold text-dark"><i class="bi bi-circle-fill text-warning me-2"></i>Maintenance / Perbaikan</span>
                                <span class="fw-bold text-dark"><?= $stall_maintenance; ?> Stall</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="data-card-premium h-100">
                    <div class="data-card-header">
                        <h5 class="data-card-title"><i class="bi bi-people text-primary me-2"></i>Pelanggan Baru Terdaftar (Terbaru)</h5>
                    </div>
                    <div class="py-2">
                        <?php
                        if (mysqli_num_rows($query_recent_cust) > 0) {
                            while ($cust = mysqli_fetch_assoc($query_recent_cust)) {
                                ?>
                                <div class="recent-item">
                                    <div>
                                        <h6 class="m-0 fw-bold text-dark small" style="font-size:14px;"><?= safe_text($cust['nama_lengkap']); ?></h6>
                                        <span class="text-muted" style="font-size:11px; font-weight:600;"><i class="bi bi-card-text me-1"></i><?= safe_text($cust['nomor_pelanggan']); ?></span>
                                    </div>
                                    <span class="badge bg-light text-secondary border px-2 py-1 small" style="font-size:11px;">
                                        <?= date('d M Y', strtotime($cust['created_at'])); ?>
                                    </span>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="text-center text-muted py-5 small"><i class="bi bi-person-x d-block fs-3 mb-2 opacity-50"></i>Belum ada data pelanggan baru.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>