<?php include VIEW_PATH . '/layouts/header.php'; ?>
<div class="container page-shell">
    <section class="editorial-hero">
        <span class="eyebrow">Account Center</span>
        <h1>Manage your profile with less friction.</h1>
        <p>Keep your personal details, password, and delivery setup current so checkout, orders, and support stay accurate.</p>
    </section>
    
    <div class="account-grid checkout-layout">
        <div class="account-panel">
            <div class="card-header">📝 Personal Information</div>
            <div class="card-body">
                <form method="POST" action="<?= base_url('profile/update') ?>">
                    <?= csrf_field() ?>
                    <div class="form-group"><label>Full Name</label><input name="name" class="form-control" value="<?= e($user['name']) ?>" required></div>
                    <div class="form-group"><label>Email</label><input class="form-control" value="<?= e($user['email']) ?>" disabled style="background:var(--bg-alt);"></div>
                    <div class="form-group"><label>Phone</label><input name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>"></div>
                    <div class="form-group"><label>Role</label><input class="form-control" value="<?= ucfirst($user['role']) ?>" disabled style="background:var(--bg-alt);"></div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

        <div>
            <div class="account-panel mb-3">
                <div class="card-header">🖼️ Profile Picture</div>
                <div class="card-body" style="text-align:center;">
                    <div class="avatar-badge">
                        <?php if ($user['avatar']): ?>
                        <img src="<?= asset('uploads/avatars/' . $user['avatar']) ?>" style="width:100px;height:100px;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="<?= base_url('profile/avatar') ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="file" name="avatar" accept="image/*" class="form-control mb-2" required>
                        <button type="submit" class="btn btn-outline btn-sm">Upload</button>
                    </form>
                </div>
            </div>

            <div class="account-panel">
                <div class="card-header">🔐 Change Password</div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('profile/password') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                        <div class="form-group"><label>New Password</label><input type="password" name="new_password" class="form-control" required minlength="6"></div>
                        <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="section-actions">
        <a href="<?= base_url('profile/addresses') ?>" class="btn btn-outline">📍 Manage Addresses</a>
        <a href="<?= base_url('orders') ?>" class="btn btn-outline">📦 My Orders</a>
        <a href="<?= base_url('wishlist') ?>" class="btn btn-outline">❤️ My Wishlist</a>
        <a href="<?= base_url('logout') ?>" class="btn btn-danger">🚪 Logout</a>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
