<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะผู้ดูแลระบบ');
}

$withdraws = pg_query($conn, "
    SELECT w.*, u.phone 
    FROM withdraw_requests w
    JOIN users u ON w.user_id = u.id
    WHERE w.status = 'pending'
    ORDER BY w.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อนุมัติคำขอถอนเงิน | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="assets/admin_custom.css">
</head>
<body class="admin-body">

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="page-header">
            <h1 class="page-title">อนุมัติคำขอถอนเงิน (THB)</h1>
            <p style="color: var(--admin-text-muted); margin-top: 0.5rem;">ตรวจสอบความถูกต้องของบัญชีธนาคารและจำนวนเงินก่อนอนุมัติรายการ</p>
        </header>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">ID</th>
                        <th>ผู้ขอถอน</th>
                        <th style="text-align: right;">จำนวนเงิน (THB)</th>
                        <th>รายละเอียดบัญชีธนาคาร</th>
                        <th>วันเวลาที่ยื่นคำขอ</th>
                        <th style="text-align: right;">จัดการรายการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = pg_fetch_assoc($withdraws)): ?>
                    <tr>
                        <td style="text-align: center; color: var(--admin-text-muted);">#<?= $row['id']; ?></td>
                        <td>
                            <div style="font-weight: 600; color: var(--admin-text);">📞 <?= htmlspecialchars($row['phone']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">User ID: <?= $row['user_id'] ?></div>
                        </td>
                        <td style="text-align: right;">
                            <strong style="color: #ef4444; font-size: 1.125rem;">฿<?= number_format($row['amount'], 2); ?></strong>
                        </td>
                        <td>
                            <div style="background: #f8fafc; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0; display: inline-block; min-width: 200px;">
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.9rem;"><?= htmlspecialchars($row['bank']); ?></div>
                                <div style="font-family: monospace; font-size: 1.1rem; color: var(--admin-primary); margin: 0.25rem 0;"><?= htmlspecialchars($row['bank_account']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--admin-text-muted);"><?= htmlspecialchars($row['account_name']); ?></div>
                            </div>
                        </td>
                        <td style="color: var(--admin-text-muted); font-size: 0.85rem;">
                            <?= date('d M Y', strtotime($row['created_at'])); ?><br>
                            <span style="font-size: 0.75rem; opacity: 0.7;"><?= date('H:i', strtotime($row['created_at'])); ?> น.</span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display:flex; gap:6px; justify-content: flex-end;">
                                <a href="approve_withdraw.php?id=<?= $row['id']; ?>&action=approve" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.8rem; background: #10b981; color: white; border: none; border-radius: 6px; font-weight: 600; text-decoration: none;">✅ อนุมัติ</a>
                                <a href="approve_withdraw.php?id=<?= $row['id']; ?>&action=reject" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.8rem; background: #ef4444; color: white; border: none; border-radius: 6px; font-weight: 600; text-decoration: none;">❌ ปฏิเสธ</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if (pg_num_rows($withdraws) == 0): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 4rem; color: var(--admin-text-muted);">ไม่มีคำขอถอนเงินที่รอตรวจสอบ 🎉</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
