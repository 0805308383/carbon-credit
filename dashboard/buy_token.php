<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เติม Token | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <style>
        .qr-section {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: #f8fafc;
            border-radius: 16px;
            border: 2px dashed #e2e8f0;
            transition: border-color 0.2s;
        }
        .qr-section:hover {
            border-color: var(--primary);
        }
        .qr-section img {
            max-width: 220px;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .token-input-group {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f1f5f9;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .file-upload-box {
            border: 2px dashed #cbd5e1;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #f8fafc;
        }
        .file-upload-box:hover {
            border-color: var(--primary);
            background: #f0fdf4;
        }
    </style>
</head>
<body>

<div class="db-layout">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="db-main">
        <header style="margin-bottom: 2rem;">
            <h1 style="margin:0;">💳 เติม Carbon Token</h1>
            <p style="color:var(--text-light);">ซื้อ Token เพื่อใช้สำหรับการซื้อขายคาร์บอนเครดิตในตลาด</p>
        </header>

        <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 2rem; align-items: start;">
            <!-- QR Info -->
            <div class="card">
                <div class="qr-section">
                    <h4 style="margin-top:0; margin-bottom: 1rem; color: var(--text-light);">Scan QR Code เพื่อโอนเงิน</h4>
                    <img src="../uploads/qr.jfif" alt="QR Code">
                    <div style="font-size: 1.1rem; font-weight: 700; color: var(--text);">ธนาคารกสิกรไทย</div>
                    <div style="font-size: 1.25rem; letter-spacing: 1px; color: var(--primary); font-weight: 800; margin: 0.25rem 0;">064-390-8239</div>
                    <div style="font-size: 0.95rem; color: var(--text-light);">ชื่อบัญชี: Carbon Credit Market</div>
                </div>
                
                <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 1rem; border-radius: 4px;">
                    <p style="margin:0; font-size: 0.85rem; color: #92400e;">
                        <i class="fas fa-exclamation-triangle"></i> <strong>คำเตือน:</strong> โปรดตรวจสอบยอดเงินและชื่อบัญชีให้ถูกต้องก่อนกดยืนยันการโอน
                    </p>
                </div>
            </div>

            <!-- Form Card -->
            <div class="card">
                <form action="../process/buy_token_process.php" method="POST" enctype="multipart/form-data">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display:block; margin-bottom:0.75rem; font-weight:600;">ระบุจำนวน Token ที่ต้องการ (1 CC = 1 บาท)</label>
                        <div class="token-input-group">
                            <i class="fas fa-coins" style="color: #f59e0b;"></i>
                            <input type="number" name="token_amount" min="1" step="1" required placeholder="ระบุจำนวน" 
                                   style="border:none; background:transparent; margin-bottom:0; font-size:1.25rem; font-weight:700; width:100%; outline:none;">
                            <span style="font-weight:800; color:var(--text-light);">CC</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label style="display:block; margin-bottom:0.75rem; font-weight:600;">แนบสลิปการโอนเงิน</label>
                        <div class="file-upload-box" onclick="document.getElementById('slipInput').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size:2rem; color:var(--primary); margin-bottom:0.5rem;"></i>
                            <div style="font-size:0.9rem; color:var(--text-light);">คลิกเพื่อเลือกไฟล์รูปภาพสลิป</div>
                            <input type="file" name="slip" id="slipInput" accept="image/*" required style="display:none;" onchange="previewSlip(this)">
                        </div>
                        
                        <div id="slipPreview" style="margin-top:1.5rem; display:none; border-radius:12px; overflow:hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                            <img id="previewImg" src="#" alt="Preview" style="max-width:100%; height:auto; display:block;">
                        </div>
                    </div>

                    <button type="submit" style="width:100%; font-weight:700; padding: 1rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                        <i class="fas fa-paper-plane"></i> ยืนยันและแจ้งโอนเงิน
                    </button>
                    <p style="text-align:center; font-size:0.85rem; color:var(--text-light); margin-top:1rem;">
                        * เจ้าหน้าที่จะใช้เวลาตรวจสอบยอดเงิน 5-15 นาที
                    </p>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function previewSlip(input) {
    const preview = document.getElementById('slipPreview');
    const img = document.getElementById('previewImg');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>

<?php include '../includes/alerts.php'; ?>
</body>
</html>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
