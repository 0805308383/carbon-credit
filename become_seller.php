<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    exit('ไม่มีสิทธิ์');
}

$user_id = $_SESSION['user_id'];

// Check status
$check = pg_query($conn, "SELECT * FROM seller_requests WHERE user_id = $user_id ORDER BY id DESC LIMIT 1");
$existing = pg_fetch_assoc($check);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!$existing || $existing['status'] === 'rejected')) {
    pg_query($conn, "INSERT INTO seller_requests (user_id) VALUES ($user_id)");
    $_SESSION['flash_alert'] = [
        'type' => 'success',
        'title' => 'ส่งคำขอสำเร็จ',
        'message' => 'คำขอของคุณถูกส่งให้ Admin ตรวจสอบแล้ว'
    ];
    header("Location: dashboard/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครเป็นผู้ขาย | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>

<div class="navbar">
    <div style="font-weight:700;">🌟 สมัครเป็นผู้ขาย</div>
    <a href="dashboard/index.php" style="color:var(--text);">← กลับ Dashboard</a>
</div>

<div class="container" style="max-width:600px; text-align:center; margin-top:3rem;">
    <div class="card" style="padding:3rem;">
        <div style="font-size:3rem; margin-bottom:1rem;">🌳</div>
        <h2 style="margin-bottom:1rem;">เริ่มต้นขายคาร์บอนเครดิต</h2>
        <p style="color:var(--text-light); margin-bottom:2rem;">
            เปลี่ยนพื้นที่สีเขียวของคุณให้เป็นรายได้ ลงทะเบียนเพื่อเริ่มขายคาร์บอนเครดิตได้ทันที
        </p>
        
        <?php if ($existing && $existing['status'] === 'pending'): ?>
            <div style="background:#dbeafe; color:#1e40af; padding:1rem; border-radius:8px;">
                ⏳ คำขอของคุณกำลังอยู่ระหว่างการตรวจสอบ
            </div>
        <?php else: ?>
            <form method="POST">
                <button type="submit" style="font-size:1.1rem; padding:1rem 2rem;">ส่งคำขอเป็นผู้ขาย</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/alerts.php'; ?>
</body>
</html>
