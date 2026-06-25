<?php
session_start();
include "../config/koneksi.php"; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Layanan - SIBEO</title>
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
        }
        .sidebar .nav-link:hover {
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.04);
        }
        .sidebar .nav-link.active {
            background: rgba(59, 130, 246, 0.12);
            color: #3b82f6;
            font-weight: 700;
            border-left-color: #3b82f6;
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
            min-height: 100vh;
        }
        .btn-add {
            background-color: #2563eb;
            border: none;
            color: white;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-add:hover {
            background-color: #1d4ed8;
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }
        
        /* Tabel */
        .table-container-sibeo {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            padding: 24px;
            margin-top: 25px;
            border: 1px solid rgba(0, 0, 0, 0.02);
        }
        .table-container-sibeo thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .table-container-sibeo tbody td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 14px;
        }
        .table-container-sibeo tbody tr:last-child td {
            border-bottom: none;
        }
        .text-harga-premium {
            color: #2563eb;
            font-weight: 700;
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
                    <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-simple me-3"></i>Dashboard</a>
                    <a href="pelanggan.php" class="nav-link"><i class="fa-solid fa-users me-3"></i>Data Pelanggan</a>
                    <a href="suku_cadang.php" class="nav-link"><i class="fa-solid fa-layer-group me-3"></i>Suku Cadang</a>
                    <a href="mekanik.php" class="nav-link"><i class="fa-solid fa-clipboard-user me-3"></i>Data Mekanik</a>
                    <a href="kendaraan.php" class="nav-link"><i class="fa-solid fa-car me-3"></i>Data Kendaraan</a>
                </div>

                <div class="nav-section-title">MENU OPERASIONAL</div>
                <div class="nav flex-column">
                    <a href="paket_layanan.php" class="nav-link active"><i class="fa-solid fa-tags me-3"></i>Paket Layanan</a>
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
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h2 class="fw-bold m-0" style="color: #1e293b; letter-spacing: -0.5px;">Paket Layanan</h2>
                    <p class="text-muted small m-0 mt-1">Kelola daftar paket servis berkala dan penentuan harga jasa bengkel SIBEO.</p>
                </div>
                <a href="tambah_paket.php" class="btn btn-add">
                    <i class="fa-solid fa-plus me-2"></i> Tambah Paket
                </a>
            </div>

            <div class="table-container-sibeo">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;" class="text-center">NO</th>
                                <th>NAMA PAKET LAYANAN</th>
                                <th>DESKRIPSI LAYANAN</th>
                                <th>HARGA JASA</th>
                                <th class="text-center" style="width: 140px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query_paket = mysqli_query($koneksi, "SELECT * FROM tbl_paket_layanan ORDER BY id_paket ASC");
                            
                            if (mysqli_num_rows($query_paket) == 0) {
                                echo "<tr><td colspan='5' class='text-center text-muted py-4 small'>Belum ada paket layanan yang terdaftar.</td></tr>";
                            }
                            
                            while ($data = mysqli_fetch_assoc($query_paket)) {
                                ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted"><?= $no++; ?></td>
                                    <td class="fw-bold" style="color: #1e293b;"><?= htmlspecialchars($data['nama_paket']); ?></td>
                                    <td class="text-muted fw-medium"><?= htmlspecialchars($data['deskripsi']); ?></td>
                                    <td class="text-harga-premium">Rp <?= number_format($data['harga'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <a href="edit_paket.php?id=<?= $data['id_paket']; ?>" class="btn btn-sm btn-light text-primary me-1 border" style="background: #ffffff;">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <a href="hapus_paket.php?id=<?= $data['id_paket']; ?>" class="btn btn-sm btn-light text-danger border" style="background: #ffffff;" onclick="return confirm('Hapus paket layanan ini?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
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