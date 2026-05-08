<?php $headerTitle = 'Categories'; $headerAction = '<button class="btn btn-primary btn-sm" onclick="document.getElementById(\'add-cat-form\').style.display=document.getElementById(\'add-cat-form\').style.display===\'none\'?\'block\':\'none\'">+ Add Category</button>'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>

<div id="add-cat-form" class="dashboard-card mb-3" style="display:none;">
    <div class="card-title">Add Category</div>
    <div class="card-content" style="padding:20px;">
        <form method="POST" action="<?= base_url('admin/categories/add') ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
            <?= csrf_field() ?>
            <div class="form-group" style="flex:1;min-width:200px;"><label>Name</label><input name="name" class="form-control" required></div>
            <div class="form-group" style="width:80px;"><label>Icon</label><input name="icon" class="form-control" placeholder="🥬" value="🥬"></div>
            <div class="form-group" style="flex:1;min-width:200px;"><label>Description</label><input name="description" class="form-control"></div>
            <div class="form-group" style="width:80px;"><label>Order</label><input name="sort_order" type="number" class="form-control" value="0"></div>
            <button type="submit" class="btn btn-primary" style="height:44px;">Add</button>
        </form>
    </div>
</div>

<div class="dashboard-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Icon</th><th>Name</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td style="font-size:24px;"><?= $cat['icon'] ?? '🥬' ?></td>
                    <td style="font-weight:600;"><?= e($cat['name']) ?></td>
                    <td><?= $cat['product_count'] ?? 0 ?></td>
                    <td><span class="badge <?= $cat['is_active'] ? 'badge-success' : 'badge-warning' ?>"><?= $cat['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <form method="POST" action="<?= base_url('admin/categories/delete/'.$cat['id']) ?>" onsubmit="return confirm('Delete this category?')">
                            <?= csrf_field() ?><button class="btn btn-sm" style="color:var(--danger);border:1px solid var(--danger);background:none;">🗑️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
