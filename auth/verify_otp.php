<?php
session_start();
if (!isset($_SESSION['register_data'])) {
    exit('Session หมดอายุ');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ยืนยัน OTP | สมัครสมาชิก</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f3f4f6;
        }
        .auth-box {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="auth-box">
    <h1 style="color:var(--primary); margin-bottom:1rem;">🔐 ยืนยันเบอร์โทรศัพท์</h1>
    <p style="color:var(--text-light); margin-bottom:2rem;">ระบบได้ส่งรหัส OTP ไปยังเบอร์ของคุณแล้ว</p>

    <div style="background:#fee2e2; color:#991b1b; padding:1rem; border-radius:8px; margin-bottom:2rem; font-weight:bold; letter-spacing:2px;">
        OTP DEMO: <?= $_SESSION['otp_demo'] ?? 'XXXXXX'; ?>
    </div>

    <form action="../process/register_process.php" method="POST">
        <input type="text" name="otp" required placeholder="XXXXXX" style="text-align:center; letter-spacing:5px; font-size:1.5rem; width:200px;">
        <br>
        <button type="submit" style="width:100%;">ยืนยันการสมัคร</button>
    </form>
</div>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
