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
$query_mekanik = mysqli_query($koneksi, "SELECT id_mekanik, nama, kepegawaian, spesialisasi, shift FROM tbl_mekanik WHERE id_mekanik = '$id_mekanik'");
$data_mekanik = mysqli_fetch_assoc($query_mekanik);

// 3. HITUNG STATISTIK BERDASARKAN TBL_PENGERJAAN
$query_proses = mysqli_query($koneksi, "SELECT id_pengerjaan FROM tbl_pengerjaan WHERE id_mekanik = '$id_mekanik' AND status IN ('Pending', 'Proses', 'Sedang Dikerjakan')");
$tugas_proses = $query_proses ? mysqli_num_rows($query_proses) : 0;

// Hitung tugas selesai
$query_selesai = mysqli_query($koneksi, "SELECT id_pengerjaan FROM tbl_pengerjaan WHERE id_mekanik = '$id_mekanik' AND status = 'Selesai'");
$tugas_selesai = $query_selesai ? mysqli_num_rows($query_selesai) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mekanik - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            overflow-x: hidden;
        }
        .sidebar {
            background: #111625;
            height: 100vh;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 999;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar-brand-wrapper { padding: 20px 24px 10px 24px; }
        .sidebar-brand { font-size: 22px; font-weight: 800; letter-spacing: 1.5px; color: #38bdf8; }
        .sidebar-subtitle { font-size: 9px; font-weight: 700; letter-spacing: 1px; color: #475569; margin-top: 2px; text-transform: uppercase; }
        .nav-section-title { font-size: 10px; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 1.5px; padding: 15px 24px 6px 24px; }
        
        .sidebar .nav-link {
            color: #94a3b8; font-size: 14px; font-weight: 600; padding: 11px 24px; display: flex; align-items: center; transition: all 0.2s ease; border-left: 4px solid transparent; text-decoration: none;
        }
        .sidebar .nav-link i { font-size: 16px; width: 28px; color: #64748b; transition: all 0.2s ease; }
        .sidebar .nav-link:hover { color: #38bdf8; background: rgba(56, 189, 248, 0.04); }
        .sidebar .nav-link:hover i { color: #38bdf8; }
        .sidebar .nav-link.active { background: rgba(59, 130, 246, 0.12); color: #3b82f6; font-weight: 700; border-left-color: #3b82f6; }
        .sidebar .nav-link.active i { color: #3b82f6; }
        .logout-link { color: #ef4444 !important; font-weight: 700 !important; }
        .logout-link i { color: #ef4444 !important; }
        .logout-link:hover { background: rgba(239, 68, 68, 0.08) !important; }
        
        /* Layout Konten Utama */
        .main-wrapper { padding: 25px 15px; }
        @media (min-width: 768px) { .main-wrapper { padding: 40px; } }
        
        /* BANNER SELAMAT DATANG (ELEGANT DARK GRADIENT) */
        .welcome-banner-premium {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 20px;
            color: white;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
            position: relative;
            overflow: hidden;
        }
        .welcome-banner-premium::before {
            content: ''; position: absolute; top: -30px; right: -30px; width: 140px; height: 140px;
            background: rgba(56, 189, 248, 0.07); border-radius: 50%;
        }
        .profile-avatar-circle {
            width: 56px; height: 56px; background: rgba(56, 189, 248, 0.15); 
            border: 1.5px solid rgba(56, 189, 248, 0.4); border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; color: #38bdf8; font-size: 22px;
        }
        
        /* CARD RINGKASAN STATISTIK */
        .stat-card-premium {
            background: white;
            border-radius: 20px;
            border: none;
            padding: 24px;
            height: 100%;
            box-shadow: 0 4px 18px rgba(148, 163, 184, 0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
        }
        .stat-card-premium:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(148, 163, 184, 0.12);
        }
        .stat-icon-box {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 20px;
        }
        
        /* Desain Tabel Premium */
        .table-premium { background: white; border-radius: 20px; box-shadow: 0 4px 18px rgba(148, 163, 184, 0.06); padding: 24px; }
        .table-premium thead th { background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 14px 16px; border-bottom: none; }
        .table-premium tbody td { padding: 16px 16px; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 13.5px; }
    </style>
</head>
<body>

<div class="bg-dark text-white d-md-none p-3 sticky-top d-flex justify-content-between align-items-center shadow-sm">
    <div class="fw-bold fs-5" style="background: linear-gradient(45deg, #38bdf8, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">SIBEO</div>
    <button class="btn btn-outline-light btn-sm px-2.5" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"><i class="fa-solid fa-bars"></i> Menu</button>
</div>

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <div class="col-md-3 col-lg-2 sidebar d-none d-md-flex">
            <div>
                <div class="sidebar-brand-wrapper text-start ps-4">
                    <div class="sidebar-brand">SIBEO</div>
                    <div class="sidebar-subtitle">Mechanic SYSTEM</div>
                </div>
                
                <div class="nav-section-title">MENU WORKSPACE</div>
                <div class="nav flex-column">
                    <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-chart-simple me-3"></i>Dashboard Kerja</a>
                    <a href="pengerjaan.php" class="nav-link"><i class="fa-solid fa-screwdriver-wrench me-3"></i>Pengerjaan</a>
                    <a href="peminjaman_alat.php" class="nav-link"><i class="fa-solid fa-boxes-stacked me-3"></i>Peminjaman Alat</a>
                </div>
            </div>
            
            <div class="mb-3 pt-2">
                <div class="nav flex-column">
                    <a href="../auth/logout.php" class="nav-link logout-link" onclick="return confirm('Keluar dari sistem?')">
                        <i class="fa-solid fa-sign-out-alt me-3"></i>Keluar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 offset-md-3 offset-lg-2">
            <div class="main-wrapper">
                
                <div class="welcome-banner-premium d-flex align-items-center gap-4 mb-4">
                    <div class="profile-avatar-circle flex-shrink-0 d-none d-sm-flex">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold m-0 text-white">Selamat Datang Kembali, <?= htmlspecialchars($data_mekanik['nama']); ?>!</h4>
                        <p class="small m-0 mt-2" style="color: #94a3b8; font-size: 13px; line-height: 1.6;">
                            Status Kepegawaian: <span class="badge bg-white bg-opacity-20 text-primary px-2 py-0.5 fw-bold text-uppercase" style="font-size: 10px;"><?= htmlspecialchars($data_mekanik['kepegawaian']); ?></span> <br class="d-block d-sm-none">
                            Spesialisasi Tim: <span class="text-light fw-semibold"><?= htmlspecialchars($data_mekanik['spesialisasi']); ?></span> &nbsp;|&nbsp; 
                            Jadwal Shift: <span class="text-light fw-semibold"><?= htmlspecialchars($data_mekanik['shift']); ?></span>
                        </p>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    
                    <div class="col-12 col-md-6">
                        <a href="pengerjaan.php" class="stat-card-premium">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="text-muted d-block fw-semibold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Tugas Aktif</span>
                                    <h2 class="fw-extrabold m-0 mt-2 text-dark" style="font-size: 36px; font-weight: 800;"><?= $tugas_proses; ?> <span style="font-size: 16px; font-weight: 500; color: #64748b;">Unit</span></h2>
                                </div>
                                <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                </div>
                            </div>
                            <span class="text-muted d-block" style="font-size: 11px;"><i class="fa-solid fa-arrow-right me-1"></i> Sedang berjalan / antrean pengerjaan</span>
                        </a>
                    </div>

                    <div class="col-12 col-md-6">
                        <a href="pengerjaan.php" class="stat-card-premium">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="text-muted d-block fw-semibold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Tugas Selesai</span>
                                    <h2 class="fw-extrabold m-0 mt-2 text-success" style="font-size: 36px; font-weight: 800;"><?= $tugas_selesai; ?> <span style="font-size: 16px; font-weight: 500; color: #64748b;">Unit</span></h2>
                                </div>
                                <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                            </div>
                            <span class="text-muted d-block" style="font-size: 11px;"><i class="fa-solid fa-arrow-right me-1"></i> Selesai dikerjakan hari ini</span>
                        </a>
                    </div>

                </div>

                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark m-0">Aktivitas Pengerjaan Terbaru</h5>
                        <p class="text-muted small m-0">5 antrean kendaraan terbaru yang ditugaskan kepada Anda.</p>
                    </div>
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
                                echo "<tr><td colspan='5' class='text-center text-muted py-4 small'>Belum ada riwayat aktivitas kerja terbaru yang tercatat.</td></tr>";
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
                                        <td style="font-size: 13px;"><?= (!empty($r['waktu_mulai']) && $r['waktu_mulai'] != '0000-00-00 00:00:00') ? $r['waktu_mulai'] : '-'; ?></td>
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
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>