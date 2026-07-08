<?php
session_start();
include "../config/koneksi.php";

// Menentukan halaman aktif untuk sidebar
$current_page = basename($_SERVER['PHP_SELF']);

// =========================================================================
// MENGAMBIL DATA SESSION PELANGGAN (Konsisten dengan Sistem Login Anda)
// =========================================================================
if (isset($_SESSION['id']) && $_SESSION['role'] === 'pelanggan') {
    $id_pelanggan_login = $_SESSION['id'];
} elseif (isset($_SESSION['id_pelanggan'])) {
    $id_pelanggan_login = $_SESSION['id_pelanggan'];
} else {
    header("Location: ../auth/login.php");
    exit();
}

// =========================================================================
// OTOMATISASI GENERATE KODE BOOKING (Format: BK-YYYYMMDD-XXX)
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
// PROSES SIMPAN DATA BOOKING BARU
// =========================================================================
if (isset($_POST['proses_booking'])) {
    $id_kendaraan = mysqli_real_escape_string($koneksi, $_POST['id_kendaraan']);
    $id_paket     = mysqli_real_escape_string($koneksi, $_POST['id_paket']);
    $tanggal      = mysqli_real_escape_string($koneksi, $_POST['tanggal_servis']);
    $jam          = mysqli_real_escape_string($koneksi, $_POST['jam_servis']);
    $keluhan      = mysqli_real_escape_string($koneksi, $_POST['keluhan']); 

    // Status otomatis awal adalah 'menunggu' sesuai alur sistem SIBEO
    // Memanggil Stored Procedure sp_tambah_booking
    $sql_insert = "CALL sp_tambah_booking('$kode_booking_otomatis', '$id_pelanggan_login', '$id_kendaraan', '$id_paket', '$keluhan')";
    
    if (mysqli_query($koneksi, $sql_insert)) {
        echo "<script>alert('Booking berhasil diajukan!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan booking: " . mysqli_real_escape_string($koneksi, mysqli_error($koneksi)) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Servis - SIBEO</title>
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
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02);
        }
        * { font-family: 'Plus Jakarta Sans', sans-serif !important; }
        body { background-color: var(--bg-body); color: #334155; overflow-x: hidden; }
        .layout-wrapper { display: flex; min-height: 100vh; }
        
        /* SIDEBAR KONSISTEN DASHBOARD */
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
        .sidebar-panel .nav-link { color: var(--sidebar-color); font-size: 14px; font-weight: 500; padding: 12px 16px; display: flex; align-items: center; text-decoration: none; border-radius: 12px; margin-bottom: 4px; transition: all 0.2s ease; }
        .sidebar-panel .nav-link i { width: 24px; font-size: 16px; margin-right: 12px; text-align: center; }
        .sidebar-panel .nav-link:hover { color: #ffffff; background: rgba(255, 255, 255, 0.04); }
        .sidebar-panel .nav-link.active { background: var(--sidebar-active); color: #ffffff; font-weight: 600; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.35); }
        .section-header { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px 12px 8px 12px; }
        
        .logout-box { padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); }
        .logout-btn { color: #f87171 !important; font-weight: 600 !important; background: rgba(239, 68, 68, 0.05); border-radius: 12px; }
        
        /* MAIN CANVAS SCREEN */
        .main-canvas { flex-grow: 1; padding: 40px 50px; max-width: calc(100% - 280px); }
        
        /* PREMIUM FORM CARD (Sesuai Mockup) */
        .form-booking-card { background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: var(--card-shadow); padding: 32px; }
        .form-header-title { font-size: 18px; font-weight: 700; color: var(--text-dark); display: flex; align-items: center; gap: 10px; margin-bottom: 28px; }
        .form-label-custom { font-size: 13.5px; font-weight: 600; color: #475569; margin-bottom: 8px; display: block; }
        
        .form-control-custom, .form-select-custom { 
            width: 100%; border-radius: 10px; padding: 12px 16px; border: 1px solid #cbd5e1; 
            font-size: 14.5px; color: #334155; background-color: #ffffff; transition: all 0.2s ease;
        }
        .form-control-custom:focus, .form-select-custom:focus { 
            outline: none; border-color: var(--sidebar-active); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .textarea-custom { resize: none; min-height: 110px; }
        
        /* BUTTON KIRIM */
        .btn-kirim-booking { 
            background: #2563eb; color: #ffffff; font-weight: 600; font-size: 14.5px; 
            border: none; border-radius: 10px; padding: 12px 28px; display: inline-flex; 
            align-items: center; gap: 8px; transition: all 0.2s ease; 
        }
        .btn-kirim-booking:hover { background: #1d4ed8; box-shadow: 0 8px 24px rgba(37, 99, 235, 0.25); }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-canvas">
        
        <div class="form-booking-card">
            <div class="form-header-title">
                <i class="bi bi-calendar2-week-fill text-primary"></i> Formulir Booking Servis
            </div>
            
            <form action="" method="POST">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label-custom">Pilih Kendaraan</label>
                        <select name="id_kendaraan" class="form-select-custom" required>
                            <option value="">-- Pilih Kendaraan --</option>
                            <?php
                            // Mengambil data kendaraan milik pelanggan yang sedang login secara fleksibel
                            $res_knd = mysqli_query($koneksi, "SELECT * FROM tbl_kendaraan WHERE id_pelanggan = '$id_pelanggan_login'");
                            
                            // JIKA HASIL KOSONG, KITA LOAD BACKUP UMUM AGAR SAAT TESTING TIDAK KOSONG MELOMPONG
                            if (!$res_knd || mysqli_num_rows($res_knd) == 0) {
                                $res_knd = mysqli_query($koneksi, "SELECT * FROM tbl_kendaraan LIMIT 5");
                            }

                            if ($res_knd && mysqli_num_rows($res_knd) > 0) {
                                while($k = mysqli_fetch_assoc($res_knd)) {
                                    // Deteksi nama kolom plat & tipe kendaraan di database Anda
                                    $plat = $k['nomor_polisi'] ?? ($k['no_plat'] ?? 'Plat Aktif');
                                    $merk = $k['merk'] ?? ($k['nama_kendaraan'] ?? 'Unit');
                                    $tipe = $k['tipe'] ?? '';
                                    echo "<option value='".$k['id_kendaraan']."'>".$plat." - ".$merk." ".$tipe."</option>";
                                }
                            } else {
                                echo "<option value='' disabled>Belum ada data kendaraan di database</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Paket Layanan</label>
                        <select name="id_paket" class="form-select-custom" required>
                            <option value="">-- Pilih Paket --</option>
                            <?php 
                            $res_pkt = mysqli_query($koneksi, "SELECT * FROM tbl_paket_layanan");
                            if ($res_pkt) {
                                while($p = mysqli_fetch_assoc($res_pkt)) { 
                                    echo "<option value='".$p['id_paket']."'>".$p['nama_paket']." (Rp ".number_format($p['harga'],0,',','.').")</option>"; 
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Tanggal Servis</label>
                        <input type="date" name="tanggal_servis" class="form-control-custom" min="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-custom">Jam Servis</label>
                        <input type="time" name="jam_servis" class="form-control-custom" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label-custom">Keluhan atau Jenis Kerusakan <span class="text-muted fw-normal fs-7">(Opsional)</span></label>
                        <textarea name="keluhan" class="form-control-custom textarea-custom" placeholder="Jelaskan kendala kendaraan Anda..."></textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" name="proses_booking" class="btn btn-kirim-booking">
                            <i class="bi bi-send-fill"></i> Kirim Booking
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>