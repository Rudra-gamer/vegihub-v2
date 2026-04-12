<?php $extraCss = $extraCss ?? []; $extraJs = $extraJs ?? []; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Sign In - Vegihub') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/modern.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>">
</head>
<body>
<div class="auth-page">
    <div class="auth-standalone">
        <div class="auth-layout">
            <section class="auth-showcase">
                <span class="eyebrow">Buyer and Seller Access</span>
                <h1>Fresh commerce with a calmer, cleaner flow.</h1>
                <p>Sign in to manage orders, discover seasonal produce, or run your seller dashboard with a more polished experience across the platform.</p>
                <div class="auth-showcase-grid">
                    <div class="auth-showcase-item"><strong>Verified accounts</strong>Secure sessions, protected forms, and a more reliable checkout journey.</div>
                    <div class="auth-showcase-item"><strong>Fast reordering</strong>Pick up where you left off with cart, orders, and account tools in one place.</div>
                    <div class="auth-showcase-item"><strong>Seller-ready</strong>Access product management, order progression, and business reporting after login.</div>
                </div>
            </section>
            <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="<?= base_url() ?>"><span>🌿</span> Vegi<span>hub</span></a>
            </div>
            <h2 class="auth-title">Welcome Back!</h2>
            <p class="auth-subtitle">Sign in to your account to continue shopping</p>

            <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('warning')): ?>
            <div class="alert alert-warning"><?= e($msg) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= base_url('login') ?>" class="auth-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required value="<?= e(old('email')) ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-toggle">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <button type="button" class="toggle-btn">👁️</button>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                        <input type="checkbox" name="remember" style="accent-color:var(--primary);">
                        Remember me
                    </label>
                    <a href="<?= base_url('forgot-password') ?>" style="font-size:14px;font-weight:600;">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">🔒 Sign In</button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="<?= base_url('register') ?>">Create one</a>
            </div>
        </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= asset('js/auth.js') ?>"></script>
</body>
</html>
