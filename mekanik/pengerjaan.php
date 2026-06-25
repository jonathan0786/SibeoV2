<?php
session_start();
include "../config/koneksi.php";

// 1. KEAMANAN AKSES
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'mekanik') {
    header("Location: ../auth/login.php");
    exit();
}

$id_mekanik = $_SESSION['id'];

// 2. PROSES UPDATE STATUS & CATATAN MEKANIK
if (isset($_POST['update_pengerjaan'])) {
    $id_pengerjaan = mysqli_real_escape_string($koneksi, $_POST['id_pengerjaan']);
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status_pengerjaan']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan_mekanik']);
    
    // Jika status diubah menjadi Selesai, set waktu_selesai ke jam sekarang
    if ($status_baru == 'Selesai') {
        $waktu_sekarang = date('Y-m-d H:i:s');
        $query_update = "UPDATE tbl_pengerjaan SET 
                            status_pengerjaan = '$status_baru', 
                            catatan_mekanik = '$catatan', 
                            waktu_selesai = '$waktu_sekarang' 
                         WHERE id_pengerjaan = '$id_pengerjaan' AND id_mekanik = '$id_mekanik'";
    } else {
        // Jika status diubah ke Proses/Sedang Dikerjakan dan waktu_mulai masih kosong, isi waktu_mulai
        $query_cek = mysqli_query($koneksi, "SELECT waktu_mulai FROM tbl_pengerjaan WHERE id_pengerjaan = '$id_pengerjaan'");
        $data_cek = mysqli_fetch_assoc($query_cek);
        
        if (empty($data_cek['waktu_mulai']) || $data_cek['waktu_mulai'] == '0000-00-00 00:00:00') {
            $waktu_mulai = date('Y-m-d H:i:s');
            $query_update = "UPDATE tbl_pengerjaan SET 
                                status_pengerjaan = '$status_baru', 
                                catatan_mekanik = '$catatan', 
                                waktu_mulai = '$waktu_mulai' 
                             WHERE id_pengerjaan = '$id_pengerjaan' AND id_mekanik = '$id_mekanik'";
        } else {
            $query_update = "UPDATE tbl_pengerjaan SET 
                                status_pengerjaan = '$status_baru', 
                                catatan_mekanik = '$catatan' 
                             WHERE id_pengerjaan = '$id_pengerjaan' AND id_mekanik = '$id_mekanik'";
        }
    }
    
    if (mysqli_query($koneksi, $query_update)) {
        echo "<script>alert('Status pengerjaan berhasil diperbarui!'); window.location='pengerjaan.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui status!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengerjaan - SIBEO</title>
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
            padding: 11px 24px; 
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
        
        /* FIX: Menghilangkan margin-left manual agar diserahkan murni ke Grid Bootstrap offset */
        .main-wrapper { padding: 25px 15px; }
        @media (min-width: 768px) { .main-wrapper { padding: 40px; } }
        
        .table-premium {
            background: white; border-radius: 24px; box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08); padding: 25px;
        }
        .table-premium thead th {
            background-color: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 16px 20px; border-bottom: none;
        }
        .table-premium tbody td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 14px; }
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
                    <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-simple me-3"></i>Dashboard Kerja</a>
                    <a href="pengerjaan.php" class="nav-link active"><i class="fa-solid fa-screwdriver-wrench me-3"></i>Pengerjaan</a>
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
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold m-0 text-dark" style="letter-spacing: -0.5px;">Daftar Tugas Pengerjaan</h3>
                        <p class="text-muted small m-0 mt-1">Kelola status dan berikan catatan perbaikan pada kendaraan pelanggan.</p>
                    </div>
                </div>

                <div class="table-premium">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>KODE</th>
                                    <th>KENDARAAN & PLAT</th>
                                    <th>LAYANAN & KELUHAN</th>
                                    <th>WAKTU MULAI / SELESAI</th>
                                    <th>STATUS</th>
                                    <th class="text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $query_pengerjaan = mysqli_query($koneksi, "SELECT p.*, b.kode_booking, b.keluhan, k.merk, k.nomor_polisi, l.nama_paket 
                                                                        FROM tbl_pengerjaan p
                                                                        JOIN tbl_booking b ON p.id_booking = b.id_booking
                                                                        JOIN tbl_kendaraan k ON b.id_kendaraan = k.id_kendaraan
                                                                        JOIN tbl_paket_layanan l ON b.id_paket = l.id_paket
                                                                        WHERE p.id_mekanik = '$id_mekanik'
                                                                        ORDER BY p.id_pengerjaan DESC");

                            if (!$query_pengerjaan || mysqli_num_rows($query_pengerjaan) == 0) {
                                echo "<tr><td colspan='6' class='text-center text-muted py-4 small'>Belum ada daftar pengerjaan yang ditugaskan.</td></tr>";
                            } else {
                                while ($r = mysqli_fetch_assoc($query_pengerjaan)) {
                                    $status = $r['status_pengerjaan'];
                                    $badge = "bg-warning text-warning";
                                    if ($status == "Selesai") $badge = "bg-success text-success";
                                    elseif ($status == "Sedang Dikerjakan" || $status == "Proses") $badge = "bg-primary text-primary";
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= $r['kode_booking']; ?></td>
                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($r['merk']); ?>
                                            <br><code class="text-secondary small fw-bold"><?= htmlspecialchars($r['nomor_polisi']); ?></code>
                                        </td>
                                        <td>
                                            <span class="fw-medium"><?= htmlspecialchars($r['nama_paket']); ?></span>
                                            <br><small class="text-muted d-block" style="max-width: 250px; font-size: 11px;">Ket: <?= !empty($r['keluhan']) ? htmlspecialchars($r['keluhan']) : '-'; ?></small>
                                        </td>
                                        <td style="font-size: 13px;">
                                            <div><i class="fa-solid fa-play text-muted me-1 small"></i> <?= (!empty($r['waktu_mulai']) && $r['waktu_mulai'] != '0000-00-00 00:00:00') ? $r['waktu_mulai'] : '-'; ?></div>
                                            <div class="mt-1"><i class="fa-solid fa-flag-checkered text-muted me-1 small"></i> <?= (!empty($r['waktu_selesai']) && $r['waktu_selesai'] != '0000-00-00 00:00:00') ? $r['waktu_selesai'] : '-'; ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-opacity-10 <?= $badge; ?> px-2.5 py-1.5 rounded" style="font-size: 12px; font-weight: 600;"><?= $status; ?></span>
                                            <?php if(!empty($r['catatan_mekanik'])): ?>
                                                <br><small class="text-secondary" style="font-size: 11px;"><i>Note: <?= htmlspecialchars($r['catatan_mekanik']); ?></i></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalUpdate<?= $r['id_pengerjaan']; ?>">
                                                <i class="fa-solid fa-pen-to-square me-1"></i> Update
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="modalUpdate<?= $r['id_pengerjaan']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="border-radius: 16px;">
                                                <form method="POST" action="">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Update Pengerjaan - <?= $r['kode_booking']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_pengerjaan" value="<?= $r['id_pengerjaan']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">STATUS PENGERJAAN</label>
                                                            <select class="form-select" name="status_pengerjaan" required>
                                                                <option value="Pending" <?= ($status == 'Pending') ? 'selected' : ''; ?>>Pending (Belum Dikerjakan)</option>
                                                                <option value="Sedang Dikerjakan" <?= ($status == 'Sedang Dikerjakan' || $status == 'Proses') ? 'selected' : ''; ?>>Sedang Dikerjakan</option>
                                                                <option value="Selesai" <?= ($status == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-2">
                                                            <label class="form-label fw-semibold small text-muted">CATATAN MEKANIK</label>
                                                            <textarea class="form-control" name="catatan_mekanik" rows="3" placeholder="Contoh: Oli sudah diganti, rem depan dibersihkan..."><?= htmlspecialchars($r['catatan_mekanik']); ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="update_pengerjaan" class="btn btn-sm btn-primary px-3">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> </div> </div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>