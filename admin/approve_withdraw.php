<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะแอดมิน');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('ID ไม่ถูกต้อง');
}

$q = mysqli_query($conn, "
    SELECT * FROM withdraw_requests
    WHERE id = $id AND status = 'pending'
");

$data = mysqli_fetch_assoc($q);
if (!$data) {
    exit('ไม่พบคำขอ');
}

mysqli_query($conn, "
    UPDATE withdraw_requests
    SET status = 'approved'
    WHERE id = $id
");

header("Location: manage_withdraws.php");
exit;
