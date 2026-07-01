<?php
session_start();
include "../config/koneksi.php";

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function columnExists($koneksi, $table, $column) {
    $table = mysqli_real_escape_string($koneksi, $table);
    $column = mysqli_real_escape_string($koneksi, $column);
    $result = mysqli_query($koneksi, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

$hasShiftColumn = columnExists($koneksi, 'tbl_mekanik', 'shift');
$currentPage = basename($_SERVER['PHP_SELF']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $nama = trim($_POST['nama'] ?? '');
        $kepegawaian = trim($_POST['kepegawaian'] ?? '');
        $spesialisasi = trim($_POST['spesialisasi'] ?? '');
        $shift = trim($_POST['shift'] ?? 'Pagi');

        if ($nama === '' || $kepegawaian === '' || $spesialisasi === '') {
            header("Location: $currentPage?status=kosong");
            exit;
        }

        if ($hasShiftColumn) {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_mekanik (nama, kepegawaian, spesialisasi, shift) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'ssss', $nama, $kepegawaian, $spesialisasi, $shift);
        } else {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_mekanik (nama, kepegawaian, spesialisasi) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $nama, $kepegawaian, $spesialisasi);
        }

        $success = $stmt && mysqli_stmt_execute($stmt);
        header("Location: $currentPage?status=" . ($success ? 'tambah_sukses' : 'tambah_gagal'));
        exit;
    }

    if ($aksi === 'edit') {
        $id_mekanik = (int) ($_POST['id_mekanik'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $kepegawaian = trim($_POST['kepegawaian'] ?? '');
        $spesialisasi = trim($_POST['spesialisasi'] ?? '');
        $shift = trim($_POST['shift'] ?? 'Pagi');

        if ($id_mekanik <= 0 || $nama === '' || $kepegawaian === '' || $spesialisasi === '') {
            header("Location: $currentPage?status=kosong");
            exit;
        }

        if ($hasShiftColumn) {
            $stmt = mysqli_prepare($koneksi, "UPDATE tbl_mekanik SET nama = ?, kepegawaian = ?, spesialisasi = ?, shift = ? WHERE id_mekanik = ?");
            mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $kepegawaian, $spesialisasi, $shift, $id_mekanik);
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE tbl_mekanik SET nama = ?, kepegawaian = ?, spesialisasi = ? WHERE id_mekanik = ?");
            mysqli_stmt_bind_param($stmt, 'sssi', $nama, $kepegawaian, $spesialisasi, $id_mekanik);
        }

        $success = $stmt && mysqli_stmt_execute($stmt);
        header("Location: $currentPage?status=" . ($success ? 'edit_sukses' : 'edit_gagal'));
        exit;
    }

    if ($aksi === 'hapus') {
        $id_mekanik = (int) ($_POST['id_mekanik'] ?? 0);

        if ($id_mekanik <= 0) {
            header("Location: $currentPage?status=hapus_gagal");
            exit;
        }

        $stmt = mysqli_prepare($koneksi, "DELETE FROM tbl_mekanik WHERE id_mekanik = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id_mekanik);
        $success = $stmt && mysqli_stmt_execute($stmt);

        header("Location: $currentPage?status=" . ($success ? 'hapus_sukses' : 'hapus_gagal'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mekanik - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .sidebar {
            background: #111625;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 999;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar-brand-wrapper {
            padding: 20px 24px 10px 24px;
        }
        .sidebar-brand {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #38bdf8;
        }
        .sidebar-subtitle {
            font-size: 9px; 
            font-weight: 700; 
            letter-spacing: 1px; 
            color: #475569;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .nav-section-title {
            font-size: 10px;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 15px 24px 6px 24px;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            font-size: 14px;
            font-weight: 600;
            padding: 9px 24px; 
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
            text-decoration: none;
        }
        .sidebar .nav-link i {
            font-size: 16px;
            width: 28px;
            color: #64748b;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover {
            color: #38bdf8;
            background: rgba(56, 189, 248, 0.04);
        }
        .sidebar .nav-link:hover i {
            color: #38bdf8;
        }
        .sidebar .nav-link.active {
            background: rgba(59, 130, 246, 0.12);
            color: #3b82f6;
            font-weight: 700;
            border-left-color: #3b82f6;
        }
        .sidebar .nav-link.active i {
            color: #3b82f6;
        }
        .logout-link {
            color: #ef4444 !important;
            font-weight: 700 !important;
        }
        .logout-link i {
            color: #ef4444 !important;
        }
        .logout-link:hover {
            background: rgba(239, 68, 68, 0.08) !important;
        }
        .main-wrapper {
            margin-left: 16.666667%; 
            padding: 40px;
        }
        .table-premium {
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08);
            padding: 30px;
        }
        .table-premium thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 20px;
            border-bottom: none;
        }
        .table-premium tbody td {
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            font-size: 14px;
        }
        .badge-shift {
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
        }
        .btn-add {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
            color: white;
        }
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.22);
        }
        .modal-header {
            border-bottom: 1px solid #eef2f7;
            padding: 24px 24px 14px 24px;
        }
        .modal-body {
            padding: 24px;
        }
        .modal-footer {
            border-top: 1px solid #eef2f7;
            padding: 16px 24px 22px 24px;
        }
        .form-label {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid #dbe3ef;
            padding: 11px 13px;
            font-size: 14px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }
        .btn-save {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 10px 18px;
            font-weight: 700;
            font-size: 14px;
        }
        .btn-save:hover {
            background: #1d4ed8;
            color: white;
        }
        .btn-cancel {
            background: #f8fafc;
            color: #1e293b;
            border: none;
            border-radius: 12px;
            padding: 10px 18px;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-danger-action {
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 10px 18px;
            font-weight: 700;
            font-size: 14px;
        }
        .btn-danger-action:hover {
            background: #b91c1c;
            color: white;
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <div class="col-md-3 col-lg-2 sidebar">
            <div>
                <div class="sidebar-brand-wrapper text-start ps-4">
                    <div class="sidebar-brand">SIBEO</div>
                    <div class="sidebar-subtitle">MANAGEMENT SYSTEM</div>
                </div>
                
                <div class="nav-section-title">MENU UTAMA</div>
                <div class="nav flex-column">
                    <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-simple me-3"></i>Dashboard</a>
                    <a href="pelanggan.php" class="nav-link"><i class="fa-solid fa-users me-3"></i>Data Pelanggan</a>
                    <a href="suku_cadang.php" class="nav-link"><i class="fa-solid fa-layer-group me-3"></i>Suku Cadang</a>
                    <a href="mekanik.php" class="nav-link active"><i class="fa-solid fa-clipboard-user me-3"></i>Data Mekanik</a>
                    <a href="kendaraan.php" class="nav-link"><i class="fa-solid fa-car me-3"></i>Data Kendaraan</a>
                </div>

                <div class="nav-section-title">MENU OPERASIONAL</div>
                <div class="nav flex-column">
                    <a href="paket_layanan.php" class="nav-link"><i class="fa-solid fa-tags me-3"></i>Paket Layanan</a>
                    <a href="alat_kerja.php" class="nav-link"><i class="fa-solid fa-toolbox me-3"></i>Alat Kerja</a>
                    <a href="stall.php" class="nav-link"><i class="fa-solid fa-circle-dot me-3"></i>Data Stall</a>
                </div>

                <div class="nav-section-title">MENU TRANSAKSI</div>
                <div class="nav flex-column">
                    <a href="booking.php" class="nav-link"><i class="fa-solid fa-tags me-3"></i>Booking</a>
                    <a href="alat_kerja.php" class="nav-link"><i class="fa-solid fa-toolbox me-3"></i>Alat Kerja</a>
                    <a href="stall.php" class="nav-link"><i class="fa-solid fa-circle-dot me-3"></i>Data Stall</a>
                </div>
            </div>
            
            <div class="mb-3 pt-2">
                <div class="nav flex-column">
                    <a href="../auth/logout.php" class="nav-link logout-link" onclick="return confirm('Keluar dari sistem?')">
                        <i class="fa-solid fa-sign-out-alt"></i>Keluar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 main-wrapper">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold m-0" style="color: #0f172a; letter-spacing: -0.5px;">Data Mekanik</h2>
                    <p class="text-muted small m-0 mt-1">Kelola data seluruh tim mekanik dan pembagian shift operasional bengkel SIBEO.</p>
                </div>
                <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalTambahMekanik">
                    <i class="fa-solid fa-plus me-2"></i> Tambah Mekanik
                </button>
            </div>

            <?php if (isset($_GET['status'])): ?>
                <?php
                    $status = $_GET['status'];
                    $alerts = [
                        'tambah_sukses' => ['success', 'Data mekanik berhasil ditambahkan.'],
                        'tambah_gagal' => ['danger', 'Data mekanik gagal ditambahkan.'],
                        'edit_sukses' => ['success', 'Data mekanik berhasil diperbarui.'],
                        'edit_gagal' => ['danger', 'Data mekanik gagal diperbarui.'],
                        'hapus_sukses' => ['success', 'Data mekanik berhasil dihapus.'],
                        'hapus_gagal' => ['danger', 'Data mekanik gagal dihapus.'],
                        'kosong' => ['warning', 'Semua field wajib diisi.']
                    ];
                    $alert = $alerts[$status] ?? null;
                ?>
                <?php if ($alert): ?>
                    <div class="alert alert-<?= e($alert[0]); ?> alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                        <?= e($alert[1]); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="table-premium">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;" class="text-center">NO</th>
                                <th>NAMA MEKANIK</th>
                                <th>NOMOR TELEPON</th>
                                <th>SPESIALISASI</th>
                                <th class="text-center">SHIFT KERJA</th>
                                <th class="text-center" style="width: 140px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query_mekanik = mysqli_query($koneksi, "SELECT * FROM tbl_mekanik ORDER BY id_mekanik ASC");
                            
                            if (mysqli_num_rows($query_mekanik) == 0) {
                                echo "<tr><td colspan='6' class='text-center text-muted py-4 small'>Belum ada data mekanik terdaftar. Klik tombol di atas untuk menambah mekanik!</td></tr>";
                            }
                            
                            while ($data = mysqli_fetch_assoc($query_mekanik)) {
                                $shift = $hasShiftColumn && isset($data['shift']) && $data['shift'] !== '' ? $data['shift'] : 'Pagi';
                                $shiftLower = strtolower($shift);
                                if ($shiftLower == 'malam') {
                                    $badge = '<span class="badge bg-dark bg-opacity-10 text-dark badge-shift"><i class="fa-solid fa-moon me-1"></i> Malam</span>';
                                } elseif ($shiftLower == 'siang') {
                                    $badge = '<span class="badge bg-warning bg-opacity-10 text-warning badge-shift"><i class="fa-solid fa-cloud-sun me-1"></i> Siang</span>';
                                } else {
                                    $badge = '<span class="badge bg-success bg-opacity-10 text-success badge-shift"><i class="fa-solid fa-sun me-1"></i> Pagi</span>';
                                }
                                ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                    <td class="fw-bold" style="color: #0f172a;"><?= e($data['nama']); ?></td>
                                    <td class="fw-medium text-secondary"><?= e($data['kepegawaian']); ?></td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-3 px-3 py-2"><?= e($data['spesialisasi']); ?></span></td>
                                    <td class="text-center"><?= $badge; ?></td>
                                    <td class="text-center">
                                        <button type="button"
                                            class="btn btn-sm btn-light text-primary me-1 rounded-3 btn-edit"
                                            title="Edit Data"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditMekanik"
                                            data-id="<?= (int) $data['id_mekanik']; ?>"
                                            data-nama="<?= e($data['nama']); ?>"
                                            data-kepegawaian="<?= e($data['kepegawaian']); ?>"
                                            data-spesialisasi="<?= e($data['spesialisasi']); ?>"
                                            data-shift="<?= e($shift); ?>">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-sm btn-light text-danger rounded-3 btn-delete"
                                            title="Hapus Data"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalHapusMekanik"
                                            data-id="<?= (int) $data['id_mekanik']; ?>"
                                            data-nama="<?= e($data['nama']); ?>">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahMekanik" tabindex="-1" aria-labelledby="modalTambahMekanikLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= e($currentPage); ?>">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalTambahMekanikLabel">Tambah Mekanik</h5>
                        <p class="text-muted small mb-0 mt-1">Isi data mekanik baru, lalu klik simpan.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Mekanik</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama mekanik" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="kepegawaian" class="form-control" placeholder="Contoh: 081234567890" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Spesialisasi</label>
                        <input type="text" name="spesialisasi" class="form-control" placeholder="Contoh: Mesin, Kelistrikan, Rem" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Shift Kerja</label>
                        <select name="shift" class="form-select" required>
                            <option value="Pagi">Pagi</option>
                            <option value="Siang">Siang</option>
                            <option value="Malam">Malam</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-save"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditMekanik" tabindex="-1" aria-labelledby="modalEditMekanikLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= e($currentPage); ?>">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id_mekanik" id="edit_id_mekanik">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalEditMekanikLabel">Edit Mekanik</h5>
                        <p class="text-muted small mb-0 mt-1">Ubah data mekanik, lalu klik simpan.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Mekanik</label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="kepegawaian" id="edit_kepegawaian" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Spesialisasi</label>
                        <input type="text" name="spesialisasi" id="edit_spesialisasi" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Shift Kerja</label>
                        <select name="shift" id="edit_shift" class="form-select" required>
                            <option value="Pagi">Pagi</option>
                            <option value="Siang">Siang</option>
                            <option value="Malam">Malam</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-save"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHapusMekanik" tabindex="-1" aria-labelledby="modalHapusMekanikLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <form method="POST" action="<?= e($currentPage); ?>">
                <input type="hidden" name="aksi" value="hapus">
                <input type="hidden" name="id_mekanik" id="hapus_id_mekanik">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalHapusMekanikLabel">Hapus Mekanik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">Yakin ingin menghapus data ini?</p>
                    <p class="fw-bold mb-0" id="hapus_nama_mekanik" style="color:#0f172a;"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger-action"><i class="fa-solid fa-trash-can me-2"></i>Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-edit').forEach(function(button) {
    button.addEventListener('click', function() {
        document.getElementById('edit_id_mekanik').value = this.dataset.id || '';
        document.getElementById('edit_nama').value = this.dataset.nama || '';
        document.getElementById('edit_kepegawaian').value = this.dataset.kepegawaian || '';
        document.getElementById('edit_spesialisasi').value = this.dataset.spesialisasi || '';
        document.getElementById('edit_shift').value = this.dataset.shift || 'Pagi';
    });
});

document.querySelectorAll('.btn-delete').forEach(function(button) {
    button.addEventListener('click', function() {
        document.getElementById('hapus_id_mekanik').value = this.dataset.id || '';
        document.getElementById('hapus_nama_mekanik').textContent = this.dataset.nama || '';
    });
});
</script>
</body>
</html>
