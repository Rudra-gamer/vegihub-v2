<?php
class ProductController extends Controller {
    
    public function index() {
        $product = new Product();
        $category = new Category();
        $filters = [
            'category' => $_GET['category'] ?? '',
            'min_price' => $_GET['min_price'] ?? '',
            'max_price' => $_GET['max_price'] ?? '',
            'organic' => $_GET['organic'] ?? '',
            'rating' => $_GET['rating'] ?? '',
            'seller_id' => $_GET['seller_id'] ?? '',
            'in_stock' => $_GET['in_stock'] ?? '',
            'sort' => $_GET['sort'] ?? 'newest',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $product->searchProducts('', $filters, $page);
        $categories = $category->getWithProductCount();

        $this->view('products/index', [
            'pageTitle' => 'All Products - Vegihub',
            'currentPage' => 'products',
            'products' => $result['data'],
            'pagination' => $result,
            'categories' => $categories,
            'sellerOptions' => $product->getSellerOptions(),
            'filters' => $filters,
            'extraCss' => ['product.css'],
        ]);
    }

    public function search() {
        $q = trim($_GET['q'] ?? '');
        $product = new Product();
        $category = new Category();
        $filters = [
            'category' => $_GET['category'] ?? '',
            'seller_id' => $_GET['seller_id'] ?? '',
            'in_stock' => $_GET['in_stock'] ?? '',
            'sort' => $_GET['sort'] ?? 'popular',
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $product->searchProducts($q, $filters, $page);

        $this->view('products/index', [
            'pageTitle' => "Search: {$q} - Vegihub",
            'currentPage' => 'products',
            'searchQuery' => $q,
            'products' => $result['data'],
            'pagination' => $result,
            'categories' => $category->getWithProductCount(),
            'sellerOptions' => $product->getSellerOptions(),
            'filters' => $filters,
            'extraCss' => ['product.css'],
        ]);
    }

    public function detail($slug) {
        $product = new Product();
        $p = $product->getBySlug($slug);
        if (!$p) {
            http_response_code(404);
            include VIEW_PATH . '/errors/404.php';
            return;
        }

        $product->incrementViews($p['id']);
        $review = new Review();
        $sellerReview = new SellerReview();
        $reviews = $review->getProductReviews($p['id']);
        $sellerReviews = $sellerReview->getSellerReviews($p['seller_id']);
        $sellerRating = $sellerReview->getSellerRatingSummary($p['seller_id']);
        $related = $product->getRelated($p['id'], $p['category_id']);
        $hasReviewed = is_logged_in() ? $review->hasReviewed($_SESSION['user_id'], $p['id']) : false;
        $hasReviewedSeller = is_logged_in() ? $sellerReview->hasReviewedSeller($_SESSION['user_id'], $p['seller_id'], $p['id']) : false;
        $canReview = is_logged_in() ? ($review->canReview($_SESSION['user_id'], $p['id']) && !$hasReviewed) : false;
        $canReviewSeller = is_logged_in()
            ? ((bool)$sellerReview->canReviewSeller($_SESSION['user_id'], $p['seller_id'], $p['id']) && !$hasReviewedSeller)
            : false;

        $this->view('products/detail', [
            'pageTitle' => $p['name'] . ' - Vegihub',
            'product' => $p,
            'reviews' => $reviews,
            'sellerReviews' => $sellerReviews,
            'sellerRating' => $sellerRating,
            'related' => $related,
            'canReview' => $canReview,
            'canReviewSeller' => $canReviewSeller,
            'hasReviewed' => $hasReviewed,
            'hasReviewedSeller' => $hasReviewedSeller,
            'extraCss' => ['product.css'],
        ]);
    }

    public function byCategory($slug) {
        $category = new Category();
        $cat = $category->getBySlug($slug);
        if (!$cat) {
            http_response_code(404);
            include VIEW_PATH . '/errors/404.php';
            return;
        }

        $product = new Product();
        $filters = [
            'category' => $slug,
            'seller_id' => $_GET['seller_id'] ?? '',
            'in_stock' => $_GET['in_stock'] ?? '',
            'sort' => $_GET['sort'] ?? 'popular'
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $product->searchProducts('', $filters, $page);

        $this->view('products/index', [
            'pageTitle' => $cat['name'] . ' - Vegihub',
            'currentPage' => 'products',
            'categoryName' => $cat['name'],
            'products' => $result['data'],
            'pagination' => $result,
            'categories' => $category->getWithProductCount(),
            'sellerOptions' => $product->getSellerOptions(),
            'filters' => $filters,
            'extraCss' => ['product.css'],
        ]);
    }
}
