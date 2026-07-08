<?php
session_start();
include "../config/koneksi.php";

$current_page = basename($_SERVER['PHP_SELF']);

// =========================================================================
// MENGAMBIL DATA SESSION PELANGGAN (Sesuaikan dengan variabel login Anda)
// =========================================================================
if (isset($_SESSION['id']) && $_SESSION['role'] === 'pelanggan') {
    $id_pelanggan_login = $_SESSION['id'];
    $nama_pelanggan_login = $_SESSION['nama_lengkap'] ?? 'Pelanggan SIBEO';
} elseif (isset($_SESSION['id_pelanggan'])) {
    $id_pelanggan_login = $_SESSION['id_pelanggan'];
    $nama_pelanggan_login = $_SESSION['nama_lengkap'] ?? 'Pelanggan SIBEO';
} else {
    header("Location: ../auth/login.php");
    exit();
} 

// =========================================================================
// AGREGASI DATA STATISTIK DARI DATABASE
// =========================================================================
// 1. Hitung Servis Berjalan (status: menunggu, terkonfirmasi, dalam_proses)
$servis_berjalan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_booking WHERE id_pelanggan = '$id_pelanggan_login' AND status IN ('menunggu', 'terkonfirmasi', 'dalam_proses')"))['total'];

// 2. Hitung Total Kunjungan yang sudah selesai
$total_servis = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_booking WHERE id_pelanggan = '$id_pelanggan_login' AND status = 'selesai'"))['total'];

// 3. Hitung Total Pengeluaran Servis menggunakan UDF
$biaya_res = mysqli_query($koneksi, "
    SELECT SUM(udf_hitung_total(b.id_booking)) AS total_pengeluaran 
    FROM tbl_booking b
    JOIN tbl_paket_layanan pk ON b.id_paket = pk.id_paket
    WHERE b.id_pelanggan = '$id_pelanggan_login' AND b.status = 'selesai'
");
$data_biaya = mysqli_fetch_assoc($biaya_res);
$total_pengeluaran = $data_biaya['total_pengeluaran'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - SIBEO</title>
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
        
        /* SIDEBAR COMPONENT */
        .sidebar-panel { 
            width: 280px; background: var(--sidebar-bg); flex-shrink: 0; 
            display: flex; flex-direction: column; justify-content: space-between; 
            padding: 30px 20px; box-shadow: 10px 0 30px rgba(15, 23, 42, 0.05);
            position: sticky; top: 0; height: 100vh;
        }
        .brand-section { padding: 0 12px 25px 12px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .brand-title { font-size: 24px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 10px; }
        .brand-title span { color: var(--sidebar-active); }
        .brand-subtitle { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 4px; }

        .menu-container { overflow-y: auto; flex-grow: 1; margin-top: 20px; }
        .menu-container::-webkit-scrollbar { width: 4px; }
        .menu-container::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        .section-header { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px 12px 8px 12px; }
        .sidebar-panel .nav-link { color: var(--sidebar-color); font-size: 14px; font-weight: 500; padding: 12px 16px; display: flex; align-items: center; text-decoration: none; border-radius: 12px; margin-bottom: 4px; transition: all 0.2s ease; }
        .sidebar-panel .nav-link i { width: 24px; font-size: 16px; margin-right: 12px; text-align: center; }
        .sidebar-panel .nav-link:hover { color: #ffffff; background: rgba(255, 255, 255, 0.04); }
        .sidebar-panel .nav-link.active { background: var(--sidebar-active); color: #ffffff; font-weight: 600; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.35); }

        .logout-box { padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); }
        .logout-btn { color: #f87171 !important; font-weight: 600 !important; background: rgba(239, 68, 68, 0.05); border-radius: 12px; }
        .logout-btn:hover { background: #ef4444 !important; color: #ffffff !important; }
        
        /* MAIN CANVAS SCREEN */
        .main-canvas { flex-grow: 1; padding: 40px 50px; max-width: calc(100% - 280px); }
        
        /* STATISTIK CARD */
        .stat-card { background: #ffffff; border-radius: 18px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: var(--card-shadow); display: flex; align-items: center; justify-content: space-between; }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .stat-value { font-size: 24px; font-weight: 800; color: var(--text-dark); margin: 4px 0 0 0; }
        .stat-label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }

        /* HERO CARD WELCOME */
        .welcome-hero { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border-radius: 20px; padding: 35px; color: #ffffff; position: relative; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.2); }
        .welcome-hero::after { content: ''; position: absolute; width: 300px; height: 300px; background: rgba(255, 255, 255, 0.08); border-radius: 50%; right: -50px; top: -50px; }
        
        /* PREMIUM DATA TABLE */
        .data-card-premium { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: var(--card-shadow); overflow: hidden; }
        .data-card-header { padding: 24px; background: #ffffff; border-bottom: 1px solid #f1f5f9; }
        .data-card-title { font-size: 16px; font-weight: 700; color: var(--text-dark); margin: 0; }
        .table-premium thead th { background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; }
        .table-premium tbody td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-canvas">
        <div class="mb-4">
            <h3 class="fw-bold text-dark m-0">Dashboard Pelanggan</h3>
            <p class="text-muted small m-0 mt-1">Pantau status pengerjaan workshop dan kelayakan kendaraan Anda.</p>
        </div>

        <div class="welcome-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-9" style="z-index: 2;">
                    <span class="badge bg-white text-primary px-3 py-1.5 mb-3 fw-bold small text-uppercase" style="letter-spacing: 0.5px;">Selamat Datang</span>
                    <h2 class="fw-bold text-white mb-2">Halo, Semangat Beraktivitas!</h2>
                    <p class="text-white-50 m-0" style="font-size: 14px; max-width: 600px;">Gunakan layanan pemantauan berkala untuk memastikan performa kendaraan Anda selalu dalam kondisi prima dan aman berkendara.</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div>
                        <p class="stat-label">Servis Berjalan</p>
                        <h3 class="stat-value"><?= $servis_berjalan; ?> <span class="text-muted fs-6 fw-normal">Unit</span></h3>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stat-card">
                    <div>
                        <p class="stat-label">Total Kunjungan</p>
                        <h3 class="stat-value"><?= $total_servis; ?> <span class="text-muted fs-6 fw-normal">Kali</span></h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-tools"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div>
                        <p class="stat-label">Total Biaya Servis</p>
                        <h3 class="stat-value">Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="data-card-premium">
            <div class="data-card-header d-flex justify-content-between align-items-center">
                <h5 class="data-card-title"><i class="bi bi-broadcast text-primary me-2"></i>Status Pelayanan Kendaraan Anda</h5>
                <a href="booking.php" class="btn btn-sm btn-primary fw-semibold" style="border-radius: 8px; font-size: 12.5px;">
                    <i class="bi bi-plus-circle me-1"></i> Buat Antrean Baru
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-premium align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Paket Servis</th>
                            <th>Lokasi Stall</th>
                            <th>Mekanik</th>
                            <th>Status Terkini</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Memperbaiki Fatal Error: Menggunakan 'm.nama' sesuai struktur kolom di tabel database Anda
                        $query_live = mysqli_query($koneksi, "
                            SELECT b.*, pk.nama_paket, 
                                   IFNULL(m.nama, '-') AS nama_mekanik, 
                                   IFNULL(s.nomor_stall, '-') AS nomor_stall
                            FROM tbl_booking b
                            LEFT JOIN tbl_paket_layanan pk ON b.id_paket = pk.id_paket
                            LEFT JOIN tbl_mekanik m ON b.id_mekanik = m.id_mekanik
                            LEFT JOIN tbl_stall s ON b.id_stall = s.id_stall
                            WHERE b.id_pelanggan = '$id_pelanggan_login'
                            ORDER BY b.id_booking DESC LIMIT 3
                        ");

                        if ($query_live && mysqli_num_rows($query_live) > 0) {
                            while ($data = mysqli_fetch_assoc($query_live)) {
                                $st = strtolower($data['status']);
                                ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border px-2 py-1 fw-bold"><?= htmlspecialchars($data['kode_booking']); ?></span></td>
                                    <td><strong class="text-dark"><?= htmlspecialchars($data['nama_paket']); ?></strong></td>
                                    <td>
                                        <span class="text-primary fw-bold"><i class="bi bi-geo-alt me-1"></i>Stall: <?= htmlspecialchars($data['nomor_stall']); ?></span>
                                    </td>
                                    <td><i class="bi bi-person text-secondary me-1"></i><?= htmlspecialchars($data['nama_mekanik']); ?></td>
                                    <td>
                                        <?php 
                                        if($st == 'menunggu' || $st == 'menunggu antrean') {
                                            echo '<span class="badge bg-secondary px-2 py-1 text-uppercase small fw-bold">Menunggu Validasi</span>';
                                        } elseif($st == 'terkonfirmasi') {
                                            echo '<span class="badge bg-info text-dark px-2 py-1 text-uppercase small fw-bold">Antrean Dijadwalkan</span>';
                                        } elseif($st == 'dalam proses' || $st == 'dalam_proses') {
                                            echo '<span class="badge bg-warning text-dark px-2 py-1 text-uppercase small fw-bold"><i class="bi bi-hourglass-split me-1"></i>Sedang Dikerjakan</span>';
                                        } else {
                                            echo '<span class="badge bg-success px-2 py-1 text-uppercase small fw-bold"><i class="bi bi-check-all me-1"></i>Selesai</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php 
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted py-5 small'><i class='bi bi-calendar-x d-block fs-2 mb-2 opacity-50'></i>Belum ada rekaman aktivitas pelayanan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>