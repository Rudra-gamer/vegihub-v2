<?php $headerTitle = 'Coupons'; $headerAction = '<button class="btn btn-primary btn-sm" onclick="document.getElementById(\'add-coupon-form\').style.display=document.getElementById(\'add-coupon-form\').style.display===\'none\'?\'block\':\'none\'">+ Add Coupon</button>'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>

<div id="add-coupon-form" class="dashboard-card mb-3" style="display:none;">
    <div class="card-title">Create Coupon</div>
    <div class="card-content" style="padding:20px;">
        <form method="POST" action="<?= base_url('admin/coupons/add') ?>">
            <?= csrf_field() ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;">
                <div class="form-group"><label>Code</label><input name="code" class="form-control" required placeholder="e.g. WELCOME10" style="text-transform:uppercase;"></div>
                <div class="form-group"><label>Type</label><select name="type" class="form-select"><option value="percentage">Percentage</option><option value="fixed">Fixed Amount</option></select></div>
                <div class="form-group"><label>Value</label><input name="value" type="number" class="form-control" step="0.01" required placeholder="e.g. 10"></div>
                <div class="form-group"><label>Min Order (₹)</label><input name="min_order" type="number" class="form-control" value="0"></div>
                <div class="form-group"><label>Max Discount (₹)</label><input name="max_discount" type="number" class="form-control" placeholder="Optional"></div>
                <div class="form-group"><label>Usage Limit</label><input name="usage_limit" type="number" class="form-control" value="100"></div>
                <div class="form-group"><label>Start Date</label><input name="start_date" type="date" class="form-control" required></div>
                <div class="form-group"><label>End Date</label><input name="end_date" type="date" class="form-control" required></div>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Create Coupon</button>
        </form>
    </div>
</div>

<div class="dashboard-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Min Order</th><th>Used</th><th>Status</th><th>Expires</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($coupons as $c): ?>
                <tr>
                    <td style="font-weight:700;font-family:monospace;letter-spacing:1px;"><?= e($c['code']) ?></td>
                    <td><?= ucfirst($c['type']) ?></td>
                    <td style="font-weight:600;"><?= $c['type'] === 'percentage' ? $c['value'] . '%' : format_price($c['value']) ?></td>
                    <td><?= format_price($c['min_order']) ?></td>
                    <td><?= $c['used_count'] ?>/<?= $c['usage_limit'] ?></td>
                    <td><span class="badge <?= $c['is_active'] ? 'badge-success' : 'badge-warning' ?>"><?= $c['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td style="font-size:13px;color:var(--text-muted);"><?= date('M d, Y', strtotime($c['end_date'])) ?></td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <form method="POST" action="<?= base_url('admin/coupons/toggle/'.$c['id']) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline"><?= $c['is_active'] ? '🔴' : '🟢' ?></button></form>
                            <form method="POST" action="<?= base_url('admin/coupons/delete/'.$c['id']) ?>" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button class="btn btn-sm" style="color:var(--danger);border:1px solid var(--danger);background:none;">🗑️</button></form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
