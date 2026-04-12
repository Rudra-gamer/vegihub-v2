<?php $pageTitle = '404 - Page Not Found'; include VIEW_PATH . '/layouts/header.php'; ?>
<div class="container" style="padding:80px 20px;text-align:center;">
    <div class="empty-state">
        <div class="empty-icon">🥬</div>
        <h1 style="font-size:72px;font-weight:900;color:var(--primary);margin-bottom:8px;">404</h1>
        <h3>Oops! This page doesn't exist</h3>
        <p>The page you're looking for might have been moved or doesn't exist.</p>
        <a href="<?= base_url() ?>" class="btn btn-primary btn-lg">🏠 Go Home</a>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
