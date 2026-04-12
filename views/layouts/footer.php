    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <span>🌿</span> Vegi<span>hub</span>
                    </div>
                    <p>Your trusted online marketplace for fresh vegetables, fruits, and herbs. We connect local farmers directly to your kitchen.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">📘</a>
                        <a href="#" class="social-link">🐦</a>
                        <a href="#" class="social-link">📸</a>
                        <a href="#" class="social-link">🔗</a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?= base_url() ?>">🏠 Home</a></li>
                        <li><a href="<?= base_url('products') ?>">🥬 Products</a></li>
                        <li><a href="<?= base_url('about') ?>">ℹ️ About Us</a></li>
                        <li><a href="<?= base_url('contact') ?>">📞 Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>My Account</h4>
                    <ul>
                        <li><a href="<?= base_url('profile') ?>">👤 My Profile</a></li>
                        <li><a href="<?= base_url('orders') ?>">📦 My Orders</a></li>
                        <li><a href="<?= base_url('wishlist') ?>">❤️ Wishlist</a></li>
                        <li><a href="<?= base_url('cart') ?>">🛒 Cart</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contact Info</h4>
                    <ul>
                        <li><a href="mailto:rudranahak1000@gmail.com">📧 rudranahak1000@gmail.com</a></li>
                        <li><a href="tel:7064841325">📞 7064841325</a></li>
                        <li><a href="#">📍 India</a></li>
                        <li><a href="#">⏰ Mon-Sat, 8AM-8PM</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© <?= date('Y') ?> Vegihub. All rights reserved. Made with Mr.Rudra💚 for fresh food lovers.</p>
                <div class="payment-methods">
                    <span>💳 Razorpay</span>
                    <span>🛡️ Secure</span>
                    <span>💵 COD</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-nav">
        <div class="mobile-nav-items">
            <a href="<?= base_url() ?>" class="mobile-nav-item <?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>">
                <span class="mobile-icon">🏠</span>
                <span>Home</span>
            </a>
            <a href="<?= base_url('products') ?>" class="mobile-nav-item <?= ($currentPage ?? '') === 'products' ? 'active' : '' ?>">
                <span class="mobile-icon">🔍</span>
                <span>Explore</span>
            </a>
            <a href="<?= base_url('cart') ?>" class="mobile-nav-item <?= ($currentPage ?? '') === 'cart' ? 'active' : '' ?>" style="position:relative;">
                <span class="mobile-icon">🛒</span>
                <?php if (get_cart_count() > 0): ?>
                <span class="mobile-badge"><?= get_cart_count() ?></span>
                <?php endif; ?>
                <span>Cart</span>
            </a>
            <a href="<?= base_url('wishlist') ?>" class="mobile-nav-item <?= ($currentPage ?? '') === 'wishlist' ? 'active' : '' ?>">
                <span class="mobile-icon">❤️</span>
                <span>Wishlist</span>
            </a>
            <a href="<?= base_url(is_logged_in() ? 'profile' : 'login') ?>" class="mobile-nav-item <?= ($currentPage ?? '') === 'profile' ? 'active' : '' ?>">
                <span class="mobile-icon">👤</span>
                <span>Account</span>
            </a>
        </div>
    </nav>

    <script src="<?= asset('js/app.js') ?>"></script>
    <?php if (isset($extraJs)): foreach((array)$extraJs as $js): ?>
    <script src="<?= asset('js/' . $js) ?>"></script>
    <?php endforeach; endif; ?>
</body>
</html>
