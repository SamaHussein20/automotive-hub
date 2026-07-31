<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/product.php";

class ShopController
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

    protected function get($key, $default = "")
    {
        return $_GET[$key] ?? $default;
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

    protected function requireAdmin()
    {
        $this->requireLogin();

        require_once __DIR__ . "/../Model/admin.php";

        $admin = new Admin($this->db);

        if (!$admin->isAdmin($_SESSION['user_id'])) {
            $this->go("index.php");
        }
    }

    // Get all products
    public function showProducts()
    {
        $product = new Product($this->db);
        return $product->getAll();
    }

    // Get filtered products
    public function filterProducts($brand = "", $category = "", $maxPrice = "", $minRating = "", $sort = "")
    {
        $product = new Product($this->db);
        return $product->filter($brand, $category, $maxPrice, $minRating, $sort);
    }

    // Get product by id
    public function getProduct($productId)
    {
        $product = new Product($this->db);
        return $product->getById($productId);
    }

    // Get products by category
    public function getByCategory($category)
    {
        $product = new Product($this->db);
        return $product->getByCategory($category);
    }
}
