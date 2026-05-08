<?php $activePage = 'dashboard'; $headerTitle = 'Seller Dashboard'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">💰</div>
        <div class="stat-info">
            <div class="stat-value"><?= format_price($stats['total_revenue'] ?? 0) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">📦</div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_orders'] ?? 0 ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">🥬</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalProducts ?></div>
            <div class="stat-label">Products</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">⏳</div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['pending_orders'] ?? 0 ?></div>
            <div class="stat-label">Pending Orders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">⭐</div>
        <div class="stat-info">
            <div class="stat-value"><?= number_format((float)($sellerRating['avg_rating'] ?? 0), 1) ?></div>
            <div class="stat-label">Vendor Rating (<?= (int)($sellerRating['total_reviews'] ?? 0) ?>)</div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-title">
            <span>📋 Recent Orders</span>
            <a href="<?= base_url('seller/orders') ?>" style="font-size:14px;font-weight:600;">View All →</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">No orders yet</td></tr>
                    <?php else: foreach ($recentOrders as $order): ?>
                    <tr onclick="window.location='<?= base_url('seller/orders/' . $order['id']) ?>'" style="cursor:pointer;">
                        <td style="font-weight:600;color:var(--primary);">#<?= e($order['order_number']) ?></td>
                        <td><?= e($order['buyer_name'] ?? 'N/A') ?></td>
                        <td style="font-weight:600;"><?= format_price($order['total']) ?></td>
                        <td>
                            <?php $badges = ['pending'=>'badge-warning','confirmed'=>'badge-info','shipped'=>'badge-primary','delivered'=>'badge-success','cancelled'=>'badge-danger']; ?>
                            <span class="badge <?= $badges[$order['status']] ?? '' ?>"><?= ucfirst($order['status']) ?></span>
                        </td>
                        <td style="color:var(--text-muted);"><?= date('M d', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <div class="dashboard-card" style="margin-bottom:20px;">
            <div class="card-title">📈 Recent Earnings</div>
            <div class="card-content" style="padding:20px;">
                <?php $maxRevenue = 0; foreach ($monthlyRevenue as $row) { $maxRevenue = max($maxRevenue, (float)$row['revenue']); } ?>
                <?php if (empty($monthlyRevenue)): ?>
                <p style="color:var(--text-muted);margin:0;">No paid sales recorded yet.</p>
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
            <div class="card-title">⚠️ Low Stock Alerts</div>
            <div class="card-content" style="padding:20px;">
                <?php if (empty($lowStockProducts)): ?>
                <p style="color:var(--text-muted);margin:0;">No low-stock products right now.</p>
                <?php else: foreach ($lowStockProducts as $product): ?>
                <div class="summary-line">
                    <div>
                        <strong><?= e($product['name']) ?></strong>
                        <div class="summary-subtle"><?= ucfirst($product['status']) ?></div>
                    </div>
                    <span class="badge <?= (int)$product['stock'] > 0 ? 'badge-warning' : 'badge-danger' ?>"><?= (int)$product['stock'] ?> left</span>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-title">🥬 Best-Selling Products</div>
    <div class="card-content" style="padding:20px;">
        <?php if (empty($topProducts)): ?>
        <p style="color:var(--text-muted);margin:0;">No product sales yet.</p>
        <?php else: foreach ($topProducts as $product): ?>
        <div class="summary-line">
            <div>
                <strong><?= e($product['name']) ?></strong>
                <div class="summary-subtle"><?= (int)$product['units_sold'] ?> units sold</div>
            </div>
            <div style="text-align:right;">
                <div style="font-weight:700;"><?= format_price($product['revenue']) ?></div>
                <div class="summary-subtle">Stock: <?= (int)$product['stock'] ?></div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
