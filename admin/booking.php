<?php
session_start();
include "../config/koneksi.php";

$current_page = basename($_SERVER['PHP_SELF']);

// =========================================================================
// OTOMATISASI GENERATE KODE BOOKING
// =========================================================================
$today_format = "BK-" . date('Ymd') . "-";
$query_auto = mysqli_query($koneksi, "SELECT kode_booking FROM tbl_booking WHERE kode_booking LIKE '$today_format%' ORDER BY id_booking DESC LIMIT 1");
if ($query_auto && mysqli_num_rows($query_auto) > 0) {
    $data_auto = mysqli_fetch_assoc($query_auto);
    $last_kode = $data_auto['kode_booking'];
    $clean_num = (int)substr($last_kode, -3);
    $next_num  = $clean_num + 1;
    $kode_booking_otomatis = $today_format . sprintf("%03d", $next_num);
} else {
    $kode_booking_otomatis = $today_format . "001";
}

// =========================================================================
// PROSES OPERASIONAL WORKSHOP (SISI ADMIN)
// =========================================================================

// TAHAP 1: INPUT ANTREAN AWAL (STATUS: menunggu)
if (isset($_POST['action_tambah'])) {
    $kode_booking = mysqli_real_escape_string($koneksi, $_POST['kode_booking']);
    $id_pelanggan = mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']);
    $id_kendaraan = mysqli_real_escape_string($koneksi, $_POST['id_kendaraan']);
    $id_paket     = mysqli_real_escape_string($koneksi, $_POST['id_paket']);
    $keluhan      = mysqli_real_escape_string($koneksi, $_POST['keluhan']);
    
    // Memanggil Stored Procedure sp_tambah_booking
    $sql_sp = "CALL sp_tambah_booking('$kode_booking', '$id_pelanggan', '$id_kendaraan', '$id_paket', '$keluhan')";
    
    if (mysqli_query($koneksi, $sql_sp)) {
        echo "<script>alert('Sukses! Antrean masuk dengan status: Menunggu.'); window.location='booking.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_real_escape_string($koneksi, mysqli_error($koneksi)) . "'); window.location='booking.php';</script>";
    }
}

// TAHAP 2: ADMIN PLOT MEKANIK & STALL (STATUS BERUBAH: terkonfirmasi)
if (isset($_POST['action_konfirmasi'])) {
    $id_booking = mysqli_real_escape_string($koneksi, $_POST['id_booking']);
    $id_stall   = mysqli_real_escape_string($koneksi, $_POST['id_stall']);
    $id_mekanik = mysqli_real_escape_string($koneksi, $_POST['id_mekanik']);
    
    // Memanggil Stored Procedure sp_penugasan_servis
    $query_konf = mysqli_query($koneksi, "CALL sp_penugasan_servis('$id_booking', '$id_mekanik', '$id_stall')");
    
    if ($query_konf) {
        echo "<script>alert('Sukses! Status berubah menjadi Terkonfirmasi. Pengerjaan telah dimulai untuk Mekanik.'); window.location='booking.php';</script>";
    } else {
        echo "<script>alert('Gagal melakukan konfirmasi: " . mysqli_real_escape_string($koneksi, mysqli_error($koneksi)) . "'); window.location='booking.php';</script>";
    }
}

// TAHAP 4: ARCHIVE SETELAH MEKANIK SELESAI
if (isset($_GET['arsip_selesai'])) {
    $id_booking = mysqli_real_escape_string($koneksi, $_GET['arsip_selesai']);
    echo "<script>alert('Transaksi berhasil diverifikasi dan diarsipkan ke laporan.'); window.location='booking.php';</script>";
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM tbl_booking WHERE id_booking='$id_hapus'");
    echo "<script>window.location='booking.php';</script>";
}

function safe_text($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$kolom_mekanik = "nama"; 
$check_col = mysqli_query($koneksi, "SHOW COLUMNS FROM tbl_mekanik LIKE 'nama_lengkap'");
if ($check_col && mysqli_num_rows($check_col) > 0) { $kolom_mekanik = "nama_lengkap"; }

// AMBIL DATA KENDARAAN UNTUK JAVASCRIPT FILTERING
$query_kendaraan = "SELECT id_kendaraan, id_pelanggan, nomor_polisi, merk, tipe FROM tbl_kendaraan";
$res_kendaraan = mysqli_query($koneksi, $query_kendaraan);
$arr_kendaraan = [];
if ($res_kendaraan) {
    while ($rk = mysqli_fetch_assoc($res_kendaraan)) {
        $arr_kendaraan[] = $rk;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Booking - SIBEO</title>
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
        
        /* SIDEBAR COMPONENT */
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

        .section-header { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px 12px 8px 12px; }
        .sidebar-panel .nav-link { color: var(--sidebar-color); font-size: 14px; font-weight: 500; padding: 12px 16px; display: flex; align-items: center; text-decoration: none; border-radius: 12px; margin-bottom: 4px; transition: all 0.2s ease; }
        .sidebar-panel .nav-link i { width: 24px; font-size: 16px; margin-right: 12px; text-align: center; }
        .sidebar-panel .nav-link:hover { color: #ffffff; background: rgba(255, 255, 255, 0.04); }
        .sidebar-panel .nav-link.active { background: var(--sidebar-active); color: #ffffff; font-weight: 600; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.35); }

        .logout-box { padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); flex-shrink: 0; }
        .logout-btn { color: #f87171 !important; font-weight: 600 !important; background: rgba(239, 68, 68, 0.05); }
        .logout-btn:hover { background: #ef4444 !important; color: #ffffff !important; }
        
        /* MAIN CANVAS SCREEN */
        .main-canvas { flex-grow: 1; padding: 40px 50px; max-width: calc(100% - 280px); }
        .data-card-premium { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: var(--card-shadow); overflow: hidden; }
        .data-card-header { padding: 24px; background: #ffffff; border-bottom: 1px solid #f1f5f9; }
        .data-card-title { font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0; }
        
        .table-premium thead th { background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; }
        .table-premium tbody td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }

        .btn-premium-primary { background-color: var(--sidebar-active); color: #ffffff; border: none; border-radius: 10px; padding: 10px 20px; font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: all 0.2s ease; }
        .btn-premium-primary:hover { background-color: #2563eb; color: #ffffff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2); }
        .form-control, .form-select { border-radius: 10px; padding: 10.5px 14px; border: 1px solid #cbd5e1; font-size: 14px; }
        .form-control:focus, .form-select:focus { border-color: var(--sidebar-active); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-canvas">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Kendali Alur Kerja Workshop</h3>
                <p class="text-muted small m-0 mt-1">Sinkronisasi status pengerjaan antara Admin dan Mekanik Lapangan.</p>
            </div>
            <button type="button" class="btn btn-premium-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle-fill"></i>Registrasi Antrean Baru
            </button>
        </div>

        <div class="data-card-premium">
            <div class="data-card-header">
                <h5 class="data-card-title"><i class="bi bi-hdd-network text-primary me-2"></i>Log Validasi Status Kendaraan</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-premium align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Kode Booking</th>
                            <th>Pelanggan</th>
                            <th>Paket Layanan</th>
                            <th>Mekanik & Stall</th>
                            <th>Total Paket</th>
                            <th>Status Kerja</th>
                            <th class="text-center">Aksi Kendali Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query_booking = mysqli_query($koneksi, "
                            SELECT b.*, p.nama_lengkap, pk.nama_paket, pk.harga AS harga_paket, 
                                   IFNULL(m.$kolom_mekanik, '-') AS nama_mekanik, 
                                   IFNULL(s.nomor_stall, '-') AS nomor_stall,
                                   COALESCE(pk.harga, 0) AS total_akhir
                            FROM tbl_booking b
                            LEFT JOIN tbl_pelanggan p ON b.id_pelanggan = p.id_pelanggan
                            LEFT JOIN tbl_paket_layanan pk ON b.id_paket = pk.id_paket
                            LEFT JOIN tbl_mekanik m ON b.id_mekanik = m.id_mekanik
                            LEFT JOIN tbl_stall s ON b.id_stall = s.id_stall
                            ORDER BY b.id_booking DESC
                        ");

                        if ($query_booking && mysqli_num_rows($query_booking) > 0) {
                            while ($data = mysqli_fetch_assoc($query_booking)) {
                                $st = strtolower($data['status']);
                                ?>
                                <tr>
                                    <td class="text-center text-secondary fw-semibold"><?= $no++; ?></td>
                                    <td><span class="badge bg-light text-dark border px-2 py-1 fw-bold"><?= safe_text($data['kode_booking']); ?></span></td>
                                    <td><strong class="text-dark"><?= safe_text($data['nama_lengkap']); ?></strong></td>
                                    <td><?= safe_text($data['nama_paket']); ?></td>
                                    <td>
                                        <small class="d-block text-secondary"><i class="bi bi-person me-1"></i><?= safe_text($data['nama_mekanik']); ?></small>
                                        <span class="text-primary small fw-bold"><i class="bi bi-geo-alt me-1"></i>Stall: <?= safe_text($data['nomor_stall']); ?></span>
                                    </td>
                                    <td><strong class="text-dark">Rp <?= number_format($data['total_akhir'], 0, ',', '.'); ?></strong></td>
                                    <td>
                                        <?php 
                                        if($st == 'menunggu') {
                                            echo '<span class="badge bg-secondary px-2 py-1 text-uppercase small fw-bold">Menunggu</span>';
                                        } elseif($st == 'terkonfirmasi') {
                                            echo '<span class="badge bg-info text-dark px-2 py-1 text-uppercase small fw-bold">Terkonfirmasi</span>';
                                        } elseif($st == 'dalam_proses') {
                                            echo '<span class="badge bg-warning text-dark px-2 py-1 text-uppercase small fw-bold"><i class="bi bi-hourglass-split me-1"></i>Dalam Proses</span>';
                                        } else {
                                            echo '<span class="badge bg-success px-2 py-1 text-uppercase small fw-bold"><i class="bi bi-check-all me-1"></i>Selesai</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <?php if($st == 'menunggu'): ?>
                                                <button class="btn btn-sm btn-primary fw-bold px-2.5" data-bs-toggle="modal" data-bs-target="#modalKonf<?= $data['id_booking']; ?>" style="border-radius:6px; font-size:12px;">
                                                    <i class="bi bi-shield-check"></i> Konfirmasi & Plot
                                                </button>
                                                
                                            <?php elseif($st == 'terkonfirmasi'): ?>
                                                <button class="btn btn-sm btn-light text-muted px-2.5" style="border-radius:6px; font-size:12px;" disabled>
                                                    <i class="bi bi-clock"></i> Menunggu Mekanik
                                                </button>

                                            <?php elseif($st == 'dalam_proses'): ?>
                                                <button class="btn btn-sm btn-outline-warning fw-bold px-2.5" style="cursor: not-allowed; border-radius:6px; font-size:12px;" disabled>
                                                    <i class="bi bi-lock-fill"></i> Mekanik Sedang Kerja
                                                </button>

                                            <?php else: ?>
                                                <a href="booking.php?arsip_selesai=<?= $data['id_booking']; ?>" class="btn btn-sm btn-success fw-bold px-2.5" style="border-radius:6px; font-size:12px;">
                                                    <i class="bi bi-cloud-arrow-up-fill"></i> Diarsipkan
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="booking.php?hapus=<?= $data['id_booking']; ?>" class="btn btn-sm btn-light text-danger px-2" onclick="return confirm('Hapus data?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="modalKonf<?= $data['id_booking']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content" style="border-radius: 16px; border: none;">
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold text-dark">Plotting Alokasi Kerja: <?= $data['kode_booking']; ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="booking.php" method="POST">
                                                <input type="hidden" name="id_booking" value="<?= $data['id_booking']; ?>">
                                                <div class="modal-body row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label small fw-bold">Pilih Stall Kerja (Hanya yang Tersedia)</label>
                                                        <select name="id_stall" class="form-select" required>
                                                            <option value="">-- Pilih Stall --</option>
                                                            <?php 
                                                            $s_res = mysqli_query($koneksi, "SELECT id_stall, nomor_stall FROM tbl_stall WHERE LOWER(status)='tersedia'");
                                                            while($s = mysqli_fetch_assoc($s_res)) { echo "<option value='".$s['id_stall']."'>".$s['nomor_stall']."</option>"; }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small fw-bold">Tunjuk Mekanik Lapangan</label>
                                                        <select name="id_mekanik" class="form-select" required>
                                                             <option value="">-- Pilih Mekanik --</option>
                                                             <?php 
                                                             $m_res = mysqli_query($koneksi, "SELECT id_mekanik, $kolom_mekanik FROM tbl_mekanik WHERE id_mekanik NOT IN (SELECT id_mekanik FROM tbl_booking WHERE id_mekanik IS NOT NULL AND status IN ('terkonfirmasi', 'proses', 'dalam_proses')) AND id_mekanik NOT IN (SELECT id_mekanik FROM tbl_pengerjaan WHERE status != 'Selesai')");
                                                             while($m = mysqli_fetch_assoc($m_res)) { echo "<option value='".$m['id_mekanik']."'>".$m[$kolom_mekanik]."</option>"; }
                                                             ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="action_confirmasi" class="btn btn-primary w-100 fw-bold" style="border-radius: 10px;">Kirim Tugas ke Mekanik</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center text-muted py-5 small'><i class='bi bi-calendar-x d-block fs-2 mb-2 opacity-50'></i>Belum ada data transaksi booking.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php include '../includes/footer.php'; ?>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-calendar-plus text-primary me-2"></i>Registrasi Antrean Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="booking.php" method="POST">
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Kode Booking</label>
                        <input type="text" name="kode_booking" class="form-control bg-light fw-bold text-primary" value="<?= $kode_booking_otomatis; ?>" readonly>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Pilih Pelanggan</label>
                        <select id="id_pelanggan" name="id_pelanggan" class="form-select" onchange="filterKendaraan()" required>
                            <option value="">-- Pelanggan --</option>
                            <?php 
                            $res = mysqli_query($koneksi, "SELECT id_pelanggan, nama_lengkap FROM tbl_pelanggan");
                            while($c = mysqli_fetch_assoc($res)) { echo "<option value='".$c['id_pelanggan']."'>".$c['nama_lengkap']."</option>"; }
                            ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Pilih Kendaraan Pelanggan</label>
                        <select id="id_kendaraan" name="id_kendaraan" class="form-select" required>
                            <option value="">-- Pilih Pelanggan Dahulu --</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Pilih Paket Layanan</label>
                        <select name="id_paket" class="form-select" required>
                            <option value="">-- Paket --</option>
                            <?php 
                            $res = mysqli_query($koneksi, "SELECT id_paket, nama_paket, harga FROM tbl_paket_layanan");
                            while($p = mysqli_fetch_assoc($res)) { echo "<option value='".$p['id_paket']."'>".$p['nama_paket']." (Rp ".number_format($p['harga'],0,',','.').")</option>"; }
                            ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary">Keluhan</label>
                        <textarea name="keluhan" class="form-control" rows="2" placeholder="Masukkan keluhan mesin..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="action_tambah" class="btn btn-primary w-100 fw-bold" style="border-radius:10px;">Masuk Daftar Antrean (SP)</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const dataKendaraan = <?php echo json_encode($arr_kendaraan); ?>;

    function filterKendaraan() {
        const idPelangganSelected = document.getElementById('id_pelanggan').value;
        const selectKendaraan = document.getElementById('id_kendaraan');
        
        // Reset Dropdown Kendaraan ke default
        selectKendaraan.innerHTML = '<option value="">-- Pilih Kendaraan --</option>';
        
        // Saring kendaraan berdasarkan id_pelanggan pemiliknya
        const kendaraanTerfilter = dataKendaraan.filter(k => k.id_pelanggan == idPelangganSelected);
        
        if(kendaraanTerfilter.length > 0) {
            kendaraanTerfilter.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.id_kendaraan;
                opt.textContent = `${k.nomor_polisi} - ${k.merk} (${k.tipe})`;
                selectKendaraan.appendChild(opt);
            });
        } else {
            const opt = document.createElement('option');
            opt.value = "";
            opt.textContent = "⚠️ Pelanggan belum mendaftarkan kendaraan!";
            opt.disabled = true;
            selectKendaraan.appendChild(opt);
        }
    }
</script>
</body>
</html>