<?php
session_start();
include "../config/koneksi.php";

// Menentukan halaman aktif untuk sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// =========================================================================
// MENGAMBIL DATA SESSION PELANGGAN
// =========================================================================
if (isset($_SESSION['id_pelanggan'])) {
    $id_pelanggan_login = $_SESSION['id_pelanggan'];
} else {
    // Sesuai gambar database Anda, pelanggan ID 2 memiliki 2 kendaraan (Motor & Mobil)
    $id_pelanggan_login = 2; 
}

// =========================================================================
// PROSES TAMBAH KENDARAAN BARU
// =========================================================================
if (isset($_POST['tambah_kendaraan'])) {
    $nomor_polisi     = mysqli_real_escape_string($koneksi, $_POST['nomor_polisi']);
    $merk             = mysqli_real_escape_string($koneksi, $_POST['merk']);
    $tipe             = mysqli_real_escape_string($koneksi, $_POST['tipe']);
    $tahun_pembuatan  = mysqli_real_escape_string($koneksi, $_POST['tahun_pembuatan']);

    $sql_insert = "INSERT INTO tbl_kendaraan (id_pelanggan, nomor_polisi, merk, tipe, tahun_pembuatan) 
                   VALUES ('$id_pelanggan_login', '$nomor_polisi', '$merk', '$tipe', '$tahun_pembuatan')";
    
    if (mysqli_query($koneksi, $sql_insert)) {
        echo "<script>alert('Kendaraan berhasil ditambahkan!'); window.location='kendaraan.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan kendaraan: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kendaraan - SIBEO</title>
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
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02);
        }
        * { font-family: 'Plus Jakarta Sans', sans-serif !important; }
        body { background-color: var(--bg-body); color: #334155; overflow-x: hidden; }
        .layout-wrapper { display: flex; min-height: 100vh; }
        
        /* SIDEBAR KONSISTEN SIBEO */
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
        .sidebar-panel .nav-link { color: var(--sidebar-color); font-size: 14px; font-weight: 500; padding: 12px 16px; display: flex; align-items: center; text-decoration: none; border-radius: 12px; margin-bottom: 4px; transition: all 0.2s ease; }
        .sidebar-panel .nav-link i { width: 24px; font-size: 16px; margin-right: 12px; text-align: center; }
        .sidebar-panel .nav-link:hover { color: #ffffff; background: rgba(255, 255, 255, 0.04); }
        .sidebar-panel .nav-link.active { background: var(--sidebar-active); color: #ffffff; font-weight: 600; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.35); }
        .section-header { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px 12px 8px 12px; }
        
        .logout-box { padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); }
        .logout-btn { color: #f87171 !important; font-weight: 600 !important; background: rgba(239, 68, 68, 0.05); border-radius: 12px; }
        
        /* MAIN CANVAS SCREEN */
        .main-canvas { flex-grow: 1; padding: 40px 50px; max-width: calc(100% - 280px); }
        
        /* PREMIUM CARD & TABLE STYLE */
        .data-card { background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: var(--card-shadow); padding: 32px; }
        .card-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; }
        .card-header-title { font-size: 18px; font-weight: 700; color: var(--text-dark); display: flex; align-items: center; gap: 10px; margin: 0; }
        
        .table-premium { margin: 0; }
        .table-premium thead th { background: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 16px; border-bottom: 1px solid #e2e8f0; }
        .table-premium tbody td { padding: 16px; font-size: 14.5px; color: #334155; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        
        .badge-plat { background: #f1f5f9; color: #0f172a; font-weight: 700; padding: 6px 12px; border-radius: 6px; font-size: 13px; border: 1px solid #cbd5e1; display: inline-block; text-transform: uppercase; }
        .badge-jenis { font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px; }
        .badge-motor { background: #fef3c7; color: #d97706; }
        .badge-mobil { background: #dbeafe; color: #2563eb; }

        .btn-add { background: #2563eb; color: #ffffff; font-weight: 600; font-size: 14px; border: none; border-radius: 10px; padding: 10px 20px; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease; }
        .btn-add:hover { background: #1d4ed8; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.2); }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <div class="sidebar-panel">
        <div class="brand-section">
            <div class="brand-title"><i class="bi bi-lightning-charge-fill"></i>SIBEO<span>.</span></div>
            <div class="brand-subtitle">PORTAL PELANGGAN</div>
        </div>
        <div class="menu-container">
            <div class="section-header">MENU UTAMA</div>
            <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-fill"></i>1. Dashboard</a>
            <a href="booking.php" class="nav-link"><i class="bi bi-calendar-plus-fill"></i>2. Booking Servis</a>
            <a href="kendaraan.php" class="nav-link active"><i class="bi bi-car-front-fill"></i>3. Data Kendaraan</a>
            <a href="riwayat.php" class="nav-link"><i class="bi bi-clock-history"></i>4. Riwayat & Nota</a>
        </div>
        <div class="logout-box">
            <a href="../auth/logout.php" class="nav-link logout-btn" onclick="return confirm('Keluar dari portal SIBEO?')"><i class="bi bi-power"></i>Log Out</a>
        </div>
    </div>

    <div class="main-canvas">
        
        <div class="data-card">
            <div class="card-header-flex">
                <h5 class="card-header-title">
                    <i class="bi bi-car-front-fill text-primary"></i> Garasi & Data Kendaraan Anda
                </h5>
                <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-lg"></i> Tambah Kendaraan
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="table table-premium align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70px;">No</th>
                            <th>Jenis</th>
                            <th>Nomor Plat / Polisi</th>
                            <th>Merk & Tipe Kendaraan</th>
                            <th>Tahun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        // Query mengambil data kendaraan milik pelanggan yang sedang login
                        $res_table = mysqli_query($koneksi, "SELECT id_kendaraan, nomor_polisi, merk, tipe, tahun_pembuatan FROM tbl_kendaraan WHERE id_pelanggan = '$id_pelanggan_login'");
                        
                        if ($res_table && mysqli_num_rows($res_table) > 0) {
                            while($row = mysqli_fetch_assoc($res_table)) {
                                // Menentukan badge berdasarkan kata kunci tipe kendaraan di database Anda
                                $tipe_clean = strtolower($row['tipe']);
                                if (strpos($tipe_clean, 'motor') !== false || strpos($tipe_clean, 'vario') !== false || strpos($tipe_clean, 'beat') !== false) {
                                    $jenis_badge = '<span class="badge-jenis badge-motor"><i class="bi bi-bicycle"></i> Motor</span>';
                                } else {
                                    $jenis_badge = '<span class="badge-jenis badge-mobil"><i class="bi bi-car-front"></i> Mobil</span>';
                                }
                        ?>
                                <tr>
                                    <td><span class="text-muted fw-bold"><?= $no++; ?></span></td>
                                    <td><?= $jenis_badge; ?></td>
                                    <td><span class="badge-plat"><?= htmlspecialchars($row['nomor_polisi']); ?></span></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($row['merk'] . ' ' . $row['tipe']); ?></td>
                                    <td class="text-secondary"><?= htmlspecialchars($row['tahun_pembuatan']); ?></td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Belum ada kendaraan terdaftar di garasi Anda.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="modal-header bg-dark text-white p-3">
                <h6 class="modal-title fw-bold m-0"><i class="bi bi-car-front-fill me-2 text-primary"></i>Daftarkan Kendaraan Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nomor Polisi (Plat)</label>
                        <input type="text" name="nomor_polisi" class="form-control text-uppercase" placeholder="Contoh: B 1234 CDE" required style="border-radius: 8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Merk Kendaraan</label>
                        <input type="text" name="merk" class="form-control" placeholder="Contoh: Honda / Nissan" required style="border-radius: 8px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Tipe Kendaraan</label>
                        <input type="text" name="tipe" class="form-control" placeholder="Contoh: Motor Vario / Mobil GTR" required style="border-radius: 8px;">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-secondary">Tahun Pembuatan</label>
                        <input type="number" name="tahun_pembuatan" class="form-control" placeholder="Contoh: 2024" required style="border-radius: 8px;">
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-0 text-end">
                    <button type="button" class="btn btn-secondary btn-sm rounded-3 px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_kendaraan" class="btn btn-primary btn-sm rounded-3 px-4 fw-semibold">Simpan Kendaraan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>