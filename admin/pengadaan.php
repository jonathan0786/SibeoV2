<?php
session_start();
include "../config/koneksi.php";

$current_page = basename($_SERVER['PHP_SELF']);

// ==========================================
// GENERATE OTOMATIS KODE PENGADAAN (PGD-XXX)
// ==========================================
$query_auto = mysqli_query($koneksi, "SELECT kode_pengadaan FROM tbl_pengadaan ORDER BY id_pengadaan DESC LIMIT 1");
if (mysqli_num_rows($query_auto) > 0) {
    $data_auto = mysqli_fetch_assoc($query_auto);
    $last_num  = $data_auto['kode_pengadaan']; 
    $clean_num = (int)substr($last_num, 4);    
    $next_num  = $clean_num + 1;
    $kode_otomatis = "PGD-" . sprintf("%03d", $next_num); 
} else {
    $kode_otomatis = "PGD-001";
}

// ==========================================
// PROSES LOGIKAL TRANSAKSI PENGADAAN
// ==========================================

if (isset($_POST['action_tambah'])) {
    $kode_pengadaan = mysqli_real_escape_string($koneksi, $_POST['kode_pengadaan']);
    $id_sc          = mysqli_real_escape_string($koneksi, $_POST['id_suku_cadang']);
    $supplier       = mysqli_real_escape_string($koneksi, $_POST['supplier']);
    $jumlah         = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $total_biaya    = mysqli_real_escape_string($koneksi, $_POST['total_biaya']);
    $tanggal        = mysqli_real_escape_string($koneksi, $_POST['tanggal_pengadaan']);
    
    mysqli_autocommit($koneksi, FALSE);
    $sukses = true;
    
    // 1. Insert ke tabel pengadaan
    $query_add = mysqli_query($koneksi, "INSERT INTO tbl_pengadaan (kode_pengadaan, id_suku_cadang, supplier, jumlah, total_biaya, tanggal_pengadaan) 
                                         VALUES ('$kode_pengadaan', '$id_sc', '$supplier', '$jumlah', '$total_biaya', '$tanggal')");
    if(!$query_add) $sukses = false;
    
    // 2. Update tambah stok ke tabel suku cadang
    $query_up = mysqli_query($koneksi, "UPDATE tbl_suku_cadang SET stok = stok + $jumlah WHERE id_suku_cadang = '$id_sc'");
    if(!$query_up) $sukses = false;
    
    if ($sukses) {
        mysqli_commit($koneksi);
        echo "<script>alert('Pengadaan berhasil disimpan dan stok telah bertambah!'); window.location='pengadaan.php';</script>";
    } else {
        mysqli_rollback($koneksi);
        echo "<script>alert('Gagal menyimpan transaksi pengadaan.');</script>";
    }
}

if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    $get_data = mysqli_query($koneksi, "SELECT id_suku_cadang, jumlah FROM tbl_pengadaan WHERE id_pengadaan='$id_hapus'");
    if(mysqli_num_rows($get_data) > 0) {
        $dt = mysqli_fetch_assoc($get_data);
        $id_sc = $dt['id_suku_cadang'];
        $jumlah = $dt['jumlah'];
        
        mysqli_autocommit($koneksi, FALSE);
        $q_del = mysqli_query($koneksi, "DELETE FROM tbl_pengadaan WHERE id_pengadaan='$id_hapus'");
        $q_up  = mysqli_query($koneksi, "UPDATE tbl_suku_cadang SET stok = GREATEST(0, stok - $jumlah) WHERE id_suku_cadang = '$id_sc'");
        
        if($q_del && $q_up){
            mysqli_commit($koneksi);
            echo "<script>alert('Data pengadaan dihapus, stok dikembalikan.'); window.location='pengadaan.php';</script>";
        } else {
            mysqli_rollback($koneksi);
        }
    }
}

$total_pengadaan_query = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_pengadaan");
$total_pengadaan = (int) mysqli_fetch_assoc($total_pengadaan_query)['total'];

$total_nilai_query = mysqli_query($koneksi, "SELECT COALESCE(SUM(total_biaya), 0) AS total FROM tbl_pengadaan");
$total_nilai = (int) mysqli_fetch_assoc($total_nilai_query)['total'];

function safe_text($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengadaan Suku Cadang - SIBEO</title>
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
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        * { font-family: 'Plus Jakarta Sans', sans-serif !important; }
        html, body { min-height: 100%; height: 100%; }
        body { margin: 0; background-color: var(--bg-body); color: #334155; overflow-x: hidden; }
        .layout-wrapper { display: flex; min-height: 100vh; width: 100%; }

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

        .main-canvas { flex: 1; display: flex; flex-direction: column; min-height: 100vh; padding: 32px 40px 24px; }
        .main-content { flex: 1; display: flex; flex-direction: column; gap: 20px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 4px 2px; }
        .page-title h3 { font-size: 24px; }

        .summary-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .summary-card { background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%); border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px 20px; box-shadow: var(--card-shadow); display: flex; align-items: center; gap: 14px; }
        .summary-icon { width: 46px; height: 46px; display: grid; place-items: center; border-radius: 12px; background: rgba(59, 130, 246, 0.12); color: var(--sidebar-active); font-size: 20px; }
        .summary-icon.accent { background: rgba(16, 185, 129, 0.12); color: #10b981; }
        .summary-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; }
        .summary-value { font-size: 18px; font-weight: 700; color: var(--text-dark); }

        .data-card-premium { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: var(--card-shadow); overflow: hidden; }
        .data-card-header { padding: 22px 24px; background: #ffffff; border-bottom: 1px solid #f1f5f9; }
        .data-card-title { font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0; }
        .data-card-body { padding: 16px 18px 20px; }
        
        .table-premium thead th { background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; }
        .table-premium tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
        .table-premium tbody tr:hover { background: #f8fbff; }

        .btn-premium-primary { background-color: var(--sidebar-active); color: #ffffff; border: none; border-radius: 10px; padding: 10px 18px; font-size: 13.5px; font-weight: 600; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px; }
        .btn-premium-primary:hover { background-color: #2563eb; color: #ffffff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2); }
        
        .modal-content { border-radius: 16px; border: none; box-shadow: 0 20px 50px rgba(0,0,0,0.1); }
        .modal-header { border-bottom: 1px solid #f1f5f9; padding: 20px 24px; }
        .modal-body { padding: 24px; }
        .modal-footer { border-top: 1px solid #f1f5f9; padding: 16px 24px; }
        .form-control, .form-select { border-radius: 10px; padding: 10.5px 14px; border: 1px solid #cbd5e1; font-size: 14px; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); border-color: var(--sidebar-active); }

        @media (max-width: 992px) {
            .main-canvas { padding: 24px 20px; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .summary-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="layout-wrapper">
    
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-canvas">
        <div class="main-content">
            <div class="page-header">
                <div class="page-title">
                    <h3 class="fw-bold text-dark m-0">Restock & Pengadaan Suku Cadang</h3>
                    <p class="text-muted small m-0 mt-1">Catat belanja masuk stok barang dari supplier untuk gudang.</p>
                </div>
                <button type="button" class="btn btn-premium-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-cart-plus"></i> Catat Pengadaan Baru
                </button>
            </div>

            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon"><i class="bi bi-cart-check"></i></div>
                    <div>
                        <div class="summary-label">Total Pengadaan</div>
                        <div class="summary-value"><?= number_format($total_pengadaan, 0, ',', '.'); ?> kali</div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon accent"><i class="bi bi-cash-coin"></i></div>
                    <div>
                        <div class="summary-label">Nilai Belanja</div>
                        <div class="summary-value">Rp <?= number_format($total_nilai, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>

            <div class="data-card-premium">
                <div class="data-card-header">
                    <h5 class="data-card-title"><i class="bi bi-bag-check-fill text-primary me-2"></i>Histori Pembelian Stok</h5>
                </div>
                <div class="data-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-premium align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Tanggal</th>
                                    <th>Kode Transaksi</th>
                                    <th>Nama Suku Cadang</th>
                                    <th>Supplier</th>
                                    <th class="text-center">Qty Masuk</th>
                                    <th>Total Biaya</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query_pgd = mysqli_query($koneksi, "
                                    SELECT p.*, sc.nama_part, sc.kode_part 
                                    FROM tbl_pengadaan p
                                    JOIN tbl_suku_cadang sc ON p.id_suku_cadang = sc.id_suku_cadang
                                    ORDER BY p.tanggal_pengadaan DESC, p.id_pengadaan DESC
                                ");
                                
                                if ($query_pgd && mysqli_num_rows($query_pgd) > 0) {
                                    while ($data = mysqli_fetch_assoc($query_pgd)) {
                                        ?>
                                        <tr>
                                            <td class="text-center text-secondary fw-semibold"><?= $no++; ?></td>
                                            <td><span class="fw-bold text-dark"><?= date('d M Y', strtotime($data['tanggal_pengadaan'])); ?></span></td>
                                            <td><span class="badge bg-light text-primary border border-primary-subtle px-2 py-1"><?= safe_text($data['kode_pengadaan']); ?></span></td>
                                            <td>
                                                <strong class="text-dark"><?= safe_text($data['nama_part']); ?></strong><br>
                                                <small class="text-muted"><?= safe_text($data['kode_part']); ?></small>
                                            </td>
                                            <td><i class="bi bi-truck text-muted me-1"></i><?= safe_text($data['supplier']); ?></td>
                                            <td class="text-center fw-bold text-success">+<?= safe_text($data['jumlah']); ?> Pcs</td>
                                            <td><strong class="text-danger">Rp <?= number_format($data['total_biaya'], 0, ',', '.'); ?></strong></td>
                                            <td class="text-center">
                                                <a href="pengadaan.php?hapus=<?= $data['id_pengadaan']; ?>" class="btn btn-sm btn-light text-danger fw-bold px-2" onclick="return confirm('Hapus data (Stok akan dikurangi kembali)?')">
                                                    <i class="bi bi-trash3-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center text-muted py-5 small'><i class='bi bi-inbox d-block fs-2 mb-2 opacity-50'></i>Belum ada histori pengadaan barang.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-cart-plus text-primary me-2"></i>Form Pengadaan Barang Masuk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="pengadaan.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Kode Transaksi</label>
                            <input type="text" name="kode_pengadaan" class="form-control bg-light fw-bold text-primary" value="<?= $kode_otomatis; ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Tanggal Pengadaan</label>
                            <input type="date" name="tanggal_pengadaan" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-secondary">Pilih Suku Cadang (Sparepart)</label>
                            <select name="id_suku_cadang" class="form-select" required>
                                <option value="">-- Pilih Barang yang Dibeli --</option>
                                <?php 
                                $res_sc = mysqli_query($koneksi, "SELECT id_suku_cadang, nama_part, kode_part, stok FROM tbl_suku_cadang");
                                while($sc = mysqli_fetch_assoc($res_sc)) { 
                                    echo "<option value='".$sc['id_suku_cadang']."'>".$sc['kode_part']." - ".$sc['nama_part']." (Sisa: ".$sc['stok'].")</option>"; 
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-secondary">Nama Toko / Supplier</label>
                            <input type="text" name="supplier" class="form-control" placeholder="Contoh: PT. Astra Otoparts" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary">Jumlah (Qty)</label>
                            <input type="number" name="jumlah" class="form-control" placeholder="0" min="1" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-secondary">Total Tagihan / Biaya (Rp)</label>
                            <input type="number" name="total_biaya" class="form-control" placeholder="Contoh: 1500000" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light fw-semibold px-4" style="border-radius:10px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="action_tambah" class="btn btn-primary fw-semibold px-4" style="border-radius:10px;">Simpan & Tambah ke Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>