<?php
session_start();
include "../config/koneksi.php";

$current_page = basename($_SERVER['PHP_SELF']);

// ==========================================
// GENERATE OTOMATIS KODE PART (SP-XXX)
// ==========================================
$query_auto_part = mysqli_query($koneksi, "SELECT kode_part FROM tbl_suku_cadang ORDER BY id_suku_cadang DESC LIMIT 1");
if (mysqli_num_rows($query_auto_part) > 0) {
    $data_auto = mysqli_fetch_assoc($query_auto_part);
    $last_num  = $data_auto['kode_part']; // Contoh: "SP-002"
    $clean_num = (int)substr($last_num, 3);    // Ambil angka "2"
    $next_num  = $clean_num + 1;
    $kode_part_otomatis = "SP-" . sprintf("%03d", $next_num); // Jadi "SP-003"
} else {
    $kode_part_otomatis = "SP-001";
}

// ==========================================
// PROSES LOGIKAL TRANSAKSI (POST BACK)
// ==========================================

// 1. PROSES TAMBAH DATA suku cadang
if (isset($_POST['action_tambah'])) {
    $kode_part = mysqli_real_escape_string($koneksi, $_POST['kode_part']);
    $nama_part = mysqli_real_escape_string($koneksi, $_POST['nama_part']);
    $stok      = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $harga     = mysqli_real_escape_string($koneksi, $_POST['harga_satuan']);
    
    $query_add = mysqli_query($koneksi, "INSERT INTO tbl_suku_cadang (kode_part, nama_part, stok, harga_satuan) 
                                         VALUES ('$kode_part', '$nama_part', '$stok', '$harga')");
    if ($query_add) {
        echo "<script>alert('Suku cadang baru berhasil disimpan!'); window.location='suku_cadang.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data.');</script>";
    }
}

// 2. PROSES EDIT DATA suku cadang
if (isset($_POST['action_edit'])) {
    $id_sc     = mysqli_real_escape_string($koneksi, $_POST['id_suku_cadang']);
    $nama_part = mysqli_real_escape_string($koneksi, $_POST['nama_part']);
    $stok      = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $harga     = mysqli_real_escape_string($koneksi, $_POST['harga_satuan']);
    
    $query_up = mysqli_query($koneksi, "UPDATE tbl_suku_cadang SET nama_part='$nama_part', stok='$stok', harga_satuan='$harga' WHERE id_suku_cadang='$id_sc'");
    if ($query_up) {
        echo "<script>alert('Data suku cadang berhasil diubah!'); window.location='suku_cadang.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
    }
}

// 3. PROSES HAPUS DATA suku cadang
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $query_del = mysqli_query($koneksi, "DELETE FROM tbl_suku_cadang WHERE id_suku_cadang='$id_hapus'");
    if ($query_del) {
        echo "<script>alert('Suku cadang berhasil dihapus!'); window.location='suku_cadang.php';</script>";
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
    <title>Data Suku Cadang - SIBEO</title>
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
        
        /* SIDEBAR PANEL WITH SCROLL */
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
        .form-control { border-radius: 10px; padding: 10.5px 14px; border: 1px solid #cbd5e1; font-size: 14px; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); border-color: var(--sidebar-active); }
    </style>
</head>
<body>

<div class="layout-wrapper">
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
            <a href="booking.php" class="nav-link <?= $current_page=='booking.php'?'active':'' ?>"><i class="bi bi-calendar-check-fill"></i>Transaksi Booking</a>
            <a href="laporan.php" class="nav-link <?= $current_page=='laporan.php'?'active':'' ?>"><i class="bi bi-graph-up-arrow"></i>Laporan Pelayanan</a>
        </div>

        <div class="logout-box">
            <a href="../auth/logout.php" class="nav-link logout-btn" onclick="return confirm('Keluar dari aplikasi SIBEO?')">
                <i class="bi bi-power"></i>Log Out
            </a>
        </div>
    </div>

    <div class="main-canvas">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Data Suku Cadang (Sparepart)</h3>
                <p class="text-muted small m-0 mt-1">Kelola stok inventaris suku cadang bengkel SIBEO.</p>
            </div>
            <button type="button" class="btn btn-premium-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle-fill"></i>Tambah Suku Cadang
            </button>
        </div>

        <div class="data-card-premium">
            <div class="data-card-header">
                <h5 class="data-card-title"><i class="bi bi-box-seam text-primary me-2"></i>Daftar Master Suku Cadang</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-premium align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th>Kode Part</th>
                            <th>Nama Suku Cadang</th>
                            <th>Stok</th>
                            <th>Harga Satuan</th>
                            <th style="width: 130px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query_sc = mysqli_query($koneksi, "SELECT * FROM tbl_suku_cadang ORDER BY id_suku_cadang ASC");
                        
                        if ($query_sc && mysqli_num_rows($query_sc) > 0) {
                            while ($data = mysqli_fetch_assoc($query_sc)) {
                                ?>
                                <tr>
                                    <td class="text-center text-secondary fw-semibold"><?= $no++; ?></td>
                                    <td><span class="badge bg-light text-primary border border-primary-subtle px-2 py-1 fw-bold"><?= safe_text($data['kode_part']); ?></span></td>
                                    <td><strong class="text-dark"><?= safe_text($data['nama_part']); ?></strong></td>
                                    <td>
                                        <span class="fw-bold <?= $data['stok'] <= 5 ? 'text-danger' : 'text-success'; ?>">
                                            <?= safe_text($data['stok']); ?> Pcs
                                        </span>
                                    </td>
                                    <td><strong class="text-dark">Rp <?= number_format($data['harga_satuan'], 0, ',', '.'); ?></strong></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" 
                                                    class="btn btn-sm btn-light text-primary fw-bold px-2 btn-edit-trigger"
                                                    data-id="<?= $data['id_suku_cadang']; ?>"
                                                    data-kode="<?= safe_text($data['kode_part']); ?>"
                                                    data-nama="<?= safe_text($data['nama_part']); ?>"
                                                    data-stok="<?= safe_text($data['stok']); ?>"
                                                    data-harga="<?= (int)$data['harga_satuan']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEdit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="suku_cadang.php?hapus=<?= $data['id_suku_cadang']; ?>" class="btn btn-sm btn-light text-danger fw-bold px-2" onclick="return confirm('Hapus suku cadang ini?')">
                                                <i class="bi bi-trash3-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center text-muted py-5 small'><i class='bi bi-box d-block fs-2 mb-2 opacity-50'></i>Belum ada data suku cadang di database.</td></tr>";
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i>Tambah Suku Cadang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="suku_cadang.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Kode Part (Otomatis)</label>
                        <input type="text" name="kode_part" class="form-control bg-light fw-bold text-primary" value="<?= $kode_part_otomatis; ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nama Suku Cadang</label>
                        <input type="text" name="nama_part" class="form-control" placeholder="Contoh: Oli Top 1 / Kampas Rem" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Jumlah Stok Awal</label>
                        <input type="number" name="stok" class="form-control" placeholder="Masukkan jumlah item" min="0" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">Harga Satuan (Rp)</label>
                        <input type="number" name="harga_satuan" class="form-control" placeholder="Contoh: 150000" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light fw-semibold px-4" style="border-radius:10px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="action_tambah" class="btn btn-primary fw-semibold px-4" style="border-radius:10px;">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-warning me-2"></i>Ubah Data Suku Cadang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="suku_cadang.php" method="POST">
                <input type="hidden" name="id_suku_cadang" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Kode Part</label>
                        <input type="text" id="edit_kode" class="form-control bg-light fw-bold text-muted" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nama Suku Cadang</label>
                        <input type="text" name="nama_part" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Stok</label>
                        <input type="number" name="stok" id="edit_stok" class="form-control" min="0" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">Harga Satuan (Rp)</label>
                        <input type="number" name="harga_satuan" id="edit_harga" class="form-control" min="0" required>
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
            document.getElementById('edit_kode').value = this.getAttribute('data-kode');
            document.getElementById('edit_nama').value = this.getAttribute('data-nama');
            document.getElementById('edit_stok').value = this.getAttribute('data-stok');
            document.getElementById('edit_harga').value = this.getAttribute('data-harga');
        });
    });
</script>
</body>
</html>