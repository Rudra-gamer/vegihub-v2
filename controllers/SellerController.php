<?php
class SellerController extends Controller {
    public function __construct() { }

    private function checkSeller() { $this->requireRole('seller'); }

    private function validateSellerProductInput($categoryId, $price, $salePrice, $stock, $unit) {
        $validUnits = ['kg', 'g', 'piece', 'bunch', 'dozen', 'pack'];
        if ($categoryId <= 0 || !(new Category())->find($categoryId)) {
            throw new Exception('Please choose a valid category.');
        }
        if ($price <= 0) {
            throw new Exception('Price must be greater than 0.');
        }
        if ($salePrice !== null && $salePrice <= 0) {
            throw new Exception('Sale price must be greater than 0.');
        }
        if ($salePrice !== null && $salePrice >= $price) {
            throw new Exception('Sale price must be lower than the base price.');
        }
        if ($stock < 0) {
            throw new Exception('Stock cannot be negative.');
        }
        if (!in_array($unit, $validUnits, true)) {
            throw new Exception('Invalid unit selected.');
        }
    }

    public function dashboard() {
        $this->checkSeller();
        $orderModel = new Order();
        $productModel = new Product();
        $sellerReview = new SellerReview();
        $stats = $orderModel->getSellerStats($_SESSION['user_id']);
        $sellerRating = $sellerReview->getSellerRatingSummary($_SESSION['user_id']);
        $totalProducts = $productModel->count("seller_id = ?", [$_SESSION['user_id']]);
        $recentOrders = $orderModel->getSellerOrders($_SESSION['user_id'], 1, 5);
        $topProducts = $productModel->getTopProductsBySeller($_SESSION['user_id'], 5);
        $lowStockProducts = $productModel->getLowStockBySeller($_SESSION['user_id'], 10, 5);
        $monthlyRevenue = $orderModel->getSellerMonthlyRevenue($_SESSION['user_id'], 6);
        
        $this->view('seller/dashboard', [
            'pageTitle' => 'Seller Dashboard - Vegihub',
            'stats' => $stats,
            'sellerRating' => $sellerRating,
            'totalProducts' => $totalProducts,
            'recentOrders' => $recentOrders['data'],
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'monthlyRevenue' => $monthlyRevenue,
            'extraCss' => ['dashboard.css'],
        ]);
    }

    public function products() {
        $this->checkSeller();
        $product = new Product();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $product->getBySellerPaginated($_SESSION['user_id'], $page);
        $lowStockProducts = $product->getLowStockBySeller($_SESSION['user_id'], 10, 8);
        $this->view('seller/products', [
            'pageTitle' => 'My Products - Vegihub',
            'products' => $result['data'],
            'pagination' => $result,
            'lowStockProducts' => $lowStockProducts,
            'extraCss' => ['dashboard.css'],
        ]);
    }

    public function addProductForm() {
        $this->checkSeller();
        $categories = (new Category())->getActive();
        $this->view('seller/add_product', [
            'pageTitle' => 'Add Product - Vegihub',
            'categories' => $categories,
            'extraCss' => ['dashboard.css'],
        ]);
    }

    public function addProduct() {
        $this->checkSeller();
        $this->validateCsrf();
        
        $name = trim($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $salePrice = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
        $unit = $_POST['unit'] ?? 'kg';
        $stock = (int)($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $shortDesc = trim($_POST['short_description'] ?? '');
        $isOrganic = isset($_POST['is_organic']) ? 1 : 0;

        if (!$name || !$categoryId || $price <= 0) {
            flash('error', 'Please fill in all required fields.');
            back();
        }

        try {
            $this->validateSellerProductInput($categoryId, $price, $salePrice, $stock, $unit);
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            back();
        }

        $image = null;
        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $this->uploadFile($_FILES['image'], 'products');
            if ($image === false) back();
        }

        $product = new Product();
        $slug = generate_slug($name);
        if ($product->findBy('slug', $slug)) $slug .= '-' . uniqid();

        $product->create([
            'seller_id' => $_SESSION['user_id'],
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'short_description' => $shortDesc,
            'price' => $price,
            'sale_price' => $salePrice,
            'unit' => $unit,
            'stock' => $stock,
            'image' => $image,
            'is_organic' => $isOrganic,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logAuditEvent('seller.product_created', ['seller_id' => (int)$_SESSION['user_id'], 'product_name' => $name, 'status' => 'pending']);
        flash('success', 'Product submitted successfully and is pending admin approval.');
        redirect(base_url('seller/products'));
    }

    public function editProductForm($id) {
        $this->checkSeller();
        $product = (new Product())->find($id);
        if (!$product || $product['seller_id'] != $_SESSION['user_id']) {
            flash('error', 'Product not found.');
            redirect(base_url('seller/products'));
        }
        $categories = (new Category())->getActive();
        $this->view('seller/edit_product', [
            'pageTitle' => 'Edit Product - Vegihub',
            'product' => $product,
            'categories' => $categories,
            'extraCss' => ['dashboard.css'],
        ]);
    }

    public function editProduct($id) {
        $this->checkSeller();
        $this->validateCsrf();
        $productModel = new Product();
        $product = $productModel->find($id);
        if (!$product || $product['seller_id'] != $_SESSION['user_id']) {
            flash('error', 'Product not found.');
            redirect(base_url('seller/products'));
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'price' => (float)($_POST['price'] ?? 0),
            'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
            'unit' => $_POST['unit'] ?? 'kg',
            'stock' => (int)($_POST['stock'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'is_organic' => isset($_POST['is_organic']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $this->validateSellerProductInput($data['category_id'], $data['price'], $data['sale_price'], $data['stock'], $data['unit']);
        } catch (Exception $e) {
            flash('error', $e->getMessage());
            back();
        }

        $requestedStatus = $_POST['status'] ?? $product['status'];
        if ($requestedStatus === 'inactive') {
            $data['status'] = 'inactive';
        } elseif ($product['status'] === 'active') {
            $data['status'] = 'active';
        } else {
            $data['status'] = 'pending';
        }

        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = $this->uploadFile($_FILES['image'], 'products');
            if ($image) {
                if ($product['image']) $this->deleteFile($product['image'], 'products');
                $data['image'] = $image;
            }
        }

        $productModel->update($id, $data);
        $this->logAuditEvent('seller.product_updated', ['product_id' => (int)$id, 'seller_id' => (int)$_SESSION['user_id'], 'status' => $data['status'] ?? null]);
        flash('success', 'Product updated! ✅');
        redirect(base_url('seller/products'));
    }

    public function deleteProduct($id) {
        $this->checkSeller();
        $this->validateCsrf();
        $product = (new Product())->find($id);
        if ($product && $product['seller_id'] == $_SESSION['user_id']) {
            if ($product['image']) $this->deleteFile($product['image'], 'products');
            (new Product())->delete($id);
            $this->logAuditEvent('seller.product_deleted', ['product_id' => (int)$id, 'seller_id' => (int)$_SESSION['user_id']]);
            flash('success', 'Product deleted.');
        }
        redirect(base_url('seller/products'));
    }

    public function orders() {
        $this->checkSeller();
        $orderModel = new Order();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? null;
        $result = $orderModel->getSellerOrders($_SESSION['user_id'], $page, 10, $status);
        $this->view('seller/orders', [
            'pageTitle' => 'Orders - Vegihub',
            'orders' => $result['data'],
            'pagination' => $result,
            'statusFilter' => $status,
            'extraCss' => ['dashboard.css'],
        ]);
    }

    public function orderDetail($id) {
        $this->checkSeller();
        $orderModel = new Order();
        $order = $orderModel->getWithItems($id);
        if (!$order) { flash('error', 'Order not found.'); redirect(base_url('seller/orders')); }
        $order['items'] = array_filter($order['items'], fn($i) => $i['seller_id'] == $_SESSION['user_id']);
        if (empty($order['items'])) {
            flash('error', 'You do not have permission to view that order.');
            redirect(base_url('seller/orders'));
        }
        $this->view('seller/order_detail', [
            'pageTitle' => "Order #{$order['order_number']} - Vegihub",
            'order' => $order,
            'extraCss' => ['dashboard.css'],
        ]);
    }

    public function updateOrderStatus() {
        $this->checkSeller();
        $this->validateCsrf();
        $itemId = (int)($_POST['item_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $validStatuses = ['confirmed', 'shipped', 'delivered'];
        $allowedTransitions = [
            'pending' => ['confirmed'],
            'confirmed' => ['shipped'],
            'shipped' => ['delivered'],
        ];
        if (!in_array($status, $validStatuses)) { flash('error', 'Invalid status.'); back(); }
        $db = getDB();
        $stmt = $db->prepare("
            SELECT oi.*, o.user_id, o.payment_status, o.payment_method, o.status as order_status
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE oi.id = ? AND oi.seller_id = ?
        ");
        $stmt->execute([$itemId, $_SESSION['user_id']]);
        $item = $stmt->fetch();

        if (!$item) {
            flash('error', 'Order item not found.');
            back();
        }

        if ($item['payment_method'] === 'razorpay' && $item['payment_status'] !== 'paid') {
            flash('error', 'Online orders cannot be progressed until payment is verified.');
            back();
        }

        if ($item['payment_method'] === 'cod' && $status === 'delivered' && $item['payment_status'] !== 'paid') {
            flash('error', 'COD orders must be marked paid by an admin before you can mark them delivered.');
            back();
        }

        $currentStatus = $item['status'];
        if (!isset($allowedTransitions[$currentStatus]) || !in_array($status, $allowedTransitions[$currentStatus], true)) {
            flash('error', 'Invalid status transition.');
            back();
        }

        $db->prepare("UPDATE order_items SET status = ? WHERE id = ?")->execute([$status, $itemId]);
        (new Order())->syncOrderStatusFromItems($item['order_id']);
        if (!empty($item['user_id'])) {
            (new Notification())->createNotification(
                (int)$item['user_id'],
                'Order status updated',
                'An item in your order #' . $item['order_id'] . ' is now ' . $status . '.',
                'info',
                base_url('orders/' . $item['order_id'])
            );
        }
        $this->logAuditEvent('seller.order_status_updated', [
            'seller_id' => (int)$_SESSION['user_id'],
            'order_id' => (int)$item['order_id'],
            'order_item_id' => (int)$itemId,
            'status' => $status
        ]);

        flash('success', 'Order status updated.');
        back();
    }

    public function earnings() {
        $this->checkSeller();
        $orderModel = new Order();
        $productModel = new Product();
        $stats = $orderModel->getSellerStats($_SESSION['user_id']);
        $monthlyRevenue = $orderModel->getSellerMonthlyRevenue($_SESSION['user_id'], 6);
        $topProducts = $productModel->getTopProductsBySeller($_SESSION['user_id'], 5);
        $this->view('seller/earnings', [
            'pageTitle' => 'Earnings - Vegihub',
            'stats' => $stats,
            'monthlyRevenue' => $monthlyRevenue,
            'topProducts' => $topProducts,
            'extraCss' => ['dashboard.css'],
        ]);
    }
}
