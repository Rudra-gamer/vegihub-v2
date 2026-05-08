<?php
class Product extends Model {
    protected $table = 'products';

    public function getWithSeller($id) {
        return $this->rawQueryOne("
            SELECT p.*,
                   COALESCE((SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.product_id = p.id), 0) as avg_rating,
                   (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) as total_reviews,
                   COALESCE((SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.product_id = p.id AND oi.status = 'delivered'), 0) as total_sold,
                   u.name as seller_name, u.avatar as seller_avatar, c.name as category_name, c.slug as category_slug
            FROM products p
            JOIN users u ON p.seller_id = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
        ", [$id]);
    }

    public function getBySlug($slug) {
        return $this->rawQueryOne("
            SELECT p.*,
                   COALESCE((SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.product_id = p.id), 0) as avg_rating,
                   (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) as total_reviews,
                   COALESCE((SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.product_id = p.id AND oi.status = 'delivered'), 0) as total_sold,
                   u.name as seller_name, u.avatar as seller_avatar, c.name as category_name, c.slug as category_slug
            FROM products p
            JOIN users u ON p.seller_id = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ? AND p.status = 'active'
        ", [$slug]);
    }

    public function getFeatured($limit = 8) {
        return $this->rawQuery("
            SELECT p.*, u.name as seller_name, c.name as category_name
            FROM products p
            JOIN users u ON p.seller_id = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.is_featured = 1 AND p.status = 'active'
            ORDER BY p.total_sold DESC
            LIMIT ?
        ", [$limit]);
    }

    public function getDeals($limit = 6) {
        return $this->rawQuery("
            SELECT p.*, u.name as seller_name, c.name as category_name,
                   ROUND(((p.price - p.sale_price) / p.price) * 100) as discount_pct
            FROM products p
            JOIN users u ON p.seller_id = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.sale_price IS NOT NULL AND p.sale_price < p.price AND p.status = 'active'
            ORDER BY discount_pct DESC
            LIMIT ?
        ", [$limit]);
    }

    public function getNewArrivals($limit = 8) {
        return $this->rawQuery("
            SELECT p.*, u.name as seller_name, c.name as category_name
            FROM products p
            JOIN users u ON p.seller_id = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC
            LIMIT ?
        ", [$limit]);
    }

    public function getBestSellers($limit = 8) {
        return $this->rawQuery("
            SELECT p.*, u.name as seller_name, c.name as category_name
            FROM products p
            JOIN users u ON p.seller_id = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active'
            ORDER BY p.total_sold DESC
            LIMIT ?
        ", [$limit]);
    }

    public function searchProducts($query, $filters = [], $page = 1, $perPage = 12) {
        $where = ["p.status = 'active'"];
        $params = [];

        if (!empty($query)) {
            $where[] = "(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
            $params[] = "%{$query}%";
            $params[] = "%{$query}%";
            $params[] = "%{$query}%";
        }

        if (!empty($filters['category'])) {
            $where[] = "c.slug = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['min_price'])) {
            $where[] = "COALESCE(p.sale_price, p.price) >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = "COALESCE(p.sale_price, p.price) <= ?";
            $params[] = $filters['max_price'];
        }

        if (!empty($filters['organic'])) {
            $where[] = "p.is_organic = 1";
        }

        if (!empty($filters['seller_id'])) {
            $where[] = "p.seller_id = ?";
            $params[] = (int)$filters['seller_id'];
        }

        if (!empty($filters['in_stock'])) {
            $where[] = "p.stock > 0";
        }

        if (!empty($filters['rating'])) {
            $where[] = "p.avg_rating >= ?";
            $params[] = $filters['rating'];
        }

        $whereClause = implode(' AND ', $where);
        
        $sortOptions = [
            'newest' => 'p.created_at DESC',
            'price_low' => 'COALESCE(p.sale_price, p.price) ASC',
            'price_high' => 'COALESCE(p.sale_price, p.price) DESC',
            'rating' => 'p.avg_rating DESC',
            'popular' => 'p.total_sold DESC',
        ];
        $sort = $sortOptions[$filters['sort'] ?? 'newest'] ?? 'p.created_at DESC';

        $countSql = "SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE {$whereClause}";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $sql = "
            SELECT p.*,
                   COALESCE((SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.product_id = p.id), 0) as avg_rating,
                   (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) as total_reviews,
                   COALESCE((SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.product_id = p.id AND oi.status = 'delivered'), 0) as total_sold,
                   u.name as seller_name, c.name as category_name, c.slug as category_slug
            FROM products p
            JOIN users u ON p.seller_id = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY {$sort}
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function getRelated($productId, $categoryId, $limit = 4) {
        return $this->rawQuery("
            SELECT p.*, u.name as seller_name
            FROM products p
            JOIN users u ON p.seller_id = u.id
            WHERE p.category_id = ? AND p.id != ? AND p.status = 'active'
            ORDER BY RAND()
            LIMIT ?
        ", [$categoryId, $productId, $limit]);
    }

    public function getBySellerPaginated($sellerId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $total = $this->count("seller_id = ?", [$sellerId]);
        
        $data = $this->rawQuery("
            SELECT p.*, c.name as category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.seller_id = ?
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ", [$sellerId, $perPage, $offset]);

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function incrementViews($id) {
        $this->rawExecute("UPDATE products SET views = views + 1 WHERE id = ?", [$id]);
    }

    public function getSellerOptions() {
        return $this->rawQuery("
            SELECT DISTINCT u.id, u.name
            FROM users u
            JOIN products p ON p.seller_id = u.id
            WHERE u.role = 'seller'
            ORDER BY u.name ASC
        ");
    }

    public function getTopProductsBySeller($sellerId, $limit = 5) {
        return $this->rawQuery("
            SELECT p.id, p.name, p.slug, p.stock,
                   COALESCE(SUM(CASE WHEN oi.status = 'delivered' THEN oi.quantity ELSE 0 END), 0) as units_sold,
                   COALESCE(SUM(CASE WHEN o.payment_status = 'paid' THEN oi.total ELSE 0 END), 0) as revenue
            FROM products p
            LEFT JOIN order_items oi ON oi.product_id = p.id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE p.seller_id = ?
            GROUP BY p.id, p.name, p.slug, p.stock
            ORDER BY units_sold DESC, revenue DESC
            LIMIT ?
        ", [$sellerId, $limit]);
    }

    public function getLowStockBySeller($sellerId, $threshold = 10, $limit = 5) {
        return $this->rawQuery("
            SELECT id, name, slug, stock, status
            FROM products
            WHERE seller_id = ?
              AND stock <= ?
            ORDER BY stock ASC, updated_at DESC
            LIMIT ?
        ", [$sellerId, $threshold, $limit]);
    }

    public function updateRating($productId) {
        $this->rawExecute("
            UPDATE products SET 
                avg_rating = COALESCE((SELECT AVG(rating) FROM reviews WHERE product_id = ?), 0),
                total_reviews = (SELECT COUNT(*) FROM reviews WHERE product_id = ?)
            WHERE id = ?
        ", [$productId, $productId, $productId]);
    }

    public function quickSearch($query, $limit = 5) {
        return $this->rawQuery("
            SELECT id, name, slug, price, sale_price, image, unit
            FROM products
            WHERE status = 'active' AND name LIKE ?
            ORDER BY total_sold DESC
            LIMIT ?
        ", ["%{$query}%", $limit]);
    }

    public function applyBulkDiscount($type, $value, $status = 'all') {
        $allowedTypes = ['percentage', 'fixed'];
        $allowedStatuses = ['all', 'active', 'inactive', 'pending'];

        if (!in_array($type, $allowedTypes, true)) {
            throw new Exception('Invalid discount type.');
        }

        if (!in_array($status, $allowedStatuses, true)) {
            throw new Exception('Invalid product status filter.');
        }

        $value = (float)$value;
        if ($value <= 0) {
            throw new Exception('Discount value must be greater than 0.');
        }

        if ($type === 'percentage' && $value >= 100) {
            throw new Exception('Percentage discount must be less than 100.');
        }

        $where = '';
        $params = [];
        if ($status !== 'all') {
            $where = " WHERE status = ?";
            $params[] = $status;
        }

        $sql = $type === 'percentage'
            ? "UPDATE products SET sale_price = ROUND(price - (price * ? / 100), 2), updated_at = ?{$where}"
            : "UPDATE products SET sale_price = ROUND(GREATEST(price - ?, 0.01), 2), updated_at = ?{$where}";

        array_unshift($params, $value);
        array_splice($params, 1, 0, [date('Y-m-d H:i:s')]);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function clearBulkDiscount($status = 'all') {
        $allowedStatuses = ['all', 'active', 'inactive', 'pending'];
        if (!in_array($status, $allowedStatuses, true)) {
            throw new Exception('Invalid product status filter.');
        }

        $sql = "UPDATE products SET sale_price = NULL, updated_at = ?";
        $params = [date('Y-m-d H:i:s')];
        if ($status !== 'all') {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }
}
