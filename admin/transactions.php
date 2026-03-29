<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะผู้ดูแลระบบ');
}

// ขยาย Query เพื่อดึงข้อมูลรายละเอียดโครงการและคู่ค้า
$orders = mysqli_query($conn, "
    SELECT 
        o.id,
        o.price,
        o.status,
        o.created_at,
        o.buy_amount,
        b.username AS buyer_name,
        b.phone AS buyer_phone,
        s.username AS seller_name,
        s.phone AS seller_phone,
        l.type AS item_type,
        l.province,
        l.carbon_amount AS total_l_carbon,
        l.tree_count,
        l.rice_area
    FROM orders o
    JOIN users b ON o.buyer_id = b.id
    JOIN carbon_listings l ON o.listing_id = l.id
    JOIN users s ON l.seller_id = s.id
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการซื้อขายทั้งหมด | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="assets/admin_custom.css">
    <style>
        .detail-row {
            background-color: #f8fafc;
            display: none;
            transition: all 0.3s;
        }
        .detail-row.active {
            display: table-row;
        }
        .detail-content {
            padding: 1.5rem;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .detail-section h4 {
            margin: 0 0 1rem 0;
            font-size: 0.9rem;
            color: var(--admin-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.025em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        .detail-label { color: var(--admin-text-muted); }
        .detail-value { font-weight: 600; color: var(--admin-text); }
        .btn-detail {
            background: #eff6ff;
            color: #3b82f6;
            border: 1px solid #dbeafe;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-detail:hover {
            background: #dbeafe;
        }
    </style>
</head>
<body class="admin-body">

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="page-header">
            <h1 class="page-title">💰 ประวัติการซื้อขายคาร์บอนเครดิต</h1>
            <p style="color: var(--admin-text-muted); margin-top: 0.5rem;">ตรวจสอบรายละเอียดธุรกรรม ผู้ซื้อ-ผู้ขาย และปริมาณคาร์บอนที่โอนย้ายทั้งหมด</p>
        </header>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">ID</th>
                        <th>ผู้ซื้อ</th>
                        <th>ผู้ขาย</th>
                        <th style="text-align: right;">ราคา (Token)</th>
                        <th style="text-align: center;">สถานะ</th>
                        <th>วันเวลา</th>
                        <th style="text-align: right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($orders)): 
                        // คำนวณปริมาณคาร์บอนที่โอนย้ายจริง
                        $capacity = ($row['item_type'] == 'tree') ? $row['tree_count'] : $row['rice_area'];
                        $order_carbon = ($row['buy_amount'] / ($capacity ?: 1)) * $row['total_l_carbon'];
                    ?>
                    <tr>
                        <td style="text-align: center; color: var(--admin-text-muted); font-weight: 600;">#<?= $row['id']; ?></td>
                        <td>
                            <div style="font-weight: 600; color: var(--admin-text);"><?= htmlspecialchars($row['buyer_name']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">📞 <?= htmlspecialchars($row['buyer_phone']); ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--admin-text);"><?= htmlspecialchars($row['seller_name']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">📞 <?= htmlspecialchars($row['seller_phone']); ?></div>
                        </td>
                        <td style="text-align: right;">
                            <strong style="color: var(--admin-primary); font-size: 1rem;"><?= number_format($row['price'], 2); ?> CC</strong>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($row['status'] === 'approved'): ?><span class="badge badge-green">สำเร็จ</span>
                            <?php elseif ($row['status'] === 'rejected'): ?><span class="badge badge-red">ปฏิเสธ</span>
                            <?php else: ?><span class="badge badge-blue">รออนุมัติ</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--admin-text-muted); font-size: 0.85rem;">
                            <?= date('d M Y, H:i', strtotime($row['created_at'])); ?>
                        </td>
                        <td style="text-align: right;">
                            <button class="btn-detail" onclick="toggleDetails(<?= $row['id'] ?>)">
                                <i class="fas fa-search me-1"></i> รายละเอียด
                            </button>
                        </td>
                    </tr>
                    <!-- Detail View (Initially Hidden) -->
                    <tr id="details-<?= $row['id'] ?>" class="detail-row">
                        <td colspan="7">
                            <div class="detail-content">
                                <div class="detail-section">
                                    <h4><i class="fas fa-info-circle"></i> ข้อมูลโครงการ</h4>
                                    <div class="detail-item">
                                        <span class="detail-label">ประเภทสินค้า:</span>
                                        <span class="detail-value"><?= $row['item_type'] == 'tree' ? '🌳 ทรัพยากรป่าไม้' : '🌾 ภาคการเกษตร' ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">สถานที่:</span>
                                        <span class="detail-value">📍 จังหวัด<?= htmlspecialchars($row['province']) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">ปริมาณที่ซื้อ:</span>
                                        <span class="detail-value"><?= number_format($row['buy_amount']) ?> <?= $row['item_type'] == 'tree' ? 'ต้น' : 'ไร่' ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">คาร์บอนที่ได้รับ:</span>
                                        <span class="detail-value" style="color: var(--admin-success);"><?= number_format($order_carbon, 2) ?> ตัน</span>
                                    </div>
                                </div>
                                <div class="detail-section">
                                    <h4><i class="fas fa-hand-holding-usd"></i> ข้อมูลธุรกรรม</h4>
                                    <div class="detail-item">
                                        <span class="detail-label">ผู้ขาย (Seller):</span>
                                        <span class="detail-value"><?= htmlspecialchars($row['seller_name']) ?> (<?= htmlspecialchars($row['seller_phone']) ?>)</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">ผู้ซื้อ (Buyer):</span>
                                        <span class="detail-value"><?= htmlspecialchars($row['buyer_name']) ?> (<?= htmlspecialchars($row['buyer_phone']) ?>)</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">ราคาต่อหน่วย:</span>
                                        <span class="detail-value"><?= number_format($row['price'] / ($row['buy_amount'] ?: 1), 2) ?> CC / <?= $row['item_type'] == 'tree' ? 'ต้น' : 'ไร่' ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">ราคาสุทธิ:</span>
                                        <span class="detail-value" style="color: var(--admin-primary);"><?= number_format($row['price'], 2) ?> CC</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($orders) == 0): ?>
                        <tr><td colspan="7" style="text-align:center; padding: 4rem; color: var(--admin-text-muted);">ไม่พบรายการธุรกรรมในขณะนี้ 🎉</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
function toggleDetails(id) {
    const detailRow = document.getElementById('details-' + id);
    const isActive = detailRow.classList.contains('active');
    
    // ปิดช่องรายละเอียดอื่นที่เปิดอยู่ (ถ้าต้องการ)
    // document.querySelectorAll('.detail-row').forEach(row => row.classList.remove('active'));
    
    if (isActive) {
        detailRow.classList.remove('active');
    } else {
        detailRow.classList.add('active');
    }
}
</script>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
