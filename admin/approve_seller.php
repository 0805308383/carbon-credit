<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('ไม่มีสิทธิ์');
}

$request_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$request_id || !$action) {
    exit('ข้อมูลไม่ครบ');
}

// ดึงคำขอ
$request = mysqli_query($conn, "
    SELECT * FROM seller_requests WHERE id = $request_id
");

$data = mysqli_fetch_assoc($request);
if (!$data) {
    exit('ไม่พบคำขอ');
}

$user_id = (int)$data['user_id'];

if ($action === 'approve') {

    // 🔒 กัน approve ซ้ำ
    if ($data['status'] === 'approved') {
        header("Location: seller_requests.php");
        exit;
    }

    // 1) เปลี่ยน role เป็น seller
    mysqli_query($conn, "
        UPDATE users 
        SET role='seller'
        WHERE id=$user_id
    ");

    // 2) อัปเดตสถานะคำขอ
    mysqli_query($conn, "
        UPDATE seller_requests
        SET status='approved', approved_at=NOW()
        WHERE id=$request_id
    ");

    // 3) สร้าง wallet ถ้ายังไม่มี (สำคัญสุด)
    $checkWallet = mysqli_query($conn, "
        SELECT id FROM wallets WHERE user_id = $user_id
    ");

    if (mysqli_num_rows($checkWallet) == 0) {
        mysqli_query($conn, "
            INSERT INTO wallets (user_id, balance, token)
            VALUES ($user_id, 0, 0)
        ");
    }

    // กลับไปหน้ารายการ
    header("Location: seller_requests.php");
    exit;

} elseif ($action === 'reject') {

    // อัปเดตสถานะเป็น rejected
    mysqli_query($conn, "
        UPDATE seller_requests
        SET status='rejected'
        WHERE id=$request_id
    ");

    header("Location: seller_requests.php");
    exit;

} else {
    exit('action ไม่ถูกต้อง');
}
