<?php
session_start();
include "../config/koneksi.php";

$current_page = basename($_SERVER['PHP_SELF']);

// ==========================================
// GENERATE OTOMATIS NOMOR STALL (STL-XXX)
// ==========================================
$query_auto_stall = mysqli_query($koneksi, "SELECT nomor_stall FROM tbl_stall ORDER BY id_stall DESC LIMIT 1");
if (mysqli_num_rows($query_auto_stall) > 0) {
    $data_auto = mysqli_fetch_assoc($query_auto_stall);
    $last_num  = $data_auto['nomor_stall']; // Contoh: "STL-001"
    $clean_num = (int)substr($last_num, 4);     // Ambil angka setelah "STL-"
    $next_num  = $clean_num + 1;
    $nomor_stall_otomatis = "STL-" . sprintf("%03d", $next_num); // Jadi "STL-002"
} else {
    $nomor_stall_otomatis = "STL-001";
}

// ==========================================
// PROSES LOGIKAL TRANSAKSI (POST BACK)
// ==========================================

// 1. PROSES TAMBAH DATA STALL
if (isset($_POST['action_tambah'])) {
    $nomor_stall = mysqli_real_escape_string($koneksi, $_POST['nomor_stall']);
    $keterangan  = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $status      = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    $query_add = mysqli_query($koneksi, "INSERT INTO tbl_stall (nomor_stall, keterangan, status) 
                                         VALUES ('$nomor_stall', '$keterangan', '$status')");
    if ($query_add) {
        echo "<script>alert('Stall pelayanan baru berhasil ditambahkan!'); window.location='stall.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data stall.');</script>";
    }
}

// 2. PROSES EDIT DATA STALL
if (isset($_POST['action_edit'])) {
    $id_stall    = mysqli_real_escape_string($koneksi, $_POST['id_stall']);
    $keterangan  = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $status      = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    $query_up = mysqli_query($koneksi, "UPDATE tbl_stall SET keterangan='$keterangan', status='$status' WHERE id_stall='$id_stall'");
    if ($query_up) {
        echo "<script>alert('Data stall berhasil diperbarui!'); window.location='stall.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
    }
}

// 3. PROSES HAPUS DATA STALL
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $query_del = mysqli_query($koneksi, "DELETE FROM tbl_stall WHERE id_stall='$id_hapus'");
    if ($query_del) {
        echo "<script>alert('Stall berhasil dihapus!'); window.location='stall.php';</script>";
    }
}

function safe_text($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Stall - SIBEO</title>
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
        
        /* SIDEBAR PANEL STYLE */
        .sidebar-panel { 
            width: 280px; background: var(--sidebar-bg); flex-shrink: 0; 
            display: flex; flex-direction: column; justify-content: space-between; 
            padding: 30px 20px; box-shadow: 10px 0 30px rgba(15, 23, 42, 0.05);
            position: sticky; top: 0; height: 100vh;
        }
        .brand-section { padding: 0 12px 25px 12px; border-bottom: 1px solid rgba(255,255,255,0.06); flex-shrink: 0; }
        .brand-title { font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px; }
        .brand-title span { color: var(--sidebar-active); }
        .brand-subtitle { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 4px; }

        .menu-container { overflow-y: auto; flex-grow: 1; padding-right: 8px; margin-top: 20px; }
        .menu-container::-webkit-scrollbar { width: 5px; }
        .menu-container::-webkit-scrollbar-track { background: transparent; }
        .menu-container::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        .menu-container::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.25); }

        .section-header { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px 12px 8px 12px; }
        .sidebar-panel .nav-link { color: var(--sidebar-color); font-size: 14px; font-weight: 500; padding: 12px 16px; display: flex; align-items: center; text-decoration: none; border-radius: 12px; margin-bottom: 4px; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-panel .nav-link i { width: 24px; font-size: 16px; margin-right: 12px; text-align: center; }
        .sidebar-panel .nav-link:hover { color: #ffffff; background: rgba(255, 255, 255, 0.04); }
        .sidebar-panel .nav-link.active { background: var(--sidebar-active); color: #ffffff; font-weight: 600; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.35); }

        .logout-box { padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); flex-shrink: 0; }
        .logout-btn { color: #f87171 !important; font-weight: 600 !important; background: rgba(239, 68, 68, 0.05); }
        .logout-btn:hover { background: #ef4444 !important; color: #ffffff !important; }
        
        /* MAIN CANVAS */
        .main-canvas { flex-grow: 1; padding: 40px 50px; max-width: calc(100% - 280px); }
        .data-card-premium { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: var(--card-shadow); overflow: hidden; }
        .data-card-header { padding: 24px; background: #ffffff; border-bottom: 1px solid #f1f5f9; }
        .data-card-title { font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0; }
        
        .table-premium thead th { background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; }
        .table-premium tbody td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }

        .btn-premium-primary { background-color: var(--sidebar-active); color: #ffffff; border: none; border-radius: 10px; padding: 10px 20px; font-size: 13.5px; font-weight: 600; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px; }
        .btn-premium-primary:hover { background-color: #2563eb; color: #ffffff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2); }
        
        .modal-content { border-radius: 16px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.1); }
        .modal-header { border-bottom: 1px solid #f1f5f9; padding: 20px 24px; }
        .modal-body { padding: 24px; }
        .modal-footer { border-top: 1px solid #f1f5f9; padding: 16px 24px; }
        .form-control, .form-select { border-radius: 10px; padding: 10.5px 14px; border: 1px solid #cbd5e1; font-size: 14px; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); border-color: var(--sidebar-active); }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <!-- PANEL SIDEBAR -->
    <div class="sidebar-panel">
        <div class="brand-section">
            <div class="brand-title"><i class="bi bi-lightning-charge-fill"></i>SIBEO<span>.</span></div>
            <div class="brand-subtitle">WORKSHOP PANEL v2</div>
        </div>

        <div class="menu-container">
            <div class="section-header">UTAMA</div>
            <a href="dashboard.php" class="nav-link <?= $current_page=='dashboard.php'?'active':'' ?>"><i class="bi bi-speedometer2"></i>Dashboard</a>

            <div class="section-header">Data Master</div>
            <a href="pelanggan.php" class="nav-link <?= $current_page=='pelanggan.php'?'active':'' ?>"><i class="bi bi-people-fill"></i>Pelanggan</a>
            <a href="suku_cadang.php" class="nav-link <?= $current_page=='suku_cadang.php'?'active':'' ?>"><i class="bi bi-box-seam-fill"></i>Suku Cadang</a>
            <a href="mekanik.php" class="nav-link <?= $current_page=='mekanik.php'?'active':'' ?>"><i class="bi bi-tools"></i>Mekanik</a>
            <a href="paket_layanan.php" class="nav-link <?= $current_page=='paket_layanan.php'?'active':'' ?>"><i class="bi bi-tags-fill"></i>Paket Layanan</a>
            <a href="alat_kerja.php" class="nav-link <?= $current_page=='alat_kerja.php'?'active':'' ?>"><i class="bi bi-wrench-adjustable-circle-fill"></i>Alat Kerja</a>
            <a href="stall.php" class="nav-link <?= $current_page=='stall.php'?'active':'' ?>"><i class="bi bi-house-gear-fill"></i>Data Stall</a>
            <div class="section-header">OPERASIONAL</div>
            <a href="pengadaan.php" class="nav-link <?= $current_page=='pengadaan.php'?'active':'' ?>"><i class="bi bi-cart-plus-fill"></i>Pengadaan Stok</a>
            <a href="booking.php" class="nav-link <?= $current_page=='booking.php'?'active':'' ?>"><i class="bi bi-calendar-check-fill"></i>Transaksi Booking</a>
            <a href="laporan.php" class="nav-link <?= $current_page=='laporan.php'?'active':'' ?>"><i class="bi bi-graph-up-arrow"></i>Laporan Pelayanan</a>
            <a href="laporan_sparepart.php" class="nav-link <?= $current_page=='laporan_sparepart.php'?'active':'' ?>"><i class="bi bi-box-seam"></i>Laporan Sparepart</a>
        </div>

        <div class="logout-box">
            <a href="../auth/logout.php" class="nav-link logout-btn" onclick="return confirm('Keluar dari aplikasi SIBEO?')">
                <i class="bi bi-power"></i>Log Out
            </a>
        </div>
    </div>

    <!-- MAIN CANVAS -->
    <div class="main-canvas">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Data Stall Pelayanan</h3>
                <p class="text-muted small m-0 mt-1">Kelola pembagian lokasi atau antrean mekanik (Stall Kerja) bengkel SIBEO.</p>
            </div>
            <button type="button" class="btn btn-premium-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-house-gear-fill"></i>Tambah Stall Baru
            </button>
        </div>

        <div class="data-card-premium">
            <div class="data-card-header">
                <h5 class="data-card-title"><i class="bi bi-grid-1x2 text-primary me-2"></i>Daftar Lokasi Stall</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-premium align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th>Nomor Stall</th>
                            <th>Keterangan / Fungsi Stall</th>
                            <th>Status Operasional</th>
                            <th style="width: 130px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query_stall = mysqli_query($koneksi, "SELECT * FROM tbl_stall ORDER BY id_stall DESC");
                        
                        if ($query_stall && mysqli_num_rows($query_stall) > 0) {
                            while ($data = mysqli_fetch_assoc($query_stall)) {
                                ?>
                                <tr>
                                    <td class="text-center text-secondary fw-semibold"><?= $no++; ?></td>
                                    <td><span class="badge bg-light text-primary border border-primary-subtle px-2 py-1 fw-bold"><?= safe_text($data['nomor_stall']); ?></span></td>
                                    <td><strong class="text-dark"><?= safe_text($data['keterangan']); ?></strong></td>
                                    <td>
                                        <?php 
                                        $status = strtolower($data['status']);
                                        if($status == 'tersedia') {
                                            echo '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 text-uppercase small fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Tersedia</span>';
                                        } elseif($status == 'terpakai' || $status == 'terisi') {
                                            echo '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 text-uppercase small fw-bold"><i class="bi bi-cone-striped me-1"></i>Sedang Terisi</span>';
                                        } else {
                                            echo '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 text-uppercase small fw-bold"><i class="bi bi-tools me-1"></i>Maintenance</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" 
                                                    class="btn btn-sm btn-light text-primary fw-bold px-2 btn-edit-trigger"
                                                    data-id="<?= $data['id_stall']; ?>"
                                                    data-nomor="<?= safe_text($data['nomor_stall']); ?>"
                                                    data-keterangan="<?= safe_text($data['keterangan']); ?>"
                                                    data-status="<?= safe_text($data['status']); ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEdit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="stall.php?hapus=<?= $data['id_stall']; ?>" class="btn btn-sm btn-light text-danger fw-bold px-2" onclick="return confirm('Hapus data stall ini?')">
                                                <i class="bi bi-trash3-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted py-5 small'><i class='bi bi-house-gear d-block fs-2 mb-2 opacity-50'></i>Belum ada data lokasi stall di database.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
MODAL POP UP 1: TAMBAH DATA STALL
========================================== -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-house-gear text-primary me-2"></i>Tambah Stall Pelayanan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="stall.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nomor Stall (Otomatis)</label>
                        <input type="text" name="nomor_stall" class="form-control bg-light fw-bold text-primary" value="<?= $nomor_stall_otomatis; ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Keterangan / Fungsi Stall</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Stall Tune Up / Stall Kelistrikan" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">Status Awal</label>
                        <select name="status" class="form-select" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="terisi">Terisi</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light fw-semibold px-4" style="border-radius:10px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="action_tambah" class="btn btn-primary fw-semibold px-4" style="border-radius:10px;">Simpan Stall</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================
MODAL POP UP 2: EDIT DATA STALL
========================================== -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-warning me-2"></i>Ubah Data Stall</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="stall.php" method="POST">
                <input type="hidden" name="id_stall" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nomor Stall</label>
                        <input type="text" id="edit_nomor" class="form-control bg-light fw-bold text-muted" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Keterangan / Fungsi Stall</label>
                        <input type="text" name="keterangan" id="edit_keterangan" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">Status Operasional</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="terisi">Terisi</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light fw-semibold px-4" style="border-radius:10px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="action_edit" class="btn btn-warning text-dark fw-semibold px-4" style="border-radius:10px;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JAVASCRIPT BINDING DATA KE DALAM MODAL EDIT POPUP
    const editButtons = document.querySelectorAll('.btn-edit-trigger');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_nomor').value = this.getAttribute('data-nomor');
            document.getElementById('edit_keterangan').value = this.getAttribute('data-keterangan');
            
            // Konversi ke lowercase agar sesuai dengan value tag <select>
            const statusValue = this.getAttribute('data-status').toLowerCase();
            document.getElementById('edit_status').value = statusValue;
        });
    });
</script>
</body>
</html>