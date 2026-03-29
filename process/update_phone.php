<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$new_phone = $_POST['new_phone'];

// Fetch user data
$user = mysqli_fetch_assoc(pg_query($conn, "SELECT * FROM users WHERE id = $user_id"));

$currentMonth = date('Y-m');
$lastChangeDate = $user['last_phone_change_date'];
$lastChangeMonth = $lastChangeDate ? date('Y-m', strtotime($lastChangeDate)) : '';

// Reset count if new month
if ($lastChangeMonth !== $currentMonth) {
    // It's a new month, reset count logic is handled implicitly by just checking if we should reset 
    // actually better to update the DB to 0 if we detect it, but we can just use local variable for logic check
    // If I update DB here, it's safer.
    pg_query($conn, "UPDATE users SET phone_change_count = 0 WHERE id = $user_id");
    $user['phone_change_count'] = 0;
}

if ($user['phone_change_count'] >= 2) {
    $_SESSION['flash_alert'] = [
        'type' => 'warning',
        'title' => 'จำกัดสิทธิ์',
        'message' => 'คุณเปลี่ยนเบอร์ครบ 2 ครั้งในเดือนนี้แล้ว'
    ];
    header("Location: ../dashboard/profile.php");
    exit;
}

// ... duplicate check ...
if (mysqli_num_rows($check) > 0) {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'เบอร์โทรนี้มีผู้ใช้งานแล้ว'
    ];
    header("Location: ../dashboard/profile.php");
    exit;
}

// Update
$currentDate = date('Y-m-d');
$sql = "UPDATE users SET phone = '$new_phone', phone_change_count = phone_change_count + 1, last_phone_change_date = '$currentDate' WHERE id = $user_id";

if (pg_query($conn, $sql)) {
    $_SESSION['phone'] = $new_phone;
    $_SESSION['flash_alert'] = [
        'type' => 'success',
        'title' => 'สำเร็จ',
        'message' => 'เปลี่ยนเบอร์โทรศัพท์สำเร็จ'
    ];
    header("Location: ../dashboard/profile.php");
} else {
    $_SESSION['flash_alert'] = [
        'type' => 'error',
        'title' => 'ผิดพลาด',
        'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'
    ];
    header("Location: ../dashboard/profile.php");
}
?>
