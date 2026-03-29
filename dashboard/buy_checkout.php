<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id = (int)$_GET['id'];
$q = mysqli_query($conn, "
    SELECT l.*, u.phone, u.user_type, u.first_name, u.last_name, u.agency_name 
    FROM carbon_listings l
    JOIN users u ON l.seller_id = u.id
    WHERE l.id = $id AND l.status = 'approved'
");
$row = mysqli_fetch_assoc($q);

if (!$row) {
    $_SESSION['flash_alert'] = ['type' => 'error', 'title' => 'ผิดพลาด', 'message' => 'ไม่พบรายการที่ต้องการดึงข้อมูล'];
    header("Location: marketplace.php");
    exit;
}

$rem = $row['remaining_amount'] ?? ($row['type'] === 'tree' ? $row['tree_count'] : $row['rice_area']);
if ($rem <= 0 || $row['status'] === 'sold') {
    $_SESSION['flash_alert'] = ['type' => 'error', 'title' => 'ผิดพลาด', 'message' => 'สินค้านี้ถูกขายไปหมดแล้ว'];
    header("Location: marketplace.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>สั่งซื้อและระบุจำนวน | Carbon Market</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/modern.css">
<style>
    .checkout-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        max-width: 600px;
        margin: 2rem auto;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
    }
    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 1rem;
        margin-top: 0.5rem;
    }
</style>
</head>
<body style="background:#f1f5f9;">

<div class="navbar">
    <div style="font-weight:700; font-size:1.25rem;">🛒 CarbonMarket Checkout</div>
    <a href="marketplace.php" class="btn" style="background:transparent; color:var(--text); border:1px solid var(--border);">← กลับไปตลาด</a>
</div>

<div class="container">
    <div class="checkout-card">
        <h2 style="margin-bottom:1.5rem; color:var(--primary);">ชำระเงิน / ระบุจำนวนที่ต้องการซื้อ</h2>
        
        <?php 
        $displayImage = !empty($row['full_tree_image']) ? $row['full_tree_image'] : $row['image'];
        if (!empty($displayImage)): ?>
            <div style="margin-bottom: 20px; border-radius: 8px; overflow: hidden; max-height: 250px;">
                <img src="../uploads/<?= htmlspecialchars($displayImage); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
            </div>
        <?php endif; ?>

        <div style="background:#f8fafc; padding:1.5rem; border-radius:8px; margin-bottom:1.5rem; border:1px solid #e2e8f0;">
            <div style="margin-bottom:0.5rem; font-size:1.1rem;">
                <span style="font-weight:600;">โปรเจกต์:</span> 
                <?= $row['type'] === 'tree' ? '🌳 ต้นไม้' : '🌾 นาข้าว'; ?> 
                (<?= htmlspecialchars($row['province'] ?? 'ไม่ระบุ') ?>)
            </div>
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
            <div style="margin-bottom:0.5rem;">
                <span style="font-weight:600; color:#64748b;">ผู้ขาย:</span> <?= htmlspecialchars($sellerName) ?> <span style="color:#94a3b8; font-size:0.85rem;"><?= $typeLabel ?></span>
            </div>
            
            <?php if ($row['type'] === 'tree'): ?>
                <div style="margin-bottom:0.5rem;">
                    <span style="font-weight:600;">คงเหลือ:</span> <strong class="text-primary"><?= (int)$rem ?></strong> ต้น
                </div>
            <?php else: ?>
                <div style="margin-bottom:0.5rem;">
                    <span style="font-weight:600;">คงเหลือ:</span> <strong class="text-primary"><?= number_format($rem * 400) ?></strong> ตารางวา
                </div>
            <?php endif; ?>
            <div style="color:var(--text-light); font-size:0.9rem;">
                ราคาโครงการรวม: <?= number_format($row['price_token'], 2); ?> CC
            </div>
        </div>

        <form action="../process/buy_listing.php" method="POST">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            
            <div style="margin-bottom:1.5rem;">
                <label style="font-weight:600; display:block;">
                    <?php if ($row['type'] === 'tree'): ?>
                        จำนวนต้นไม้ที่ต้องการซื้อ (ขั้นต่ำ 1 ต้น)
                    <?php else: ?>
                        จำนวนพื้นที่ที่ต้องการซื้อ (ทวีคูณของ 20 ตารางวา)
                    <?php endif; ?>
                </label>
                
                <?php if ($row['type'] === 'tree'): ?>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="number" name="buy_amount" id="buy_amount" class="form-control" min="1" max="<?= (int)$rem ?>" required placeholder="เช่น 5" style="max-width:200px;">
                        <span>ต้น</span>
                    </div>
                <?php else: ?>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="number" name="buy_amount" id="buy_amount" class="form-control" step="20" min="20" max="<?= $rem * 400 ?>" required placeholder="เช่น 20" style="max-width:200px;">
                        <span>ตารางวา</span>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <span style="color:var(--text-light); font-size:0.9rem;">โทเคนที่ต้องใช้โดยประมาณ</span><br>
                    <strong style="font-size:1.25rem; color:#ef4444;" id="estimated_price">0.00 CC</strong>
                </div>
                <div>
                    <span style="color:var(--text-light); font-size:0.9rem;">คาร์บอนเครดิตที่ได้รับโดยประมาณ</span><br>
                    <strong style="font-size:1.25rem; color:#10b981;" id="estimated_carbon">0.00</strong>
                </div>
                <button type="submit" class="btn" style="padding:0.75rem 2rem; font-size:1.1rem;">ดำเนินการต่อ</button>
            </div>
        </form>
    </div>
</div>

<script>
const input = document.getElementById('buy_amount');
const estPrice = document.getElementById('estimated_price');
const estCarbon = document.getElementById('estimated_carbon');
const submitBtn = document.querySelector('button[type="submit"]');

const type = '<?= $row['type'] ?>';
const totalPrice = <?= (float)$row['price_token'] ?>;
const totalUnits = <?= $row['type'] === 'tree' ? (int)$row['tree_count'] : $row['rice_area'] * 400 ?>;
const totalCarbon = <?= (float)$row['carbon_amount'] ?>;
const maxAllowed = <?= $row['type'] === 'tree' ? (int)$rem : $rem * 400 ?>;

input.addEventListener('input', function() {
    let val = parseFloat(input.value) || 0;
    
    if (val > maxAllowed) {
        estPrice.innerHTML = '<span style="color:#ef4444;">เกินจำนวนคงเหลือ</span>';
        estCarbon.innerHTML = '<span style="color:#ef4444;">0.00</span>';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
        return;
    } else {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    }

    if (val > 0) {
        let proportion = val / totalUnits;
        let finalPrice = proportion * totalPrice;
        let finalCarbon = proportion * totalCarbon;
        
        estPrice.innerHTML = finalPrice.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' CC';
        estCarbon.innerHTML = finalCarbon.toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    } else {
        estPrice.innerHTML = '0.00 CC';
        estCarbon.innerHTML = '0.00';
    }
});
</script>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
