<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/cart.php";

class CartController
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

    public function addToCart()
    {
        $this->requireLogin();

        $cart = new Cart($this->db, $this->currentUserId(), session_id());
        $cart->addItem($this->post('product_id'), $this->post('quantity', 1));

        $redirectTo = $this->post('redirect_to', '../View/cart.php');
        $separator = strpos($redirectTo, '?') !== false ? '&' : '?';
        $this->redirectTo($redirectTo . $separator . 'cart_added=1');
    }

    public function updateQuantity()
    {
        $this->requireLogin();

        $cart = new Cart($this->db, $this->currentUserId(), session_id());
        $cart->updateQuantity($this->post('cart_item_id'), $this->post('quantity'));

        $this->go("cart.php");
    }

    public function removeItem()
    {
        $this->requireLogin();

        $cart = new Cart($this->db, $this->currentUserId(), session_id());
        $cart->removeItem($this->post('cart_item_id'));

        $this->go("cart.php");
    }

    public function clearCart()
    {
        $this->requireLogin();

        $cart = new Cart($this->db, $this->currentUserId(), session_id());
        $cart->clear();

        $this->go("cart.php");
    }

    public function applyCoupon()
    {
        header('Content-Type: application/json');

        $couponCode = trim($this->post('coupon_code', ''));

        if ($couponCode === '') {
            echo json_encode(['success' => false, 'message' => 'No coupon code provided.']);
            exit;
        }

        $cart   = new Cart($this->db, $this->currentUserId(), session_id());
        $result = $cart->applyCoupon($couponCode);

        if ($result === false) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon.']);
        } else {
            echo json_encode([
                'success'          => true,
                'coupon_id'        => $result['coupon_id'],
                'code'             => $result['code'],
                'subtotal'         => $result['subtotal'],
                'discount_percent' => $result['discount_percent'],
                'discount_amount'  => $result['discount_amount'],
                'final_total'      => $result['final_total'] + 50  // include shipping
            ]);
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action     = $_POST['action'] ?? '';
    $controller = new CartController();

    switch ($action) {
        case 'add_to_cart':
            $controller->addToCart();
            break;
        case 'update_cart_quantity':
            $controller->updateQuantity();
            break;
        case 'remove_cart_item':
            $controller->removeItem();
            break;
        case 'clear_cart':
            $controller->clearCart();
            break;
        case 'apply_coupon':
            $controller->applyCoupon();
            break;
        default:
            http_response_code(400);
            echo 'Invalid cart action';
            break;
    }
}