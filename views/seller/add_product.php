<?php $activePage = 'add_product'; $headerTitle = 'Add New Product'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>

<div class="dashboard-card">
    <div class="card-content" style="padding:24px;">
        <form method="POST" action="<?= base_url('seller/products/add') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group"><label>Product Name *</label><input name="name" class="form-control" required placeholder="e.g. Fresh Baby Spinach"></div>
                <div class="form-group"><label>Category *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" class="form-control" step="0.01" required placeholder="0.00"></div>
                <div class="form-group"><label>Sale Price (₹)</label><input type="number" name="sale_price" class="form-control" step="0.01" placeholder="Leave empty if no sale"></div>
                <div class="form-group"><label>Unit *</label>
                    <select name="unit" class="form-select"><option value="kg">Kilogram</option><option value="g">Gram</option><option value="piece">Piece</option><option value="bunch">Bunch</option><option value="dozen">Dozen</option><option value="pack">Pack</option></select>
                </div>
                <div class="form-group"><label>Stock *</label><input type="number" name="stock" class="form-control" required placeholder="Available quantity"></div>
            </div>
            <div class="form-group"><label>Short Description</label><input name="short_description" class="form-control" placeholder="Brief one-line description"></div>
            <div class="form-group"><label>Full Description</label><textarea name="description" class="form-control" rows="4" placeholder="Detailed product description..."></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group"><label>Product Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
                <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:28px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="is_organic" style="width:20px;height:20px;accent-color:var(--primary);">
                        <span>🌱 This is an organic product</span>
                    </label>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:12px;">
                <button type="submit" class="btn btn-primary btn-lg">🚀 Submit for Approval</button>
                <a href="<?= base_url('seller/products') ?>" class="btn btn-outline btn-lg">Cancel</a>
            </div>
            <p style="margin-top:12px;font-size:13px;color:var(--text-muted);">New products are now submitted as pending and require admin approval before they go live.</p>
        </form>
    </div>
</div>

<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
