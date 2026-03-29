<?php
require 'config/db.php';
$res = pg_query($conn, "DESCRIBE users");
while($row = pg_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
