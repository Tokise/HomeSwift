<?php
/**
 * Home Controller
 */
require_once SRC_PATH . '/controllers/Controller.php';
require_once SRC_PATH . '/models/Service.php';
require_once SRC_PATH . '/models/ServiceCategory.php';

class HomeController extends Controller {
    private $serviceModel;
    private $categoryModel;
    
    public function __construct() {
        $this->serviceModel = new Service();
        $this->categoryModel = new ServiceCategory();
    }
    
    /**
     * Landing page
     */
    public function index() {
        // Make sure any debug routing information is cleared
        if (ob_get_length()) {
            ob_clean();
        }
        
        $this->render('home/landing', [
            'title' => 'Home Services Made Easy - HomEase'
        ]);
    }
    
    /**
     * Services homepage
     */
    public function services() {
        // Get featured services
        $featuredServices = $this->serviceModel->getFeatured(6);
        
        // Get all service categories
        $categories = $this->categoryModel->getAll();
        
        $this->render('home/index', [
            'title' => 'Home Services Made Easy',
            'featuredServices' => $featuredServices,
            'categories' => $categories
        ]);
    }
    
    /**
     * About page
     */
    public function about() {
        $this->render('home/about', [
            'title' => 'About Us'
        ]);
    }
    
    /**
     * Contact page
     */
    public function contact() {
        $this->render('home/contact', [
            'title' => 'Contact Us'
        ]);
    }
    
    /**
     * Process contact form
     */
    public function processContact() {
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(APP_URL . '/contact');
            return;
        }
        
        // Validate inputs
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        if (empty($name) || empty($email) || empty($message)) {
            $_SESSION['flash_message'] = 'Please fill in all required fields.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect(APP_URL . '/contact');
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_message'] = 'Please enter a valid email address.';
            $_SESSION['flash_type'] = 'danger';
            $this->redirect(APP_URL . '/contact');
            return;
        }
        
        // In a real application, you would send an email here
        // For now, we'll just simulate it
        
        $_SESSION['flash_message'] = 'Thank you for your message! We will get back to you soon.';
        $_SESSION['flash_type'] = 'success';
        $this->redirect(APP_URL . '/contact');
    }
    
    /**
     * Terms of Service page
     */
    public function termsOfService() {
        $this->render('home/terms', [
            'title' => 'Terms of Service'
        ]);
    }
    
    /**
     * Privacy Policy page
     */
    public function privacyPolicy() {
        $this->render('home/privacy', [
            'title' => 'Privacy Policy'
        ]);
    }
} 