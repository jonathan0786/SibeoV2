<?php
session_start();
include "../config/koneksi.php";

$current_page = basename($_SERVER['PHP_SELF']);

// ==========================================
// PROSES LOGIKAL TRANSAKSI (POST BACK)
// ==========================================

// 1. PROSES TAMBAH DATA PAKET LAYANAN
if (isset($_POST['action_tambah'])) {
    $nama_paket = mysqli_real_escape_string($koneksi, $_POST['nama_paket']);
    $deskripsi  = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga      = mysqli_real_escape_string($koneksi, $_POST['harga']);
    
    $query_add = mysqli_query($koneksi, "INSERT INTO tbl_paket_layanan (nama_paket, deskripsi, harga) 
                                         VALUES ('$nama_paket', '$deskripsi', '$harga')");
    if ($query_add) {
        echo "<script>alert('Paket layanan baru berhasil ditambahkan!'); window.location='paket_layanan.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data paket layanan.');</script>";
    }
}

// 2. PROSES EDIT DATA PAKET LAYANAN
if (isset($_POST['action_edit'])) {
    $id_paket   = mysqli_real_escape_string($koneksi, $_POST['id_paket']);
    $nama_paket = mysqli_real_escape_string($koneksi, $_POST['nama_paket']);
    $deskripsi  = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga      = mysqli_real_escape_string($koneksi, $_POST['harga']);
    
    $query_up = mysqli_query($koneksi, "UPDATE tbl_paket_layanan SET nama_paket='$nama_paket', deskripsi='$deskripsi', harga='$harga' WHERE id_paket='$id_paket'");
    if ($query_up) {
        echo "<script>alert('Data paket layanan berhasil diperbarui!'); window.location='paket_layanan.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
    }
}

// 3. PROSES HAPUS DATA PAKET LAYANAN
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $query_del = mysqli_query($koneksi, "DELETE FROM tbl_paket_layanan WHERE id_paket='$id_hapus'");
    if ($query_del) {
        echo "<script>alert('Paket layanan berhasil dihapus!'); window.location='paket_layanan.php';</script>";
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
    <title>Paket Layanan - SIBEO</title>
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
        .form-control { border-radius: 10px; padding: 10.5px 14px; border: 1px solid #cbd5e1; font-size: 14px; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); border-color: var(--sidebar-active); }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-canvas">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Data Paket Layanan Servis</h3>
                <p class="text-muted small m-0 mt-1">Kelola jenis paket pemeriksaan dan perbaikan bengkel SIBEO.</p>
            </div>
            <button type="button" class="btn btn-premium-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle-fill"></i>Tambah Paket Layanan
            </button>
        </div>

        <div class="data-card-premium">
            <div class="data-card-header">
                <h5 class="data-card-title"><i class="bi bi-tags text-primary me-2"></i>Daftar Master Layanan</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-premium align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;" class="text-center">No</th>
                            <th>Nama Paket Layanan</th>
                            <th>Deskripsi Pekerjaan</th>
                            <th>Biaya Jasa (Harga)</th>
                            <th style="width: 130px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query_layanan = mysqli_query($koneksi, "SELECT * FROM tbl_paket_layanan ORDER BY id_paket DESC");
                        
                        if ($query_layanan && mysqli_num_rows($query_layanan) > 0) {
                            while ($data = mysqli_fetch_assoc($query_layanan)) {
                                ?>
                                <tr>
                                    <td class="text-center text-secondary fw-semibold"><?= $no++; ?></td>
                                    <td><strong class="text-dark"><?= safe_text($data['nama_paket']); ?></strong></td>
                                    <td><span class="text-secondary small fw-medium"><?= safe_text($data['deskripsi']); ?></span></td>
                                    <td><strong class="text-success">Rp <?= number_format($data['harga'], 0, ',', '.'); ?></strong></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" 
                                                    class="btn btn-sm btn-light text-primary fw-bold px-2 btn-edit-trigger"
                                                    data-id="<?= $data['id_paket']; ?>"
                                                    data-nama="<?= safe_text($data['nama_paket']); ?>"
                                                    data-deskripsi="<?= safe_text($data['deskripsi']); ?>"
                                                    data-harga="<?= (int)$data['harga']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEdit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <a href="paket_layanan.php?hapus=<?= $data['id_paket']; ?>" class="btn btn-sm btn-light text-danger fw-bold px-2" onclick="return confirm('Hapus paket layanan ini?')">
                                                <i class="bi bi-trash3-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center text-muted py-5 small'><i class='bi bi-tag d-block fs-2 mb-2 opacity-50'></i>Belum ada data paket layanan di database.</td></tr>";
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
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-tag-fill text-primary me-2"></i>Tambah Paket Layanan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="paket_layanan.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nama Paket Layanan</label>
                        <input type="text" name="nama_paket" class="form-control" placeholder="Contoh: Servis Berkala / Tune Up" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Deskripsi Pekerjaan</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan detail cakupan perbaikan paket ini..." required></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">Biaya Jasa (Rp)</label>
                        <input type="number" name="harga" class="form-control" placeholder="Contoh: 100000" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light fw-semibold px-4" style="border-radius:10px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="action_tambah" class="btn btn-primary fw-semibold px-4" style="border-radius:10px;">Simpan Paket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-warning me-2"></i>Ubah Data Paket Layanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="paket_layanan.php" method="POST">
                <input type="hidden" name="id_paket" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nama Paket Layanan</label>
                        <input type="text" name="nama_paket" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Deskripsi Pekerjaan</label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">Biaya Jasa (Rp)</label>
                        <input type="number" name="harga" id="edit_harga" class="form-control" min="0" required>
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
            document.getElementById('edit_nama').value = this.getAttribute('data-nama');
            document.getElementById('edit_deskripsi').value = this.getAttribute('data-deskripsi');
            document.getElementById('edit_harga').value = this.getAttribute('data-harga');
        });
    });
</script>
</body>
</html>