<?php

$salePrice = $product['sale_price'] ?? null;
$originalPrice = $product['price'];
$displayPrice = $salePrice ?: $originalPrice;
$discountPct = $salePrice ? round((($originalPrice - $salePrice) / $originalPrice) * 100) : 0;
$isInWishlist = false;
if (is_logged_in()) {
    static $wishlistModel = null;
    if ($wishlistModel === null) $wishlistModel = new Wishlist();
    $isInWishlist = $wishlistModel->isInWishlist($_SESSION['user_id'], $product['id']);
}
?>
<div class="product-card">
    <a href="<?= base_url('products/' . $product['slug']) ?>" class="product-image">
        <img src="<?= asset('uploads/products/' . ($product['image'] ?? 'placeholder.jpg')) ?>" 
             alt="<?= e($product['name']) ?>"
             onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300&h=300&fit=crop'"
             loading="lazy">
        <div class="product-badges">
            <?php if ($discountPct > 0): ?>
            <span class="discount-badge">-<?= $discountPct ?>%</span>
            <?php endif; ?>
            <?php if (!empty($product['is_organic'])): ?>
            <span class="organic-badge">🌱 Organic</span>
            <?php endif; ?>
        </div>
    </a>
    <button class="wishlist-btn <?= $isInWishlist ? 'active' : '' ?>" 
            onclick="toggleWishlist(<?= $product['id'] ?>, this)" title="Add to Wishlist">
        <?= $isInWishlist ? '❤️' : '🤍' ?>
    </button>
    <div class="product-info">
        <div class="product-category"><?= e($product['category_name'] ?? '') ?></div>
        <a href="<?= base_url('products/' . $product['slug']) ?>" class="product-name"><?= e($product['name']) ?></a>
        <div class="product-seller">by <?= e($product['seller_name'] ?? 'Vegihub') ?></div>
        <div class="product-rating">
            <div class="stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star <?= $i <= round($product['avg_rating']) ? '' : 'empty' ?>">★</span>
                <?php endfor; ?>
            </div>
            <span class="count">
                <?= (int)($product['total_reviews'] ?? 0) > 0 ? '(' . (int)$product['total_reviews'] . ')' : '(new)' ?>
            </span>
        </div>
        <div class="product-price">
            <span class="current-price"><?= format_price($displayPrice) ?></span>
            <?php if ($salePrice): ?>
            <span class="original-price"><?= format_price($originalPrice) ?></span>
            <?php endif; ?>
            <span class="unit-label">/<?= $product['unit'] ?></span>
        </div>
        <div class="product-meta-row">
            <span class="stock-badge"><?= (int)($product['stock'] ?? 0) > 0 ? 'Fresh stock' : 'Out of stock' ?></span>
            <?php if ((int)($product['total_reviews'] ?? 0) > 0): ?>
            <span class="count"><?= (int)$product['total_reviews'] ?> reviews</span>
            <?php endif; ?>
        </div>
        <div class="product-actions">
            <button class="add-cart-btn" onclick="addToCart(<?= $product['id'] ?>)">
                🛒 Add to Cart
            </button>
        </div>
    </div>
</div>
