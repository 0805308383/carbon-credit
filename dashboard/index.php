<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
require '../config/db.php';

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

// Seller Status
$sellerStatus = null;
$qStatus = pg_query($conn, "SELECT status FROM seller_requests WHERE user_id = $user_id ORDER BY id DESC LIMIT 1");
if ($qStatus && $row = mysqli_fetch_assoc($qStatus)) {
    $sellerStatus = $row['status'];
}

// Wallet
$wallet = null;
$qWallet = pg_query($conn, "SELECT balance, token, carbon_balance FROM wallets WHERE user_id = $user_id");
if ($qWallet && mysqli_num_rows($qWallet) > 0) {
    $wallet = mysqli_fetch_assoc($qWallet);
}

// User Type Mapping
$qUser = pg_query($conn, "SELECT user_type FROM users WHERE id = $user_id");
$uRow = mysqli_fetch_assoc($qUser);
$utype = $uRow['user_type'] ?? 'individual';

$badgeLabel = 'บุคคลธรรมดา';
$badgeColor = '#3b82f6'; // Blue

if ($role === 'admin') {
    $badgeLabel = 'ผู้ดูแลระบบ';
    $badgeColor = '#ef4444'; // Red
} elseif ($utype === 'government' || $utype === 'gov') {
    $badgeLabel = 'ภาครัฐ';
    $badgeColor = '#8b5cf6'; // Purple
} elseif ($utype === 'corporate' || $utype === 'corp') {
    $badgeLabel = 'ภาคเอกชน';
    $badgeColor = '#f59e0b'; // Amber
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Carbon Credit Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, white, #f9fafb);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
        }
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="db-layout">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>


    <!-- Main Content -->
    <div class="db-main">

        <header style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
            <div>
                <h1 style="margin:0;">สวัสดี, <?= htmlspecialchars($_SESSION['username']); ?></h1>
                <p style="color:var(--text-light);">ยินดีต้อนรับสู่ระบบซื้อขายคาร์บอนเครดิต</p>
            </div>
            <div style="display:flex; align-items:center; gap:1rem;">
                <span class="badge" style="background:<?= $badgeColor ?>; color:white;"><?= $badgeLabel ?></span>
            </div>
        </header>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">ยอดเงินคงเหลือ (THB)</div>
                <div class="stat-value">฿<?= $wallet ? number_format($wallet['balance'], 2) : '0.00' ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Carbon Tokens (CC)</div>
                <div class="stat-value"><?= $wallet ? number_format($wallet['token'], 2) : '0.00' ?> CC</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #f0fdf4, #ecfccb); border-color: #d9f99d;">
                <div class="stat-label" style="color:#166534; font-weight:600;">คาร์บอนเครดิตสะสม (Ton)</div>
                <div class="stat-value" style="color:var(--primary);"><?= $wallet ? number_format($wallet['carbon_balance'], 2) : '0.00' ?> ตัน</div>
            </div>
            
            <div class="stat-card" style="display:flex; flex-direction:column; justify-content:center;">
                <div class="stat-label">เมนูด่วน</div>
                <div style="margin-top:1rem; display:flex; gap:0.5rem;">
                    <a href="marketplace.php" style="background:var(--primary); color:white; padding:0.5rem 1rem; border-radius:6px; text-decoration:none; font-size:0.9rem; text-align:center; flex:1; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">🛒 ตลาดซื้อขาย</a>
                    <a href="create_listing.php" style="background:#3b82f6; color:white; padding:0.5rem 1rem; border-radius:6px; text-decoration:none; font-size:0.9rem; text-align:center; flex:1; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">➕ ลงขายสินค้า</a>
                </div>
            </div>
        </div>

        <?php if ($role !== 'admin'): ?>
            <?php
            // ผู้ซื้อ ดูรายการซื้อ
            $carbon_used = $wallet ? $wallet['carbon_balance'] : 0;
            $buyer_orders = pg_query($conn, "
                SELECT o.buy_amount, o.price, o.created_at, o.status, 
                       l.type, l.province, l.image, l.full_tree_image, 
                       l.carbon_amount as total_l_carbon, l.tree_count, l.rice_area 
                FROM orders o 
                JOIN carbon_listings l ON o.listing_id = l.id 
                WHERE o.buyer_id = $user_id 
                ORDER BY o.created_at DESC LIMIT 6
            ");
            ?>
            <div class="card" style="margin-bottom:2rem; padding:2rem; border-top: 5px solid var(--primary); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <div style="background:#ecfdf5; color:#10b981; width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                            🛍️
                        </div>
                        <div>
                            <h3 style="margin:0; font-size:1.25rem; font-weight:700; color:#111827;">รายการที่ซื้อแล้ว</h3>
                            <div style="color:#6b7280; font-size:0.875rem;">ประวัติการซื้อคาร์บอนเครดิตล่าสุดของคุณ</div>
                        </div>
                    </div>
                    <div style="background:linear-gradient(135deg, #f0fdf4, #ecfccb); border: 1px solid #d9f99d; color:#166534; padding:0.75rem 1.25rem; border-radius:16px; display:flex; align-items:center; gap:0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <span style="font-weight:500; font-size:0.9rem;">ยอดรวมพลังงานสีเขียวที่ได้รับ</span> 
                        <span style="font-size:1.25rem; font-weight:800; color:var(--primary);"><?= number_format($carbon_used, 2) ?> ตัน</span>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.25rem;">
                    <?php if (mysqli_num_rows($buyer_orders) > 0): ?>
                        <?php while($order = mysqli_fetch_assoc($buyer_orders)): 
                            $img = !empty($order['full_tree_image']) ? $order['full_tree_image'] : $order['image'];
                        ?>
                        <div style="border:1px solid #f3f4f6; border-radius:16px; overflow:hidden; display:flex; flex-direction:column; background:white; position:relative; transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.02)';">
                            <div style="height:140px; width:100%; position:relative;">
                                <?php if ($img): ?>
                                    <img src="../uploads/<?= htmlspecialchars($img) ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:100%; height:100%; background:linear-gradient(135deg, #f3f4f6, #e5e7eb); display:flex; align-items:center; justify-content:center; color:#9ca3af;">
                                        <span style="font-size:2rem;">📸</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="position:absolute; top:0.75rem; right:0.75rem; display:flex; gap:0.5rem; flex-direction:column; align-items:flex-end;">
                                    <?php if($order['status'] !== 'approved'): ?>
                                        <span style="font-size:0.75rem; background:rgba(245, 158, 11, 0.9); color:white; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; box-shadow:0 2px 4px rgba(0,0,0,0.1); backdrop-filter:blur(4px);">รอตรวจสอบ</span>
                                    <?php else: ?>
                                        <span style="font-size:0.75rem; background:rgba(22, 163, 74, 0.9); color:white; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; box-shadow:0 2px 4px rgba(0,0,0,0.1); backdrop-filter:blur(4px);">สำเร็จ</span>
                                    <?php endif; ?>
                                    
                                    <span style="font-size:0.75rem; background:rgba(255, 255, 255, 0.9); color:#374151; padding:0.25rem 0.6rem; border-radius:8px; font-weight:600; backdrop-filter:blur(4px); display:flex; align-items:center; gap:0.3rem;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                        <?= htmlspecialchars($order['province'] ?? 'ไม่ระบุ') ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div style="padding:1.25rem; flex-grow:1; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                                        <div style="font-size:1rem; font-weight:700; color:#1f2937; display:flex; align-items:center; gap:0.5rem;">
                                            <?= $order['type'] == 'tree' ? '<span style="background:#dcfce7; padding:0.25rem 0.4rem; border-radius:6px; font-size:1.1rem; line-height:1;">🌳</span> ทรัพยากรป่าไม้' : '<span style="background:#fef9c3; padding:0.25rem 0.4rem; border-radius:6px; font-size:1.1rem; line-height:1;">🌾</span> ภาคการเกษตร' ?>
                                        </div>
                                    </div>
                                    <?php 
                                        // คำนวณปริมาณคาร์บอนที่ได้รับจริงจากออเดอร์นี้
                                        $capacity = ($order['type'] == 'tree') ? $order['tree_count'] : $order['rice_area'];
                                        $order_carbon = ($order['buy_amount'] / ($capacity ?: 1)) * $order['total_l_carbon'];
                                    ?>
                                    <div style="font-size:0.85rem; color:#6b7280; font-weight:500; margin-bottom:1rem;">
                                        จำนวนที่ได้รับ: <span style="font-weight:700; color:#374151;"><?= number_format($order_carbon, 2) ?> ตัน</span>
                                        <br><small style="color:#9ca3af;">(จากสินค้า: <?= number_format($order['buy_amount'], 0) ?> <?= $order['type'] == 'tree' ? 'ต้น' : 'ไร่' ?>)</small>
                                    </div>
                                </div>
                                
                                <div style="display:flex; justify-content:space-between; align-items:flex-end; border-top:1px dashed #e5e7eb; padding-top:1rem;">
                                    <div>
                                        <div style="font-size:0.7rem; color:#9ca3af; margin-bottom:0.1rem; text-transform:uppercase; font-weight:600; letter-spacing:0.05em;">มูลค่าที่ใช้ไป</div>
                                        <div style="font-size:1.25rem; font-weight:800; color:var(--primary); line-height:1;">
                                            <?= number_format($order['price'], 2) ?> CC
                                        </div>
                                    </div>
                                    <div style="font-size:0.75rem; color:#9ca3af; font-weight:500;">
                                        <?= date('d M Y', strtotime($order['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; padding:4rem 2rem; text-align:center; background:#f9fafb; border-radius:16px; border:2px dashed #e5e7eb; display:flex; flex-direction:column; align-items:center;">
                            <div style="background:white; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom:1.5rem;">
                                <span style="font-size:2.5rem; opacity:0.8;">🛒</span>
                            </div>
                            <h4 style="font-size:1.25rem; font-weight:700; color:#374151; margin:0 0 0.5rem 0;">ยังไม่มีประวัติการซื้อคาร์บอนเครดิต</h4>
                            <p style="color:#6b7280; font-size:0.95rem; margin:0 0 1.5rem 0; max-width:400px; line-height:1.5;">ร่วมเป็นส่วนหนึ่งในการลดคาร์บอนและสนับสนุนโครงการเพื่อสิ่งแวดล้อมได้วันนี้</p>
                            <a href="marketplace.php" class="btn btn-primary" style="padding:0.75rem 1.5rem; border-radius:99px; font-weight:600; box-shadow:0 4px 14px 0 rgba(16, 185, 129, 0.39);">ไปที่ตลาดซื้อขาย</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            // ผู้ขาย ดูรายการขาย (listings)
            $seller_listings = pg_query($conn, "
                SELECT l.type, l.province, l.image, l.full_tree_image, l.carbon_amount, l.price_token, l.status, l.created_at 
                FROM carbon_listings l 
                WHERE l.seller_id = $user_id 
                ORDER BY l.created_at DESC LIMIT 6
            ");
            ?>
            <div class="card" style="margin-bottom:2rem; padding:2rem; border-top: 5px solid #3b82f6; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);">
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.5rem;">
                    <div style="background:#eff6ff; color:#3b82f6; width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                        📊
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.25rem; font-weight:700; color:#111827;">รายการที่ลงขายล่าสุด</h3>
                        <div style="color:#6b7280; font-size:0.875rem;">ติดตามสถานะโปรเจกต์คาร์บอนเครดิตของคุณ</div>
                    </div>
                </div>
                
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.25rem;">
                    <?php if (mysqli_num_rows($seller_listings) > 0): ?>
                        <?php while($listing = mysqli_fetch_assoc($seller_listings)): 
                            $img = !empty($listing['full_tree_image']) ? $listing['full_tree_image'] : $listing['image'];
                        ?>
                        <div style="border:1px solid #f3f4f6; border-radius:16px; overflow:hidden; display:flex; flex-direction:column; background:white; position:relative; transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.02)';">
                            <div style="height:140px; width:100%; position:relative;">
                                <?php if ($img): ?>
                                    <img src="../uploads/<?= htmlspecialchars($img) ?>" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:100%; height:100%; background:linear-gradient(135deg, #f3f4f6, #e5e7eb); display:flex; align-items:center; justify-content:center; color:#9ca3af;">
                                        <span style="font-size:2rem;">📸</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="position:absolute; top:0.75rem; right:0.75rem; display:flex; gap:0.5rem; flex-direction:column; align-items:flex-end;">
                                    <?php if($listing['status'] == 'approved'): ?>
                                        <span style="font-size:0.75rem; background:rgba(22, 163, 74, 0.9); color:white; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; box-shadow:0 2px 4px rgba(0,0,0,0.1); backdrop-filter:blur(4px);">พร้อมขาย</span>
                                    <?php elseif($listing['status'] == 'sold'): ?>
                                        <span style="font-size:0.75rem; background:rgba(30, 64, 175, 0.9); color:white; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; box-shadow:0 2px 4px rgba(0,0,0,0.1); backdrop-filter:blur(4px);">ขายหมดแล้ว</span>
                                    <?php else: ?>
                                        <span style="font-size:0.75rem; background:rgba(217, 119, 6, 0.9); color:white; padding:0.25rem 0.75rem; border-radius:9999px; font-weight:600; box-shadow:0 2px 4px rgba(0,0,0,0.1); backdrop-filter:blur(4px);">รอตรวจสอบ</span>
                                    <?php endif; ?>
                                    
                                    <span style="font-size:0.75rem; background:rgba(255, 255, 255, 0.9); color:#374151; padding:0.25rem 0.6rem; border-radius:8px; font-weight:600; backdrop-filter:blur(4px); display:flex; align-items:center; gap:0.3rem;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                        <?= htmlspecialchars($listing['province'] ?? 'ไม่ระบุ') ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div style="padding:1.25rem; flex-grow:1; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                                        <div style="font-size:1rem; font-weight:700; color:#1f2937; display:flex; align-items:center; gap:0.5rem;">
                                            <?= $listing['type'] == 'tree' ? '<span style="background:#dcfce7; padding:0.25rem 0.4rem; border-radius:6px; font-size:1.1rem; line-height:1;">🌳</span> ต้นไม้' : '<span style="background:#fef9c3; padding:0.25rem 0.4rem; border-radius:6px; font-size:1.1rem; line-height:1;">🌾</span> นาข้าว' ?>
                                        </div>
                                    </div>
                                    <div style="font-size:0.85rem; color:#6b7280; font-weight:500; margin-bottom:1rem;">
                                        ปริมาณคาร์บอน: <span style="font-weight:700; color:#374151;"><?= number_format($listing['carbon_amount'], 2) ?> ตัน</span>
                                    </div>
                                </div>
                                
                                <div style="display:flex; justify-content:space-between; align-items:flex-end; border-top:1px dashed #e5e7eb; padding-top:1rem;">
                                    <div>
                                        <div style="font-size:0.7rem; color:#9ca3af; margin-bottom:0.1rem; text-transform:uppercase; font-weight:600; letter-spacing:0.05em;">ราคาประเมิน</div>
                                        <div style="font-size:1.25rem; font-weight:800; color:#3b82f6; line-height:1;">
                                            <?= number_format($listing['price_token'], 2) ?> CC
                                        </div>
                                    </div>
                                    <div style="font-size:0.75rem; color:#9ca3af; font-weight:500;">
                                        <?= date('d M Y', strtotime($listing['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; padding:4rem 2rem; text-align:center; background:#f9fafb; border-radius:16px; border:2px dashed #e5e7eb; display:flex; flex-direction:column; align-items:center;">
                            <div style="background:white; width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 10px 15px -3px rgba(0,0,0,0.05); margin-bottom:1.5rem;">
                                <span style="font-size:2.5rem; opacity:0.8;">📈</span>
                            </div>
                            <h4 style="font-size:1.25rem; font-weight:700; color:#374151; margin:0 0 0.5rem 0;">ยังไม่มีรายการลงขาย</h4>
                            <p style="color:#6b7280; font-size:0.95rem; margin:0 0 1.5rem 0; max-width:400px; line-height:1.5;">เริ่มสร้างรายได้จากการประเมินและขึ้นทะเบียนคาร์บอนเครดิตของคุณได้เลย</p>
                            <a href="create_listing.php" class="btn btn-primary" style="background:#3b82f6; padding:0.75rem 1.5rem; border-radius:99px; font-weight:600; box-shadow:0 4px 14px 0 rgba(59, 130, 246, 0.39);">ลงขายสินค้าใหม่</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>📢 ประกาศล่าสุด</h3>
            <?php
            $annQ = pg_query($conn, "SELECT * FROM system_announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5");
            if (mysqli_num_rows($annQ) > 0) {
                while ($ann = mysqli_fetch_assoc($annQ)) {
                    echo "<div style='margin-bottom:1.5rem; border-bottom:1px solid #eee; padding-bottom:1rem;'>";
                    if (!empty($ann['image'])) {
                        echo "<img src='../uploads/" . htmlspecialchars($ann['image']) . "' alt='promo' style='width:100%; max-height:200px; object-fit:cover; border-radius:8px; margin-bottom:1rem;'>";
                    }
                    echo "<strong style='display:block; margin-bottom:0.25rem; font-size:1.1rem;'>" . htmlspecialchars($ann['title']) . "</strong>";
                    echo "<span style='color:var(--text-light); font-size:0.95rem; line-height:1.5; display:block;'>" . nl2br(htmlspecialchars($ann['content'])) . "</span>";
                    echo "<small style='color:#cbd5e1; font-size:0.8rem; display:block; margin-top:0.5rem;'>" . date('d M Y', strtotime($ann['created_at'])) . "</small>";
                    echo "</div>";
                }
            } else {
                echo "<p style='color:var(--text-light);'>ไม่มีประกาศใหม่</p>";
            }
            ?>
        </div>

    </div>
</div>

<script src="../assets/js/autologout.js"></script>
<?php include '../includes/alerts.php'; ?>
</body>
</html>
