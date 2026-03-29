<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะผู้ดูแลระบบ');
}

$topups = mysqli_query($conn, "
    SELECT t.*, u.phone 
    FROM token_topups t
    JOIN users u ON t.user_id = u.id
    WHERE t.status = 'pending_admin'
    ORDER BY t.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อนุมัติการเติม Token | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="assets/admin_custom.css">
</head>
<body class="admin-body">

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="page-header">
            <h1 class="page-title">อนุมัติการเติม Token</h1>
            <p style="color: var(--admin-text-muted); margin-top: 0.5rem;">ตรวจสอบสลิปการโอนเงินและอนุมัติเหรียญคาร์บอน (CC) ให้กับผู้ใช้งาน</p>
        </header>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">ID</th>
                        <th>ผู้ใช้งาน</th>
                        <th style="text-align: right;">ยอดเงิน (บาท)</th>
                        <th style="text-align: right;">Token ที่ได้รับ</th>
                        <th>หลักฐานการโอน</th>
                        <th>วันที่ทำรายการ</th>
                        <th style="text-align: right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($topups)): ?>
                    <tr>
                        <td style="text-align: center; color: var(--admin-text-muted);">#<?= $row['id']; ?></td>
                        <td>
                            <div style="font-weight: 600; color: var(--admin-text);">📞 <?= htmlspecialchars($row['phone']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">User ID: <?= $row['user_id'] ?></div>
                        </td>
                        <td style="text-align: right; font-weight: 600;">฿<?= number_format($row['price'], 2); ?></td>
                        <td style="text-align: right; font-weight: 700; color: var(--admin-primary);"><?= number_format($row['token_amount'], 2); ?> CC</td>
                        <td>
                            <?php if (!empty($row['slip_image'])): ?>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <a href="../<?= htmlspecialchars($row['slip_image']); ?>" target="_blank" style="text-decoration: none;">
                                        <img src="../<?= htmlspecialchars($row['slip_image']); ?>" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                    </a>
                                    <span style="font-size: 0.7rem; color: var(--admin-text-muted);">คลิกดูสลิป</span>
                                </div>
                            <?php else: ?>
                                <span style="color: var(--admin-text-muted); font-size: 0.8rem;">(ไม่มีหลักฐาน)</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--admin-text-muted); font-size: 0.85rem;"><?= date('d M Y, H:i', strtotime($row['created_at'])); ?> น.</td>
                        <td style="text-align: right;">
                            <div style="display:flex; gap:6px; justify-content: flex-end;">
                                <a href="approve_token_topup.php?id=<?= $row['id']; ?>&action=approve" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.8rem; background: #10b981; color: white; border: none; border-radius: 6px; font-weight: 600; text-decoration: none;">อนุมัติ</a>
                                <a href="approve_token_topup.php?id=<?= $row['id']; ?>&action=reject" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.8rem; background: #ef4444; color: white; border: none; border-radius: 6px; font-weight: 600; text-decoration: none;">ปฏิเสธ</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($topups) == 0): ?>
                        <tr><td colspan="7" style="text-align:center; padding: 4rem; color: var(--admin-text-muted);">ไม่มีคำขอเติม Token ที่รอตรวจสอบ 🎉</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
