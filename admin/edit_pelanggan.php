<?php
session_start();
include "../config/koneksi.php";

$id = $_GET['id'];
$query_ambil = mysqli_query($koneksi, "SELECT * FROM tbl_pelanggan WHERE id_pelanggan = '$id'");
$data = mysqli_fetch_assoc($query_ambil);

if (mysqli_num_rows($query_ambil) < 1) {
    header("Location: pelanggan.php");
    exit;
}

// Proses Update Data
if (isset($_POST['ubah'])) {
    $nama_lengkap    = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $nik             = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $no_telepon      = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);

    $query_update = mysqli_query($koneksi, "UPDATE tbl_pelanggan SET nama_lengkap='$nama_lengkap', nik='$nik', no_telepon='$no_telepon' WHERE id_pelanggan='$id'");

    if ($query_update) {
        echo "<script>alert('Data pelanggan berhasil diperbarui!'); window.location='pelanggan.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pelanggan - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .main-wrapper { padding: 40px; max-width: 700px; margin: 0 auto; }
        .form-card { background: white; border-radius: 24px; box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08); padding: 35px; border: none; }
        .form-label { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { border-radius: 12px; border: 1px solid #cbd5e1; padding: 12px 16px; font-size: 14px; transition: all 0.3s ease; background-color: #f8fafc; }
        .form-control:focus { background-color: #fff; border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15); }
        .btn-update { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; color: white; font-weight: 600; border-radius: 12px; padding: 12px 24px; }
        .btn-update:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25); color: white; }
        .btn-cancel { background: #f1f5f9; color: #64748b; font-weight: 600; border-radius: 12px; padding: 12px 24px; border: 1px solid #e2e8f0; }
        .btn-cancel:hover { background: #e2e8f0; color: #475569; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="mb-4">
        <a href="pelanggan.php" class="text-decoration-none text-muted small fw-bold"><i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar</a>
        <h2 class="fw-bold m-0 mt-2" style="color: #0f172a; letter-spacing: -0.5px;">Ubah Informasi Pelanggan</h2>
    </div>

    <div class="form-card">
        <form action="" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">No. Customer (Kunci)</label>
                    <input type="text" class="form-control fw-bold text-muted" value="<?= $data['nomor_pelanggan']; ?>" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIM / NPK / No. Identitas</label>
                    <input type="text" name="nik" class="form-control" value="<?= $data['nik']; ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Nama Lengkap Pelanggan</label>
                    <input type="text" name="nama_lengkap" class="form-control" value="<?= $data['nama_lengkap']; ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">No. WhatsApp / Kontak</label>
                    <input type="text" name="no_telepon" class="form-control" value="<?= $data['no_telepon']; ?>" placeholder="0812xxxxxxx" required>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="pelanggan.php" class="btn btn-cancel">Batal</a>
                    <button type="submit" name="ubah" class="btn btn-update"><i class="fa-solid fa-pen-to-square me-2"></i> Perbarui Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

</body>
</html>