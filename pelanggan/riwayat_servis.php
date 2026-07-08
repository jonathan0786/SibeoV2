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