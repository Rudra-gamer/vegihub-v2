<?php $headerTitle = 'Order #' . e($order['order_number']); include VIEW_PATH . '/layouts/admin_layout.php'; ?>
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card"><div class="stat-icon blue">📦</div><div class="stat-info"><div class="stat-value">#<?= e($order['order_number']) ?></div><div class="stat-label">Order Number</div></div></div>
    <div class="stat-card"><div class="stat-icon green">💰</div><div class="stat-info"><div class="stat-value"><?= format_price($order['total']) ?></div><div class="stat-label">Total Amount</div></div></div>
    <div class="stat-card"><div class="stat-icon orange">📋</div><div class="stat-info"><div class="stat-value"><?= ucfirst($order['status']) ?></div><div class="stat-label">Status</div></div></div>
    <div class="stat-card"><div class="stat-icon purple">💳</div><div class="stat-info"><div class="stat-value"><?= ucfirst($order['payment_status']) ?></div><div class="stat-label">Payment</div></div></div>
</div>
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
    <div class="dashboard-card">
        <div class="card-title">📦 Items</div>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($order['items'] as $item): ?>
                    <tr>
                        <td style="font-weight:600;"><?= e($item['product_name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= format_price($item['price']) ?></td>
                        <td style="font-weight:600;"><?= format_price($item['total']) ?></td>
                        <td><span class="badge <?= ['pending'=>'badge-warning','confirmed'=>'badge-info','shipped'=>'badge-primary','delivered'=>'badge-success','cancelled'=>'badge-danger'][$item['status']] ?? '' ?>"><?= ucfirst($item['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <div class="dashboard-card" style="margin-bottom:20px;">
            <div class="card-title">👤 Customer</div>
            <div class="card-content" style="padding:20px;">
                <p><strong><?= e($buyer['name'] ?? '') ?></strong></p>
                <p style="color:var(--text-secondary);"><?= e($buyer['email'] ?? '') ?></p>
                <p style="color:var(--text-secondary);"><?= e($buyer['phone'] ?? '') ?></p>
            </div>
        </div>
        <div class="dashboard-card">
            <div class="card-title">💵 Payment Details</div>
            <div class="card-content" style="padding:20px;">
                <p>Subtotal: <?= format_price($order['subtotal']) ?></p>
                <?php if ($order['discount'] > 0): ?><p style="color:var(--success);">Discount: -<?= format_price($order['discount']) ?></p><?php endif; ?>
                <p>Delivery: <?= $order['delivery_fee'] > 0 ? format_price($order['delivery_fee']) : 'FREE' ?></p>
                <p style="font-weight:700;font-size:18px;border-top:2px solid var(--border);padding-top:10px;">Total: <?= format_price($order['total']) ?></p>
                <p style="margin-top:8px;">Method: <?= ucfirst($order['payment_method']) ?></p>
                <p>Payment Status: <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($order['payment_status']) ?></span></p>
                <p>Placed: <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
                <?php if (($order['payment_method'] ?? '') === 'cod' && ($order['payment_status'] ?? '') !== 'paid' && ($order['status'] ?? '') !== 'cancelled'): ?>
                <form method="POST" action="<?= base_url('admin/orders/' . $order['id'] . '/mark-cod-paid') ?>" style="margin-top:16px;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Confirm that COD payment has been collected for this order?')">Mark COD Paid</button>
                </form>
                <p style="font-size:12px;color:var(--text-muted);margin-top:8px;">Sellers cannot complete COD delivery until this is confirmed by admin.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
