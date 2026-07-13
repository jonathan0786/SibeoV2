<?php
session_start();
include "../config/koneksi.php";

// 1. KEAMANAN AKSES
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'mekanik') {
    header("Location: ../auth/login.php");
    exit();
}

$id_mekanik = $_SESSION['id'];

// 2. QUERY DETAIL MEKANIK
$query_mekanik = mysqli_query($koneksi, "SELECT id_mekanik, nama, nim, spesialisasi, shift FROM tbl_mekanik WHERE id_mekanik = '$id_mekanik'");
$data_mekanik = mysqli_fetch_assoc($query_mekanik);

// 3. HITUNG STATISTIK BERDASARKAN TBL_PENGERJAAN
$query_proses = mysqli_query($koneksi, "SELECT id_pengerjaan FROM tbl_pengerjaan WHERE id_mekanik = '$id_mekanik' AND status IN ('Pending', 'Proses', 'Sedang Dikerjakan', 'dimulai')");
$tugas_proses = $query_proses ? mysqli_num_rows($query_proses) : 0;

$query_selesai = mysqli_query($koneksi, "SELECT id_pengerjaan FROM tbl_pengerjaan WHERE id_mekanik = '$id_mekanik' AND status = 'Selesai'");
$tugas_selesai = $query_selesai ? mysqli_num_rows($query_selesai) : 0;

$query_total = mysqli_query($koneksi, "SELECT COUNT(*) AS total_tugas FROM tbl_pengerjaan WHERE id_mekanik = '$id_mekanik'");
$data_total = mysqli_fetch_assoc($query_total);
$total_tugas = (int)($data_total['total_tugas'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mekanik - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome untuk konten utama -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap Icons untuk Sidebar agar 100% sama dengan Admin/Pelanggan -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* =========================================================
           1. CSS STANDAR UNIFIED (Sama Persis Admin & Pelanggan)
           ========================================================= */
        :root {
            --bg-body: #f4f6f9;
            --sidebar-bg: #1e293b;
            --sidebar-color: #94a3b8;
            --sidebar-active: #3b82f6;
            --text-dark: #0f172a;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02);
        }
        
        * { font-family: 'Plus Jakarta Sans', sans-serif !important; }
        body { background-color: var(--bg-body); color: #334155; overflow-x: hidden; }
        .layout-wrapper { display: flex; min-height: 100vh; }
        
        /* SIDEBAR KONSISTEN DASHBOARD */
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
        .sidebar-panel .nav-link { 
            color: var(--sidebar-color); font-size: 14px; font-weight: 500; 
            padding: 12px 16px; display: flex; align-items: center; text-decoration: none; 
            border-radius: 12px; margin-bottom: 4px; transition: all 0.2s ease; 
        }
        .sidebar-panel .nav-link i { width: 24px; font-size: 16px; margin-right: 12px; text-align: center; }
        .sidebar-panel .nav-link:hover { color: #ffffff; background: rgba(255, 255, 255, 0.04); }
        .sidebar-panel .nav-link.active { background: var(--sidebar-active); color: #ffffff; font-weight: 600; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.35); }
        .section-header { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px 12px 8px 12px; }
        
        .logout-box { padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); }
        .logout-btn { color: #f87171 !important; font-weight: 600 !important; background: rgba(239, 68, 68, 0.05); border-radius: 12px; }
        .logout-btn:hover { background: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; }
        
        /* MAIN CANVAS */
        .main-canvas { flex-grow: 1; padding: 40px 50px; max-width: calc(100% - 280px); }

        .welcome-banner-premium {
            position: relative; overflow: hidden; border-radius: 24px; color: white; padding: 30px;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 45%, #2563eb 100%);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.16);
        }
        .welcome-banner-premium::before {
            content: ''; position: absolute; top: -30px; right: -30px; width: 180px; height: 180px;
            background: rgba(255,255,255,0.08); border-radius: 50%;
        }
        .profile-avatar-circle {
            width: 58px; height: 58px; background: rgba(56, 189, 248, 0.16); 
            border: 1.5px solid rgba(56, 189, 248, 0.4); border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 22px;
        }
        .banner-pill {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px;
            background: rgba(255,255,255,0.14); color: #dbeafe; border-radius: 999px;
            font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        }
        
        .stat-card-premium {
            background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%); border-radius: 20px; border: 1px solid #edf2f7; padding: 24px; height: 100%;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); display: flex; flex-direction: column;
            justify-content: space-between; transition: transform 0.2s ease, box-shadow 0.2s ease; text-decoration: none;
        }
        .stat-card-premium:hover { transform: translateY(-4px); box-shadow: 0 16px 35px rgba(15, 23, 42, 0.08); }
        .stat-icon-box { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        
        .table-premium { background: white; border-radius: 24px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); padding: 24px; border: 1px solid #edf2f7; }
        .table-premium thead th { background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 14px 16px; border-bottom: none; }
        .table-premium tbody td { padding: 16px 16px; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 13.5px; }
        .table-premium tbody tr:hover { background-color: #f8fbff; }
        .empty-state { border: 1px dashed #cbd5e1; border-radius: 16px; background: #f8fafc; }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-canvas">
        
        <div class="welcome-banner-premium d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="profile-avatar-circle flex-shrink-0 d-none d-sm-flex">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <span class="banner-pill"><i class="fa-solid fa-sparkles"></i> Dashboard Mekanik</span>
                    <h4 class="fw-bold m-0 mt-3 text-white">Selamat Datang Kembali, <?= htmlspecialchars($data_mekanik['nama']); ?>!</h4>
                    <p class="small m-0 mt-2" style="color: #cbd5e1; font-size: 13px; line-height: 1.7;">
                        NIM: <span class="badge bg-white bg-opacity-20 text-primary px-2 py-0.5 fw-bold text-uppercase" style="font-size: 10px;"><?= htmlspecialchars($data_mekanik['nim']); ?></span> | <br class="d-block d-sm-none">
                        Spesialisasi: <span class="text-light fw-semibold"><?= htmlspecialchars($data_mekanik['spesialisasi']); ?></span> &nbsp;|&nbsp; 
                        Jadwal Shift: <span class="text-light fw-semibold"><?= htmlspecialchars($data_mekanik['shift']); ?></span>
                    </p>
                </div>
            </div>
            <div class="text-white small fw-semibold" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); border-radius: 16px; padding: 14px 16px; min-width: 220px;">
                <div class="d-flex justify-content-between mb-2"><span>Total Tugas</span><strong><?= $total_tugas; ?></strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Task Aktif</span><strong><?= $tugas_proses; ?></strong></div>
                <div class="d-flex justify-content-between"><span>Task Selesai</span><strong><?= $tugas_selesai; ?></strong></div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-md-4">
                <a href="pengerjaan.php" class="stat-card-premium">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-muted d-block fw-semibold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Total Tugas</span>
                            <h2 class="fw-extrabold m-0 mt-2 text-dark" style="font-size: 30px; font-weight: 800;"><?= $total_tugas; ?></h2>
                        </div>
                        <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                    </div>
                    <span class="text-muted d-block" style="font-size: 11px;"><i class="fa-solid fa-arrow-right me-1"></i> Seluruh tugas yang ditugaskan kepada Anda</span>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a href="pengerjaan.php" class="stat-card-premium">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-muted d-block fw-semibold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Tugas Aktif</span>
                            <h2 class="fw-extrabold m-0 mt-2 text-dark" style="font-size: 30px; font-weight: 800;"><?= $tugas_proses; ?></h2>
                        </div>
                        <div class="stat-icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="fa-solid fa-spinner"></i>
                        </div>
                    </div>
                    <span class="text-muted d-block" style="font-size: 11px;"><i class="fa-solid fa-arrow-right me-1"></i> Sedang berjalan / antrean pengerjaan</span>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a href="pengerjaan.php" class="stat-card-premium">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="text-muted d-block fw-semibold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Tugas Selesai</span>
                            <h2 class="fw-extrabold m-0 mt-2 text-success" style="font-size: 30px; font-weight: 800;"><?= $tugas_selesai; ?></h2>
                        </div>
                        <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                    <span class="text-muted d-block" style="font-size: 11px;"><i class="fa-solid fa-arrow-right me-1"></i> Selesai dikerjakan</span>
                </a>
            </div>
        </div>

        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-dark m-0">Aktivitas Pengerjaan Terbaru</h5>
                <p class="text-muted small m-0">5 antrean kendaraan terbaru yang ditugaskan kepada Anda.</p>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">
                <i class="fa-solid fa-clipboard-list me-1"></i> <?= $total_tugas; ?> data
            </span>
        </div>

        <div class="table-premium">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>KODE BOOKING</th>
                            <th>KENDARAAN</th>
                            <th>PAKET LAYANAN</th>
                            <th>WAKTU MASUK</th>
                            <th class="text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $query_tabel = mysqli_query($koneksi, "SELECT p.status, p.waktu_mulai, b.kode_booking, b.keluhan, k.merk, k.nomor_polisi, l.nama_paket 
                                                           FROM tbl_pengerjaan p
                                                           JOIN tbl_booking b ON p.id_booking = b.id_booking
                                                           JOIN tbl_kendaraan k ON b.id_kendaraan = k.id_kendaraan
                                                           JOIN tbl_paket_layanan l ON b.id_paket = l.id_paket
                                                           WHERE p.id_mekanik = '$id_mekanik'
                                                           ORDER BY p.id_pengerjaan DESC LIMIT 5");

                    if (!$query_tabel || mysqli_num_rows($query_tabel) == 0) {
                        echo "<tr><td colspan='5' class='text-center py-4'><div class='empty-state py-4 px-3 text-muted small'>Belum ada riwayat aktivitas kerja terbaru yang tercatat.</div></td></tr>";
                    } else {
                        while ($r = mysqli_fetch_assoc($query_tabel)) {
                            $status = $r['status'];
                            $badge_class = "bg-warning text-warning";
                            if ($status == "Selesai") $badge_class = "bg-success text-success";
                            elseif ($status == "Sedang Dikerjakan" || $status == "Proses") $badge_class = "bg-primary text-primary";
                            ?>
                            <tr>
                                <td class="fw-bold text-primary">#<?= htmlspecialchars($r['kode_booking']); ?></td>
                                <td class="fw-semibold">
                                    <?= htmlspecialchars($r['merk']); ?> 
                                    (<code class="text-secondary fw-bold" style="font-size: 12px;"><?= htmlspecialchars($r['nomor_polisi']); ?></code>)
                                </td>
                                <td>
                                    <span class="fw-medium"><?= htmlspecialchars($r['nama_paket']); ?></span>
                                    <br><small class="text-muted" style="font-size: 11px;">Ket: <?= (!empty($r['keluhan'])) ? htmlspecialchars($r['keluhan']) : '-'; ?></small>
                                </td>
                                <td style="font-size: 13px;"><?= (!empty($r['waktu_mulai']) && $r['waktu_mulai'] != '0000-00-00 00:00:00') ? htmlspecialchars($r['waktu_mulai']) : '-'; ?></td>
                                <td class="text-center">
                                    <span class="badge bg-opacity-10 <?= $badge_class; ?> px-2.5 py-1.5 rounded text-capitalize" style="font-size: 12px; font-weight: 600;"><?= $status; ?></span>
                                </td>
                            </tr>
                            <?php
                        }
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