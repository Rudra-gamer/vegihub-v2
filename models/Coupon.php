<?php
class Coupon extends Model {
    protected $table = 'coupons';

    public function validateCoupon($code, $subtotal) {
        $coupon = $this->findBy('code', strtoupper($code));
        if (!$coupon) return ['valid' => false, 'message' => 'Invalid coupon code.'];
        if (!$coupon['is_active']) return ['valid' => false, 'message' => 'This coupon is no longer active.'];
        if (strtotime($coupon['start_date']) > time()) return ['valid' => false, 'message' => 'This coupon is not yet active.'];
        if (strtotime($coupon['end_date']) < time()) return ['valid' => false, 'message' => 'This coupon has expired.'];
        if ($coupon['used_count'] >= $coupon['usage_limit']) return ['valid' => false, 'message' => 'Coupon usage limit reached.'];
        if ($subtotal < $coupon['min_order']) return ['valid' => false, 'message' => "Minimum order of ₹{$coupon['min_order']} required."];
        if ((float)$coupon['value'] <= 0) return ['valid' => false, 'message' => 'This coupon is invalid.'];
        if ($coupon['type'] === 'percentage' && (float)$coupon['value'] > 100) {
            return ['valid' => false, 'message' => 'This coupon is invalid.'];
        }

        $discount = $coupon['type'] === 'percentage' 
            ? ($subtotal * $coupon['value'] / 100) 
            : $coupon['value'];
        
        if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
            $discount = $coupon['max_discount'];
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => round($discount, 2),
            'message' => "Coupon applied! You save ₹" . number_format($discount, 2),
        ];
    }

    public function incrementUsage($id) {
        return $this->rawExecute(
            "UPDATE coupons SET used_count = used_count + 1 WHERE id = ? AND used_count < usage_limit",
            [$id]
        );
    }
}
