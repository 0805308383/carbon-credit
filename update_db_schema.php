<?php
require_once 'config/db.php';

// Add national_id column
$sql1 = "ALTER TABLE users ADD COLUMN national_id VARCHAR(13) DEFAULT NULL UNIQUE AFTER password";

// Add last_phone_change_date column
$sql2 = "ALTER TABLE users ADD COLUMN last_phone_change_date DATE DEFAULT NULL AFTER phone_change_year";

if ($conn->query($sql1) === TRUE) {
    echo "Column national_id added successfully.<br>";
} else {
    echo "Error adding national_id: " . $conn->error . "<br>";
}

if ($conn->query($sql2) === TRUE) {
    echo "Column last_phone_change_date added successfully.<br>";
} else {
    echo "Error adding last_phone_change_date: " . $conn->error . "<br>";
}

$conn->close();
?>
