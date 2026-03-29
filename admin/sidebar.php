<?php
// Function to count rows for badges
if (!function_exists('countRow')) {
    function countRow($conn, $sql) {
        $q = pg_query($conn, $sql);
        if (!$q) return 0;
        $r = mysqli_fetch_assoc($q);
        return $r ? (int)$r['c'] : 0;
    }
}
$pendingListings = countRow($conn, "SELECT COUNT(*) AS c FROM carbon_listings WHERE status='pending'");
$pendingOrders   = countRow($conn, "SELECT COUNT(*) AS c FROM orders WHERE status='pending_admin'");
$pendingTopups   = countRow($conn, "SELECT COUNT(*) AS c FROM token_topups WHERE status='pending_admin'");
$pendingWithdraws = countRow($conn, "SELECT COUNT(*) AS c FROM withdraw_requests WHERE status='pending'");

$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="admin-sidebar">
    <div class="sidebar-logo">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 32px; height: 32px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        Admin Panel
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            ภาพรวมระบบ
        </a>
        
        <a href="manage_listings.php" class="nav-link <?= $current_page == 'manage_listings.php' ? 'active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            อนุมัติรายการขาย
            <?php if ($pendingListings > 0): ?><span class="badge"><?= $pendingListings ?></span><?php endif; ?>
        </a>
        
        <a href="manage_orders.php" class="nav-link <?= $current_page == 'manage_orders.php' ? 'active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            อนุมัติการซื้อ
            <?php if ($pendingOrders > 0): ?><span class="badge"><?= $pendingOrders ?></span><?php endif; ?>
        </a>
        
        <a href="manage_withdraws.php" class="nav-link <?= $current_page == 'manage_withdraws.php' ? 'active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            อนุมัติถอนเงิน
            <?php if ($pendingWithdraws > 0): ?><span class="badge"><?= $pendingWithdraws ?></span><?php endif; ?>
        </a>
        
        <a href="manage_token_topups.php" class="nav-link <?= $current_page == 'manage_token_topups.php' ? 'active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            อนุมัติเติม Token
            <?php if ($pendingTopups > 0): ?><span class="badge"><?= $pendingTopups ?></span><?php endif; ?>
        </a>
        
        <a href="transactions.php" class="nav-link <?= $current_page == 'transactions.php' ? 'active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            ประวัติการซื้อขาย
        </a>
        
        <a href="users.php" class="nav-link <?= $current_page == 'users.php' ? 'active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            รายชื่อผู้ใช้งาน
        </a>
        
        <a href="manage_announcements.php" class="nav-link <?= $current_page == 'manage_announcements.php' ? 'active' : '' ?>">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.167H3.3a1.598 1.598 0 01-1.6-1.614V7.728c0-.856.66-1.571 1.516-1.614h1.76l2.147-6.167a1.76 1.76 0 013.417.592zM17.273 19.307l2.043 2.043m0-18.701l-2.043 2.043M22.02 12l-2.043-2.043M22.02 12l-2.043 2.043"></path></svg>
            จัดการประกาศ
        </a>
        
        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #334155;">
            <a href="../auth/logout.php" class="nav-link" style="color: #f87171;">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                ออกจากระบบ
            </a>
        </div>
    </nav>
</div>
