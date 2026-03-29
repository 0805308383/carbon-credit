<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    exit('เฉพาะผู้ใช้งานทั่วไป');
}

$user_id = (int)$_SESSION['user_id'];
$token = (float)($_POST['token_amount'] ?? 0);

if ($token <= 0) {
    exit('จำนวนไม่ถูกต้อง');
}

// อัตรา mock
$price = $token * 1; // 1 Token = 1 บาท

// Handle File Upload - Mandatory
if (!isset($_FILES['slip']) || $_FILES['slip']['error'] !== 0) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ไม่พบคลิปการโอน',
        'message' => 'กรุณาแนบรูปภาพสลิปการโอนเงินเพื่อยืนยัน'
    ];
    header("Location: ../dashboard/buy_token.php");
    exit;
}

$allowed = ['jpg', 'jpeg', 'png', 'webp'];
$filename = $_FILES['slip']['name'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ไฟล์ไม่รองรับ',
        'message' => 'กรุณาอัปโหลดรูปภาพสลิปในรูปแบบ JPG, PNG หรือ WebP เท่านั้น'
    ];
    header("Location: ../dashboard/buy_token.php");
    exit;
}

// Create upload dir if not exists
$upload_dir = '../uploads/slips/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$new_name = 'slip_' . time() . '_' . rand(1000,9999) . '.' . $ext;
$dest = $upload_dir . $new_name;

if (!move_uploaded_file($_FILES['slip']['tmp_name'], $dest)) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'อัปโหลดล้มเหลว',
        'message' => 'ไม่สามารถบันทึกรูปภาพสลิปได้ กรุณาลองใหม่อีกครั้ง'
    ];
    header("Location: ../dashboard/buy_token.php");
    exit;
}

$slip_path = 'uploads/slips/' . $new_name;

// Insert with mandatory slip image
$sql = "INSERT INTO token_topups (user_id, token_amount, price, slip_image)
        VALUES ($user_id, $token, $price, '$slip_path')";

if (!pg_query($conn, $sql)) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'
    ];
    header("Location: ../dashboard/buy_token.php");
    exit;
}

$_SESSION['flash_alert'] = [
    'type' => 'success',
    'title' => 'สั่งซื้อสำเร็จ',
    'message' => 'ระบบได้รับคำสั่งซื้อของคุณแล้ว และจะดำเนินการตรวจสอบสลิปโดยเร็ว'
];

header("Location: ../dashboard/index.php");
exit;
?>
