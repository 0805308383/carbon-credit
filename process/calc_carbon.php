<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    exit('เฉพาะผู้ใช้งานเท่านั้น');
}

$user_id = (int)$_SESSION['user_id'];
$activity = $_POST['activity'] ?? 'General';
$carbon_amount = isset($_POST['calculated_carbon']) ? (float)$_POST['calculated_carbon'] : 0;

if ($carbon_amount <= 0) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'ยอดคาร์บอนต้องมากกว่า 0'
    ];
    header("Location: ../dashboard/calculator.php");
    exit;
}

// ... logic ...
pg_query($conn, "
    INSERT INTO carbon_transactions (user_id, activity_type, amount_input, token_generated)
    VALUES ($user_id, '$activity', $carbon_amount, $token)
");

pg_query($conn, "
    UPDATE wallets
    SET token = token + $token
    WHERE user_id = $user_id
");

$_SESSION['flash_alert'] = [
    'type' => 'success',
    'title' => 'บันทึกสำเร็จ',
    'message' => 'คุณได้รับ ' . number_format($token, 2) . ' Token จากคาร์บอน ' . number_format($carbon_amount, 2) . ' Ton'
];

header("Location: ../dashboard/index.php");
exit;
?>
