<?php include VIEW_PATH . '/layouts/header.php'; ?>
<div class="container" style="padding:30px 20px 60px;">
    <h1 style="font-size:28px;font-weight:800;margin-bottom:24px;">❤️ My Wishlist</h1>
    <?php if (empty($items)): ?>
    <div class="empty-state">
        <div class="empty-icon">💝</div>
        <h3>Your wishlist is empty</h3>
        <p>Save your favorite products here for later!</p>
        <a href="<?= base_url('products') ?>" class="btn btn-primary">Browse Products</a>
    </div>
    <?php else: ?>
    <div class="products-grid">
        <?php foreach ($items as $product): ?>
        <?php include VIEW_PATH . '/partials/product_card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
