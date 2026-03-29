<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    exit('เฉพาะผู้ใช้งานเท่านั้น');
}

$user_id = $_SESSION['user_id'];
$bank = mysqli_fetch_assoc(pg_query($conn, "SELECT * FROM bank_accounts WHERE user_id = $user_id LIMIT 1"));
$wallet = mysqli_fetch_assoc(pg_query($conn, "SELECT balance FROM wallets WHERE user_id = $user_id"));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ถอนเงิน | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <style>
        .balance-card {
            background: linear-gradient(135deg, var(--primary) 0%, #059669 100%);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.2);
        }
        .balance-amount {
            font-size: 3rem;
            font-weight: 800;
            margin: 0.5rem 0;
        }
        .bank-info {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }
        .bank-info h4 {
            margin: 0 0 1rem 0;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .bank-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>

<div class="db-layout">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="db-main">
        <header style="margin-bottom: 2rem;">
            <h1 style="margin:0;">💸 ถอนเงิน</h1>
            <p style="color:var(--text-light);">ถอนรายได้จากการขายคาร์บอนเครดิตเข้าบัญชีธนาคารของคุณ</p>
        </header>

        <div style="max-width: 600px; margin: 0 auto;">
            <div class="balance-card">
                <div style="font-size:1rem; opacity:0.9; font-weight: 500;">ยอดเงินที่ถอนได้สุทธิ</div>
                <div class="balance-amount">฿<?= number_format($wallet['balance'] ?? 0, 2) ?></div>
                <div style="font-size:0.85rem; opacity:0.8;">(ยอดเงินจากการขายคาร์บอนที่อนุมัติแล้ว)</div>
            </div>

            <div class="card">
                <div class="bank-info">
                    <h4><i class="fas fa-university"></i> บัญชีธนาคารรับเงิน</h4>
                    <div class="bank-detail">
                        <span style="color:var(--text-light);">ธนาคาร:</span>
                        <span style="font-weight:600;"><?= htmlspecialchars($bank['bank_name'] ?? '-'); ?></span>
                    </div>
                    <div class="bank-detail">
                        <span style="color:var(--text-light);">เลขที่บัญชี:</span>
                        <span style="font-weight:600; letter-spacing: 1px;"><?= htmlspecialchars($bank['account_number'] ?? '-'); ?></span>
                    </div>
                    <div class="bank-detail">
                        <span style="color:var(--text-light);">ชื่อบัญชี:</span>
                        <span style="font-weight:600;"><?= htmlspecialchars($bank['account_name'] ?? '-'); ?></span>
                    </div>
                </div>

                <form action="../process/withdraw_process.php" method="POST">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display:block; margin-bottom:0.5rem; font-weight:500;">ระบุจำนวนเงินที่ต้องการถอน (บาท)</label>
                        <div style="position:relative;">
                            <input type="number" name="amount" min="100" step="0.01" required placeholder="0.00" 
                                   style="padding-left: 2.5rem; margin-bottom: 0;">
                            <i class="fas fa-coins" style="position:absolute; left:1rem; top:50%; transform:translateY(-50%); color:var(--text-light);"></i>
                        </div>
                        <small style="color:var(--text-light); margin-top:0.5rem; display:block;">* ถอนขั้นต่ำ 100.00 บาท</small>
                    </div>
                    
                    <button type="submit" style="width:100%; font-weight:600; padding: 1rem;">
                        ยืนยันการแจ้งถอนเงิน
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<?php include '../includes/alerts.php'; ?>
</body>
</html>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
