<?php
$host = "aws-1-ap-southeast-2.pooler.supabase.com";
$port = "6543"; // เปลี่ยน!
$db   = "postgres";
$user = "postgres.hzcyclqcxomqetbhthnq"; // สำคัญ!
$pass = "carboncredit-simulator";

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