<?php
include "config/koneksi.php";

try {
    echo "Creating tbl_pengadaan table...\n";
    $q1 = "CREATE TABLE IF NOT EXISTS `tbl_pengadaan` (
      `id_pengadaan` int(11) NOT NULL AUTO_INCREMENT,
      `kode_pengadaan` varchar(30) NOT NULL,
      `tanggal_pengadaan` date NOT NULL,
      `jenis_pengadaan` enum('suku_cadang','alat_kerja') NOT NULL,
      `supplier` varchar(100) NOT NULL,
      `total_biaya` decimal(12,2) NOT NULL DEFAULT 0.00,
      `keterangan` text DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id_pengadaan`),
      UNIQUE KEY `kode_pengadaan` (`kode_pengadaan`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if (mysqli_query($koneksi, $q1)) {
        echo "Table tbl_pengadaan created successfully or already exists!\n";
    } else {
        echo "Failed to create tbl_pengadaan: " . mysqli_error($koneksi) . "\n";
    }

    echo "Creating tbl_pengadaan_detail table...\n";
    $q2 = "CREATE TABLE IF NOT EXISTS `tbl_pengadaan_detail` (
      `id_detail` int(11) NOT NULL AUTO_INCREMENT,
      `id_pengadaan` int(11) NOT NULL,
      `id_item` int(11) NOT NULL,
      `jumlah` int(11) NOT NULL,
      `harga_beli` decimal(12,2) NOT NULL,
      `subtotal` decimal(12,2) NOT NULL,
      PRIMARY KEY (`id_detail`),
      KEY `id_pengadaan` (`id_pengadaan`),
      CONSTRAINT `fk_detail_pengadaan` FOREIGN KEY (`id_pengadaan`) REFERENCES `tbl_pengadaan` (`id_pengadaan`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    if (mysqli_query($koneksi, $q2)) {
        echo "Table tbl_pengadaan_detail created successfully or already exists!\n";
    } else {
        echo "Failed to create tbl_pengadaan_detail: " . mysqli_error($koneksi) . "\n";
    }

    // Drop and create Function fn_hitung_subtotal_pengadaan
    echo "Creating Function fn_hitung_subtotal_pengadaan...\n";
    mysqli_query($koneksi, "DROP FUNCTION IF EXISTS fn_hitung_subtotal_pengadaan");
    $q_func = "CREATE FUNCTION fn_hitung_subtotal_pengadaan(harga DECIMAL(12,2), qty INT)
               RETURNS DECIMAL(12,2)
               DETERMINISTIC
               BEGIN
                   RETURN harga * qty;
               END;";
    if (mysqli_query($koneksi, $q_func)) {
        echo "Function fn_hitung_subtotal_pengadaan created successfully!\n";
    } else {
        echo "Failed to create function: " . mysqli_error($koneksi) . "\n";
    }

    // Triggers
    echo "Creating trigger trg_tambah_stok_pengadaan...\n";
    mysqli_query($koneksi, "DROP TRIGGER IF EXISTS trg_tambah_stok_pengadaan");
    $q_trig1 = "CREATE TRIGGER trg_tambah_stok_pengadaan
                AFTER INSERT ON tbl_pengadaan_detail
                FOR EACH ROW
                BEGIN
                    DECLARE v_jenis VARCHAR(20);
                    SELECT jenis_pengadaan INTO v_jenis FROM tbl_pengadaan WHERE id_pengadaan = NEW.id_pengadaan;
                    
                    IF v_jenis = 'suku_cadang' THEN
                        UPDATE tbl_suku_cadang 
                        SET stok = stok + NEW.jumlah 
                        WHERE id_suku_cadang = NEW.id_item;
                    ELSEIF v_jenis = 'alat_kerja' THEN
                        UPDATE tbl_alat_kerja 
                        SET jumlah = jumlah + NEW.jumlah 
                        WHERE id_alat = NEW.id_item;
                    END IF;
                END;";
    if (mysqli_query($koneksi, $q_trig1)) {
        echo "Trigger trg_tambah_stok_pengadaan created successfully!\n";
    } else {
        echo "Failed to create trigger 1: " . mysqli_error($koneksi) . "\n";
    }

    echo "Creating trigger trg_kurangi_stok_pengadaan_delete...\n";
    mysqli_query($koneksi, "DROP TRIGGER IF EXISTS trg_kurangi_stok_pengadaan_delete");
    $q_trig2 = "CREATE TRIGGER trg_kurangi_stok_pengadaan_delete
                AFTER DELETE ON tbl_pengadaan_detail
                FOR EACH ROW
                BEGIN
                    DECLARE v_jenis VARCHAR(20);
                    SELECT jenis_pengadaan INTO v_jenis FROM tbl_pengadaan WHERE id_pengadaan = OLD.id_pengadaan;
                    
                    IF v_jenis = 'suku_cadang' THEN
                        UPDATE tbl_suku_cadang 
                        SET stok = GREATEST(0, stok - OLD.jumlah)
                        WHERE id_suku_cadang = OLD.id_item;
                    ELSEIF v_jenis = 'alat_kerja' THEN
                        UPDATE tbl_alat_kerja 
                        SET jumlah = GREATEST(0, jumlah - OLD.jumlah)
                        WHERE id_alat = OLD.id_item;
                    END IF;
                END;";
    if (mysqli_query($koneksi, $q_trig2)) {
        echo "Trigger trg_kurangi_stok_pengadaan_delete created successfully!\n";
    } else {
        echo "Failed to create trigger 2: " . mysqli_error($koneksi) . "\n";
    }

    echo "Creating trigger trg_update_total_pengadaan_insert...\n";
    mysqli_query($koneksi, "DROP TRIGGER IF EXISTS trg_update_total_pengadaan_insert");
    $q_trig3 = "CREATE TRIGGER trg_update_total_pengadaan_insert
                AFTER INSERT ON tbl_pengadaan_detail
                FOR EACH ROW
                BEGIN
                    UPDATE tbl_pengadaan 
                    SET total_biaya = (SELECT IFNULL(SUM(subtotal), 0) FROM tbl_pengadaan_detail WHERE id_pengadaan = NEW.id_pengadaan)
                    WHERE id_pengadaan = NEW.id_pengadaan;
                END;";
    if (mysqli_query($koneksi, $q_trig3)) {
        echo "Trigger trg_update_total_pengadaan_insert created successfully!\n";
    } else {
        echo "Failed to create trigger 3: " . mysqli_error($koneksi) . "\n";
    }

    echo "Creating trigger trg_update_total_pengadaan_delete...\n";
    mysqli_query($koneksi, "DROP TRIGGER IF EXISTS trg_update_total_pengadaan_delete");
    $q_trig4 = "CREATE TRIGGER trg_update_total_pengadaan_delete
                AFTER DELETE ON tbl_pengadaan_detail
                FOR EACH ROW
                BEGIN
                    UPDATE tbl_pengadaan 
                    SET total_biaya = (SELECT IFNULL(SUM(subtotal), 0) FROM tbl_pengadaan_detail WHERE id_pengadaan = OLD.id_pengadaan)
                    WHERE id_pengadaan = OLD.id_pengadaan;
                END;";
    if (mysqli_query($koneksi, $q_trig4)) {
        echo "Trigger trg_update_total_pengadaan_delete created successfully!\n";
    } else {
        echo "Failed to create trigger 4: " . mysqli_error($koneksi) . "\n";
    }

    echo "DB Upgrade for Pengadaan Completed successfully!\n";
} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
}
?>
