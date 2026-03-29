<?php
require_once 'config/db.php';

$sql = "ALTER TABLE wallets ADD COLUMN carbon_balance DECIMAL(15,2) DEFAULT 0.00 AFTER token";

if (pg_query($conn, $sql)) {
    echo "Column 'carbon_balance' added successfully.";
} else {
    echo "Error adding column: " . mysqli_error($conn);
}
?>
