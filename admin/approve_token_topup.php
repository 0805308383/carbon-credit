<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('ไม่มีสิทธิ์');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) exit('ข้อมูลไม่ถูกต้อง');

// ดึงข้อมูล topup
$q = pg_query($conn, "
    SELECT * FROM token_topups
    WHERE id = $id AND status='pending_admin'
");
$topup = mysqli_fetch_assoc($q);
if (!$topup) exit('ไม่พบรายการ');

$user_id = (int)$topup['user_id'];
$token   = (float)$topup['token_amount'];

mysqli_begin_transaction($conn);

try {
    // 🔥 สร้าง wallet ถ้ายังไม่มี (จุดที่ขาด)
    $check = pg_query($conn, "
        SELECT id FROM wallets WHERE user_id = $user_id
    ");
    if (mysqli_num_rows($check) == 0) {
        pg_query($conn, "
            INSERT INTO wallets (user_id, balance, token)
            VALUES ($user_id, 0, 0)
        ");
    }

    // เพิ่ม token ให้ buyer
    pg_query($conn, "
        UPDATE wallets
        SET token = token + $token
        WHERE user_id = $user_id
    ");

    // เปลี่ยนสถานะคำขอ
    pg_query($conn, "
        UPDATE token_topups
        SET status='approved'
        WHERE id = $id
    ");

    mysqli_commit($conn);

} catch (Exception $e) {
    mysqli_rollback($conn);
    exit('ผิดพลาด');
}

header("Location: manage_token_topups.php");
exit;
