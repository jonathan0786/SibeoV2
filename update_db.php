<?php
include "config/koneksi.php";

try {
    echo "Altering tbl_stall status column to enum('tersedia', 'terpakai', 'maintenance')...\n";
    $alter_query = "ALTER TABLE tbl_stall MODIFY COLUMN status ENUM('tersedia', 'terpakai', 'maintenance') NOT NULL DEFAULT 'tersedia'";
    if (mysqli_query($koneksi, $alter_query)) {
        echo "Alter table successful!\n";
    } else {
        echo "Alter table failed: " . mysqli_error($koneksi) . "\n";
    }

    echo "Updating invalid stall status values (empty or invalid) to 'maintenance' or default...\n";
    // First, let's see which stalls are invalid or empty
    // On MySQL, invalid enum values when strict mode is off become empty string ''
    $update_query = "UPDATE tbl_stall SET status = 'maintenance' WHERE status = '' OR status IS NULL OR status = 'terisi'";
    if (mysqli_query($koneksi, $update_query)) {
        echo "Stall status values updated successfully!\n";
    } else {
        echo "Update failed: " . mysqli_error($koneksi) . "\n";
    }

} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
}
?>
