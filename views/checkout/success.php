<?php include VIEW_PATH . '/layouts/header.php'; ?>
<div class="container" style="padding:60px 20px;text-align:center;">
    <div style="max-width:500px;margin:0 auto;">
        <div style="font-size:72px;margin-bottom:16px;">🎉</div>
        <h1 style="font-size:32px;font-weight:800;color:var(--primary-dark);margin-bottom:8px;">Order Confirmed!</h1>
        <p style="color:var(--text-secondary);font-size:16px;margin-bottom:32px;">Thank you for your order. We'll start preparing it right away!</p>
        
        <div class="card" style="text-align:left;margin-bottom:24px;">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                    <span class="text-secondary">Order Number</span>
                    <span class="fw-700" style="color:var(--primary);">#<?= e($order['order_number']) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                    <span class="text-secondary">Payment Method</span>
                    <span class="fw-600"><?= $order['payment_method'] === 'cod' ? '💵 Cash on Delivery' : '💳 Online Payment' ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                    <span class="text-secondary">Status</span>
                    <span class="badge badge-success"><?= ucfirst($order['status']) ?></span>
                </div>
                <div style="border-top:2px solid var(--border);padding-top:12px;display:flex;justify-content:space-between;">
                    <span style="font-size:18px;font-weight:700;">Total Paid</span>
                    <span style="font-size:22px;font-weight:800;color:var(--primary-dark);"><?= format_price($order['total']) ?></span>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <a href="<?= base_url('orders/' . $order['id']) ?>" class="btn btn-primary btn-lg">📦 Track Order</a>
            <a href="<?= base_url('products') ?>" class="btn btn-outline btn-lg">🛍️ Continue Shopping</a>
        </div>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
