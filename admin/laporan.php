<?php
session_start();
include "../config/koneksi.php";

$current_page = 'laporan.php';

// Cek keamanan akses admin jika diperlukan (sesuai sistem login Anda)
// if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') { ... }

function safe_text($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Operasional - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --bg-body: #f4f6f9;
            --sidebar-bg: #1e293b;
            --sidebar-color: #94a3b8;
            --sidebar-active: #3b82f6;
            --text-dark: #0f172a;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.02), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }

        * { font-family: 'Plus Jakarta Sans', sans-serif !important; }
        body { background-color: var(--bg-body); color: #334155; overflow-x: hidden; }
        .layout-wrapper { display: flex; min-height: 100vh; }
        
        /* SIDEBAR PANEL STYLE */
        .sidebar-panel { 
            width: 280px; background: var(--sidebar-bg); flex-shrink: 0; 
            display: flex; flex-direction: column; justify-content: space-between; 
            padding: 30px 20px; box-shadow: 10px 0 30px rgba(15, 23, 42, 0.05);
            position: sticky; top: 0; height: 100vh;
        }
        .brand-section { padding: 0 12px 25px 12px; border-bottom: 1px solid rgba(255,255,255,0.06); flex-shrink: 0; }
        .brand-title { font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px; }
        .brand-title span { color: var(--sidebar-active); }
        .brand-subtitle { font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 4px; }

        .menu-container { overflow-y: auto; flex-grow: 1; padding-right: 8px; margin-top: 20px; }
        .menu-container::-webkit-scrollbar { width: 5px; }
        .menu-container::-webkit-scrollbar-track { background: transparent; }
        .menu-container::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }

        .section-header { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 1.5px; padding: 20px 12px 8px 12px; }
        .sidebar-panel .nav-link { color: var(--sidebar-color); font-size: 14px; font-weight: 500; padding: 12px 16px; display: flex; align-items: center; text-decoration: none; border-radius: 12px; margin-bottom: 4px; transition: all 0.2s ease; }
        .sidebar-panel .nav-link i { width: 24px; font-size: 16px; margin-right: 12px; text-align: center; }
        .sidebar-panel .nav-link:hover { color: #ffffff; background: rgba(255, 255, 255, 0.04); }
        .sidebar-panel .nav-link.active { background: var(--sidebar-active); color: #ffffff; font-weight: 600; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.35); }

        .logout-box { padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); flex-shrink: 0; }
        .logout-btn { color: #f87171 !important; font-weight: 600 !important; background: rgba(239, 68, 68, 0.05); }
        .logout-btn:hover { background: #ef4444 !important; color: #ffffff !important; }
        
        /* MAIN CANVAS */
        .main-canvas { flex-grow: 1; padding: 40px 50px; max-width: calc(100% - 280px); }
        .data-card-premium { background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: var(--card-shadow); overflow: hidden; margin-top: 20px; }
        .data-card-header { padding: 24px; background: #ffffff; border-bottom: 1px solid #f1f5f9; }
        .data-card-title { font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 0; }
        
        /* TABLE PREMIUM */
        .table-premium thead th { background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; }
        .table-premium tbody td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }

        /* TABS CUSTOM */
        .nav-tabs-custom {
            display: flex;
            gap: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            background: #ffffff;
            color: #64748b;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 20px;
            border-radius: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            border: 1px solid #e2e8f0;
        }
        .nav-tabs-custom .nav-link:hover {
            color: var(--sidebar-active);
            background: #f8fafc;
        }
        .nav-tabs-custom .nav-link.active {
            background: var(--sidebar-active);
            color: #ffffff;
            border-color: var(--sidebar-active);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
        
        .badge-status {
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="layout-wrapper">
    <!-- PANEL SIDEBAR -->
    <div class="sidebar-panel">
        <div class="brand-section">
            <div class="brand-title"><i class="bi bi-lightning-charge-fill"></i>SIBEO<span>.</span></div>
            <div class="brand-subtitle">WORKSHOP PANEL v2</div>
        </div>

        <div class="menu-container">
            <div class="section-header">UTAMA</div>
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i>Dashboard</a>

            <div class="section-header">Data Master</div>
            <a href="pelanggan.php" class="nav-link"><i class="bi bi-people-fill"></i>Pelanggan</a>
            <a href="suku_cadang.php" class="nav-link"><i class="bi bi-box-seam-fill"></i>Suku Cadang</a>
            <a href="mekanik.php" class="nav-link"><i class="bi bi-tools"></i>Mekanik</a>
            <a href="paket_layanan.php" class="nav-link"><i class="bi bi-tags-fill"></i>Paket Layanan</a>
            <a href="alat_kerja.php" class="nav-link"><i class="bi bi-wrench-adjustable-circle-fill"></i>Alat Kerja</a>
            <a href="stall.php" class="nav-link"><i class="bi bi-house-gear-fill"></i>Data Stall</a>
            
            <div class="section-header">OPERASIONAL</div>
            <a href="pengadaan.php" class="nav-link"><i class="bi bi-cart-plus-fill"></i>Pengadaan Stok</a>
            <a href="booking.php" class="nav-link"><i class="bi bi-calendar-check-fill"></i>Transaksi Booking</a>
            <a href="laporan.php" class="nav-link active"><i class="bi bi-graph-up-arrow"></i>Laporan Pelayanan</a>
            <a href="laporan_sparepart.php" class="nav-link"><i class="bi bi-box-seam"></i>Laporan Sparepart</a>
        </div>

        <div class="logout-box">
            <a href="../auth/logout.php" class="nav-link logout-btn" onclick="return confirm('Keluar dari aplikasi SIBEO?')">
                <i class="bi bi-power"></i>Log Out
            </a>
        </div>
    </div>

    <!-- MAIN CANVAS -->
    <div class="main-canvas">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Laporan Operasional & Analitik</h3>
                <p class="text-muted small m-0 mt-1">Data rekapitulasi servis, penggunaan inventaris, dan evaluasi kinerja mekanik.</p>
            </div>
            <button onclick="window.print()" class="btn btn-premium-primary">
                <i class="bi bi-printer-fill"></i> Cetak Laporan
            </button>
        </div>

        <!-- TABS NAV -->
        <ul class="nav nav-tabs-custom" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="servis-tab" data-bs-toggle="tab" data-bs-target="#servis" type="button" role="tab"><i class="bi bi-car-front me-2"></i>Servis Kendaraan</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sparepart-tab" data-bs-toggle="tab" data-bs-target="#sparepart" type="button" role="tab"><i class="bi bi-box-seam me-2"></i>Penggunaan Sparepart</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="alat-tab" data-bs-toggle="tab" data-bs-target="#alat" type="button" role="tab"><i class="bi bi-wrench me-2"></i>Peminjaman Alat</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="kinerja-tab" data-bs-toggle="tab" data-bs-target="#kinerja" type="button" role="tab"><i class="bi bi-person-badge me-2"></i>Kinerja Mekanik (UDF)</button>
            </li>
        </ul>

        <!-- TAB CONTENT -->
        <div class="tab-content" id="reportTabsContent">
            
            <!-- 1. LAPORAN SERVIS KENDARAAN -->
            <div class="tab-pane fade show active" id="servis" role="tabpanel">
                <div class="data-card-premium">
                    <div class="data-card-header">
                        <h5 class="data-card-title"><i class="bi bi-card-checklist text-primary me-2"></i>Riwayat Pelayanan Servis Kendaraan</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Servis</th>
                                    <th>Pelanggan</th>
                                    <th>Kendaraan</th>
                                    <th>Jenis Servis</th>
                                    <th>Mekanik</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query_servis = mysqli_query($koneksi, "
                                    SELECT b.tanggal_servis, p.nama_lengkap AS nama_pelanggan, 
                                           CONCAT(k.merk, ' (', k.nomor_polisi, ')') AS nama_kendaraan,
                                           pl.nama_paket AS jenis_servis, 
                                           IFNULL(m.nama, '-') AS nama_mekanik, 
                                           b.status
                                    FROM tbl_booking b
                                    LEFT JOIN tbl_pelanggan p ON b.id_pelanggan = p.id_pelanggan
                                    LEFT JOIN tbl_kendaraan k ON b.id_kendaraan = k.id_kendaraan
                                    LEFT JOIN tbl_paket_layanan pl ON b.id_paket = pl.id_paket
                                    LEFT JOIN tbl_mekanik m ON b.id_mekanik = m.id_mekanik
                                    ORDER BY b.tanggal_servis DESC, b.id_booking DESC
                                ");
                                if ($query_servis && mysqli_num_rows($query_servis) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_servis)) {
                                        $st = strtolower($row['status']);
                                        $badge_class = 'bg-secondary text-white';
                                        if($st == 'selesai') $badge_class = 'bg-success text-white';
                                        elseif($st == 'proses' || $st == 'dalam_proses') $badge_class = 'bg-warning text-dark';
                                        elseif($st == 'terkonfirmasi') $badge_class = 'bg-info text-dark';
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><span class="fw-bold text-dark"><?= date('d F Y', strtotime($row['tanggal_servis'])); ?></span></td>
                                            <td><?= safe_text($row['nama_pelanggan']); ?></td>
                                            <td><code class="text-dark fw-bold"><?= safe_text($row['nama_kendaraan']); ?></code></td>
                                            <td><?= safe_text($row['jenis_servis']); ?></td>
                                            <td><i class="bi bi-person-fill text-muted me-1"></i><?= safe_text($row['nama_mekanik']); ?></td>
                                            <td><span class="badge badge-status <?= $badge_class; ?>"><?= safe_text($row['status']); ?></span></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center py-4 text-muted'>Belum ada riwayat pelayanan servis.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. LAPORAN PENGGUNAAN SPAREPART -->
            <div class="tab-pane fade" id="sparepart" role="tabpanel">
                <div class="data-card-premium">
                    <div class="data-card-header d-flex justify-content-between align-items-center">
                        <h5 class="data-card-title m-0"><i class="bi bi-gears text-primary me-2"></i>Rekapitulasi Penggunaan Suku Cadang</h5>
                        <a href="laporan_sparepart.php" class="btn btn-sm btn-primary" style="border-radius: 8px; font-weight: 600;"><i class="bi bi-box-seam me-1"></i> Buka Laporan Detail & Filter</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Sparepart</th>
                                    <th class="text-center">Jumlah Digunakan</th>
                                    <th>Harga Satuan</th>
                                    <th>Total Biaya</th>
                                    <th>Sisa Stok Gudang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query_sparepart = mysqli_query($koneksi, "
                                    SELECT sc.nama_part, sc.kode_part, 
                                           IFNULL(SUM(pcs.jumlah_pakai), 0) AS total_pakai, 
                                           sc.harga_satuan, 
                                           sc.stok AS sisa_stok
                                    FROM tbl_suku_cadang sc
                                    LEFT JOIN tbl_pengerjaan_suku_cadang pcs ON sc.id_suku_cadang = pcs.id_suku_cadang
                                    GROUP BY sc.id_suku_cadang
                                    ORDER BY total_pakai DESC, sc.nama_part ASC
                                ");
                                if ($query_sparepart && mysqli_num_rows($query_sparepart) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_sparepart)) {
                                        $total_biaya = $row['total_pakai'] * $row['harga_satuan'];
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td>
                                                <strong class="text-dark"><?= safe_text($row['nama_part']); ?></strong>
                                                <br><small class="text-muted"><?= safe_text($row['kode_part']); ?></small>
                                            </td>
                                            <td class="text-center fw-bold text-primary"><?= $row['total_pakai']; ?> Unit</td>
                                            <td>Rp <?= number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                                            <td class="fw-bold text-dark">Rp <?= number_format($total_biaya, 0, ',', '.'); ?></td>
                                            <td>
                                                <?php if($row['sisa_stok'] <= 5): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold"><?= $row['sisa_stok']; ?> Unit (Kritis)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold"><?= $row['sisa_stok']; ?> Unit</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Belum ada data sparepart.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. LAPORAN PEMINJAMAN ALAT -->
            <div class="tab-pane fade" id="alat" role="tabpanel">
                <div class="data-card-premium">
                    <div class="data-card-header">
                        <h5 class="data-card-title"><i class="bi bi-tools text-primary me-2"></i>Log Aktivitas Peminjaman Alat Kerja</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Alat</th>
                                    <th>Mekanik Peminjam</th>
                                    <th>Waktu Pinjam</th>
                                    <th>Waktu Kembali</th>
                                    <th>Kondisi Pengembalian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query_alat = mysqli_query($koneksi, "
                                    SELECT ak.nama_alat, ak.kode_alat, m.nama AS nama_mekanik, 
                                           pa.waktu_pinjam, pa.waktu_kembali, pa.status,
                                           IFNULL(pa.kondisi_kembali, '-') AS kondisi_alat
                                    FROM tbl_peminjaman_alat pa
                                    JOIN tbl_alat_kerja ak ON pa.id_alat = ak.id_alat
                                    JOIN tbl_pengerjaan p ON pa.id_pengerjaan = p.id_pengerjaan
                                    JOIN tbl_mekanik m ON p.id_mekanik = m.id_mekanik
                                    ORDER BY pa.waktu_pinjam DESC
                                ");
                                if ($query_alat && mysqli_num_rows($query_alat) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_alat)) {
                                        $kond = strtolower($row['kondisi_alat']);
                                        $badge_class = 'bg-secondary text-white';
                                        if($kond == 'baik') $badge_class = 'bg-success text-white';
                                        elseif($kond == 'rusak_ringan') $badge_class = 'bg-warning text-dark';
                                        elseif($kond == 'rusak_berat') $badge_class = 'bg-danger text-white';
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td>
                                                <strong class="text-dark"><?= safe_text($row['nama_alat']); ?></strong>
                                                <br><small class="text-muted"><?= safe_text($row['kode_alat']); ?></small>
                                            </td>
                                            <td><i class="bi bi-person-fill text-muted me-1"></i><?= safe_text($row['nama_mekanik']); ?></td>
                                            <td><?= date('d M Y H:i', strtotime($row['waktu_pinjam'])); ?></td>
                                            <td><?= ($row['status'] == 'dikembalikan') ? date('d M Y H:i', strtotime($row['waktu_kembali'])) : '<span class="text-warning fw-bold">Masih Dipinjam</span>'; ?></td>
                                            <td>
                                                <?php if($row['status'] == 'dipinjam'): ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-bold">DIPINJAM</span>
                                                <?php else: ?>
                                                    <span class="badge badge-status <?= $badge_class; ?>"><?= str_replace('_', ' ', $row['kondisi_alat']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Belum ada data peminjaman alat kerja.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 4. LAPORAN KINERJA MEKANIK -->
            <div class="tab-pane fade" id="kinerja" role="tabpanel">
                <div class="data-card-premium">
                    <div class="data-card-header">
                        <h5 class="data-card-title"><i class="bi bi-person-lines-fill text-primary me-2"></i>Evaluasi Kinerja Mekanik Mahasiswa</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Mekanik</th>
                                    <th>Shift Kerja</th>
                                    <th class="text-center">Jumlah Servis Selesai</th>
                                    <th class="text-center">Rata-rata Durasi Servis (UDF)</th>
                                    <th class="text-center">Alat Rusak / Hilang</th>
                                    <th class="text-center">Nilai Kinerja (UDF)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                // Menghitung durasi rata-rata & jumlah kerusakan alat menggunakan MySQL Function UDF
                                $query_kinerja = mysqli_query($koneksi, "
                                    SELECT m.id_mekanik, m.nama AS nama_mekanik, m.shift,
                                           (SELECT COUNT(*) FROM tbl_pengerjaan p WHERE p.id_mekanik = m.id_mekanik AND p.status = 'Selesai') AS jumlah_servis,
                                           (SELECT IFNULL(ROUND(AVG(fn_hitung_durasi_servis(p.id_pengerjaan)), 0), 0) FROM tbl_pengerjaan p WHERE p.id_mekanik = m.id_mekanik AND p.status = 'Selesai') AS avg_durasi,
                                           (SELECT IFNULL(SUM(pa.jumlah_pinjam), 0) FROM tbl_peminjaman_alat pa JOIN tbl_pengerjaan p ON pa.id_pengerjaan = p.id_pengerjaan WHERE p.id_mekanik = m.id_mekanik AND pa.status = 'dikembalikan' AND pa.kondisi_kembali IN ('rusak_ringan', 'rusak_berat')) AS alat_rusak,
                                           fn_hitung_nilai_mekanik(m.id_mekanik) AS nilai_kinerja
                                    FROM tbl_mekanik m
                                    ORDER BY nilai_kinerja DESC, m.nama ASC
                                ");
                                if ($query_kinerja && mysqli_num_rows($query_kinerja) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_kinerja)) {
                                        $nk = $row['nilai_kinerja'];
                                        $badge_nk = 'bg-success text-white';
                                        if($nk < 30) $badge_nk = 'bg-danger text-white';
                                        elseif($nk < 70) $badge_nk = 'bg-warning text-dark';
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><strong class="text-dark"><?= safe_text($row['nama_mekanik']); ?></strong></td>
                                            <td><span class="badge bg-light text-dark border px-2.5 py-1 text-capitalize fw-bold"><?= safe_text($row['shift']); ?></span></td>
                                            <td class="text-center fw-bold text-dark"><?= $row['jumlah_servis']; ?> Servis</td>
                                            <td class="text-center fw-bold text-secondary"><?= $row['avg_durasi']; ?> Menit</td>
                                            <td class="text-center text-danger fw-bold"><?= $row['alat_rusak']; ?> Unit</td>
                                            <td class="text-center">
                                                <span class="badge badge-status <?= $badge_nk; ?> px-3 py-1.5 fs-7"><?= $nk; ?> Poin</span>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center py-4 text-muted'>Belum ada data kinerja mekanik.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
