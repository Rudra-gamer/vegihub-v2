<?php
class OrderController extends Controller {
    public function index() {
        $this->requireAuth();
        $orderModel = new Order();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? null;
        $result = $orderModel->getUserOrders($_SESSION['user_id'], $page, 10, $status);
        
        $this->view('orders/index', [
            'pageTitle' => 'My Orders - Vegihub',
            'currentPage' => 'orders',
            'orders' => $result['data'],
            'pagination' => $result,
            'statusFilter' => $status,
        ]);
    }

    public function detail($id) {
        $this->requireAuth();
        $orderModel = new Order();
        $order = $orderModel->getWithItems($id);
        if (!$order || $order['user_id'] != $_SESSION['user_id']) {
            flash('error', 'Order not found.');
            redirect(base_url('orders'));
        }

        $reviewModel = new Review();
        $sellerReviewModel = new SellerReview();
        foreach ($order['items'] as &$item) {
            $isDelivered = ($item['status'] ?? '') === 'delivered';
            $item['can_review_product'] = $isDelivered && !$reviewModel->hasReviewed($_SESSION['user_id'], (int)$item['product_id']);
            $item['can_review_seller'] = $isDelivered && !$sellerReviewModel->hasReviewedSeller(
                $_SESSION['user_id'],
                (int)$item['seller_id'],
                (int)$item['product_id']
            );
        }
        unset($item);

        $this->view('orders/detail', [
            'pageTitle' => "Order #{$order['order_number']} - Vegihub",
            'currentPage' => 'orders',
            'order' => $order,
        ]);
    }

    public function cancel($id) {
        $this->requireAuth();
        $this->validateCsrf();
        $orderModel = new Order();
        try {
            $orderModel->cancelOrderForUser($id, $_SESSION['user_id']);
            flash('success', 'Order cancelled successfully.');
        } catch (Exception $e) {
            flash('error', $e->getMessage());
        }
        redirect(base_url('orders/' . $id));
    }
}
