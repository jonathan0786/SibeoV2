<?php
session_start();
include "../config/koneksi.php";

// 1. KEAMANAN AKSES
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit();
}

$id_pelanggan = mysqli_real_escape_string($koneksi, $_SESSION['id']);
$pesan = "";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function ambil_kolom($data, $daftar_kolom, $default = '') {
    foreach ($daftar_kolom as $kolom) {
        if (isset($data[$kolom]) && trim((string)$data[$kolom]) !== '') {
            return $data[$kolom];
        }
    }
    return $default;
}

function format_rupiah($nilai) {
    if ($nilai === '' || $nilai === null) {
        return '';
    }

    return 'Rp ' . number_format((float)$nilai, 0, ',', '.');
}

// 2. AMBIL DATA KENDARAAN MILIK PELANGGAN LOGIN
$q_kendaraan = mysqli_query($koneksi, "
    SELECT * FROM tbl_kendaraan
    WHERE id_pelanggan = '$id_pelanggan'
    ORDER BY id_kendaraan DESC
");

// 3. AMBIL DATA PAKET LAYANAN
$q_paket = mysqli_query($koneksi, "
    SELECT * FROM tbl_paket_layanan
    ORDER BY id_paket ASC
");

// 4. PROSES DAFTAR BOOKING SERVIS
if (isset($_POST['buat_booking'])) {
    $id_kendaraan   = mysqli_real_escape_string($koneksi, $_POST['id_kendaraan'] ?? '');
    $id_paket       = mysqli_real_escape_string($koneksi, $_POST['id_paket'] ?? '');
    $tanggal_servis = mysqli_real_escape_string($koneksi, $_POST['tanggal_servis'] ?? '');
    $jam_servis     = mysqli_real_escape_string($koneksi, $_POST['jam_servis'] ?? '');
    $keluhan        = mysqli_real_escape_string($koneksi, $_POST['keluhan'] ?? '');

    if ($id_kendaraan === '' || $id_paket === '' || $tanggal_servis === '' || $jam_servis === '') {
        $pesan = "<div class='alert alert-warning small rounded-3 alert-dismissible fade show' role='alert'>
                    <i class='fa-solid fa-triangle-exclamation me-2'></i>Lengkapi kendaraan, paket layanan, tanggal, dan jam servis.
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
    } else {
        // Membuat kode antrean otomatis
        $hari_ini  = date("Ymd");
        $cek_nomor = mysqli_query($koneksi, "
            SELECT kode_booking
            FROM tbl_booking
            WHERE kode_booking LIKE 'BK-$hari_ini-%'
            ORDER BY id_booking DESC
            LIMIT 1
        ");

        if ($cek_nomor && mysqli_num_rows($cek_nomor) > 0) {
            $data_nomor = mysqli_fetch_assoc($cek_nomor);
            $nomor_terakhir = substr($data_nomor['kode_booking'], -3);
            $nomor_urut = str_pad((int)$nomor_terakhir + 1, 3, "0", STR_PAD_LEFT);
        } else {
            $nomor_urut = "001";
        }

        $kode_booking = "BK-" . $hari_ini . "-" . $nomor_urut;

        $simpan = mysqli_query($koneksi, "
            INSERT INTO tbl_booking
            (kode_booking, id_pelanggan, id_kendaraan, id_paket, tanggal_servis, jam_servis, keluhan, status)
            VALUES
            ('$kode_booking', '$id_pelanggan', '$id_kendaraan', '$id_paket', '$tanggal_servis', '$jam_servis', '$keluhan', 'Menunggu Antrean')
        ");

        if ($simpan) {
            $pesan = "<div class='alert alert-success small rounded-3 alert-dismissible fade show' role='alert'>
                        <i class='fa-solid fa-circle-check me-2'></i>Booking berhasil dibuat! Kode antrean Anda: <strong>" . e($kode_booking) . "</strong>
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                      </div>";
        } else {
            $pesan = "<div class='alert alert-danger small rounded-3 alert-dismissible fade show' role='alert'>
                        <i class='fa-solid fa-circle-xmark me-2'></i>Gagal membuat jadwal booking. Error: " . e(mysqli_error($koneksi)) . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                      </div>";
        }
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
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                            <h5 class="fw-bold text-dark"><i class="fa-solid fa-calendar-plus me-2 text-primary"></i>Formulir Booking Servis</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="booking.php" method="POST">

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-secondary">Pilih Kendaraan</label>
                                        <select name="id_kendaraan" class="form-select form-control-lg" required>
                                            <option value="">-- Pilih Kendaraan --</option>
                                            <?php if ($q_kendaraan && mysqli_num_rows($q_kendaraan) > 0) { ?>
                                                <?php while ($kendaraan = mysqli_fetch_assoc($q_kendaraan)) { ?>
                                                    <?php
                                                        $merk  = ambil_kolom($kendaraan, ['merk', 'merek', 'brand']);
                                                        $tipe  = ambil_kolom($kendaraan, ['tipe', 'model', 'jenis']);
                                                        $nopol = ambil_kolom($kendaraan, ['no_polisi', 'nomor_polisi', 'plat_nomor', 'nopol']);
                                                        $label_kendaraan = trim($merk . ' ' . $tipe);

                                                        if ($nopol !== '') {
                                                            $label_kendaraan .= ' - ' . $nopol;
                                                        }

                                                        if (trim($label_kendaraan) === '') {
                                                            $label_kendaraan = 'Kendaraan #' . $kendaraan['id_kendaraan'];
                                                        }
                                                    ?>
                                                    <option value="<?= e($kendaraan['id_kendaraan']); ?>"><?= e($label_kendaraan); ?></option>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <option value="" disabled>Belum ada kendaraan terdaftar</option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-secondary">Paket Layanan</label>
                                        <select name="id_paket" class="form-select form-control-lg" required>
                                            <option value="">-- Pilih Paket --</option>
                                            <?php if ($q_paket && mysqli_num_rows($q_paket) > 0) { ?>
                                                <?php while ($paket = mysqli_fetch_assoc($q_paket)) { ?>
                                                    <?php
                                                        $nama_paket = ambil_kolom($paket, ['nama_paket', 'nama_layanan', 'jenis_paket', 'paket'], 'Paket #' . $paket['id_paket']);
                                                        $harga = ambil_kolom($paket, ['harga', 'biaya', 'tarif']);
                                                        $label_paket = $nama_paket;

                                                        if ($harga !== '') {
                                                            $label_paket .= ' - ' . format_rupiah($harga);
                                                        }
                                                    ?>
                                                    <option value="<?= e($paket['id_paket']); ?>"><?= e($label_paket); ?></option>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <option value="" disabled>Belum ada paket layanan</option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-secondary">Tanggal Servis</label>
                                        <input type="date" name="tanggal_servis" class="form-control form-control-lg" min="<?= date('Y-m-d'); ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-secondary">Jam Servis</label>
                                        <input type="time" name="jam_servis" class="form-control form-control-lg" required>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="form-label small fw-bold text-secondary">Keluhan atau Jenis Kerusakan</label>
                                    <textarea name="keluhan" class="form-control form-control-lg" rows="4" placeholder="Jelaskan kendala kendaraan Anda..."></textarea>
                                </div>

                                <div class="d-flex justify-content-end mt-4 pt-2">
                                    <button type="submit" name="buat_booking" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Kirim Booking
                                    </button>
                                </div>

                            </form>
                        </div>
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
