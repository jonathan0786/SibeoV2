<?php
session_start();
include "../config/koneksi.php";

if (isset($_POST['simpan'])) {
    $kode_part    = mysqli_real_escape_string($koneksi, $_POST['kode_part']);
    $nama_part    = mysqli_real_escape_string($koneksi, $_POST['nama_part']);
    $harga_satuan = mysqli_real_escape_string($koneksi, $_POST['harga_satuan']);
    $stok         = mysqli_real_escape_string($koneksi, $_POST['stok']);

    $query_simpan = mysqli_query($koneksi, "INSERT INTO tbl_suku_cadang (kode_part, nama_part, stok, harga_satuan) VALUES ('$kode_part', '$nama_part', '$stok', '$harga_satuan')");

    if ($query_simpan) {
        echo "<script>alert('Komponen baru berhasil ditambahkan ke gudang!'); window.location='suku_cadang.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan barang: " . mysqli_error($koneksi) . "');</script>";
    }
}

// Untuk Kode barang otomatis
$query_id = mysqli_query($koneksi, "SELECT MAX(id_suku_cadang) AS max_id FROM tbl_suku_cadang");
$data_id = mysqli_fetch_assoc($query_id);
$next_num = isset($data_id['max_id']) ? $data_id['max_id'] + 1 : 1;
$auto_code = "SP-" . str_pad($next_num, 3, "0", STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Suku Cadang - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .main-wrapper { padding: 40px; max-width: 700px; margin: 0 auto; }
        .form-card { background: white; border-radius: 24px; box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08); padding: 35px; border: none; }
        .form-label { font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { border-radius: 12px; border: 1px solid #cbd5e1; padding: 12px 16px; font-size: 14px; transition: all 0.3s ease; background-color: #f8fafc; }
        .form-control:focus { background-color: #fff; border-color: #22c55e; box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15); }
        .btn-save { background: linear-gradient(135deg, #22c55e 0%, #15803d 100%); border: none; color: white; font-weight: 600; border-radius: 12px; padding: 12px 24px; }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25); color: white; }
        .btn-cancel { background: #f1f5f9; color: #64748b; font-weight: 600; border-radius: 12px; padding: 12px 24px; border: 1px solid #e2e8f0; }
        .btn-cancel:hover { background: #e2e8f0; color: #475569; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="mb-4">
        <a href="suku_cadang.php" class="text-decoration-none text-muted small fw-bold"><i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Inventori</a>
        <h2 class="fw-bold m-0 mt-2" style="color: #0f172a; letter-spacing: -0.5px;">Tambah Komponen Gudang</h2>
    </div>

    <div class="form-card">
        <form action="" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Kode Barang (Otomatis)</label>
                    <input type="text" name="kode_part" class="form-control fw-bold text-success" value="<?= $auto_code; ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stok Awal</label>
                    <input type="number" name="stok" class="form-control" min="0" placeholder="0" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Nama Komponen / Suku Cadang</label>
                    <input type="text" name="nama_part" class="form-control" placeholder="Contoh: Oli Shell Advance 1L, Kampas Rem" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Harga Satuan (Rupiah)</label>
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-secondary bg-opacity-10 fw-bold" style="border-radius: 12px 0 0 12px;">Rp</span>
                        <input type="number" name="harga_satuan" class="form-control" style="border-radius: 0 12px 12px 0;" placeholder="Contoh: 45000" required>
                    </div>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <a href="suku_cadang.php" class="btn btn-cancel">Batal</a>
                    <button type="submit" name="simpan" class="btn btn-save"><i class="fa-solid fa-box-archive me-2"></i> Masukkan Gudang</button>
                </div>
            </div>
        </form>
    </div>
</div>

</body>
</html>