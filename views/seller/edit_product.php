<?php $activePage = 'products'; $headerTitle = 'Edit Product'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>
<div class="dashboard-card">
    <div class="card-content" style="padding:24px;">
        <form method="POST" action="<?= base_url('seller/products/edit/' . $product['id']) ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group"><label>Product Name *</label><input name="name" class="form-control" value="<?= e($product['name']) ?>" required></div>
                <div class="form-group"><label>Category *</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" class="form-control" step="0.01" value="<?= $product['price'] ?>" required></div>
                <div class="form-group"><label>Sale Price (₹)</label><input type="number" name="sale_price" class="form-control" step="0.01" value="<?= $product['sale_price'] ?>"></div>
                <div class="form-group"><label>Unit *</label>
                    <select name="unit" class="form-select">
                        <?php foreach(['kg','g','piece','bunch','dozen','pack'] as $u): ?>
                        <option value="<?= $u ?>" <?= $u === $product['unit'] ? 'selected' : '' ?>><?= ucfirst($u) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Stock *</label><input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required></div>
            </div>
            <div class="form-group"><label>Short Description</label><input name="short_description" class="form-control" value="<?= e($product['short_description'] ?? '') ?>"></div>
            <div class="form-group"><label>Full Description</label><textarea name="description" class="form-control" rows="4"><?= e($product['description'] ?? '') ?></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group">
                    <label>Product Image</label>
                    <?php if ($product['image']): ?><img src="<?= asset('uploads/products/' . $product['image']) ?>" style="width:80px;height:80px;border-radius:8px;object-fit:cover;margin-bottom:8px;" onerror="this.style.display='none'"><?php endif; ?>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="form-group"><label>Status</label>
                    <select name="status" class="form-select">
                        <?php if ($product['status'] === 'active'): ?>
                        <option value="active" selected>Active</option>
                        <?php endif; ?>
                        <?php if ($product['status'] === 'pending'): ?>
                        <option value="pending" selected>Pending Approval</option>
                        <?php endif; ?>
                        <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <div style="margin-top:8px;font-size:12px;color:var(--text-muted);">Sellers can pause products, but only admins can approve products for live listing.</div>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:12px;">
                        <input type="checkbox" name="is_organic" <?= $product['is_organic'] ? 'checked' : '' ?> style="width:20px;height:20px;accent-color:var(--primary);">
                        <span>🌱 Organic</span>
                    </label>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:12px;">
                <button type="submit" class="btn btn-primary btn-lg">💾 Save Changes</button>
                <a href="<?= base_url('seller/products') ?>" class="btn btn-outline btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
