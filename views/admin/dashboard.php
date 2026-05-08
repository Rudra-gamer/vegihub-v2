<?php $activePage = 'dashboard'; $headerTitle = 'Admin Dashboard'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon green">💰</div><div class="stat-info"><div class="stat-value"><?= format_price($orderStats['total_revenue'] ?? 0) ?></div><div class="stat-label">Total Revenue</div></div></div>
    <div class="stat-card"><div class="stat-icon blue">📦</div><div class="stat-info"><div class="stat-value"><?= $orderStats['total_orders'] ?? 0 ?></div><div class="stat-label">Total Orders</div></div></div>
    <div class="stat-card"><div class="stat-icon purple">👥</div><div class="stat-info"><div class="stat-value"><?= array_sum(array_column($userStats, 'count')) ?></div><div class="stat-label">Total Users</div></div></div>
    <div class="stat-card"><div class="stat-icon orange">🥬</div><div class="stat-info"><div class="stat-value"><?= $totalProducts ?></div><div class="stat-label">Products</div></div></div>
    <div class="stat-card"><div class="stat-icon red">⏳</div><div class="stat-info"><div class="stat-value"><?= $pendingPaymentSummary['pending_orders'] ?? 0 ?></div><div class="stat-label">Pending Payments</div></div></div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-title"><span>📋 Recent Orders</span><a href="<?= base_url('admin/orders') ?>" style="font-size:14px;">View All →</a></div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($recentOrders as $o): ?>
                    <tr style="cursor:pointer;" onclick="window.location='<?= base_url('admin/orders/' . $o['id']) ?>'">
                        <td style="font-weight:600;color:var(--primary);">#<?= e($o['order_number']) ?></td>
                        <td><?= e($o['buyer_name'] ?? '') ?></td>
                        <td style="font-weight:600;"><?= format_price($o['total']) ?></td>
                        <td><span class="badge <?= $o['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($o['payment_status']) ?></span></td>
                        <td><span class="badge <?= ['pending'=>'badge-warning','confirmed'=>'badge-info','shipped'=>'badge-primary','delivered'=>'badge-success','cancelled'=>'badge-danger'][$o['status']] ?? '' ?>"><?= ucfirst($o['status']) ?></span></td>
                        <td style="color:var(--text-muted);"><?= date('M d', strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <div class="dashboard-card" style="margin-bottom:20px;">
            <div class="card-title">📈 Monthly Revenue</div>
            <div class="card-content" style="padding:20px;">
                <?php $maxRevenue = 0; foreach ($monthlyRevenue as $row) { $maxRevenue = max($maxRevenue, (float)$row['revenue']); } ?>
                <?php if (empty($monthlyRevenue)): ?>
                <p style="color:var(--text-muted);margin:0;">No paid orders yet.</p>
                <?php else: foreach ($monthlyRevenue as $row): ?>
                <div class="mini-chart-row">
                    <div class="mini-chart-label"><?= e($row['revenue_label']) ?></div>
                    <div class="mini-chart-track"><span style="width:<?= $maxRevenue > 0 ? max(8, round(($row['revenue'] / $maxRevenue) * 100)) : 8 ?>%"></span></div>
                    <div class="mini-chart-value"><?= format_price($row['revenue']) ?></div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <div class="dashboard-card" style="margin-bottom:20px;">
            <div class="card-title">👥 User Breakdown</div>
            <div class="card-content" style="padding:20px;">
                <?php foreach ($userStats as $s): ?>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-light);">
                    <span><?= ['buyer'=>'🛒 Buyers','seller'=>'🏪 Sellers','admin'=>'🛡️ Admins'][$s['role']] ?? $s['role'] ?></span>
                    <strong><?= $s['count'] ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="dashboard-card">
            <div class="card-title">📊 Order Stats</div>
            <div class="card-content" style="padding:20px;">
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-light);"><span>⏳ Pending</span><strong><?= $orderStats['pending_orders'] ?? 0 ?></strong></div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-light);"><span>✅ Confirmed</span><strong><?= $orderStats['confirmed_orders'] ?? 0 ?></strong></div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-light);"><span>🚚 Shipped</span><strong><?= $orderStats['shipped_orders'] ?? 0 ?></strong></div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-light);"><span>🏠 Delivered</span><strong><?= $orderStats['delivered_orders'] ?? 0 ?></strong></div>
                <div style="display:flex;justify-content:space-between;padding:10px 0;"><span>💵 Pending amount</span><strong><?= format_price($pendingPaymentSummary['pending_amount'] ?? 0) ?></strong></div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-title">🏪 Top Vendors</div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Vendor</th><th>Orders</th><th>Units</th><th>Revenue</th></tr></thead>
                <tbody>
                    <?php if (empty($topVendors)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted);">No vendor sales yet</td></tr>
                    <?php else: foreach ($topVendors as $vendor): ?>
                    <tr>
                        <td style="font-weight:600;"><?= e($vendor['name']) ?></td>
                        <td><?= (int)$vendor['orders_count'] ?></td>
                        <td><?= (int)$vendor['delivered_units'] ?></td>
                        <td style="font-weight:700;"><?= format_price($vendor['revenue']) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="dashboard-card">
        <div class="card-title">🥬 Top Products</div>
        <div class="card-content" style="padding:20px;">
            <?php if (empty($topProducts)): ?>
            <p style="color:var(--text-muted);margin:0;">No product sales yet.</p>
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
