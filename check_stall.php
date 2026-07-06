<?php
include "config/koneksi.php";

echo "--- tbl_stall columns ---\n";
$q = mysqli_query($koneksi, "SHOW COLUMNS FROM tbl_stall");
while ($r = mysqli_fetch_assoc($q)) {
    print_r($r);
}

echo "\n--- tbl_stall rows ---\n";
$q2 = mysqli_query($koneksi, "SELECT * FROM tbl_stall");
while ($r2 = mysqli_fetch_assoc($q2)) {
    print_r($r2);
}
?>
