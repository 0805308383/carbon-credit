<?php
require 'config/db.php';
pg_query($conn, "ALTER TABLE orders ADD COLUMN buy_amount DECIMAL(10,2) NULL AFTER price");
echo "Done DB update_db_schema_v4";
?>
