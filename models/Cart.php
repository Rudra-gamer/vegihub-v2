<?php
class Cart extends Model {
    protected $table = 'cart';

    public function getCartItems($userId) {
        return $this->rawQuery("
            SELECT c.*, p.name, p.slug, p.price, p.sale_price, p.image, p.unit, p.stock, p.status,
                   u.name as seller_name, u.id as seller_id
            FROM cart c
            JOIN products p ON c.product_id = p.id
            JOIN users u ON p.seller_id = u.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ", [$userId]);
    }

    public function getCartTotal($userId) {
        return $this->rawQueryOne("
            SELECT 
                COUNT(*) as item_count,
                COALESCE(SUM(c.quantity), 0) as total_qty,
                COALESCE(SUM(c.quantity * COALESCE(p.sale_price, p.price)), 0) as subtotal
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND p.status = 'active'
        ", [$userId]);
    }

    public function addItem($userId, $productId, $quantity = 1) {
        $existing = $this->rawQueryOne(
            "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
        
        if ($existing) {
            $this->rawExecute(
                "UPDATE cart SET quantity = quantity + ? WHERE id = ?",
                [$quantity, $existing['id']]
            );
            return $existing['id'];
        }
        
        return $this->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateQuantity($userId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->rawExecute(
                "DELETE FROM cart WHERE user_id = ? AND product_id = ?",
                [$userId, $productId]
            );
        }
        return $this->rawExecute(
            "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?",
            [$quantity, $userId, $productId]
        );
    }

    public function removeItem($userId, $productId) {
        return $this->rawExecute(
            "DELETE FROM cart WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
    }

    public function clearCart($userId) {
        return $this->rawExecute("DELETE FROM cart WHERE user_id = ?", [$userId]);
    }

    public function getCount($userId) {
        return (int)$this->rawQueryOne(
            "SELECT COALESCE(SUM(quantity), 0) as cnt FROM cart WHERE user_id = ?",
            [$userId]
        )['cnt'];
    }

    public function findItem($userId, $productId) {
        return $this->rawQueryOne(
            "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
    }

    public function clearSelectedItems($userId, array $productIds) {
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        if (empty($productIds)) {
            return true;
        }

        $placeholders = implode(', ', array_fill(0, count($productIds), '?'));
        $params = array_merge([$userId], $productIds);

        return $this->rawExecute(
            "DELETE FROM cart WHERE user_id = ? AND product_id IN ({$placeholders})",
            $params
        );
    }
}
