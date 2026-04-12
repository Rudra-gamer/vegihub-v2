<?php
class Review extends Model {
    protected $table = 'reviews';

    public function getProductReviews($productId, $limit = 10) {
        return $this->rawQuery("
            SELECT r.*, u.name as user_name, u.avatar as user_avatar
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ?
            ORDER BY r.created_at DESC
            LIMIT ?
        ", [$productId, $limit]);
    }

    public function hasReviewed($userId, $productId) {
        return $this->exists('user_id', $userId) && 
            $this->rawQueryOne("SELECT COUNT(*) as cnt FROM reviews WHERE user_id = ? AND product_id = ?", [$userId, $productId])['cnt'] > 0;
    }

    public function canReview($userId, $productId) {
        $result = $this->rawQueryOne("
            SELECT COUNT(*) as cnt FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = ? AND oi.product_id = ? AND oi.status = 'delivered'
        ", [$userId, $productId]);
        return $result && $result['cnt'] > 0;
    }
}
