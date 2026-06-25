<?php
session_start();
include "../config/koneksi.php";

// 1. KEAMANAN AKSES
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit();
}

$id_pelanggan = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Servis - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .sidebar {
            background: #0f172a;
            height: 100vh;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 999;
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar-brand-wrapper { padding: 30px 24px 20px 24px; }
        .sidebar-brand {
            font-size: 24px; font-weight: 800; letter-spacing: 1.5px;
            background: linear-gradient(45deg, #38bdf8, #3b82f6);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .sidebar-subtitle { font-size: 10px; font-weight: 600; letter-spacing: 1px; color: #475569; margin-top: 4px; }
        .nav-section-title { font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 1px; padding: 20px 24px 10px 24px; }
        .sidebar .nav-link { color: #94a3b8; font-size: 14px; font-weight: 500; padding: 14px 24px; display: flex; align-items: center; transition: all 0.2s ease; border-left: 4px solid transparent; }
        .sidebar .nav-link i { font-size: 16px; width: 28px; }
        .sidebar .nav-link:hover { color: #38bdf8; background: rgba(56, 189, 248, 0.04); }
        .sidebar .nav-link.active { background: rgba(59, 130, 246, 0.08); color: #3b82f6; font-weight: 600; border-left-color: #3b82f6; }
        
        .main-wrapper { margin-left: 16.666667%; padding: 40px; }
        
        /* Premium Table Component */
        .table-premium { background: white; border-radius: 24px; box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08); padding: 30px; }
        .table-premium thead th { background-color: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 16px 20px; border-bottom: none; }
        .table-premium tbody td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 14px; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <div class="col-md-3 col-lg-2 sidebar">
            <div>
                <div class="sidebar-brand-wrapper text-start ps-4">
                    <div class="sidebar-brand">SIBEO</div>
                    <div class="sidebar-subtitle">CUSTOMER SYSTEM</div>
                </div>
                
                <div class="nav-section-title">MENU UTAMA</div>
                <div class="nav flex-column">
                    <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-simple me-3"></i>Dashboard</a>
                    <a href="booking.php" class="nav-link"><i class="fa-solid fa-calendar-check me-3"></i>Booking Servis</a>
                    <a href="kendaraan.php" class="nav-link"><i class="fa-solid fa-car me-3"></i>Kendaraan Saya</a>
                    <a href="riwayat_servis.php" class="nav-link active"><i class="fa-solid fa-clock-rotate-left me-3"></i>Riwayat Servis</a>
                </div>
            </div>
            
            <div class="mb-4 border-top border-secondary border-opacity-10 pt-2">
                <div class="nav flex-column">
                    <a href="../auth/logout.php" class="nav-link text-danger" onclick="return confirm('Keluar dari sistem pelanggan?')"><i class="fa-solid fa-sign-out-alt me-3"></i>Keluar</a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 main-wrapper">
            
            <div class="mb-4">
                <h4 class="fw-bold m-0" style="color: #0f172a;">Log Aktivitas & Riwayat Servis</h4>
                <p class="text-muted small m-0 mt-1">Lacak semua daftar pengajuan booking, status pengerjaan aktif, dan riwayat perawatan kendaraan yang telah selesai.</p>
            </div>

            <div class="table-premium">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>KODE ANTREAN</th>
                                <th>KENDARAAN MEREK</th>
                                <th>NOMOR POLISI</th>
                                <th>KELUHAN / KERUSAKAN</th>
                                <th>JADWAL KEDATANGAN</th>
                                <th class="text-center">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mengambil SELURUH data booking/servis milik pelanggan tanpa dibatasi LIMIT
                            $ambil_riwayat = mysqli_query($koneksi, "SELECT b.*, k.nomor_polisi, k.merk 
                                                                    FROM tbl_booking b 
                                                                    JOIN tbl_kendaraan k ON b.id_kendaraan = k.id_kendaraan 
                                                                    WHERE b.id_pelanggan = '$id_pelanggan' 
                                                                    ORDER BY b.id_booking DESC");
                                                                    
                            if (mysqli_num_rows($ambil_riwayat) == 0) {
                                echo "<tr><td colspan='6' class='text-center text-muted small py-5'><i class='fa-solid fa-folder-open fs-2 mb-3 text-secondary d-block'></i>Belum ada data aktivitas transaksi atau servis yang tercatat.</td></tr>";
                            } else {
                                while ($r = mysqli_fetch_assoc($ambil_riwayat)) {
                                    
                                    // Set warna label dinamis berdasarkan status operasional bengkel
                                    $badge_class = "bg-warning text-warning"; // Default: Menunggu Antrean
                                    
                                    if ($r['status'] == "Selesai") {
                                        $badge_class = "bg-success text-success";
                                    } elseif ($r['status'] == "Sedang Dikerjakan" || $r['status'] == "Proses") {
                                        $badge_class = "bg-primary text-primary";
                                    } elseif ($r['status'] == "Dibatalkan") {
                                        $badge_class = "bg-danger text-danger";
                                    }
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= $r['kode_booking']; ?></td>
                                        <td class="fw-semibold" style="color: #0f172a;"><?= $r['merk']; ?></td>
                                        <td><code class="text-secondary fw-bold" style="font-size: 13px;"><?= $r['nomor_polisi']; ?></code></td>
                                        <td><?= (!empty($r['keluhan'])) ? htmlspecialchars($r['keluhan']) : '-'; ?></td>
                                        <td>
                                            <div class="fw-medium text-dark"><?= $r['tanggal_servis']; ?></div>
                                            <div class="text-muted small" style="font-size: 11px;"><i class="fa-regular fa-clock me-1"></i><?= $r['jam_servis']; ?> WIB</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-opacity-10 <?= $badge_class; ?> px-2 py-1.5 rounded small" style="font-size: 12px; font-weight: 600; min-width: 110px;">
                                                <?= $r['status']; ?>
                                            </span>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>