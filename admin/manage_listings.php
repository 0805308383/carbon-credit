<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะผู้ดูแลระบบ');
}

$listings = mysqli_query($conn, "
    SELECT l.*, u.phone
    FROM carbon_listings l
    JOIN users u ON l.seller_id = u.id
    ORDER BY l.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>บริหารจัดการรายการขาย | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="assets/admin_custom.css">
</head>
<body class="admin-body">

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="page-header">
            <h1 class="page-title">อนุมัติรายการคาร์บอนเครดิต</h1>
            <p style="color: var(--admin-text-muted); margin-top: 0.5rem;">ตรวจสอบความถูกต้องของรูปภาพและเอกสารก่อนอนุมัติรายการ</p>
        </header>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ข้อมูลผู้ขาย</th>
                        <th>ประเภท/พื้นที่</th>
                        <th>รายละเอียดโครงการ</th>
                        <th style="text-align: right;">ปริมาณคาร์บอน (ตัน)</th>
                        <th style="text-align: right;">ราคา (CC)</th>
                        <th>หลักฐาน/เอกสาร</th>
                        <th>สถานะ</th>
                        <th style="text-align: right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($listings)): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: var(--admin-text);">📞 <?= htmlspecialchars($row['phone']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);">ID: #<?= $row['id'] ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">
                                <?= $row['type'] === 'tree' ? '🌳 ทรัพยากรป่าไม้' : '🌾 ภาคการเกษตร'; ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 0.25rem;">
                                📍 จังหวัด<?= htmlspecialchars($row['province'] ?? 'ไม่ระบุ'); ?>
                            </div>
                        </td>
                        <td style="font-size: 0.85rem; line-height: 1.4;">
                            <?php if ($row['type'] === 'tree'): ?>
                                <span style="color: var(--admin-text-muted);">จำนวน:</span> <strong><?= number_format($row['tree_count']); ?></strong> ต้น<br>
                                <span style="color: var(--admin-text-muted);">อายุ:</span> <strong><?= $row['tree_age']; ?></strong> ปี • <strong><?= $row['avg_height'] ?? $row['tree_height']; ?></strong> ม.
                            <?php else: ?>
                                <span style="color: var(--admin-text-muted);">พื้นที่:</span> <strong><?= number_format($row['rice_area'] ?? $row['land_area']); ?></strong> ไร่<br>
                                <span style="color: var(--admin-text-muted);">รอบเก็บเกี่ยว:</span> <strong><?= $row['rice_age'] ?? '-'; ?></strong> ปี/รอบ
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;"><strong style="color: var(--admin-success); font-size: 1rem;"><?= number_format($row['carbon_amount'], 2); ?></strong></td>
                        <td style="text-align: right;"><strong style="color: var(--admin-primary); font-size: 1rem;"><?= number_format($row['price_token'], 2); ?></strong></td>
                        <td>
                            <div style="display:flex; gap:6px;">
                                <?php 
                                $images = [
                                    'รูป' => $row['full_tree_image'] ?: $row['image'],
                                    'อ้างอิง' => $row['reference_image'],
                                    'ปชช' => $row['id_card_image'],
                                    'ที่ดิน' => $row['land_document']
                                ];
                                foreach ($images as $label => $img): 
                                    if ($img):
                                        $isPdf = strtolower(pathinfo($img, PATHINFO_EXTENSION)) === 'pdf';
                                        $isDoc = ($label === 'ที่ดิน');
                                        $baseDir = $isDoc ? '../uploads/documents/' : '../uploads/';
                                        $fileUrl = $baseDir . htmlspecialchars($img);
                                ?>
                                    <div style="text-align:center;">
                                        <a href="<?= $fileUrl ?>" target="_blank" style="text-decoration: none;">
                                            <?php if ($isPdf): ?>
                                                <div style="width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:#fee2e2; border:1px solid #fecaca; border-radius:6px;">
                                                    <span style="font-size: 0.7rem; color: #dc2626; font-weight: 700;">PDF</span>
                                                </div>
                                            <?php else: ?>
                                                <img src="<?= $fileUrl ?>" style="width:36px; height:36px; object-fit:cover; border:1px solid #e2e8f0; border-radius:6px;">
                                            <?php endif; ?>
                                        </a>
                                        <div style="font-size: 0.6rem; color: var(--admin-text-muted); margin-top: 2px;"><?= $label ?></div>
                                    </div>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'approved'): ?><span class="badge badge-green">อนุมัติแล้ว</span>
                            <?php elseif ($row['status'] === 'rejected'): ?><span class="badge badge-red">ปฏิเสธ</span>
                            <?php else: ?><span class="badge" style="background:#fef3c7; color:#d97706; border: 1px solid #fde68a;">⏳ รออนุมัติ</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <?php if ($row['status'] === 'pending'): ?>
                                <div style="display:flex; gap:4px; justify-content: flex-end;">
                                    <a href="update_listing_status.php?id=<?= $row['id']; ?>&action=approve" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.75rem; background: #10b981; color: white; border: none; border-radius: 6px; font-weight: 600; text-decoration: none;">อนุมัติ</a>
                                    <a href="update_listing_status.php?id=<?= $row['id']; ?>&action=reject" class="btn" style="padding: 0.5rem 0.75rem; font-size: 0.75rem; background: #ef4444; color: white; border: none; border-radius: 6px; font-weight: 600; text-decoration: none;">ปฏิเสธ</a>
                                </div>
                            <?php else: ?>
                                <span style="font-size: 0.75rem; color: var(--admin-text-muted);">จัดการแล้ว</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
