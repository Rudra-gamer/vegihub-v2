<?php
class Category extends Model {
    protected $table = 'categories';

    public function getActive() {
        return $this->rawQuery("
            SELECT c.*,
                   (
                       SELECT COUNT(*)
                       FROM products p
                       WHERE p.category_id = c.id
                         AND p.status = 'active'
                   ) AS product_count
            FROM categories c
            WHERE c.is_active = 1
            ORDER BY c.sort_order ASC
        ");
    }

    public function getBySlug($slug) {
        return $this->findBy('slug', $slug);
    }

    public function getWithProductCount() {
        return $this->rawQuery("
            SELECT c.*,
                   (
                       SELECT COUNT(*)
                       FROM products p
                       WHERE p.category_id = c.id
                         AND p.status = 'active'
                   ) AS product_count
            FROM categories c
            WHERE c.is_active = 1
            ORDER BY c.sort_order ASC
        ");
    }
}
