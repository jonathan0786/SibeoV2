<?php
session_start();
include "../config/koneksi.php";

function buatKodePart($koneksi) {
    $prefix = "SP-";
    $nomor_terbesar = 0;

    $query_nomor = mysqli_query(
        $koneksi,
        "SELECT kode_part FROM tbl_suku_cadang"
    );

    if ($query_nomor) {
        while ($data_nomor = mysqli_fetch_assoc($query_nomor)) {
            $kode_lama = $data_nomor['kode_part'] ?? '';

            if (preg_match('/(\d+)$/', $kode_lama, $hasil)) {
                $nomor = (int) $hasil[1];

                if ($nomor > $nomor_terbesar) {
                    $nomor_terbesar = $nomor;
                }
            }
        }
    }

    $nomor_urut = $nomor_terbesar + 1;

    return $prefix . str_pad((string) $nomor_urut, 3, "0", STR_PAD_LEFT);
}

$kode_part_baru = buatKodePart($koneksi);

if (isset($_POST['simpan_suku_cadang'])) {
    $kode_part = buatKodePart($koneksi);
    $nama_part = trim($_POST['nama_part'] ?? '');
    $harga_satuan = (int) preg_replace('/[^0-9]/', '', $_POST['harga_satuan'] ?? '');
    $stok = (int) preg_replace('/[^0-9]/', '', $_POST['stok'] ?? '');

    if ($nama_part === '' || $harga_satuan < 0 || $stok < 0) {
        header("Location: suku_cadang.php?status=kosong");
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO tbl_suku_cadang (kode_part, nama_part, harga_satuan, stok) VALUES (?, ?, ?, ?)"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssii", $kode_part, $nama_part, $harga_satuan, $stok);
        $simpan = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($simpan) {
            header("Location: suku_cadang.php?status=sukses");
            exit;
        }
    }

    header("Location: suku_cadang.php?status=gagal");
    exit;
}

if (isset($_POST['update_suku_cadang'])) {
    $id_suku_cadang = (int) ($_POST['id_suku_cadang'] ?? 0);
    $nama_part = trim($_POST['edit_nama_part'] ?? '');
    $harga_satuan = (int) preg_replace('/[^0-9]/', '', $_POST['edit_harga_satuan'] ?? '');
    $stok = (int) preg_replace('/[^0-9]/', '', $_POST['edit_stok'] ?? '');

    if ($id_suku_cadang <= 0 || $nama_part === '' || $harga_satuan < 0 || $stok < 0) {
        header("Location: suku_cadang.php?status=edit_kosong");
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE tbl_suku_cadang SET nama_part = ?, harga_satuan = ?, stok = ? WHERE id_suku_cadang = ?"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "siii", $nama_part, $harga_satuan, $stok, $id_suku_cadang);
        $update = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($update) {
            header("Location: suku_cadang.php?status=edit_sukses");
            exit;
        }
    }

    header("Location: suku_cadang.php?status=edit_gagal");
    exit;
}

if (isset($_POST['hapus_suku_cadang'])) {
    $id_suku_cadang = (int) ($_POST['hapus_id_suku_cadang'] ?? 0);

    if ($id_suku_cadang <= 0) {
        header("Location: suku_cadang.php?status=hapus_gagal");
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM tbl_suku_cadang WHERE id_suku_cadang = ?"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_suku_cadang);
        $hapus = mysqli_stmt_execute($stmt);
        $jumlah_terhapus = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($hapus && $jumlah_terhapus > 0) {
            header("Location: suku_cadang.php?status=hapus_sukses");
            exit;
        }
    }

    header("Location: suku_cadang.php?status=hapus_gagal");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventori Suku Cadang - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        /* SideBar */
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
        
        /* Main Section */
        .main-wrapper {
            margin-left: 16.666667%; 
            padding: 40px;
        }
        
        /* Tabel */
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

        .badge-stok {
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
        }
        
        /* Button */
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
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            color: white;
        }
        .modal-content {
            border: none;
            border-radius: 22px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18);
        }
        .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 22px 24px 14px 24px;
        }
        .modal-body {
            padding: 22px 24px;
        }
        .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 16px 24px 22px 24px;
        }
        .form-label {
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }
        .form-control {
            border-radius: 12px;
            border-color: #e2e8f0;
            padding: 11px 14px;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
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
                    <a href="suku_cadang.php" class="nav-link active"><i class="fa-solid fa-layer-group me-3"></i>Suku Cadang</a>
                    <a href="mekanik.php" class="nav-link"><i class="fa-solid fa-clipboard-user me-3"></i>Data Mekanik</a>
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
                    <h2 class="fw-bold m-0" style="color: #0f172a; letter-spacing: -0.5px;">Inventori Suku Cadang</h2>
                    <p class="text-muted small m-0 mt-1">Pantau stok, harga, dan ketersediaan komponen bengkel SIBEO.</p>
                </div>
                <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalTambahSukuCadang">
                    <i class="fa-solid fa-plus me-2"></i> Tambah Komponen
                </button>
            </div>

            <?php if (isset($_GET['status'])) { ?>
                <?php if ($_GET['status'] == 'sukses') { ?>
                    <div class="alert alert-success border-0 rounded-4 shadow-sm small">
                        Data suku cadang berhasil ditambahkan.
                    </div>
                <?php } elseif ($_GET['status'] == 'edit_sukses') { ?>
                    <div class="alert alert-success border-0 rounded-4 shadow-sm small">
                        Data suku cadang berhasil diperbarui.
                    </div>
                <?php } elseif ($_GET['status'] == 'hapus_sukses') { ?>
                    <div class="alert alert-success border-0 rounded-4 shadow-sm small">
                        Data suku cadang berhasil dihapus.
                    </div>
                <?php } elseif ($_GET['status'] == 'kosong' || $_GET['status'] == 'edit_kosong') { ?>
                    <div class="alert alert-warning border-0 rounded-4 shadow-sm small">
                        Nama komponen, harga satuan, dan stok wajib diisi dengan benar.
                    </div>
                <?php } elseif ($_GET['status'] == 'gagal') { ?>
                    <div class="alert alert-danger border-0 rounded-4 shadow-sm small">
                        Data suku cadang gagal ditambahkan. Periksa koneksi database atau struktur tabel.
                    </div>
                <?php } elseif ($_GET['status'] == 'edit_gagal') { ?>
                    <div class="alert alert-danger border-0 rounded-4 shadow-sm small">
                        Data suku cadang gagal diperbarui. Periksa koneksi database atau struktur tabel.
                    </div>
                <?php } elseif ($_GET['status'] == 'hapus_gagal') { ?>
                    <div class="alert alert-danger border-0 rounded-4 shadow-sm small">
                        Data suku cadang gagal dihapus. Data mungkin sudah tidak tersedia atau terjadi masalah pada database.
                    </div>
                <?php } ?>
            <?php } ?>

            <div class="table-premium">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;" class="text-center">NO</th>
                                <th>KODE BARANG</th>
                                <th>NAMA KOMPONEN</th>
                                <th>HARGA SATUAN</th>
                                <th class="text-center">STOK GUDANG</th>
                                <th class="text-center" style="width: 140px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query_sparepart = mysqli_query($koneksi, "SELECT * FROM tbl_suku_cadang ORDER BY id_suku_cadang ASC");
                            
                            if (mysqli_num_rows($query_sparepart) == 0) {
                                echo "<tr><td colspan='6' class='text-center text-muted py-4 small'>Belum ada data suku cadang di gudang. Klik tombol di atas untuk menambah barang!</td></tr>";
                            }
                            
                            while ($data = mysqli_fetch_assoc($query_sparepart)) {
                                $stok = (int) ($data['stok'] ?? 0);
                                if ($stok <= 0) {
                                    $badge = '<span class="badge bg-danger bg-opacity-10 text-danger badge-stok">Habis</span>';
                                } elseif ($stok <= 5) {
                                    $badge = '<span class="badge bg-warning bg-opacity-10 text-warning badge-stok">' . $stok . ' Item (Limit)</span>';
                                } else {
                                    $badge = '<span class="badge bg-success bg-opacity-10 text-success badge-stok">' . $stok . ' Item</span>';
                                }
                                ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($data['kode_part'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="fw-semibold" style="color: #0f172a;"><?= htmlspecialchars($data['nama_part'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="fw-medium text-dark">Rp <?= number_format((int) ($data['harga_satuan'] ?? 0), 0, ',', '.'); ?></td>
                                    <td class="text-center"><?= $badge; ?></td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-light text-primary me-1 rounded-3"
                                                title="Edit Data"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditSukuCadang"
                                                data-id="<?= htmlspecialchars($data['id_suku_cadang'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-kode="<?= htmlspecialchars($data['kode_part'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-nama="<?= htmlspecialchars($data['nama_part'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-harga="<?= htmlspecialchars($data['harga_satuan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-stok="<?= htmlspecialchars($data['stok'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-light text-danger rounded-3"
                                                title="Hapus Data"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalHapusSukuCadang"
                                                data-id="<?= htmlspecialchars($data['id_suku_cadang'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-kode="<?= htmlspecialchars($data['kode_part'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-nama="<?= htmlspecialchars($data['nama_part'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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

<div class="modal fade" id="modalTambahSukuCadang" tabindex="-1" aria-labelledby="modalTambahSukuCadangLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="suku_cadang.php" class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="modalTambahSukuCadangLabel" style="color: #0f172a;">Tambah Suku Cadang</h5>
                    <p class="text-muted small mb-0 mt-1">Isi data komponen baru, lalu klik simpan.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="kode_part" class="form-label">Kode Barang</label>
                    <input type="text" class="form-control bg-light" id="kode_part" value="<?= htmlspecialchars($kode_part_baru, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="nama_part" class="form-label">Nama Komponen</label>
                    <input type="text" class="form-control" id="nama_part" name="nama_part" placeholder="Masukkan nama komponen" required>
                </div>
                <div class="mb-3">
                    <label for="harga_satuan" class="form-label">Harga Satuan</label>
                    <input type="number" class="form-control" id="harga_satuan" name="harga_satuan" min="0" placeholder="Contoh: 75000" required>
                </div>
                <div>
                    <label for="stok" class="form-label">Stok Gudang</label>
                    <input type="number" class="form-control" id="stok" name="stok" min="0" placeholder="Contoh: 10" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="simpan_suku_cadang" class="btn btn-add px-4">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditSukuCadang" tabindex="-1" aria-labelledby="modalEditSukuCadangLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="suku_cadang.php" class="modal-content">
            <input type="hidden" id="edit_id_suku_cadang" name="id_suku_cadang">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="modalEditSukuCadangLabel" style="color: #0f172a;">Edit Suku Cadang</h5>
                    <p class="text-muted small mb-0 mt-1">Ubah data komponen, lalu klik simpan perubahan.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_kode_part" class="form-label">Kode Barang</label>
                    <input type="text" class="form-control bg-light" id="edit_kode_part" readonly>
                </div>
                <div class="mb-3">
                    <label for="edit_nama_part" class="form-label">Nama Komponen</label>
                    <input type="text" class="form-control" id="edit_nama_part" name="edit_nama_part" placeholder="Masukkan nama komponen" required>
                </div>
                <div class="mb-3">
                    <label for="edit_harga_satuan" class="form-label">Harga Satuan</label>
                    <input type="number" class="form-control" id="edit_harga_satuan" name="edit_harga_satuan" min="0" placeholder="Contoh: 75000" required>
                </div>
                <div>
                    <label for="edit_stok" class="form-label">Stok Gudang</label>
                    <input type="number" class="form-control" id="edit_stok" name="edit_stok" min="0" placeholder="Contoh: 10" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="update_suku_cadang" class="btn btn-add px-4">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalHapusSukuCadang" tabindex="-1" aria-labelledby="modalHapusSukuCadangLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="suku_cadang.php" class="modal-content">
            <input type="hidden" id="hapus_id_suku_cadang" name="hapus_id_suku_cadang">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="modalHapusSukuCadangLabel" style="color: #0f172a;">Hapus Suku Cadang</h5>
                    <p class="text-muted small mb-0 mt-1">Pastikan komponen yang dihapus sudah benar.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning border-0 rounded-4 small mb-3">
                    Data suku cadang yang sudah dihapus tidak bisa dikembalikan dari halaman ini.
                </div>
                <div class="p-3 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="small text-muted fw-semibold mb-1">Kode Barang</div>
                    <div class="fw-bold text-primary mb-3" id="hapus_kode_part">-</div>
                    <div class="small text-muted fw-semibold mb-1">Nama Komponen</div>
                    <div class="fw-bold" style="color: #0f172a;" id="hapus_nama_part">-</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="hapus_suku_cadang" class="btn btn-danger rounded-3 px-4">
                    <i class="fa-solid fa-trash-can me-2"></i>Hapus Data
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEditSukuCadang = document.getElementById('modalEditSukuCadang');
    const modalHapusSukuCadang = document.getElementById('modalHapusSukuCadang');

    if (modalEditSukuCadang) {
        modalEditSukuCadang.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            document.getElementById('edit_id_suku_cadang').value = button.getAttribute('data-id') || '';
            document.getElementById('edit_kode_part').value = button.getAttribute('data-kode') || '';
            document.getElementById('edit_nama_part').value = button.getAttribute('data-nama') || '';
            document.getElementById('edit_harga_satuan').value = button.getAttribute('data-harga') || '';
            document.getElementById('edit_stok').value = button.getAttribute('data-stok') || '';
        });
    }

    if (modalHapusSukuCadang) {
        modalHapusSukuCadang.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            document.getElementById('hapus_id_suku_cadang').value = button.getAttribute('data-id') || '';
            document.getElementById('hapus_kode_part').textContent = button.getAttribute('data-kode') || '-';
            document.getElementById('hapus_nama_part').textContent = button.getAttribute('data-nama') || '-';
        });
    }
});
</script>

</body>
</html>
