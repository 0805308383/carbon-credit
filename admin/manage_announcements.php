<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะผู้ดูแลระบบ');
}

// ดำเนินการลบ
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    pg_query($conn, "DELETE FROM system_announcements WHERE id = $id");
    $_SESSION['flash_alert'] = ['type' => 'success', 'title' => 'ลบสำเร็จ', 'message' => 'ประกาศถูกลบแล้ว'];
    header("Location: manage_announcements.php");
    exit;
}

// ดำเนินการเปลี่ยนสถานะ
if (isset($_GET['toggle_active'])) {
    $id = (int)$_GET['toggle_active'];
    pg_query($conn, "UPDATE system_announcements SET is_active = NOT is_active WHERE id = $id");
    $_SESSION['flash_alert'] = ['type' => 'success', 'title' => 'เปลี่ยนสถานะ', 'message' => 'อัปเดตสถานะสำเร็จ'];
    header("Location: manage_announcements.php");
    exit;
}

// ดำเนินการเพิ่มเนื้อหา
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = pg_real_escape_string($conn, $_POST['title']);
    $content = pg_real_escape_string($conn, $_POST['content']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'promo_' . time() . '_' . uniqid() . '.' . $ext;
        $dest = '../uploads/' . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $imagePath = $filename;
        }
    }
    
    $imgSql = $imagePath ? "'$imagePath'" : "NULL";
    pg_query($conn, "INSERT INTO system_announcements (title, content, image, is_active) VALUES ('$title', '$content', $imgSql, $is_active)");
    $_SESSION['flash_alert'] = ['type' => 'success', 'title' => 'สำเร็จ', 'message' => 'เพิ่มประกาศแล้ว'];
    header("Location: manage_announcements.php");
    exit;
}

// โหลดรายการทั้งหมด
$query = pg_query($conn, "SELECT * FROM system_announcements ORDER BY created_at DESC");

// ขอดึงค่าตัวเลขเพื่อ badge ใน sidebar
function countRow($conn, $sql) {
    $q = pg_query($conn, $sql);
    if (!$q) return 0;
    $r = pg_fetch_assoc($q);
    return $r ? (int)$r['c'] : 0;
}
$pendingListings = countRow($conn, "SELECT COUNT(*) AS c FROM carbon_listings WHERE status='pending'");
$pendingOrders = countRow($conn, "SELECT COUNT(*) AS c FROM orders WHERE status='pending_admin'");
$pendingTopups = countRow($conn, "SELECT COUNT(*) AS c FROM token_topups WHERE status='pending_admin'");
$pendingWithdraws = countRow($conn, "SELECT COUNT(*) AS c FROM withdraw_requests WHERE status='pending'");

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการประกาศ | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="assets/admin_custom.css">
</head>
<body class="admin-body">

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>

    <main class="admin-main">
        <header class="page-header">
            <h1 class="page-title">📢 จัดการประกาศและโปรโมชั่น</h1>
            <p style="color: var(--admin-text-muted); margin-top: 0.5rem;">เพิ่มและจัดการข่าวสารหรือโปรโมชั่นที่แสดงผลบนแผงควบคุมของผู้ใช้งาน</p>
        </header>

        <!-- Add Form Card -->
        <div class="table-container" style="padding: 2rem; margin-bottom: 2rem;">
            <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.125rem;">เพิ่มประกาศใหม่</h3>
            <form action="manage_announcements.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem;">
                    <div>
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--admin-text);">หัวข้อประกาศ *</label>
                            <input type="text" name="title" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 8px;">
                        </div>
                        
                        <div>
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--admin-text);">รายละเอียดเนื้อหา *</label>
                            <textarea name="content" rows="4" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--admin-border); border-radius: 8px; font-family: inherit;"></textarea>
                        </div>
                    </div>
                    <div>
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--admin-text);">รูปภาพประกอบ (ถ้ามี)</label>
                            <input type="file" name="image" accept="image/*" style="width: 100%; font-size: 0.9rem;">
                            <p style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 0.5rem;">ระบบจะแสดงรูปนี้ในหน้าแดชบอร์ดของผู้ใช้งาน</p>
                        </div>
                        
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 1.5rem;">
                            <input type="checkbox" name="is_active" checked style="width: 18px; height: 18px; accent-color: var(--admin-primary);"> 
                            <span style="font-weight: 500;">เปิดใช้งานทันที (Active)</span>
                        </label>

                        <button type="submit" class="btn" style="width: 100%; background: var(--admin-primary); color: white; padding: 0.75rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-plus-circle me-1"></i> บันทึกประกาศ
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- List Table -->
        <div class="table-container">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--admin-border);">
                <h3 style="margin: 0; font-size: 1.125rem;">รายการประกาศทั้งหมด</h3>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">รูปภาพ</th>
                        <th>หัวข้อและเนื้อหา</th>
                        <th style="width: 120px; text-align: center;">สถานะ</th>
                        <th style="width: 150px;">วันที่ลงประกาศ</th>
                        <th style="width: 120px; text-align: right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = pg_fetch_assoc($query)): ?>
                    <tr>
                        <td>
                            <?php if ($row['image']): ?>
                                <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" style="width: 70px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <?php else: ?>
                                <div style="width: 70px; height: 45px; background: #f1f5f9; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; color: #94a3b8; border: 1px dashed #cbd5e1;">NO IMG</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--admin-text); margin-bottom: 0.25rem;"><?= htmlspecialchars($row['title']) ?></div>
                            <div style="font-size: 0.85rem; color: var(--admin-text-muted); line-height: 1.4;"><?= nl2br(htmlspecialchars(mb_strimwidth($row['content'], 0, 150, "..."))) ?></div>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($row['is_active']): ?>
                                <span class="badge badge-green">แสดงผล</span>
                            <?php else: ?>
                                <span class="badge" style="background:#f1f5f9; color:#64748b;">ซ่อน</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--admin-text-muted); font-size: 0.85rem;">
                            <?= date('d M Y', strtotime($row['created_at'])) ?>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a href="manage_announcements.php?toggle_active=<?= $row['id'] ?>" class="btn" style="background: #eff6ff; color: #3b82f6; padding: 0.5rem; border: none; border-radius: 6px;" title="เปิด/ซ่อน">
                                    <i class="fas <?= $row['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                </a>
                                <a href="manage_announcements.php?delete=<?= $row['id'] ?>" class="btn" style="background: #fef2f2; color: #ef4444; padding: 0.5rem; border: none; border-radius: 6px;" onclick="return confirm('ยืนยันการลบประกาศนี้?')" title="ลบ">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (pg_num_rows($query) == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 4rem;">
                                <div style="color: var(--admin-text-muted); font-size: 1rem;">ยังไม่มีรายการประกาศในระบบ</div>
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
