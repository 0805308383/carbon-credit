<?php
require 'config/db.php';

$sql = "ALTER TABLE `carbon_listings` 
    ADD COLUMN `province` VARCHAR(100) AFTER `seller_id`,
    ADD COLUMN `avg_height` DECIMAL(5,2) AFTER `tree_height`,
    ADD COLUMN `rice_age` INT(11) AFTER `rice_area`,
    ADD COLUMN `full_tree_image` VARCHAR(255) AFTER `image`,
    ADD COLUMN `reference_image` VARCHAR(255) AFTER `full_tree_image`,
    ADD COLUMN `id_card_image` VARCHAR(255) AFTER `reference_image`,
    CHANGE COLUMN `image` `image` VARCHAR(255) NULL;";

if (pg_query($conn, $sql)) {
    echo "Database schema updated successfully.\n";
} else {
    echo "Error updating database schema: " . pg_error($conn) . "\n";
}

// Ensure ref_provinces has data (optional but good for testing)
$check = pg_query($conn, "SELECT COUNT(*) as count FROM ref_provinces");
$row = pg_fetch_assoc($check);
if ($row['count'] == 0) {
    echo "Warning: ref_provinces is empty. Please ensure you have province data.\n";
}
?>
