<?php $activePage = 'products'; $headerTitle = 'My Products'; $headerAction = '<a href="' . base_url('seller/products/add') . '" class="btn btn-primary btn-sm">+ Add Product</a>'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>

<?php if (!empty($lowStockProducts)): ?>
<div class="dashboard-card" style="margin-bottom:20px;">
    <div class="card-title">⚠️ Low Stock Watchlist</div>
    <div class="card-content" style="padding:20px;">
        <?php foreach ($lowStockProducts as $product): ?>
        <div class="summary-line">
            <div>
                <strong><?= e($product['name']) ?></strong>
                <div class="summary-subtle"><?= ucfirst($product['status']) ?></div>
            </div>
            <span class="badge <?= (int)$product['stock'] > 0 ? 'badge-warning' : 'badge-danger' ?>"><?= (int)$product['stock'] ?> left</span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="dashboard-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No products yet. <a href="<?= base_url('seller/products/add') ?>">Add your first product!</a></td></tr>
                <?php else: foreach ($products as $p): ?>
                <tr>
                    <td><img src="<?= asset('uploads/products/' . ($p['image'] ?? 'placeholder.jpg')) ?>" style="width:50px;height:50px;border-radius:8px;object-fit:cover;background:var(--bg-alt);" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?w=60&h=60&fit=crop'"></td>
                    <td style="font-weight:600;"><?= e($p['name']) ?></td>
                    <td><?= e($p['category_name'] ?? '') ?></td>
                    <td style="font-weight:600;"><?= format_price($p['sale_price'] ?: $p['price']) ?></td>
                    <td><span class="badge <?= $p['stock'] > 10 ? 'badge-success' : ($p['stock'] > 0 ? 'badge-warning' : 'badge-danger') ?>"><?= $p['stock'] ?></span></td>
                    <td><span class="badge <?= $p['status'] === 'active' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="<?= base_url('seller/products/edit/' . $p['id']) ?>" class="btn btn-outline btn-sm">✏️</a>
                            <form method="POST" action="<?= base_url('seller/products/delete/' . $p['id']) ?>" onsubmit="return confirm('Delete this product?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm" style="color:var(--danger);border:1px solid var(--danger);background:none;">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
