<?php
$host = "aws-1-ap-southeast-2.pooler.supabase.com";
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