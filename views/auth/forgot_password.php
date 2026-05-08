<?php $extraCss = $extraCss ?? []; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Forgot Password - Vegihub') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/modern.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>">
</head>
<body>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo"><a href="<?= base_url() ?>"><span>🌿</span> Vegi<span>hub</span></a></div>
            <h2 class="auth-title">Forgot Password?</h2>
            <p class="auth-subtitle">Enter your email and we'll send you a 6-digit reset code</p>
            <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
            <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
            <form method="POST" action="<?= base_url('forgot-password') ?>" class="auth-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your registered email" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">📧 Send Reset Code</button>
            </form>
            <div class="auth-footer"><a href="<?= base_url('login') ?>">← Back to Login</a></div>
        </div>
    </div>
</div>
<script src="<?= asset('js/auth.js') ?>"></script>
</body>
</html>
