<?php
class ApiController extends Controller {
    
    public function searchProducts() {
        $query = trim($_GET['q'] ?? '');
        if (strlen($query) < 2) {
            $this->json(['results' => []]);
        }
        $product = new Product();
        $results = $product->quickSearch($query, 8);
        $formatted = array_map(function($p) {
            return [
                'id' => $p['id'],
                'name' => $p['name'],
                'slug' => $p['slug'],
                'price' => $p['sale_price'] ?: $p['price'],
                'original_price' => $p['price'],
                'image' => $p['image'],
                'unit' => $p['unit'],
                'url' => base_url('products/' . $p['slug']),
            ];
        }, $results);
        $this->json(['results' => $formatted]);
    }

    public function getNotifications() {
        if (!is_logged_in()) $this->json(['notifications' => []]);
        $notif = new Notification();
        $notifications = $notif->getUserNotifications($_SESSION['user_id'], 10);
        $unread = $notif->getUnreadCount($_SESSION['user_id']);
        $this->json(['notifications' => $notifications, 'unread_count' => $unread]);
    }

    public function markNotificationRead() {
        if (!is_logged_in()) $this->json(['success' => false]);
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            (new Notification())->markAsRead($id, $_SESSION['user_id']);
        } else {
            (new Notification())->markAllRead($_SESSION['user_id']);
        }
        $this->json(['success' => true]);
    }
}
