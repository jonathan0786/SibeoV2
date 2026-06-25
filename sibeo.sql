-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Jun 2026 pada 02.40
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sibeo`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `id_admin` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_admin`
--

INSERT INTO `tbl_admin` (`id_admin`, `nama`, `username`, `password`) VALUES
(1, 'Administrator SIBEO', 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_alat_kerja`
--

CREATE TABLE `tbl_alat_kerja` (
  `id_alat` int(11) NOT NULL,
  `nama_alat` varchar(100) NOT NULL,
  `kode_alat` varchar(30) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 0,
  `kondisi` enum('baik','rusak') NOT NULL DEFAULT 'baik'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_booking`
--

CREATE TABLE `tbl_booking` (
  `id_booking` int(11) NOT NULL,
  `kode_booking` varchar(20) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_kendaraan` int(11) NOT NULL,
  `id_paket` int(11) NOT NULL,
  `id_stall` int(11) DEFAULT NULL,
  `id_mekanik` int(11) DEFAULT NULL,
  `tanggal_servis` date NOT NULL,
  `jam_servis` time NOT NULL,
  `keluhan` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_kendaraan`
--

CREATE TABLE `tbl_kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `nomor_polisi` varchar(12) NOT NULL,
  `merk` varchar(50) NOT NULL,
  `tipe` varchar(50) NOT NULL,
  `tahun_pembuatan` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_mekanik`
--

CREATE TABLE `tbl_mekanik` (
  `id_mekanik` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kepegawaian` varchar(20) NOT NULL,
  `spesialisasi` varchar(100) NOT NULL,
  `shift` enum('pagi','sore') NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_mekanik`
--

INSERT INTO `tbl_mekanik` (`id_mekanik`, `nama`, `kepegawaian`, `spesialisasi`, `shift`, `username`, `password`) VALUES
(1, 'Eko Mekanik', 'MK-001', 'Mesin & Oli', 'pagi', 'mekanik', 'mekanik');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_paket_layanan`
--

CREATE TABLE `tbl_paket_layanan` (
  `id_paket` int(11) NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_pelanggan`
--

CREATE TABLE `tbl_pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nomor_pelanggan` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `no_telepon` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `password` varchar(50) NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_pelanggan`
--

INSERT INTO `tbl_pelanggan` (`id_pelanggan`, `nomor_pelanggan`, `nama_lengkap`, `nik`, `no_telepon`, `email`, `alamat`, `password`, `status`, `created_at`) VALUES
(2, 'CS-002', 'Rayyan Abdurrahman Qadar', '0920250049', '81210822482', '', '', '', 'aktif', '2026-06-18 00:18:35'),
(3, 'CS-003', 'Daffa Hanif Muzaki', '0920250032', '81234567889', '', '', '', 'aktif', '2026-06-18 00:20:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_pembayaran`
--

CREATE TABLE `tbl_pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_booking` int(11) NOT NULL,
  `nomor_nota` varchar(20) NOT NULL,
  `biaya_jasa` decimal(12,2) NOT NULL DEFAULT 0.00,
  `biaya_suku_cadang` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_tagihan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `metode_pembayaran` varchar(30) DEFAULT NULL,
  `status` enum('belum_bayar','lunas') NOT NULL DEFAULT 'belum_bayar',
  `tanggal_bayar` datetime DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_peminjaman_alat`
--

CREATE TABLE `tbl_peminjaman_alat` (
  `id_peminjaman` int(11) NOT NULL,
  `id_pengerjaan` int(11) NOT NULL,
  `id_alat` int(11) NOT NULL,
  `jumlah_pinjam` int(11) NOT NULL DEFAULT 1,
  `waktu_pinjam` datetime NOT NULL,
  `waktu_kembali` datetime DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_pengerjaan`
--

CREATE TABLE `tbl_pengerjaan` (
  `id_pengerjaan` int(11) NOT NULL,
  `id_booking` int(11) NOT NULL,
  `id_mekanik` int(11) NOT NULL,
  `catatan_pemeriksaan` text DEFAULT NULL,
  `catatan_pengerjaan` text DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'dimulai',
  `waktu_mulai` datetime NOT NULL,
  `waktu_selesai` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_pengerjaan_suku_cadang`
--

CREATE TABLE `tbl_pengerjaan_suku_cadang` (
  `id` int(11) NOT NULL,
  `id_pengerjaan` int(11) NOT NULL,
  `id_suku_cadang` int(11) NOT NULL,
  `jumlah_pakai` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_stall`
--

CREATE TABLE `tbl_stall` (
  `id_stall` int(11) NOT NULL,
  `nomor_stall` varchar(10) NOT NULL,
  `keterangan` varchar(100) NOT NULL,
  `status` enum('tersedia','terpakai') NOT NULL DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_suku_cadang`
--

CREATE TABLE `tbl_suku_cadang` (
  `id_suku_cadang` int(11) NOT NULL,
  `nama_part` varchar(100) NOT NULL,
  `kode_part` varchar(30) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `harga_satuan` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_suku_cadang`
--

INSERT INTO `tbl_suku_cadang` (`id_suku_cadang`, `nama_part`, `kode_part`, `stok`, `harga_satuan`) VALUES
(1, '', '', 20, 0.00);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indeks untuk tabel `tbl_alat_kerja`
--
ALTER TABLE `tbl_alat_kerja`
  ADD PRIMARY KEY (`id_alat`),
  ADD UNIQUE KEY `kode_alat` (`kode_alat`);

--
-- Indeks untuk tabel `tbl_booking`
--
ALTER TABLE `tbl_booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_kendaraan` (`id_kendaraan`),
  ADD KEY `id_paket` (`id_paket`),
  ADD KEY `id_stall` (`id_stall`),
  ADD KEY `id_mekanik` (`id_mekanik`);

--
-- Indeks untuk tabel `tbl_kendaraan`
--
ALTER TABLE `tbl_kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indeks untuk tabel `tbl_mekanik`
--
ALTER TABLE `tbl_mekanik`
  ADD PRIMARY KEY (`id_mekanik`);

--
-- Indeks untuk tabel `tbl_paket_layanan`
--
ALTER TABLE `tbl_paket_layanan`
  ADD PRIMARY KEY (`id_paket`);

--
-- Indeks untuk tabel `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indeks untuk tabel `tbl_pembayaran`
--
ALTER TABLE `tbl_pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `nomor_nota` (`nomor_nota`),
  ADD KEY `id_booking` (`id_booking`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `tbl_peminjaman_alat`
--
ALTER TABLE `tbl_peminjaman_alat`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_pengerjaan` (`id_pengerjaan`),
  ADD KEY `id_alat` (`id_alat`);

--
-- Indeks untuk tabel `tbl_pengerjaan`
--
ALTER TABLE `tbl_pengerjaan`
  ADD PRIMARY KEY (`id_pengerjaan`),
  ADD KEY `id_booking` (`id_booking`),
  ADD KEY `id_mekanik` (`id_mekanik`);

--
-- Indeks untuk tabel `tbl_pengerjaan_suku_cadang`
--
ALTER TABLE `tbl_pengerjaan_suku_cadang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pengerjaan` (`id_pengerjaan`),
  ADD KEY `id_suku_cadang` (`id_suku_cadang`);

--
-- Indeks untuk tabel `tbl_stall`
--
ALTER TABLE `tbl_stall`
  ADD PRIMARY KEY (`id_stall`);

--
-- Indeks untuk tabel `tbl_suku_cadang`
--
ALTER TABLE `tbl_suku_cadang`
  ADD PRIMARY KEY (`id_suku_cadang`),
  ADD UNIQUE KEY `kode_part` (`kode_part`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tbl_alat_kerja`
--
ALTER TABLE `tbl_alat_kerja`
  MODIFY `id_alat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_booking`
--
ALTER TABLE `tbl_booking`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_kendaraan`
--
ALTER TABLE `tbl_kendaraan`
  MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_mekanik`
--
ALTER TABLE `tbl_mekanik`
  MODIFY `id_mekanik` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tbl_paket_layanan`
--
ALTER TABLE `tbl_paket_layanan`
  MODIFY `id_paket` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `tbl_pembayaran`
--
ALTER TABLE `tbl_pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_peminjaman_alat`
--
ALTER TABLE `tbl_peminjaman_alat`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_pengerjaan`
--
ALTER TABLE `tbl_pengerjaan`
  MODIFY `id_pengerjaan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_pengerjaan_suku_cadang`
--
ALTER TABLE `tbl_pengerjaan_suku_cadang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_stall`
--
ALTER TABLE `tbl_stall`
  MODIFY `id_stall` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_suku_cadang`
--
ALTER TABLE `tbl_suku_cadang`
  MODIFY `id_suku_cadang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbl_booking`
--
ALTER TABLE `tbl_booking`
  ADD CONSTRAINT `tbl_booking_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `tbl_booking_ibfk_2` FOREIGN KEY (`id_kendaraan`) REFERENCES `tbl_kendaraan` (`id_kendaraan`),
  ADD CONSTRAINT `tbl_booking_ibfk_3` FOREIGN KEY (`id_paket`) REFERENCES `tbl_paket_layanan` (`id_paket`),
  ADD CONSTRAINT `tbl_booking_ibfk_4` FOREIGN KEY (`id_stall`) REFERENCES `tbl_stall` (`id_stall`) ON DELETE SET NULL,
  ADD CONSTRAINT `tbl_booking_ibfk_5` FOREIGN KEY (`id_mekanik`) REFERENCES `tbl_mekanik` (`id_mekanik`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `tbl_kendaraan`
--
ALTER TABLE `tbl_kendaraan`
  ADD CONSTRAINT `tbl_kendaraan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_pembayaran`
--
ALTER TABLE `tbl_pembayaran`
  ADD CONSTRAINT `tbl_pembayaran_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `tbl_booking` (`id_booking`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_pembayaran_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `tbl_admin` (`id_admin`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `tbl_peminjaman_alat`
--
ALTER TABLE `tbl_peminjaman_alat`
  ADD CONSTRAINT `tbl_peminjaman_alat_ibfk_1` FOREIGN KEY (`id_pengerjaan`) REFERENCES `tbl_pengerjaan` (`id_pengerjaan`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_peminjaman_alat_ibfk_2` FOREIGN KEY (`id_alat`) REFERENCES `tbl_alat_kerja` (`id_alat`);

--
-- Ketidakleluasaan untuk tabel `tbl_pengerjaan`
--
ALTER TABLE `tbl_pengerjaan`
  ADD CONSTRAINT `tbl_pengerjaan_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `tbl_booking` (`id_booking`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_pengerjaan_ibfk_2` FOREIGN KEY (`id_mekanik`) REFERENCES `tbl_mekanik` (`id_mekanik`);

--
-- Ketidakleluasaan untuk tabel `tbl_pengerjaan_suku_cadang`
--
ALTER TABLE `tbl_pengerjaan_suku_cadang`
  ADD CONSTRAINT `tbl_pengerjaan_suku_cadang_ibfk_1` FOREIGN KEY (`id_pengerjaan`) REFERENCES `tbl_pengerjaan` (`id_pengerjaan`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_pengerjaan_suku_cadang_ibfk_2` FOREIGN KEY (`id_suku_cadang`) REFERENCES `tbl_suku_cadang` (`id_suku_cadang`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
