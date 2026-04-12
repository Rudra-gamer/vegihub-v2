<?php $headerTitle = 'Platform Settings'; $activePage = 'settings'; include VIEW_PATH . '/layouts/admin_layout.php'; ?>
<div class="dashboard-card">
    <div class="card-title">⚙️ General Settings</div>
    <div class="card-content" style="padding:24px;">
        <form method="POST" action="<?= base_url('admin/settings') ?>">
            <?= csrf_field() ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group"><label>Site Name</label><input name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? 'Vegihub') ?>"></div>
                <div class="form-group"><label>Tagline</label><input name="site_tagline" class="form-control" value="<?= e($settings['site_tagline'] ?? '') ?>"></div>
                <div class="form-group"><label>Delivery Fee (₹)</label><input name="delivery_fee" type="number" class="form-control" value="<?= e($settings['delivery_fee'] ?? '15') ?>"></div>
                <div class="form-group"><label>Free Delivery Above (₹)</label><input name="free_delivery_above" type="number" class="form-control" value="<?= e($settings['free_delivery_above'] ?? '500') ?>"></div>
                <div class="form-group"><label>Commission Rate (%)</label><input name="commission_rate" type="number" class="form-control" value="<?= e($settings['commission_rate'] ?? '10') ?>"></div>
                <div class="form-group"><label>Min Order Amount (₹)</label><input name="min_order_amount" type="number" class="form-control" value="<?= e($settings['min_order_amount'] ?? '100') ?>"></div>
                <div class="form-group"><label>Contact Email</label><input name="contact_email" type="email" class="form-control" value="<?= e($settings['contact_email'] ?? '') ?>"></div>
                <div class="form-group"><label>Contact Phone</label><input name="contact_phone" class="form-control" value="<?= e($settings['contact_phone'] ?? '') ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg mt-3">💾 Save Settings</button>
        </form>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/admin_footer.php'; ?>
