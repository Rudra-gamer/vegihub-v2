<?php
class ReviewController extends Controller {
    public function add() {
        $this->requireAuth();
        $this->validateCsrf();
        $productId = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5) { flash('error', 'Invalid rating.'); back(); }
        $review = new Review();
        if ($review->hasReviewed($_SESSION['user_id'], $productId)) { flash('error', 'You already reviewed this product.'); back(); }
        $review->create([
            'user_id' => $_SESSION['user_id'], 'product_id' => $productId, 'rating' => $rating, 'comment' => $comment, 'created_at' => date('Y-m-d H:i:s')
        ]);
        $product = new Product();
        $product->updateRating($productId);
        flash('success', 'Review submitted! Thank you. ⭐');
        back();
    }

    public function addSeller() {
        $this->requireBuyer();
        $this->validateCsrf();

        $sellerId = (int)($_POST['seller_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($sellerId <= 0 || $productId <= 0) {
            flash('error', 'Invalid seller review request.');
            back();
        }

        if ($rating < 1 || $rating > 5) {
            flash('error', 'Invalid rating.');
            back();
        }

        $product = new Product();
        $productData = $product->find($productId);
        if (!$productData || (int)$productData['seller_id'] !== $sellerId) {
            flash('error', 'Seller review could not be matched to this product.');
            back();
        }

        $sellerReview = new SellerReview();
        if ($sellerReview->hasReviewedSeller($_SESSION['user_id'], $sellerId, $productId)) {
            flash('error', 'You already reviewed this vendor for this product.');
            back();
        }

        $eligibleOrder = $sellerReview->canReviewSeller($_SESSION['user_id'], $sellerId, $productId);
        if (!$eligibleOrder) {
            flash('error', 'You can review a vendor only after a delivered order.');
            back();
        }

        $sellerReview->create([
            'user_id' => $_SESSION['user_id'],
            'seller_id' => $sellerId,
            'product_id' => $productId,
            'order_id' => $eligibleOrder['id'],
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        flash('success', 'Vendor review submitted successfully.');
        back();
    }
}
