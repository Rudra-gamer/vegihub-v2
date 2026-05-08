
document.addEventListener('DOMContentLoaded', function() {
    initCartPage();
});

function initCartPage() {
    document.querySelectorAll('.cart-qty-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const row = this.closest('.cart-item');
            const productId = row.dataset.productId;
            const input = row.querySelector('.cart-qty-input');
            let qty = parseInt(input.value) || 1;

            if (this.dataset.action === 'decrease') qty = Math.max(1, qty - 1);
            else qty = Math.min(99, qty + 1);
            
            input.value = qty;
            await updateCartItem(productId, qty, row);
        });
    });

    document.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('change', async function() {
            const row = this.closest('.cart-item');
            const productId = row.dataset.productId;
            const min = parseInt(this.min, 10) || 1;
            const max = parseInt(this.max, 10) || 99;
            const qty = Math.max(min, Math.min(max, parseInt(this.value, 10) || min));
            this.value = qty;
            await updateCartItem(productId, qty, row);
        });
    });

    document.querySelectorAll('.cart-remove-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Remove this item from cart?')) return;
            const row = this.closest('.cart-item');
            const productId = row.dataset.productId;
            await removeCartItem(productId, row);
        });
    });
}

async function updateCartItem(productId, qty, row) {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
    try {
        const res = await fetch(baseUrl + '/cart/update', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `product_id=${productId}&quantity=${qty}&_csrf_token=${getCSRF()}`
        });
        const data = await res.json();
        if (data.success) {
            updateCartSummary(data);
            const price = parseFloat(row.dataset.price);
            row.querySelector('.item-total').textContent = '₹' + (price * qty).toFixed(2);
            updateCartBadge(data.cart_count);
        } else {
            showToast(data.message || 'Unable to update cart item', 'error');
            const input = row.querySelector('.cart-qty-input');
            if (data.available_stock !== undefined) {
                input.max = data.available_stock;
                input.value = Math.max(1, data.available_stock);
            }
        }
    } catch(e) {
        console.error(e);
        showToast('Unable to update cart right now', 'error');
    }
}

async function removeCartItem(productId, row) {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
    try {
        const res = await fetch(baseUrl + '/cart/remove', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `product_id=${productId}&_csrf_token=${getCSRF()}`
        });
        const data = await res.json();
        if (data.success) {
            row.style.animation = 'slideDown 0.3s ease reverse';
            setTimeout(() => {
                row.remove();
                updateCartSummary(data);
                updateCartBadge(data.cart_count);
                if (data.cart_count === 0) location.reload();
            }, 300);
        } else {
            showToast(data.message || 'Unable to remove item', 'error');
        }
    } catch(e) {
        console.error(e);
        showToast('Unable to update cart right now', 'error');
    }
}

function updateCartSummary(data) {
    const subtotalEl = document.getElementById('cart-subtotal');
    const totalEl = document.getElementById('cart-total');
    const deliveryEl = document.getElementById('cart-delivery');
    const quantityEl = document.getElementById('cart-total-qty');
    const freeDeliveryNote = document.getElementById('cart-free-delivery-note');
    
    if (subtotalEl) subtotalEl.textContent = '₹' + parseFloat(data.subtotal).toFixed(2);
    if (totalEl) totalEl.textContent = '₹' + parseFloat(data.total).toFixed(2);
    if (quantityEl) quantityEl.textContent = parseInt(data.total_qty || 0, 10);
    if (deliveryEl) {
        deliveryEl.textContent = data.delivery_fee > 0 ? '₹' + parseFloat(data.delivery_fee).toFixed(2) : 'FREE';
        deliveryEl.style.color = data.delivery_fee > 0 ? '' : '#40916C';
    }
    if (freeDeliveryNote) {
        const remaining = Math.max(0, 500 - parseFloat(data.subtotal || 0));
        freeDeliveryNote.textContent = remaining > 0
            ? 'Add ₹' + remaining.toFixed(2) + ' more for free delivery.'
            : 'You have unlocked free delivery.';
    }
}

function getCSRF() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function updateCartBadge(count) {
    document.querySelectorAll('.cart-badge, .mobile-badge').forEach(el => {
        el.textContent = count;
        el.style.display = count > 0 ? '' : 'none';
    });
}
