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

// 2. PROSES TAMBAH KENDARAAN
if (isset($_POST['tambah_kendaraan'])) {
    $merk = mysqli_real_escape_string($koneksi, $_POST['merk']);
    $nomor_polisi = mysqli_real_escape_string($koneksi, $_POST['nomor_polisi']);
    $tipe = mysqli_real_escape_string($koneksi, $_POST['tipe']);
    $tahun_pembuatan = mysqli_real_escape_string($koneksi, $_POST['tahun_pembuatan']);

    $cek_plat = mysqli_query($koneksi, "SELECT id_kendaraan FROM tbl_kendaraan WHERE nomor_polisi = '$nomor_polisi'");
    if (mysqli_num_rows($cek_plat) > 0) {
        $pesan = "<div class='alert alert-danger small rounded-3 alert-dismissible fade show' role='alert'>
                    <i class='fa-solid fa-circle-xmark me-2'></i>Nomor polisi tersebut sudah terdaftar!
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    } else {
        $simpan = mysqli_query($koneksi, "INSERT INTO tbl_kendaraan (id_pelanggan, merk, nomor_polisi, tipe, tahun_pembuatan) 
                                          VALUES ('$id_pelanggan', '$merk', '$nomor_polisi', '$tipe', '$tahun_pembuatan')");
        if ($simpan) {
            $pesan = "<div class='alert alert-success small rounded-3 alert-dismissible fade show' role='alert'>
                        <i class='fa-solid fa-circle-check me-2'></i>Kendaraan berhasil ditambahkan!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        }
    }
}

// 3. PROSES EDIT KENDARAAN
if (isset($_POST['edit_kendaraan'])) {
    $id_kendaraan = mysqli_real_escape_string($koneksi, $_POST['id_kendaraan']);
    $merk = mysqli_real_escape_string($koneksi, $_POST['merk']);
    $nomor_polisi = mysqli_real_escape_string($koneksi, $_POST['nomor_polisi']);
    $tipe = mysqli_real_escape_string($koneksi, $_POST['tipe']);
    $tahun_pembuatan = mysqli_real_escape_string($koneksi, $_POST['tahun_pembuatan']);

    // Cek nomor polisi agar tidak bentrok dengan kendaraan lain
    $cek_plat = mysqli_query($koneksi, "SELECT id_kendaraan FROM tbl_kendaraan WHERE nomor_polisi = '$nomor_polisi' AND id_kendaraan != '$id_kendaraan'");
    if (mysqli_num_rows($cek_plat) > 0) {
        $pesan = "<div class='alert alert-danger small rounded-3 alert-dismissible fade show' role='alert'>
                    <i class='fa-solid fa-circle-xmark me-2'></i>Gagal edit! Nomor polisi sudah dipakai kendaraan lain.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    } else {
        $update = mysqli_query($koneksi, "UPDATE tbl_kendaraan SET merk='$merk', nomor_polisi='$nomor_polisi', tipe='$tipe', tahun_pembuatan='$tahun_pembuatan' 
                                          WHERE id_kendaraan='$id_kendaraan' AND id_pelanggan='$id_pelanggan'");
        if ($update) {
            $pesan = "<div class='alert alert-success small rounded-3 alert-dismissible fade show' role='alert'>
                        <i class='fa-solid fa-circle-check me-2'></i>Data kendaraan berhasil diperbarui!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        }
    }
}

// 4. PROSES HAPUS KENDARAAN
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Keamanan tambahan: Pastikan kendaraan yang dihapus adalah benar milik pelanggan yang sedang login
    $hapus = mysqli_query($koneksi, "DELETE FROM tbl_kendaraan WHERE id_kendaraan = '$id_hapus' AND id_pelanggan = '$id_pelanggan'");
    if ($hapus) {
        $pesan = "<div class='alert alert-success small rounded-3 alert-dismissible fade show' role='alert'>
                    <i class='fa-solid fa-circle-check me-2'></i>Kendaraan berhasil dihapus dari garasi.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garasi Kendaraan - SIBEO</title>
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
        .form-control, .form-select { border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 16px; font-size: 14px; color: #334155; }
        .form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        
        .btn-premium { background: #3b82f6; color: white; border-radius: 12px; font-weight: 600; font-size: 14px; padding: 10px 20px; border: none; transition: all 0.2s; }
        .btn-premium:hover { background: #2563eb; color: white; }
        .btn-add { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; font-size: 13px; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25); transition: all 0.2s; }
        .btn-add:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35); color: white; }
        
        .table-premium thead th { background-color: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 16px 20px; border-bottom: none; }
        .table-premium tbody td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 14px; }
        
        .modal-content { border: none; border-radius: 24px; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.2); }
        .modal-header { border-bottom: 1px solid #f1f5f9; padding: 24px 30px 16px 30px; }
        .modal-body { padding: 20px 30px 30px 30px; }
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
                    <a href="booking.php" class="nav-link"><i class="fa-solid fa-calendar-check me-3"></i>Booking Servis</a>
                    <a href="kendaraan.php" class="nav-link active"><i class="fa-solid fa-car me-3"></i>Kendaraan Saya</a>
                    <a href="riwayat_servis.php" class="nav-link"><i class="fa-solid fa-clock-rotate-left me-3"></i>Riwayat Servis</a>
                </div>
            </div>
            <div class="mb-4 border-top border-secondary border-opacity-10 pt-2">
                <div class="nav flex-column">
                    <a href="../auth/logout.php" class="nav-link text-danger" onclick="return confirm('Keluar dari sistem?')"><i class="fa-solid fa-sign-out-alt me-3"></i>Keluar</a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 main-wrapper">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold m-0" style="color: #0f172a;">Garasi Kendaraan Saya</h4>
                    <p class="text-muted small m-0 mt-1">Kelola data kendaraan pribadi Anda di sini.</p>
                </div>
                <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalTambahKendaraan">
                    <i class="fa-solid fa-plus me-2"></i>Tambah Kendaraan
                </button>
            </div>

            <?= $pesan; ?>

            <div class="card-premium table-premium">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">IKON</th>
                                <th>MERK / NAMA KENDARAAN</th>
                                <th>NOMOR POLISI</th>
                                <th>TIPE</th>
                                <th>TAHUN</th>
                                <th class="text-center" style="width: 150px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ambil_kendaraan = mysqli_query($koneksi, "SELECT * FROM tbl_kendaraan WHERE id_pelanggan = '$id_pelanggan' ORDER BY id_kendaraan DESC");
                            
                            if (mysqli_num_rows($ambil_kendaraan) == 0) {
                                echo "<tr><td colspan='6' class='text-center text-muted small py-5'>Belum ada kendaraan di garasi Anda.</td></tr>";
                            } else {
                                while ($k = mysqli_fetch_assoc($ambil_kendaraan)) {
                                    $ikon = ($k['tipe'] == 'Mobil') ? 'fa-car' : 'fa-motorcycle';
                                    $warna_ikon = ($k['tipe'] == 'Mobil') ? 'text-success bg-success' : 'text-primary bg-primary';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-center rounded-3 bg-opacity-10 <?= $warna_ikon; ?>" style="width: 42px; height: 42px; font-size: 16px;">
                                                <i class="fa-solid <?= $ikon; ?>"></i>
                                            </div>
                                        </td>
                                        <td class="fw-bold" style="color: #0f172a;"><?= $k['merk']; ?></td>
                                        <td><code class="text-secondary fw-extrabold px-2 py-1 rounded bg-light border" style="font-size: 13px;"><?= $k['nomor_polisi']; ?></code></td>
                                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1.5 rounded fw-semibold" style="font-size: 11px;"><?= $k['tipe']; ?></span></td>
                                        <td class="fw-medium text-secondary"><?= (!empty($k['tahun_pembuatan'])) ? $k['tahun_pembuatan'] : '-'; ?></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-light text-primary me-1" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalEditKendaraan<?= $k['id_kendaraan']; ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <a href="kendaraan.php?hapus=<?= $k['id_kendaraan']; ?>" class="btn btn-sm btn-light text-danger" style="border-radius: 8px;" onclick="return confirm('Apakah Anda yakin ingin menghapus kendaraan <?= $k['merk']; ?> ini?')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="modalEditKendaraan<?= $k['id_kendaraan']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" style="color: #0f172a;"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Data Kendaraan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="" method="POST">
                                                    <div class="modal-body text-start">
                                                        <input type="hidden" name="id_kendaraan" value="<?= $k['id_kendaraan']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary">Merk / Nama Kendaraan</label>
                                                            <input type="text" name="merk" class="form-control" value="<?= $k['merk']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary">Nomor Polisi (Plat No)</label>
                                                            <input type="text" name="nomor_polisi" class="form-control" value="<?= $k['nomor_polisi']; ?>" style="text-transform: uppercase;" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary">Tipe Kendaraan</label>
                                                            <select name="tipe" class="form-select" required>
                                                                <option value="Motor" <?= ($k['tipe'] == 'Motor') ? 'selected' : ''; ?>>Sepeda Motor</option>
                                                                <option value="Mobil" <?= ($k['tipe'] == 'Mobil') ? 'selected' : ''; ?>>Mobil</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-2">
                                                            <label class="form-label small fw-semibold text-secondary">Tahun Pembuatan</label>
                                                            <input type="number" name="tahun_pembuatan" class="form-control" value="<?= $k['tahun_pembuatan']; ?>" placeholder="Contoh: 2020">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                                                        <button type="submit" name="edit_kendaraan" class="btn btn-premium"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan</button>
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
</div>

<div class="modal fade" id="modalTambahKendaraan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="color: #0f172a;"><i class="fa-solid fa-square-plus text-primary me-2"></i>Tambah Kendaraan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Merk / Nama Kendaraan</label>
                        <input type="text" name="merk" class="form-control" placeholder="Contoh: Honda Vario 150" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nomor Polisi (Plat No)</label>
                        <input type="text" name="nomor_polisi" class="form-control" placeholder="Contoh: B 1234 ABC" style="text-transform: uppercase;" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Tipe Kendaraan</label>
                        <select name="tipe" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Tipe Kendaraan --</option>
                            <option value="Motor">Sepeda Motor</option>
                            <option value="Mobil">Mobil</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Tahun Pembuatan</label>
                        <input type="number" name="tahun_pembuatan" class="form-control" placeholder="Contoh: 2022" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                    <button type="submit" name="tambah_kendaraan" class="btn btn-premium"><i class="fa-solid fa-save me-2"></i>Simpan Ke Garasi</button>
                </div>
            </form>
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