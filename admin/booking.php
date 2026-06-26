<?php
session_start();
include "../config/koneksi.php";

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking - SIBEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        /* SideBar Admin (100% Persis File Kendaraan, Pelanggan & Dashboard) */
        .sidebar {
            background: #111625; height: 100vh; position: fixed;
            top: 0; left: 0; bottom: 0; z-index: 999;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .sidebar-brand-wrapper { padding: 24px 24px 10px 24px; }
        .sidebar-brand { font-size: 22px; font-weight: 800; letter-spacing: 1.5px; color: #38bdf8; }
        .sidebar-subtitle { font-size: 9px; font-weight: 700; letter-spacing: 1px; color: #475569; margin-top: 2px; text-transform: uppercase; }
        .nav-section-title { font-size: 10px; font-weight: 800; color: #334155; text-transform: uppercase; letter-spacing: 1.5px; padding: 24px 24px 10px 24px; }
        .sidebar .nav-link { color: #94a3b8; font-size: 14px; font-weight: 600; padding: 12px 24px; display: flex; align-items: center; transition: all 0.2s ease; border-left: 4px solid transparent; text-decoration: none; }
        .sidebar .nav-link i { font-size: 16px; width: 28px; color: #64748b; }
        .sidebar .nav-link:hover { color: #38bdf8; background: rgba(56, 189, 248, 0.04); }
        .sidebar .nav-link.active { background: rgba(59, 130, 246, 0.12); color: #3b82f6; font-weight: 700; border-left-color: #3b82f6; }
        .sidebar .nav-link.active i { color: #3b82f6; }
        .logout-link { color: #ef4444 !important; font-weight: 700 !important; }
        .logout-link:hover { background: rgba(239, 68, 68, 0.08) !important; }
        
        .main-workspace { margin-left: 16.666667%; padding: 40px; }
        
        /* Premium Table Component */
        .table-premium { background: white; border-radius: 24px; box-shadow: 0 4px 18px rgba(148, 163, 184, 0.08); padding: 30px; }
        .table-premium thead th { background-color: #f8fafc; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 16px 20px; border-bottom: none; }
        .table-premium tbody td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; color: #475569; font-size: 14px; }

        /* Badge Status */
        .badge-sibeo { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; display: inline-block; text-transform: uppercase; }
        .bg-pending { background-color: #fef3c7; color: #d97706; }
        .bg-konfirmasi { background-color: #e0f2fe; color: #0369a1; }
        .bg-proses { background-color: #e0e7ff; color: #4338ca; }
        .bg-selesai { background-color: #dcfce7; color: #15803d; }

        /* Form Modal Vertikal */
        .modal-custom-content { border-radius: 24px !important; border: none !important; padding: 10px; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.1); }
        .form-label-custom { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .form-control-custom { border: 1px solid #cbd5e1 !important; border-radius: 12px !important; padding: 11px 16px !important; background-color: #f8fafc !important; font-size: 14px !important; color: #0f172a !important; }
        .form-control-custom:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important; background-color: #ffffff !important; outline: none; }
        
        .btn-submit-gradient { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none; color: white; font-weight: 600; font-size: 14px; padding: 12px 24px; border-radius: 12px; transition: all 0.2s ease; }
        .btn-submit-gradient:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); color: white; }
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
                    <a href="mekanik.php" class="nav-link"><i class="fa-solid fa-clipboard-user me-3"></i>Data Mekanik</a>
                    <a href="kendaraan.php" class="nav-link"><i class="fa-solid fa-car me-3"></i>Data Kendaraan</a>
                </div>
                <div class="nav-section-title">MENU OPERASIONAL</div>
                <div class="nav flex-column">
                    <a href="booking.php" class="nav-link active"><i class="fa-solid fa-calendar-check me-3"></i>Data Booking</a>
                    <a href="alat_kerja.php" class="nav-link"><i class="fa-solid fa-toolbox me-3"></i>Alat Kerja</a>
                    <a href="stall.php" class="nav-link"><i class="fa-solid fa-circle-dot me-3"></i>Data Stall</a>
                </div>
            </div>
            <div class="mb-3 pt-2">
                <div class="nav flex-column">
                    <a href="../auth/logout.php" class="nav-link logout-link" onclick="return confirm('Keluar?')"><i class="fa-solid fa-sign-out-alt me-3"></i>Keluar</a>
                </div>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 main-workspace">
            <div class="mb-4">
                <h2 class="fw-bold m-0" style="color: #0f172a; letter-spacing: -0.5px;">Workflow Alur Antrean Servis</h2>
                <p class="text-muted small m-0 mt-1">Siklus kendali penuh operasional pendaftaran, penugasan teknisi, hingga motor selesai diservis.</p>
            </div>

            <div class="table-premium">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">NO</th>
                                <th>KODE</th>
                                <th>PELANGGAN / MOTOR</th>
                                <th>PENUGASAN BENGKEL</th>
                                <th>ESTIMASI WAKTU</th>
                                <th class="text-center">STATUS WORKFLOW</th>
                                <th class="text-center" style="width: 180px;">AKSI OPERASIONAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            
                            // QUERY DIUBAH MENJADI s.* UNTUK MENGHINDARI SALAH NAMA KOLOM STALL
                            $query_booking = mysqli_query($koneksi, "SELECT b.*, 
                                m.nama AS nama_mekanik,
                                s.*,
                                p.nama_lengkap AS nama_p,
                                k.merk AS merk_k, k.nomor_polisi AS nopol_k
                                FROM tbl_booking b 
                                LEFT JOIN tbl_mekanik m ON b.id_mekanik = m.id_mekanik
                                LEFT JOIN tbl_stall s ON b.id_stall = s.id_stall
                                LEFT JOIN tbl_pelanggan p ON b.id_pelanggan = p.id_pelanggan
                                LEFT JOIN tbl_kendaraan k ON b.id_kendaraan = k.id_kendaraan
                                ORDER BY b.id_booking DESC");
                            
                            while ($data = mysqli_fetch_assoc($query_booking)) {
                                $status = $data['status'] ?? 'pending';
                                $badge_class = "bg-pending";
                                if($status == 'konfirmasi') $badge_class = "bg-konfirmasi";
                                if($status == 'proses') $badge_class = "bg-proses";
                                if($status == 'selesai') $badge_class = "bg-selesai";

                                $tampil_nama = !empty($data['nama_p']) ? $data['nama_p'] : ($data['nama_pelanggan'] ?? 'Pelanggan');
                                $tampil_merk = !empty($data['merk_k']) ? $data['merk_k'] : ($data['merk'] ?? ($data['tipe_motor'] ?? 'Motor'));
                                $tampil_nopol = !empty($data['nopol_k']) ? $data['nopol_k'] : ($data['nomor_polisi'] ?? ($data['plat_nomor'] ?? '-'));
                                
                                // DETEKSI CERDAS NAMA LAJUR STALL (Mencegah Undefined Key)
                                $nama_lajur_stall = $data['nama_stall'] ?? ($data['lajur'] ?? ($data['nama'] ?? ('Stall #' . ($data['id_stall'] ?? ''))));
                                ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                    <td class="fw-bold text-primary"><?= $data['kode_booking']; ?></td>
                                    <td>
                                        <div class="fw-semibold" style="color: #0f172a;"><?= htmlspecialchars($tampil_nama); ?></div>
                                        <div class="text-muted small" style="font-size: 12px;"><?= htmlspecialchars($tampil_merk); ?> - <?= htmlspecialchars($tampil_nopol); ?></div>
                                    </td>
                                    <td>
                                        <div class="small mb-1">
                                            <i class="fa-solid fa-user-gear text-primary me-1" style="width:14px;"></i> 
                                            <?= !empty($data['nama_mekanik']) ? htmlspecialchars($data['nama_mekanik']) : '<span class="text-danger fw-semibold">Belum Set</span>'; ?>
                                        </div>
                                        <div class="small text-secondary">
                                            <i class="fa-solid fa-circle-dot text-indigo me-1" style="width:14px; color:#4f46e5;"></i> 
                                            Lajur: <?= (!empty($data['id_stall']) && $data['id_stall'] != 0) ? htmlspecialchars($nama_lajur_stall) : '<span class="text-muted">-</span>'; ?>
                                        </div>
                                    </td>
                                    <td class="small">
                                        <?php if(!empty($data['jam_mulai']) && $data['jam_mulai'] != '00:00:00'): ?>
                                            <div><i class="fa-regular fa-clock text-success me-1"></i> Mulai: <?= date('H:i', strtotime($data['jam_mulai'])); ?></div>
                                            <div class="text-muted"><i class="fa-regular fa-clock text-danger me-1"></i> Selesai: <?= date('H:i', strtotime($data['jam_selesai'])); ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-sibeo <?= $badge_class; ?>"><?= $status; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if($status == 'pending'): ?>
                                            <a href="proses_booking.php?action=konfirmasi&id=<?= $data['id_booking']; ?>" class="btn btn-sm btn-light text-primary fw-bold rounded-3 px-3 py-1.5" onclick="return confirm('Konfirmasi pendaftaran ini?')">
                                                <i class="fa-solid fa-check me-1"></i> Konfirmasi
                                            </a>
                                        <?php elseif($status == 'konfirmasi'): ?>
                                            <button class="btn btn-sm btn-light fw-bold rounded-3 px-3 py-1.5 btn-trigger-assign" 
                                                    style="color: #4f46e5;"
                                                    data-id="<?= $data['id_booking']; ?>" 
                                                    data-kode="<?= $data['kode_booking']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalWorkflow">
                                                <i class="fa-solid fa-user-plus me-1"></i> Set Mekanik & Stall
                                            </button>
                                        <?php elseif($status == 'proses'): ?>
                                            <a href="proses_booking.php?action=selesai&id=<?= $data['id_booking']; ?>" class="btn btn-sm btn-success fw-bold text-white rounded-3 px-3 py-1.5" onclick="return confirm('Nyatakan pendaftaran servis ini telah selesai dikerjakan?')">
                                                <i class="fa-solid fa-circle-check me-1"></i> Selesai Servis
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small fw-medium"><i class="fa-solid fa-lock text-success me-1"></i> Terkunci</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalWorkflow" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-custom-content">
            <div class="modal-header border-0 pb-0 pt-3 px-4">
                <div>
                    <h5 class="fw-bold m-0" style="color: #0f172a;">Setup Penugasan Operasional</h5>
                    <p class="text-muted small m-0 mt-1">Tentukan mekanik, area lajur pengerjaan, dan estimasi waktu kerja.</p>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="proses_booking.php?action=assign" method="POST">
                <input type="hidden" name="id_booking" id="modal-id-booking">
                
                <div class="modal-body px-4 pt-4">
                    <div class="mb-3">
                        <label class="form-label-custom">KODE BOOKING TERPILIH</label>
                        <input type="text" id="modal-kode-booking" class="form-control form-control-custom fw-bold text-primary" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label-custom">MEKANIK YANG BERTUGAS</label>
                        <select name="id_mekanik" class="form-select form-control-custom" required>
                            <option value="">-- Pilih Nama Mekanik --</option>
                            <?php
                            $q_mekanik = mysqli_query($koneksi, "SELECT id_mekanik, nama FROM tbl_mekanik ORDER BY nama ASC");
                            while($m = mysqli_fetch_assoc($q_mekanik)) {
                                echo "<option value='".$m['id_mekanik']."'>".htmlspecialchars($m['nama'])."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-custom">LAJUR AREA KERJA (STALL)</label>
                        <select name="id_stall" class="form-select form-control-custom" required>
                            <option value="">-- Pilih Lajur Stall Bengkel --</option>
                            <?php
                            $q_stall = mysqli_query($koneksi, "SELECT * FROM tbl_stall");
                            while($s = mysqli_fetch_assoc($q_stall)) {
                                $nama_s = $s['nama_stall'] ?? ($s['lajur'] ?? ($s['nama'] ?? 'Stall '.$s['id_stall']));
                                echo "<option value='".$s['id_stall']."'>".htmlspecialchars($nama_s)."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <div class="mb-2">
                                <label class="form-label-custom">ESTIMASI MULAI</label>
                                <input type="time" name="jam_mulai" class="form-control form-control-custom" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <label class="form-label-custom">ESTIMASI SELESAI</label>
                                <input type="time" name="jam_selesai" class="form-control form-control-custom" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 px-4 pt-3 pb-2 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-light fw-semibold text-muted rounded-3 px-4 py-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-submit-gradient">Mulai Pengerjaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).on("click", ".btn-trigger-assign", function () {
        $("#modal-id-booking").val($(this).data('id'));
        $("#modal-kode-booking").val($(this).data('kode'));
    });
</script>
</body>
</html>