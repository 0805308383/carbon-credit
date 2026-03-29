<?php
require 'config/db.php';

echo "<h2>Starting Database Schema Update V3...</h2>";

$queries = [
    // 1. Add first_name and last_name to users
    "ALTER TABLE users ADD COLUMN first_name VARCHAR(100) NULL AFTER id",
    "ALTER TABLE users ADD COLUMN last_name VARCHAR(100) NULL AFTER first_name",
    
    // 2. Add land_document and remaining_amount to carbon_listings
    "ALTER TABLE carbon_listings ADD COLUMN land_document VARCHAR(255) NULL AFTER image",
    "ALTER TABLE carbon_listings ADD COLUMN remaining_amount DECIMAL(10,2) NULL AFTER price_token"
];

foreach ($queries as $sql) {
    if (pg_query($conn, $sql)) {
        echo "<p style='color:green;'>Success: $sql</p>";
    } else {
        $error = mysqli_error($conn);
        if (strpos($error, 'Duplicate column name') !== false) {
            echo "<p style='color:blue;'>Skipping: Column already exists - $sql</p>";
        } else {
            echo "<p style='color:red;'>Error executing: $sql <br> Error: $error</p>";
        }
    }
}

echo "<h3>Update Complete!</h3>";
?>
