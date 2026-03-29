<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะผู้ดูแลระบบ');
}

$requests = mysqli_query($conn, "
    SELECT r.*, u.phone 
    FROM seller_requests r
    JOIN users u ON r.user_id = u.id
    WHERE r.status = 'pending'
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คำขอสมัครเป็นผู้ขาย | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="assets/admin_custom.css">
</head>
<body class="admin-body">

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="page-header">
            <h1 class="page-title">คำขอสมัครเป็นผู้ขาย</h1>
            <p style="color: var(--admin-text-muted); margin-top: 0.5rem;">ตรวจสอบและอนุมัติสิทธิ์การลงขายสินค้าสำหรับผู้ใช้งาน</p>
        </header>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">ID</th>
                        <th>ข้อมูลผู้ใช้งาน</th>
                        <th>วันที่ส่งคำขอ</th>
                        <th>สถานะการสมัคร</th>
                        <th style="text-align: right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($requests)): ?>
                    <tr>
                        <td style="text-align: center; color: var(--admin-text-muted);">#<?= $row['id']; ?></td>
                        <td>
                            <div style="font-weight: 600; color: var(--admin-text);">📞 <?= htmlspecialchars($row['phone']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">User ID: <?= $row['user_id'] ?></div>
                        </td>
                        <td style="color: var(--admin-text-muted); font-size: 0.85rem;"><?= date('d M Y, H:i', strtotime($row['created_at'])); ?> น.</td>
                        <td><span class="badge" style="background:#fef3c7; color:#d97706; border: 1px solid #fde68a;">⏳ รออนุมัติ</span></td>
                        <td style="text-align: right;">
                            <div style="display:flex; gap:6px; justify-content: flex-end;">
                                <a href="approve_seller.php?id=<?= $row['id']; ?>&action=approve" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.8rem; background: #10b981; color: white; border: none; border-radius: 6px; font-weight: 600; text-decoration: none;">อนุมัติ</a>
                                <a href="approve_seller.php?id=<?= $row['id']; ?>&action=reject" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.8rem; background: #ef4444; color: white; border: none; border-radius: 6px; font-weight: 600; text-decoration: none;">ปฏิเสธ</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($requests) == 0): ?>
                        <tr><td colspan="5" style="text-align:center; padding:4rem; color:var(--admin-text-muted); font-size:1rem;">ไม่มีคำขอใหม่ในระบบ 🎉</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
