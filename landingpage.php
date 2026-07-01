<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIBEO — Booking Servis Kendaraan Kampus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2 family=Archivo+Black&family=Space+Grotesk:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  
  <style>
    /* Variabel Warna */
    :root {
      --ink: #101820;
      --paper: #F4F2EC;
      --gray: #6c757d;
      --paper-dim: #EAE6DC;
      --safety: #FFB200;
      --blue: rgb(5, 39, 94);
      --line-strong: rgba(16,24,32,0.28);
    }
    body {
      background: var(--paper);
      color: var(--ink);
      font-family: 'Space Grotesk', sans-serif;
      overflow-x: hidden;
    }
    .mono { font-family: 'IBM Plex Mono', monospace; }
    .display { font-family: 'Archivo Black', sans-serif; }
    
    /* Efek Tekstur Noise Lembut */
    .noise {
      pointer-events: none; position: fixed; inset: 0; z-index: 999; opacity: 0.035;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    }
    .hazard-strip { height: 8px; background: repeating-linear-gradient(135deg, var(--blue) 0 14px, var(--ink) 14px 28px); }
    
    /* Efek Underline SVG SIBEO */
    .underline-svg { position: relative; white-space: nowrap; }
    .underline-svg svg { position: absolute; left: 0; bottom: -10px; width: 100%; height: 14px; }

    /* Customisasi Tombol & Efek Shadow Brutalist */
    .btn-brutal {
      background: var(--ink); color: var(--paper); border: 1.5px solid var(--ink);
      box-shadow: 4px 4px 0 var(--blue); transition: all .18s ease;
    }
    .btn-brutal:hover { 
      transform: translate(-2px,-2px); 
      background: var(--paper); border-color: var(--gray);
      box-shadow: 6px 6px 0 var(--blue); color: var(--gray); }
    
    /* Komponen Tiket Real-time */
    .ticket { background: var(--ink); color: var(--paper); border-radius: 18px; transform: rotate(2.5deg); transition: transform .4s; }
    .ticket:hover { transform: rotate(0deg) translateY(-4px); }
    .ticket-stub { position: absolute; left: 50%; top: 0; transform: translate(-50%,-50%); width: 30px; height: 30px; background: var(--paper); border-radius: 50%; }
    
    /* Floating Chips Position */
    .float-chip { position: absolute; background: var(--paper); border: 1.5px solid var(--ink); box-shadow: 4px 4px 0 rgba(16,24,32,0.12); z-index: 10; font-size: 11.5px; }
    .float-chip.c1 { top: -20px; right: -10px; transform: rotate(-6deg); }
    .float-chip.c2 { bottom: 20px; left: -30px; transform: rotate(4deg); }

    /* Marquee Banner */
    .marquee { background: var(--ink); color: var(--paper); overflow: hidden; white-space: nowrap; }
    .marquee-track { display: inline-flex; animation: scroll 28s linear infinite; }
    @keyframes scroll { from { transform: translateX(0) } to { transform: translateX(-50%) } }

    /* Animasi Reveal on Scroll */
    .reveal { opacity: 0; transform: translateY(22px); transition: all .7s ease; }
    .reveal.in { opacity: 1; transform: translateY(0); }
  </style>
</head>
<body>

<div class="noise"></div>
<div class="hazard-strip"></div>

<header class="navbar navbar-expand-md sticky-top bg-light bg-opacity-75 border-bottom py-3" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
  <div class="container container-xl">
    <a class="navbar-brand d-flex align-items-center gap-2 display m-0 fs-5" href="#">
    <img src="img/logo.jpeg" alt="SIBEO Logo" width="40" height="40" class="d-inline-block align-text-top">
    <div>SIBEO <span class="d-block mono text-muted" style="font-size: 9px; letter-spacing: 1.5px;">Bengkel Otomotif</span></div>
    </a>
    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
      <nav class="navbar-nav gap-4 fw-semibold" style="font-size: 13.5px;">
        <a class="nav-link text-secondary" href="#">Beranda</a>
        <a class="nav-link text-secondary" href="#alur">Cara Kerja</a>
        <a class="nav-link text-secondary" href="#manfaat">Kenapa SIBEO</a>
        <a class="nav-link text-secondary" href="#paket">Paket Servis</a>
      </nav>
    </div>
    <a href="auth/login.php" class="btn btn-dark mono fw-medium py-2 px-3" style="font-size:12.5px;">Masuk Ke Sistem →</a>
  </div>
</header>

<section class="container container-xl py-5 my-4">
  <div class="row align-items-center g-5">
    <div class="col-md-7">
      <h1 class="display mb-4 m-0 fw-black" style="font-size: clamp(40px, 5vw, 64px); line-height: 0.98;">
        Sistem<br>Bengkel 
        <span class="underline-svg">Otomotif.
          <svg viewBox="0 0 200 14" preserveAspectRatio="none"><path d="M2 9 Q 50 2, 100 9 T 198 9" stroke="var(--blue)" stroke-width="5" fill="none" stroke-linecap="round"/></svg>
        </span>
      </h1>
      <p class="text-secondary mb-4 pb-2" style="max-width: 480px; font-size: 16.5px; line-height: 1.65;">
        Tinggalkan antrean manual. Pilih kendaraan, paket servis, dan jadwal sesukamu, lalu pantau progres pengerjaannya secara langsung di SIBEO.
      </p>
      <div class="d-flex gap-3 flex-wrap">
        <a href="auth/login.php" class="btn btn-brutal fw-bold py-3 px-4" style="font-size:14.5px;">Masuk Ke Sistem →</a>
        <a href="#alur" class="btn btn-outline-secondary fw-bold py-3 px-4" style="font-size:14.5px;">Lihat Cara Kerja</a>
      </div>
    </div>

    <div class="col-md-5 d-flex justify-content-center mt-5 mt-md-0">
      <div class="position-relative w-100" style="max-width: 380px;">
        <div class="float-chip c1 rounded-3 py-2 px-3 mono fw-bold"><span class="d-inline-block rounded-circle bg-danger me-2" style="width:8px; height:8px;"></span>Servismu Sedang Dikerjakan</div>
        
        <div class="ticket p-0 shadow-lg position-relative">
          <div class="ticket-stub"></div>
          <div class="p-4 pb-3 border-bottom border-secondary border-opacity-25 border-dashed" style="border-bottom-style: dashed !important;">
            <div class="d-flex justify-content-between align-items-start mb-4">
              <div class="display fs-6">SIBEO <span class="text-warning">/SERVIS</span></div>
              <div class="mono bg-warning text-dark fw-bold rounded px-2 py-1" style="font-size:10.5px;">ANTREAN B-04</div>
            </div>
            <div class="mb-3">
              <div class="mono text-muted text-uppercase mb-1" style="font-size:9.5px; letter-spacing:1px;">Nomor Polisi</div>
              <div class="d-inline-block mono fw-bold bg-warning text-dark px-3 py-1 border border-2 border-dark rounded fs-5" style="letter-spacing:2px;">D 4519 KAM</div>
            </div>
            <div class="mb-3">
              <div class="mono text-muted text-uppercase mb-1" style="font-size:9.5px; letter-spacing:1px;">Paket Layanan</div>
              <div class="fw-bold">Servis Berkala + Ganti Oli</div>
            </div>
            <div class="mb-0">
              <div class="mono text-muted text-uppercase mb-1" style="font-size:9.5px; letter-spacing:1px;">Jadwal</div>
              <div class="fw-bold" style="font-size:14px;">Kamis, 09:30 · Stall 3</div>
            </div>
          </div>
          <div class="p-4">
            <div class="mono fw-bold d-flex align-items-center gap-2" style="font-size:12px;">
              <span class="d-block rounded-circle bg-success" style="width:9px; height:9px; box-shadow:0 0 8px #5BD679;"></span> Sedang Dikerjakan — Rafi S.
            </div>
            <div class="progress mt-3 bg-secondary bg-opacity-25" style="height:6px;">
              <div class="progress-bar bg-warning" style="width: 62%"></div>
            </div>
          </div>
        </div>

    </div>
  </div>
</section>

<div class="marquee py-3 border-top border-bottom">
  <div class="marquee-track mono fw-bold text-uppercase" style="font-size:12.5px; letter-spacing:1px;">
    <span>BOOKING TANPA ANTRE <span class="text-warning px-3">/</span> PANTAU STATUS REAL-TIME <span class="text-warning px-3">/</span> PILIH JADWAL SENDIRI <span class="text-warning px-3">/</span> RIWAYAT SERVIS TERSIMPAN <span class="text-warning px-3">/</span> RINCIAN BIAYA JELAS <span class="text-warning px-3">/</span> KHUSUS CIVITAS KAMPUS <span class="text-warning px-3">/</span></span>
    <span>BOOKING TANPA ANTRE <span class="text-warning px-3">/</span> PANTAU STATUS REAL-TIME <span class="text-warning px-3">/</span> PILIH JADWAL SENDIRI <span class="text-warning px-3">/</span> RIWAYAT SERVIS TERSIMPAN <span class="text-warning px-3">/</span> RINCIAN BIAYA JELAS <span class="text-warning px-3">/</span> KHUSUS CIVITAS KAMPUS <span class="text-warning px-3">/</span></span>
  </div>
</div>

<section id="alur" class="container container-xl py-5 my-5">
  <div class="mb-5 reveal" style="max-width:640px;">
    <div class="mono text-uppercase fw-bold text-muted small mb-2 d-flex align-items-center gap-2"><span class="d-inline-block bg-warning" style="width:18px; height:2.5px;"></span>Cara Kerja</div>
    <h2 class="display fs-2">Empat langkah, dari booking sampai Kendaraan kamu kelar diservis.</h2>
    <p class="text-secondary small mt-3">Tidak perlu datang dulu cuma untuk daftar antrean. Semua bisa kamu lakukan dari HP, mulai dari pilih jadwal sampai bayar.</p>
  </div>

  <div class="row g-0 bg-white border border-secondary border-opacity-50 rounded-4 overflow-hidden reveal">
    <div class="col-sm-6 col-lg-3 p-4 border-end border-bottom border-secondary border-opacity-50 position-relative">
      <span class="mono text-muted small d-block mb-3">LANGKAH 01</span>
      <div class="d-flex align-items-center justify-content-center bg-light border border-secondary border-opacity-50 rounded-3 fs-4 mb-3" style="width:46px; height:46px;">📅</div>
      <h3 class="h6 fw-bold">Booking Mandiri</h3>
      <p class="text-secondary small mb-0">Masuk ke akun SIBEO, pilih kendaraanmu, paket servis, lalu tentukan tanggal &amp; jam yang kamu mau.</p>
    </div>
    <div class="col-sm-6 col-lg-3 p-4 border-end border-bottom border-secondary border-opacity-50 position-relative">
      <span class="mono text-muted small d-block mb-3">LANGKAH 02</span>
      <div class="d-flex align-items-center justify-content-center bg-light border border-secondary border-opacity-50 rounded-3 fs-4 mb-3" style="width:46px; height:46px;">✅</div>
      <h3 class="h6 fw-bold">Jadwal Dikonfirmasi</h3>
      <p class="text-secondary small mb-0">Kamu dapat kepastian stall dan mekanik yang akan menangani kendaraanmu — datang langsung tanpa antre lagi.</p>
    </div>
    <div class="col-sm-6 col-lg-3 p-4 border-end border-bottom border-secondary border-opacity-50 position-relative">
      <span class="mono text-muted small d-block mb-3">LANGKAH 03</span>
      <div class="d-flex align-items-center justify-content-center bg-light border border-secondary border-opacity-50 rounded-3 fs-4 mb-3" style="width:46px; height:46px;">🔧</div>
      <h3 class="h6 fw-bold">Pantau Progresnya</h3>
      <p class="text-secondary small mb-0">Lihat status pengerjaan dan suku cadang yang dipakai langsung dari akunmu, tanpa perlu mondar-mandir nanya.</p>
    </div>
    <div class="col-sm-6 col-lg-3 p-4 border-bottom border-secondary border-opacity-50 position-relative">
      <span class="mono text-muted small d-block mb-3">LANGKAH 04</span>
      <div class="d-flex align-items-center justify-content-center bg-light border border-secondary border-opacity-50 rounded-3 fs-4 mb-3" style="width:46px; height:46px;">💳</div>
      <h3 class="h6 fw-bold">Bayar &amp; Selesai</h3>
      <p class="text-secondary small mb-0">Rincian biaya paket dan sparepart otomatis terhitung jelas, kamu tinggal konfirmasi pembayaran.</p>
    </div>
  </div>
</section>

<section id="manfaat" class="py-5" style="background:var(--paper-dim);">
  <div class="container container-xl py-5">
    <div class="mb-5 reveal" style="max-width:640px;">
      <div class="mono text-uppercase fw-bold text-muted small mb-2 d-flex align-items-center gap-2"><span class="d-inline-block bg-warning" style="width:18px; height:2.5px;"></span>Kenapa SIBEO</div>
      <h2 class="display fs-2">Dibuat supaya servis Kendaraan nggak lagi ribet.</h2>
      <p class="text-secondary small mt-3">Semua hal yang biasanya bikin males ke bengkel kampus, sekarang ditangani sistem.</p>
    </div>

    <div class="row g-4 reveal">
      <div class="col-md-6 col-lg-4">
        <div class="bg-white border border-secondary border-opacity-50 rounded-4 p-4 position-relative h-100 shadow-sm">
          <span class="position-absolute top-0 end-0 p-3 mono text-muted small">01</span>
          <div class="fs-4 mb-3">📲</div><h3 class="h6 fw-bold">Booking Tanpa Antre</h3><p class="text-secondary small mb-0">Pilih jadwal yang kosong langsung dari HP, tanpa harus datang dulu cuma untuk daftar antrean.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="bg-white border border-secondary border-opacity-50 rounded-4 p-4 position-relative h-100 shadow-sm">
          <span class="position-absolute top-0 end-0 p-3 mono text-muted small">02</span>
          <div class="fs-4 mb-3">🔍</div><h3 class="h6 fw-bold">Status Selalu Jelas</h3><p class="text-secondary small mb-0">Tahu persis kendaraanmu sedang menunggu, dikerjakan, atau sudah selesai — tanpa perlu nelepon bengkel.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="bg-white border border-secondary border-opacity-50 rounded-4 p-4 position-relative h-100 shadow-sm">
          <span class="position-absolute top-0 end-0 p-3 mono text-muted small">03</span>
          <div class="fs-4 mb-3">🚘</div><h3 class="h6 fw-bold">Banyak Kendaraan</h3><p class="text-secondary small mb-0">Punya Kendaraan lebih dari satu? Daftarkan semua di akun yang sama, tinggal pilih saat booking.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="bg-white border border-secondary border-opacity-50 rounded-4 p-4 position-relative h-100 shadow-sm">
          <span class="position-absolute top-0 end-0 p-3 mono text-muted small">04</span>
          <div class="fs-4 mb-3">🧾</div><h3 class="h6 fw-bold">Biaya Transparan</h3><p class="text-secondary small mb-0">Harga paket dan sparepart yang dipakai tercatat jelas — tidak ada biaya kejutan saat bayar.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="bg-white border border-secondary border-opacity-50 rounded-4 p-4 position-relative h-100 shadow-sm">
          <span class="position-absolute top-0 end-0 p-3 mono text-muted small">05</span>
          <div class="fs-4 mb-3">🗂️</div><h3 class="h6 fw-bold">Riwayat Tersimpan</h3><p class="text-secondary small mb-0">Semua servis sebelumnya tersimpan di akunmu, jadi gampang dicek kapan terakhir ganti oli.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="bg-white border border-secondary border-opacity-50 rounded-4 p-4 position-relative h-100 shadow-sm">
          <span class="position-absolute top-0 end-0 p-3 mono text-muted small">06</span>
          <div class="fs-4 mb-3">🎓</div><h3 class="h6 fw-bold">Civitas Kampus</h3><p class="text-secondary small mb-0">Layanan ini dibuat untuk mahasiswa dan dosen — daftar pakai identitas kampusmu sendiri.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="paket" class="container container-xl py-5 my-5">
  <div class="mb-5 reveal" style="max-width:640px;">
    <div class="mono text-uppercase fw-bold text-muted small mb-2 d-flex align-items-center gap-2"><span class="d-inline-block bg-warning" style="width:18px; height:2.5px;"></span>Pilihan Layanan</div>
    <h2 class="display fs-2">Tinggal pilih paket sesuai kebutuhan Kendaraanmu.</h2>
    <p class="text-secondary small mt-3">Setiap paket sudah jelas cakupannya — kamu pilih saat booking, tanpa nego di tempat.</p>
  </div>

  <div class="row g-4 reveal">
    <div class="col-lg-4">
      <div class="bg-white border border-secondary border-opacity-50 rounded-4 p-4 d-flex flex-column h-100">
        <div class="mono text-muted text-uppercase fw-bold opacity-75 small mb-2" style="font-size:10.5px;">Paket Ringan</div>
        <h3 class="display fs-4 mb-3">Servis Berkala</h3>
        <p class="text-secondary small mb-4">Pengecekan rutin supaya Kendaraan tetap nyaman dipakai harian.</p>
        <ul class="list-unstyled mt-auto pt-3 border-top text-secondary small d-flex flex-column gap-2">
          <li><span class="text-dark fw-bold me-2">✓</span> Cek &amp; setel rem, rantai, oli</li>
          <li><span class="text-dark fw-bold me-2">✓</span> Pembersihan komponen utama</li>
          <li><span class="text-dark fw-bold me-2">✓</span> Cocok tiap 2–3 bulan sekali</li>
        </ul>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="bg-warning rounded-4 p-4 d-flex flex-column h-100 text-dark border border-dark">
        <div class="mono text-dark text-uppercase fw-bold opacity-75 small mb-2" style="font-size:10.5px;">Paling Sering Dipilih</div>
        <h3 class="display fs-4 mb-3">Ganti Oli + Servis</h3>
        <p class="small mb-4 opacity-75">Kombinasi servis berkala dan penggantian oli dalam satu kunjungan.</p>
        <ul class="list-unstyled mt-auto pt-3 border-top border-dark text-dark small d-flex flex-column gap-2">
          <li><span class="fw-bold me-2">✓</span> Semua item servis berkala</li>
          <li><span class="fw-bold me-2">✓</span> Ganti oli sesuai tipe Kendaraan</li>
          <li><span class="fw-bold me-2">✓</span> Estimasi waktu pengerjaan jelas</li>
        </ul>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="bg-dark text-white rounded-4 p-4 d-flex flex-column h-100">
        <div class="mono text-white-50 text-uppercase fw-bold small mb-2" style="font-size:10.5px;">Paket Lengkap</div>
        <h3 class="display fs-4 mb-3 text-white">Servis Besar</h3>
        <p class="small mb-4 text-white-50">Untuk Kendaraan yang butuh penanganan lebih menyeluruh.</p>
        <ul class="list-unstyled mt-auto pt-3 border-top border-secondary text-white-50 small d-flex flex-column gap-2">
          <li><span class="text-white fw-bold me-2">✓</span> Pengecekan mesin lebih detail</li>
          <li><span class="text-white fw-bold me-2">✓</span> Penggantian suku cadang bila perlu</li>
          <li><span class="text-white fw-bold me-2">✓</span> Rincian sparepart terpakai tercatat</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Efek Scroll Reveal dengan Intersection Observer
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target);} });
  }, {threshold:0.12});
  document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
</script>

</body>
</html>