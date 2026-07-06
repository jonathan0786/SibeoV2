<?php
include "config/koneksi.php";

try {
    echo "Adding dummy mechanic...\n";
    $query_add = mysqli_query($koneksi, "INSERT INTO tbl_mekanik (nama, kepegawaian, spesialisasi, shift, username, password) 
                                         VALUES ('Dummy Mekanik', '12345678', 'Mesin & Oli', 'pagi', 'dummy', 'dummy123')");
    $id_dummy = mysqli_insert_id($koneksi);
    echo "Added dummy with ID: $id_dummy\n";

    echo "Deleting dummy mechanic...\n";
    $query_del = mysqli_query($koneksi, "DELETE FROM tbl_mekanik WHERE id_mekanik='$id_dummy'");
    echo "Deleted successfully!\n";
} catch (Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
?>
