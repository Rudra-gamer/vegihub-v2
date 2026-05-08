<?php

$user = current_user();
$isAdmin = $user['role'] === 'admin';
$basePath = $isAdmin ? 'admin' : 'seller';
$unreadMessages = 0;
if ($isAdmin) { try { $unreadMessages = (new Message())->getUnreadCount(); } catch(Exception $e) {} }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= base_url() ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Dashboard - Vegihub') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>">
</head>
<body>
<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?= base_url() ?>" class="sidebar-logo">
                <span class="logo-icon">🌿</span> Vegi<span>hub</span>
            </a>
        </div>
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
            <div class="sidebar-user-info">
                <div class="user-name"><?= e($user['name']) ?></div>
                <div class="user-role"><?= ucfirst($user['role']) ?></div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="<?= base_url($basePath) ?>" class="nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
            </div>

            <?php if ($isAdmin): ?>
            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <a href="<?= base_url('admin/users') ?>" class="nav-item <?= ($activePage ?? '') === 'users' ? 'active' : '' ?>">
                    <span class="nav-icon">👥</span> Users
                </a>
                <a href="<?= base_url('admin/products') ?>" class="nav-item <?= ($activePage ?? '') === 'products' ? 'active' : '' ?>">
                    <span class="nav-icon">🥬</span> Products
                </a>
                <a href="<?= base_url('admin/orders') ?>" class="nav-item <?= ($activePage ?? '') === 'orders' ? 'active' : '' ?>">
                    <span class="nav-icon">📦</span> Orders
                </a>
                <a href="<?= base_url('admin/categories') ?>" class="nav-item <?= ($activePage ?? '') === 'categories' ? 'active' : '' ?>">
                    <span class="nav-icon">📂</span> Categories
                </a>
                <a href="<?= base_url('admin/coupons') ?>" class="nav-item <?= ($activePage ?? '') === 'coupons' ? 'active' : '' ?>">
                    <span class="nav-icon">🎫</span> Coupons
                </a>
                <a href="<?= base_url('admin/messages') ?>" class="nav-item <?= ($activePage ?? '') === 'messages' ? 'active' : '' ?>">
                    <span class="nav-icon">💬</span> Messages
                    <?php if ($unreadMessages > 0): ?><span class="nav-badge"><?= $unreadMessages ?></span><?php endif; ?>
                </a>
            </div>
            <?php else: ?>
            <div class="nav-section">
                <div class="nav-section-title">Store</div>
                <a href="<?= base_url('seller/products') ?>" class="nav-item <?= ($activePage ?? '') === 'products' ? 'active' : '' ?>">
                    <span class="nav-icon">🥬</span> Products
                </a>
                <a href="<?= base_url('seller/products/add') ?>" class="nav-item <?= ($activePage ?? '') === 'add_product' ? 'active' : '' ?>">
                    <span class="nav-icon">➕</span> Add Product
                </a>
                <a href="<?= base_url('seller/orders') ?>" class="nav-item <?= ($activePage ?? '') === 'orders' ? 'active' : '' ?>">
                    <span class="nav-icon">📦</span> Orders
                </a>
                <a href="<?= base_url('seller/earnings') ?>" class="nav-item <?= ($activePage ?? '') === 'earnings' ? 'active' : '' ?>">
                    <span class="nav-icon">💰</span> Earnings
                </a>
            </div>
            <?php endif; ?>

            <div class="nav-section">
                <div class="nav-section-title">Account</div>
                <a href="<?= base_url() ?>" class="nav-item"><span class="nav-icon">🏠</span> Visit Site</a>
                <a href="<?= base_url('profile') ?>" class="nav-item"><span class="nav-icon">👤</span> Profile</a>
                <a href="<?= base_url('logout') ?>" class="nav-item"><span class="nav-icon">🚪</span> Logout</a>
            </div>
        </nav>
    </aside>

    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-header">
            <div style="display:flex;align-items:center;gap:12px;">
                <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
                <h1><?= e($headerTitle ?? 'Dashboard') ?></h1>
            </div>
            <div class="header-actions">
                <?php if (isset($headerAction)): ?>
                <?= $headerAction ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="content-body">
            <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
            <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
