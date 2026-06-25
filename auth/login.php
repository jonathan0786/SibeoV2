<?php
session_start();
include "../config/koneksi.php"; // Jalur aman sesuai struktur foldermu

$pesan = "";

if (isset($_POST['login'])) {
    // Menangkap input dari satu kotak form yang sama (name="username")
    $userInput = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // 1. JALANKAN PENGECEKAN PERTAMA: Tabel Admin (menggunakan kolom 'username')
    $queryadmin = mysqli_query($koneksi, "SELECT * FROM tbl_admin WHERE username='$userInput' AND password='$password'");
    
    if (mysqli_num_rows($queryadmin) > 0) {
        $data = mysqli_fetch_assoc($queryadmin);
        
        $_SESSION['id'] = $data['id_admin']; 
        $_SESSION['nama_lengkap'] = $data['nama'];
        $_SESSION['role'] = 'admin';
        
        header("Location: ../index.php"); // Diarahkan ke pengatur rute utama
        exit();
    } 
    
    // 2. JALANKAN PENGECEKAN KEDUA: Tabel Mekanik (Tambahan Baru)
    $query_mekanik = mysqli_query($koneksi, "SELECT * FROM tbl_mekanik WHERE username='$userInput' AND password='$password'");
    
    if (mysqli_num_rows($query_mekanik) > 0) {
        $data_mekanik = mysqli_fetch_assoc($query_mekanik);
        
        // WAJIB DISIMPAN: id_mekanik untuk query filter di halaman dashboard mekanik
        $_SESSION['id'] = $data_mekanik['id_mekanik']; 
        $_SESSION['id_mekanik'] = $data_mekanik['id_mekanik']; 
        $_SESSION['mekanik_nama'] = $data_mekanik['nama']; 
        $_SESSION['nama_lengkap'] = $data_mekanik['nama']; // Ditambah agar sinkron jika layout sidebar memanggil nama_lengkap
        $_SESSION['role'] = 'mekanik';
        
        header("Location: ../mekanik/dashboard.php"); // Langsung dialihkan ke folder mekanik
        exit();
    }
    
    // 3. JALANKAN PENGECEKAN KETIGA: Tabel Pelanggan (menggunakan kolom 'nomor_pelanggan')
    $querypelanggan = mysqli_query($koneksi, "SELECT * FROM tbl_pelanggan WHERE nomor_pelanggan='$userInput' AND password='$password'");
    
    if (mysqli_num_rows($querypelanggan) > 0) {
        $data = mysqli_fetch_assoc($querypelanggan);
        
        $_SESSION['id'] = $data['id_pelanggan']; 
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['role'] = 'pelanggan';
        
        header("Location: ../index.php"); // Diarahkan ke pengatur rute utama
        exit();
    } 
    
    // 4. JIKA DI KETIGA TABEL TIDAK DITEMUKAN DATA YANG COCOK
    $pesan = "Kombinasi Username/Nomor Pelanggan atau Password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow-x: hidden;
        }
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 15px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px 35px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        .brand-logo {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            background: linear-gradient(45deg, #38bdf8, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .form-label {
            color: #fff;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid #fff;
            border-radius: 12px;
            color: #ffffff;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            color: #ffffff;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3) !important;
            opacity: 1;
        }
        .form-label i {
            color: #38bdf8;
        }
        .btn-submit {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            border-radius: 12px;
            color: white;
            padding: 12px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .alert-custom {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            font-size: 13px;
            border-radius: 12px;
            padding: 12px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        
        <div class="text-center mb-4">
            <div class="brand-logo mb-1">SIBEO</div>
            <p style="color: #ffff; font-size: 13px; font-weight: 500;">Sistem Manajemen Bengkel Kampus</p>
        </div>

        <?php if($pesan != ""): ?>
            <div class="alert alert-custom text-center mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $pesan; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label"><i class="fa-regular fa-user me-2"></i>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username " required autocomplete="off">
            </div>
            
            <div class="mb-4">
                <label class="form-label"><i class="fa-solid fa-lock me-2"></i>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" name="login" class="btn btn-submit w-100">
                Masuk Sistem <i class="fa-solid fa-arrow-right ms-2"></i>
            </button>
            <a href="../landingpage.php" class="d-block text-center mt-3 text-decoration-none text-secondary small" style="font-weight: 500;">
                <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Beranda
            </a>
        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>