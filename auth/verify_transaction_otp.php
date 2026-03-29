<?php
session_start();
if (!isset($_SESSION['otp_pending_transaction']) && !isset($_SESSION['register_data'])) {
    if (!isset($_SESSION['register_data'])) {
         // handle normal otp checks
    }
}
// Unified view for both Register OTP and Transaction OTP might be tricky if paths differ, 
// using separate file for verify_otp.php (register) vs verify_transaction_otp.php (transaction) 
// This file updates verify_transaction_otp.php
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ยืนยัน OTP | Carbon Market</title>
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
    <h1 style="color:var(--primary); margin-bottom:1rem;">🔐 ยืนยัน OTP</h1>
    <p style="color:var(--text-light); margin-bottom:2rem;">กรุณากรอกรหัส OTP เพื่อทำรายการต่อ</p>

    <div style="background:#fee2e2; color:#991b1b; padding:1rem; border-radius:8px; margin-bottom:2rem; font-weight:bold; letter-spacing:2px;">
        OTP DEMO: <?= $_SESSION['otp_demo'] ?? 'XXXXXX'; ?>
    </div>

    <form action="../process/finish_transaction.php" method="POST">
        <input type="text" name="otp" required placeholder="XXXXXX" style="text-align:center; letter-spacing:5px; font-size:1.5rem; width:200px;">
        <br>
        <button type="submit" style="width:100%;">ยืนยันรายการ</button>
    </form>
    
    <div style="margin-top:1rem;">
        <a href="../dashboard/index.php" style="color:var(--text-light); font-size:0.9rem;">ยกเลิก</a>
    </div>
</div>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
