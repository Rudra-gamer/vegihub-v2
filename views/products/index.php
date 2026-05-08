<?php include VIEW_PATH . '/layouts/header.php'; ?>

<div class="product-listing-page">
    <div class="container">
        <div class="listing-header">
            <div>
                <h1><?= e($categoryName ?? $searchQuery ?? 'All Products') ?></h1>
                <p class="text-secondary"><?= $pagination['total'] ?> products found</p>
            </div>
            <div class="listing-controls">
                <select class="form-select" onchange="window.location.href=this.value" style="width:auto;">
                    <?php 
                    $sorts = ['newest'=>'Newest First','popular'=>'Most Popular','price_low'=>'Price: Low to High','price_high'=>'Price: High to Low','rating'=>'Highest Rated'];
                    $baseParams = $_GET; 
                    foreach ($sorts as $val => $label): 
                        $baseParams['sort'] = $val;
                        $url = '?' . http_build_query($baseParams);
                    ?>
                    <option value="<?= $url ?>" <?= ($filters['sort'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline btn-sm" onclick="document.querySelector('.filter-sidebar').classList.toggle('open')" style="display:none;" id="filter-toggle">
                    🔍 Filters
                </button>
            </div>
        </div>

        <div class="listing-layout">
            <!-- Filter Sidebar -->
            <aside class="filter-sidebar">
                <form method="GET" action="<?= base_url(isset($searchQuery) ? 'products/search' : 'products') ?>">
                    <?php if (isset($searchQuery)): ?>
                    <input type="hidden" name="q" value="<?= e($searchQuery) ?>">
                    <?php endif; ?>
                    <input type="hidden" name="sort" value="<?= e($filters['sort'] ?? 'newest') ?>">

                    <div class="filter-section">
                        <h3>Categories</h3>
                        <?php foreach ($categories as $cat): ?>
                        <label class="filter-option">
                            <input type="radio" name="category" value="<?= e($cat['slug']) ?>" <?= ($filters['category'] ?? '') === $cat['slug'] ? 'checked' : '' ?>>
                            <span><?= e($cat['name']) ?> (<?= $cat['product_count'] ?>)</span>
                        </label>
                        <?php endforeach; ?>
                        <label class="filter-option">
                            <input type="radio" name="category" value="" <?= empty($filters['category']) ? 'checked' : '' ?>>
                            <span>All Categories</span>
                        </label>
                    </div>

                    <div class="filter-section">
                        <h3>Price Range</h3>
                        <div class="price-range">
                            <input type="number" name="min_price" placeholder="₹ Min" value="<?= e($filters['min_price'] ?? '') ?>">
                            <span>—</span>
                            <input type="number" name="max_price" placeholder="₹ Max" value="<?= e($filters['max_price'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3>Rating</h3>
                        <?php for ($r = 4; $r >= 1; $r--): ?>
                        <label class="filter-option">
                            <input type="radio" name="rating" value="<?= $r ?>" <?= ($filters['rating'] ?? '') == $r ? 'checked' : '' ?>>
                            <span><?= str_repeat('★', $r) . str_repeat('☆', 5 - $r) ?> & Up</span>
                        </label>
                        <?php endfor; ?>
                    </div>

                    <div class="filter-section">
                        <h3>Seller</h3>
                        <label class="filter-option">
                            <input type="radio" name="seller_id" value="" <?= empty($filters['seller_id']) ? 'checked' : '' ?>>
                            <span>All Sellers</span>
                        </label>
                        <?php foreach (($sellerOptions ?? []) as $seller): ?>
                        <label class="filter-option">
                            <input type="radio" name="seller_id" value="<?= (int)$seller['id'] ?>" <?= (string)($filters['seller_id'] ?? '') === (string)$seller['id'] ? 'checked' : '' ?>>
                            <span><?= e($seller['name']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-section">
                        <label class="filter-option">
                            <input type="checkbox" name="organic" value="1" <?= !empty($filters['organic']) ? 'checked' : '' ?>>
                            <span>🌱 Organic Only</span>
                        </label>
                        <label class="filter-option">
                            <input type="checkbox" name="in_stock" value="1" <?= !empty($filters['in_stock']) ? 'checked' : '' ?>>
                            <span>📦 In Stock Only</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                    <a href="<?= base_url('products') ?>" class="btn btn-outline btn-block btn-sm mt-2">Clear All</a>
                </form>
            </aside>

            <!-- Products Grid -->
            <div>
                <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters or search terms.</p>
                    <a href="<?= base_url('products') ?>" class="btn btn-primary">View All Products</a>
                </div>
                <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                    <?php include VIEW_PATH . '/partials/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php if ($pagination['current_page'] > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1])) ?>">← Prev</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="<?= $i === $pagination['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1])) ?>">Next →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    #filter-toggle { display: inline-flex !important; }
}
</style>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
