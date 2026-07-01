<?php
session_start();
include "../config/koneksi.php";

if (isset($_POST['simpan'])) {
    $nomor_pelanggan = mysqli_real_escape_string($koneksi, $_POST['nomor_pelanggan']);
    $nama_lengkap    = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $nik             = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $no_telepon      = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    
    // Tangkap dan enkripsi password
    $password   = $_POST['password'];
    $password_enkripsi = password_hash($password, PASSWORD_DEFAULT);

    // Tambahkan password ke dalam query INSERT
    $query_simpan = mysqli_query($koneksi, "INSERT INTO tbl_pelanggan (nomor_pelanggan, nama_lengkap, nik, no_telepon, password) VALUES ('$nomor_pelanggan', '$nama_lengkap', '$nik', '$no_telepon', '$password_enkripsi')");

    if ($query_simpan) {
        echo "<script>alert('Data pelanggan berhasil ditambahkan!'); window.location='pelanggan.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data: " . mysqli_error($koneksi) . "');</script>";
    }
}

//Untuk Nomor pelanggan Otomatis
$query_id = mysqli_query($koneksi, "SELECT MAX(id_pelanggan) AS max_id FROM tbl_pelanggan");
$data_id = mysqli_fetch_assoc($query_id);
$next_num = $data_id['max_id'] + 1;
$auto_number = "CS-" . str_pad($next_num, 3, "0", STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pelanggan - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .main-wrapper { padding: 40px; max-width: 700px; margin: 0 auto; }
        .form-card { background: white; border-radius: 24px; box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08); padding: 35px; border: none; }
        .form-label { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { border-radius: 12px; border: 1px solid #cbd5e1; padding: 12px 16px; font-size: 14px; transition: all 0.3s ease; background-color: #f8fafc; }
        .form-control:focus { background-color: #fff; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); }
        .btn-save { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none; color: white; font-weight: 600; border-radius: 12px; padding: 12px 24px; }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); color: white; }
        .btn-cancel { background: #f1f5f9; color: #64748b; font-weight: 600; border-radius: 12px; padding: 12px 24px; border: 1px solid #e2e8f0; }
        .btn-cancel:hover { background: #e2e8f0; color: #475569; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="mb-4">
        <a href="pelanggan.php" class="text-decoration-none text-muted small fw-bold"><i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar</a>
        <h2 class="fw-bold m-0 mt-2" style="color: #0f172a; letter-spacing: -0.5px;">Tambah Pelanggan Baru</h2>
    </div>

    <div class="form-card">
        <form action="" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">No. Customer (Otomatis)</label>
                    <input type="text" name="nomor_pelanggan" class="form-control fw-bold text-primary" value="<?= $auto_number; ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIM / NPK / No. Identitas</label>
                    <input type="text" name="nik" class="form-control" placeholder="Masukkan NIM atau NPK" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Nama Lengkap Pelanggan</label>
                    <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap sesuai KTM/KTP" required>
                </div>
                <div class="col-12">
                    <label class="form-label">No. WhatsApp (Kontak Aktif)</label>
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-secondary bg-opacity-10 fw-bold" style="border-radius: 12px 0 0 12px;">+62</span>
                        <input type="number" name="no_telepon" class="form-control" style="border-radius: 0 12px 12px 0;" placeholder="812xxxxxxx" required>
                    </div>
                </div>
                
                <div class="col-12">
                    <label class="form-label">Password Akun</label>
                    <input type="password" name="password" class="form-control" placeholder="Buat password untuk login pelanggan" required>
                    <small class="text-muted mt-2 d-block" style="font-size: 11px;">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i> Password akan dienkripsi secara otomatis oleh sistem.
                    </small>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="pelanggan.php" class="btn btn-cancel">Batal</a>
                    <button type="submit" name="simpan" class="btn btn-save"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan Pelanggan</button>
                </div>
            </div>
        </form>
    </div>
</div>

</body>
</html>