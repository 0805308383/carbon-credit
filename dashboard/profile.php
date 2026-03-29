<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = pg_fetch_assoc(pg_query($conn, "SELECT * FROM users WHERE id = $user_id"));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>โปรไฟล์ | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modern.css">
</head>
<body>

<div class="db-layout">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="db-main">
        <header style="margin-bottom: 2rem;">
            <h1 style="margin:0;">👤 โปรไฟล์ผู้ใช้งาน</h1>
            <p style="color:var(--text-light);">จัดการข้อมูลส่วนตัวและตั้งค่าความปลอดภัยของบัญชี</p>
        </header>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Info Card -->
            <div class="card">
                <h3 style="margin-bottom:1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-id-card" style="color:var(--primary);"></i> ข้อมูลประจำตัว
                </h3>
                
                <div style="margin-bottom:1.5rem;">
                    <label style="color:var(--text-light); font-size:0.85rem; display: block; margin-bottom: 0.25rem;">เบอร์โทรศัพท์พื้นฐาน</label>
                    <div style="font-size:1.1rem; font-weight:600; color: var(--text);"><?php echo htmlspecialchars($user['phone']); ?></div>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <?php if ($user['user_type'] === 'corporate' || $user['user_type'] === 'corp'): ?>
                        <label style="color:var(--text-light); font-size:0.85rem; display: block; margin-bottom: 0.25rem;">เลขประจำตัวผู้เสียภาษี (Tax ID)</label>
                        <div style="font-size:1.1rem; font-weight:600; color: var(--text); letter-spacing:1px;"><?php echo htmlspecialchars($user['tax_id'] ?: '-'); ?></div>
                    <?php elseif ($user['user_type'] === 'government' || $user['user_type'] === 'gov'): ?>
                        <label style="color:var(--text-light); font-size:0.85rem; display: block; margin-bottom: 0.25rem;">ชื่อหน่วยงาน</label>
                        <div style="font-size:1.1rem; font-weight:600; color: var(--text);"><?php echo htmlspecialchars($user['agency_name'] ?: '-'); ?></div>
                    <?php else: ?>
                        <label style="color:var(--text-light); font-size:0.85rem; display: block; margin-bottom: 0.25rem;">รหัสบัตรประชาชน</label>
                        <div style="font-size:1.1rem; font-weight:600; color: var(--text); letter-spacing:1px;"><?php echo htmlspecialchars($user['national_id'] ?: '-'); ?></div>
                    <?php endif; ?>
                </div>
                
                <div>
                     <label style="color:var(--text-light); font-size:0.85rem; display: block; margin-bottom: 0.5rem;">ประเภทบัญชี</label>
                     <div>
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge badge-red">🛡️ Admin</span>
                        <?php elseif ($user['user_type'] === 'corporate' || $user['user_type'] === 'corp'): ?>
                            <span class="badge" style="background:#fef3c7; color:#92400e; border: 1px solid #fde68a;">🏢 Corporate</span>
                        <?php elseif ($user['user_type'] === 'government' || $user['user_type'] === 'gov'): ?>
                            <span class="badge" style="background:#f3e8ff; color:#6b21a8; border: 1px solid #e9d5ff;">🏛️ Government</span>
                        <?php else: ?>
                            <span class="badge badge-blue">👤 Individual</span>
                        <?php endif; ?>
                     </div>
                </div>
            </div>

            <!-- Security Card -->
            <div class="card">
                <h3 style="margin-bottom:1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-shield-alt" style="color:var(--primary);"></i> ความปลอดภัย
                </h3>
                
                <div style="background:#f9fafb; padding:1.25rem; border-radius:12px; margin-bottom:1.5rem; border: 1px solid #f1f5f9;">
                    <p style="margin:0; font-size:0.9rem; color:var(--text-light);">
                        การเปลี่ยนเบอร์โทรศัพท์จำกัดที่ <strong>2 ครั้งต่อเดือน</strong>
                    </p>
                    <div style="margin-top:0.75rem; font-weight:600; font-size: 0.95rem;">
                        เดือนนี้คุณเปลี่ยนไปแล้ว: <span style="color:var(--primary);"><?php echo $user['phone_change_count']; ?></span> / 2 ครั้ง
                    </div>
                </div>

                <form action="../process/update_phone.php" method="POST">
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">เบอร์โทรศัพท์ใหม่</label>
                        <input type="text" name="new_phone" required placeholder="08XXXXXXXX" style="margin-bottom: 0;">
                    </div>
                    
                    <button type="submit" style="width:100%; font-weight: 600;">อัปเดตเบอร์โทรศัพท์</button>
                </form>
            </div>
        </div>
    </main>
</div>

<script src="../assets/js/autologout.js"></script>
<?php include '../includes/alerts.php'; ?>
</body>
</html>

<script src="../assets/js/autologout.js"></script>
<?php include '../includes/alerts.php'; ?>
</body>
</html>
