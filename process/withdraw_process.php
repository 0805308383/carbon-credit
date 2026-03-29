<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    exit('เฉพาะผู้ใช้งานเท่านั้น');
}

$user_id = (int)$_SESSION['user_id'];
$amount  = (float)($_POST['amount'] ?? 0);

if ($amount <= 0) {
    exit('จำนวนเงินไม่ถูกต้อง');
}

/* ======================
   1) ตรวจ wallet
====================== */
$wallet = mysqli_fetch_assoc(pg_query($conn, "
    SELECT balance FROM wallets WHERE user_id = $user_id
"));

if (!$wallet || $wallet['balance'] < $amount) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'ยอดเงินไม่เพียงพอ'
    ];
    header("Location: ../dashboard/withdraw.php");
    exit;
}

/* ======================
   2) ดึงบัญชีธนาคาร (ใช้ชื่อ column ให้ตรง DB)
====================== */
$bank = mysqli_fetch_assoc(pg_query($conn, "
    SELECT bank_name, account_number, account_name
    FROM bank_accounts
    WHERE user_id = $user_id
"));

if (!$bank) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'ไม่พบบัญชีธนาคาร กรุณาตั้งค่าโปรไฟล์ก่อน'
    ];
    header("Location: ../dashboard/profile.php");
    exit;
}

pg_query($conn, "START TRANSACTION");

/* ======================
   3) หักเงินจาก wallet
====================== */
pg_query($conn, "
    UPDATE wallets
    SET balance = balance - $amount
    WHERE user_id = $user_id
");

/* ======================
   4) บันทึกคำขอถอนเงิน
====================== */
pg_query($conn, "
    INSERT INTO withdraw_requests
        (user_id, amount, bank, bank_account, account_name, status)
    VALUES
        (
            $user_id,
            $amount,
            '{$bank['bank_name']}',
            '{$bank['account_number']}',
            '{$bank['account_name']}',
            'pending'
        )
");

/* ======================
   5) commit
====================== */
pg_query($conn, "COMMIT");

$_SESSION['flash_alert'] = [
    'type' => 'success',
    'title' => 'แจ้งถอนเงินสำเร็จ',
    'message' => 'ระบบจะดำเนินการโอนเงินให้คุณภายใน 24 ชม.'
];

header("Location: ../dashboard/index.php");
exit;
