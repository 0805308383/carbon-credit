<?php
require 'config/db.php';

$sql = "ALTER TABLE token_topups ADD COLUMN slip_image VARCHAR(255) DEFAULT NULL";

if (mysqli_query($conn, $sql)) {
    echo "Column 'slip_image' added successfully.";
} else {
    echo "Error adding column: " . mysqli_error($conn);
}
?>
