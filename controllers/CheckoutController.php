<?php
class CheckoutController extends Controller {

    private function getCheckoutProductIds(array $items) {
        return array_map(function ($item) {
            return (int)$item['product_id'];
        }, $items);
    }

    private function clearPurchasedCartItems($userId, array $items) {
        (new Cart())->clearSelectedItems($userId, $this->getCheckoutProductIds($items));
        $_SESSION['cart_count'] = (new Cart())->getCount($userId);
    }

    public function index() {
        $this->requireAuth();
        $cart = new Cart();
        $items = $cart->getCartItems($_SESSION['user_id']);
        
        if (empty($items)) {
            flash('warning', 'Your cart is empty.');
            redirect(base_url('products'));
        }

        $address = new Address();
        $addresses = $address->getUserAddresses($_SESSION['user_id']);
        $totals = $cart->getCartTotal($_SESSION['user_id']);
        $deliveryFee = $totals['subtotal'] >= 500 ? 0 : 15;

        $this->view('checkout/index', [
            'pageTitle' => 'Checkout - Vegihub',
            'items' => $items,
            'addresses' => $addresses,
            'totals' => $totals,
            'deliveryFee' => $deliveryFee,
            'couponDiscount' => $_SESSION['checkout_discount'] ?? 0,
            'couponCode' => $_SESSION['checkout_coupon'] ?? '',
            'currentPage' => 'checkout',
            'extraCss' => ['checkout-flow.css'],
            'extraJs' => ['checkout.js'],
        ]);
    }

    public function applyCoupon() {
        $this->validateCsrf();

        if (!is_logged_in()) $this->json(['success' => false, 'message' => 'Login required.']);
        
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        $cart = new Cart();
        $totals = $cart->getCartTotal($_SESSION['user_id']);
        
        $coupon = new Coupon();
        $result = $coupon->validateCoupon($code, $totals['subtotal']);
        
        if ($result['valid']) {
            $_SESSION['checkout_discount'] = $result['discount'];
            $_SESSION['checkout_coupon'] = $code;
            $_SESSION['checkout_coupon_id'] = $result['coupon']['id'];
        } else {
            unset($_SESSION['checkout_discount'], $_SESSION['checkout_coupon'], $_SESSION['checkout_coupon_id']);
        }
        
        $this->json(['success' => $result['valid'], 'message' => $result['message']]);
    }

    public function removeCoupon() {
        $this->validateCsrf();

        unset($_SESSION['checkout_discount'], $_SESSION['checkout_coupon'], $_SESSION['checkout_coupon_id']);
        $this->json(['success' => true, 'message' => 'Coupon removed.']);
    }

    public function createOrder() {
        $this->requireAuth();
        $this->validateCsrf();
        
        $addressId = (int)($_POST['address_id'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'razorpay';
        
        if (!$addressId) {
            $this->json(['success' => false, 'message' => 'Please select a delivery address.']);
        }

        if (!in_array($paymentMethod, ['razorpay', 'cod'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid payment method.'], 422);
        }

        $addressModel = new Address();
        $addr = $addressModel->find($addressId);
        if (!$addr || $addr['user_id'] != $_SESSION['user_id']) {
            $this->json(['success' => false, 'message' => 'Invalid address.']);
        }

        if (empty(trim((string)($addr['phone'] ?? '')))) {
            $this->json(['success' => false, 'message' => 'Delivery address must include a phone number.'], 422);
        }

        $cart = new Cart();
        $items = $cart->getCartItems($_SESSION['user_id']);
        if (empty($items)) {
            $this->json(['success' => false, 'message' => 'Cart is empty.']);
        }

        foreach ($items as $item) {
            if (($item['status'] ?? '') !== 'active') {
                $this->json(['success' => false, 'message' => $item['name'] . ' is no longer available.'], 409);
            }

            if ((int)$item['stock'] < (int)$item['quantity']) {
                $this->json(['success' => false, 'message' => 'Not enough stock for ' . $item['name'] . '.'], 409);
            }
        }

        $totals = $cart->getCartTotal($_SESSION['user_id']);
        $deliveryFee = $totals['subtotal'] >= 500 ? 0 : 15;
        $discount = 0.0;
        $couponId = $_SESSION['checkout_coupon_id'] ?? null;
        $couponCode = $_SESSION['checkout_coupon'] ?? null;

        if ($couponId && $couponCode) {
            $couponModel = new Coupon();
            $couponResult = $couponModel->validateCoupon($couponCode, $totals['subtotal']);
            if ($couponResult['valid'] && (int)$couponResult['coupon']['id'] === (int)$couponId) {
                $discount = (float)$couponResult['discount'];
            } else {
                unset($_SESSION['checkout_discount'], $_SESSION['checkout_coupon'], $_SESSION['checkout_coupon_id']);
                $couponId = null;
                $couponCode = null;
            }
        }

        $total = $totals['subtotal'] - $discount + $deliveryFee;
        if ($total <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid order total.'], 422);
        }

        $orderNumber = generate_order_number();

        $addressSnapshot = json_encode($addr);

        $orderData = [
            'order_number' => $orderNumber,
            'user_id' => $_SESSION['user_id'],
            'address_id' => $addressId,
            'address_snapshot' => $addressSnapshot,
            'subtotal' => $totals['subtotal'],
            'discount' => $discount,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'coupon_id' => $couponId,
            'coupon_code' => $couponCode,
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
            'status' => $paymentMethod === 'cod' ? 'confirmed' : 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $orderItems = [];
        foreach ($items as $item) {
            $orderItems[] = [
                'product_id' => $item['product_id'],
                'seller_id' => $item['seller_id'],
                'product_name' => $item['name'],
                'product_image' => $item['image'],
                'price' => $item['sale_price'] ?: $item['price'],
                'quantity' => $item['quantity'],
                'total' => ($item['sale_price'] ?: $item['price']) * $item['quantity'],
            ];
        }

        try {
            $orderModel = new Order();
            
            if ($paymentMethod === 'razorpay') {
                $config = getRazorpayConfig();
                if (empty($config['key_id']) || empty($config['key_secret']) || $config['key_id'] === 'your_razorpay_key' || $config['key_secret'] === 'your_razorpay_secret') {
                    $this->json(['success' => false, 'message' => 'Online payment is not configured yet. Please use Cash on Delivery or update Razorpay keys.'], 503);
                }

                $razorpay = getRazorpayClient();
                $razorpayOrder = $razorpay->order->create([
                    'amount' => (int)($total * 100),
                    'currency' => 'INR',
                    'receipt' => $orderNumber,
                ]);
                $orderData['razorpay_order_id'] = $razorpayOrder->id;
            }

            $orderId = $orderModel->createOrder($orderData, $orderItems);

            $user = current_user();
            $response = [
                'success' => true,
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'customer_name' => $user['name'],
                'customer_email' => $user['email'],
                'customer_phone' => $addr['phone'] ?? '',
            ];

            if ($paymentMethod === 'razorpay') {
                $response['razorpay_key'] = $config['key_id'];
                $response['razorpay_order_id'] = $orderData['razorpay_order_id'];
                $response['amount'] = (int)($total * 100);
            } else {
                $this->clearPurchasedCartItems($_SESSION['user_id'], $items);
                unset($_SESSION['checkout_discount'], $_SESSION['checkout_coupon'], $_SESSION['checkout_coupon_id']);

                if (!empty($orderData['coupon_id'])) {
                    $couponModel = new Coupon();
                    $couponModel->incrementUsage($orderData['coupon_id']);
                }
            }

            if ($paymentMethod === 'cod') {
                try {
                    $mailer = new Mailer();
                    $mailer->sendOrderConfirmation($user['email'], $user['name'], array_merge($orderData, ['id' => $orderId]));
                } catch(Exception $e) { error_log($e->getMessage()); }
            }

            $this->json($response);
        } catch (Exception $e) {
            error_log("Order creation failed: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to create order. ' . (APP_DEBUG ? $e->getMessage() : 'Please try again.')]);
        }
    }

    public function verifyPayment() {
        $this->requireAuth();
        $this->validateCsrf();
        
        $orderId = (int)($_POST['order_id'] ?? 0);
        $razorpayPaymentId = $_POST['razorpay_payment_id'] ?? '';
        $razorpayOrderId = $_POST['razorpay_order_id'] ?? '';
        $razorpaySignature = $_POST['razorpay_signature'] ?? '';

        try {
            $orderModel = new Order();
            $order = $orderModel->find($orderId);
            if (
                !$order ||
                (int)$order['user_id'] !== (int)$_SESSION['user_id'] ||
                $order['payment_method'] !== 'razorpay' ||
                $order['razorpay_order_id'] !== $razorpayOrderId
            ) {
                $this->json(['success' => false, 'message' => 'Invalid payment request.'], 422);
            }

            $config = getRazorpayConfig();
            $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $config['key_secret']);
            
            if (hash_equals($expectedSignature, $razorpaySignature)) {
                $orderModel->markPaid($orderId, [
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'razorpay_signature' => $razorpaySignature,
                ]);

                $order = $orderModel->find($orderId);
                $orderItems = $orderModel->getWithItems($orderId)['items'] ?? [];
                $this->clearPurchasedCartItems($_SESSION['user_id'], $orderItems);
                unset($_SESSION['checkout_discount'], $_SESSION['checkout_coupon'], $_SESSION['checkout_coupon_id']);

                if (!empty($order['coupon_id'])) {
                    $couponModel = new Coupon();
                    $couponModel->incrementUsage($order['coupon_id']);
                }

                $user = current_user();
                try {
                    $mailer = new Mailer();
                    $mailer->sendOrderConfirmation($user['email'], $user['name'], $order);
                } catch(Exception $e) { error_log($e->getMessage()); }

                $this->json(['success' => true]);
            } else {
                $orderModel->markFailed($orderId, 'Payment signature verification failed.');
                $this->json(['success' => false, 'message' => 'Payment verification failed.']);
            }
        } catch(Exception $e) {
            error_log('Payment verification error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Verification error.']);
        }
    }

    public function paymentFailed() {
        $this->requireAuth();
        $this->validateCsrf();

        $orderId = (int)($_POST['order_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Payment cancelled or failed.');
        if (!$orderId) {
            $this->json(['success' => false, 'message' => 'Order not found.'], 422);
        }

        try {
            $orderModel = new Order();
            $order = $orderModel->find($orderId);
            if (
                !$order ||
                (int)$order['user_id'] !== (int)$_SESSION['user_id'] ||
                $order['payment_method'] !== 'razorpay'
            ) {
                $this->json(['success' => false, 'message' => 'Invalid payment failure request.'], 422);
            }

            $orderModel->markFailed($orderId, $reason);
            $this->json(['success' => true]);
        } catch (Exception $e) {
            error_log('Payment failure update error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to update payment status.'], 500);
        }
    }

    public function success($orderId) {
        $this->requireAuth();
        $orderModel = new Order();
        $order = $orderModel->getWithItems($orderId);
        if (!$order || $order['user_id'] != $_SESSION['user_id']) redirect(base_url());

        $this->view('checkout/success', [
            'pageTitle' => 'Order Confirmed - Vegihub',
            'currentPage' => 'checkout',
            'order' => $order,
        ]);
    }
}
