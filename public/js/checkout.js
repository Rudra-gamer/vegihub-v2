
document.addEventListener('DOMContentLoaded', function() {
    initCheckout();
});

function initCheckout() {
    const payBtn = document.getElementById('pay-btn');
    if (!payBtn || payBtn.dataset.checkoutBound === 'true') return;
    payBtn.dataset.checkoutBound = 'true';
    const paymentOptions = document.querySelectorAll('.payment-option');

    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            paymentOptions.forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            const input = this.querySelector('input[type="radio"]');
            if (input) input.checked = true;
            resetPayButton(payBtn);
        });
    });

    payBtn.addEventListener('click', async function() {
        const addressId = document.querySelector('input[name="address_id"]:checked');
        if (!addressId) {
            showToast('Please select a delivery address', 'error');
            return;
        }

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!paymentMethod) {
            showToast('Please select a payment method', 'error');
            return;
        }

        payBtn.disabled = true;
        payBtn.innerHTML = '<span class="spinner" style="width:20px;height:20px;border-width:3px;"></span> Processing...';

        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
        let orderId = null;

        try {
            const data = await postCheckoutForm('/checkout/create-order', {
                address_id: addressId.value,
                payment_method: paymentMethod.value,
                coupon_code: document.getElementById('coupon-code')?.value || '',
                _csrf_token: getCSRF()
            });
            orderId = data.order_id || null;

            if (!data.success) {
                showToast(data.message || 'Failed to create order', 'error');
                resetPayButton(payBtn);
                return;
            }

            if (paymentMethod.value === 'cod') {
                window.location.href = baseUrl + '/checkout/success/' + data.order_id;
                return;
            }

            if (typeof Razorpay === 'undefined') {
                await reportPaymentFailure(data.order_id, 'Razorpay checkout could not load.');
                showToast('Online payment could not load. Please try again or use Cash on Delivery.', 'error');
                resetPayButton(payBtn);
                return;
            }

            const options = {
                key: data.razorpay_key,
                amount: data.amount,
                currency: 'INR',
                name: 'Vegihub',
                description: 'Order #' + data.order_number,
                order_id: data.razorpay_order_id,
                handler: async function(response) {
                    const verifyData = await postCheckoutForm('/checkout/verify-payment', {
                        order_id: data.order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature,
                        _csrf_token: getCSRF()
                    });
                    if (verifyData.success) {
                        window.location.href = baseUrl + '/checkout/success/' + data.order_id;
                    } else {
                        await reportPaymentFailure(data.order_id, verifyData.message || 'Payment verification failed');
                        showToast(verifyData.message || 'Payment verification failed', 'error');
                        resetPayButton(payBtn);
                    }
                },
                prefill: {
                    name: data.customer_name || '',
                    email: data.customer_email || '',
                    contact: data.customer_phone || ''
                },
                theme: { color: '#2D6A4F' },
                modal: {
                    ondismiss: async function() {
                        await reportPaymentFailure(data.order_id, 'Payment cancelled by customer.');
                        resetPayButton(payBtn);
                        showToast('Payment cancelled', 'warning');
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.on('payment.failed', async function(response) {
                const reason = response?.error?.description || 'Payment failed';
                await reportPaymentFailure(data.order_id, reason);
                showToast(reason, 'error');
                resetPayButton(payBtn);
            });
            rzp.open();
        } catch(e) {
            console.error(e);
            if (orderId) {
                await reportPaymentFailure(orderId, 'Checkout request failed before payment could complete.');
            }
            showToast('Something went wrong', 'error');
            resetPayButton(payBtn);
        }
    });

    const couponBtn = document.getElementById('apply-coupon-btn');
    if (couponBtn) {
        couponBtn.addEventListener('click', async function() {
            try {
                const code = document.getElementById('coupon-code').value.trim();
                if (!code) { showToast('Enter a coupon code', 'warning'); return; }

                const data = await postCheckoutForm('/checkout/apply-coupon', {
                    coupon_code: code,
                    _csrf_token: getCSRF()
                });
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) location.reload();
            } catch (e) {
                console.error(e);
                showToast('Unable to apply coupon right now', 'error');
            }
        });
    }
}

async function reportPaymentFailure(orderId, reason) {
    if (!orderId) return;

    try {
        await postCheckoutForm('/checkout/payment-failed', {
            order_id: orderId,
            reason: reason,
            _csrf_token: getCSRF()
        });
    } catch (e) {
        console.error(e);
    }
}

function resetPayButton(payBtn) {
    payBtn.disabled = false;
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
    payBtn.innerHTML = paymentMethod === 'cod' ? 'Place Order' : 'Pay Securely';
}

function getCSRF() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function postCheckoutForm(path, payload) {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
    const response = await fetch(baseUrl + path, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams(payload).toString()
    });

    const contentType = response.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
        throw new Error('Expected JSON response from checkout endpoint.');
    }

    return response.json();
}
