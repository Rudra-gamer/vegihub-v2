<?php include VIEW_PATH . '/layouts/header.php'; ?>
<div class="container page-shell">
    <section class="editorial-hero">
        <span class="eyebrow">Order History</span>
        <h1>Track every order with clearer status and totals.</h1>
        <p>Your orders now sit in a cleaner history view so it is easier to filter, review payment state, and jump into order details.</p>
    </section>
    
    <div class="order-panel">
    <div class="order-filter-row">
        <?php $statuses = ['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled']; ?>
        <?php foreach ($statuses as $val => $label): ?>
        <a href="<?= base_url('orders' . ($val ? '?status=' . $val : '')) ?>" class="btn btn-sm <?= ($statusFilter ?? '') === $val ? 'btn-primary' : 'btn-outline' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($orders)): ?>
    <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h3>No orders yet</h3>
        <p>Start shopping and your orders will appear here.</p>
        <a href="<?= base_url('products') ?>" class="btn btn-primary">Start Shopping</a>
    </div>
    <?php else: ?>
    <?php foreach ($orders as $order): ?>
    <div class="order-card-modern mb-3" onclick="window.location='<?= base_url('orders/' . $order['id']) ?>'">
            <div>
                <div style="font-weight:700;font-size:16px;color:var(--primary);">#<?= e($order['order_number']) ?></div>
                <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">Placed on <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></div>
                <div style="font-size:14px;margin-top:4px;"><?= $order['item_count'] ?> item(s)</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:20px;font-weight:800;color:var(--primary-dark);"><?= format_price($order['total']) ?></div>
                <?php 
                $statusBadges = ['pending'=>'badge-warning','confirmed'=>'badge-info','processing'=>'badge-info','shipped'=>'badge-primary','delivered'=>'badge-success','cancelled'=>'badge-danger'];
                ?>
                <span class="badge <?= $statusBadges[$order['status']] ?? 'badge-primary' ?>" style="margin-top:6px;"><?= ucfirst($order['status']) ?></span>
            </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
