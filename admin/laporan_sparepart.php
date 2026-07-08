<?php
session_start();
include "../config/koneksi.php";

$current_page = 'laporan_sparepart.php';

// Safe text escape function
function safe_text($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Initialise filters
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Sanitise inputs for SQL
$start_date_db = mysqli_real_escape_string($koneksi, $tanggal_mulai);
$end_date_db = mysqli_real_escape_string($koneksi, $tanggal_akhir);

// Build WHERE SQL
$where_clauses = ["b.tanggal_servis BETWEEN '$start_date_db' AND '$end_date_db'"];

if (!empty($search)) {
    $search_db = mysqli_real_escape_string($koneksi, $search);
    $where_clauses[] = "(sc.nama_part LIKE '%$search_db%' OR sc.kode_part LIKE '%$search_db%' OR b.kode_booking LIKE '%$search_db%' OR p.nama_lengkap LIKE '%$search_db%')";
}

$where_sql = implode(" AND ", $where_clauses);

// ==========================================
// KURS DATA RINGKASAN (SUMMARY CARDS)
// ==========================================
$query_summary = mysqli_query($koneksi, "
    SELECT 
        IFNULL(SUM(pcs.jumlah_pakai), 0) AS total_qty,
        IFNULL(SUM(pcs.jumlah_pakai * pcs.harga_satuan), 0) AS total_nominal,
        COUNT(DISTINCT pcs.id_suku_cadang) AS unique_parts
    FROM tbl_pengerjaan_suku_cadang pcs
    JOIN tbl_pengerjaan pg ON pcs.id_pengerjaan = pg.id_pengerjaan
    JOIN tbl_booking b ON pg.id_booking = b.id_booking
    JOIN tbl_suku_cadang sc ON pcs.id_suku_cadang = sc.id_suku_cadang
    LEFT JOIN tbl_pelanggan p ON b.id_pelanggan = p.id_pelanggan
    WHERE $where_sql
");
$summary_data = mysqli_fetch_assoc($query_summary);

// Hitung stok kritis (real-time)
$query_kritis = mysqli_query($koneksi, "SELECT COUNT(*) AS total_kritis FROM tbl_suku_cadang WHERE stok <= 5");
$kritis_data = mysqli_fetch_assoc($query_kritis);

// Cari Sparepart Terlaris (Top Part) di Periode Ini
$query_top = mysqli_query($koneksi, "
    SELECT sc.nama_part, SUM(pcs.jumlah_pakai) AS total_pakai
    FROM tbl_pengerjaan_suku_cadang pcs
    JOIN tbl_pengerjaan pg ON pcs.id_pengerjaan = pg.id_pengerjaan
    JOIN tbl_booking b ON pg.id_booking = b.id_booking
    JOIN tbl_suku_cadang sc ON pcs.id_suku_cadang = sc.id_suku_cadang
    LEFT JOIN tbl_pelanggan p ON b.id_pelanggan = p.id_pelanggan
    WHERE $where_sql
    GROUP BY pcs.id_suku_cadang
    ORDER BY total_pakai DESC
    LIMIT 1
");
$top_part = (mysqli_num_rows($query_top) > 0) ? mysqli_fetch_assoc($query_top) : ['nama_part' => '-', 'total_pakai' => 0];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penggunaan Sparepart - SIBEO</title>
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

        /* BUTTON PREMIUM */
        .btn-premium-primary { background-color: var(--sidebar-active); color: #ffffff; border: none; border-radius: 10px; padding: 10px 20px; font-size: 13.5px; font-weight: 600; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px; }
        .btn-premium-primary:hover { background-color: #2563eb; color: #ffffff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2); }

        /* STATS CARD */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        /* PRINT STYLE */
        @media print {
            body { background: #ffffff !important; color: #000000 !important; }
            .sidebar-panel, .no-print, .filter-card, .btn-premium-primary { display: none !important; }
            .main-canvas { max-width: 100% !important; padding: 0 !important; margin: 0 !important; width: 100% !important; }
            .data-card-premium { border: none !important; box-shadow: none !important; margin-top: 0 !important; }
            .table-premium thead th { background: #f1f5f9 !important; color: #000000 !important; border-bottom: 2px solid #000000 !important; }
            .table-premium tbody td { border-bottom: 1px solid #e2e8f0 !important; }
            .print-header { display: block !important; }
        }
        
        .print-header { display: none; margin-bottom: 30px; text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; }
        .print-header h2 { margin: 0; font-weight: 800; }
        .print-header p { margin: 5px 0 0 0; font-size: 14px; }
        
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
    </style>
</head>
<body>

<div class="layout-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-canvas">
        
        <div class="print-header">
            <h2>BENGKEL SIBEO (SYSTEM INTEGRATION BENGKEL OTOMOTIF)</h2>
            <p>Laporan Penggunaan Suku Cadang (Sparepart) Bengkel</p>
            <p class="small text-secondary">Periode: <?= date('d M Y', strtotime($tanggal_mulai)); ?> s.d. <?= date('d M Y', strtotime($tanggal_akhir)); ?></p>
        </div>

        <!-- Header Screen -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h3 class="fw-bold text-dark m-0">Laporan Penggunaan Sparepart</h3>
                <p class="text-muted small m-0 mt-1">Pantau grafik pemakaian, pengeluaran stok, serta total omset penjualan suku cadang.</p>
            </div>
            <button onclick="window.print()" class="btn btn-premium-primary">
                <i class="bi bi-printer-fill"></i> Cetak Laporan
            </button>
        </div>

        <!-- Summary Cards Grid -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Nominal Terpakai</span>
                        <h4 class="fw-bold text-dark mb-0">Rp <?= number_format($summary_data['total_nominal'], 0, ',', '.'); ?></h4>
                        <small class="text-muted">Total nilai suku cadang keluar</small>
                    </div>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Qty Terpakai</span>
                        <h4 class="fw-bold text-dark mb-0"><?= number_format($summary_data['total_qty'], 0, ',', '.'); ?> Pcs</h4>
                        <small class="text-muted">Dari <?= $summary_data['unique_parts']; ?> jenis sparepart</small>
                    </div>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Terlaris (Top Part)</span>
                        <h4 class="fw-bold text-dark text-truncate mb-0" style="max-width: 160px;" title="<?= safe_text($top_part['nama_part']); ?>">
                            <?= safe_text($top_part['nama_part']); ?>
                        </h4>
                        <small class="text-success fw-bold"><?= $top_part['total_pakai']; ?> Pcs terpakai</small>
                    </div>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">Stok Kritis (&le; 5)</span>
                        <h4 class="fw-bold mb-0 <?= $kritis_data['total_kritis'] > 0 ? 'text-danger' : 'text-dark'; ?>">
                            <?= $kritis_data['total_kritis']; ?> Item
                        </h4>
                        <small class="text-muted">Perlu restock segera</small>
                    </div>
                    <div class="stat-icon <?= $kritis_data['total_kritis'] > 0 ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary'; ?>">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card data-card-premium no-print filter-card border-0 mb-4" style="margin-top: 0;">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-funnel-fill text-primary me-2"></i>Filter Laporan</h6>
                <form method="GET" action="laporan_sparepart.php" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="<?= $tanggal_mulai; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" class="form-control" value="<?= $tanggal_akhir; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Cari Sparepart / Booking / Pelanggan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Kode Part, Nama Part, Kode Booking..." value="<?= safe_text($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-premium-primary w-100 justify-content-center py-2.5">
                            Filter
                        </button>
                        <a href="laporan_sparepart.php" class="btn btn-light border py-2.5 px-3 text-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabs Nav -->
        <ul class="nav nav-tabs-custom mb-2 no-print" id="sparepartReportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="log-tab" data-bs-toggle="tab" data-bs-target="#log" type="button" role="tab">
                    <i class="bi bi-list-task me-2"></i>Log Penggunaan Suku Cadang
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rekap-tab" data-bs-toggle="tab" data-bs-target="#rekap" type="button" role="tab">
                    <i class="bi bi-bar-chart-steps me-2"></i>Rekap per Sparepart
                </button>
            </li>
        </ul>

        <!-- Tab Contents -->
        <div class="tab-content" id="sparepartReportTabsContent">
            
            <!-- TAB 1: LOG PENGGUNAAN SPAREPART -->
            <div class="tab-pane fade show active" id="log" role="tabpanel">
                <div class="data-card-premium" style="margin-top: 0;">
                    <div class="data-card-header d-flex justify-content-between align-items-center">
                        <h5 class="data-card-title"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Penggunaan Suku Cadang</h5>
                        <span class="badge bg-light text-secondary border px-3 py-2 no-print">
                            Periode: <?= date('d M Y', strtotime($tanggal_mulai)); ?> - <?= date('d M Y', strtotime($tanggal_akhir)); ?>
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Tanggal</th>
                                    <th>Kode Booking</th>
                                    <th>Suku Cadang</th>
                                    <th>Pelanggan</th>
                                    <th>Mekanik</th>
                                    <th class="text-center">Qty</th>
                                    <th>Harga Satuan</th>
                                    <th>Total Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query_log = mysqli_query($koneksi, "
                                    SELECT 
                                        b.tanggal_servis, 
                                        b.kode_booking, 
                                        sc.kode_part, 
                                        sc.nama_part, 
                                        p.nama_lengkap AS nama_pelanggan, 
                                        m.nama AS nama_mekanik, 
                                        pcs.jumlah_pakai, 
                                        pcs.harga_satuan, 
                                        (pcs.jumlah_pakai * pcs.harga_satuan) AS total_harga
                                    FROM tbl_pengerjaan_suku_cadang pcs
                                    JOIN tbl_pengerjaan pg ON pcs.id_pengerjaan = pg.id_pengerjaan
                                    JOIN tbl_booking b ON pg.id_booking = b.id_booking
                                    JOIN tbl_suku_cadang sc ON pcs.id_suku_cadang = sc.id_suku_cadang
                                    LEFT JOIN tbl_pelanggan p ON b.id_pelanggan = p.id_pelanggan
                                    LEFT JOIN tbl_mekanik m ON pg.id_mekanik = m.id_mekanik
                                    WHERE $where_sql
                                    ORDER BY b.tanggal_servis DESC, pcs.id DESC
                                ");
                                if ($query_log && mysqli_num_rows($query_log) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_log)) {
                                        ?>
                                        <tr>
                                            <td class="text-center text-secondary fw-semibold"><?= $no++; ?></td>
                                            <td><span class="fw-bold text-dark"><?= date('d F Y', strtotime($row['tanggal_servis'])); ?></span></td>
                                            <td><span class="badge bg-light text-primary border border-primary-subtle px-2 py-1 fw-bold"><?= safe_text($row['kode_booking']); ?></span></td>
                                            <td>
                                                <strong class="text-dark"><?= safe_text($row['nama_part']); ?></strong>
                                                <br><small class="text-muted"><?= safe_text($row['kode_part']); ?></small>
                                            </td>
                                            <td><?= safe_text($row['nama_pelanggan']); ?></td>
                                            <td><i class="bi bi-person-fill text-muted me-1"></i><?= safe_text($row['nama_mekanik']); ?></td>
                                            <td class="text-center fw-bold text-primary"><?= $row['jumlah_pakai']; ?> Pcs</td>
                                            <td>Rp <?= number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                                            <td class="fw-bold text-dark">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center py-5 text-muted'><i class='bi bi-inbox d-block fs-3 mb-2 opacity-50'></i>Tidak ada riwayat pemakaian sparepart yang sesuai filter.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: REKAP AGREGAT PER SPAREPART -->
            <div class="tab-pane fade" id="rekap" role="tabpanel">
                <div class="data-card-premium" style="margin-top: 0;">
                    <div class="data-card-header d-flex justify-content-between align-items-center">
                        <h5 class="data-card-title"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Rekap Total Penggunaan per Suku Cadang</h5>
                        <span class="badge bg-light text-secondary border px-3 py-2 no-print">
                            Diurutkan dari Paling Sering Dipakai
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th>Kode Part</th>
                                    <th>Nama Suku Cadang</th>
                                    <th class="text-center">Total Frekuensi Pengerjaan</th>
                                    <th class="text-center">Total Jumlah Terpakai</th>
                                    <th>Rata-rata Harga Jual</th>
                                    <th>Total Pendapatan/Penggunaan</th>
                                    <th>Stok Gudang Sekarang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no_rekap = 1;
                                $query_rekap = mysqli_query($koneksi, "
                                    SELECT 
                                        sc.kode_part, 
                                        sc.nama_part, 
                                        COUNT(pcs.id) AS frekuensi, 
                                        SUM(pcs.jumlah_pakai) AS total_pakai, 
                                        AVG(pcs.harga_satuan) AS avg_harga, 
                                        SUM(pcs.jumlah_pakai * pcs.harga_satuan) AS total_nominal,
                                        sc.stok AS sisa_stok
                                    FROM tbl_suku_cadang sc
                                    JOIN tbl_pengerjaan_suku_cadang pcs ON sc.id_suku_cadang = pcs.id_suku_cadang
                                    JOIN tbl_pengerjaan pg ON pcs.id_pengerjaan = pg.id_pengerjaan
                                    JOIN tbl_booking b ON pg.id_booking = b.id_booking
                                    LEFT JOIN tbl_pelanggan p ON b.id_pelanggan = p.id_pelanggan
                                    WHERE $where_sql
                                    GROUP BY sc.id_suku_cadang
                                    ORDER BY total_pakai DESC, sc.nama_part ASC
                                ");
                                if ($query_rekap && mysqli_num_rows($query_rekap) > 0) {
                                    while ($row = mysqli_fetch_assoc($query_rekap)) {
                                        ?>
                                        <tr>
                                            <td class="text-center text-secondary fw-semibold"><?= $no_rekap++; ?></td>
                                            <td><span class="badge bg-light text-primary border border-primary-subtle px-2 py-1 fw-bold"><?= safe_text($row['kode_part']); ?></span></td>
                                            <td><strong class="text-dark"><?= safe_text($row['nama_part']); ?></strong></td>
                                            <td class="text-center fw-semibold text-secondary"><?= $row['frekuensi']; ?> Kali</td>
                                            <td class="text-center fw-bold text-primary"><?= $row['total_pakai']; ?> Pcs</td>
                                            <td>Rp <?= number_format($row['avg_harga'], 0, ',', '.'); ?></td>
                                            <td class="fw-bold text-dark">Rp <?= number_format($row['total_nominal'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php if($row['sisa_stok'] <= 5): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold"><?= $row['sisa_stok']; ?> Pcs (Kritis)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold"><?= $row['sisa_stok']; ?> Pcs</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center py-5 text-muted'><i class='bi bi-inbox d-block fs-3 mb-2 opacity-50'></i>Tidak ada rekap data sparepart yang sesuai filter.</td></tr>";
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
