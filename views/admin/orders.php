<?php $headerTitle = 'All Orders'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>
<div style="margin-bottom:20px;display:flex;gap:8px;flex-wrap:wrap;">
    <?php foreach(['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $val => $label): ?>
    <a href="<?= base_url('admin/orders' . ($val ? '?status='.$val : '')) ?>" class="btn btn-sm <?= ($statusFilter ?? '') === $val ? 'btn-primary' : 'btn-outline' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>
<div class="dashboard-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr style="cursor:pointer;" onclick="window.location='<?= base_url('admin/orders/' . $o['id']) ?>'">
                    <td style="font-weight:600;color:var(--primary);">#<?= e($o['order_number']) ?></td>
                    <td><?= e($o['buyer_name'] ?? '') ?></td>
                    <td style="font-weight:600;"><?= format_price($o['total']) ?></td>
                    <td><span class="badge <?= $o['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($o['payment_status']) ?></span></td>
                    <td><span class="badge <?= ['pending'=>'badge-warning','confirmed'=>'badge-info','shipped'=>'badge-primary','delivered'=>'badge-success','cancelled'=>'badge-danger'][$o['status']] ?? '' ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td style="color:var(--text-muted);"><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
