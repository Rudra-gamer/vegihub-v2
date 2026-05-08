<?php include VIEW_PATH . '/layouts/header.php'; ?>
<div class="container page-shell">
    <section class="editorial-hero">
        <span class="eyebrow">Delivery Setup</span>
        <h1>Keep your addresses organized and checkout-ready.</h1>
        <p>Store home, work, or alternate delivery details so your order flow stays quick and accurate on every purchase.</p>
    </section>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;gap:16px;flex-wrap:wrap;">
        <h1 class="section-title" style="margin:0;">My Addresses</h1>
        <button class="btn btn-primary" onclick="document.getElementById('add-address-form').style.display=document.getElementById('add-address-form').style.display==='none'?'block':'none'">+ Add Address</button>
    </div>

    <div id="add-address-form" class="content-panel mb-3" style="display:none;">
        <div class="card-header">Add New Address</div>
        <div class="card-body">
            <form method="POST" action="<?= base_url('profile/addresses/add') ?>">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-group"><label>Full Name</label><input name="full_name" class="form-control" required></div>
                    <div class="form-group"><label>Phone</label><input name="phone" class="form-control" required></div>
                </div>
                <div class="form-group"><label>Address Line 1</label><input name="address_line1" class="form-control" required></div>
                <div class="form-group"><label>Address Line 2</label><input name="address_line2" class="form-control"></div>
                <div class="form-row">
                    <div class="form-group"><label>City</label><input name="city" class="form-control" required></div>
                    <div class="form-group"><label>State</label><input name="state" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Pincode</label><input name="pincode" class="form-control" required maxlength="6"></div>
                    <div class="form-group"><label>Label</label><select name="label" class="form-select"><option>Home</option><option>Work</option><option>Other</option></select></div>
                </div>
                <button type="submit" class="btn btn-primary">Save Address</button>
            </form>
        </div>
    </div>

    <?php if (empty($addresses)): ?>
    <div class="empty-state">
        <div class="empty-icon">📍</div>
        <h3>No addresses saved</h3>
        <p>Add a delivery address to get started.</p>
    </div>
    <?php else: ?>
    <div class="address-grid">
        <?php foreach ($addresses as $addr): ?>
        <div class="address-card-modern">
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                    <strong><?= e($addr['full_name']) ?></strong>
                    <span class="badge <?= $addr['is_default'] ? 'badge-primary' : 'badge-info' ?>"><?= $addr['is_default'] ? '⭐ Default' : e($addr['label'] ?? 'Home') ?></span>
                </div>
                <div style="font-size:14px;color:var(--text-secondary);line-height:1.7;">
                    <?= e($addr['address_line1']) ?><?= $addr['address_line2'] ? ', ' . e($addr['address_line2']) : '' ?><br>
                    <?= e($addr['city']) ?>, <?= e($addr['state']) ?> - <?= e($addr['pincode']) ?><br>
                    📞 <?= e($addr['phone']) ?>
                </div>
                <div style="display:flex;gap:8px;margin-top:12px;">
                    <?php if (!$addr['is_default']): ?>
                    <form method="POST" action="<?= base_url('profile/addresses/default') ?>">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $addr['id'] ?>">
                        <button type="submit" class="btn btn-outline btn-sm">Set Default</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" action="<?= base_url('profile/addresses/delete') ?>" onsubmit="return confirm('Delete this address?')">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $addr['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="color:var(--danger);background:none;border:1px solid var(--danger);">Delete</button>
                    </form>
                </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
