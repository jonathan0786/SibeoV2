<?php
session_start();
include "../config/koneksi.php";

// =================================================================
// LOGIKA GENERATE KODE ALAT OTOMATIS (ALT-001) UNTUK DITAMPILKAN DI FORM
// =================================================================
$query_cari_kode = mysqli_query($koneksi, "SELECT max(kode_alat) as kodeTerbesar FROM tbl_alat_kerja WHERE kode_alat LIKE 'ALT-%'");
$data_kode = mysqli_fetch_array($query_cari_kode);
$kode_alat_terakhir = $data_kode['kodeTerbesar'];

// Mengambil angka dari kode terakhir (misal 'ALT-001' diambil 001)
$urutan = (int) substr($kode_alat_terakhir, 4, 3);
$urutan++; // Angka urutan ditambah 1

// Membentuk kembali string kode baru (misal: ALT-001, ALT-002)
$kode_otomatis = "ALT-" . sprintf("%03s", $urutan);
// =================================================================

// Proses Insert Data Alat Kerja Baru
if (isset($_POST['simpan'])) {
    // Karena input diset readonly, nilai name="kode_alat" tetap ikut dikirim via POST
    $kode_alat = mysqli_real_escape_string($koneksi, $_POST['kode_alat']);
    $nama_alat = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $jumlah    = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $kondisi   = mysqli_real_escape_string($koneksi, $_POST['kondisi']);

    // Query INSERT ke database tbl_alat_kerja
    $query_tambah = mysqli_query($koneksi, "INSERT INTO tbl_alat_kerja (kode_alat, nama_alat, jumlah, kondisi) 
                     VALUES ('$kode_alat', '$nama_alat', '$jumlah', '$kondisi')");

    if ($query_tambah) {
        echo "<script>alert('Data alat kerja baru berhasil ditambahkan!'); window.location='alat_kerja.php';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan data: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Alat Kerja - SIBEO</title>
    
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
        
        /* STYLE KHUSUS INPUTAN READONLY (Sama seperti image_1e4d8a.png) */
        .form-control[readonly] { 
            background-color: #e0ebf6; /* Warna soft blue-grey */
            color: #1e293b; 
            font-weight: 700; 
            cursor: not-allowed; 
            border: 1px solid #b9d2ec;
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
    <h1 class="page-title">Tambah Alat Kerja</h1>
    <div class="form-card">
        <div class="card-inner-title">Registrasi Alat Baru</div>
        <div class="card-inner-subtitle">Sistem otomatis membuat kode urut alat untuk inventaris pengerjaan motor.</div>
        
        <form action="" method="POST">
            <div class="row g-3">
                
                <div class="col-md-12">
                    <label class="form-label">Nomor Alat (Otomatis)</label>
                    <input type="text" name="kode_alat" class="form-control" value="<?= $kode_otomatis; ?>" readonly>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Nama Alat / Tools</label>
                    <input type="text" name="nama_alat" class="form-control" placeholder="Contoh: Kunci T 14 Tekiro" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Jumlah Unit</label>
                    <input type="number" name="jumlah" class="form-control" placeholder="0" min="1" required>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Kondisi Alat</label>
                    <select name="kondisi" class="form-select" required>
                        <option value="Baik" selected>Baik (Siap Pakai)</option>
                        <option value="Rusak">Rusak / Perbaikan</option>
                    </select>
                </div>
                
                <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                    <a href="alat_kerja.php" class="btn btn-cancel-premium">Batal</a>
                    <button type="submit" name="simpan" class="btn btn-save-premium">Simpan Alat</button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>