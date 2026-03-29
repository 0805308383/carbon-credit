<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['otp_pending_transaction'])) {
    exit('ไม่มีข้อมูลรายการ');
}

$inputOtp = $_POST['otp'];
$demoOtp  = $_SESSION['otp_demo'];

if ($inputOtp != $demoOtp) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'OTP ไม่ถูกต้อง'
    ];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

$transaction = $_SESSION['otp_pending_transaction'];
$type = $transaction['type'];

if ($type === 'buy_listing') {
    $buyer_id   = $transaction['buyer_id'];
    $listing_id = $transaction['listing_id'];
    $price      = $transaction['price'];
    $buy_amount = $transaction['buy_amount'] ?? 0;

    // Re-verify Balance before final insert
    $wallet_q = pg_query($conn, "SELECT token FROM wallets WHERE user_id = $buyer_id");
    $wallet = mysqli_fetch_assoc($wallet_q);

    if (!$wallet || (float)$wallet['token'] < $price) {
        $_SESSION['flash_alert'] = [
            'type' => 'error',
            'title' => 'ผิดพลาด',
            'message' => 'ยอดเงินไม่เพียงพอ กรุณาตรวจสอบกระเป๋าเงินของคุณ'
        ];
        header("Location: ../dashboard/marketplace.php");
        exit;
    }

    // Insert Order
    pg_query($conn, "
        INSERT INTO orders (buyer_id, listing_id, price, buy_amount, status)
        VALUES ($buyer_id, $listing_id, $price, $buy_amount, 'pending_admin')
    ");

    // Amount is now deducted directly at admin/approve_order.php
    $_SESSION['flash_alert'] = [
        'type' => 'success',
        'title' => 'สั่งซื้อสำเร็จ',
        'message' => 'รายการสั่งซื้อของคุณรอการอนุมัติจาก Admin'
    ];
    header("Location: ../dashboard/index.php");

} elseif ($type === 'create_listing') {
    // Note: create_listing_process.php was already updated to not use this logic 
    // but I'll update it here for consistency if any old flow uses it.
    $seller_id = $transaction['seller_id'];
    // ... rest of the code ...
    pg_query($conn, $sql) or die(mysqli_error($conn));

    $_SESSION['flash_alert'] = [
        'type' => 'success',
        'title' => 'ลงขายสำเร็จ',
        'message' => 'ข้อมูลงขายของคุณถูกส่งให้ Admin ตรวจสอบแล้ว'
    ];
    header("Location: ../dashboard/index.php");
}

// Clear session
unset($_SESSION['otp_pending_transaction']);
unset($_SESSION['otp_demo']);
?>
