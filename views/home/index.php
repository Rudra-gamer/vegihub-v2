<?php include VIEW_PATH . '/layouts/header.php'; ?>

<!-- Hero Carousel -->
<section class="hero-section">
    <div class="hero-carousel">
        <div class="hero-slide active">
            <div class="container">
                <div class="hero-content">
                    <div class="hero-badge">🚀 Free Delivery on Orders Above ₹500</div>
                    <h1 class="hero-title">
                        Fresh <span class="highlight">Vegetables</span> Delivered to Your Door
                    </h1>
                    <p class="hero-description">
                        Farm-fresh vegetables, fruits, and herbs handpicked daily. 
                        From local farmers to your kitchen — guaranteed freshness or your money back.
                    </p>
                    <div class="hero-buttons">
                        <a href="<?= base_url('products') ?>" class="btn btn-accent btn-lg">🛍️ Shop Now</a>
                        <a href="<?= base_url('register') ?>" class="btn btn-outline btn-lg" style="border-color:white;color:white;">Join as Seller</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?w=400&h=400&fit=crop" alt="Fresh vegetables basket" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
        <div class="hero-slide">
            <div class="container">
                <div class="hero-content">
                    <div class="hero-badge">🌱 100% Organic Available</div>
                    <h1 class="hero-title">
                        Go <span class="highlight">Organic</span> for a Healthier Life
                    </h1>
                    <p class="hero-description">
                        Certified organic vegetables grown without pesticides. 
                        Support sustainable farming while feeding your family the best.
                    </p>
                    <div class="hero-buttons">
                        <a href="<?= base_url('category/organic') ?>" class="btn btn-accent btn-lg">🌿 Shop Organic</a>
                        <a href="<?= base_url('about') ?>" class="btn btn-outline btn-lg" style="border-color:white;color:white;">Learn More</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=400&h=400&fit=crop" alt="Organic produce" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
        <div class="hero-slide">
            <div class="container">
                <div class="hero-content">
                    <div class="hero-badge">💰 Big Savings This Week</div>
                    <h1 class="hero-title">
                        Save Up to <span class="highlight">40%</span> on Fresh Produce
                    </h1>
                    <p class="hero-description">
                        Check out our deal of the day! Premium quality vegetables at unbeatable prices.
                        Use code WELCOME10 for extra discount on your first order.
                    </p>
                    <div class="hero-buttons">
                        <a href="<?= base_url('products?sort=price_low') ?>" class="btn btn-accent btn-lg">🔥 View Deals</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=400&fit=crop" alt="Grocery savings" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </div>
    <div class="hero-dots">
        <div class="hero-dot active"></div>
        <div class="hero-dot"></div>
        <div class="hero-dot"></div>
    </div>
</section>

<!-- Categories -->
<section class="categories-section animate-on-scroll">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Shop by Category</h2>
            <a href="<?= base_url('products') ?>" class="section-link">View All →</a>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= base_url('category/' . $cat['slug']) ?>" class="category-card">
                <span class="cat-icon"><?= $cat['icon'] ?? '🥬' ?></span>
                <span class="cat-name"><?= e($cat['name']) ?></span>
                <span class="cat-count"><?= $cat['product_count'] ?? 0 ?> items</span>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="modern-stat-strip">
            <div class="modern-stat-card">
                <strong>3 hrs</strong>
                <span>Average same-day dispatch across active orders</span>
            </div>
            <div class="modern-stat-card">
                <strong>100%</strong>
                <span>Transparent pricing with live cart and checkout totals</span>
            </div>
            <div class="modern-stat-card">
                <strong>Local</strong>
                <span>Fresh produce from nearby growers and trusted sellers</span>
            </div>
            <div class="modern-stat-card">
                <strong>Secure</strong>
                <span>Verified online payments plus cash on delivery support</span>
            </div>
        </div>
    </div>
</section>

<!-- Deal of the Day -->
<?php if (!empty($deals)): ?>
<section class="deals-section animate-on-scroll">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title" style="color:#E63946;">🔥 Deal of the Day</h2>
            </div>
            <div class="deal-timer">
                <span style="font-weight:600;color:#E63946;">Ends in:</span>
                <div class="timer-block"><span class="number" data-hours>00</span><span class="label">Hours</span></div>
                <div class="timer-block"><span class="number" data-minutes>00</span><span class="label">Mins</span></div>
                <div class="timer-block"><span class="number" data-seconds>00</span><span class="label">Secs</span></div>
            </div>
        </div>
        <div class="products-grid">
            <?php foreach ($deals as $product): ?>
            <?php include VIEW_PATH . '/partials/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="section animate-on-scroll">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">⭐ Featured Products</h2>
            <a href="<?= base_url('products?sort=popular') ?>" class="section-link">See All →</a>
        </div>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <?php include VIEW_PATH . '/partials/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Trust Badges -->
<section class="trust-section animate-on-scroll">
    <div class="container">
        <div class="trust-grid">
            <div class="trust-card">
                <span class="trust-icon">🌿</span>
                <h3>100% Fresh</h3>
                <p>Handpicked daily from local farms. Guaranteed freshness or full refund.</p>
            </div>
            <div class="trust-card">
                <span class="trust-icon">🚚</span>
                <h3>Fast Delivery</h3>
                <p>Same-day delivery available. Free delivery on orders above ₹500.</p>
            </div>
            <div class="trust-card">
                <span class="trust-icon">🛡️</span>
                <h3>Secure Payment</h3>
                <p>100% secure payments via Razorpay. COD also available.</p>
            </div>
            <div class="trust-card">
                <span class="trust-icon">🌱</span>
                <h3>Organic Options</h3>
                <p>Certified organic produce grown without harmful pesticides.</p>
            </div>
        </div>

        <div class="story-grid">
            <div class="story-card">
                <h3>Built for kitchens that care about quality</h3>
                <p>VegiHub is designed to feel less like a crowded marketplace and more like a trusted produce desk: cleaner discovery, clearer pricing, and a faster route from browsing to delivery.</p>
                <div class="story-points">
                    <div class="story-point"><strong>Curated freshness</strong><br>Highlighting seasonal picks, organic options, and high-trust sellers.</div>
                    <div class="story-point"><strong>Fewer dead ends</strong><br>Cart, checkout, and order flows are now aligned with the backend state.</div>
                    <div class="story-point"><strong>Designed for mobile first</strong><br>Large tap targets, readable cards, and a cleaner bottom navigation.</div>
                </div>
            </div>
            <div class="quote-card">
                <h3>What shoppers want</h3>
                <blockquote>"I just want fresh vegetables, clear prices, and checkout that feels reliable."</blockquote>
                <p>That is the direction of the interface: calm browsing, stronger trust signals, and less friction between product discovery and payment.</p>
            </div>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<?php if (!empty($newArrivals)): ?>
<section class="section animate-on-scroll" style="background:var(--surface);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">🆕 New Arrivals</h2>
            <a href="<?= base_url('products?sort=newest') ?>" class="section-link">See All →</a>
        </div>
        <div class="products-grid">
            <?php foreach ($newArrivals as $product): ?>
            <?php include VIEW_PATH . '/partials/product_card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Newsletter -->
<section class="newsletter-section animate-on-scroll">
    <div class="container">
        <h2>🌿 Stay Fresh with Vegihub</h2>
        <p>Subscribe to get the latest deals, seasonal vegetables, and exclusive offers!</p>
        <form class="newsletter-form" onsubmit="event.preventDefault(); showToast('Thank you for subscribing! 🎉', 'success');">
            <input type="email" placeholder="Enter your email address" required>
            <button type="submit">Subscribe</button>
        </form>
    </div>
</section>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
