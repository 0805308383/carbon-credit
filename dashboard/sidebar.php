<!-- User Sidebar -->
<div class="db-sidebar">
    <div class="db-sidebar-brand">
        🌱 CarbonMarket
    </div>
    <nav class="db-sidebar-menu">
        <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">📊 ภาพรวม</a>
        <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">👤 โปรไฟล์</a>
        <a href="marketplace.php" class="<?= basename($_SERVER['PHP_SELF']) == 'marketplace.php' ? 'active' : '' ?>">🛒 ตลาดซื้อขาย</a>
        
        <?php if ($_SESSION['role'] !== 'admin'): ?>
            <a href="create_listing.php" class="<?= basename($_SERVER['PHP_SELF']) == 'create_listing.php' ? 'active' : '' ?>">➕ ลงขายสินค้า</a>
            <a href="calculator.php" class="<?= basename($_SERVER['PHP_SELF']) == 'calculator.php' ? 'active' : '' ?>">🧮 คำนวณคาร์บอน</a>
            <a href="convert_token.php" class="<?= basename($_SERVER['PHP_SELF']) == 'convert_token.php' ? 'active' : '' ?>">🔄 แปลง Token</a>
            <a href="buy_token.php" class="<?= basename($_SERVER['PHP_SELF']) == 'buy_token.php' ? 'active' : '' ?>">💳 เติม Token</a>
            <a href="history.php" class="<?= basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : '' ?>">🧾 ประวัติการซื้อขาย</a>
            <a href="withdraw.php" class="<?= basename($_SERVER['PHP_SELF']) == 'withdraw.php' ? 'active' : '' ?>">💸 ถอนเงิน</a>
        <?php endif; ?>

        <div style="flex-grow:1"></div>
        <a href="../auth/logout.php" style="color: #ef4444; border-top: 1px solid #f1f5f9; padding-top: 1rem; border-radius: 0;">🚪 ออกจากระบบ</a>
    </nav>
</div>

