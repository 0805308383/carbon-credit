<?php
require 'config/db.php';

// disable foreign key checks temporarily if any (though engine is myisam/innodb without strict fk usually in simple setups)
pg_query($conn, "SET FOREIGN_KEY_CHECKS = 0;");

$tables_to_truncate = [
    'carbon_listings',
    'orders',
    'carbon_transactions',
    'token_topups',
    'withdraw_requests',
    'seller_requests',
    'token_conversions',
    'otp_verifications'
];

foreach ($tables_to_truncate as $table) {
    echo "Clearing $table... ";
    if (pg_query($conn, "TRUNCATE TABLE $table")) {
        echo "Done.\n";
    } else {
        echo "Error: " . pg_error($conn) . "\n";
    }
}

echo "Resetting wallets... ";
if (pg_query($conn, "UPDATE wallets SET balance = 0, token = 0")) {
    echo "Done.\n";
} else {
    echo "Error: " . pg_error($conn) . "\n";
}

pg_query($conn, "SET FOREIGN_KEY_CHECKS = 1;");

echo "\nData cleanup completed successfully. Marketplace is now fresh!\n";
?>
