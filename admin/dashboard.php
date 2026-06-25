<?php
session_start();
include "../config/koneksi.php";

// ====================================================================
// MASTER DATA COUNTER (Bawaan Kode Asli)
// ====================================================================
$hitung_pelanggan = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_pelanggan FROM tbl_pelanggan"));
$hitung_sparepart = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_suku_cadang FROM tbl_suku_cadang"));

// ====================================================================
// SINKRONISASI COUTER STATISTIK REALTIME HARI INI (FIXED)
// ====================================================================

// 1. Total Booking Hari Ini (Menggunakan kolom 'tanggal_servis' sesuai database)
$q_booking = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tbl_booking WHERE DATE(tanggal_servis) = CURDATE()");
$r_booking = mysqli_fetch_assoc($q_booking);
$total_booking = $r_booking['total'] ?? 0;

// 2. Mekanik Standby / Aktif
// Jika tabel tbl_mekanik belum punya kolom status, query otomatis menghitung total mekanik terdaftar
$q_mekanik_check = mysqli_query($koneksi, "SHOW COLUMNS FROM tbl_mekanik LIKE 'status'");
if (mysqli_num_rows($q_mekanik_check) > 0) {
    $q_mekanik = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tbl_mekanik WHERE status = 'aktif' OR status = 'bekerja'");
    $r_mekanik = mysqli_fetch_assoc($q_mekanik);
    $mekanik_aktif = $r_mekanik['total'] ?? 0;
} else {
    $mekanik_aktif = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_mekanik FROM tbl_mekanik"));
}

// 3. Omset Masuk Hari Ini
// Diproteksi agar tidak crash jika kamu belum membuat tabel transaksi pembayaran
$val_transaksi = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_transaksi'");
if (mysqli_num_rows($val_transaksi) > 0) {
    // Menyesuaikan format kolom tanggal transaksi yang lazim digunakan
    $q_pendapatan = mysqli_query($koneksi, "SELECT SUM(total_bayar) as total FROM tbl_transaksi WHERE DATE(tgl_transaksi) = CURDATE() OR DATE(created_at) = CURDATE()");
    $r_pendapatan = mysqli_fetch_assoc($q_pendapatan);
    $pendapatan_hari_ini = $r_pendapatan['total'] ?? 0;
} else {
    $pendapatan_hari_ini = 0;
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        /* SideBar */
        .sidebar {
            background: #111625;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 999;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar-brand-wrapper {
            padding: 20px 24px 10px 24px;
        }
        .sidebar-brand {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #38bdf8;
        }
        .sidebar-subtitle {
            font-size: 9px; 
            font-weight: 700;
            letter-spacing: 1px; 
            color: #475569;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .nav-section-title {
            font-size: 10px;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 15px 24px 6px 24px;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            font-size: 14px;
            font-weight: 600;
            padding: 9px 24px; 
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
            text-decoration: none;
        }
        .sidebar .nav-link i {
            font-size: 16px;
            width: 28px;
            color: #64748b;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover {
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.04);
        }
        .sidebar .nav-link:hover i {
            color: #38bdf8;
        }
        .sidebar .nav-link.active {
            background: rgba(59, 130, 246, 0.12);
            color: #3b82f6;
            font-weight: 700;
            border-left-color: #3b82f6;
        }
        .sidebar .nav-link.active i {
            color: #3b82f6;
        }
        .logout-link {
            color: #ef4444 !important;
            font-weight: 700 !important;
        }
        .logout-link i {
            color: #ef4444 !important;
        }
        .logout-link:hover {
            background: rgba(239, 68, 68, 0.08) !important;
        }
        
        /* Main Section */
        .main-wrapper {
            margin-left: 16.666667%; 
            padding: 40px;
        }
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

        /* Card Info */
        .stat-card-premium {
            background: white;
            border: none;
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08);
            height: 100%;
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
                    <div class="sidebar-subtitle">MANAGEMENT SYSTEM</div>
                </div>
                
                <div class="nav-section-title">MENU UTAMA</div>
                <div class="nav flex-column">
                    <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-chart-simple me-3"></i>Dashboard</a>
                    <a href="pelanggan.php" class="nav-link"><i class="fa-solid fa-users me-3"></i>Data Pelanggan</a>
                    <a href="suku_cadang.php" class="nav-link"><i class="fa-solid fa-layer-group me-3"></i>Suku Cadang</a>
                    <a href="mekanik.php" class="nav-link"><i class="fa-solid fa-clipboard-user me-3"></i>Data Mekanik</a>
                    <a href="kendaraan.php" class="nav-link"><i class="fa-solid fa-car me-3"></i>Data Kendaraan</a>
                </div>

                <div class="nav-section-title">MENU OPERASIONAL</div>
                <div class="nav flex-column">
                    <a href="paket_layanan.php" class="nav-link"><i class="fa-solid fa-tags me-3"></i>Paket Layanan</a>
                    <a href="alat_kerja.php" class="nav-link"><i class="fa-solid fa-toolbox me-3"></i>Alat Kerja</a>
                    <a href="stall.php" class="nav-link"><i class="fa-solid fa-circle-dot me-3"></i>Data Stall</a>
                </div>
            </div>
            
            <div class="mb-3 pt-2">
                <div class="nav flex-column">
                    <a href="../auth/logout.php" class="nav-link logout-link" onclick="return confirm('Keluar dari sistem?')">
                        <i class="fa-solid fa-sign-out-alt"></i>Keluar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 main-wrapper">
            
            <!-- Banner Welcome -->
            <div class="welcome-banner mb-4">
                <span class="badge mb-3 px-3 py-2" style="font-size: 11px; font-weight: 800; letter-spacing: 1px; background: rgba(255, 255, 255, 0.15); color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25);">WORKSPACE AKTIF</span>
                <h1 class="fw-bold m-0 mb-2" style="font-size: 28px; letter-spacing: -0.5px;">Selamat Datang di SIBEO</h1>
                <p class="small m-0">Kelola penuh alur pendaftaran servis mahasiswa, rekapitulasi data mekanik, dan inventori gudang sparepart secara realtime.</p>
            </div>

            <h6 class="fw-bold text-uppercase mb-3" style="font-size: 11px; letter-spacing: 1px; color: #64748b;"><i class="fa-solid fa-bolt me-2 text-warning"></i>Statistik Performa Hari Ini</h6>
            <div class="row g-4 mb-5">
                
                <div class="col-md-6 col-lg-4">
                    <div class="stat-card-premium">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; color: #94a3b8 !important;">ANTREAN BOOKING</span>
                                <h2 class="fw-extrabold mb-0 mt-2" style="color: #0f172a; font-size: 32px; font-weight: 800;"><?= $total_booking; ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">Kendaraan</span></h2>
                            </div>
                            <div class="icon-shape bg-primary bg-opacity-10 text-primary">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top border-light">
                            <a href="booking.php" class="text-decoration-none small fw-bold text-primary">Lihat Antrean Hari Ini &rarr;</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="stat-card-premium">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; color: #94a3b8 !important;">MEKANIK STANDBY</span>
                                <h2 class="fw-extrabold mb-0 mt-2" style="color: #0f172a; font-size: 32px; font-weight: 800;"><?= $mekanik_aktif; ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">Orang</span></h2>
                            </div>
                            <div class="icon-shape bg-success bg-opacity-10 text-success">
                                <i class="fa-solid fa-user-gear"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top border-light">
                            <a href="mekanik.php" class="text-decoration-none small fw-bold text-success">Manajemen Mekanik &rarr;</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="stat-card-premium">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; color: #94a3b8 !important;">OMSET MASUK</span>
                                <h2 class="fw-extrabold mb-0 mt-2" style="color: #10b981; font-size: 26px; font-weight: 800;">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.'); ?></h2>
                            </div>
                            <div class="icon-shape bg-warning bg-opacity-10 text-warning">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top border-light">
                            <a href="transaksi.php" class="text-decoration-none small fw-bold text-warning" style="color: #d97706 !important;">Buka Laporan Transaksi &rarr;</a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- SECTION MASTER DATA REKAPITULASI (Kode Asli) -->
            <h6 class="fw-bold text-uppercase mb-3" style="font-size: 11px; letter-spacing: 1px; color: #64748b;"><i class="fa-solid fa-database me-2"></i>Total Data Master</h6>
            <div class="row g-4 mb-5">
                <div class="col-md-6 col-lg-4">
                    <div class="stat-card-premium">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; color: #94a3b8 !important;">REGISTRASI PELANGGAN</span>
                                <h2 class="fw-extrabold mb-0 mt-2" style="color: #0f172a; font-size: 32px; font-weight: 800;"><?= $hitung_pelanggan; ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">Civitas</span></h2>
                            </div>
                            <div class="icon-shape bg-primary bg-opacity-10 text-primary">
                                <i class="fa-solid fa-book"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top border-light">
                            <a href="pelanggan.php" class="text-decoration-none small fw-bold text-primary">Buka Manajer Pelanggan &rarr;</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="stat-card-premium">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px; color: #94a3b8 !important;">INVENTORI KOMPONEN</span>
                                <h2 class="fw-extrabold mb-0 mt-2" style="color: #0f172a; font-size: 32px; font-weight: 800;"><?= $hitung_sparepart; ?> <span style="font-size: 14px; font-weight: 500; color: #64748b;">Item</span></h2>
                            </div>
                            <div class="icon-shape bg-success bg-opacity-10 text-success">
                                <i class="fa-solid fa-wrench"></i>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top border-light">
                            <a href="suku_cadang.php" class="text-decoration-none small fw-bold text-success">Cek Stok Komponen &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Aktivitas Terbaru (Kode Asli) -->
            <div class="table-premium">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold m-0" style="color: #0f172a;">Aktivitas Pelanggan Terbaru</h5>
                        <p class="text-muted small m-0 mt-1">Daftar pendaftaran akun civitas akademika terakhir yang masuk ke database.</p>
                    </div>
                    <a href="pelanggan.php" class="btn btn-sm btn-outline-secondary rounded-3 px-3 py-1.5 fw-semibold" style="font-size: 12px;">Lihat Semua</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>NO. CUSTOMER</th>
                                <th>NAMA LENGKAP</th>
                                <th>NIM / NPK IDENTITY</th>
                                <th>KONTAK WHATSAPP</th>
                                <th class="text-center">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tampil_terbaru = mysqli_query($koneksi, "SELECT * FROM tbl_pelanggan ORDER BY id_pelanggan DESC LIMIT 3");
                            if (mysqli_num_rows($tampil_terbaru) == 0) {
                                echo "<tr><td colspan='5' class='text-center text-muted small py-4'>Belum ada data pelanggan yang terdaftar.</td></tr>";
                            }
                            while ($r = mysqli_fetch_assoc($tampil_terbaru)) {
                                ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= $r['nomor_pelanggan']; ?></td>
                                    <td class="fw-semibold" style="color: #0f172a;"><?= $r['nama_lengkap']; ?></td>
                                    <td><?= $r['nik']; ?></td>
                                    <td><?= $r['no_telepon']; ?></td>
                                    <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded small" style="font-size: 12px; font-weight: 600;">Aktif</span></td>
                                </tr>
                                <?php
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