<?php
session_start();
include "../config/koneksi.php";

// 1. KEAMANAN AKSES
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit();
}

$id_pelanggan = $_SESSION['id'];
$pesan = "";

// 2. PROSES DAFTAR BOOKING SERVIS
if (isset($_POST['buat_booking'])) {
    $id_kendaraan   = mysqli_real_escape_string($koneksi, $_POST['id_kendaraan']);
    $id_paket       = mysqli_real_escape_string($koneksi, $_POST['id_paket']); 
    $tanggal_servis = mysqli_real_escape_string($koneksi, $_POST['tanggal_servis']);
    $jam_servis     = mysqli_real_escape_string($koneksi, $_POST['jam_servis']);
    $keluhan        = mysqli_real_escape_string($koneksi, $_POST['keluhan']);
    
    // Membuat Kode Antrean Otomatis
    $hari_ini   = date("Ymd");
    $cek_nomor  = mysqli_query($koneksi, "SELECT kode_booking FROM tbl_booking WHERE kode_booking LIKE 'BK-$hari_ini-%' ORDER BY id_booking DESC LIMIT 1");
    
    if (mysqli_num_rows($cek_nomor) > 0) {
        $data_nomor = mysqli_fetch_assoc($cek_nomor);
        $nomor_terakhir = substr($data_nomor['kode_booking'], -3);
        $nomor_urut = str_pad((int)$nomor_terakhir + 1, 3, "0", STR_PAD_LEFT);
    } else {
        $nomor_urut = "001";
    }
    $kode_booking = "BK-" . $hari_ini . "-" . $nomor_urut;

    // Menyimpan data ke database termasuk id_paket yang sebelumnya error
    $simpan = mysqli_query($koneksi, "INSERT INTO tbl_booking (kode_booking, id_pelanggan, id_kendaraan, id_paket, tanggal_servis, jam_servis, keluhan, status) 
                                      VALUES ('$kode_booking', '$id_pelanggan', '$id_kendaraan', '$id_paket', '$tanggal_servis', '$jam_servis', '$keluhan', 'Menunggu Antrean')");

    if ($simpan) {
        $pesan = "<div class='alert alert-success small rounded-3 alert-dismissible fade show' role='alert'>
                    <i class='fa-solid fa-circle-check me-2'></i>Booking berhasil dibuat! Kode Antrean Anda: <strong>$kode_booking</strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
    } else {
        $pesan = "<div class='alert alert-danger small rounded-3 alert-dismissible fade show' role='alert'>
                    <i class='fa-solid fa-circle-xmark me-2'></i>Gagal membuat jadwal booking. Silakan coba lagi.
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .sidebar { background: #0f172a; height: 100vh; position: fixed; top: 0; left: 0; bottom: 0; z-index: 999; box-shadow: 4px 0 24px rgba(15, 23, 42, 0.15); display: flex; flex-direction: column; justify-content: space-between; }
        .sidebar-brand-wrapper { padding: 30px 24px 20px 24px; }
        .sidebar-brand { font-size: 24px; font-weight: 800; letter-spacing: 1.5px; background: linear-gradient(45deg, #38bdf8, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .sidebar-subtitle { font-size: 10px; font-weight: 600; letter-spacing: 1px; color: #475569; margin-top: 4px; }
        .nav-section-title { font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 1px; padding: 20px 24px 10px 24px; }
        .sidebar .nav-link { color: #94a3b8; font-size: 14px; font-weight: 500; padding: 14px 24px; display: flex; align-items: center; transition: all 0.2s ease; border-left: 4px solid transparent; }
        .sidebar .nav-link i { font-size: 16px; width: 28px; }
        .sidebar .nav-link:hover { color: #38bdf8; background: rgba(56, 189, 248, 0.04); }
        .sidebar .nav-link.active { background: rgba(59, 130, 246, 0.08); color: #3b82f6; font-weight: 600; border-left-color: #3b82f6; }
        
        .main-wrapper { margin-left: 16.666667%; padding: 40px; }
        .card-premium { background: white; border: none; border-radius: 24px; box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08); padding: 30px; }
        .form-control, .form-select { border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; font-size: 14px; color: #334155; }
        .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .btn-premium { background: #3b82f6; color: white; border-radius: 12px; font-weight: 600; font-size: 14px; padding: 12px 24px; border: none; transition: all 0.2s; }
        .btn-premium:hover { background: #2563eb; color: white; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <div class="col-md-3 col-lg-2 sidebar">
            <div>
                <div class="sidebar-brand-wrapper text-start ps-4">
                    <div class="sidebar-brand">SIBEO</div>
                    <div class="sidebar-subtitle">CUSTOMER SYSTEM</div>
                </div>
                <div class="nav-section-title">MENU UTAMA</div>
                <div class="nav flex-column">
                    <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-simple me-3"></i>Dashboard</a>
                    <a href="booking.php" class="nav-link active"><i class="fa-solid fa-calendar-check me-3"></i>Booking Servis</a>
                    <a href="kendaraan.php" class="nav-link"><i class="fa-solid fa-car me-3"></i>Kendaraan Saya</a>
                    <a href="riwayat_servis.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left me-3"></i>Riwayat Servis</a>
                </div>
            </div>
            <div class="mb-4 border-top border-secondary border-opacity-10 pt-2">
                <div class="nav flex-column">
                    <a href="../auth/logout.php" class="nav-link text-danger" onclick="return confirm('Keluar dari sistem pelanggan?')"><i class="fa-solid fa-sign-out-alt me-3"></i>Keluar</a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 main-wrapper">
            
            <div class="mb-4">
                <h4 class="fw-bold m-0" style="color: #0f172a;">Reservasi Booking Servis</h4>
                <p class="text-muted small m-0 mt-1">Hindari antrean panjang di bengkel kampus dengan menjadwalkan waktu perawatan kendaraan Anda secara mandiri.</p>
            </div>

            <?= $pesan; ?>

            <div class="row">
                <div class="col-xl-8 col-lg-10">
                    <div class="card-premium">
                        <h6 class="fw-bold mb-4" style="color: #0f172a;"><i class="fa-solid fa-file-invoice text-primary me-2"></i>Form Formulir Pendaftaran</h6>
                        
                        <form action="" method="POST">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Kendaraan Anda</label>
                                    <select name="id_kendaraan" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Kendaraan --</option>
                                        <?php
                                        $ambil_kendaraan = mysqli_query($koneksi, "SELECT id_kendaraan, merk, nomor_polisi FROM tbl_kendaraan WHERE id_pelanggan = '$id_pelanggan'");
                                        if (mysqli_num_rows($ambil_kendaraan) == 0) {
                                            echo "<option value='' disabled>Belum ada kendaraan terdaftar.</option>";
                                        } else {
                                            while ($k = mysqli_fetch_assoc($ambil_kendaraan)) {
                                                echo "<option value='".$k['id_kendaraan']."'>".$k['merk']." (".$k['nomor_polisi'].")</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Paket Layanan</label>
                                    <select name="id_paket" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Paket --</option>
                                        <?php
                                        $ambil_paket = mysqli_query($koneksi, "SELECT id_paket, nama_paket FROM tbl_paket_layanan");
                                        if (mysqli_num_rows($ambil_paket) > 0) {
                                            while ($p = mysqli_fetch_assoc($ambil_paket)) {
                                                echo "<option value='".$p['id_paket']."'>".$p['nama_paket']."</option>";
                                            }
                                        } else {
                                            echo "<option value='' disabled>Data paket kosong</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Rencana Tanggal Kedatangan</label>
                                    <input type="date" name="tanggal_servis" class="form-control" min="<?= date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Pilihan Jam / Sesi</label>
                                    <select name="jam_servis" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Jam --</option>
                                        <option value="07:00">07:00 - 11:00 WIB (Sesi Pagi I)</option>
                                        <option value="13:00">13:00 - 16:00 WIB (Sesi Siang)</option>
                                        <option value="19:00">19:00 - 21:00 WIB (Sesi Malam)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary">Keluhan atau Jenis Kerusakan</label>
                                <textarea name="keluhan" class="form-control" rows="4" placeholder="Jelaskan secara rinci kendala pada kendaraan Anda (Contoh: Ganti oli rutin, Rem depan berbunyi, atau Mesin brebet saat digas)"></textarea>
                            </div>

                            <div class="d-flex justify-content-end border-top border-light pt-3">
                                <button type="submit" name="buat_booking" class="btn btn-premium px-4 py-2.5">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Kirim Jadwal Booking
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const alertElement = document.querySelector('.alert');
        if (alertElement) {
            setTimeout(function() {
                const bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();
            }, 3000); 
        }
    });
</script>
</body>
</html>