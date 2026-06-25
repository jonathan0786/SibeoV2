<?php
session_start();
include "../config/koneksi.php"; 

if (!isset($_GET['id'])) {
    header("Location: paket_layanan.php");
    exit;
}
$id_paket = mysqli_real_escape_string($koneksi, $_GET['id']);

$query_ambil = mysqli_query($koneksi, "SELECT * FROM tbl_paket_layanan WHERE id_paket = '$id_paket'");
$data_lama   = mysqli_fetch_assoc($query_ambil);

if (mysqli_num_rows($query_ambil) == 0) {
    echo "<script>alert('Data paket tidak ditemukan!'); window.location='paket_layanan.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $nama_paket = mysqli_real_escape_string($koneksi, $_POST['nama_paket']);
    $deskripsi  = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga      = mysqli_real_escape_string($koneksi, $_POST['harga']);

    $query_update = mysqli_query($koneksi, "UPDATE tbl_paket_layanan SET 
                    nama_paket = '$nama_paket', 
                    deskripsi = '$deskripsi', 
                    harga = '$harga' 
                    WHERE id_paket = '$id_paket'");

    if ($query_update) {
        echo "<script>alert('Perubahan paket layanan berhasil disimpan!'); window.location='paket_layanan.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Paket - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f1f5f9; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }
        .form-wrapper-centered {
            width: 100%;
            max-width: 520px;
        }
        .btn-back-link {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 8px;
            transition: color 0.2s ease;
        }
        .btn-back-link:hover { color: #1e293b; }

        .page-title {
            color: #1e293b; 
            font-size: 26px; 
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 20px;
        }

        /* Card Form */
        .form-card { 
            background: #ffffff; 
            border-radius: 24px; 
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.02); 
            padding: 30px; 
            border: 1px solid rgba(0, 0, 0, 0.01);
        }
        
        .card-inner-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }
        .card-inner-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 22px;
        }

        .form-label {
            font-size: 11px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .form-control { 
            border-radius: 12px; 
            padding: 10px 14px; 
            background-color: #f8fafc; 
            border: 1px solid #e2e8f0;
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            background-color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .input-group-custom {
            display: flex;
            position: relative;
            align-items: center;
        }
        .input-group-custom .currency-label {
            position: absolute;
            left: 14px;
            font-weight: 700;
            color: #64748b;
            font-size: 13px;
            z-index: 10;
        }
        .input-group-custom .form-control {
            padding-left: 40px;
            width: 100%;
        }

        /* Button */
        .btn-save-premium {
            background-color: #2563eb;
            border: none;
            color: white;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        .btn-save-premium:hover {
            background-color: #1d4ed8;
        }
        .btn-cancel-premium {
            background-color: #f1f5f9;
            border: none;
            color: #475569;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-cancel-premium:hover {
            background-color: #e2e8f0;
        }
    </style>
</head>
<body>

<div class="form-wrapper-centered">
    
    <a href="paket_layanan.php" class="btn-back-link">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
    </a>
    
    <h1 class="page-title">Edit Paket Layanan</h1>

    <div class="form-card">
        <div class="card-inner-title">Modifikasi Paket Layanan</div>
        <div class="card-inner-subtitle">Perbarui rincian informasi data paket layanan bengkel SIBEO.</div>
        
        <form action="" method="POST">
            <div class="row g-3">
                
                <div class="col-md-12">
                    <label class="form-label">Nama Paket Layanan</label>
                    <input type="text" name="nama_paket" class="form-control" value="<?= htmlspecialchars($data_lama['nama_paket']); ?>" placeholder="Contoh: Servis Berkala Ringan" required>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Deskripsi Layanan</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Contoh: Ganti oli mesin, tune up..." required><?= htmlspecialchars($data_lama['deskripsi']); ?></textarea>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Harga Jasa</label>
                    <div class="input-group-custom">
                        <span class="currency-label">Rp</span>
                        <input type="number" name="harga" class="form-control" value="<?= htmlspecialchars($data_lama['harga']); ?>" placeholder="Contoh: 150000" required>
                    </div>
                </div>
                
                <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                    <a href="paket_layanan.php" class="btn btn-cancel-premium">Batal</a>
                    <button type="submit" name="update" class="btn btn-save-premium">Simpan Perubahan</button>
                </div>
                
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>