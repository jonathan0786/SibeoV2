<?php
session_start();
include "../config/koneksi.php";

if (isset($_POST['simpan'])) {
    $nama         = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $spesialisasi = mysqli_real_escape_string($koneksi, $_POST['spesialisasi']);
    $shift        = mysqli_real_escape_string($koneksi, $_POST['shift']);
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password     = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query_kode  = mysqli_query($koneksi, "SELECT max(kepegawaian) as kode_terbesar FROM tbl_mekanik");
    $data_kode   = mysqli_fetch_array($query_kode);
    $kode_mekanik = $data_kode['kode_terbesar'];

    $urutan = (int) substr($kode_mekanik, 3, 3);
    $urutan++;

    $prefix = "MK-";
    $kepegawaian = $prefix . sprintf("%03s", $urutan);

    $query_simpan = mysqli_query($koneksi, "INSERT INTO tbl_mekanik (nama, kepegawaian, spesialisasi, shift, username, password) VALUES ('$nama', '$kepegawaian', '$spesialisasi', '$shift', '$username', '$password')");

    if ($query_simpan) {
        echo "<script>alert('Mekanik baru dengan kode $kepegawaian berhasil didaftarkan!'); window.location='mekanik.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Mekanik - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; padding: 40px; }
        .form-card { background: white; border-radius: 20px; box-shadow: 0 4px 18px rgba(0,0,0,0.05); padding: 35px; max-width: 650px; margin: 0 auto; }
        .form-control, .form-select { border-radius: 10px; padding: 10px 15px; background-color: #f8fafc; }
    </style>
</head>
<body>

<div class="form-card">
    <h4 class="fw-bold mb-1">Registrasi Mekanik Baru</h4>
    <p class="text-muted small mb-4">Kode Kepegawaian (MK-XXX) akan dibuat otomatis oleh sistem.</p>
    
    <form action="" method="POST">
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label small fw-bold text-secondary text-uppercase">Nama Mekanik</label>
                <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap Karyawan" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary text-uppercase">Spesialisasi</label>
                <input type="text" name="spesialisasi" class="form-control" placeholder="Contoh: Mesin & Oli" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary text-uppercase">Shift Kerja</label>
                <select name="shift" class="form-select" required>
                    <option value="pagi">Pagi</option>
                    <option value="siang">Siang</option>
                    <option value="malam">Malam</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary text-uppercase">Username Login</label>
                <input type="text" name="username" class="form-control" placeholder="Untuk login mekanik" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary text-uppercase">Password Login</label>
                <input type="text" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                <a href="mekanik.php" class="btn btn-light px-4">Batal</a>
                <button type="submit" name="simpan" class="btn btn-primary px-4">Daftarkan Mekanik</button>
            </div>
        </div>
    </form>
</div>

</body>
</html>