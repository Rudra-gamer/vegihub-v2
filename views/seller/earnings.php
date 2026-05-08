<?php $activePage = 'earnings'; $headerTitle = 'Earnings Overview'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon green">💰</div><div class="stat-info"><div class="stat-value"><?= format_price($stats['total_revenue'] ?? 0) ?></div><div class="stat-label">Total Revenue</div></div></div>
    <div class="stat-card"><div class="stat-icon blue">📦</div><div class="stat-info"><div class="stat-value"><?= $stats['total_orders'] ?? 0 ?></div><div class="stat-label">Total Orders</div></div></div>
    <div class="stat-card"><div class="stat-icon orange">✅</div><div class="stat-info"><div class="stat-value"><?= $stats['delivered_orders'] ?? 0 ?></div><div class="stat-label">Delivered</div></div></div>
    <div class="stat-card"><div class="stat-icon red">⏳</div><div class="stat-info"><div class="stat-value"><?= $stats['pending_orders'] ?? 0 ?></div><div class="stat-label">Pending</div></div></div>
</div>
<div class="dashboard-card">
    <div class="card-title">💡 Earnings Info</div>
    <div class="card-content" style="padding:24px;">
        <p style="color:var(--text-secondary);line-height:1.8;">
            Your earnings are calculated based on completed orders. A platform commission of <?= env('COMMISSION_RATE', 10) ?>% is deducted from each order.<br>
            <strong>Estimated Net Earnings:</strong> <span style="font-size:24px;font-weight:800;color:var(--primary);"><?= format_price(($stats['total_revenue'] ?? 0) * (1 - (int)env('COMMISSION_RATE', 10) / 100)) ?></span>
        </p>
    </div>
</div>
<div class="dashboard-grid" style="margin-top:24px;">
    <div class="dashboard-card">
        <div class="card-title">📈 Paid Revenue Trend</div>
        <div class="card-content" style="padding:20px;">
            <?php $maxRevenue = 0; foreach ($monthlyRevenue as $row) { $maxRevenue = max($maxRevenue, (float)$row['revenue']); } ?>
            <?php if (empty($monthlyRevenue)): ?>
            <p style="color:var(--text-muted);margin:0;">No paid sales yet.</p>
            <?php else: foreach ($monthlyRevenue as $row): ?>
            <div class="mini-chart-row">
                <div class="mini-chart-label"><?= e($row['revenue_label']) ?></div>
                <div class="mini-chart-track"><span style="width:<?= $maxRevenue > 0 ? max(8, round(($row['revenue'] / $maxRevenue) * 100)) : 8 ?>%"></span></div>
                <div class="mini-chart-value"><?= format_price($row['revenue']) ?></div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
    <div class="dashboard-card">
        <div class="card-title">🥇 Top Earning Products</div>
        <div class="card-content" style="padding:20px;">
            <?php if (empty($topProducts)): ?>
            <p style="color:var(--text-muted);margin:0;">No product earnings yet.</p>
            <?php else: foreach ($topProducts as $product): ?>
            <div class="summary-line">
                <div>
                    <strong><?= e($product['name']) ?></strong>
                    <div class="summary-subtle"><?= (int)$product['units_sold'] ?> units sold</div>
                </div>
                <div style="font-weight:700;"><?= format_price($product['revenue']) ?></div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
