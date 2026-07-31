<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/wishlist.php";

class WishlistController
{
    protected $db;

    public function __construct()
    {
        // Singleton Pattern: reuse one shared database connection.
        $this->db = Database::getInstance()->getConnection();

        if (!$this->db) {
            die("Database connection failed");
        }
    }

    protected function go($page)
    {
        header("Location: ../View/" . $page);
        exit;
    }

    protected function redirectTo($url)
    {
        header("Location: " . $url);
        exit;
    }

    protected function post($key, $default = "")
    {
        return $_POST[$key] ?? $default;
    }

    protected function currentUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    protected function requireLogin()
    {
        if (empty($_SESSION['user_id'])) {
            $this->go("login.php");
        }
    }

    public function addToWishlist()
    {
        $this->requireLogin();

        $wishlist = new Wishlist($this->db, $this->currentUserId());
        $wishlist->addItem($this->post('product_id'));

        $this->redirectTo($this->post('redirect_to', '../View/wishlist.php'));
    }

    public function removeFromWishlist()
    {
        $this->requireLogin();

        $wishlist = new Wishlist($this->db, $this->currentUserId());
        $wishlist->removeItem($this->post('product_id'));

        $this->go("wishlist.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action     = $_POST['action'] ?? '';
    $controller = new WishlistController();

    switch ($action) {
        case 'add_to_wishlist':
            $controller->addToWishlist();
            break;
        case 'remove_from_wishlist':
            $controller->removeFromWishlist();
            break;
        default:
            http_response_code(400);
            echo 'Invalid wishlist action';
            break;
    }
}
