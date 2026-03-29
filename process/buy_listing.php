<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    exit('เฉพาะผู้ใช้งานทั่วไป');
}

$buyer_id   = (int)$_SESSION['user_id'];
$listing_id = (int)($_REQUEST['id'] ?? 0);
$buy_amount = (float)($_REQUEST['buy_amount'] ?? 0);

$q = pg_query($conn, "
    SELECT id, type, tree_count, rice_area, carbon_amount, price_token, remaining_amount, status 
    FROM carbon_listings
    WHERE id = $listing_id AND status = 'approved'
");

$row = mysqli_fetch_assoc($q);
if (!$row) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'ไม่พบรายการขายที่ต้องการ'
    ];
    header("Location: ../dashboard/marketplace.php");
    exit;
}

$rem = $row['remaining_amount'] ?? ($row['type'] === 'tree' ? $row['tree_count'] : $row['rice_area']);
if ($rem <= 0) {
    $_SESSION['flash_alert'] = ['type' => 'error', 'title' => 'ผิดพลาด', 'message' => 'สินค้านี้ถูกขายหมดแล้ว'];
    header("Location: ../dashboard/marketplace.php");
    exit;
}

$total_price = (float)$row['price_token'];

if ($buy_amount <= 0) {
    $actual_buy_amount = $rem;
    $final_price = $total_price; 
} else {
    // Buy partial
    if ($row['type'] === 'tree') {
        $actual_buy_amount = (int)$buy_amount; 
        if ($actual_buy_amount < 1) die('Minimum 1 tree');
        $total_units = (int)$row['tree_count'];
    } else {
        $actual_buy_amount = $buy_amount / 400; // back to Rai
        if (($buy_amount % 20) != 0) die('Must be multiple of 20 sq.wah');
        $total_units = (float)$row['rice_area'];
    }

    if ($actual_buy_amount > $rem) {
        $_SESSION['flash_alert'] = ['type' => 'warning', 'title' => 'ผิดพลาด', 'message' => 'จำนวนที่ซื้อเกินกว่าคงเหลือ'];
        header("Location: ../dashboard/marketplace.php");
        exit;
    }
    
    // Proportional price calculation
    $final_price = ($actual_buy_amount / $total_units) * $total_price;
}

// Check Buyer Wallet Balance
$wallet_q = pg_query($conn, "SELECT token FROM wallets WHERE user_id = $buyer_id");
$wallet = mysqli_fetch_assoc($wallet_q);

if (!$wallet || (float)$wallet['token'] < $final_price) {
    $_SESSION['flash_alert'] = [
        'type' => 'warning',
        'title' => 'ยอดเงินไม่เพียงพอ',
        'message' => 'คุณมีเหรียญไม่เพียงพอสำหรับซื้อรายการนี้ กรุณาเติมเหรียญก่อน'
    ];
    header("Location: ../dashboard/marketplace.php");
    exit;
}

// สร้าง OTP และเก็บ Session
$otp = rand(100000, 999999);
$_SESSION['otp_demo'] = $otp;

$_SESSION['otp_pending_transaction'] = [
    'type' => 'buy_listing',
    'buyer_id' => $buyer_id,
    'listing_id' => $listing_id,
    'price' => $final_price,
    'buy_amount' => $actual_buy_amount
];

header("Location: ../auth/verify_transaction_otp.php");
exit;
