<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะผู้ดูแลระบบ');
}

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$id = (int)$_GET['id'];
$query = mysqli_query($conn, "
    SELECT u.*, b.bank_name, b.account_number AS bank_account
    FROM users u 
    LEFT JOIN bank_accounts b ON u.id = b.user_id 
    WHERE u.id = $id
");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    exit('ไม่พบผู้ใช้งาน');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดผู้ใช้ | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
</head>
<body>

<div class="navbar">
    <div style="font-weight:700;">🛡 รายละเอียดผู้ใช้งาน (ID: #<?= $user['id'] ?>)</div>
    <a href="users.php" style="color:var(--text);">← กลับหน้ารายชื่อ</a>
</div>

<div class="container" style="max-width:800px; padding:2rem 0;">
    <div class="card">
        <h2 style="margin-bottom:1.5rem;">ข้อมูลส่วนตัว</h2>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:2rem;">
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">Username</p>
                <div style="font-weight:500;"><?= htmlspecialchars($user['username'] ?? '-') ?></div>
            </div>
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">Email</p>
                <div style="font-weight:500;"><?= htmlspecialchars($user['email'] ?? '-') ?></div>
            </div>
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">เบอร์โทรศัพท์</p>
                <div style="font-weight:500;"><?= htmlspecialchars($user['phone'] ?? '-') ?></div>
            </div>
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">ประเภทผู้ใช้งาน</p>
                <div style="font-weight:500;">
                    <?php 
                        if(in_array($user['user_type'], ['gov', 'government'])) echo '🏛️ ภาครัฐ';
                        elseif(in_array($user['user_type'], ['corp', 'corporate'])) echo '🏢 เอกชน (นิติบุคคล)';
                        else echo '👤 บุคคลธรรมดา';
                    ?>
                </div>
            </div>
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">เลขบัตรประชาชน</p>
                <div style="font-weight:500;"><?= htmlspecialchars($user['national_id'] ?: '-') ?></div>
            </div>
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">สถานะ</p>
                <div style="font-weight:500;">
                    <?= $user['status'] === 'banned' ? '<span style="color:#ef4444;">ระงับการใช้งาน</span>' : '<span style="color:#10b981;">ปกติ</span>' ?>
                </div>
            </div>
        </div>

        <h2 style="margin-bottom:1.5rem; border-top:1px solid #e2e8f0; padding-top:1.5rem;">ข้อมูลบัญชีธนาคาร & ภาษี</h2>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:2rem;">
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">ธนาคาร</p>
                <div style="font-weight:500;"><?= htmlspecialchars($user['bank_name'] ?? '-') ?></div>
            </div>
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">เลขบัญชี</p>
                <div style="font-weight:500;"><?= htmlspecialchars($user['bank_account'] ?? '-') ?></div>
            </div>
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">เลขประจำตัวผู้เสียภาษี</p>
                <div style="font-weight:500;"><?= htmlspecialchars($user['tax_id'] ?: '-') ?></div>
            </div>
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">วันที่สมัครบัญชี</p>
                <div style="font-weight:500;"><?= $user['created_at'] ?></div>
            </div>
        </div>

        <?php if (in_array($user['user_type'], ['gov', 'government'])): ?>
        <h2 style="margin-bottom:1.5rem; border-top:1px solid #e2e8f0; padding-top:1.5rem; color:#2563eb;">ข้อมูลหน่วยงานภาครัฐ</h2>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1rem;">
            <div style="grid-column: span 2;">
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">ชื่อหน่วยงาน</p>
                <div style="font-weight:500;"><?= htmlspecialchars($user['agency_name'] ?: '-') ?></div>
            </div>
            <div style="grid-column: span 2;">
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">ที่อยู่หน่วยงาน</p>
                <div style="font-weight:500; font-size:0.95rem; line-height:1.5; background:#f8fafc; padding:10px; border-radius:6px;"><?= nl2br(htmlspecialchars($user['agency_address'] ?: '-')) ?></div>
            </div>
        </div>
        
        <h3 style="margin-bottom:1rem; margin-top:1.5rem; color:#475569;">ผู้มีอำนาจลงนาม</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1rem; background:#f8fafc; padding:15px; border-radius:8px; border:1px solid #e2e8f0;">
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">ชื่อ-นามสกุล ผู้มีอำนาจ</p>
                <div style="font-weight:500;"><?= htmlspecialchars(trim(($user['auth_first_name']??'') . ' ' . ($user['auth_last_name']??'')) ?: '-') ?></div>
            </div>
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">ตำแหน่ง</p>
                <div style="font-weight:500;"><?= htmlspecialchars($user['auth_position'] ?: '-') ?></div>
            </div>
        </div>

        <?php if ($user['has_poa']): ?>
        <h3 style="margin-bottom:1rem; margin-top:1.5rem; color:#166534;">ข้อมูลผู้รับมอบอำนาจ (หากมีการมอบอำนาจ)</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-bottom:1rem; background:#f0fdf4; padding:15px; border-radius:8px; border:1px solid #bbf7d0;">
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">ชื่อ-นามสกุล ผู้รับมอบ</p>
                <div style="font-weight:500;"><?= htmlspecialchars(trim(($user['poa_first_name']??'') . ' ' . ($user['poa_last_name']??'')) ?: '-') ?></div>
            </div>
            <div>
                <p style="color:#64748b; font-size:0.9rem; margin-bottom:0.2rem;">ตำแหน่ง</p>
                <div style="font-weight:500;"><?= htmlspecialchars($user['poa_position'] ?: '-') ?></div>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <h2 style="margin-bottom:1.5rem; border-top:1px solid #e2e8f0; padding-top:1.5rem;">เอกสารอ้างอิง (PDF / Image)</h2>
        <div style="display:flex; flex-wrap:wrap; gap:1rem;">
            <?php if ($user['gov_doc']): ?>
                <a href="../assets/uploads/docs/<?= htmlspecialchars($user['gov_doc']) ?>" target="_blank" style="padding:0.5rem 1rem; background:#3b82f6; color:white; border-radius:6px; text-decoration:none;">📄 ดูหนังสือยืนยันตัวตนภาครัฐ</a>
            <?php endif; ?>
            
            <?php if ($user['auth_doc']): ?>
                <a href="../assets/uploads/docs/<?= htmlspecialchars($user['auth_doc']) ?>" target="_blank" style="padding:0.5rem 1rem; background:#0ea5e9; color:white; border-radius:6px; text-decoration:none;">📄 หนังสือแต่งตั้งผู้มีอำนาจลงนาม</a>
            <?php endif; ?>
            
            <?php if ($user['poa_doc']): ?>
                <a href="../assets/uploads/docs/<?= htmlspecialchars($user['poa_doc']) ?>" target="_blank" style="padding:0.5rem 1rem; background:#10b981; color:white; border-radius:6px; text-decoration:none;">📄 หนังสือมอบอำนาจ + บัตร ปชช. ผู้รับมอบ</a>
            <?php endif; ?>
            
            <?php if ($user['corp_cert']): ?>
                <a href="../assets/uploads/docs/<?= htmlspecialchars($user['corp_cert']) ?>" target="_blank" style="padding:0.5rem 1rem; background:#f59e0b; color:white; border-radius:6px; text-decoration:none;">📄 ดูหนังสือรับรองบริษัท</a>
            <?php endif; ?>
            
            <?php if ($user['vat_id']): ?>
                <a href="../assets/uploads/docs/<?= htmlspecialchars($user['vat_id']) ?>" target="_blank" style="padding:0.5rem 1rem; background:#f59e0b; color:white; border-radius:6px; text-decoration:none;">📄 ดูใบทะเบียนภาษีมูลค่าเพิ่ม (ภ.พ.20)</a>
            <?php endif; ?>

            <?php if (!$user['gov_doc'] && !$user['corp_cert'] && !$user['vat_id'] && !$user['auth_doc'] && !$user['poa_doc']): ?>
                <p style="color:#64748b;">(ไม่มีเอกสารแนบ - กรณีบุคคลธรรมดา)</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
