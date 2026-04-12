<?php
class CartController extends Controller {
    
    public function index() {
        $this->requireAuth();
        $cart = new Cart();
        $items = $cart->getCartItems($_SESSION['user_id']);
        $totals = $cart->getCartTotal($_SESSION['user_id']);
        $deliveryFee = $totals['subtotal'] >= 500 ? 0 : 15;

        $this->view('cart/index', [
            'pageTitle' => 'Your Cart - Vegihub',
            'currentPage' => 'cart',
            'items' => $items,
            'totals' => $totals,
            'deliveryFee' => $deliveryFee,
            'extraCss' => ['checkout-flow.css'],
        ]);
    }

    public function add() {
        $this->validateCsrf();

        if (!is_logged_in()) {
            $this->json(['success' => false, 'message' => 'Please login to add items to cart.'], 401);
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        $product = new Product();
        $p = $product->find($productId);
        if (!$p || $p['status'] !== 'active') {
            $this->json(['success' => false, 'message' => 'Product not available.']);
        }

        if ($p['stock'] < $quantity) {
            $this->json(['success' => false, 'message' => 'Not enough stock available.']);
        }

        $cart = new Cart();
        $existing = $cart->findItem($_SESSION['user_id'], $productId);
        $finalQty = $quantity + (int)($existing['quantity'] ?? 0);
        if ($finalQty > (int)$p['stock']) {
            $this->json(['success' => false, 'message' => 'Only ' . $p['stock'] . ' item(s) available right now.']);
        }

        $cart->addItem($_SESSION['user_id'], $productId, $quantity);
        $count = $cart->getCount($_SESSION['user_id']);
        $_SESSION['cart_count'] = $count;

        $this->json(['success' => true, 'message' => 'Added to cart! 🛒', 'cart_count' => $count]);
    }

    public function update() {
        $this->validateCsrf();

        if (!is_logged_in()) $this->json(['success' => false, 'message' => 'Please login first.'], 401);
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        $product = (new Product())->find($productId);
        if (!$product || $product['status'] !== 'active') {
            $this->json(['success' => false, 'message' => 'Product is no longer available.'], 404);
        }

        if ($quantity > (int)$product['stock']) {
            $this->json([
                'success' => false,
                'message' => 'Only ' . $product['stock'] . ' item(s) available right now.',
                'available_stock' => (int)$product['stock'],
            ], 422);
        }

        $cart = new Cart();
        $cart->updateQuantity($_SESSION['user_id'], $productId, $quantity);
        $totals = $cart->getCartTotal($_SESSION['user_id']);
        $deliveryFee = $totals['subtotal'] >= 500 ? 0 : 15;
        $count = $cart->getCount($_SESSION['user_id']);
        $_SESSION['cart_count'] = $count;

        $this->json([
            'success' => true,
            'subtotal' => $totals['subtotal'],
            'total_qty' => $totals['total_qty'],
            'delivery_fee' => $deliveryFee,
            'total' => $totals['subtotal'] + $deliveryFee,
            'cart_count' => $count
        ]);
    }

    public function remove() {
        $this->validateCsrf();

        if (!is_logged_in()) $this->json(['success' => false, 'message' => 'Please login first.'], 401);
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $cart = new Cart();
        $cart->removeItem($_SESSION['user_id'], $productId);
        $totals = $cart->getCartTotal($_SESSION['user_id']);
        $deliveryFee = $totals['subtotal'] >= 500 ? 0 : 15;
        $count = $cart->getCount($_SESSION['user_id']);
        $_SESSION['cart_count'] = $count;

        $this->json([
            'success' => true,
            'subtotal' => $totals['subtotal'],
            'total_qty' => $totals['total_qty'],
            'delivery_fee' => $deliveryFee,
            'total' => $totals['subtotal'] + $deliveryFee,
            'cart_count' => $count
        ]);
    }

    public function clear() {
        if (!is_logged_in()) $this->json(['success' => false]);
        $cart = new Cart();
        $cart->clearCart($_SESSION['user_id']);
        $_SESSION['cart_count'] = 0;
        flash('success', 'Cart cleared.');
        redirect(base_url('cart'));
    }

    public function count() {
        $count = is_logged_in() ? (new Cart())->getCount($_SESSION['user_id']) : 0;
        $this->json(['count' => $count]);
    }
}
