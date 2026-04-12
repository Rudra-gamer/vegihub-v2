<?php
class Order extends Model {
    protected $table = 'orders';

    public function getWithItems($orderId) {
        $order = $this->find($orderId);
        if (!$order) return null;
        
        $order['items'] = $this->rawQuery("
            SELECT oi.*, p.slug
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ", [$orderId]);
        
        return $order;
    }

    public function getUserOrders($userId, $page = 1, $perPage = 10, $status = null) {
        $where = "o.user_id = ?";
        $params = [$userId];
        
        if ($status) {
            $where .= " AND o.status = ?";
            $params[] = $status;
        }

        $total = (int)$this->rawQueryOne("SELECT COUNT(*) as cnt FROM orders o WHERE {$where}", $params)['cnt'];
        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;

        $data = $this->rawQuery("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
            FROM orders o
            WHERE {$where}
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ", $params);

        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function getSellerOrders($sellerId, $page = 1, $perPage = 10, $status = null) {
        $where = "oi.seller_id = ?";
        $params = [$sellerId];
        
        if ($status) {
            $where .= " AND oi.status = ?";
            $params[] = $status;
        }

        $total = (int)$this->rawQueryOne("
            SELECT COUNT(DISTINCT o.id) as cnt 
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            WHERE {$where}
        ", $params)['cnt'];

        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;

        $data = $this->rawQuery("
            SELECT DISTINCT o.*, u.name as buyer_name, u.email as buyer_email
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            JOIN users u ON o.user_id = u.id
            WHERE {$where}
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ", $params);

        return [
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function getSellerStats($sellerId) {
        return $this->rawQueryOne("
            SELECT 
                COUNT(DISTINCT o.id) as total_orders,
                COALESCE(SUM(CASE WHEN o.payment_status = 'paid' THEN oi.total ELSE 0 END), 0) as total_revenue,
                SUM(CASE WHEN oi.status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN oi.status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE oi.seller_id = ?
        ", [$sellerId]);
    }

    public function getAdminStats() {
        return $this->rawQueryOne("
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(total), 0) as total_revenue,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
                SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) as paid_revenue
            FROM orders
        ");
    }

    public function getRecentOrders($limit = 10) {
        return $this->rawQuery("
            SELECT o.*, u.name as buyer_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT ?
        ", [$limit]);
    }

    public function getWeeklyRevenue() {
        return $this->rawQuery("
            SELECT DATE(created_at) as date, COALESCE(SUM(total), 0) as revenue, COUNT(*) as orders
            FROM orders
            WHERE payment_status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
    }

    public function getAdminRevenueByMonth($months = 6) {
        return $this->rawQuery("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as revenue_month,
                   DATE_FORMAT(created_at, '%b %Y') as revenue_label,
                   COALESCE(SUM(total), 0) as revenue,
                   COUNT(*) as orders
            FROM orders
            WHERE payment_status = 'paid'
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
            ORDER BY revenue_month ASC
        ", [$months]);
    }

    public function getTopVendors($limit = 5) {
        return $this->rawQuery("
            SELECT u.id,
                   u.name,
                   COUNT(DISTINCT oi.order_id) as orders_count,
                   COALESCE(SUM(CASE WHEN o.payment_status = 'paid' THEN oi.total ELSE 0 END), 0) as revenue,
                   COALESCE(SUM(CASE WHEN oi.status = 'delivered' THEN oi.quantity ELSE 0 END), 0) as delivered_units
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN users u ON oi.seller_id = u.id
            GROUP BY u.id, u.name
            ORDER BY revenue DESC, orders_count DESC
            LIMIT ?
        ", [$limit]);
    }

    public function getTopProducts($limit = 5) {
        return $this->rawQuery("
            SELECT p.id,
                   p.name,
                   p.slug,
                   COALESCE(SUM(CASE WHEN oi.status = 'delivered' THEN oi.quantity ELSE 0 END), 0) as units_sold,
                   COALESCE(SUM(CASE WHEN o.payment_status = 'paid' THEN oi.total ELSE 0 END), 0) as revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            GROUP BY p.id, p.name, p.slug
            ORDER BY units_sold DESC, revenue DESC
            LIMIT ?
        ", [$limit]);
    }

    public function getPendingPaymentSummary() {
        return $this->rawQueryOne("
            SELECT COUNT(*) as pending_orders,
                   COALESCE(SUM(total), 0) as pending_amount,
                   SUM(CASE WHEN payment_method = 'cod' THEN 1 ELSE 0 END) as cod_pending_orders
            FROM orders
            WHERE payment_status = 'pending' AND status != 'cancelled'
        ") ?: ['pending_orders' => 0, 'pending_amount' => 0, 'cod_pending_orders' => 0];
    }

    public function getSellerMonthlyRevenue($sellerId, $months = 6) {
        return $this->rawQuery("
            SELECT DATE_FORMAT(o.created_at, '%Y-%m') as revenue_month,
                   DATE_FORMAT(o.created_at, '%b %Y') as revenue_label,
                   COALESCE(SUM(CASE WHEN o.payment_status = 'paid' THEN oi.total ELSE 0 END), 0) as revenue,
                   COUNT(DISTINCT o.id) as orders
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE oi.seller_id = ?
              AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(o.created_at, '%Y-%m'), DATE_FORMAT(o.created_at, '%b %Y')
            ORDER BY revenue_month ASC
        ", [$sellerId, $months]);
    }

    public function createOrder($orderData, $items) {
        $this->db->beginTransaction();
        try {
            $orderId = $this->create($orderData);
            
            foreach ($items as $item) {
                $stockStmt = $this->db->prepare("
                    UPDATE products
                    SET stock = stock - ?, total_sold = total_sold + ?
                    WHERE id = ? AND status = 'active' AND stock >= ?
                ");
                $stockStmt->execute([
                    $item['quantity'],
                    $item['quantity'],
                    $item['product_id'],
                    $item['quantity'],
                ]);

                if ($stockStmt->rowCount() !== 1) {
                    throw new Exception('Insufficient stock for ' . $item['product_name'] . '.');
                }

                $item['order_id'] = $orderId;
                $stmt = $this->db->prepare("
                    INSERT INTO order_items (order_id, product_id, seller_id, product_name, product_image, price, quantity, total, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([
                    $orderId, $item['product_id'], $item['seller_id'],
                    $item['product_name'], $item['product_image'],
                    $item['price'], $item['quantity'], $item['total']
                ]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function markPaid($orderId, array $paymentData = []) {
        $this->db->beginTransaction();
        try {
            $order = $this->find($orderId);
            if (!$order) {
                throw new Exception('Order not found.');
            }

            $this->update($orderId, array_merge([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'updated_at' => date('Y-m-d H:i:s'),
            ], $paymentData));

            $this->rawExecute(
                "UPDATE order_items SET status = 'confirmed' WHERE order_id = ? AND status = 'pending'",
                [$orderId]
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function markCodPaid($orderId) {
        $this->db->beginTransaction();
        try {
            $order = $this->find($orderId);
            if (!$order) {
                throw new Exception('Order not found.');
            }

            if (($order['payment_method'] ?? '') !== 'cod') {
                throw new Exception('Only COD orders can be marked as collected.');
            }

            if (($order['status'] ?? '') === 'cancelled') {
                throw new Exception('Cancelled orders cannot be marked as paid.');
            }

            if (($order['payment_status'] ?? '') === 'paid') {
                $this->db->commit();
                return true;
            }

            $this->update($orderId, [
                'payment_status' => 'paid',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function markFailed($orderId, $notes = null) {
        $this->db->beginTransaction();
        try {
            $order = $this->find($orderId);
            if (!$order) {
                throw new Exception('Order not found.');
            }

            if ($order['payment_status'] === 'failed' || $order['status'] === 'cancelled') {
                $this->db->commit();
                return true;
            }

            $this->update($orderId, [
                'payment_status' => 'failed',
                'status' => 'cancelled',
                'notes' => $notes,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $items = $this->rawQuery(
                "SELECT product_id, quantity FROM order_items WHERE order_id = ?",
                [$orderId]
            );

            foreach ($items as $item) {
                $this->rawExecute(
                    "UPDATE products SET stock = stock + ?, total_sold = GREATEST(total_sold - ?, 0) WHERE id = ?",
                    [$item['quantity'], $item['quantity'], $item['product_id']]
                );
            }

            $this->rawExecute(
                "UPDATE order_items SET status = 'cancelled' WHERE order_id = ?",
                [$orderId]
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function syncOrderStatusFromItems($orderId) {
        $statuses = $this->rawQuery(
            "SELECT status FROM order_items WHERE order_id = ?",
            [$orderId]
        );

        if (empty($statuses)) {
            return null;
        }

        $statusValues = array_column($statuses, 'status');
        if (count(array_unique($statusValues)) === 1 && $statusValues[0] === 'cancelled') {
            $newStatus = 'cancelled';
        } elseif (in_array('pending', $statusValues, true)) {
            $newStatus = 'pending';
        } elseif (in_array('confirmed', $statusValues, true)) {
            $newStatus = 'confirmed';
        } elseif (in_array('shipped', $statusValues, true)) {
            $newStatus = 'shipped';
        } elseif (count(array_unique($statusValues)) === 1 && $statusValues[0] === 'delivered') {
            $newStatus = 'delivered';
        } else {
            $newStatus = 'processing';
        }

        $this->update($orderId, [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $newStatus;
    }

    public function cancelOrderForUser($orderId, $userId) {
        $this->db->beginTransaction();
        try {
            $order = $this->find($orderId);
            if (!$order || (int)$order['user_id'] !== (int)$userId) {
                throw new Exception('Order not found.');
            }

            if (!in_array($order['status'], ['pending', 'confirmed'], true)) {
                throw new Exception('This order cannot be cancelled.');
            }

            if ($order['payment_status'] === 'paid') {
                throw new Exception('Paid online orders cannot be cancelled automatically. Please contact support.');
            }

            if (($order['payment_method'] ?? '') === 'cod' && ($order['status'] ?? '') !== 'pending') {
                throw new Exception('COD orders can only be cancelled before seller confirmation.');
            }

            $items = $this->rawQuery(
                "SELECT product_id, quantity FROM order_items WHERE order_id = ?",
                [$orderId]
            );

            foreach ($items as $item) {
                $this->rawExecute(
                    "UPDATE products SET stock = stock + ?, total_sold = GREATEST(total_sold - ?, 0) WHERE id = ?",
                    [$item['quantity'], $item['quantity'], $item['product_id']]
                );
            }

            $this->update($orderId, [
                'status' => 'cancelled',
                'payment_status' => $order['payment_status'] === 'pending' ? 'failed' : $order['payment_status'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->rawExecute(
                "UPDATE order_items SET status = 'cancelled' WHERE order_id = ?",
                [$orderId]
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
