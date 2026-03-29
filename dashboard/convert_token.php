<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    exit('เฉพาะผู้ใช้งานเท่านั้น');
}

$user_id = $_SESSION['user_id'];
$wallet = pg_fetch_assoc(pg_query($conn, "SELECT token FROM wallets WHERE user_id = $user_id"));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แปลง Token | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <style>
        .token-balance-card {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
        }
        .token-amount {
            font-size: 3.5rem;
            font-weight: 800;
            margin: 0.5rem 0;
        }
        .convert-info-box {
            background: #eff6ff;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            border: 1px solid #dbeafe;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="db-layout">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="db-main">
        <header style="margin-bottom: 2rem;">
            <h1 style="margin:0;">🔄 แปลง Token เป็นเงินบาท</h1>
            <p style="color:var(--text-light);">เปลี่ยน Carbon Token ของคุณให้เป็นยอดเงินในกระเป๋าเพื่อแจ้งถอน</p>
        </header>

        <div style="max-width: 600px; margin: 0 auto;">
            <div class="token-balance-card">
                <div style="font-size:1.1rem; opacity:0.9; font-weight: 500;">เหรียญคาร์บอนคงเหลือ (Available CC)</div>
                <div class="token-amount"><?= number_format($wallet['token'] ?? 0, 2) ?> CC</div>
                <div style="font-size:0.9rem; opacity:0.8;">(สะสมจากการขายคาร์บอนเครดิต)</div>
            </div>

            <div class="card">
                <div class="convert-info-box">
                    <div style="font-size: 0.9rem; color: #1e40af; margin-bottom: 0.25rem;">อัตราแลกเปลี่ยนปัจจุบัน</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #1d4ed8;">1 Carbon Token (CC) = 1.00 บาท</div>
                </div>

                <form action="../process/convert_token_process.php" method="POST">
                    <div style="margin-bottom: 2rem;">
                        <label style="display:block; margin-bottom:0.75rem; font-weight:600;">ระบุจำนวน Token ที่ต้องการแปลง</label>
                        <div style="position:relative;">
                            <input type="number" name="token_amount" min="1" step="0.01" required placeholder="0.00" 
                                   style="padding-left: 3rem; margin-bottom: 0; font-size: 1.25rem; font-weight: 700;">
                            <i class="fas fa-coins" style="position:absolute; left:1.25rem; top:50%; transform:translateY(-50%); color:#f59e0b;"></i>
                            <span style="position:absolute; right:1.25rem; top:50%; transform:translateY(-50%); font-weight:800; color:var(--text-light);">CC</span>
                        </div>
                    </div>

                    <button type="submit" style="width:100%; font-weight:700; padding:1.25rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                        <i class="fas fa-exchange-alt"></i> ยืนยันการแปลงเหรียญ
                    </button>
                    
                    <p style="text-align:center; font-size:0.85rem; color:var(--text-light); margin-top:1.5rem;">
                        * เมื่อแปลงสำเร็จ ยอดจะถูกเพิ่มเข้าไปในกระเป๋าเงิน (Wallet) ของคุณทันที
                    </p>
                </form>
            </div>
        </div>
    </main>
</div>

<script src="../assets/js/autologout.js"></script>
<?php include '../includes/alerts.php'; ?>
</body>
</html>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
