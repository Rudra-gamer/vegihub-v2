<?php $extraCss = $extraCss ?? []; $extraJs = $extraJs ?? []; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Verify Email - Vegihub') ?></title>
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
            <div class="auth-logo">
                <a href="<?= base_url() ?>"><span>🌿</span> Vegi<span>hub</span></a>
            </div>
            <h2 class="auth-title">Verify Your Email</h2>
            <p class="auth-subtitle">We've sent a 6-digit code to<br><strong><?= e($email) ?></strong></p>

            <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= e($msg) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= base_url('verify') ?>" class="auth-form">
                <?= csrf_field() ?>
                <input type="hidden" id="verification_code" name="verification_code">
                <input type="hidden" id="verify-email" value="<?= e($email) ?>">

                <div class="otp-inputs">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autofocus>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">✅ Verify Email</button>
            </form>

            <div class="resend-section">
                Didn't receive the code?
                <button class="resend-btn" disabled>Resend Code</button>
                <span class="resend-timer">(60s)</span>
            </div>

            <div class="auth-footer">
                <a href="<?= base_url('login') ?>">← Back to Login</a>
            </div>
        </div>
    </div>
</div>
<script src="<?= asset('js/auth.js') ?>"></script>
</body>
</html>
