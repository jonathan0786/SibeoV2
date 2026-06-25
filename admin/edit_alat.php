<?php
session_start();
include "../config/koneksi.php"; 

// 1. Ambil ID Alat dari URL
if (!isset($_GET['id'])) {
    header("Location: alat_kerja.php");
    exit;
}
$id_alat = mysqli_real_escape_string($koneksi, $_GET['id']);

// 2. Tarik data lama berdasarkan ID Alat dari tbl_alat_kerja
$query_ambil = mysqli_query($koneksi, "SELECT * FROM tbl_alat_kerja WHERE id_alat = '$id_alat'");
$data_lama   = mysqli_fetch_assoc($query_ambil);

if (mysqli_num_rows($query_ambil) == 0) {
    echo "<script>alert('Data alat kerja tidak ditemukan!'); window.location='alat_kerja.php';</script>";
    exit;
}

// 3. Proses Update Data Alat Kerja
if (isset($_POST['update'])) {
    $kode_alat = mysqli_real_escape_string($koneksi, $_POST['kode_alat']);
    $nama_alat = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $jumlah    = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $kondisi   = mysqli_real_escape_string($koneksi, $_POST['kondisi']);

    // Update data ke tabel tbl_alat_kerja sesuai nama kolom di database
    $query_update = mysqli_query($koneksi, "UPDATE tbl_alat_kerja SET 
                    kode_alat = '$kode_alat', 
                    nama_alat = '$nama_alat', 
                    jumlah = '$jumlah', 
                    kondisi = '$kondisi' 
                    WHERE id_alat = '$id_alat'");

    if ($query_update) {
        echo "<script>alert('Data alat kerja berhasil diperbarui!'); window.location='alat_kerja.php';</script>";
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
    <title>Edit Alat Kerja - SIBEO</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* BASE LAYOUT SYSTEM */
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

        /* NAVIGATION ELEMENTS */
        .btn-back-link { 
            color: #64748b; 
            font-size: 13px; 
            font-weight: 600; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            margin-bottom: 8px; 
        }
        .page-title { 
            color: #1e293b; 
            font-size: 26px; 
            font-weight: 800; 
            letter-spacing: -0.5px; 
            margin-bottom: 20px; 
        }

        /* CARD CONTAINER COMPONENTS */
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

        /* FORM FIELD COMPONENTS */
        .form-label { 
            font-size: 11px; 
            font-weight: 800; 
            color: #475569; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            margin-bottom: 6px; 
        }
        .form-control, .form-select { 
            border-radius: 12px; 
            padding: 10px 14px; 
            background-color: #f8fafc; 
            border: 1px solid #e2e8f0; 
            font-size: 13px; 
            font-weight: 500; 
            color: #1e293b; 
        }
        .form-control[readonly] { 
            background-color: #e2e8f0; 
            color: #475569; 
            font-weight: 700; 
            cursor: not-allowed; 
        }
        
        /* ACTION BUTTON PREMIUM STYLE */
        .btn-save-premium { 
            background-color: #2563eb; 
            border: none; 
            color: white; 
            font-weight: 700; 
            padding: 10px 20px; 
            border-radius: 12px; 
            font-size: 13px; 
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
        }
    </style>
</head>
<body>
<div class="form-wrapper-centered">
    <a href="alat_kerja.php" class="btn-back-link"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar</a>
    <h1 class="page-title">Edit Alat Kerja</h1>
    <div class="form-card">
        <div class="card-inner-title">Modifikasi Item Alat</div>
        <div class="card-inner-subtitle">Perbarui informasi kuantitas atau kondisi kelayakan inventaris bengkel.</div>
        
        <form action="" method="POST">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Kode Alat</label>
                    <input type="text" name="kode_alat" class="form-control" value="<?= htmlspecialchars($data_lama['kode_alat'] ?? ''); ?>" readonly>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Nama Alat Kerja</label>
                    <input type="text" name="nama_alat" class="form-control" value="<?= htmlspecialchars($data_lama['nama_alat'] ?? ''); ?>" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Jumlah (Pcs/Unit)</label>
                    <input type="number" name="jumlah" class="form-control" value="<?= htmlspecialchars($data_lama['jumlah'] ?? '0'); ?>" required>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Kondisi Alat</label>
                    <?php $kondisi_alat = strtolower(trim($data_lama['kondisi'] ?? '')); ?>
                    <select name="kondisi" class="form-select" required>
                        <option value="Baik" <?= ($kondisi_alat == 'baik' || $kondisi_alat == '') ? 'selected' : ''; ?>>Baik (Siap Pakai)</option>
                        <option value="Rusak" <?= ($kondisi_alat == 'rusak') ? 'selected' : ''; ?>>Rusak / Perbaikan</option>
                    </select>
                </div>
                
                <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                    <a href="alat_kerja.php" class="btn btn-cancel-premium">Batal</a>
                    <button type="submit" name="update" class="btn btn-save-premium">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>