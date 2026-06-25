<?php
session_start();
include "../config/koneksi.php";

if (!isset($_GET['id'])) {
    header("Location: mekanik.php");
    exit;
}

$id_mekanik = mysqli_real_escape_string($koneksi, $_GET['id']);

$query_ambil = mysqli_query($koneksi, "SELECT * FROM tbl_mekanik WHERE id_mekanik = '$id_mekanik'");
$data = mysqli_fetch_assoc($query_ambil);

if (!$data) {
    header("Location: mekanik.php");
    exit;
}

// Proses Update Data Mekanik
if (isset($_POST['update'])) {
    $nama         = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $spesialisasi = mysqli_real_escape_string($koneksi, $_POST['spesialisasi']);
    $shift        = mysqli_real_escape_string($koneksi, $_POST['shift']);
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password     = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query_update = mysqli_query($koneksi, "UPDATE tbl_mekanik SET 
        nama = '$nama', 
        spesialisasi = '$spesialisasi', 
        shift = '$shift',
        username = '$username',
        password = '$password'
        WHERE id_mekanik = '$id_mekanik'");

    if ($query_update) {
        echo "<script>alert('Data mekanik sukses diperbarui!'); window.location='mekanik.php';</script>";
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Mekanik - SIBEO</title>
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
    <h4 class="fw-bold mb-4">Ubah Data Mekanik</h4>
    <form action="" method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary text-uppercase">Kode Kepegawaian</label>
                <input type="text" name="kepegawaian" class="form-control bg-light text-muted" value="<?= htmlspecialchars($data['kepegawaian']); ?>" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary text-uppercase">Nama Mekanik</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary text-uppercase">Spesialisasi</label>
                <input type="text" name="spesialisasi" class="form-control" value="<?= htmlspecialchars($data['spesialisasi']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary text-uppercase">Shift Kerja</label>
                <select name="shift" class="form-select" required>
                    <option value="pagi" <?= $data['shift'] == 'pagi' ? 'selected' : ''; ?>>Pagi</option>
                    <option value="siang" <?= $data['shift'] == 'siang' ? 'selected' : ''; ?>>Siang</option>
                    <option value="malam" <?= $data['shift'] == 'malam' ? 'selected' : ''; ?>>Malam</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary text-uppercase">Username Akses</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($data['username']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary text-uppercase">Password Akses</label>
                <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($data['password']); ?>" required>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                <a href="mekanik.php" class="btn btn-light px-4">Batal</a>
                <button type="submit" name="update" class="btn btn-primary px-4">Simpan Perubahan</button>
            </div>
        </div>
    </form>
</div>

</body>
</html>