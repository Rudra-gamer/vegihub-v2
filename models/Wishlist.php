<?php
class Wishlist extends Model {
    protected $table = 'wishlist';

    public function getUserWishlist($userId) {
        return $this->rawQuery("
            SELECT w.*, p.name, p.slug, p.price, p.sale_price, p.image, p.unit, p.stock, p.status,
                   COALESCE((SELECT ROUND(AVG(r.rating), 1) FROM reviews r WHERE r.product_id = p.id), 0) as avg_rating,
                   (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) as total_reviews,
                   u.name as seller_name
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            JOIN users u ON p.seller_id = u.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ", [$userId]);
    }

    public function toggle($userId, $productId) {
        $exists = $this->rawQueryOne("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
        if ($exists) {
            $this->delete($exists['id']);
            return false;
        }
        $this->create(['user_id' => $userId, 'product_id' => $productId, 'created_at' => date('Y-m-d H:i:s')]);
        return true;
    }

    public function isInWishlist($userId, $productId) {
        return (bool)$this->rawQueryOne("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
    }
}
