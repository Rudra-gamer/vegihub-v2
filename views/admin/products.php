<?php $headerTitle = 'Products Management'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>
<div style="margin-bottom:20px;display:flex;gap:8px;flex-wrap:wrap;">
    <?php foreach(['' => 'All', 'active' => 'Active', 'inactive' => 'Inactive', 'pending' => 'Pending'] as $val => $label): ?>
    <a href="<?= base_url('admin/products' . ($val ? '?status='.$val : '')) ?>" class="btn btn-sm <?= ($statusFilter ?? '') === $val ? 'btn-primary' : 'btn-outline' ?>"><?= $label ?></a>
    <?php endforeach; ?>
</div>
<div class="dashboard-card" style="margin-bottom:20px;">
    <div class="card-title">🏷️ Bulk Discount</div>
    <div style="padding:20px;">
        <form method="POST" action="<?= base_url('admin/products/bulk-discount') ?>" style="display:grid;grid-template-columns:1.1fr 1fr 1fr auto;gap:12px;align-items:end;">
            <?= csrf_field() ?>
            <div class="form-group" style="margin:0;">
                <label>Discount Type</label>
                <select name="discount_type" class="form-control" required>
                    <option value="percentage">Percentage (%)</option>
                    <option value="fixed">Fixed amount (₹)</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label>Discount Value</label>
                <input type="number" name="discount_value" class="form-control" min="0.01" step="0.01" required placeholder="e.g. 10">
            </div>
            <div class="form-group" style="margin:0;">
                <label>Apply To</label>
                <select name="status_scope" class="form-control">
                    <option value="all">All products</option>
                    <option value="active">Active only</option>
                    <option value="inactive">Inactive only</option>
                    <option value="pending">Pending only</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>

        <form method="POST" action="<?= base_url('admin/products/clear-discount') ?>" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;margin-top:16px;">
            <?= csrf_field() ?>
            <div class="form-group" style="margin:0;min-width:220px;">
                <label>Clear Sale Price From</label>
                <select name="status_scope" class="form-control">
                    <option value="all">All products</option>
                    <option value="active">Active only</option>
                    <option value="inactive">Inactive only</option>
                    <option value="pending">Pending only</option>
                </select>
            </div>
            <button type="submit" class="btn btn-outline" onclick="return confirm('Clear sale prices for the selected products?')">Clear Discounts</button>
        </form>
    </div>
</div>
<div class="dashboard-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Image</th><th>Name</th><th>Price</th><th>Sale Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><img src="<?= asset('uploads/products/' . ($p['image'] ?? 'placeholder.jpg')) ?>" style="width:45px;height:45px;border-radius:8px;object-fit:cover;background:var(--bg-alt);" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?w=50&h=50&fit=crop'"></td>
                    <td style="font-weight:600;"><?= e($p['name']) ?></td>
                    <td style="font-weight:600;"><?= format_price($p['price']) ?></td>
                    <td style="font-weight:600;<?= $p['sale_price'] ? 'color:var(--success);' : 'color:var(--text-muted);' ?>"><?= $p['sale_price'] ? format_price($p['sale_price']) : 'No sale' ?></td>
                    <td><?= $p['stock'] ?></td>
                    <td><span class="badge <?= $p['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <?php if ($p['status'] !== 'active'): ?>
                            <form method="POST" action="<?= base_url('admin/products/approve/'.$p['id']) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline" title="Approve">✅</button></form>
                            <?php else: ?>
                            <form method="POST" action="<?= base_url('admin/products/reject/'.$p['id']) ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline" title="Deactivate">🚫</button></form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
