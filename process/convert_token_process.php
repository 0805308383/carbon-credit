<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    exit('เฉพาะผู้ใช้งานเท่านั้น');
}

$user_id = (int)$_SESSION['user_id'];
$token_amount = (float)$_POST['token_amount'];

$rate = 1; // 1 Token = 1 บาท

$wallet = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT token, balance FROM wallets WHERE user_id = $user_id
"));

if ($token_amount <= 0 || $token_amount > $wallet['token']) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'จำนวน Token ไม่ถูกต้อง'
    ];
    header("Location: ../dashboard/convert_token.php");
    exit;
}

$money = $token_amount * $rate;

mysqli_query($conn, "START TRANSACTION");

// หัก token
mysqli_query($conn, "
    UPDATE wallets
    SET token = token - $token_amount
    WHERE user_id = $user_id
");

// เพิ่ม balance
mysqli_query($conn, "
    UPDATE wallets
    SET balance = balance + $money
    WHERE user_id = $user_id
");

// บันทึกประวัติ
mysqli_query($conn, "
    INSERT INTO token_conversions
    (user_id, token_amount, rate, money_amount)
    VALUES ($user_id, $token_amount, $rate, $money)
");

mysqli_query($conn, "COMMIT");

$_SESSION['flash_alert'] = [
    'type' => 'success',
    'title' => 'สำเร็จ',
    'message' => 'แปลง Token เป็นเงินสำเร็จ'
];

header("Location: ../dashboard/index.php");
exit;
