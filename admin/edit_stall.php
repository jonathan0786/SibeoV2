<?php
session_start();
include "../config/koneksi.php"; 

// 1. Ambil ID Stall dari URL
if (!isset($_GET['id'])) {
    header("Location: stall.php");
    exit;
}
$id_stall = mysqli_real_escape_string($koneksi, $_GET['id']);

// 2. Tarik data lama berdasarkan ID
$query_ambil = mysqli_query($koneksi, "SELECT * FROM tbl_stall WHERE id_stall = '$id_stall'");
$data_lama   = mysqli_fetch_assoc($query_ambil);

if (mysqli_num_rows($query_ambil) == 0) {
    echo "<script>alert('Data stall tidak ditemukan!'); window.location='stall.php';</script>";
    exit;
}

// 3. Proses Update Data
if (isset($_POST['update'])) {
    $nomor_stall = mysqli_real_escape_string($koneksi, $_POST['nomor_stall']);
    $keterangan  = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $status      = mysqli_real_escape_string($koneksi, $_POST['status']); // Mengambil value 'tersedia' / 'terpakai'

    // Update data ke tabel tbl_stall
    $query_update = mysqli_query($koneksi, "UPDATE tbl_stall SET 
                    nomor_stall = '$nomor_stall', 
                    keterangan = '$keterangan', 
                    status = '$status' 
                    WHERE id_stall = '$id_stall'");

    if ($query_update) {
        echo "<script>alert('Status lajur stall berhasil diperbarui!'); window.location='stall.php';</script>";
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
    <title>Edit Stall - SIBEO</title>
    
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
    <a href="stall.php" class="btn-back-link"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar</a>
    <h1 class="page-title">Edit Lajur Stall</h1>
    <div class="form-card">
        <div class="card-inner-title">Modifikasi Stall</div>
        <div class="card-inner-subtitle">Perbarui rincian area atau ubah status keterisian lajur kerja secara berkala.</div>
        
        <form action="" method="POST">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Nomor Stall</label>
                    <input type="text" name="nomor_stall" class="form-control" value="<?= htmlspecialchars($data_lama['nomor_stall']); ?>" readonly>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Keterangan / Deskripsi Area</label>
                    <input type="text" name="keterangan" class="form-control" value="<?= htmlspecialchars($data_lama['keterangan']); ?>" required>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Status Keterisian</label>
                    <?php $status_lama = strtolower(trim($data_lama['status'])); ?>
                    <select name="status" class="form-select" required>
                        <option value="tersedia" <?= ($status_lama == 'tersedia' || $status_lama == 'kosong') ? 'selected' : ''; ?>>Kosong (Tersedia)</option>
                        <option value="terpakai" <?= ($status_lama == 'terpakai' || $status_lama == 'dipakai') ? 'selected' : ''; ?>>Sedang Dipakai</option>
                    </select>
                </div>
                
                <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                    <a href="stall.php" class="btn btn-cancel-premium">Batal</a>
                    <button type="submit" name="update" class="btn btn-save-premium">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>