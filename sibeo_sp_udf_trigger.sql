-- =========================================================================
-- SIBEO DATABASE IMPLEMENTATION: STORED PROCEDURES, UDFs, AND TRIGGERS
-- =========================================================================

-- 1. ALTER TABLE AND CREATE NEW TABLES
-- Add missing kondisi_kembali column to tbl_peminjaman_alat
ALTER TABLE `tbl_peminjaman_alat` 
ADD COLUMN `kondisi_kembali` ENUM('baik', 'rusak_ringan', 'rusak_berat') DEFAULT NULL AFTER `status`;

-- Create tbl_histori_pengembalian_alat if not exists
CREATE TABLE IF NOT EXISTS `tbl_histori_pengembalian_alat` (
  `id_histori` INT(11) NOT NULL AUTO_INCREMENT,
  `id_peminjaman` INT(11) NOT NULL,
  `id_alat` INT(11) NOT NULL,
  `kondisi_kembali` VARCHAR(30) NOT NULL,
  `waktu_kembali` DATETIME NOT NULL,
  PRIMARY KEY (`id_histori`),
  KEY `id_peminjaman` (`id_peminjaman`),
  KEY `id_alat` (`id_alat`),
  CONSTRAINT `fk_histori_peminjaman` FOREIGN KEY (`id_peminjaman`) REFERENCES `tbl_peminjaman_alat` (`id_peminjaman`) ON DELETE CASCADE,
  CONSTRAINT `fk_histori_alat` FOREIGN KEY (`id_alat`) REFERENCES `tbl_alat_kerja` (`id_alat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =========================================================================
-- 2. USER DEFINED FUNCTIONS (UDF)
-- =========================================================================

DELIMITER //

-- UDF-01 : fn_hitung_subtotal_sparepart()
-- Menghitung subtotal penggunaan sparepart.
DROP FUNCTION IF EXISTS fn_hitung_subtotal_sparepart //
CREATE FUNCTION fn_hitung_subtotal_sparepart(harga DECIMAL(12,2), qty INT)
RETURNS DECIMAL(12,2)
DETERMINISTIC
BEGIN
    RETURN harga * qty;
END //

-- UDF-02 : fn_hitung_total_servis()
-- Menghitung total biaya servis (tarif paket + total sparepart).
DROP FUNCTION IF EXISTS fn_hitung_total_servis //
CREATE FUNCTION fn_hitung_total_servis(booking_id INT)
RETURNS DECIMAL(12,2)
DETERMINISTIC
BEGIN
    DECLARE v_harga_paket DECIMAL(12,2) DEFAULT 0.00;
    DECLARE v_harga_sparepart DECIMAL(12,2) DEFAULT 0.00;
    
    -- Ambil harga paket layanan dari booking
    SELECT pk.harga INTO v_harga_paket
    FROM tbl_booking b
    JOIN tbl_paket_layanan pk ON b.id_paket = pk.id_paket
    WHERE b.id_booking = booking_id;
    
    -- Ambil total harga sparepart yang digunakan dalam pengerjaan
    SELECT IFNULL(SUM(pcs.jumlah_pakai * pcs.harga_satuan), 0.00) INTO v_harga_sparepart
    FROM tbl_pengerjaan p
    JOIN tbl_pengerjaan_suku_cadang pcs ON p.id_pengerjaan = pcs.id_pengerjaan
    WHERE p.id_booking = booking_id;
    
    RETURN v_harga_paket + v_harga_sparepart;
END //

-- udf_hitung_total()
-- Wrapper function used in the existing PHP scripts (admin/booking.php and pelanggan/dashboard.php)
DROP FUNCTION IF EXISTS udf_hitung_total //
CREATE FUNCTION udf_hitung_total(booking_id INT)
RETURNS DECIMAL(12,2)
DETERMINISTIC
BEGIN
    RETURN fn_hitung_total_servis(booking_id);
END //

-- UDF-03 : fn_hitung_durasi_servis()
-- Menghitung durasi pengerjaan servis dalam satuan menit.
DROP FUNCTION IF EXISTS fn_hitung_durasi_servis //
CREATE FUNCTION fn_hitung_durasi_servis(pengerjaan_id INT)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE v_mulai DATETIME;
    DECLARE v_selesai DATETIME;
    DECLARE v_durasi INT DEFAULT 0;
    
    SELECT waktu_mulai, waktu_selesai INTO v_mulai, v_selesai 
    FROM tbl_pengerjaan 
    WHERE id_pengerjaan = pengerjaan_id;
    
    IF v_mulai IS NOT NULL AND v_selesai IS NOT NULL THEN
        SET v_durasi = TIMESTAMPDIFF(MINUTE, v_mulai, v_selesai);
    END IF;
    
    RETURN v_durasi;
END //

-- UDF-04 : fn_hitung_nilai_mekanik()
-- Menghitung nilai kinerja mekanik. Nilai = (Jumlah Servis * 10) - (Jumlah Kerusakan Alat * 5)
DROP FUNCTION IF EXISTS fn_hitung_nilai_mekanik //
CREATE FUNCTION fn_hitung_nilai_mekanik(mekanik_id INT)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE v_jumlah_servis INT DEFAULT 0;
    DECLARE v_jumlah_rusak INT DEFAULT 0;
    
    -- Hitung jumlah servis selesai oleh mekanik
    SELECT COUNT(*) INTO v_jumlah_servis 
    FROM tbl_pengerjaan 
    WHERE id_mekanik = mekanik_id AND status = 'Selesai';
    
    -- Hitung jumlah alat rusak (rusak ringan atau rusak berat) yang dikembalikan oleh mekanik
    SELECT IFNULL(SUM(pa.jumlah_pinjam), 0) INTO v_jumlah_rusak
    FROM tbl_peminjaman_alat pa
    JOIN tbl_pengerjaan p ON pa.id_pengerjaan = p.id_pengerjaan
    WHERE p.id_mekanik = mekanik_id 
      AND pa.status = 'dikembalikan' 
      AND pa.kondisi_kembali IN ('rusak_ringan', 'rusak_berat');
      
    RETURN (v_jumlah_servis * 10) - (v_jumlah_rusak * 5);
END //

DELIMITER ;

-- =========================================================================
-- 3. STORED PROCEDURES (SP)
-- =========================================================================

DELIMITER //

-- SP-01 : sp_tambah_booking()
-- Menambahkan data booking baru dengan validasi pelanggan & kendaraan.
DROP PROCEDURE IF EXISTS sp_tambah_booking //
CREATE PROCEDURE sp_tambah_booking(
    IN in_kode_booking VARCHAR(20),
    IN in_id_pelanggan INT,
    IN in_id_kendaraan INT,
    IN in_id_paket INT,
    IN in_tanggal_servis DATE,
    IN in_jam_servis TIME,
    IN in_keluhan TEXT
)
BEGIN
    -- Validasi Pelanggan
    IF NOT EXISTS (SELECT 1 FROM tbl_pelanggan WHERE id_pelanggan = in_id_pelanggan) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Pelanggan tidak terdaftar!';
    END IF;
    
    -- Validasi Kendaraan
    IF NOT EXISTS (SELECT 1 FROM tbl_kendaraan WHERE id_kendaraan = in_id_kendaraan) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Kendaraan tidak ditemukan!';
    END IF;
    
    -- Insert Data Booking
    INSERT INTO tbl_booking (
        kode_booking, id_pelanggan, id_kendaraan, id_paket, 
        tanggal_servis, jam_servis, keluhan, status
    ) VALUES (
        in_kode_booking, in_id_pelanggan, in_id_kendaraan, in_id_paket,
        in_tanggal_servis, in_jam_servis, in_keluhan, 'menunggu'
    );
END //

-- SP-02 : sp_penugasan_servis()
-- Menugaskan mekanik dan stall, lalu membuat pengerjaan baru.
DROP PROCEDURE IF EXISTS sp_penugasan_servis //
CREATE PROCEDURE sp_penugasan_servis(
    IN in_id_booking INT,
    IN in_id_mekanik INT,
    IN in_id_stall INT
)
BEGIN
    DECLARE v_status_stall VARCHAR(20);
    
    -- Validasi Booking
    IF NOT EXISTS (SELECT 1 FROM tbl_booking WHERE id_booking = in_id_booking) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Booking tidak ditemukan!';
    END IF;
    
    -- Validasi Mekanik
    IF NOT EXISTS (SELECT 1 FROM tbl_mekanik WHERE id_mekanik = in_id_mekanik) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Mekanik tidak ditemukan!';
    END IF;
    
    -- Validasi Stall
    SELECT status INTO v_status_stall FROM tbl_stall WHERE id_stall = in_id_stall;
    IF v_status_stall IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Stall tidak ditemukan!';
    ELSEIF LOWER(v_status_stall) != 'tersedia' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Stall sedang digunakan atau sedang maintenance!';
    END IF;
    
    -- Update Booking dengan Mekanik, Stall, dan Status Terkonfirmasi
    UPDATE tbl_booking 
    SET id_mekanik = in_id_mekanik, id_stall = in_id_stall, status = 'terkonfirmasi'
    WHERE id_booking = in_id_booking;
    
    -- Membuat transaksi pengerjaan servis secara otomatis
    INSERT INTO tbl_pengerjaan (id_booking, id_mekanik, status, waktu_mulai)
    VALUES (in_id_booking, in_id_mekanik, 'dimulai', NOW());
END //

-- SP-03 : sp_tambah_sparepart()
-- Mencatat penggunaan sparepart pada pengerjaan servis.
DROP PROCEDURE IF EXISTS sp_tambah_sparepart //
CREATE PROCEDURE sp_tambah_sparepart(
    IN in_id_pengerjaan INT,
    IN in_id_sparepart INT,
    IN in_qty INT
)
BEGIN
    DECLARE v_harga DECIMAL(12,2);
    
    -- Validasi Pengerjaan
    IF NOT EXISTS (SELECT 1 FROM tbl_pengerjaan WHERE id_pengerjaan = in_id_pengerjaan) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Pengerjaan servis tidak ditemukan!';
    END IF;
    
    -- Ambil harga dari tbl_suku_cadang
    SELECT harga_satuan INTO v_harga FROM tbl_suku_cadang WHERE id_suku_cadang = in_id_sparepart;
    IF v_harga IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Suku cadang tidak ditemukan!';
    END IF;
    
    -- Insert Data Penggunaan
    INSERT INTO tbl_pengerjaan_suku_cadang (id_pengerjaan, id_suku_cadang, jumlah_pakai, harga_satuan)
    VALUES (in_id_pengerjaan, in_id_sparepart, in_qty, v_harga);
END //

-- SP-04 : sp_selesaikan_servis()
-- Menyelesaikan pengerjaan servis dan mengubah status-status terkait.
DROP PROCEDURE IF EXISTS sp_selesaikan_servis //
CREATE PROCEDURE sp_selesaikan_servis(
    IN in_id_pengerjaan INT
)
BEGIN
    DECLARE v_id_booking INT;
    
    -- Ambil id_booking
    SELECT id_booking INTO v_id_booking FROM tbl_pengerjaan WHERE id_pengerjaan = in_id_pengerjaan;
    IF v_id_booking IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Pengerjaan servis tidak ditemukan!';
    END IF;
    
    -- Update Status Pengerjaan
    UPDATE tbl_pengerjaan 
    SET status = 'Selesai', waktu_selesai = NOW() 
    WHERE id_pengerjaan = in_id_pengerjaan;
    
    -- Update Status Booking (Trigger trg_stall_tersedia akan otomatis mereset status Stall)
    UPDATE tbl_booking 
    SET status = 'selesai' 
    WHERE id_booking = v_id_booking;
END //

DELIMITER ;

-- =========================================================================
-- 4. TRIGGERS
-- =========================================================================

DELIMITER //

-- Trigger 1: trg_kurangi_stok_sparepart
-- Dijalankan sebelum data penggunaan sparepart ditambahkan untuk validasi stok dan pemotongan stok otomatis.
DROP TRIGGER IF EXISTS trg_kurangi_stok_sparepart //
CREATE TRIGGER trg_kurangi_stok_sparepart
BEFORE INSERT ON tbl_pengerjaan_suku_cadang
FOR EACH ROW
BEGIN
    DECLARE v_stok INT;
    
    SELECT stok INTO v_stok FROM tbl_suku_cadang WHERE id_suku_cadang = NEW.id_suku_cadang;
    IF v_stok IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Suku cadang tidak ditemukan!';
    ELSEIF v_stok < NEW.jumlah_pakai THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Stok suku cadang tidak mencukupi!';
    END IF;
    
    -- Kurangi stok
    UPDATE tbl_suku_cadang 
    SET stok = stok - NEW.jumlah_pakai 
    WHERE id_suku_cadang = NEW.id_suku_cadang;
END //

-- Trigger 2: trg_stall_digunakan
-- Mengubah status stall menjadi 'terpakai' setelah transaksi pengerjaan servis dibuat.
DROP TRIGGER IF EXISTS trg_stall_digunakan //
CREATE TRIGGER trg_stall_digunakan
AFTER INSERT ON tbl_pengerjaan
FOR EACH ROW
BEGIN
    DECLARE v_id_stall INT;
    
    SELECT id_stall INTO v_id_stall FROM tbl_booking WHERE id_booking = NEW.id_booking;
    IF v_id_stall IS NOT NULL THEN
        UPDATE tbl_stall 
        SET status = 'terpakai' 
        WHERE id_stall = v_id_stall;
    END IF;
END //

-- Trigger 3: trg_stall_tersedia
-- Mengubah status stall menjadi 'tersedia' setelah status servis menjadi selesai.
DROP TRIGGER IF EXISTS trg_stall_tersedia //
CREATE TRIGGER trg_stall_tersedia
AFTER UPDATE ON tbl_booking
FOR EACH ROW
BEGIN
    IF LOWER(NEW.status) = 'selesai' AND OLD.status != NEW.status AND NEW.id_stall IS NOT NULL THEN
        UPDATE tbl_stall 
        SET status = 'tersedia' 
        WHERE id_stall = NEW.id_stall;
    END IF;
END //

-- Trigger 4: trg_histori_pengembalian_alat
-- Mencatat histori kondisi alat setelah digunakan.
DROP TRIGGER IF EXISTS trg_histori_pengembalian_alat //
CREATE TRIGGER trg_histori_pengembalian_alat
AFTER UPDATE ON tbl_peminjaman_alat
FOR EACH ROW
BEGIN
    IF NEW.status = 'dikembalikan' AND OLD.status != 'dikembalikan' THEN
        INSERT INTO tbl_histori_pengembalian_alat (id_peminjaman, id_alat, kondisi_kembali, waktu_kembali)
        VALUES (NEW.id_peminjaman, NEW.id_alat, NEW.kondisi_kembali, NEW.waktu_kembali);
    END IF;
END //

DELIMITER ;
