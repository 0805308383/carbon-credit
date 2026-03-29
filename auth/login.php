<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
    </style>
</head>
<body>

<div class="auth-box">
    <div style="text-align:center; margin-bottom:2rem;">
        <h1 style="color:var(--primary);">เข้าสู่ระบบ</h1>
        <p style="color:var(--text-light);">Carbon Credit Simulator</p>
    </div>

    <form action="../process/login_process.php" method="POST">
        <label>ชื่อผู้ใช้งาน (Username)</label>
        <input type="text" name="username" required placeholder="กรอกชื่อผู้ใช้งาน">

        <label>รหัสผ่าน</label>
        <input type="password" name="password" required placeholder="••••••••">
        
        <label>ยืนยันตัวตน</label>
        <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:1rem;">
            <div style="display:flex; align-items:center; gap:10px;">
                <img src="../assets/captcha.php" alt="CAPTCHA" id="captchaImg" style="border-radius:4px; border:1px solid #000; height:80px;">
                <button type="button" onclick="document.getElementById('captchaImg').src='../assets/captcha.php?'+Math.random();" style="padding:0.5rem; background:none; border:1px solid var(--border); color:var(--text); width:auto;">↻</button>
            </div>
            <input type="text" name="captcha" required placeholder="กรอกตัวเลขตามภาพ" style="margin-bottom:0;">
        </div>

        <button type="submit" style="width:100%;">เข้าสู่ระบบ</button>
    </form>

    <div style="text-align:center; margin-top:1.5rem;">
        <span style="color:var(--text-light);">ยังไม่มีบัญชี?</span>
        <a href="register.php" style="font-weight:600;">สมัครสมาชิก</a>
    </div>
</div>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
