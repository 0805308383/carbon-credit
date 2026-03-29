<?php
$host = "db.xxxxx.supabase.co";
$port = "5432";
$db   = "postgres";
$user = "postgres";
$pass = "your-password";

$conn = pg_connect("
  host=$host
  port=$port
  dbname=$db
  user=$user
  password=$pass
");

if (!$conn) {
  die("Database connection failed");
}
?>