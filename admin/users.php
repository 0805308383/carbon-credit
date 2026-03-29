<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะผู้ดูแลระบบ');
}

$users = pg_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>บริหารจัดการผู้ใช้งาน | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="assets/admin_custom.css">
</head>
<body class="admin-body">

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="page-header">
            <h1 class="page-title">รายชื่อผู้ใช้งานในระบบ</h1>
            <p style="color: var(--admin-text-muted); margin-top: 0.5rem;">ตรวจสอบสถานะและจัดการบัญชีผู้ใช้งานทั้งหมด</p>
        </header>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px; text-align: center;">ID</th>
                        <th>ข้อมูลผู้ใช้</th>
                        <th>บัตรประชาชน</th>
                        <th>บทบาท/ประเภท</th>
                        <th>สถานะ</th>
                        <th>วันที่สมัคร</th>
                        <th style="text-align: right;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td style="text-align: center; color: var(--admin-text-muted);">#<?= $row['id']; ?></td>
                        <td>
                            <div style="font-weight: 600; color: var(--admin-text);"><?= htmlspecialchars($row['username'] ?? 'User #'.$row['id']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= htmlspecialchars($row['phone']); ?></div>
                        </td>
                        <td><code style="background: #f1f5f9; padding: 2px 4px; border-radius: 4px;"><?= htmlspecialchars($row['national_id']); ?></code></td>
                        <td>
                            <?php if ($row['role'] === 'admin'): ?>
                                <span class="badge badge-red">🛡️ แอดมิน</span>
                            <?php elseif ($row['user_type'] === 'corp' || $row['user_type'] === 'corporate'): ?>
                                <span class="badge" style="background:#fef3c7; color:#92400e; border: 1px solid #fde68a;">🏢 ภาคเอกชน</span>
                            <?php elseif ($row['user_type'] === 'gov' || $row['user_type'] === 'government'): ?>
                                <span class="badge" style="background:#f3e8ff; color:#6b21a8; border: 1px solid #e9d5ff;">🏛️ ภาครัฐ</span>
                            <?php else: ?>
                                <span class="badge badge-blue">👤 บุคคลธรรมดา</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'banned'): ?>
                                <span class="badge badge-red">❌ ถูกระงับ</span>
                            <?php else: ?>
                                <span class="badge badge-green">✅ ปกติ</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: var(--admin-text-muted); font-size: 0.8rem;"><?= date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a href="user_details.php?id=<?= $row['id'] ?>" class="btn" style="background: #f1f5f9; color: #475569; padding: 0.5rem 0.75rem; font-size: 0.8rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                                    🔍 ดูข้อมูล
                                </a>
                                <?php if ($row['role'] !== 'admin' && $row['status'] !== 'banned'): ?>
                                    <button onclick="banUser(<?= $row['id'] ?>)" class="btn" style="background: #fee2e2; color: #dc2626; padding: 0.5rem 0.75rem; font-size: 0.8rem; border: 1px solid #fecaca; border-radius: 6px; cursor: pointer;">
                                        🚫 ระงับ
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function banUser(userId) {
    Swal.fire({
        title: 'ระบุเหตุผลการระงับบัญชี',
        input: 'textarea',
        inputPlaceholder: 'พิมพ์เหตุผลที่ระงับบัญชีผู้ใช้นี้...',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ยืนยันการระงับบัญชี',
        cancelButtonText: 'ยกเลิก',
        preConfirm: (reason) => {
            if (!reason) {
                Swal.showValidationMessage('กรุณาระบุเหตุผล');
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${userId}&reason=${encodeURIComponent(result.value)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('สำเร็จ', 'ระงับบัญชีผู้ใช้งานแล้ว', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('ผิดพลาด', data.message || 'ไม่สามารถระงับบัญชีได้', 'error');
                }
            });
        }
    });
}
</script>

</body>
</html>
