<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('เฉพาะผู้ดูแลระบบ');
}

function countRow($conn, $sql) {
    $q = mysqli_query($conn, $sql);
    if (!$q) return 0;
    $r = mysqli_fetch_assoc($q);
    return $r ? (int)$r['c'] : 0;
}

// Stats
$totalUsers     = countRow($conn, "SELECT COUNT(*) AS c FROM users");
$totalWallets   = countRow($conn, "SELECT COUNT(*) AS c FROM wallets");
$pendingListings = countRow($conn, "SELECT COUNT(*) AS c FROM carbon_listings WHERE status='pending'");
$pendingOrders = countRow($conn, "SELECT COUNT(*) AS c FROM orders WHERE status='pending_admin'");
$pendingTopups = countRow($conn, "SELECT COUNT(*) AS c FROM token_topups WHERE status='pending_admin'");
$pendingWithdraws = countRow($conn, "SELECT COUNT(*) AS c FROM withdraw_requests WHERE status='pending'");

// Chart Data: Sales Volume
$salesData = [];
$salesLabels = [];
$qSales = mysqli_query($conn, "
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, SUM(price) AS total 
    FROM orders 
    WHERE status = 'approved' 
    GROUP BY m 
    ORDER BY m ASC 
    LIMIT 6
");
while ($r = mysqli_fetch_assoc($qSales)) {
    $salesLabels[] = $r['m'];
    $salesData[] = $r['total'];
}

// Chart Data: Carbon Usage
$carbonData = [];
$carbonLabels = [];
$qCarbon = mysqli_query($conn, "
    SELECT DATE_FORMAT(o.created_at, '%Y-%m') AS m, SUM(l.carbon_amount) AS total 
    FROM orders o 
    JOIN carbon_listings l ON o.listing_id = l.id 
    WHERE o.status = 'approved' 
    GROUP BY m 
    ORDER BY m ASC 
    LIMIT 6
");
while ($r = mysqli_fetch_assoc($qCarbon)) {
    $carbonLabels[] = $r['m'];
    $carbonData[] = $r['total'];
}

// Add Province Sales Ranking Data Query
$topProvinces = [];
$totalListingsAll = countRow($conn, "SELECT COUNT(*) AS c FROM carbon_listings");

$qProvinceSales = mysqli_query($conn, "
    SELECT 
        l.province, 
        SUM(o.price) AS total_sales,
        COUNT(DISTINCT l.id) AS listing_count
    FROM orders o
    JOIN carbon_listings l ON o.listing_id = l.id
    WHERE o.status = 'approved' AND l.province IS NOT NULL AND l.province != ''
    GROUP BY l.province
    ORDER BY total_sales DESC
    LIMIT 10
");

$provincesWithListings = [];
$qProv = mysqli_query($conn, "SELECT DISTINCT province FROM carbon_listings WHERE province IS NOT NULL AND province != ''");
while ($p = mysqli_fetch_assoc($qProv)) {
    $provincesWithListings[] = $p['province'];
}

while ($r = mysqli_fetch_assoc($qProvinceSales)) {
    $topProvinces[] = $r;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <link rel="stylesheet" href="assets/admin_custom.css">
</head>
<body class="admin-body">

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>

    <main class="admin-main">
        <header class="page-header">
            <h1 class="page-title">แดชบอร์ดภาพรวมระบบ</h1>
            <p style="color: var(--admin-text-muted); margin-top: 0.5rem;">ติดตามสถิติและการอนุมัติรายการทั้งหมดในระบบ</p>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>ผู้ใช้ทั้งหมด</h3>
                    <p class="stat-value"><?= number_format($totalUsers) ?></p>
                </div>
                <div class="stat-icon icon-blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Wallet ทั้งระบบ</h3>
                    <p class="stat-value"><?= number_format($totalWallets) ?></p>
                </div>
                <div class="stat-icon icon-purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>รออนุมัติขาย</h3>
                    <p class="stat-value"><?= number_format($pendingListings) ?></p>
                </div>
                <div class="stat-icon icon-amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>รออนุมัติซื้อ</h3>
                    <p class="stat-value"><?= number_format($pendingOrders) ?></p>
                </div>
                <div class="stat-icon icon-red">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Charts Container -->
        <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="table-container" style="padding: 1.5rem;">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.125rem;">ยอดขายรวม (Token)</h3>
                <canvas id="salesChart" height="200"></canvas>
            </div>
            <div class="table-container" style="padding: 1.5rem;">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.125rem;">การใช้คาร์บอน (Ton)</h3>
                <canvas id="carbonChart" height="300"></canvas>
            </div>
        </div>

        <!-- Province Ranking Table -->
        <div class="table-container">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--admin-border); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.125rem;">🏆 10 อันดับจังหวัดยอดขายสูงสุด</h3>
                <div style="text-align: right;">
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted);">โครงการทั้งหมด: <strong><?= $totalListingsAll ?></strong></span>
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted); margin-left: 1rem;">จังหวัดที่มีโครงการ: <strong><?= count($provincesWithListings) ?></strong></span>
                </div>
            </div>
            
            <?php if (empty($topProvinces)): ?>
                <div style="padding: 4rem; text-align: center; color: var(--admin-text-muted);">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto 1rem; opacity: 0.3;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <p>ยังไม่มีข้อมูลยอดขายในระบบ</p>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 80px; text-align: center;">อันดับ</th>
                            <th>จังหวัด</th>
                            <th style="text-align: right;">รายการสะสม</th>
                            <th style="text-align: right;">ยอดขายรวม (Token)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProvinces as $index => $ranking): ?>
                            <tr>
                                <td style="text-align: center; font-weight: 600;">
                                    <?php 
                                        if($index == 0) echo '<span style="font-size: 1.25rem;">🥇</span>'; 
                                        elseif($index == 1) echo '<span style="font-size: 1.25rem;">🥈</span>'; 
                                        elseif($index == 2) echo '<span style="font-size: 1.25rem;">🥉</span>'; 
                                        else echo ($index + 1); 
                                    ?>
                                </td>
                                <td style="font-weight: 500;"><?= htmlspecialchars($ranking['province'] ?? 'ไม่ระบุ') ?></td>
                                <td style="text-align: right; color: var(--admin-text-muted);"><?= number_format($ranking['listing_count']) ?></td>
                                <td style="text-align: right; font-weight: 700; color: var(--admin-success);"><?= number_format($ranking['total_sales'], 2) ?> CC</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($salesLabels) ?>,
                datasets: [{
                    label: 'ยอดขาย (Token)',
                    data: <?= json_encode($salesData) ?>,
                    backgroundColor: '#4f46e5',
                    borderRadius: 8,
                    hoverBackgroundColor: '#4338ca'
                }]
            },
            options: { 
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                    x: { grid: { display: false } }
                }
            }
        });

        const carbonCtx = document.getElementById('carbonChart').getContext('2d');
        new Chart(carbonCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($carbonLabels) ?>,
                datasets: [{
                    label: 'การใช้คาร์บอน (Ton)',
                    data: <?= json_encode($carbonData) ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4
                }]
            },
            options: { 
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                    x: { grid: { display: false } }
                }
            }
        });
        </script>
    </main>
</div>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
