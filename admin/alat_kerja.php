<?php
session_start();
include "../config/koneksi.php";

// Ambil data inventori alat kerja dari database
$query_alat = mysqli_query($koneksi, "SELECT * FROM tbl_alat_kerja ORDER BY id_alat DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alat Kerja - SIBEO</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* BASE LAYOUT SYSTEM */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            overflow-x: hidden;
        }
        
        /* SIDEBAR COMPONENTS */
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

        /* MAIN WRAPPER SYSTEM */
        .main-wrapper {
            margin-left: 16.666667%; 
            padding: 40px;
            min-height: 100vh;
        }

        /* DATA TABLE COMPONENTS */
        .table-container-sibeo {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            padding: 30px;
            margin-top: 30px;
            border: 1px solid rgba(0, 0, 0, 0.01);
        }
        .table th {
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.75px;
            border-bottom: 2px solid #f1f5f9;
            padding: 16px;
        }
        .table td {
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        /* ACTION BUTTON COMPONENTS */
        .btn-add-premium {
            background-color: #2563eb;
            color: white;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-add-premium:hover { 
            background-color: #1d4ed8; 
            color: white; 
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
                    <a href="paket_layanan.php" class="nav-link"><i class="fa-solid fa-tags me-3"></i>Paket Layanan</a>
                    <a href="alat_kerja.php" class="nav-link active"><i class="fa-solid fa-toolbox me-3"></i>Alat Kerja</a>
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
            
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="fw-bold m-0" style="color: #1e293b; font-size: 32px; letter-spacing: -0.5px;">Inventori Alat Kerja</h1>
                    <p class="text-muted small m-0 mt-1">Daftar tools bengkel, jumlah unit tersedia, dan status kelayakan pakai mekanik.</p>
                </div>
                <a href="tambah_alat.php" class="btn btn-add-premium d-flex align-items-center">
                    <i class="fa-solid fa-plus me-2"></i> Tambah Alat
                </a>
            </div>

            <div class="table-container-sibeo">
                <div class="table-responsive">
                    <table class="table table-borderless m-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;" class="text-center">NO</th>
                                <th>NAMA ALAT / TOOLS</th>
                                <th class="text-center">JUMLAH</th>
                                <th>KONDISI</th>
                                <th style="width: 120px;" class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($query_alat) > 0) {
                                while($row = mysqli_fetch_assoc($query_alat)) {
                                    
                                    // Normalisasi data string kondisi dari database
                                    $kondisi_db = strtolower(trim($row['kondisi'] ?? ''));

                                    // Render badge berdasarkan isi data kondisi asli
                                    if ($kondisi_db == 'baik' || $kondisi_db == '') {
                                        $badge_kondisi = '<span class="badge bg-success" style="font-weight: 700; font-size: 12px; padding: 6px 12px; border-radius: 10px;">
                                                            <i class="fa-solid fa-circle-check me-1"></i> Baik (Siap Pakai)
                                                          </span>';
                                    } else {
                                        $badge_kondisi = '<span class="badge bg-danger" style="font-weight: 700; font-size: 12px; padding: 6px 12px; border-radius: 10px;">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Rusak / Perbaikan
                                                          </span>';
                                    }
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?= $no++; ?></td>
                                <td class="fw-semibold" style="color: #1e293b;"><?= htmlspecialchars($row['nama_alat']); ?></td>
                                <td class="text-center fw-bold"><?= htmlspecialchars($row['jumlah']); ?> Unit</td>
                                <td><?= $badge_kondisi; ?></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="edit_alat.php?id=<?= $row['id_alat']; ?>" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;"><i class="fa-solid fa-pen-to-square"></i></a>
                                        <a href="hapus_alat.php?id=<?= $row['id_alat']; ?>" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" onclick="return confirm('Hapus alat kerja ini dari daftar?')"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-5 text-muted fw-semibold'>Belum ada daftar inventori alat kerja.</td></tr>";
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