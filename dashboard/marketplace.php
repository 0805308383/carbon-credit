<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$listings = mysqli_query($conn, "
    SELECT l.*, u.phone, u.user_type, u.first_name, u.last_name, u.agency_name
    FROM carbon_listings l
    JOIN users u ON l.seller_id = u.id
    WHERE l.status = 'approved'
    ORDER BY l.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Marketplace | Carbon Market</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/modern.css">
<style>
    .marketplace-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    .listing-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .listing-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .listing-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: #f3f4f6;
    }
    .listing-content {
        padding: 1.5rem;
    }
    .listing-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
    }
    .listing-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: #f3f4f6;
        border-radius: 99px;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
</style>
</head>
<body>

<div class="db-layout">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <main class="db-main">
        <header style="margin-bottom: 2rem;">
            <h1 style="margin:0;">🛒 ตลาดซื้อขาย</h1>
            <p style="color:var(--text-light);">เลือกซื้อคาร์บอนเครดิตจากโครงการที่ผ่านการรับรอง</p>
        </header>

        <div class="container-fluid">
            <?php if (mysqli_num_rows($listings) == 0): ?>
                <div style="text-align:center; padding:4rem; color:var(--text-light); background:white; border-radius:12px; border:1px solid var(--border);">
                    <p style="font-size:1.2rem;">ยังไม่มีรายการขายในขณะนี้</p>
                </div>
            <?php endif; ?>

            <div class="marketplace-grid">
            <?php while ($row = mysqli_fetch_assoc($listings)): ?>
                <div class="listing-card">
                    <?php 
                    $displayImage = !empty($row['full_tree_image']) ? $row['full_tree_image'] : $row['image'];
                    if (!empty($displayImage)): ?>
                        <img src="../uploads/<?= htmlspecialchars($displayImage); ?>" class="listing-image">
                    <?php else: ?>
                        <div class="listing-image" style="display:flex; align-items:center; justify-content:center; color:#9fa6b2;">
                            No Image
                        </div>
                    <?php endif; ?>

                    <div class="listing-content">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                            <span class="listing-badge">
                                <?= $row['type'] === 'tree' ? '🌳 ต้นไม้' : '🌾 นาข้าว'; ?>
                            </span>
                            <span style="font-size: 0.8rem; color: #64748b;">📍 <?= htmlspecialchars($row['province'] ?? 'ไม่ระบุ') ?></span>
                        </div>
                        
                        <h3 style="margin: 0.5rem 0;">
                            <?= number_format($row['carbon_amount'], 2); ?> ตันคาร์บอน
                        </h3>

                        <?php 
                            $rem = $row['remaining_amount'] ?? ($row['type'] === 'tree' ? $row['tree_count'] : ($row['rice_area'] ?? $row['land_area']));
                        ?>
                        <div style="color:var(--text-light); font-size:0.9rem; margin-bottom:1rem;">
                            <?php if ($row['type'] === 'tree'): ?>
                                คงเหลือ: <strong class="text-primary remaining-text" data-id="<?= $row['id'] ?>" data-tree="1"><?= (int)$rem ?></strong> ต้น<br>
                                (สัดส่วนจาก <?= $row['tree_count']; ?> ต้น) | สูง <?= $row['tree_height'] ?? $row['avg_height']; ?> ม.
                            <?php else: ?>
                                คงเหลือ: <strong class="text-primary remaining-text" data-id="<?= $row['id'] ?>"><?= number_format($rem * 400) ?></strong> ตร.ว.<br>
                                (สัดส่วนจาก <?= ($row['rice_area'] ?? $row['land_area']) * 400; ?> ตร.ว.)
                            <?php endif; ?>
                            <br>
                            <?php 
                                $utype = $row['user_type'] ?? '';
                                $typeLabel = '(บุคคลธรรมดา)';
                                $sellerName = trim(($row['first_name']??'') . ' ' . ($row['last_name']??''));
                                
                                if (in_array($utype, ['government', 'gov'])) {
                                    $typeLabel = '(ภาครัฐ)';
                                    $sellerName = $row['agency_name'] ?: 'ไม่ระบุชื่อหน่วยงาน';
                                }
                                elseif (in_array($utype, ['corporate', 'corp'])) {
                                    $typeLabel = '(เอกชน)';
                                    $sellerName = $row['agency_name'] ?: 'ไม่ระบุชื่อบริษัท';
                                }
                                
                                if (!$sellerName) $sellerName = 'ไม่ระบุชื่อ';
                            ?>
                            ผู้ขาย: <?= htmlspecialchars($sellerName); ?> <span style="color:#94a3b8; font-size:0.8rem;"><?= $typeLabel ?></span>
                        </div>

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem; border-top:1px solid var(--border); padding-top:1rem; margin-bottom:0.5rem;">
                            <div class="listing-price">
                                <?= number_format($row['price_token'], 2); ?> CC
                                <div style="font-size:0.75rem; color:#64748b; font-weight:normal;">(ราคารวมทั้งโปรเจกต์)</div>
                            </div>
                        </div>

                        <?php if ($rem <= 0 || $row['status'] == 'sold'): ?>
                            <div class="mt-2 text-center" style="color:white; background:#ef4444; padding:8px; border-radius:6px; font-weight:bold;">SOLD OUT</div>
                        <?php else: ?>
                            <?php if ($_SESSION['user_id'] != $row['seller_id']): ?>
                                <div style="text-align: right;">
                                    <a href="buy_checkout.php?id=<?= $row['id'] ?>" class="btn" style="padding:6px 16px; background:var(--primary); color:white; text-decoration:none; border-radius:6px; font-weight:600;">เลือกซื้อสินค้า</a>
                                </div>
                            <?php else: ?>
                                <div class="mt-2" style="color:#ef4444; font-size:0.875rem; text-align:center;">สินค้าของคุณเอง</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/alerts.php'; ?>
<script>
setInterval(() => {
    let ids = [];
    document.querySelectorAll('.remaining-text').forEach(el => {
        ids.push(el.getAttribute('data-id'));
    });
    
    if (ids.length > 0) {
        fetch('../process/get_listing_status.php?ids=' + ids.join(','))
        .then(res => res.json())
        .then(response => {
            if (response.success && response.data) {
                for (const [id, info] of Object.entries(response.data)) {
                    let el = document.querySelector(`.remaining-text[data-id="${id}"]`);
                    if (el) {
                        let isTree = el.hasAttribute('data-tree');
                        let newVal = isTree ? parseInt(info.remaining_amount) : (parseFloat(info.remaining_amount) * 400);
                        if (newVal !== parseInt(el.innerText.replace(/,/g, ''))) {
                            el.innerText = newVal.toLocaleString();
                        }
                        
                        if (info.remaining_amount <= 0 || info.status === 'sold') {
                            let checkoutBtn = el.closest('.listing-card').querySelector('a.btn');
                            if (checkoutBtn) {
                                let parent = checkoutBtn.parentElement;
                                parent.innerHTML = '<div class="mt-2 text-center" style="color:white; background:#ef4444; padding:8px; border-radius:6px; font-weight:bold;">SOLD OUT</div>';
                            }
                        }
                    }
                }
            }
        }).catch(err => console.error(err));
    }
}, 5000);
</script>
</body>
</html>
