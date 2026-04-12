<?php 
include VIEW_PATH . '/layouts/header.php';
$salePrice = $product['sale_price'] ?? null;
$originalPrice = $product['price'];
$displayPrice = $salePrice ?: $originalPrice;
$discountPct = $salePrice ? round((($originalPrice - $salePrice) / $originalPrice) * 100) : 0;
$inWishlist = false;
if (is_logged_in()) {
    $wl = new Wishlist();
    $inWishlist = $wl->isInWishlist($_SESSION['user_id'], $product['id']);
}
?>

<div class="product-detail-page">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?= base_url() ?>">Home</a>
            <span class="separator">›</span>
            <a href="<?= base_url('products') ?>">Products</a>
            <span class="separator">›</span>
            <a href="<?= base_url('category/' . $product['category_slug']) ?>"><?= e($product['category_name']) ?></a>
            <span class="separator">›</span>
            <span><?= e($product['name']) ?></span>
        </div>

        <div class="product-detail-grid">
            <!-- Image Gallery -->
            <div class="product-gallery">
                <div class="main-image">
                    <img src="<?= asset('uploads/products/' . ($product['image'] ?? 'placeholder.jpg')) ?>" 
                         alt="<?= e($product['name']) ?>" id="main-product-image"
                         onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&h=600&fit=crop'">
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info-section">
                <h1 class="product-name-detail"><?= e($product['name']) ?></h1>
                
                <div class="product-meta">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star <?= $i <= round($product['avg_rating']) ? '' : 'empty' ?>" style="font-size:18px;">★</span>
                        <?php endfor; ?>
                    </div>
                    <?php if ((int)$product['total_reviews'] > 0): ?>
                    <a href="#reviews" class="review-count"><?= (int)$product['total_reviews'] ?> reviews</a>
                    <?php else: ?>
                    <span class="review-count">No reviews yet</span>
                    <?php endif; ?>
                    <?php if ((int)$product['total_sold'] > 0): ?>
                    <span class="meta-divider"></span>
                    <span class="sold-count"><?= (int)$product['total_sold'] ?> delivered</span>
                    <?php endif; ?>
                </div>

                <div class="price-section">
                    <span class="sale-price"><?= format_price($displayPrice) ?></span>
                    <?php if ($salePrice): ?>
                    <span class="mrp"><?= format_price($originalPrice) ?></span>
                    <span class="discount">-<?= $discountPct ?>% OFF</span>
                    <?php endif; ?>
                    <div class="unit-info">Price per <?= $product['unit'] ?> • <?= $product['stock'] > 0 ? '<span style="color:var(--success);font-weight:600;">In Stock</span>' : '<span style="color:var(--danger);font-weight:600;">Out of Stock</span>' ?></div>
                </div>

                <div class="product-highlights">
                    <?php if (!empty($product['is_organic'])): ?>
                    <div class="highlight-item"><span class="highlight-icon">🌱</span> Certified Organic</div>
                    <?php endif; ?>
                    <div class="highlight-item"><span class="highlight-icon">🚚</span> Free delivery on orders above ₹500</div>
                    <div class="highlight-item"><span class="highlight-icon">🔄</span> Easy returns within 24 hours</div>
                    <div class="highlight-item"><span class="highlight-icon">✅</span> Quality guaranteed</div>
                </div>

                <?php if ($product['stock'] > 0): ?>
                <div class="quantity-selector">
                    <label>Quantity:</label>
                    <button class="qty-btn" data-action="decrease">−</button>
                    <input type="number" class="qty-value" id="product-qty" value="1" min="<?= $product['min_order_qty'] ?>" max="<?= min($product['stock'], 20) ?>">
                    <button class="qty-btn" data-action="increase">+</button>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-primary btn-lg" onclick="addToCart(<?= $product['id'] ?>, document.getElementById('product-qty').value)">
                        🛒 Add to Cart
                    </button>
                    <button class="btn <?= $inWishlist ? 'btn-danger' : 'btn-outline' ?>" onclick="toggleWishlist(<?= $product['id'] ?>, this)">
                        <?= $inWishlist ? '❤️' : '🤍' ?> Wishlist
                    </button>
                </div>
                <?php endif; ?>

                <div class="seller-info-card">
                    <div class="seller-avatar"><?= strtoupper(substr($product['seller_name'], 0, 1)) ?></div>
                    <div class="seller-details">
                        <div class="seller-label">Sold by</div>
                        <div class="seller-name"><?= e($product['seller_name']) ?></div>
                        <div style="margin-top:6px;color:var(--text-secondary);font-size:14px;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= round($sellerRating['avg_rating'] ?? 0) ? '' : 'empty' ?>" style="font-size:14px;">★</span>
                            <?php endfor; ?>
                            <span style="margin-left:6px;">
                                <?= number_format((float)($sellerRating['avg_rating'] ?? 0), 1) ?> vendor rating
                                (<?= (int)($sellerRating['total_reviews'] ?? 0) ?>)
                            </span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($product['description'])): ?>
                <div class="mt-3">
                    <h3 style="font-size:18px;font-weight:700;margin-bottom:12px;">Product Description</h3>
                    <p style="color:var(--text-secondary);line-height:1.8;"><?= nl2br(e($product['description'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews -->
        <div class="reviews-section" id="reviews">
            <h2>Customer Reviews (<?= count($reviews) ?>)</h2>
            
            <?php if ($canReview): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;">Write a Review</h3>
                    <form method="POST" action="<?= base_url('reviews/add') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <div class="form-group">
                            <label>Rating</label>
                            <select name="rating" class="form-select" required>
                                <option value="">Select rating</option>
                                <option value="5">★★★★★ Excellent</option>
                                <option value="4">★★★★☆ Good</option>
                                <option value="3">★★★☆☆ Average</option>
                                <option value="2">★★☆☆☆ Below Average</option>
                                <option value="1">★☆☆☆☆ Poor</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Your Review</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>
            <?php elseif (is_logged_in()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h3 style="font-size:16px;font-weight:600;margin-bottom:10px;">Product Review</h3>
                    <p class="text-muted" style="margin:0;">
                        <?= !empty($hasReviewed) ? 'You already reviewed this product.' : 'Review is available after this product is delivered in one of your orders.' ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
            <div class="empty-state" style="padding:40px;">
                <p class="text-muted">No reviews yet. Be the first to review!</p>
            </div>
            <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="review-avatar"><?= strtoupper(substr($rev['user_name'], 0, 1)) ?></div>
                    <div>
                        <div class="review-name"><?= e($rev['user_name']) ?></div>
                        <div class="stars" style="font-size:13px;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= $rev['rating'] ? '' : 'empty' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="review-date" style="margin-left:auto;"><?= time_ago($rev['created_at']) ?></div>
                </div>
                <?php if (!empty($rev['comment'])): ?>
                <div class="review-comment"><?= nl2br(e($rev['comment'])) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="reviews-section" style="margin-top:32px;">
            <h2>Vendor Reviews for <?= e($product['seller_name']) ?> (<?= (int)($sellerRating['total_reviews'] ?? 0) ?>)</h2>

            <?php if ($canReviewSeller): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h3 style="font-size:16px;font-weight:600;margin-bottom:16px;">Review This Vendor</h3>
                    <form method="POST" action="<?= base_url('reviews/seller/add') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="seller_id" value="<?= $product['seller_id'] ?>">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <div class="form-group">
                            <label>Rating</label>
                            <select name="rating" class="form-select" required>
                                <option value="">Select rating</option>
                                <option value="5">★★★★★ Excellent vendor</option>
                                <option value="4">★★★★☆ Good service</option>
                                <option value="3">★★★☆☆ Average</option>
                                <option value="2">★★☆☆☆ Needs improvement</option>
                                <option value="1">★☆☆☆☆ Poor experience</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Your Feedback</label>
                            <textarea name="comment" class="form-control" rows="3" placeholder="How was the vendor experience, packaging, communication, and delivery handling?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Vendor Review</button>
                    </form>
                </div>
            </div>
            <?php elseif (is_logged_in()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h3 style="font-size:16px;font-weight:600;margin-bottom:10px;">Vendor Review</h3>
                    <p class="text-muted" style="margin:0;">
                        <?= !empty($hasReviewedSeller) ? 'You already reviewed this vendor for this product.' : 'Vendor review becomes available after your delivered order.' ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($sellerReviews)): ?>
            <div class="empty-state" style="padding:40px;">
                <p class="text-muted">No vendor reviews yet.</p>
            </div>
            <?php else: ?>
            <?php foreach ($sellerReviews as $sellerRev): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="review-avatar"><?= strtoupper(substr($sellerRev['user_name'], 0, 1)) ?></div>
                    <div>
                        <div class="review-name"><?= e($sellerRev['user_name']) ?></div>
                        <div class="stars" style="font-size:13px;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= $sellerRev['rating'] ? '' : 'empty' ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <?php if (!empty($sellerRev['product_name']) && !empty($sellerRev['product_slug'])): ?>
                        <div style="color:var(--text-secondary);font-size:13px;margin-top:4px;">
                            From product:
                            <a href="<?= base_url('products/' . $sellerRev['product_slug']) ?>"><?= e($sellerRev['product_name']) ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="review-date" style="margin-left:auto;"><?= time_ago($sellerRev['created_at']) ?></div>
                </div>
                <?php if (!empty($sellerRev['comment'])): ?>
                <div class="review-comment"><?= nl2br(e($sellerRev['comment'])) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related)): ?>
        <div class="section" style="padding-top:48px;">
            <h2 class="section-title">Related Products</h2>
            <div class="products-grid" style="margin-top:24px;">
                <?php foreach ($related as $product): ?>
                <?php include VIEW_PATH . '/partials/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
