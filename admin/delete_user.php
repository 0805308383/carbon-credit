<?php
session_start();
require '../config/db.php';

// รับเฉพาะ POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = intval($_POST['id']);
$reason = trim($_POST['reason']);

if (!$user_id || empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน (รหัสผู้ใช้หรือเหตุผล)']);
    exit;
}

// ป้องกัน SQL Injection
$reason = mysqli_real_escape_string($conn, $reason);

// ตรวจสอบว่าผู้ใช้มีอยู่และไม่ใช่ Admin
$check_query = mysqli_query($conn, "SELECT role FROM users WHERE id = $user_id");
if (mysqli_num_rows($check_query) == 0) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบผู้ใช้งาน']);
    exit;
}

$user = mysqli_fetch_assoc($check_query);
if ($user['role'] === 'admin') {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถระงับบัญชี Admin ได้']);
    exit;
}

// อัปเดตสถานะเป็น banned
$update_query = "UPDATE users SET status = 'banned', banned_reason = '$reason' WHERE id = $user_id";
if (mysqli_query($conn, $update_query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในฐานข้อมูล']);
}
