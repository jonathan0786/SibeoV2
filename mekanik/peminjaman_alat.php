<?php
session_start();
include "../config/koneksi.php";

// 1. KEAMANAN AKSES
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'mekanik') {
    header("Location: ../auth/login.php");
    exit();
}

$id_mekanik = $_SESSION['id'];

// 2. PROSES INPUT PEMINJAMAN ALAT BARU
if (isset($_POST['pinjam_alat'])) {
    $id_booking = mysqli_real_escape_string($koneksi, $_POST['id_booking']);
    $id_alat = mysqli_real_escape_string($koneksi, $_POST['id_alat']);
    $jumlah_pinjam = intval($_POST['jumlah_pinjam']);
    $waktu_pinjam = date('Y-m-d H:i:s'); 
    $status = "dipinjam"; 

    // Cek pengerjaan
    $cek_pengerjaan = mysqli_query($koneksi, "SELECT id_pengerjaan FROM tbl_pengerjaan WHERE id_booking = '$id_booking'");
    
    if (mysqli_num_rows($cek_pengerjaan) > 0) {
        $data_p = mysqli_fetch_assoc($cek_pengerjaan);
        $id_pengerjaan = $data_p['id_pengerjaan'];
    } else {
        $insert_pengerjaan = mysqli_query($koneksi, "INSERT INTO tbl_pengerjaan (id_booking, id_mekanik, catatan_pemeriksaan, catatan_pengerjaan, status, waktu_mulai) 
                                                     VALUES ('$id_booking', '$id_mekanik', 'Pemeriksaan alat kerja', 'Sedang dikerjakan', 'Proses', '$waktu_pinjam')");
        $id_pengerjaan = mysqli_insert_id($koneksi);
    }

    // Cek stok
    $cek_stok = mysqli_query($koneksi, "SELECT jumlah, nama_alat FROM tbl_alat_kerja WHERE id_alat = '$id_alat'");
    $data_stok = mysqli_fetch_assoc($cek_stok);

    if ($jumlah_pinjam > $data_stok['jumlah']) {
        echo "<script>alert('Gagal! Stok alat " . $data_stok['nama_alat'] . " tidak mencukupi. Sisa stok: " . $data_stok['jumlah'] . "'); window.location='peminjaman_alat.php';</script>";
        exit();
    }

    $query_insert = "INSERT INTO tbl_peminjaman_alat (id_pengerjaan, id_alat, jumlah_pinjam, waktu_pinjam, status) 
                     VALUES ('$id_pengerjaan', '$id_alat', '$jumlah_pinjam', '$waktu_pinjam', '$status')";
    
    if (mysqli_query($koneksi, $query_insert)) {
        mysqli_query($koneksi, "UPDATE tbl_alat_kerja SET jumlah = jumlah - $jumlah_pinjam WHERE id_alat = '$id_alat'");
        echo "<script>alert('Peminjaman alat berhasil dicatat!'); window.location='peminjaman_alat.php';</script>";
    } else {
        echo "<script>alert('Gagal melakukan peminjaman: " . mysqli_error($koneksi) . "');</script>";
    }
}

// 3. PROSES PENGEMBALIAN ALAT (Via POST - Dengan Pilihan Kondisi)
if (isset($_POST['kembalikan_alat'])) {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_POST['id_peminjaman']);
    $kondisi_kembali = mysqli_real_escape_string($koneksi, $_POST['kondisi_kembali']);
    $waktu_kembali = date('Y-m-d H:i:s');

    $cek_kembali = mysqli_query($koneksi, "SELECT id_alat, jumlah_pinjam FROM tbl_peminjaman_alat WHERE id_peminjaman = '$id_peminjaman'");
    $data_k = mysqli_fetch_assoc($cek_kembali);
    $id_alat = $data_k['id_alat'];
    $jumlah_pinjam = $data_k['jumlah_pinjam'];

    $query_kembali = "UPDATE tbl_peminjaman_alat 
                      SET waktu_kembali = '$waktu_kembali', 
                          status = 'dikembalikan', 
                          kondisi_kembali = '$kondisi_kembali' 
                      WHERE id_peminjaman = '$id_peminjaman'";

    if (mysqli_query($koneksi, $query_kembali)) {
        if ($kondisi_kembali == 'baik') {
            mysqli_query($koneksi, "UPDATE tbl_alat_kerja SET jumlah = jumlah + $jumlah_pinjam WHERE id_alat = '$id_alat'");
        }
        echo "<script>alert('Alat berhasil dikembalikan dengan kondisi: " . ucfirst($kondisi_kembali) . "!'); window.location='peminjaman_alat.php';</script>";
    } else {
        echo "<script>alert('Gagal memproses pengembalian: " . mysqli_error($koneksi) . "');</script>";
    }
}

// 3b. PROSES PENGEMBALIAN ALAT (Via GET - Fallback Default Baik)
if (isset($_GET['aksi']) && $_GET['aksi'] == 'kembali') {
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_GET['id']);
    $waktu_kembali = date('Y-m-d H:i:s');

    $cek_kembali = mysqli_query($koneksi, "SELECT id_alat, jumlah_pinjam FROM tbl_peminjaman_alat WHERE id_peminjaman = '$id_peminjaman'");
    $data_k = mysqli_fetch_assoc($cek_kembali);
    $id_alat = $data_k['id_alat'];
    $jumlah_pinjam = $data_k['jumlah_pinjam'];

    $query_kembali = "UPDATE tbl_peminjaman_alat SET waktu_kembali = '$waktu_kembali', status = 'dikembalikan', kondisi_kembali = 'baik' WHERE id_peminjaman = '$id_peminjaman'";

    if (mysqli_query($koneksi, $query_kembali)) {
        mysqli_query($koneksi, "UPDATE tbl_alat_kerja SET jumlah = jumlah + $jumlah_pinjam WHERE id_alat = '$id_alat'");
        echo "<script>alert('Alat berhasil dikembalikan dan stok diperbarui!'); window.location='peminjaman_alat.php';</script>";
    } else {
        echo "<script>alert('Gagal memproses pengembalian!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Alat - SIBEO</title>
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

        /* Komponen Tabel & Modal */
        .table-premium { background: white; border-radius: 20px; box-shadow: 0 4px 18px rgba(148, 163, 184, 0.06); padding: 24px; }
        .table-premium thead th { background-color: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 14px 16px; border-bottom: none; }
        .table-premium tbody td { padding: 16px 16px; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 13.5px; }
        
        .btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none; font-weight: 600; transition: all 0.2s ease; }
        .btn-primary:hover { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
        
        .modal-content { border: none; border-radius: 14px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15); }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #cbd5e1; padding: 9px 12px; font-size: 13.5px; color: #1e293b; }
        .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12); }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-canvas">
        <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold m-0 text-dark" style="letter-spacing: -0.5px;">Log Peminjaman Alat Kerja</h3>
                <p class="text-muted small m-0 mt-1">Daftar perkakas bengkel yang digunakan dalam pengerjaan servis.</p>
            </div>
            <button class="btn btn-primary px-3 py-2 d-flex align-items-center gap-2 shadow-sm text-uppercase" style="border-radius: 10px; font-size: 12.5px; letter-spacing: 0.3px;" data-bs-toggle="modal" data-bs-target="#modalPinjam">
                <i class="fa-solid fa-plus fs-6"></i> Pinjam Alat Baru
            </button>
        </div>

        <div class="table-premium">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID PINJAM</th>
                            <th>KODE BOOKING</th>
                            <th>NAMA ALAT</th>
                            <th class="text-center">QTY</th>
                            <th>WAKTU PINJAM</th>
                            <th>WAKTU KEMBALI</th>
                            <th>STATUS</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $query_pinjam = mysqli_query($koneksi, "SELECT p.*, a.nama_alat, b.kode_booking 
                                                            FROM tbl_peminjaman_alat p
                                                            JOIN tbl_alat_kerja a ON p.id_alat = a.id_alat
                                                            JOIN tbl_pengerjaan pg ON p.id_pengerjaan = pg.id_pengerjaan
                                                            JOIN tbl_booking b ON pg.id_booking = b.id_booking
                                                            WHERE pg.id_mekanik = '$id_mekanik'
                                                            ORDER BY p.id_peminjaman DESC");

                    if (!$query_pinjam || mysqli_num_rows($query_pinjam) == 0) {
                        echo "<tr><td colspan='8' class='text-center text-muted py-4 small'>Belum ada riwayat peminjaman alat kerja.</td></tr>";
                    } else {
                        while ($r = mysqli_fetch_assoc($query_pinjam)) {
                            $status = $r['status'];
                            $badge = ($status == "dipinjam") ? "bg-warning text-warning" : "bg-success text-success";
                            ?>
                            <tr>
                                <td class="text-secondary fw-bold">#PA-<?= $r['id_peminjaman']; ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($r['kode_booking']); ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($r['nama_alat']); ?></td>
                                <td class="text-center fw-bold text-dark"><?= $r['jumlah_pinjam']; ?></td>
                                <td style="font-size: 13px;"><?= $r['waktu_pinjam']; ?></td>
                                <td style="font-size: 13px;"><?= (!empty($r['waktu_kembali']) && $r['waktu_kembali'] != '0000-00-00 00:00:00') ? $r['waktu_kembali'] : '<span class="text-muted">-</span>'; ?></td>
                                <td>
                                    <span class="badge bg-opacity-10 <?= $badge; ?> px-2.5 py-1.5 rounded text-capitalize" style="font-size: 11.5px; font-weight: 600;"><?= $status; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($status == 'dipinjam'): ?>
                                        <button class="btn btn-sm btn-outline-success px-3 rounded-pill btn-kembali-trigger" 
                                                style="font-size:12px;" 
                                                data-id="<?= $r['id_peminjaman']; ?>"
                                                data-nama="<?= htmlspecialchars($r['nama_alat']); ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalKembali">
                                            <i class="fa-solid fa-arrow-rotate-left me-1"></i> Kembali
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-light px-3 rounded-pill text-muted" style="font-size:12px;" disabled><i class="fa-solid fa-circle-check"></i> Selesai</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
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

<!-- Modal Pinjam Alat -->
<div class="modal fade" id="modalPinjam" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 380px;">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                    <h6 class="modal-title fw-bold text-dark m-0" style="font-size: 15px;">Formulir Pinjam Alat</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 10px; letter-spacing: 0.3px;">Untuk Tugas Pengerjaan</label>
                        <select class="form-select text-muted" name="id_booking" required style="font-size: 13px;">
                            <option value="">-- Pilih Kode Booking --</option>
                            <?php
                            $tugas_booking = mysqli_query($koneksi, "SELECT id_booking, kode_booking FROM tbl_booking WHERE id_mekanik = '$id_mekanik' AND status != 'Selesai'");
                            while($b = mysqli_fetch_assoc($tugas_booking)){
                                echo "<option value='".$b['id_booking']."'>".$b['kode_booking']."</option>";
                            }
                            if(mysqli_num_rows($tugas_booking) == 0){
                                echo "<option value='' disabled>Tidak ada booking aktif</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 10px; letter-spacing: 0.3px;">Pilih Alat Bengkel</label>
                        <select class="form-select text-muted" name="id_alat" required style="font-size: 13px;">
                            <option value="">-- Pilih Perkakas/Tools --</option>
                            <?php
                            $alat_master = mysqli_query($koneksi, "SELECT id_alat, nama_alat, jumlah FROM tbl_alat_kerja WHERE kondisi = 'baik' AND jumlah > 0 ORDER BY nama_alat ASC");
                            while($a = mysqli_fetch_assoc($alat_master)){
                                echo "<option value='".$a['id_alat']."'>".htmlspecialchars($a['nama_alat'])." (Stok: ".$a['jumlah'].")</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 10px; letter-spacing: 0.3px;">Jumlah Pinjam (QTY)</label>
                        <input type="number" class="form-control" name="jumlah_pinjam" min="1" value="1" required style="font-size: 13px;">
                    </div>

                </div>
                <div class="modal-footer py-3 px-4" style="border-top: 1px solid #f1f5f9; gap: 8px;">
                    <button type="button" class="btn btn-light text-secondary" data-bs-dismiss="modal" style="font-size: 12px; padding: 7px 14px; border-radius: 6px; border: 1px solid #e2e8f0; font-weight: 600;">Batal</button>
                    <button type="submit" name="pinjam_alat" class="btn btn-primary text-uppercase" style="font-size: 12px; padding: 7px 16px; border-radius: 6px; font-weight: 700; letter-spacing: 0.3px;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Kembali Alat -->
<div class="modal fade" id="modalKembali" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 380px;">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header py-3 px-4" style="border-bottom: 1px solid #f1f5f9;">
                    <h6 class="modal-title fw-bold text-dark m-0" style="font-size: 15px;">Pengembalian Alat</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id_peminjaman" id="kembali_id_peminjaman">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 10px; letter-spacing: 0.3px;">Nama Alat</label>
                        <input type="text" class="form-control bg-light" id="kembali_nama_alat" readonly style="font-size: 13px;">
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold text-secondary text-uppercase mb-1" style="font-size: 10px; letter-spacing: 0.3px;">Kondisi Pengembalian</label>
                        <select class="form-select" name="kondisi_kembali" required style="font-size: 13px;">
                            <option value="baik">Baik (Normal)</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer py-3 px-4" style="border-top: 1px solid #f1f5f9; gap: 8px;">
                    <button type="button" class="btn btn-light text-secondary" data-bs-dismiss="modal" style="font-size: 12px; padding: 7px 14px; border-radius: 6px; border: 1px solid #e2e8f0; font-weight: 600;">Batal</button>
                    <button type="submit" name="kembalikan_alat" class="btn btn-success text-uppercase text-white" style="font-size: 12px; padding: 7px 16px; border-radius: 6px; font-weight: 700; letter-spacing: 0.3px;">Kembalikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnKembaliTriggers = document.querySelectorAll('.btn-kembali-trigger');
    btnKembaliTriggers.forEach(btn => {
        btn.addEventListener('click', function() {
            const idPeminjaman = this.getAttribute('data-id');
            const namaAlat = this.getAttribute('data-nama');
            document.getElementById('kembali_id_peminjaman').value = idPeminjaman;
            document.getElementById('kembali_nama_alat').value = namaAlat;
        });
    });
});
</script>
</body>
</html>