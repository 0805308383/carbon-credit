<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request');
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$captcha = $_POST['captcha'] ?? '';

// ตรวจ Captcha
if (!isset($_SESSION['captcha']) || $captcha != $_SESSION['captcha']) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'รหัสยืนยันตัวตน (Captcha) ไม่ถูกต้อง'
    ];
    header("Location: ../auth/login.php");
    exit;
}
unset($_SESSION['captcha']); // ใช้แล้วลบทิ้งทันที

// ดึงข้อมูล user
$result = mysqli_query($conn, "
    SELECT * FROM users WHERE username = '$username' LIMIT 1
");

$user = mysqli_fetch_assoc($result);

// ไม่พบผู้ใช้
if (!$user) {
    mysqli_query($conn, "INSERT INTO logs_login (user_id, ip_address, status) VALUES (NULL, '{$_SERVER['REMOTE_ADDR']}', 'failed')");
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'ไม่พบผู้ใช้งาน'
    ];
    header("Location: ../auth/login.php");
    exit;
}

// ตรวจรหัสผ่าน
if (!password_verify($password, $user['password'])) {
    mysqli_query($conn, "INSERT INTO logs_login (user_id, ip_address, status) VALUES ('{$user['id']}', '{$_SERVER['REMOTE_ADDR']}', 'failed')");
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'รหัสผ่านไม่ถูกต้อง'
    ];
    header("Location: ../auth/login.php");
    exit;
}

// ตรวจสอบสถานะบัญชี (Banned)
if ($user['status'] === 'banned') {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'บัญชีถูกระงับการใช้งาน',
        'message' => 'เหตุผล: ' . ($user['banned_reason'] ? $user['banned_reason'] : 'ละเมิดนโยบายของระบบ')
    ];
    header("Location: ../auth/login.php");
    exit;
}

// ✅ login สำเร็จ → ตั้ง session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']    = $user['role'];

// Log success
mysqli_query($conn, "INSERT INTO logs_login (user_id, ip_address, status) VALUES ('{$user['id']}', '{$_SERVER['REMOTE_ADDR']}', 'success')");

// ❌ ห้ามเช็ก OTP ตรงนี้เด็ดขาด
// ❌ ห้าม redirect ไป verify_otp.php

// ไป dashboard เท่านั้น
// login สำเร็จ
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']    = $user['role'];

$_SESSION['flash_alert'] = [
    'type' => 'success',
    'title' => 'เข้าสู่ระบบสำเร็จ',
    'message' => 'ยินดีต้อนรับคุณ ' . $user['username']
];

if ($user['role'] === 'admin') {
    header("Location: ../admin/dashboard.php");
} else {
    header("Location: ../dashboard/index.php");
}
exit;
