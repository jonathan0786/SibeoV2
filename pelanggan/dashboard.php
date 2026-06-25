<?php
session_start();
include "../config/koneksi.php";

// 1. PERBAIKAN SESSION: Cek menggunakan $_SESSION['id'] dan $_SESSION['role'] sesuai login.php
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit();
}

$id_pelanggan = $_SESSION['id'];

// 2. PERBAIKAN QUERY & STATISTIK
// Menghitung jumlah kendaraan milik pelanggan yang sedang login
$hitung_kendaraan = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_kendaraan FROM tbl_kendaraan WHERE id_pelanggan = '$id_pelanggan'"));

// CATATAN: Karena tbl_servis tidak ada, pastikan nama tabel transaksi/booking Anda sudah benar.
// Jika nama tabel Anda bukan 'tbl_booking', silakan ganti teks 'tbl_booking' di bawah ini sesuai nama tabel di database Anda.
$query_hitung_servis = mysqli_query($koneksi, "SELECT * FROM tbl_booking WHERE id_pelanggan = '$id_pelanggan'");
$hitung_servis = $query_hitung_servis ? mysqli_num_rows($query_hitung_servis) : 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        /* Layout Sidebar Flexbox Tinggi Penuh */
        .sidebar {
            background: #0f172a;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 999;
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar-brand-wrapper {
            padding: 30px 24px 20px 24px;
        }
        .sidebar-brand {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1.5px;
            background: linear-gradient(45deg, #38bdf8, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .sidebar-subtitle {
            font-size: 10px; 
            font-weight: 600; 
            letter-spacing: 1px; 
            color: #475569;
            margin-top: 4px;
        }
        .nav-section-title {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 20px 24px 10px 24px;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        .sidebar .nav-link i {
            font-size: 16px;
            width: 28px;
        }
        .sidebar .nav-link:hover {
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.04);
        }
        .sidebar .nav-link.active {
            background: rgba(59, 130, 246, 0.08);
            color: #3b82f6;
            font-weight: 600;
            border-left-color: #3b82f6;
        }
        
        /* Main Content Adjustment */
        .main-wrapper {
            margin-left: 16.666667%; 
            padding: 40px;
        }
        
        /* Banner Atas */
        .welcome-banner {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-radius: 24px;
            padding: 35px;
            color: white;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }
        .welcome-banner p {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        /* Info Cards */
        .stat-card-premium {
            background: white;
            border: none;
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08);
        }
        .icon-shape {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        /* Tabel */
        .table-premium {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08);
            padding: 25px;
        }
        .table-premium thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 20px;
            border-bottom: none;
        }
        .table-premium tbody td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            font-size: 14px;
        }
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
                    <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-chart-simple me-3"></i>Dashboard</a>
                    <a href="booking.php" class="nav-link"><i class="fa-solid fa-calendar-check me-3"></i>Booking Servis</a>
                    <a href="kendaraan.php" class="nav-link"><i class="fa-solid fa-car me-3"></i>Kendaraan Saya</a>
                    <a href="riwayat_servis.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left me-3"></i>Riwayat Servis</a>
                </div>
            </div>
            
            <div class="mb-4 border-top border-secondary border-opacity-10 pt-2">
                <div class="nav flex-column">
                    <a href="../auth/logout.php" class="nav-link text-danger" onclick="return confirm('Keluar dari sistem')"><i class="fa-solid fa-sign-out-alt me-3"></i>Keluar</a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 main-wrapper">
            
            <div class="welcome-banner mb-4">
                <span class="badge mb-3 px-3 py-2" style="font-size: 11px; font-weight: 800; letter-spacing: 1px; background: rgba(255, 255, 255, 0.15); color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25);">AKUN CIVITAS AKTIF</span>
                <h1 class="fw-bold m-0 mb-2" style="font-size: 28px; letter-spacing: -0.5px;">Selamat Datang, <?= $_SESSION['nama_lengkap']; ?></h1>
                <p class="small m-0">Pantau status pengerjaan mekanik secara langsung, kelola data kendaraan pribadi Anda, dan lakukan registrasi booking secara mandiri.</p>
            </div>

            <div class="row g-4 mb-5">
                
                <div class="col-md-6 col-lg-4">
                    <div class="stat-card-premium">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; color: #94a3b8 !important;">GARASI KENDARAAN</span>
                                <h2 class="fw-extrabold mb-0 mt-2" style="color: #0f172a; font-size: 32px; font-weight: 800;"><?= $hitung_kendaraan; ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">Unit</span></h2>
                            </div>
                            <div class="icon-shape bg-primary bg-opacity-10 text-primary">
                                <i class="fa-solid fa-car-side"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top border-light">
                            <a href="kendaraan.php" class="text-decoration-none small fw-bold text-primary">Lihat Kendaraan Saya &nbsp;&rarr;</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="stat-card-premium">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; color: #94a3b8 !important;">TOTAL REKAP SERVIS</span>
                                <h2 class="fw-extrabold mb-0 mt-2" style="color: #0f172a; font-size: 32px; font-weight: 800;"><?= $hitung_servis; ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">Aktivitas</span></h2>
                            </div>
                            <div class="icon-shape bg-success bg-opacity-10 text-success">
                                <i class="fa-solid fa-wrench"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top border-light">
                            <a href="riwayat_servis.php" class="text-decoration-none small fw-bold text-success">Lihat Log Aktivitas &nbsp;&rarr;</a>
                        </div>
                    </div>
                </div>

            </div>

            <div class="table-premium">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold m-0" style="color: #0f172a;">Status Servis Kendaraan Terbaru</h5>
                        <p class="text-muted small m-0 mt-1">Status dan riwayat pengerjaan kendaraan Anda yang sedang atau telah diproses.</p>
                    </div>
                    <a href="riwayat_servis.php" class="btn btn-sm btn-outline-secondary rounded-3 px-3 py-1.5 fw-semibold" style="font-size: 12px;">Lihat Semua</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>NO. ANTREAN</th>
                                <th>KENDARAAN / PLAT NO.</th>
                                <th>PAKET LAYANAN</th>
                                <th>JADWAL & STALL</th>
                                <th class="text-center">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
// QUERY TERBARU: Menggabungkan 3 tabel sekaligus (Booking, Kendaraan, dan Paket)
$tampil_terbaru = mysqli_query($koneksi, "SELECT b.*, k.merk, k.nomor_polisi, p.nama_paket 
                                        FROM tbl_booking b 
                                        JOIN tbl_kendaraan k ON b.id_kendaraan = k.id_kendaraan 
                                        JOIN tbl_paket_layanan p ON b.id_paket = p.id_paket
                                        WHERE b.id_pelanggan = '$id_pelanggan' 
                                        ORDER BY b.id_booking DESC LIMIT 3");
                                        
if (!$tampil_terbaru || mysqli_num_rows($tampil_terbaru) == 0) {
    echo "<tr><td colspan='5' class='text-center text-muted small py-4'>Belum ada riwayat booking atau servis kendaraan.</td></tr>";
} else {
    while ($r = mysqli_fetch_assoc($tampil_terbaru)) {
        
        $status = $r['status']; 
        $badge_class = "bg-warning text-warning";
        
        if ($status == "Selesai") {
            $badge_class = "bg-success text-success";
        } elseif ($status == "Sedang Dikerjakan" || $status == "Proses" || $status == "Sedang Diproses") {
            $badge_class = "bg-primary text-primary";
        }
        ?>
        <tr>
            <td class="fw-bold text-primary"><?= $r['kode_booking']; ?></td>
            
            <td class="fw-semibold" style="color: #0f172a;">
                <?= htmlspecialchars($r['merk']); ?> 
                (<code class="text-secondary"><?= htmlspecialchars($r['nomor_polisi']); ?></code>)
            </td>
            
            <td>
                <span class="fw-medium"><?= htmlspecialchars($r['nama_paket']); ?></span>
            </td>
            
            <td><?= $r['tanggal_servis']; ?> (<span class="text-secondary"><?= substr($r['jam_servis'], 0, 5); ?></span>)</td>
            
            <td class="text-center">
                <span class="badge bg-opacity-10 <?= $badge_class; ?> px-2 py-1 rounded small" style="font-size: 12px; font-weight: 600;"><?= $status; ?></span>
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