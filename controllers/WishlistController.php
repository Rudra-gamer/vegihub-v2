<?php
class WishlistController extends Controller {
    public function index() {
        $this->requireAuth();
        $wishlist = new Wishlist();
        $items = $wishlist->getUserWishlist($_SESSION['user_id']);

        $this->view('profile/wishlist', [
            'pageTitle' => 'My Wishlist - Vegihub',
            'currentPage' => 'wishlist',
            'items' => $items,
        ]);
    }

    public function toggle() {
        $this->validateCsrf();

        if (!is_logged_in()) {
            $this->json(['success' => false, 'message' => 'Please login first.'], 401);
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $wishlist = new Wishlist();
        $added = $wishlist->toggle($_SESSION['user_id'], $productId);

        $this->json([
            'success' => true,
            'in_wishlist' => $added,
            'message' => $added ? 'Added to wishlist ❤️' : 'Removed from wishlist',
        ]);
    }
}
