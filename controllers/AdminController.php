<?php
class AdminController extends Controller {
    public function __construct() { }
    private function checkAdmin() { $this->requireRole('admin'); }

    public function dashboard() {
        $this->checkAdmin();
        $orderModel = new Order();
        $userModel = new User();
        $productModel = new Product();
        
        $orderStats = $orderModel->getAdminStats();
        $userStats = $userModel->getStatsByRole();
        $totalProducts = $productModel->count();
        $recentOrders = $orderModel->getRecentOrders(8);
        $monthlyRevenue = $orderModel->getAdminRevenueByMonth(6);
        $topVendors = $orderModel->getTopVendors(5);
        $topProducts = $orderModel->getTopProducts(5);
        $pendingPaymentSummary = $orderModel->getPendingPaymentSummary();
        
        $this->view('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard - Vegihub',
            'activePage' => 'dashboard',
            'orderStats' => $orderStats,
            'userStats' => $userStats,
            'totalProducts' => $totalProducts,
            'recentOrders' => $recentOrders,
            'monthlyRevenue' => $monthlyRevenue,
            'topVendors' => $topVendors,
            'topProducts' => $topProducts,
            'pendingPaymentSummary' => $pendingPaymentSummary,
            'extraCss' => ['dashboard.css'],
        ]);
    }

    public function users() {
        $this->checkAdmin();
        $userModel = new User();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $role = $_GET['role'] ?? '';
        $where = $role ? "role = ?" : '';
        $params = $role ? [$role] : [];
        $result = $userModel->paginate($page, 15, $where, $params, 'created_at DESC');
        $this->view('admin/users', ['pageTitle' => 'Users - Vegihub', 'activePage' => 'users', 'users' => $result['data'], 'pagination' => $result, 'roleFilter' => $role, 'extraCss' => ['dashboard.css']]);
    }

    public function banUser($id) {
        $this->checkAdmin();
        $this->validateCsrf();
        (new User())->update($id, ['status' => 'banned']);
        $this->logAuditEvent('admin.user_banned', ['target_user_id' => (int)$id]);
        flash('success', 'User banned.');
        back();
    }

    public function unbanUser($id) {
        $this->checkAdmin();
        $this->validateCsrf();
        (new User())->update($id, ['status' => 'active']);
        $this->logAuditEvent('admin.user_unbanned', ['target_user_id' => (int)$id]);
        flash('success', 'User unbanned.');
        back();
    }
    public function deleteUser($id) {
        $this->checkAdmin();
        $this->validateCsrf();
        $id = (int)$id;

        if ($id === (int)$_SESSION['user_id']) {
            flash('error', 'You cannot delete your own admin account.');
            redirect(base_url('admin/users'));
        }

        $userModel = new User();
        $user = $userModel->find($id);
        if (!$user) {
            flash('error', 'User not found.');
            redirect(base_url('admin/users'));
        }

        if (($user['role'] ?? '') === 'admin' && $userModel->count("role = ?", ['admin']) <= 1) {
            flash('error', 'You cannot delete the last admin account.');
            redirect(base_url('admin/users'));
        }

        $userModel->delete($id);
        $this->logAuditEvent('admin.user_deleted', ['target_user_id' => $id, 'target_role' => $user['role'] ?? null]);
        flash('success', 'User deleted.');
        redirect(base_url('admin/users'));
    }

    public function products() {
        $this->checkAdmin();
        $product = new Product();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $where = $status ? "status = ?" : '';
        $params = $status ? [$status] : [];
        $result = $product->paginate($page, 15, $where, $params, 'created_at DESC');
        $this->view('admin/products', ['pageTitle' => 'Products - Vegihub', 'activePage' => 'products', 'products' => $result['data'], 'pagination' => $result, 'statusFilter' => $status, 'extraCss' => ['dashboard.css']]);
    }

    public function approveProduct($id) {
        $this->checkAdmin();
        $this->validateCsrf();
        $product = (new Product())->find($id);
        if ($product) {
            (new Product())->update($id, ['status' => 'active']);
            (new Notification())->createNotification(
                $product['seller_id'],
                'Product approved',
                'Your product "' . $product['name'] . '" is now live.',
                'success',
                base_url('seller/products')
            );
            $this->logAuditEvent('admin.product_approved', ['product_id' => (int)$id, 'seller_id' => (int)$product['seller_id']]);
        }
        flash('success', 'Product approved.');
        back();
    }

    public function rejectProduct($id) {
        $this->checkAdmin();
        $this->validateCsrf();
        $product = (new Product())->find($id);
        if ($product) {
            (new Product())->update($id, ['status' => 'inactive']);
            (new Notification())->createNotification(
                $product['seller_id'],
                'Product not approved',
                'Your product "' . $product['name'] . '" was moved to inactive. Review it and update the listing.',
                'warning',
                base_url('seller/products')
            );
            $this->logAuditEvent('admin.product_rejected', ['product_id' => (int)$id, 'seller_id' => (int)$product['seller_id']]);
        }
        flash('success', 'Product rejected.');
        back();
    }

    public function applyBulkDiscount() {
        $this->checkAdmin();
        $this->validateCsrf();

        $type = $_POST['discount_type'] ?? 'percentage';
        $value = (float)($_POST['discount_value'] ?? 0);
        $status = $_POST['status_scope'] ?? 'all';

        try {
            $affected = (new Product())->applyBulkDiscount($type, $value, $status);
            $this->logAuditEvent('admin.products_bulk_discount_applied', [
                'discount_type' => $type,
                'discount_value' => $value,
                'status_scope' => $status,
                'affected_rows' => $affected,
            ]);
            flash('success', 'Bulk discount applied to ' . $affected . ' product(s).');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
        }

        redirect(base_url('admin/products'));
    }

    public function clearBulkDiscount() {
        $this->checkAdmin();
        $this->validateCsrf();

        $status = $_POST['status_scope'] ?? 'all';

        try {
            $affected = (new Product())->clearBulkDiscount($status);
            $this->logAuditEvent('admin.products_bulk_discount_cleared', [
                'status_scope' => $status,
                'affected_rows' => $affected,
            ]);
            flash('success', 'Sale prices cleared for ' . $affected . ' product(s).');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
        }

        redirect(base_url('admin/products'));
    }

    public function orders() {
        $this->checkAdmin();
        $orderModel = new Order();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $where = $status ? "status = ?" : '';
        $params = $status ? [$status] : [];
        $result = $orderModel->paginate($page, 15, $where, $params, 'created_at DESC');
        $db = getDB();
        foreach ($result['data'] as &$o) {
            $stmt = $db->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$o['user_id']]);
            $o['buyer_name'] = $stmt->fetchColumn() ?: 'Unknown';
        }
        $this->view('admin/orders', ['pageTitle' => 'Orders - Vegihub', 'activePage' => 'orders', 'orders' => $result['data'], 'pagination' => $result, 'statusFilter' => $status, 'extraCss' => ['dashboard.css']]);
    }

    public function orderDetail($id) {
        $this->checkAdmin();
        $order = (new Order())->getWithItems($id);
        if (!$order) { flash('error', 'Order not found.'); redirect(base_url('admin/orders')); }
        $buyer = (new User())->find($order['user_id']);
        $this->view('admin/order_detail', ['pageTitle' => 'Order #'.$order['order_number'].' - Vegihub', 'activePage' => 'orders', 'order' => $order, 'buyer' => $buyer, 'extraCss' => ['dashboard.css']]);
    }

    public function markCodPaid($id) {
        $this->checkAdmin();
        $this->validateCsrf();

        try {
            $orderModel = new Order();
            $orderModel->markCodPaid((int)$id);
            $this->logAuditEvent('admin.order_cod_paid', ['order_id' => (int)$id]);
            flash('success', 'COD payment marked as collected.');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
        }

        redirect(base_url('admin/orders/' . (int)$id));
    }

    public function categories() {
        $this->checkAdmin();
        $cats = (new Category())->getWithProductCount();
        $this->view('admin/categories', ['pageTitle' => 'Categories - Vegihub', 'activePage' => 'categories', 'categories' => $cats, 'extraCss' => ['dashboard.css']]);
    }

    public function addCategory() {
        $this->checkAdmin(); $this->validateCsrf();
        $name = trim($_POST['name'] ?? '');
        if (!$name) { flash('error', 'Name required.'); back(); }
        (new Category())->create(['name' => $name, 'slug' => generate_slug($name), 'icon' => $_POST['icon'] ?? '🥬', 'description' => $_POST['description'] ?? '', 'is_active' => 1, 'sort_order' => (int)($_POST['sort_order'] ?? 0), 'created_at' => date('Y-m-d H:i:s')]);
        flash('success', 'Category added!'); redirect(base_url('admin/categories'));
    }

    public function editCategory($id) {
        $this->checkAdmin(); $this->validateCsrf();
        (new Category())->update($id, ['name' => trim($_POST['name'] ?? ''), 'icon' => $_POST['icon'] ?? '', 'description' => $_POST['description'] ?? '', 'is_active' => isset($_POST['is_active']) ? 1 : 0, 'sort_order' => (int)($_POST['sort_order'] ?? 0)]);
        flash('success', 'Category updated!'); redirect(base_url('admin/categories'));
    }

    public function deleteCategory($id) {
        $this->checkAdmin();
        $this->validateCsrf();

        $categoryModel = new Category();
        $category = $categoryModel->find($id);
        if (!$category) {
            flash('error', 'Category not found.');
            redirect(base_url('admin/categories'));
        }

        if ((new Product())->count("category_id = ?", [$id]) > 0) {
            flash('error', 'Cannot delete a category that still has products assigned to it.');
            redirect(base_url('admin/categories'));
        }

        $categoryModel->delete($id);
        $this->logAuditEvent('admin.category_deleted', ['category_id' => (int)$id, 'category_name' => $category['name'] ?? null]);
        flash('success', 'Category deleted.');
        redirect(base_url('admin/categories'));
    }

    public function coupons() {
        $this->checkAdmin();
        $coupons = (new Coupon())->all('created_at DESC');
        $this->view('admin/coupons', ['pageTitle' => 'Coupons - Vegihub', 'activePage' => 'coupons', 'coupons' => $coupons, 'extraCss' => ['dashboard.css']]);
    }

    public function addCoupon() {
        $this->checkAdmin();
        $this->validateCsrf();

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'percentage';
        $value = (float)($_POST['value'] ?? 0);
        $minOrder = (float)($_POST['min_order'] ?? 0);
        $maxDiscount = !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null;
        $usageLimit = (int)($_POST['usage_limit'] ?? 100);
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');

        if ($code === '' || !preg_match('/^[A-Z0-9_-]{3,32}$/', $code)) {
            flash('error', 'Coupon code must be 3-32 characters using letters, numbers, dash, or underscore.');
            redirect(base_url('admin/coupons'));
        }
        if (!in_array($type, ['percentage', 'fixed'], true)) {
            flash('error', 'Invalid coupon type.');
            redirect(base_url('admin/coupons'));
        }
        if ($value <= 0 || ($type === 'percentage' && $value > 100)) {
            flash('error', 'Coupon value is invalid.');
            redirect(base_url('admin/coupons'));
        }
        if ($minOrder < 0 || $usageLimit <= 0) {
            flash('error', 'Minimum order and usage limit must be valid.');
            redirect(base_url('admin/coupons'));
        }
        if ($maxDiscount !== null && $maxDiscount <= 0) {
            flash('error', 'Maximum discount must be greater than 0.');
            redirect(base_url('admin/coupons'));
        }
        if ($startDate === '' || $endDate === '' || strtotime($startDate) === false || strtotime($endDate) === false || strtotime($startDate) > strtotime($endDate)) {
            flash('error', 'Coupon dates are invalid.');
            redirect(base_url('admin/coupons'));
        }

        $couponModel = new Coupon();
        if ($couponModel->findBy('code', $code)) {
            flash('error', 'Coupon code already exists.');
            redirect(base_url('admin/coupons'));
        }

        $couponModel->create([
            'code' => $code, 'type' => $type, 'value' => $value,
            'min_order' => $minOrder, 'max_discount' => $maxDiscount,
            'usage_limit' => $usageLimit, 'start_date' => $startDate, 'end_date' => $endDate,
            'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->logAuditEvent('admin.coupon_created', ['code' => $code, 'type' => $type, 'value' => $value]);
        flash('success', 'Coupon created! 🎫');
        redirect(base_url('admin/coupons'));
    }

    public function toggleCoupon($id) {
        $this->checkAdmin();
        $this->validateCsrf();
        $couponModel = new Coupon();
        $coupon = $couponModel->find($id);
        if (!$coupon) {
            flash('error', 'Coupon not found.');
            back();
        }
        $couponModel->update($id, ['is_active' => $coupon['is_active'] ? 0 : 1]);
        $this->logAuditEvent('admin.coupon_toggled', ['coupon_id' => (int)$id, 'new_state' => $coupon['is_active'] ? 0 : 1]);
        flash('success', 'Coupon toggled.');
        back();
    }
    public function deleteCoupon($id) {
        $this->checkAdmin();
        $this->validateCsrf();
        $coupon = (new Coupon())->find($id);
        (new Coupon())->delete($id);
        $this->logAuditEvent('admin.coupon_deleted', ['coupon_id' => (int)$id, 'code' => $coupon['code'] ?? null]);
        flash('success', 'Coupon deleted.');
        redirect(base_url('admin/coupons'));
    }

    public function messages() {
        $this->checkAdmin();
        $msgs = (new Message())->all('created_at DESC');
        $this->view('admin/messages', ['pageTitle' => 'Messages - Vegihub', 'activePage' => 'messages', 'messages' => $msgs, 'extraCss' => ['dashboard.css']]);
    }

    public function markRead($id) { $this->checkAdmin(); $this->validateCsrf(); (new Message())->update($id, ['is_read' => 1]); $this->json(['success' => true]); }
    public function deleteMessage($id) {
        $this->checkAdmin();
        $this->validateCsrf();
        (new Message())->delete($id);
        $this->logAuditEvent('admin.message_deleted', ['message_id' => (int)$id]);
        flash('success', 'Message deleted.');
        redirect(base_url('admin/messages'));
    }

    public function settings() {
        $this->checkAdmin();
        $db = getDB();
        $settings = [];
        $rows = $db->query("SELECT * FROM platform_settings")->fetchAll();
        foreach ($rows as $row) $settings[$row['setting_key']] = $row['setting_value'];
        $this->view('admin/settings', ['pageTitle' => 'Settings - Vegihub', 'activePage' => 'settings', 'settings' => $settings, 'extraCss' => ['dashboard.css']]);
    }

    public function updateSettings() {
        $this->checkAdmin(); $this->validateCsrf();
        $db = getDB();
        foreach ($_POST as $key => $value) {
            if ($key === '_csrf_token') continue;
            $db->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$key, $value, $value]);
        }
        flash('success', 'Settings updated! ⚙️'); redirect(base_url('admin/settings'));
    }
}
