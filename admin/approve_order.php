<?php
session_start();
require '../config/db.php';
require_once '../includes/mailer.php';

// เช็กแอดมิน
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะแอดมิน');
}

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    exit('order ไม่ถูกต้อง');
}

/* ======================
   1) ดึงข้อมูล order
====================== */
$qOrder = pg_query($conn, "
    SELECT * FROM orders
    WHERE id = $order_id AND status = 'pending_admin'
");
$order = mysqli_fetch_assoc($qOrder);

if (!$order) {
    exit('ไม่พบคำสั่งซื้อ หรือถูกจัดการแล้ว');
}

$buyer_id   = (int)$order['buyer_id'];
$listing_id = (int)$order['listing_id'];
$price      = (float)$order['price'];

/* ======================
   2) ดึง listing + seller
====================== */
$qListing = pg_query($conn, "
    SELECT seller_id, status, remaining_amount, type, tree_count, rice_area, carbon_amount, province
    FROM carbon_listings
    WHERE id = $listing_id
");
$listing = mysqli_fetch_assoc($qListing);

if (!$listing) {
    exit('รายการขายไม่ถูกต้อง');
}

if ($listing['status'] === 'sold') {
    exit('รายการนี้ถูกขายไปแล้ว');
}

$buy_amount = (float)($order['buy_amount'] ?? 0);
$current_rem = $listing['remaining_amount'] ?? ($listing['type'] === 'tree' ? $listing['tree_count'] : $listing['rice_area']);
if ($buy_amount <= 0) {
    $buy_amount = $current_rem;
}

if ($buy_amount > $current_rem) {
    pg_query($conn, "
        UPDATE orders SET status='rejected'
        WHERE id = $order_id
    ");
    exit('ยอดคาร์บอนคงเหลือไม่เพียงพอสำหรับคำสั่งซื้อนี้ (ถูกซื้อไปก่อนหน้าแล้ว)');
}

$seller_id = (int)$listing['seller_id'];

// คำนวณปริมาณคาร์บอนที่ได้รับจริง
$capacity = ($listing['type'] === 'tree') ? $listing['tree_count'] : $listing['rice_area'];
$exact_carbon_bought = ($buy_amount / ($capacity ?: 1)) * $listing['carbon_amount'];

// ดึงข้อมูลผู้ซื้อสำหรับส่งอีเมล
$qBuyer = pg_query($conn, "SELECT email, first_name, last_name FROM users WHERE id = $buyer_id");
$buyerInfo = mysqli_fetch_assoc($qBuyer);

/* ======================
   3) ดึง wallet buyer
====================== */
$qBuyerWallet = pg_query($conn, "
    SELECT token FROM wallets WHERE user_id = $buyer_id
");
$buyerWallet = mysqli_fetch_assoc($qBuyerWallet);

if (!$buyerWallet || $buyerWallet['token'] < $price) {
    pg_query($conn, "
        UPDATE orders SET status='rejected'
        WHERE id = $order_id
    ");
    exit('Token ผู้ซื้อไม่เพียงพอ');
}

/* ======================
   4) ดึง / สร้าง wallet seller
====================== */
$qSellerWallet = pg_query($conn, "
    SELECT token FROM wallets WHERE user_id = $seller_id
");

if (mysqli_num_rows($qSellerWallet) == 0) {
    pg_query($conn, "
        INSERT INTO wallets (user_id, balance, token)
        VALUES ($seller_id, 0, 0)
    ");
}

/* ======================
   เริ่ม Transaction
====================== */
pg_query($conn, "START TRANSACTION");

/* 5) หัก Token buyer และเพิ่ม carbon_balance */
pg_query($conn, "
    UPDATE wallets
    SET token = token - $price,
        carbon_balance = carbon_balance + $exact_carbon_bought
    WHERE user_id = $buyer_id
");

/* 6) เติม Token seller */
pg_query($conn, "
    UPDATE wallets
    SET token = token + $price
    WHERE user_id = $seller_id
");

/* 7) อัปเดต order */
pg_query($conn, "
    UPDATE orders
    SET status='approved'
    WHERE id = $order_id
");

/* 8) หักยอดคงเหลือและปิด listing (ถ้ายังไม่ได้ปิด) */
pg_query($conn, "
    UPDATE carbon_listings
    SET remaining_amount = remaining_amount - $buy_amount
    WHERE id = $listing_id
");

pg_query($conn, "
    UPDATE carbon_listings
    SET status='sold'
    WHERE id = $listing_id AND remaining_amount <= 0
");

pg_query($conn, "COMMIT");

/* ส่งอีเมลยืนยัน */
if ($buyerInfo && !empty($buyerInfo['email'])) {
    $orderDetails = [
        'order_id' => str_pad($order_id, 6, "0", STR_PAD_LEFT),
        'project_type' => ($listing['type'] === 'tree' ? 'ต้นไม้' : 'นาข้าว'),
        'province' => $listing['province'],
        'buy_amount' => $buy_amount . ($listing['type'] === 'tree' ? ' ต้น' : ' ตร.ว.'),
        'carbon_amount' => number_format($exact_carbon_bought, 2),
        'price' => number_format($price, 2),
        'transaction_date' => date('d M Y H:i')
    ];
    $buyerName = trim($buyerInfo['first_name'] . ' ' . $buyerInfo['last_name']);
    if (empty($buyerName)) $buyerName = "ผู้ใช้งาน";
    
    sendOrderConfirmationEmail($buyerInfo['email'], $buyerName, $orderDetails);
}

header("Location: manage_orders.php");
exit;
