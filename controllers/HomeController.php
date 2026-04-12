<?php
class HomeController extends Controller {
    
    public function index() {
        $product = new Product();
        $category = new Category();

        $data = [
            'pageTitle' => 'Vegihub - Fresh Vegetables Delivered to Your Door',
            'currentPage' => 'home',
            'featuredProducts' => $product->getFeatured(8),
            'deals' => $product->getDeals(6),
            'newArrivals' => $product->getNewArrivals(8),
            'bestSellers' => $product->getBestSellers(4),
            'categories' => $category->getWithProductCount(),
        ];

        $this->view('home/index', $data);
    }

    public function about() {
        $this->view('home/about', ['pageTitle' => 'About Us - Vegihub', 'currentPage' => 'about']);
    }

    public function contact() {
        $this->view('home/contact', ['pageTitle' => 'Contact Us - Vegihub', 'currentPage' => 'contact']);
    }

    public function submitContact() {
        $this->validateCsrf();
        
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$name || !$email || !$message) {
            flash('error', 'All fields are required.');
            back();
        }

        $msg = new Message();
        $msg->create([
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        flash('success', 'Thank you for your message! We will get back to you soon.');
        redirect(base_url('contact'));
    }
}
