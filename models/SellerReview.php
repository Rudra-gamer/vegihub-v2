<?php
class SellerReview extends Model {
    protected $table = 'seller_reviews';
    private static $tableReady = false;

    public function __construct() {
        parent::__construct();
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        if (self::$tableReady) {
            return;
        }

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS `seller_reviews` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `seller_id` INT NOT NULL,
                `product_id` INT DEFAULT NULL,
                `order_id` INT DEFAULT NULL,
                `rating` TINYINT NOT NULL,
                `comment` TEXT DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_seller_review_user_seller_product` (`user_id`, `seller_id`, `product_id`),
                INDEX `idx_seller_reviews_seller` (`seller_id`),
                INDEX `idx_seller_reviews_product` (`product_id`),
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
                FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::$tableReady = true;
    }

    public function getSellerReviews($sellerId, $limit = 10) {
        return $this->rawQuery("
            SELECT sr.*, u.name as user_name, p.name as product_name, p.slug as product_slug
            FROM seller_reviews sr
            JOIN users u ON sr.user_id = u.id
            LEFT JOIN products p ON sr.product_id = p.id
            WHERE sr.seller_id = ?
            ORDER BY sr.created_at DESC
            LIMIT ?
        ", [$sellerId, $limit]);
    }

    public function getSellerRatingSummary($sellerId) {
        $summary = $this->rawQueryOne("
            SELECT 
                COUNT(*) as total_reviews,
                COALESCE(ROUND(AVG(rating), 1), 0) as avg_rating
            FROM seller_reviews
            WHERE seller_id = ?
        ", [$sellerId]);

        return $summary ?: ['total_reviews' => 0, 'avg_rating' => 0];
    }

    public function hasReviewedSeller($userId, $sellerId, $productId) {
        $result = $this->rawQueryOne("
            SELECT COUNT(*) as cnt
            FROM seller_reviews
            WHERE user_id = ? AND seller_id = ? AND product_id = ?
        ", [$userId, $sellerId, $productId]);

        return $result && (int)$result['cnt'] > 0;
    }

    public function canReviewSeller($userId, $sellerId, $productId) {
        $result = $this->rawQueryOne("
            SELECT o.id
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = ?
              AND oi.seller_id = ?
              AND oi.product_id = ?
              AND oi.status = 'delivered'
            ORDER BY o.created_at DESC
            LIMIT 1
        ", [$userId, $sellerId, $productId]);

        return $result ?: null;
    }
}
