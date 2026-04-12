<?php include VIEW_PATH . '/layouts/header.php'; ?>
<div class="container flow-shell">
    <div class="flow-hero">
        <div class="flow-hero-card">
            <span class="flow-eyebrow">Checkout desk</span>
            <h1 class="flow-title">Review, pay, and confirm in one clean flow</h1>
            <p class="flow-subtitle">A marketplace-style checkout with clearer address selection, stronger payment states, and an order summary that stays visible while you confirm your purchase.</p>
            <div class="flow-stats">
                <div class="flow-stat">
                    <strong><?= format_price($totals['subtotal']) ?></strong>
                    <span>Basket subtotal</span>
                </div>
                <div class="flow-stat">
                    <strong><?= $deliveryFee > 0 ? format_price($deliveryFee) : 'FREE' ?></strong>
                    <span>Delivery</span>
                </div>
                <div class="flow-stat">
                    <strong><?= format_price($totals['subtotal'] - $couponDiscount + $deliveryFee) ?></strong>
                    <span>Payable now</span>
                </div>
            </div>
            <div class="flow-highlights">
                <div class="flow-highlight"><strong>Delivery window</strong><span>Same-day dispatch on active local inventory.</span></div>
                <div class="flow-highlight"><strong>Payment options</strong><span>UPI, cards, wallets, or cash on delivery.</span></div>
                <div class="flow-highlight"><strong>Protection</strong><span>Server-side payment verification before confirmation.</span></div>
            </div>
        </div>

        <aside class="trust-card">
            <h3>Order confidence</h3>
            <div class="trust-list">
                <div class="trust-item"><strong>Verified payments</strong><br><span>Online payments are confirmed only after signature verification on the server.</span></div>
                <div class="trust-item"><strong>Live totals</strong><br><span>Discounts, delivery fee, and final amount stay aligned with backend order data.</span></div>
                <div class="trust-item"><strong>Support line</strong><br><span>7064841325<br>rudranahak1000@gmail.com</span></div>
            </div>
        </aside>
    </div>

    <div class="flow-layout checkout-layout">
        <div>
            <div class="flow-panel mb-3">
                <div class="card-header"><span>Delivery Address</span><span class="panel-meta">Choose where this order should arrive</span></div>
                <div class="card-body">
                    <?php if (empty($addresses)): ?>
                    <div class="alert alert-warning">You don't have any saved addresses. Please add one.</div>
                    <form method="POST" action="<?= base_url('profile/addresses/add') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="redirect" value="checkout">
                        <div class="form-row">
                            <div class="form-group"><label>Full Name</label><input name="full_name" class="form-control" required value="<?= e(current_user()['name']) ?>"></div>
                            <div class="form-group"><label>Phone</label><input name="phone" class="form-control" required></div>
                        </div>
                        <div class="form-group"><label>Address Line 1</label><input name="address_line1" class="form-control" required></div>
                        <div class="form-group"><label>Address Line 2</label><input name="address_line2" class="form-control"></div>
                        <div class="form-row">
                            <div class="form-group"><label>City</label><input name="city" class="form-control" required></div>
                            <div class="form-group"><label>State</label><input name="state" class="form-control" required></div>
                        </div>
                        <div class="form-group"><label>Pincode</label><input name="pincode" class="form-control" required maxlength="6"></div>
                        <button type="submit" class="btn btn-primary">Save Address</button>
                    </form>
                    <?php else: ?>
                    <div class="address-grid">
                        <?php foreach ($addresses as $i => $addr): ?>
                        <label class="address-option address-option-modern <?= $addr['is_default'] || $i === 0 ? 'active' : '' ?>" onclick="this.querySelector('input').checked=true;document.querySelectorAll('.address-option').forEach(a=>a.classList.remove('active'));this.classList.add('active');">
                            <input type="radio" name="address_id" value="<?= $addr['id'] ?>" <?= $addr['is_default'] || $i === 0 ? 'checked' : '' ?> style="accent-color:var(--primary);margin-top:2px;">
                            <div>
                                <div class="option-title-row"><?= e($addr['full_name']) ?> <span class="badge badge-primary" style="margin-left:6px;"><?= e($addr['label'] ?? 'Home') ?></span></div>
                                <div class="option-copy">
                                    <?= e($addr['address_line1']) ?><?= $addr['address_line2'] ? ', ' . e($addr['address_line2']) : '' ?><br>
                                    <?= e($addr['city']) ?>, <?= e($addr['state']) ?> - <?= e($addr['pincode']) ?><br>
                                    Phone: <?= e($addr['phone']) ?>
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flow-panel mb-3">
                <div class="card-header"><span>Payment Method</span><span class="panel-meta">Select how you want to pay</span></div>
                <div class="card-body">
                    <label class="payment-option payment-option-modern active" style="margin-bottom:12px;">
                        <input type="radio" name="payment_method" value="razorpay" checked style="accent-color:var(--primary);">
                        <div>
                            <div class="option-title-row">Online Payment</div>
                            <div class="option-copy">UPI, cards, net banking, and wallets via Razorpay.</div>
                        </div>
                    </label>
                    <label class="payment-option payment-option-modern">
                        <input type="radio" name="payment_method" value="cod" style="accent-color:var(--primary);">
                        <div>
                            <div class="option-title-row">Cash on Delivery</div>
                            <div class="option-copy">Pay when the order reaches your doorstep.</div>
                        </div>
                    </label>
                    <div class="checkout-note">
                        Online orders are confirmed after payment verification. COD orders can ship before delivery, but final payment collection must be confirmed before completion.
                    </div>
                </div>
            </div>
        </div>

        <div class="summary-panel">
            <div class="card-header"><strong>Order Summary</strong><span class="panel-meta">Final review before confirmation</span></div>
            <div class="card-body">
                <div class="summary-mini-banner">
                    <strong><?= count($items) ?> products</strong>
                    <span>Ready for checkout</span>
                </div>
                <?php foreach ($items as $item): $price = $item['sale_price'] ?: $item['price']; ?>
                <div class="summary-line-item">
                    <img src="<?= asset('uploads/products/' . ($item['image'] ?? 'placeholder.jpg')) ?>" style="width:50px;height:50px;border-radius:8px;object-fit:cover;background:var(--bg-alt);" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?w=60&h=60&fit=crop'">
                    <div class="summary-copy">
                        <div class="summary-item-title"><?= e($item['name']) ?></div>
                        <div class="summary-item-meta"><?= $item['quantity'] ?> × <?= format_price($price) ?></div>
                    </div>
                    <div class="summary-item-price"><?= format_price($price * $item['quantity']) ?></div>
                </div>
                <?php endforeach; ?>

                <div class="summary-coupon-row">
                    <input type="text" id="coupon-code" class="form-control" placeholder="Enter coupon code" style="font-size:13px;padding:10px;" value="<?= e($couponCode) ?>">
                    <button id="apply-coupon-btn" class="btn btn-outline btn-sm" style="white-space:nowrap;">Apply</button>
                </div>

                <div class="summary-total-row">
                    <span class="text-secondary">Subtotal</span><span class="fw-600"><?= format_price($totals['subtotal']) ?></span>
                </div>
                <?php if ($couponDiscount > 0): ?>
                <div class="summary-total-row">
                    <span style="color:var(--success);">Coupon Discount</span><span style="color:var(--success);font-weight:600;">-<?= format_price($couponDiscount) ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-total-row">
                    <span class="text-secondary">Delivery</span>
                    <span class="fw-600" style="<?= $deliveryFee == 0 ? 'color:var(--success)' : '' ?>"><?= $deliveryFee > 0 ? format_price($deliveryFee) : 'FREE 🎉' ?></span>
                </div>
                <div class="summary-grand-total">
                    <span>Total</span>
                    <span><?= format_price($totals['subtotal'] - $couponDiscount + $deliveryFee) ?></span>
                </div>

                <button id="pay-btn" class="btn btn-accent btn-block btn-lg mt-3" <?= empty($addresses) ? 'disabled' : '' ?>>
                    Pay Securely
                </button>
                <p class="summary-legal">
                    Your payment is protected. By placing this order, you agree to the store terms and delivery policy.
                </p>
            </div>
        </div>
    </div>

    <div class="checkout-trust-grid">
        <div class="trust-item">
            <strong>Why shoppers stay</strong>
            Fresh produce, clear pricing, and order updates that actually match payment state.
        </div>
        <div class="trust-item">
            <strong>Contact support</strong>
            Phone: 7064841325<br>Email: rudranahak1000@gmail.com
        </div>
        <div class="testimonial-item">
            <strong>"Checkout finally feels dependable."</strong>
            Asha, Bhubaneswar
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
