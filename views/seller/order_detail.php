<?php $activePage = 'orders'; $headerTitle = 'Order #' . e($order['order_number']); include VIEW_PATH . '/layouts/admin_layout.php'; ?>
<div class="dashboard-card mb-3">
    <div class="card-title">📦 Order Items</div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td style="font-weight:600;"><?= e($item['product_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= format_price($item['price']) ?></td>
                    <td style="font-weight:600;"><?= format_price($item['total']) ?></td>
                    <td><span class="badge <?= ['pending'=>'badge-warning','confirmed'=>'badge-info','shipped'=>'badge-primary','delivered'=>'badge-success','cancelled'=>'badge-danger'][$item['status']] ?? '' ?>"><?= ucfirst($item['status']) ?></span></td>
                    <td>
                        <?php if ($item['status'] !== 'delivered' && $item['status'] !== 'cancelled'): ?>
                        <form method="POST" action="<?= base_url('seller/orders/update-status') ?>" style="display:flex;gap:6px;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <select name="status" class="form-select" style="padding:6px;font-size:13px;width:auto;">
                                <?php if ($item['status'] === 'pending'): ?>
                                <option value="confirmed">Confirm</option>
                                <?php elseif ($item['status'] === 'confirmed'): ?>
                                <option value="shipped">Mark as Shipped</option>
                                <?php elseif ($item['status'] === 'shipped'): ?>
                                <?php if (($order['payment_method'] ?? '') === 'cod' && ($order['payment_status'] ?? '') !== 'paid'): ?>
                                <option value="" disabled>Waiting for COD payment confirmation</option>
                                <?php else: ?>
                                <option value="delivered">Mark as Delivered</option>
                                <?php endif; ?>
                                <?php endif; ?>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm" <?= (($item['status'] === 'shipped') && (($order['payment_method'] ?? '') === 'cod') && (($order['payment_status'] ?? '') !== 'paid')) ? 'disabled' : '' ?>>Update</button>
                        </form>
                        <?php if (($item['status'] === 'shipped') && (($order['payment_method'] ?? '') === 'cod') && (($order['payment_status'] ?? '') !== 'paid')): ?>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:6px;">Admin must confirm COD payment before delivery can be completed.</div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="dashboard-card">
        <div class="card-title">💵 Payment</div>
        <div class="card-content" style="padding:20px;">
            <p><strong>Method:</strong> <?= ucfirst($order['payment_method']) ?></p>
            <p><strong>Status:</strong> <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($order['payment_status']) ?></span></p>
            <p><strong>Total:</strong> <?= format_price($order['total']) ?></p>
        </div>
    </div>
    <div class="dashboard-card">
        <div class="card-title">📅 Info</div>
        <div class="card-content" style="padding:20px;">
            <p><strong>Order Date:</strong> <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
            <p><strong>Order Status:</strong> <span class="badge <?= ['pending'=>'badge-warning','confirmed'=>'badge-info','shipped'=>'badge-primary','delivered'=>'badge-success','cancelled'=>'badge-danger'][$order['status']] ?? '' ?>"><?= ucfirst($order['status']) ?></span></p>
        </div>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
