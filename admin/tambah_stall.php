<?php
session_start();
include "../config/koneksi.php"; 

// ==========================================
// KODE OTOMATIS GENERATE NOMOR STALL (STL-00X)
// ==========================================
// Ambil nomor urut terbesar dari tabel tbl_stall
$query_auto = mysqli_query($koneksi, "SELECT nomor_stall FROM tbl_stall WHERE nomor_stall LIKE 'STL-%' ORDER BY nomor_stall DESC LIMIT 1");
$data_auto  = mysqli_fetch_assoc($query_auto);

if ($data_auto) {
    // Jika sudah ada data, ambil angka di belakang 'STL-' (misal STL-002 diambil 002)
    $nomor_terakhir = substr($data_auto['nomor_stall'], 4);
    // Tambah 1 dari nomor terakhir
    $angka_baru = (int)$nomor_terakhir + 1;
} else {
    // Jika tabel masih kosong, mulai dari 1
    $angka_baru = 1;
}

// Format angka menjadi 3 digit dengan awalan STL- (contoh: STL-001)
$nomor_stall_otomatis = "STL-" . str_pad($angka_baru, 3, "0", STR_PAD_LEFT);


// ==========================================
// PROSES SIMPAN DATA
// ==========================================
if (isset($_POST['simpan'])) {
    // nomor_stall diambil dari input readonly (hasil generate otomatis)
    $nomor_stall = mysqli_real_escape_string($koneksi, $_POST['nomor_stall']);
    $keterangan  = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $status      = mysqli_real_escape_string($koneksi, $_POST['status']);

    $query_simpan = mysqli_query($koneksi, "INSERT INTO tbl_stall (nomor_stall, keterangan, status) VALUES ('$nomor_stall', '$keterangan', '$status')");

    if ($query_simpan) {
        echo "<script>alert('Lajur stall baru berhasil didaftarkan!'); window.location='stall.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Stall - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 30px 20px; }
        .form-wrapper-centered { width: 100%; max-width: 520px; }
        .btn-back-link { color: #64748b; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; margin-bottom: 8px; }
        .page-title { color: #1e293b; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 20px; }
        .form-card { background: #ffffff; border-radius: 24px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.02); padding: 30px; border: 1px solid rgba(0, 0, 0, 0.01); }
        .card-inner-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 2px; }
        .card-inner-subtitle { font-size: 12px; color: #64748b; margin-bottom: 22px; }
        .form-label { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        
        .form-control, .form-select { border-radius: 12px; padding: 10px 14px; background-color: #f8fafc; border: 1px solid #e2e8f0; font-size: 13px; font-weight: 500; color: #1e293b; }
        .form-control[readonly] { background-color: #e2e8f0; color: #475569; font-weight: 700; cursor: not-allowed; }
        
        .btn-save-premium { background-color: #2563eb; border: none; color: white; font-weight: 700; padding: 10px 20px; border-radius: 12px; font-size: 13px; }
        .btn-cancel-premium { background-color: #f1f5f9; border: none; color: #475569; font-weight: 700; padding: 10px 20px; border-radius: 12px; font-size: 13px; text-decoration: none; }
    </style>
</head>
<body>
<div class="form-wrapper-centered">
    <a href="stall.php" class="btn-back-link"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar</a>
    <h1 class="page-title">Tambah Lajur Stall</h1>
    <div class="form-card">
        <div class="card-inner-title">Registrasi Stall Baru</div>
        <div class="card-inner-subtitle">Sistem otomatis membuat kode urut lajur untuk antrean pengerjaan motor.</div>
        
        <form action="" method="POST">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Nomor Stall (Otomatis)</label>
                    <input type="text" name="nomor_stall" class="form-control" value="<?= $nomor_stall_otomatis; ?>" readonly>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Keterangan / Deskripsi Area</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Khusus Servis Berat / Pit Utama" required>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Status Keterisian</label>
                    <select name="status" class="form-select" required>
                        <option value="Kosong">Tersedia</option>
                        <option value="Dipakai">Terpakai</option>
                    </select>
                </div>
                
                <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                    <a href="stall.php" class="btn btn-cancel-premium">Batal</a>
                    <button type="submit" name="simpan" class="btn btn-save-premium">Simpan Lajur</button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>