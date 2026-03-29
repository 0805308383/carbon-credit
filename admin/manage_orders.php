<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะผู้ดูแลระบบ');
}

$orders = mysqli_query($conn, "
    SELECT o.*, 
           b.phone AS buyer_phone, b.first_name AS buyer_fn, b.last_name AS buyer_ln, b.agency_name AS buyer_agency, b.user_type AS buyer_type,
           l.type AS listing_type, l.province, l.image, l.full_tree_image
    FROM orders o
    JOIN users b ON o.buyer_id = b.id
    JOIN carbon_listings l ON o.listing_id = l.id
    WHERE o.status = 'pending_admin'
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อนุมัติคำสั่งซื้อ | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="assets/admin_custom.css">
</head>
<body class="admin-body">

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="page-header">
            <h1 class="page-title">อนุมัติคำสั่งซื้อคาร์บอนเครดิต</h1>
            <p style="color: var(--admin-text-muted); margin-top: 0.5rem;">ตรวจสอบและอนุมัติรายการซื้อจากผู้ใช้งานในระบบ</p>
        </header>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">ID</th>
                        <th>ข้อมูลสินค้า</th>
                        <th>ผู้ซื้อ / ข้อมูลติดต่อ</th>
                        <th style="text-align: right;">ยอดชำระ (CC)</th>
                        <th>วันเวลาที่สั่งซื้อ</th>
                        <th style="text-align: right;">จัดการรายการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($orders)): 
                        $img = !empty($row['full_tree_image']) ? $row['full_tree_image'] : $row['image'];
                        
                        // Format buyer name
                        $buyerName = trim(($row['buyer_fn'] ?? '') . ' ' . ($row['buyer_ln'] ?? ''));
                        $bType = $row['buyer_type'] ?? '';
                        if (in_array($bType, ['gov', 'government', 'corp', 'corporate'])) {
                            $buyerName = $row['buyer_agency'] ?: 'ไม่ระบุชื่อองค์กร';
                        }
                        if (!$buyerName) $buyerName = 'ผู้ใช้งาน';

                        // Buyer type label
                        $buyerTypeBadge = '<span class="badge" style="background:#d1fae5; color:#065f46; font-size:0.7rem;">👤 บุคคลธรรมดา</span>';
                        if (in_array($bType, ['government', 'gov'])) $buyerTypeBadge = '<span class="badge" style="background:#f3e8ff; color:#6b21a8; font-size:0.7rem;">🏛️ ภาครัฐ</span>';
                        elseif (in_array($bType, ['corporate', 'corp'])) $buyerTypeBadge = '<span class="badge" style="background:#fef3c7; color:#92400e; font-size:0.7rem;">🏢 ภาคเอกชน</span>';
                    ?>
                    <tr>
                        <td style="text-align: center; color: var(--admin-text-muted); font-weight: 600;">#<?= $row['id']; ?></td>
                        <td>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <?php if (!empty($img)): ?>
                                    <img src="../uploads/<?= htmlspecialchars($img) ?>" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <?php else: ?>
                                    <div style="width: 48px; height: 48px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; color: #94a3b8; border: 1px dashed #cbd5e1;">N/A</div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight: 600; color: var(--admin-text);"><?= $row['listing_type'] === 'tree' ? '🌳 ต้นไม้' : '🌾 นาข้าว' ?></div>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                        <?= $row['listing_type'] === 'tree' ? (int)$row['buy_amount'].' ต้น' : number_format($row['buy_amount']*400).' ตร.ว.' ?> 
                                        • <?= htmlspecialchars($row['province'] ?? 'ไม่ระบุ') ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--admin-text);"><?= htmlspecialchars($buyerName); ?></div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem;">
                                <?= $buyerTypeBadge ?>
                                <span style="font-size: 0.75rem; color: var(--admin-text-muted);">📞 <?= htmlspecialchars($row['buyer_phone']); ?></span>
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <strong style="color: var(--admin-primary); font-size: 1rem;"><?= number_format($row['price'], 2); ?></strong>
                        </td>
                        <td style="color: var(--admin-text-muted); font-size: 0.8rem;">
                            <?= date('d M Y', strtotime($row['created_at'])); ?><br>
                            <span style="font-size: 0.75rem; opacity: 0.7;"><?= date('H:i', strtotime($row['created_at'])); ?> น.</span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a href="approve_order.php?id=<?= $row['id']; ?>&action=approve" class="btn" style="background: #10b981; color: white; padding: 0.5rem 0.75rem; font-size: 0.8rem; border: none; border-radius: 6px; font-weight: 600;">
                                    ✅ อนุมัติ
                                </a>
                                <a href="approve_order.php?id=<?= $row['id']; ?>&action=reject" class="btn" style="background: #ef4444; color: white; padding: 0.5rem 0.75rem; font-size: 0.8rem; border: none; border-radius: 6px; font-weight: 600;">
                                    ❌ ปฏิเสธ
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($orders) == 0): ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 4rem;">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
                                <div style="color: var(--admin-text-muted); font-size: 1rem;">ไม่มีคำสั่งซื้อที่รอตรวจสอบในขณะนี้</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
