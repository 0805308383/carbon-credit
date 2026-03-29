<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('ไม่มีสิทธิ์');
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$id || !$action) {
    exit('ข้อมูลไม่ครบ');
}

if ($action === 'approve') {
    mysqli_query($conn, "
        UPDATE carbon_listings
        SET status='approved'
        WHERE id=$id
    ");
    $_SESSION['flash_alert'] = [
        'type' => 'success',
        'title' => 'อนุมัติสำเร็จ',
        'message' => 'รายการขายถูกอนุมัติเรียบร้อยแล้ว'
    ];
}

if ($action === 'reject') {
    mysqli_query($conn, "
        UPDATE carbon_listings
        SET status='rejected'
        WHERE id=$id
    ");
    $_SESSION['flash_alert'] = [
        'type' => 'success',
        'title' => 'ปฏิเสธสำเร็จ',
        'message' => 'ปฏิเสธรายการขายเรียบรอกแล้ว'
    ];
}

header("Location: manage_listings.php");
exit;
