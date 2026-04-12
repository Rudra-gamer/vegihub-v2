<?php $extraCss = $extraCss ?? []; $extraJs = $extraJs ?? []; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Create Account - Vegihub') ?></title>
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
                <span class="eyebrow">Join VegiHub</span>
                <h1>Open a kitchen-ready account in minutes.</h1>
                <p>Create a buyer or seller profile and start using a marketplace designed around freshness, trust, and cleaner order handling.</p>
                <div class="auth-showcase-grid">
                    <div class="auth-showcase-item"><strong>For buyers</strong>Save addresses, track orders, and reorder produce with less friction.</div>
                    <div class="auth-showcase-item"><strong>For sellers</strong>List products, manage stock, and move orders through verified states.</div>
                    <div class="auth-showcase-item"><strong>For everyone</strong>Secure checkout, support channels, and clearer account management.</div>
                </div>
            </section>
            <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="<?= base_url() ?>"><span>🌿</span> Vegi<span>hub</span></a>
            </div>
            <h2 class="auth-title">Create Account</h2>
            <p class="auth-subtitle">Join Vegihub as a buyer or seller</p>

            <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger"><?= e($msg) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= base_url('register') ?>" class="auth-form">
                <?= csrf_field() ?>
                
                <div class="role-selector">
                    <div class="role-option">
                        <input type="radio" name="role" id="role-buyer" value="buyer" checked>
                        <label for="role-buyer">
                            <span class="role-icon">🛒</span>
                            <span class="role-name">I'm a Buyer</span>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" name="role" id="role-seller" value="seller">
                        <label for="role-seller">
                            <span class="role-icon">🏪</span>
                            <span class="role-name">I'm a Seller</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required value="<?= e(old('name')) ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required value="<?= e(old('email')) ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number (10 Digits)</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="10 Digit Mobile Number" required pattern="\d{10}" maxlength="10" value="<?= e(old('phone')) ?>">
                    <small style="font-size:11px;color:var(--text-muted);display:block;margin-top:4px;">Must be 10 digits and not all same.</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password (8+ chars, alphanumeric, special)</label>
                        <div class="password-toggle">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Min 8 characters, letters, numbers, and symbols" required minlength="8">
                            <button type="button" class="toggle-btn">👁️</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">🚀 Create Account</button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="<?= base_url('login') ?>">Sign In</a>
            </div>
        </div>
            </div>
        </div>
    </div>
</div>
<script src="<?= asset('js/auth.js') ?>"></script>
</body>
</html>
