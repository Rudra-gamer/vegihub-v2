<?php include VIEW_PATH . '/layouts/header.php'; 
$statusSteps = ['pending','confirmed','processing','shipped','delivered'];
$currentStepIdx = array_search($order['status'], $statusSteps);
if ($order['status'] === 'cancelled') $currentStepIdx = -1;
$addr = $order['address_snapshot'] ? json_decode($order['address_snapshot'], true) : null;
?>
<div class="container page-shell">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="<?= base_url('orders') ?>" class="btn btn-outline btn-sm">← Back</a>
        <h1 class="section-title" style="font-size:32px;margin:0;">Order #<?= e($order['order_number']) ?></h1>
        <?php $statusBadges = ['pending'=>'badge-warning','confirmed'=>'badge-info','processing'=>'badge-info','shipped'=>'badge-primary','delivered'=>'badge-success','cancelled'=>'badge-danger']; ?>
        <span class="badge <?= $statusBadges[$order['status']] ?? '' ?>"><?= ucfirst($order['status']) ?></span>
    </div>

    <?php if ($order['status'] !== 'cancelled'): ?>
    <div class="timeline-shell mb-3">
            <div class="status-timeline">
                <?php $icons = ['📋','✅','📦','🚚','🏠']; foreach ($statusSteps as $i => $step): ?>
                <div class="status-step <?= $i < $currentStepIdx ? 'completed' : ($i === $currentStepIdx ? 'active' : '') ?>">
                    <div class="step-dot"><?= $i <= $currentStepIdx ? '✓' : $icons[$i] ?></div>
                    <div class="step-label"><?= ucfirst($step) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
    </div>
    <?php endif; ?>

    <div class="order-detail-grid checkout-layout">
        <div>
            <div class="order-panel mb-3">
                <div class="card-header">📦 Items</div>
                <?php foreach ($order['items'] as $item): ?>
                <div style="padding:16px 20px;border-bottom:1px solid var(--border-light);display:flex;gap:14px;align-items:center;">
                    <img src="<?= asset('uploads/products/' . ($item['product_image'] ?? 'placeholder.jpg')) ?>" style="width:70px;height:70px;border-radius:8px;object-fit:cover;background:var(--bg-alt);" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?w=80&h=80&fit=crop'">
                    <div style="flex:1;">
                        <div style="font-weight:600;"><?= e($item['product_name']) ?></div>
                        <div style="font-size:13px;color:var(--text-muted);"><?= $item['quantity'] ?> × <?= format_price($item['price']) ?></div>
                        <?php if (($item['status'] ?? '') === 'delivered' && !empty($item['slug'])): ?>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
                            <?php if (!empty($item['can_review_product'])): ?>
                            <a href="<?= base_url('products/' . $item['slug'] . '#reviews') ?>" class="btn btn-outline btn-sm">Review Product</a>
                            <?php endif; ?>
                            <?php if (!empty($item['can_review_seller'])): ?>
                            <a href="<?= base_url('products/' . $item['slug'] . '#reviews') ?>" class="btn btn-outline btn-sm">Review Vendor</a>
                            <?php endif; ?>
                            <?php if (empty($item['can_review_product']) && empty($item['can_review_seller'])): ?>
                            <span style="font-size:13px;color:var(--text-muted);">Review already submitted</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="font-weight:700;color:var(--primary-dark);"><?= format_price($item['total']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($addr): ?>
            <div class="order-panel">
                <div class="card-header">📍 Delivery Address</div>
                <div class="card-body">
                    <div style="font-weight:600;"><?= e($addr['full_name'] ?? '') ?></div>
                    <div style="color:var(--text-secondary);font-size:14px;margin-top:4px;">
                        <?= e($addr['address_line1'] ?? '') ?><?= !empty($addr['address_line2']) ? ', ' . e($addr['address_line2']) : '' ?><br>
                        <?= e($addr['city'] ?? '') ?>, <?= e($addr['state'] ?? '') ?> - <?= e($addr['pincode'] ?? '') ?><br>
                        📞 <?= e($addr['phone'] ?? '') ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="order-panel">
                <div class="card-header"><strong>Payment Summary</strong></div>
                <div class="card-body">
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;"><span class="text-secondary">Subtotal</span><span><?= format_price($order['subtotal']) ?></span></div>
                    <?php if ($order['discount'] > 0): ?>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;"><span style="color:var(--success);">Discount</span><span style="color:var(--success);">-<?= format_price($order['discount']) ?></span></div>
                    <?php endif; ?>
                    <div style="display:flex;justify-content:space-between;margin-bottom:8px;"><span class="text-secondary">Delivery</span><span><?= $order['delivery_fee'] > 0 ? format_price($order['delivery_fee']) : 'FREE' ?></span></div>
                    <div style="border-top:2px solid var(--border);padding-top:12px;display:flex;justify-content:space-between;">
                        <span style="font-weight:700;">Total</span>
                        <span style="font-size:20px;font-weight:800;color:var(--primary-dark);"><?= format_price($order['total']) ?></span>
                    </div>
                    <div style="margin-top:12px;padding:12px;background:var(--bg-alt);border-radius:var(--radius-sm);font-size:13px;">
                        <div><strong>Payment:</strong> <?= $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Online (Razorpay)' ?></div>
                        <div><strong>Status:</strong> <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning' ?>"><?= ucfirst($order['payment_status']) ?></span></div>
                    </div>

                    <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                    <form method="POST" action="<?= base_url('orders/' . $order['id'] . '/cancel') ?>" style="margin-top:16px;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to cancel this order?')">Cancel Order</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
