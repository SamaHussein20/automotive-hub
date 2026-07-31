<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/order.php";
require_once __DIR__ . "/../Model/cart.php";
require_once __DIR__ . "/../Model/product.php";

class OrderController
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

    protected function requireAdmin()
    {
        $this->requireLogin();

        require_once __DIR__ . "/../Model/admin.php";
        $admin = new Admin($this->db);

        if (!$admin->isAdmin($_SESSION['user_id'])) {
            $this->go("index.php");
        }
    }

    // Place order from cart
    public function placeOrder()
    {
        $this->requireLogin();

        $userId = $this->currentUserId();
        $cart   = new Cart($this->db, $userId, session_id());
        $items  = $cart->getItems();

        if (empty($items)) {
            $_SESSION['order_error'] = "Your cart is empty";
            $this->go("cart.php");
        }

        $subtotal     = $cart->getTotal();
        $discount     = 0;
        $couponId     = null;
        $shippingCost = 50; // Fixed shipping

        // Apply coupon if provided
        $couponCode = $this->post('coupon_code');
        if (!empty($couponCode)) {
            $couponData = $cart->applyCoupon($couponCode);
            if ($couponData) {
                $discount = $couponData['discount_amount'];
                $couponId = $couponData['coupon_id'];
            }
        }

        $totalAmount = $subtotal - $discount + $shippingCost;

        $order    = new Order($this->db);
        $orderId  = $order->create($userId, $totalAmount, $discount, $shippingCost, $couponId);

        if (!$orderId) {
            $_SESSION['order_error'] = "Failed to place order";
            $this->go("cart.php");
        }

        // Add items to order and decrease stock
        $product = new Product($this->db);

        foreach ($items as $item) {
            $order->addItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
            $product->decreaseStock($item['product_id'], $item['quantity']);
        }

        // Clear cart
        $cart->clear();

        $_SESSION['last_order_id'] = $orderId;
        $this->go("success.php");
    }

    // Admin: update order status
    public function updateStatus()
    {
        $this->requireAdmin();

        $order = new Order($this->db);
        $order->updateStatus($this->post('order_id'), $this->post('status'));

        $this->go("admin-orders.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action     = $_POST['action'] ?? '';
    $controller = new OrderController();

    switch ($action) {
        case 'place_order':
            $controller->placeOrder();
            break;
        case 'update_status':
            $controller->updateStatus();
            break;
        default:
            http_response_code(400);
            echo 'Invalid order action';
            break;
    }
}
