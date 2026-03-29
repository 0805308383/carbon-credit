<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    exit('เฉพาะผู้ใช้งานเท่านั้น');
}

$user_id = $_SESSION['user_id'];
$transactions = [];

// 1. ดึงข้อมูลการซื้อ (Buy Orders)
$qBuy = mysqli_query($conn, "
    SELECT 
        o.id, 
        o.buy_amount,
        l.type AS item_type,
        l.province,
        l.carbon_amount as total_l_carbon,
        l.tree_count,
        l.rice_area,
        l.price_token as unit_price,
        o.price, 
        o.status, 
        o.created_at,
        u.username AS other_party,
        u.phone AS other_phone
    FROM orders o
    JOIN carbon_listings l ON o.listing_id = l.id
    JOIN users u ON l.seller_id = u.id
    WHERE o.buyer_id = $user_id
");
while ($row = mysqli_fetch_assoc($qBuy)) {
    $capacity = ($row['item_type'] == 'tree') ? $row['tree_count'] : $row['rice_area'];
    $order_carbon = ($row['buy_amount'] / ($capacity ?: 1)) * $row['total_l_carbon'];

    $transactions[] = [
        'id' => $row['id'],
        'is_buy' => true,
        'item_type' => $row['item_type'],
        'province' => $row['province'],
        'carbon_amount' => $order_carbon,
        'buy_amount' => $row['buy_amount'],
        'price' => $row['price'],
        'unit_price' => $row['price'] / ($row['buy_amount'] ?: 1),
        'status' => $row['status'],
        'created_at' => $row['created_at'],
        'other_party' => $row['other_party'],
        'other_phone' => $row['other_phone']
    ];
}

// 2. ดึงข้อมูลการลงขาย (Sell Listings)
$qSell = mysqli_query($conn, "
    SELECT 
        l.id,
        l.type AS item_type,
        l.province,
        l.carbon_amount,
        l.price_token AS price,
        l.status,
        l.created_at,
        l.tree_count,
        l.rice_area
    FROM carbon_listings l
    WHERE l.seller_id = $user_id
");
while ($row = mysqli_fetch_assoc($qSell)) {
    $buyer = '-';
    $phone = '-';
    if ($row['status'] === 'sold') {
        $qBuyer = mysqli_query($conn, "SELECT u.username, u.phone FROM orders o JOIN users u ON o.buyer_id = u.id WHERE o.listing_id = {$row['id']} AND o.status = 'approved' LIMIT 1");
        if ($b = mysqli_fetch_assoc($qBuyer)) {
            $buyer = $b['username'];
            $phone = $b['phone'];
        }
    }
    
    $transactions[] = [
        'id' => $row['id'],
        'is_buy' => false,
        'item_type' => $row['item_type'],
        'province' => $row['province'],
        'carbon_amount' => $row['carbon_amount'],
        'buy_amount' => ($row['item_type'] == 'tree' ? $row['tree_count'] : $row['rice_area']),
        'price' => $row['price'],
        'unit_price' => $row['price'] / ($row['item_type'] == 'tree' ? ($row['tree_count'] ?: 1) : ($row['rice_area'] ?: 1)),
        'status' => $row['status'],
        'created_at' => $row['created_at'],
        'other_party' => $buyer,
        'other_phone' => $phone
    ];
}

// เรียงลำดับตามวันที่ใหม่ล่าสุด
usort($transactions, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการทำรายการ | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <style>
        .detail-row {
            background-color: #f8fafc;
            display: none;
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
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.025em;
            display: flex;
            align-items: center; gap: 0.5rem;
        }
        .detail-item {
            display: flex; justify-content: space-between;
            margin-bottom: 0.5rem; font-size: 0.95rem;
        }
        .detail-label { color: var(--text-light); }
        .detail-value { font-weight: 600; color: var(--text); }
        .btn-detail {
            background: #eff6ff; color: #3b82f6; border: 1px solid #dbeafe;
            padding: 0.4rem 0.75rem; border-radius: 6px; font-size: 0.8rem;
            font-weight: 600; cursor: pointer;
        }
    </style>
</head>
<body>

<div class="db-layout">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="db-main">
        <header style="margin-bottom: 2rem;">
            <h1 style="margin:0;">🧾 ประวัติการซื้อขาย</h1>
            <p style="color:var(--text-light);">ติดตามรายละเอียดธุรกรรมและสถานะรายการทั้งหมดของคุณ</p>
        </header>

        <div class="card" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; background: #f9fafb; border-bottom: 2px solid #f3f4f6;">
                        <th style="padding: 1rem 1.5rem;">วันที่</th>
                        <th style="padding: 1rem;">ประเภท</th>
                        <th style="padding: 1rem;">รายการ</th>
                        <th style="padding: 1rem; text-align: right;">ราคา (CC)</th>
                        <th style="padding: 1rem; text-align: center;">สถานะ</th>
                        <th style="padding: 1rem; text-align: right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $txn): ?>
                    <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.2s;">
                        <td style="padding: 1rem 1.5rem; color:var(--text-light); font-size: 0.9rem;">
                            <?= date('d M Y, H:i', strtotime($txn['created_at'])) ?>
                        </td>
                        <td style="padding: 1rem;">
                            <?php if ($txn['is_buy']): ?>
                                <span class="badge badge-blue">ซื้อ 🛒</span>
                            <?php else: ?>
                                <span class="badge" style="background:#8b5cf6; color:white;">ลงขาย 🏷️</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;">
                            <div style="font-weight: 600;">
                                <?= $txn['item_type'] == 'tree' ? '🌳 ทรัพยากรป่าไม้' : '🌾 ภาคการเกษตร' ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-light);">
                                <?= number_format($txn['carbon_amount'], 2) ?> ตัน • 📍 <?= htmlspecialchars($txn['province']) ?>
                            </div>
                        </td>
                        <td style="padding: 1rem; text-align: right; font-weight: 700; color: var(--primary);">
                            <?= number_format($txn['price'], 2) ?>
                        </td>
                        <td style="padding: 1rem; text-align: center;">
                            <?php if ($txn['is_buy']): ?>
                                <?php if ($txn['status'] == 'approved'): ?>
                                    <span class="badge badge-green">สำเร็จ</span>
                                <?php elseif ($txn['status'] == 'rejected'): ?>
                                    <span class="badge badge-red">ถูกปฏิเสธ</span>
                                <?php else: ?>
                                    <span class="badge badge-blue">รอตรวจสอบ</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($txn['status'] == 'approved'): ?>
                                    <span class="badge badge-green">รอขาย</span>
                                <?php elseif ($txn['status'] == 'sold'): ?>
                                     <span class="badge badge-green" style="background:#064e3b; color:white;">ขายแล้ว</span>
                                <?php elseif ($txn['status'] == 'pending'): ?>
                                    <span class="badge badge-blue">รออนุมัติ</span>
                                <?php else: ?>
                                    <span class="badge badge-red">ถูกปฏิเสธ</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <button class="btn-detail" onclick="toggleDetails('<?= $txn['is_buy'] ? 'buy' : 'sell' ?>-<?= $txn['id'] ?>')">
                                <i class="fas fa-search me-1"></i> รายละเอียด
                            </button>
                        </td>
                    </tr>
                    <!-- Detail Reveal -->
                    <tr id="details-<?= $txn['is_buy'] ? 'buy' : 'sell' ?>-<?= $txn['id'] ?>" class="detail-row">
                        <td colspan="6">
                            <div class="detail-content">
                                <div class="detail-section">
                                    <h4><i class="fas fa-leaf"></i> ข้อมูลโครงการ</h4>
                                    <div class="detail-item">
                                        <span class="detail-label">โครงการ:</span>
                                        <span class="detail-value"><?= $txn['item_type'] == 'tree' ? 'ทรัพยากรป่าไม้' : 'ภาคการเกษตร' ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">จังหวัด:</span>
                                        <span class="detail-value"><?= htmlspecialchars($txn['province']) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">จำนวน:</span>
                                        <span class="detail-value"><?= number_format($txn['buy_amount']) ?> <?= $txn['item_type'] == 'tree' ? 'ต้น' : 'ไร่' ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">คาร์บอนที่ได้รับ:</span>
                                        <span class="detail-value" style="color: var(--primary);"><?= number_format($txn['carbon_amount'], 2) ?> ตัน</span>
                                    </div>
                                </div>
                                <div class="detail-section">
                                    <h4><i class="fas fa-handshake"></i> ข้อมูลคู่ค้าและราคา</h4>
                                    <div class="detail-item">
                                        <span class="detail-label"><?= $txn['is_buy'] ? 'ผู้ขาย' : 'ผู้ซื้อ' ?>:</span>
                                        <span class="detail-value"><?= htmlspecialchars($txn['other_party']) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">เบอร์โทรศัพท์:</span>
                                        <span class="detail-value"><?= htmlspecialchars($txn['other_phone']) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">ราคาต่อหน่วย:</span>
                                        <span class="detail-value"><?= number_format($txn['unit_price'], 2) ?> CC / <?= $txn['item_type'] == 'tree' ? 'ต้น' : 'ไร่' ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">ยอดรวมสุทธิ:</span>
                                        <span class="detail-value" style="color: var(--primary); font-size: 1.1rem;"><?= number_format($txn['price'], 2) ?> CC</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($transactions)): ?>
                        <tr><td colspan="6" style="text-align:center; padding:4rem; color:var(--text-light);">ไม่พบประวัติการทำรายการ 🎉</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
function toggleDetails(id) {
    const detailRow = document.getElementById('details-' + id);
    detailRow.classList.toggle('active');
}
</script>

<script src="../assets/js/autologout.js"></script>
<?php include '../includes/alerts.php'; ?>
</body>
</html>
