<?php include VIEW_PATH . '/layouts/header.php'; ?>

<div class="container flow-shell">
    <div class="flow-hero">
        <div class="flow-hero-card">
            <span class="flow-eyebrow">Basket overview</span>
            <h1 class="flow-title">Everything in your basket, ready for checkout</h1>
            <p class="flow-subtitle">A more modern marketplace cart with clearer totals, cleaner quantity controls, and a checkout jump that matches the backend state.</p>
            <div class="flow-stats">
                <div class="flow-stat">
                    <strong><?= (int)$totals['total_qty'] ?></strong>
                    <span>Items selected</span>
                </div>
                <div class="flow-stat">
                    <strong><?= $deliveryFee > 0 ? format_price($deliveryFee) : 'FREE' ?></strong>
                    <span>Delivery charge</span>
                </div>
                <div class="flow-stat">
                    <strong><?= format_price($totals['subtotal'] + $deliveryFee) ?></strong>
                    <span>Current total</span>
                </div>
            </div>
            <div class="flow-highlights">
                <div class="flow-highlight"><strong>Fast edits</strong><span>Quantity updates recalculate totals instantly.</span></div>
                <div class="flow-highlight"><strong>Delivery clarity</strong><span>Free delivery unlocks automatically above ₹500.</span></div>
                <div class="flow-highlight"><strong>Checkout ready</strong><span>Move to payment without losing cart accuracy.</span></div>
            </div>
        </div>

        <aside class="trust-card">
            <h3>Why shoppers convert here</h3>
            <div class="trust-list">
                <div class="trust-item"><strong>Farmer-first sourcing</strong><br><span>Fresh produce from trusted local sellers.</span></div>
                <div class="trust-item"><strong>Secure checkout</strong><br><span>Razorpay verification with clear payment states.</span></div>
                <div class="trust-item"><strong>Real support</strong><br><span>Call 7064841325 or write to rudranahak1000@gmail.com.</span></div>
            </div>
        </aside>
    </div>

    <?php if (empty($items)): ?>
    <div class="empty-state">
        <div class="empty-icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added any vegetables yet!</p>
        <a href="<?= base_url('products') ?>" class="btn btn-primary btn-lg">🛍️ Start Shopping</a>
    </div>
    <?php else: ?>
    <div class="flow-layout">
        <div class="flow-panel">
            <div class="card-header">
                <span><?= $totals['item_count'] ?> items in your cart</span>
                <form method="POST" action="<?= base_url('cart/clear') ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm" style="color:var(--danger);background:none;border:none;font-weight:600;">Clear All</button>
                </form>
            </div>

            <?php foreach ($items as $item): 
                $itemPrice = $item['sale_price'] ?: $item['price'];
                $itemTotal = $itemPrice * $item['quantity'];
            ?>
            <div class="cart-item cart-item-modern" data-product-id="<?= $item['product_id'] ?>" data-price="<?= $itemPrice ?>">
                <a href="<?= base_url('products/' . $item['slug']) ?>">
                    <img src="<?= asset('uploads/products/' . ($item['image'] ?? 'placeholder.jpg')) ?>" 
                         alt="<?= e($item['name']) ?>" 
                         style="width:90px;height:90px;border-radius:var(--radius-sm);object-fit:cover;background:var(--bg-alt);"
                         onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?w=100&h=100&fit=crop'">
                </a>
                <div style="flex:1;min-width:0;">
                    <a href="<?= base_url('products/' . $item['slug']) ?>" style="font-weight:600;font-size:16px;color:var(--text);"><?= e($item['name']) ?></a>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:2px;">by <?= e($item['seller_name']) ?> • <?= $item['unit'] ?></div>
                    <div style="display:flex;align-items:center;gap:12px;margin-top:10px;">
                        <button class="cart-qty-btn qty-btn" data-action="decrease" style="width:32px;height:32px;font-size:16px;">−</button>
                        <input type="number" class="cart-qty-input qty-value" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" style="width:50px;height:32px;font-size:14px;">
                        <button class="cart-qty-btn qty-btn" data-action="increase" style="width:32px;height:32px;font-size:16px;">+</button>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="item-total" style="font-size:18px;font-weight:700;color:var(--primary-dark);"><?= format_price($itemTotal) ?></div>
                    <?php if ($item['sale_price']): ?>
                    <div style="font-size:13px;color:var(--text-muted);text-decoration:line-through;"><?= format_price($item['price'] * $item['quantity']) ?></div>
                    <?php endif; ?>
                    <button class="cart-remove-btn" style="background:none;border:none;color:var(--danger);font-size:13px;font-weight:600;cursor:pointer;margin-top:8px;">Remove</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-panel">
            <div class="card-header"><strong>Cart Summary</strong><span class="panel-meta">Review pricing before checkout</span></div>
            <div class="card-body">
                <div class="summary-mini-banner">
                    <strong><?= (int)$totals['total_qty'] ?> units</strong>
                    <span>Across <?= (int)$totals['item_count'] ?> selections</span>
                </div>
                <div class="summary-totals">
                <div class="summary-total-row">
                    <span class="text-secondary">Subtotal (<span id="cart-total-qty"><?= (int)$totals['total_qty'] ?></span> items)</span>
                    <span class="fw-600" id="cart-subtotal"><?= format_price($totals['subtotal']) ?></span>
                </div>
                <div class="summary-total-row">
                    <span class="text-secondary">Delivery</span>
                    <span class="fw-600" id="cart-delivery" style="<?= $deliveryFee == 0 ? 'color:var(--success)' : '' ?>">
                        <?= $deliveryFee > 0 ? format_price($deliveryFee) : 'FREE' ?>
                    </span>
                </div>
                </div>
                <?php if ($deliveryFee > 0): ?>
                <div class="highlight-note" id="cart-free-delivery-note">
                    Add <?= format_price(500 - $totals['subtotal']) ?> more for free delivery.
                </div>
                <?php else: ?>
                <div class="highlight-note" id="cart-free-delivery-note">You have unlocked free delivery.</div>
                <?php endif; ?>
                <div class="summary-grand-total">
                    <span>Total</span>
                    <span id="cart-total"><?= format_price($totals['subtotal'] + $deliveryFee) ?></span>
                </div>
                <a href="<?= base_url('checkout') ?>" class="btn btn-accent btn-block btn-lg mt-3">Proceed to Checkout</a>
                <a href="<?= base_url('products') ?>" class="btn btn-outline btn-block mt-2">Continue Shopping</a>
            </div>
        </div>
    </div>

    <div class="checkout-trust-grid">
        <div class="trust-item">
            <strong>Picked with care</strong>
            Seasonal produce sourced from verified sellers.
        </div>
        <div class="trust-item">
            <strong>Easy checkout</strong>
            Cart totals stay in sync while you update quantities.
        </div>
        <div class="testimonial-item">
            <strong>"The vegetables actually feel farm-fresh."</strong>
            Priya, repeat customer
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="<?= asset('js/cart.js') ?>"></script>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
