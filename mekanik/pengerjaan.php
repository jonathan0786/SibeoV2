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
    
    // Jika status diubah menjadi Selesai, panggil stored procedure sp_selesaikan_servis
    if ($status_baru == 'Selesai') {
        // Simpan catatan terlebih dahulu
        mysqli_query($koneksi, "UPDATE tbl_pengerjaan SET catatan_pengerjaan = '$catatan' WHERE id_pengerjaan = '$id_pengerjaan'");
        $query_update = "CALL sp_selesaikan_servis('$id_pengerjaan')";
    } else {
        // Cek jika waktu mulai masih kosong
        $query_cek = mysqli_query($koneksi, "SELECT waktu_mulai FROM tbl_pengerjaan WHERE id_pengerjaan = '$id_pengerjaan'");
        $data_cek = mysqli_fetch_assoc($query_cek);
        
        if (empty($data_cek['waktu_mulai']) || $data_cek['waktu_mulai'] == '0000-00-00 00:00:00' || $data_cek['waktu_mulai'] == NULL) {
            $waktu_mulai = date('Y-m-d H:i:s');
            $query_update = "UPDATE tbl_pengerjaan SET 
                                status = '$status_baru', 
                                catatan_pengerjaan = '$catatan', 
                                waktu_mulai = '$waktu_mulai' 
                             WHERE id_pengerjaan = '$id_pengerjaan' AND id_mekanik = '$id_mekanik'";
        } else {
            $query_update = "UPDATE tbl_pengerjaan SET 
                                status = '$status_baru', 
                                catatan_pengerjaan = '$catatan' 
                             WHERE id_pengerjaan = '$id_pengerjaan' AND id_mekanik = '$id_mekanik'";
        }
    }
    
    if (mysqli_query($koneksi, $query_update)) {
        echo "<script>alert('Status pengerjaan berhasil diperbarui!'); window.location='pengerjaan.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui status: " . mysqli_error($koneksi) . "');</script>";
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
    <!-- Bootstrap Icons untuk Sidebar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* CSS UNIFIED STANDAR */
        :root {
            --bg-body: #f4f6f9;
            --sidebar-bg: #1e293b;
            --sidebar-color: #94a3b8;
            --sidebar-active: #3b82f6;
            --text-dark: #0f172a;
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

        /* Komponen Tabel Premium */
        .table-premium { background: white; border-radius: 24px; box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08); padding: 25px; }
        .table-premium thead th { background-color: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 16px 20px; border-bottom: none; }
        .table-premium tbody td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 14px; }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-canvas">
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
                            $status = $r['status'];
                            $badge = "bg-warning text-warning";
                            if ($status == "Selesai") $badge = "bg-success text-success";
                            elseif ($status == "Sedang Dikerjakan" || $status == "Proses" || $status == "dimulai") $badge = "bg-primary text-primary";
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
                                    <span class="badge bg-opacity-10 <?= $badge; ?> px-2.5 py-1.5 rounded text-capitalize" style="font-size: 12px; font-weight: 600;"><?= $status; ?></span>
                                    <?php if(!empty($r['catatan_pengerjaan'])): ?>
                                        <br><small class="text-secondary" style="font-size: 11px;"><i>Note: <?= htmlspecialchars($r['catatan_pengerjaan']); ?></i></small>
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
                                                        <option value="dimulai" <?= ($status == 'dimulai' || $status == 'Pending') ? 'selected' : ''; ?>>Belum Dikerjakan (Dimulai)</option>
                                                        <option value="Sedang Dikerjakan" <?= ($status == 'Sedang Dikerjakan' || $status == 'Proses') ? 'selected' : ''; ?>>Sedang Dikerjakan</option>
                                                        <option value="Selesai" <?= ($status == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-2">
                                                    <label class="form-label fw-semibold small text-muted">CATATAN PERBAIKAN</label>
                                                    <textarea class="form-control" name="catatan_mekanik" rows="3" placeholder="Contoh: Oli sudah diganti, rem depan dibersihkan..."><?= htmlspecialchars($r['catatan_pengerjaan']); ?></textarea>
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
    </div>
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>