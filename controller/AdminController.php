<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/admin.php";
require_once __DIR__ . "/../model/product.php";
require_once __DIR__ . "/../model/user.php";
require_once __DIR__ . "/../model/customer.php";
require_once __DIR__ . "/../model/order.php";

class AdminController
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
        header("Location: ../view/" . $page);
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

        $admin = new Admin($this->db);

        if (!$admin->isAdmin($_SESSION['user_id'])) {
            $this->go("index.php");
        }
    }

    // Add new product
    public function addProduct()
    {
        $this->requireAdmin();

        $imagePath = null;

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . "/../view/uploads/products/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename  = uniqid("product_") . "." . $ext;
            $target    = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $imagePath = "uploads/products/" . $filename;
            }
        } elseif (!empty($this->post('image_url'))) {
            $imagePath = $this->post('image_url');
        }

        $product = new Product($this->db);
        $product->create(
            $this->post('name'),
            $this->post('description'),
            $this->post('brand'),
            $this->post('category'),
            $this->post('price'),
            $this->post('stock_count'),
            $imagePath,
            $_SESSION['user_id']
        );

        $this->go("admin-products.php");
    }

    // Edit product
    public function editProduct()
    {
        $this->requireAdmin();

        $productId = (int)$this->post('product_id');
        $data      = [
            'name'        => $this->post('name'),
            'description' => $this->post('description'),
            'brand'       => $this->post('brand'),
            'category'    => $this->post('category'),
            'price'       => $this->post('price'),
            'stock_count' => $this->post('stock_count'),
        ];

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . "/../view/uploads/products/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid("product_") . "." . $ext;
            $target   = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $data['image'] = "uploads/products/" . $filename;
            }
        } elseif (!empty($this->post('image_url'))) {
            $data['image'] = $this->post('image_url');
        }

        $product = new Product($this->db);
        $product->update($productId, $data);

        $this->go("admin-products.php");
    }

    // Delete product
    public function deleteProduct()
    {
        $this->requireAdmin();

        $product = new Product($this->db);
        $product->delete((int)$this->post('product_id'));

        $this->go("admin-products.php");
    }

    // Toggle user account status
    public function toggleUserStatus()
    {
        $this->requireAdmin();

        $customer = new Customer($this->db);
        $customer->toggleStatus((int)$this->post('user_id'));

        $this->go("admin-users.php");
    }

    // Update order status
    public function updateOrderStatus()
    {
        $this->requireAdmin();

        $order = new Order($this->db);
        $order->updateStatus((int)$this->post('order_id'), $this->post('status'));

        $this->go("admin-orders.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action     = $_POST['action'] ?? '';
    $controller = new AdminController();

    switch ($action) {
        case 'add_product':
            $controller->addProduct();
            break;
        case 'edit_product':
            $controller->editProduct();
            break;
        case 'delete_product':
            $controller->deleteProduct();
            break;
        case 'toggle_user_status':
            $controller->toggleUserStatus();
            break;
        case 'update_order_status':
            $controller->updateOrderStatus();
            break;
        default:
            http_response_code(400);
            echo 'Invalid admin action';
            break;
    }
}
