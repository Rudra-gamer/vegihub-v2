<?php

$cartCount = get_cart_count();
$categories = [];
try {
    $catModel = new Category();
    $categories = $catModel->getActive();
} catch(Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Vegihub - Fresh Vegetables Delivered') ?></title>
    <meta name="description" content="<?= e($pageDescription ?? 'Vegihub - Your trusted online vegetable marketplace. Buy fresh vegetables, fruits, and herbs delivered to your door.') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/landing.css') ?>">
    <?php if (isset($extraCss)): foreach((array)$extraCss as $css): ?>
    <link rel="stylesheet" href="<?= asset('css/' . $css) ?>">
    <?php endforeach; endif; ?>
    <link rel="stylesheet" href="<?= asset('css/modern.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>">
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container">
        <?php if ($msg = flash('success')): ?>
        <div class="toast success"><span>✅</span><span><?= e($msg) ?></span><button class="toast-close">×</button></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
        <div class="toast error"><span>❌</span><span><?= e($msg) ?></span><button class="toast-close">×</button></div>
        <?php endif; ?>
        <?php if ($msg = flash('warning')): ?>
        <div class="toast warning"><span>⚠️</span><span><?= e($msg) ?></span><button class="toast-close">×</button></div>
        <?php endif; ?>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <!-- <div class="announcement-bar">
            <div class="container">
                <span>Farm-fresh produce from local sellers</span>
                <span>Free delivery above ₹500</span>
                <span>Secure checkout with Razorpay and COD</span>
            </div>
        </div> -->
        <div class="header-top">
            <div class="container">
                <button type="button" class="hamburger" aria-label="Open menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>

                <a href="<?= base_url() ?>" class="nav-logo">
                    <span class="logo-icon">🌿</span>
                    Vegi<span>hub</span>
                </a>

                <form class="search-bar" action="<?= base_url('products/search') ?>" method="GET">
                    <select name="category">
                        <option value="">All</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>" <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="search-input" name="q" placeholder="Search for fresh vegetables, fruits..." value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off">
                    <button type="submit">🔍</button>
                </form>

                <div class="nav-actions">
                    <?php if (is_logged_in()): ?>
                        <?php $user = current_user(); ?>
                        <a href="<?= base_url(is_admin() ? 'admin' : (is_seller() ? 'seller' : 'profile')) ?>" class="nav-action">
                            <span class="nav-label">Hello, <?= e(explode(' ', $user['name'])[0]) ?></span>
                            <span class="nav-text"><?= ucfirst($user['role']) ?></span>
                        </a>
                        <?php if (!is_seller() && !is_admin()): ?>
                        <a href="<?= base_url('orders') ?>" class="nav-action">
                            <span class="nav-label">Returns</span>
                            <span class="nav-text">& Orders</span>
                        </a>
                        <a href="<?= base_url('logout') ?>" class="nav-action">
                            <span class="nav-label">Safe Exit</span>
                            <span class="nav-text">Logout</span>
                        </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?= base_url('login') ?>" class="nav-action">
                            <span class="nav-label">Hello, Sign in</span>
                            <span class="nav-text">Account</span>
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('cart') ?>" class="nav-action" style="flex-direction:row;gap:4px;">
                        <span class="nav-icon" style="position:relative;">
                            🛒
                            <span class="cart-badge" style="<?= $cartCount > 0 ? '' : 'display:none' ?>"><?= $cartCount ?></span>
                        </span>
                        <span class="nav-text">Cart</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Category Navigation -->
        <nav class="header-categories">
            <div class="container">
                <a href="<?= base_url('products') ?>" class="cat-nav-item <?= ($currentPage ?? '') === 'products' ? 'active' : '' ?>">☰ All Products</a>
                <?php foreach ($categories as $cat): ?>
                <a href="<?= base_url('category/' . $cat['slug']) ?>" class="cat-nav-item">
                    <?= $cat['icon'] ?? '' ?> <?= e($cat['name']) ?>
                </a>
                <?php endforeach; ?>
                <?php if (is_logged_in()): ?>
                <a href="<?= base_url('logout') ?>" class="cat-nav-item" style="margin-left:auto;">🚪 Logout</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
