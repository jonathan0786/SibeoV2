-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 07, 2026 at 03:01 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_penugasan_servis` (IN `p_id_booking` INT, IN `p_id_mekanik` INT, IN `p_id_stall` INT)   BEGIN
    DECLARE v_status_stall VARCHAR(20);
    DECLARE v_shift_mekanik VARCHAR(20);
    
    -- Ambil status stall saat ini
    SELECT status INTO v_status_stall FROM tbl_stall WHERE id_stall = p_id_stall;
    
    -- Validasi Stall Tersedia (BR-04)
    IF v_status_stall <> 'tersedia' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stall tidak tersedia atau sedang digunakan!';
    ELSE
        -- Update booking dengan stall, mekanik dan ubah status ke 'terkonfirmasi'
        UPDATE tbl_booking 
        SET id_stall = p_id_stall, 
            id_mekanik = p_id_mekanik, 
            status = 'terkonfirmasi' 
        WHERE id_booking = p_id_booking;
        
        -- Buat entri pengerjaan baru
        INSERT INTO tbl_pengerjaan (id_booking, id_mekanik, status, waktu_mulai)
        VALUES (p_id_booking, p_id_mekanik, 'dimulai', NOW());
        
        -- Ubah status stall menjadi terpakai
        UPDATE tbl_stall SET status = 'terpakai' WHERE id_stall = p_id_stall;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_selesaikan_servis` (IN `p_id_pengerjaan` INT)   BEGIN
    DECLARE v_id_booking INT;
    DECLARE v_id_stall INT;
    DECLARE v_harga_paket DECIMAL(12,2);
    DECLARE v_biaya_suku_cadang DECIMAL(12,2);
    DECLARE v_total_tagihan DECIMAL(12,2);
    DECLARE v_count_pembayaran INT;
    
    -- Dapatkan ID Booking dan ID Stall
    SELECT id_booking INTO v_id_booking FROM tbl_pengerjaan WHERE id_pengerjaan = p_id_pengerjaan;
    SELECT id_stall INTO v_id_stall FROM tbl_booking WHERE id_booking = v_id_booking;
    
    -- Update status pengerjaan
    UPDATE tbl_pengerjaan 
    SET status = 'selesai', waktu_selesai = NOW() 
    WHERE id_pengerjaan = p_id_pengerjaan;
    
    -- Update status booking (Trigger trg_selesai_booking otomatis membebaskan stall)
    UPDATE tbl_booking 
    SET status = 'selesai' 
    WHERE id_booking = v_id_booking;
    
    -- Perhitungan Biaya Jasa Paket
    SELECT pl.harga INTO v_harga_paket 
    FROM tbl_booking b 
    JOIN tbl_paket_layanan pl ON b.id_paket = pl.id_paket 
    WHERE b.id_booking = v_id_booking;
    
    -- Perhitungan Biaya Suku Cadang
    SELECT IFNULL(SUM(jumlah_pakai * harga_satuan), 0.00) INTO v_biaya_suku_cadang 
    FROM tbl_pengerjaan_suku_cadang 
    WHERE id_pengerjaan = p_id_pengerjaan;
    
    -- Hitung total tagihan menggunakan UDF
    SET v_total_tagihan = udf_hitung_total_servis(v_harga_paket, v_biaya_suku_cadang);
    
    -- Cek jika invoice pembayaran sudah ada
    SELECT COUNT(*) INTO v_count_pembayaran FROM tbl_pembayaran WHERE id_booking = v_id_booking;
    
    IF v_count_pembayaran = 0 THEN
        -- Insert invoice baru
        INSERT INTO tbl_pembayaran (id_booking, nomor_nota, biaya_jasa, biaya_suku_cadang, total_tagihan, status)
        VALUES (v_id_booking, CONCAT('INV-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', p_id_pengerjaan), v_harga_paket, v_biaya_suku_cadang, v_total_tagihan, 'belum_bayar');
    ELSE
        -- Update invoice yang ada
        UPDATE tbl_pembayaran 
        SET biaya_jasa = v_harga_paket, 
            biaya_suku_cadang = v_biaya_suku_cadang, 
            total_tagihan = v_total_tagihan 
        WHERE id_booking = v_id_booking;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_tambah_sparepart` (IN `p_id_pengerjaan` INT, IN `p_id_suku_cadang` INT, IN `p_jumlah_pakai` INT)   BEGIN
    DECLARE v_harga DECIMAL(12,2);
    
    -- Ambil harga satuan suku cadang
    SELECT harga_satuan INTO v_harga FROM tbl_suku_cadang WHERE id_suku_cadang = p_id_suku_cadang;
    
    -- Insert ke tabel pengerjaan suku cadang (Trigger trg_kurangi_stok_sparepart akan memotong stok otomatis)
    INSERT INTO tbl_pengerjaan_suku_cadang (id_pengerjaan, id_suku_cadang, jumlah_pakai, harga_satuan)
    VALUES (p_id_pengerjaan, p_id_suku_cadang, p_jumlah_pakai, v_harga);
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `udf_hitung_durasi_servis` (`p_mulai` DATETIME, `p_selesai` DATETIME) RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    DECLARE v_menit INT;
    SET v_menit = TIMESTAMPDIFF(MINUTE, p_mulai, p_selesai);
    RETURN CONCAT(v_menit, ' menit');
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `udf_hitung_nilai_mekanik` (`p_id_mekanik` INT) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE v_jumlah_servis INT DEFAULT 0;
    DECLARE v_jumlah_rusak INT DEFAULT 0;
    DECLARE v_nilai INT DEFAULT 0;
    
    -- Hitung jumlah servis selesai
    SELECT COUNT(*) INTO v_jumlah_servis 
    FROM tbl_pengerjaan 
    WHERE id_mekanik = p_id_mekanik AND status = 'selesai';
    
    -- Hitung alat rusak saat dikembalikan
    SELECT COUNT(*) INTO v_jumlah_rusak 
    FROM tbl_peminjaman_alat pa
    JOIN tbl_pengerjaan p ON pa.id_pengerjaan = p.id_pengerjaan
    WHERE p.id_mekanik = p_id_mekanik AND pa.kondisi_kembali IN ('rusak_ringan', 'rusak_berat');
    
    -- Rumus: (Servis Selesai * 10) - (Alat Rusak * 5)
    SET v_nilai = (v_jumlah_servis * 10) - (v_jumlah_rusak * 5);
    RETURN v_nilai;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `udf_hitung_subtotal_sparepart` (`p_harga` DECIMAL(12,2), `p_qty` INT) RETURNS DECIMAL(12,2) DETERMINISTIC BEGIN
    RETURN p_harga * p_qty;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `udf_hitung_total` (`p_harga_paket` INT) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE v_total INT;
    SET v_total = p_harga_paket;
    RETURN v_total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `udf_hitung_total_servis` (`p_tarif` DECIMAL(12,2), `p_total_sparepart` DECIMAL(12,2)) RETURNS DECIMAL(12,2) DETERMINISTIC BEGIN
    RETURN p_tarif + p_total_sparepart;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `id_admin` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_admin`
--

INSERT INTO `tbl_admin` (`id_admin`, `nama`, `username`, `password`) VALUES
(1, 'Administrator SIBEO', 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_alat_kerja`
--

CREATE TABLE `tbl_alat_kerja` (
  `id_alat` int(11) NOT NULL,
  `nama_alat` varchar(100) NOT NULL,
  `kode_alat` varchar(30) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 0,
  `kondisi` enum('baik','rusak') NOT NULL DEFAULT 'baik'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_alat_kerja`
--

INSERT INTO `tbl_alat_kerja` (`id_alat`, `nama_alat`, `kode_alat`, `jumlah`, `kondisi`) VALUES
(4, 'Kunci T 14 Tekiro', 'ALT-001', 3, 'baik'),
(5, 'Kunci Roda', 'ALT-002', 5, 'baik');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_booking`
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

--
-- Dumping data for table `tbl_booking`
--

INSERT INTO `tbl_booking` (`id_booking`, `kode_booking`, `id_pelanggan`, `id_kendaraan`, `id_paket`, `id_stall`, `id_mekanik`, `tanggal_servis`, `jam_servis`, `keluhan`, `status`, `created_at`) VALUES
(1, 'BK-20260622-001', 2, 2, 1, 2, 2, '2026-06-24', '08:00:00', '', 'selesai', '2026-06-22 16:10:58'),
(2, 'BK-20260630-001', 4, 3, 3, 1, 1, '2026-07-02', '07:00:00', '-', 'selesai', '2026-06-30 01:31:45'),
(3, 'BK-20260703-001', 3, 4, 1, 1, NULL, '2026-07-13', '11:00:00', '-', 'selesai', '2026-07-03 13:33:01'),
(4, 'BK-20260703-002', 3, 4, 3, 1, 4, '2026-07-04', '04:11:00', '', 'Dalam Pengerjaan', '2026-07-03 19:11:25'),
(5, 'BK-20260704-001', 2, 1, 3, NULL, NULL, '2026-07-15', '09:10:00', '', 'selesai', '2026-07-04 07:19:31');

--
-- Triggers `tbl_booking`
--
DELIMITER $$
CREATE TRIGGER `trg_selesai_booking` AFTER UPDATE ON `tbl_booking` FOR EACH ROW BEGIN
    IF NEW.status = 'selesai' AND OLD.status <> 'selesai' THEN
        UPDATE tbl_stall SET status = 'tersedia' WHERE id_stall = NEW.id_stall;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kendaraan`
--

CREATE TABLE `tbl_kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `nomor_polisi` varchar(12) NOT NULL,
  `merk` varchar(50) NOT NULL,
  `tipe` varchar(50) NOT NULL,
  `tahun_pembuatan` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_kendaraan`
--

INSERT INTO `tbl_kendaraan` (`id_kendaraan`, `id_pelanggan`, `nomor_polisi`, `merk`, `tipe`, `tahun_pembuatan`) VALUES
(1, 2, 'B 1234 CDE', 'Honda Vario', 'Motor', '2010'),
(2, 2, 'T 0987 EPE', 'Nissan GTR', 'Mobil', '1991'),
(3, 4, 'B 4666 YAM', 'Honda Vario 160', 'Motor', '2025'),
(4, 3, 'B 444 UUU', 'Honda Hrv', 'Mobil', '2018');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mekanik`
--

CREATE TABLE `tbl_mekanik` (
  `id_mekanik` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kepegawaian` varchar(20) NOT NULL,
  `spesialisasi` varchar(100) NOT NULL,
  `shift` enum('pagi','siang','malam') NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_mekanik`
--

INSERT INTO `tbl_mekanik` (`id_mekanik`, `nama`, `kepegawaian`, `spesialisasi`, `shift`, `username`, `password`) VALUES
(1, 'Mekanik1', 'MK-001', 'Mesin & Oli', 'pagi', 'mekanik', 'mekanik123'),
(2, 'Yoga Noval', '0920250050', 'Kelistrikan', 'pagi', 'noval', 'noval123'),
(4, 'Ferdy', '0920250036', 'Overhaul Engine', 'siang', 'ferdy', 'ferdy123');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_paket_layanan`
--

CREATE TABLE `tbl_paket_layanan` (
  `id_paket` int(11) NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_paket_layanan`
--

INSERT INTO `tbl_paket_layanan` (`id_paket`, `nama_paket`, `deskripsi`, `harga`) VALUES
(1, 'Servis Berkala', 'Cek & setel rem, rantai, oli, Pembersihan komponen utama, Cocok tiap 2–3 bulan sekali', 500000.00),
(3, 'Ganti Oli + Servis', 'Semua item servis berkala, Ganti oli sesuai tipe Kendaraan,  Estimasi waktu pengerjaan jelas', 100000.00),
(4, 'Servis Besar', ' Pengecekan mesin lebih detail, Penggantian suku cadang bila perlu, Rincian sparepart terpakai tercatat', 1500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pelanggan`
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
-- Dumping data for table `tbl_pelanggan`
--

INSERT INTO `tbl_pelanggan` (`id_pelanggan`, `nomor_pelanggan`, `nama_lengkap`, `nik`, `no_telepon`, `email`, `alamat`, `password`, `status`, `created_at`) VALUES
(2, 'CS-002', 'Rayyan Abdurrahman Qadar', '0920250049', '81210822482', '', '', 'customer2', 'aktif', '2026-06-18 00:18:35'),
(3, 'CS-003', 'Daffa Hanif Muzaki', '0920250032', '81234567889', '', '', 'customer3', 'aktif', '2026-06-18 00:20:34'),
(4, 'CS-004', 'Rasya Genteng', '0920250048', '8153163517', '', '', 'customer4', 'aktif', '2026-06-22 09:22:24'),
(8, 'CS-005', 'Mazyan Ghiffani', '0920250040', '836748264927', '', '', '', 'aktif', '2026-06-30 04:05:54'),
(10, 'CS-006', 'Jonathan', '0920250037', '081280123889', '', '', 'jo123', 'aktif', '2026-07-06 08:39:12');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pembayaran`
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

--
-- Dumping data for table `tbl_pembayaran`
--

INSERT INTO `tbl_pembayaran` (`id_pembayaran`, `id_booking`, `nomor_nota`, `biaya_jasa`, `biaya_suku_cadang`, `total_tagihan`, `metode_pembayaran`, `status`, `tanggal_bayar`, `id_admin`) VALUES
(2, 5, 'INV-20260704-937', 100000.00, 0.00, 100000.00, NULL, 'lunas', '2026-07-05 00:48:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_peminjaman_alat`
--

CREATE TABLE `tbl_peminjaman_alat` (
  `id_peminjaman` int(11) NOT NULL,
  `id_pengerjaan` int(11) NOT NULL,
  `id_alat` int(11) NOT NULL,
  `jumlah_pinjam` int(11) NOT NULL DEFAULT 1,
  `waktu_pinjam` datetime NOT NULL,
  `waktu_kembali` datetime DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam',
  `kondisi_kembali` enum('baik','rusak_ringan','rusak_berat') NOT NULL DEFAULT 'baik'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `tbl_peminjaman_alat`
--
DELIMITER $$
CREATE TRIGGER `trg_histori_pengembalian_alat` AFTER UPDATE ON `tbl_peminjaman_alat` FOR EACH ROW BEGIN
    -- Jika status berubah menjadi dikembalikan
    IF NEW.status = 'dikembalikan' AND OLD.status = 'dipinjam' THEN
        -- Jika alat dikembalikan rusak, kurangi stok alat baik di tbl_alat_kerja
        IF NEW.kondisi_kembali IN ('rusak_ringan', 'rusak_berat') THEN
            UPDATE tbl_alat_kerja 
            SET kondisi = 'rusak', jumlah = GREATEST(0, jumlah - NEW.jumlah_pinjam)
            WHERE id_alat = NEW.id_alat;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengadaan`
--

CREATE TABLE `tbl_pengadaan` (
  `id_pengadaan` int(11) NOT NULL,
  `kode_pengadaan` varchar(20) NOT NULL,
  `id_suku_cadang` int(11) NOT NULL,
  `supplier` varchar(100) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `total_biaya` decimal(15,2) NOT NULL,
  `tanggal_pengadaan` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pengadaan`
--

INSERT INTO `tbl_pengadaan` (`id_pengadaan`, `kode_pengadaan`, `id_suku_cadang`, `supplier`, `jumlah`, `total_biaya`, `tanggal_pengadaan`) VALUES
(1, 'PGD-001', 1, 'PT. AHM', 5, 2500000.00, '2026-07-06');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengerjaan`
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

--
-- Dumping data for table `tbl_pengerjaan`
--

INSERT INTO `tbl_pengerjaan` (`id_pengerjaan`, `id_booking`, `id_mekanik`, `catatan_pemeriksaan`, `catatan_pengerjaan`, `status`, `waktu_mulai`, `waktu_selesai`) VALUES
(1, 2, 1, NULL, NULL, 'selesai', '2026-07-03 20:38:09', '2026-07-03 20:38:13'),
(2, 1, 2, NULL, NULL, 'selesai', '2026-07-03 20:38:16', '2026-07-03 20:38:19');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengerjaan_suku_cadang`
--

CREATE TABLE `tbl_pengerjaan_suku_cadang` (
  `id` int(11) NOT NULL,
  `id_pengerjaan` int(11) NOT NULL,
  `id_suku_cadang` int(11) NOT NULL,
  `jumlah_pakai` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `tbl_pengerjaan_suku_cadang`
--
DELIMITER $$
CREATE TRIGGER `trg_kembalikan_stok_sparepart` AFTER DELETE ON `tbl_pengerjaan_suku_cadang` FOR EACH ROW BEGIN
    UPDATE tbl_suku_cadang SET stok = stok + OLD.jumlah_pakai WHERE id_suku_cadang = OLD.id_suku_cadang;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_kurangi_stok_sparepart` BEFORE INSERT ON `tbl_pengerjaan_suku_cadang` FOR EACH ROW BEGIN
    DECLARE v_stok INT;
    
    -- Ambil stok sekarang
    SELECT stok INTO v_stok FROM tbl_suku_cadang WHERE id_suku_cadang = NEW.id_suku_cadang;
    
    -- Cek jika stok kurang
    IF v_stok < NEW.jumlah_pakai THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stok suku cadang tidak mencukupi untuk transaksi ini!';
    ELSE
        -- Kurangi stok suku cadang
        UPDATE tbl_suku_cadang SET stok = stok - NEW.jumlah_pakai WHERE id_suku_cadang = NEW.id_suku_cadang;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_stall`
--

CREATE TABLE `tbl_stall` (
  `id_stall` int(11) NOT NULL,
  `nomor_stall` varchar(10) NOT NULL,
  `keterangan` varchar(100) NOT NULL,
  `status` enum('tersedia','terpakai') NOT NULL DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_stall`
--

INSERT INTO `tbl_stall` (`id_stall`, `nomor_stall`, `keterangan`, `status`) VALUES
(1, 'STL-001', 'Stall Tune Up', 'tersedia'),
(2, 'STL-002', 'Stall Kelistrikan', 'tersedia'),
(3, 'STL-003', 'Stall Overhaul', '');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_suku_cadang`
--

CREATE TABLE `tbl_suku_cadang` (
  `id_suku_cadang` int(11) NOT NULL,
  `nama_part` varchar(100) NOT NULL,
  `kode_part` varchar(30) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `harga_satuan` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_suku_cadang`
--

INSERT INTO `tbl_suku_cadang` (`id_suku_cadang`, `nama_part`, `kode_part`, `stok`, `harga_satuan`) VALUES
(1, 'Kampas Rem Belakang Toyota Avanza Veloz', 'SP-001', 25, 342000.00),
(2, 'Kampas Kopling Daihatsu Xenia', 'SP-002', 12, 250000.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `tbl_alat_kerja`
--
ALTER TABLE `tbl_alat_kerja`
  ADD PRIMARY KEY (`id_alat`),
  ADD UNIQUE KEY `kode_alat` (`kode_alat`);

--
-- Indexes for table `tbl_booking`
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
-- Indexes for table `tbl_kendaraan`
--
ALTER TABLE `tbl_kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- Indexes for table `tbl_mekanik`
--
ALTER TABLE `tbl_mekanik`
  ADD PRIMARY KEY (`id_mekanik`);

--
-- Indexes for table `tbl_paket_layanan`
--
ALTER TABLE `tbl_paket_layanan`
  ADD PRIMARY KEY (`id_paket`);

--
-- Indexes for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `tbl_pembayaran`
--
ALTER TABLE `tbl_pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `nomor_nota` (`nomor_nota`),
  ADD KEY `id_booking` (`id_booking`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indexes for table `tbl_peminjaman_alat`
--
ALTER TABLE `tbl_peminjaman_alat`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_pengerjaan` (`id_pengerjaan`),
  ADD KEY `id_alat` (`id_alat`);

--
-- Indexes for table `tbl_pengadaan`
--
ALTER TABLE `tbl_pengadaan`
  ADD PRIMARY KEY (`id_pengadaan`);

--
-- Indexes for table `tbl_pengerjaan`
--
ALTER TABLE `tbl_pengerjaan`
  ADD PRIMARY KEY (`id_pengerjaan`),
  ADD KEY `id_booking` (`id_booking`),
  ADD KEY `id_mekanik` (`id_mekanik`);

--
-- Indexes for table `tbl_pengerjaan_suku_cadang`
--
ALTER TABLE `tbl_pengerjaan_suku_cadang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pengerjaan` (`id_pengerjaan`),
  ADD KEY `id_suku_cadang` (`id_suku_cadang`);

--
-- Indexes for table `tbl_stall`
--
ALTER TABLE `tbl_stall`
  ADD PRIMARY KEY (`id_stall`);

--
-- Indexes for table `tbl_suku_cadang`
--
ALTER TABLE `tbl_suku_cadang`
  ADD PRIMARY KEY (`id_suku_cadang`),
  ADD UNIQUE KEY `kode_part` (`kode_part`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_alat_kerja`
--
ALTER TABLE `tbl_alat_kerja`
  MODIFY `id_alat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_booking`
--
ALTER TABLE `tbl_booking`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_kendaraan`
--
ALTER TABLE `tbl_kendaraan`
  MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_mekanik`
--
ALTER TABLE `tbl_mekanik`
  MODIFY `id_mekanik` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_paket_layanan`
--
ALTER TABLE `tbl_paket_layanan`
  MODIFY `id_paket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_pelanggan`
--
ALTER TABLE `tbl_pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_pembayaran`
--
ALTER TABLE `tbl_pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_peminjaman_alat`
--
ALTER TABLE `tbl_peminjaman_alat`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_pengadaan`
--
ALTER TABLE `tbl_pengadaan`
  MODIFY `id_pengadaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_pengerjaan`
--
ALTER TABLE `tbl_pengerjaan`
  MODIFY `id_pengerjaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_pengerjaan_suku_cadang`
--
ALTER TABLE `tbl_pengerjaan_suku_cadang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_stall`
--
ALTER TABLE `tbl_stall`
  MODIFY `id_stall` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_suku_cadang`
--
ALTER TABLE `tbl_suku_cadang`
  MODIFY `id_suku_cadang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_booking`
--
ALTER TABLE `tbl_booking`
  ADD CONSTRAINT `tbl_booking_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `tbl_booking_ibfk_2` FOREIGN KEY (`id_kendaraan`) REFERENCES `tbl_kendaraan` (`id_kendaraan`),
  ADD CONSTRAINT `tbl_booking_ibfk_3` FOREIGN KEY (`id_paket`) REFERENCES `tbl_paket_layanan` (`id_paket`),
  ADD CONSTRAINT `tbl_booking_ibfk_4` FOREIGN KEY (`id_stall`) REFERENCES `tbl_stall` (`id_stall`) ON DELETE SET NULL,
  ADD CONSTRAINT `tbl_booking_ibfk_5` FOREIGN KEY (`id_mekanik`) REFERENCES `tbl_mekanik` (`id_mekanik`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_kendaraan`
--
ALTER TABLE `tbl_kendaraan`
  ADD CONSTRAINT `tbl_kendaraan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `tbl_pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_pembayaran`
--
ALTER TABLE `tbl_pembayaran`
  ADD CONSTRAINT `tbl_pembayaran_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `tbl_booking` (`id_booking`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_pembayaran_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `tbl_admin` (`id_admin`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_peminjaman_alat`
--
ALTER TABLE `tbl_peminjaman_alat`
  ADD CONSTRAINT `tbl_peminjaman_alat_ibfk_1` FOREIGN KEY (`id_pengerjaan`) REFERENCES `tbl_pengerjaan` (`id_pengerjaan`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_peminjaman_alat_ibfk_2` FOREIGN KEY (`id_alat`) REFERENCES `tbl_alat_kerja` (`id_alat`);

--
-- Constraints for table `tbl_pengerjaan`
--
ALTER TABLE `tbl_pengerjaan`
  ADD CONSTRAINT `tbl_pengerjaan_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `tbl_booking` (`id_booking`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_pengerjaan_ibfk_2` FOREIGN KEY (`id_mekanik`) REFERENCES `tbl_mekanik` (`id_mekanik`);

--
-- Constraints for table `tbl_pengerjaan_suku_cadang`
--
ALTER TABLE `tbl_pengerjaan_suku_cadang`
  ADD CONSTRAINT `tbl_pengerjaan_suku_cadang_ibfk_1` FOREIGN KEY (`id_pengerjaan`) REFERENCES `tbl_pengerjaan` (`id_pengerjaan`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_pengerjaan_suku_cadang_ibfk_2` FOREIGN KEY (`id_suku_cadang`) REFERENCES `tbl_suku_cadang` (`id_suku_cadang`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
