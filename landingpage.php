<?php
$koneksi = $koneksi ?? null;

foreach ([__DIR__ . '/config/koneksi.php', __DIR__ . '/../config/koneksi.php'] as $file) {
  if (file_exists($file)) {
    require_once $file;
    break;
  }
}

function e($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function rupiah($value) {
  return 'Rp ' . number_format((float)$value, 0, ',', '.');
}

function pendek($text, $limit = 120) {
  $text = trim(strip_tags((string)$text));
  if ($text === '') return 'Detail paket layanan mengikuti data master paket di database SIBEO.';

  if (function_exists('mb_strlen') && function_exists('mb_substr')) {
    return mb_strlen($text, 'UTF-8') > $limit ? mb_substr($text, 0, $limit, 'UTF-8') . '...' : $text;
  }

  return strlen($text) > $limit ? substr($text, 0, $limit) . '...' : $text;
}

function paket_icon($nama) {
  $nama = strtolower((string)$nama);
  if (str_contains($nama, 'oli')) return '🛢️';
  if (str_contains($nama, 'besar') || str_contains($nama, 'tune')) return '⚙️';
  if (str_contains($nama, 'rem') || str_contains($nama, 'ban')) return '🛞';
  if (str_contains($nama, 'scan') || str_contains($nama, 'elektronik')) return '📊';
  if (str_contains($nama, 'cuci') || str_contains($nama, 'bersih')) return '✨';
  return '🔧';
}

function paket_label($nama, $index) {
  $nama = strtolower((string)$nama);
  if (str_contains($nama, 'oli')) return 'Paling Sering Dipilih';
  if (str_contains($nama, 'besar') || str_contains($nama, 'lengkap') || str_contains($nama, 'tune')) return 'Paket Lengkap';
  if (str_contains($nama, 'berkala') || str_contains($nama, 'ringan') || str_contains($nama, 'rutin')) return 'Paket Ringan';
  return 'Paket Layanan';
}

function paket_items($deskripsi) {
  $text = trim(strip_tags((string)$deskripsi));
  $items = $text ? preg_split('/(?:\r\n|\r|\n|;|•|\.)+/u', $text) : [];
  $items = array_values(array_filter(array_map('trim', $items ?: [])));

  if (count($items) < 2 && $text) {
    $items = array_values(array_filter(array_map('trim', preg_split('/,\s*/u', $text))));
  }

  if (!$items) {
    $items = [
      'Cakupan layanan mengikuti data master',
      'Harga jasa tersimpan di database',
      'Dapat dipilih saat proses booking'
    ];
  }

  while (count($items) < 3) {
    $items[] = count($items) === 1 ? 'Harga jasa tercatat jelas' : 'Dapat dipilih saat proses booking';
  }

  return array_slice($items, 0, 4);
}

$paket_layanan = [];

if (isset($koneksi) && $koneksi instanceof mysqli) {
  mysqli_set_charset($koneksi, 'utf8mb4');
  $query = mysqli_query($koneksi, "SELECT id_paket, nama_paket, deskripsi, harga FROM tbl_paket_layanan ORDER BY id_paket ASC");

  if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
      $paket_layanan[] = $row;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Landing Page - SIBEO</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Space+Grotesk:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --navy: #071b52;
      --blue: #2563eb;
      --muted: #60708f;
      --line: #d9e2f2;
      --soft: #f7f9fc;
      --soft-blue: #eef4ff;
      --shadow: 0 14px 34px rgba(7, 27, 82, .08);
    }

    body {
      color: var(--navy);
      font-family: "Space Grotesk", sans-serif;
      background: linear-gradient(180deg, #fff 0%, #f7faff 50%, #eef4ff 100%);
      overflow-x: hidden;
    }

    .display {
      font-family: "Archivo Black", sans-serif;
      letter-spacing: -.04em;
    }

    .mono {
      font-family: "IBM Plex Mono", monospace;
    }

    .container-xl {
      max-width: 1180px;
    }

    .navbar-wrap {
      position: sticky;
      top: 0;
      z-index: 20;
      padding-top: 14px;
      background: rgba(255, 255, 255, .88);
      backdrop-filter: blur(12px);
    }

    .navbar-box,
    .card-ui,
    .popup {
      border: 1px solid var(--line);
      border-radius: 20px;
      background: #fff;
      box-shadow: var(--shadow);
    }

    .brand-logo {
      width: 46px;
      height: 46px;
      object-fit: cover;
      border-radius: 13px;
      border: 1px solid var(--line);
    }

    .brand-fallback {
      width: 46px;
      height: 46px;
      display: grid;
      place-items: center;
      border-radius: 13px;
      color: #fff;
      background: var(--navy);
      font-family: "Archivo Black", sans-serif;
      font-size: 27px;
    }

    .brand-sub {
      color: var(--muted);
      font-size: 10px;
      letter-spacing: 1.2px;
    }

    .nav-link {
      color: var(--navy) !important;
      font-size: 13.5px;
      font-weight: 700;
      opacity: .75;
    }

    .nav-link:hover,
    .nav-link.active {
      color: var(--blue) !important;
      opacity: 1;
    }

    .btn-main,
    .btn-ghost {
      border-radius: 14px;
      font-weight: 800;
      transition: .18s ease;
    }

    .btn-main {
      color: #fff;
      background: var(--navy);
      border: 1px solid var(--navy);
      box-shadow: 0 10px 22px rgba(7, 27, 82, .14);
    }

    .btn-main:hover {
      color: #fff;
      background: #0b2a6f;
      transform: translateY(-2px);
    }

    .btn-ghost {
      color: var(--navy);
      background: #fff;
      border: 1px solid var(--line);
    }

    .btn-ghost:hover {
      color: var(--blue);
      background: var(--soft-blue);
      transform: translateY(-2px);
    }

    .hero {
      padding: 76px 0 44px;
    }

    .badge-soft {
      width: fit-content;
      padding: 8px 12px;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--blue);
      background: #fff;
      box-shadow: var(--shadow);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: .9px;
      text-transform: uppercase;
    }

    .hero-title {
      font-size: clamp(44px, 6.4vw, 82px);
      line-height: .92;
    }

    .hero-title span {
      color: var(--blue);
    }

    .text-muted-ui {
      color: var(--muted);
      line-height: 1.7;
    }

    .hero-photo {
      position: relative;
      aspect-ratio: 4 / 3;
      border-radius: 28px;
      overflow: hidden;
      border: 1px solid var(--line);
      box-shadow: var(--shadow);
      background: #fff;
    }

    .hero-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .hero-badge {
      position: absolute;
      right: 18px;
      bottom: 18px;
      max-width: 250px;
      padding: 14px 16px;
      border-radius: 18px;
      color: #fff;
      background: rgba(7, 27, 82, .94);
      font-weight: 800;
      line-height: 1.2;
    }

    .float-chip {
      position: absolute;
      z-index: 2;
      padding: 9px 12px;
      border: 1px solid var(--line);
      border-radius: 999px;
      background: #fff;
      box-shadow: var(--shadow);
      color: var(--navy);
      font-size: 12px;
      font-weight: 800;
    }

    .float-chip.c1 { top: 26px; left: -4px; }
    .float-chip.c2 { right: -4px; top: 92px; }
    .float-chip.c3 { left: 18px; bottom: 48px; }

    .marquee {
      overflow: hidden;
      white-space: nowrap;
      color: #fff;
      background: var(--navy);
    }

    .marquee-track {
      display: inline-flex;
      animation: marquee 32s linear infinite;
    }

    .marquee-dot {
      width: 5px;
      height: 5px;
      margin: 0 18px;
      border-radius: 999px;
      background: rgba(255, 255, 255, .72);
      display: inline-flex;
    }

    .section-pad {
      padding: 86px 0;
    }

    .section-soft {
      background: var(--soft);
      border-block: 1px solid var(--line);
    }

    .section-head {
      max-width: 690px;
      margin: 0 auto 44px;
      text-align: center;
    }

    .section-label {
      color: var(--blue);
      font-size: 12px;
      font-weight: 900;
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    .section-title {
      font-size: clamp(30px, 4vw, 44px);
      line-height: 1.05;
    }

    .card-ui {
      height: 100%;
      padding: 28px;
      color: var(--navy);
      transition: .18s ease;
    }

    .card-ui:hover {
      transform: translateY(-6px);
      border-color: rgba(37, 99, 235, .34);
      box-shadow: 0 18px 42px rgba(7, 27, 82, .12);
    }

    button.card-ui {
      width: 100%;
      text-align: left;
    }

    .icon-box {
      width: 64px;
      height: 64px;
      display: grid;
      place-items: center;
      border-radius: 18px;
      color: #fff;
      background: var(--navy);
      font-size: 27px;
    }

    .mini-icon {
      width: 54px;
      height: 54px;
      flex: 0 0 auto;
      display: grid;
      place-items: center;
      border-radius: 16px;
      color: #fff;
      background: var(--navy);
      font-size: 23px;
    }

    .step-number,
    .package-label {
      color: var(--blue);
      font-size: 11px;
      font-weight: 900;
      letter-spacing: .6px;
      text-transform: uppercase;
    }

    .package-label {
      width: fit-content;
      padding: 7px 11px;
      border-radius: 999px;
      background: var(--soft-blue);
    }

    .featured {
      color: #fff;
      background: var(--navy);
      border-color: var(--navy);
      transform: translateY(-10px);
    }

    .featured:hover {
      transform: translateY(-16px);
    }

    .featured .text-muted-ui,
    .featured .package-label,
    .featured li,
    .featured .price-small {
      color: rgba(255, 255, 255, .78) !important;
    }

    .featured .icon-box,
    .featured .check {
      color: var(--navy);
      background: #fff;
    }

    .check-list {
      margin: 24px 0;
      padding-top: 20px;
      border-top: 1px solid var(--line);
      display: grid;
      gap: 11px;
      color: var(--muted);
      font-size: 14px;
    }

    .featured .check-list {
      border-top-color: rgba(255, 255, 255, .18);
    }

    .check-list li {
      display: flex;
      gap: 10px;
      align-items: flex-start;
    }

    .check {
      width: 20px;
      height: 20px;
      flex: 0 0 auto;
      display: grid;
      place-items: center;
      border-radius: 999px;
      color: #fff;
      background: var(--blue);
      font-size: 12px;
      font-weight: 900;
    }

    .price {
      font-size: 28px;
      font-weight: 900;
    }

    .price-small {
      color: var(--muted);
      font-size: 12px;
      font-weight: 800;
    }

    .overlay {
      position: fixed;
      inset: 0;
      z-index: 98;
      opacity: 0;
      pointer-events: none;
      background: rgba(7, 27, 82, .32);
      backdrop-filter: blur(8px);
      transition: .2s ease;
    }

    .overlay.open {
      opacity: 1;
      pointer-events: auto;
    }

    .popup {
      position: fixed;
      left: 50%;
      top: 50%;
      z-index: 99;
      width: min(420px, calc(100vw - 32px));
      padding: 24px;
      opacity: 0;
      pointer-events: none;
      transform: translate(-50%, -50%) scale(.96);
      transition: .2s ease;
    }

    .popup.open {
      opacity: 1;
      pointer-events: auto;
      transform: translate(-50%, -50%) scale(1);
    }

    .popup-line {
      width: 74px;
      height: 4px;
      margin: 14px 0;
      border-radius: 999px;
      background: var(--blue);
    }

    .popup-pills span {
      padding: 7px 11px;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--blue);
      background: var(--soft-blue);
      font-size: 11px;
      font-weight: 800;
    }

    .btn-close-x {
      width: 36px;
      height: 36px;
      border: 0;
      border-radius: 12px;
      color: #fff;
      background: var(--navy);
      font-weight: 900;
    }

    .reveal {
      opacity: 0;
      transform: translateY(18px);
      transition: .5s ease;
    }

    .reveal.in {
      opacity: 1;
      transform: translateY(0);
    }

    @keyframes marquee {
      to { transform: translateX(-50%); }
    }

    @media (max-width: 991.98px) {
      .navbar-collapse,
      .float-chip {
        display: none !important;
      }

      .hero {
        padding-top: 54px;
      }

      .featured,
      .featured:hover {
        transform: none;
      }
    }

    @media (max-width: 575.98px) {
      .hero-title {
        font-size: 44px;
      }

      .section-pad {
        padding: 64px 0;
      }

      .section-head {
        text-align: left;
      }

      .hero-badge {
        left: 14px;
        right: 14px;
        max-width: none;
      }
    }
  </style>
</head>

<body>
  <header class="navbar-wrap">
    <div class="container container-xl">
      <nav class="navbar navbar-expand-md navbar-box px-3 px-md-4">
        <a class="navbar-brand d-flex align-items-center gap-3 m-0" href="#">
          <img src="img/logo.jpeg" alt="SIBEO Logo" class="brand-logo" onerror="this.replaceWith(Object.assign(document.createElement('div'), {className:'brand-fallback', textContent:'S'}))">
          <div>
            <div class="display fs-5 lh-1">SIBEO</div>
            <div class="brand-sub mono text-uppercase">Bengkel Otomotif</div>
          </div>
        </a>

        <div class="collapse navbar-collapse justify-content-center">
          <nav class="navbar-nav gap-4">
            <a class="nav-link active" href="#">Beranda</a>
            <a class="nav-link" href="#alur">Cara Kerja</a>
            <a class="nav-link" href="#manfaat">Kenapa SIBEO</a>
            <a class="nav-link" href="#paket">Paket Servis</a>
          </nav>
        </div>

        <a href="auth/login.php" class="btn btn-main mono py-2 px-3 px-md-4">→ Masuk</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero">
      <div class="container container-xl">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <div class="badge-soft mono mb-3">Platform Booking Servis</div>
            <h1 class="display hero-title mb-4">Booking<br>Bengkel<br><span>Otomotif.</span></h1>
            <p class="text-muted-ui fs-6 mb-4">Tinggalkan antrean manual. Pilih kendaraan, paket servis, dan jadwal sesukamu, lalu pantau progres pengerjaannya secara langsung di SIBEO.</p>
            <div class="d-flex flex-wrap gap-3">
              <a href="auth/login.php" class="btn btn-main py-3 px-4">→ Masuk Ke Sistem</a>
              <a href="#alur" class="btn btn-ghost py-3 px-4">Lihat Cara Kerja</a>
            </div>
          </div>

          <div class="col-lg-6 position-relative">
            <div class="float-chip c1 mono">Status real-time</div>
            <div class="float-chip c2 mono">Booking cepat</div>
            <div class="float-chip c3 mono">Biaya jelas</div>
            <div class="hero-photo">
              <img src="img/gambarlp.jpeg" alt="Mekanik sedang servis kendaraan">
              <div class="hero-badge">Servis cepat, rapi, dan jelas</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="marquee py-3">
      <div class="marquee-track mono fw-bold text-uppercase">
        <?php for ($i = 0; $i < 2; $i++): ?>
          <span>BOOKING TANPA ANTRE<i class="marquee-dot"></i>PANTAU STATUS REAL-TIME<i class="marquee-dot"></i>PILIH JADWAL SENDIRI<i class="marquee-dot"></i>RIWAYAT SERVIS TERSIMPAN<i class="marquee-dot"></i>RINCIAN BIAYA JELAS<i class="marquee-dot"></i>KHUSUS CIVITAS KAMPUS<i class="marquee-dot"></i></span>
        <?php endfor; ?>
      </div>
    </div>

    <section id="alur" class="section-pad">
      <div class="container container-xl">
        <div class="section-head reveal">
          <div class="section-label mono mb-2">Cara Kerja</div>
          <h2 class="display section-title mb-3">Mudah, Cepat, Terpantau</h2>
          <p class="text-muted-ui">Empat langkah sederhana dari booking sampai kendaraan selesai diservis.</p>
        </div>

        <div class="row g-4 reveal">
          <?php
          $steps = [
            ['📅', 'Langkah 01', 'Booking Mandiri', 'Pilih kendaraan, paket servis, tanggal, dan jam yang kamu inginkan.'],
            ['✓', 'Langkah 02', 'Jadwal Dikonfirmasi', 'Kami konfirmasi jadwalmu dan menyiapkan mekanik serta peralatan.'],
            ['🔧', 'Langkah 03', 'Pantau Progresnya', 'Pantau progres pengerjaan secara real-time langsung di SIBEO.'],
            ['💳', 'Langkah 04', 'Bayar & Selesai', 'Lakukan pembayaran mudah, ambil kendaraanmu, dan siap jalan.']
          ];

          foreach ($steps as $step):
          ?>
            <div class="col-md-6 col-lg-3">
              <article class="card-ui text-center">
                <div class="icon-box mx-auto mb-3"><?= e($step[0]) ?></div>
                <span class="step-number mono"><?= e($step[1]) ?></span>
                <h3 class="h5 fw-bold mt-2"><?= e($step[2]) ?></h3>
                <p class="text-muted-ui small mb-0"><?= e($step[3]) ?></p>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section id="manfaat" class="section-pad section-soft">
      <div class="container container-xl">
        <div class="section-head reveal">
          <div class="section-label mono mb-2">Kenapa SIBEO</div>
          <h2 class="display section-title mb-3">Lebih Praktis, Lebih Pasti</h2>
          <p class="text-muted-ui">SIBEO membantu pengguna booking servis, memantau progres, melihat rincian biaya, dan menyimpan riwayat servis.</p>
        </div>

        <div class="row g-4 reveal">
          <?php
          $features = [
            ['📲', 'Booking Tanpa Antre', 'Booking online dan datang sesuai jadwalmu.', 'Pilih jadwal yang kosong langsung dari HP, tanpa harus datang dulu untuk daftar antrean.', 'Booking cepat|Tanpa antre|Pilih jadwal'],
            ['📊', 'Status Selalu Jelas', 'Pantau progres servis dari mulai dikerjakan hingga selesai.', 'Pantau status kendaraan dari menunggu, dikerjakan, sampai selesai.', 'Realtime|Transparan|Mudah dipantau'],
            ['🚘', 'Banyak Kendaraan', 'Simpan beberapa kendaraan dalam satu akun.', 'Booking berikutnya tinggal pilih kendaraan tanpa input ulang.', 'Multi kendaraan|Satu akun|Praktis'],
            ['🧾', 'Biaya Transparan', 'Rincian biaya jelas sejak awal.', 'Harga paket dan sparepart tercatat jelas.', 'Jelas|Tanpa biaya tersembunyi|Rinci'],
            ['🗂️', 'Riwayat Tersimpan', 'Semua riwayat servis tersimpan aman.', 'Pengguna mudah mengecek servis sebelumnya.', 'Riwayat|Tersimpan|Siap cek'],
            ['🎓', 'Civitas Kampus', 'Layanan dibuat untuk lingkungan kampus.', 'Mahasiswa dan dosen punya alur booking yang lebih sesuai.', 'Kampus|Mahasiswa|Dosen']
          ];

          foreach ($features as $feature):
          ?>
            <div class="col-md-6 col-lg-4">
              <button type="button" class="card-ui popup-card" data-popup-title="<?= e($feature[1]) ?>" data-popup-body="<?= e($feature[3]) ?>" data-popup-pills="<?= e($feature[4]) ?>">
                <div class="d-flex gap-3">
                  <div class="mini-icon"><?= e($feature[0]) ?></div>
                  <div>
                    <h3 class="h5 fw-bold"><?= e($feature[1]) ?></h3>
                    <p class="text-muted-ui small mb-2"><?= e($feature[2]) ?></p>
                    <span class="small fw-bold text-primary">Klik untuk detail →</span>
                  </div>
                </div>
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section id="paket" class="section-pad">
      <div class="container container-xl">
        <div class="section-head reveal">
          <div class="section-label mono mb-2">Pilihan Layanan</div>
          <h2 class="display section-title mb-3">Pilih Paket Servis yang Sesuai</h2>
          <p class="text-muted-ui">Data paket tampil langsung dari master paket layanan di database.</p>
        </div>

        <div class="row g-4 align-items-stretch reveal">
          <?php if ($paket_layanan): ?>
            <?php foreach ($paket_layanan as $index => $paket):
              $nama = $paket['nama_paket'] ?? '';
              $deskripsi = $paket['deskripsi'] ?? '';
              $harga = $paket['harga'] ?? 0;
              $featured = str_contains(strtolower($nama), 'oli') || (count($paket_layanan) >= 3 && $index === 1);
              $pills = paket_label($nama, $index) . '|Harga ' . rupiah($harga) . '|Data master';
            ?>
              <div class="col-md-6 col-xl-4">
                <button type="button" class="card-ui popup-card <?= $featured ? 'featured' : '' ?>" data-popup-title="<?= e($nama) ?>" data-popup-body="<?= e($deskripsi ?: 'Detail paket layanan mengikuti data master paket di database SIBEO.') ?>" data-popup-pills="<?= e($pills) ?>">
                  <div class="icon-box mb-3"><?= e(paket_icon($nama)) ?></div>
                  <div class="package-label mono mb-3"><?= e(paket_label($nama, $index)) ?></div>
                  <h3 class="display fs-3 mb-2"><?= e($nama) ?></h3>
                  <p class="text-muted-ui small"><?= e(pendek($deskripsi)) ?></p>

                  <ul class="check-list list-unstyled">
                    <?php foreach (paket_items($deskripsi) as $item): ?>
                      <li><span class="check">✓</span><span><?= e($item) ?></span></li>
                    <?php endforeach; ?>
                  </ul>

                  <div class="d-flex align-items-end justify-content-between gap-3">
                    <div>
                      <div class="price-small mono">Mulai dari</div>
                      <div class="price"><?= e(rupiah($harga)) ?></div>
                    </div>
                    <span class="btn <?= $featured ? 'btn-light' : 'btn-ghost' ?> px-3">Pilih Paket</span>
                  </div>
                </button>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <div class="card-ui text-center">
                <div class="icon-box mx-auto mb-3">🔧</div>
                <div class="package-label mono mx-auto mb-3">Data Kosong</div>
                <h3 class="display fs-3">Belum Ada Paket Layanan</h3>
                <p class="text-muted-ui mx-auto" style="max-width: 560px;">Tambahkan paket dari halaman master paket layanan, lalu refresh landing page.</p>
                <a href="auth/login.php" class="btn btn-main px-4 py-3 mt-2">Masuk Ke Sistem</a>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <div class="overlay" id="overlay"></div>

  <div class="popup" id="popup" aria-hidden="true">
    <div class="d-flex align-items-start justify-content-between gap-3">
      <div>
        <div class="mono text-uppercase small text-primary fw-bold mb-2">Ruang Informasi</div>
        <h3 class="h4 fw-bold" id="popupTitle">Info</h3>
      </div>
      <button type="button" class="btn-close-x" id="popupClose">×</button>
    </div>

    <div class="popup-line"></div>
    <p class="text-muted-ui mb-3" id="popupBody">Klik kartu untuk melihat detail.</p>
    <div class="popup-pills d-flex flex-wrap gap-2" id="popupPills"></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const io = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: .12 });

    document.querySelectorAll('.reveal').forEach(el => io.observe(el));

    const popup = document.getElementById('popup');
    const overlay = document.getElementById('overlay');
    const popupTitle = document.getElementById('popupTitle');
    const popupBody = document.getElementById('popupBody');
    const popupPills = document.getElementById('popupPills');

    function closePopup() {
      popup.classList.remove('open');
      overlay.classList.remove('open');
      popup.setAttribute('aria-hidden', 'true');
    }

    document.querySelectorAll('.popup-card').forEach(card => {
      card.addEventListener('click', () => {
        popupTitle.textContent = card.dataset.popupTitle || 'Info';
        popupBody.textContent = card.dataset.popupBody || '';
        popupPills.innerHTML = (card.dataset.popupPills || '').split('|').filter(Boolean).map(pill => `<span>${pill}</span>`).join('');
        popup.classList.add('open');
        overlay.classList.add('open');
        popup.setAttribute('aria-hidden', 'false');
      });
    });

    document.getElementById('popupClose').addEventListener('click', closePopup);
    overlay.addEventListener('click', closePopup);
    document.addEventListener('keydown', event => event.key === 'Escape' && closePopup());
  </script>
</body>
</html>
