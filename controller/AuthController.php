<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/user.php";
require_once __DIR__ . "/../model/customer.php";
require_once __DIR__ . "/../model/admin.php";
require_once __DIR__ . "/../model/cart.php";

class AuthController
{
    protected $db;

    public function __construct()
    {
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

    public function register()
    {
        // التحقق من الحقول الفاضية
        if (empty(trim($this->post('first_name')))) {
            $_SESSION['register_error'] = "First name is required.";
            $this->go("register.php");
        }

        if (empty(trim($this->post('last_name')))) {
            $_SESSION['register_error'] = "Last name is required.";
            $this->go("register.php");
        }

        if (empty(trim($this->post('email')))) {
            $_SESSION['register_error'] = "Email is required.";
            $this->go("register.php");
        }

        if (!filter_var($this->post('email'), FILTER_VALIDATE_EMAIL)) {
            $_SESSION['register_error'] = "Please enter a valid email address.";
            $this->go("register.php");
        }

        if (empty($this->post('password'))) {
            $_SESSION['register_error'] = "Password is required.";
            $this->go("register.php");
        }

        if (strlen($this->post('password')) < 6) {
            $_SESSION['register_error'] = "Password must be at least 6 characters.";
            $this->go("register.php");
        }

        if ($this->post('password') !== $this->post('confirm_password')) {
            $_SESSION['register_error'] = "Passwords do not match.";
            $this->go("register.php");
        }

        $user = new User($this->db);

        if ($user->emailExists($this->post('email'))) {
            $_SESSION['register_error'] = "This email is already registered.";
            $this->go("register.php");
        }

        $created = $user->registration(
            $this->post('email'),
            $this->post('password'),
            $this->post('first_name'),
            $this->post('last_name'),
            $this->post('phone')
        );

        if (!$created) {
            $_SESSION['register_error'] = "Registration failed. Please try again.";
            $this->go("register.php");
        }

        $userId   = $this->db->lastInsertId();
        $customer = new Customer($this->db, $userId);

        $customer->createCustomer(
            $userId,
            trim($this->post('first_name') . " " . $this->post('last_name')),
            $this->post('delivery_address', ''),
            true
        );

        $_SESSION['user_id']    = $userId;
        $_SESSION['email']      = $this->post('email');
        $_SESSION['first_name'] = $this->post('first_name');
        $_SESSION['role']       = "customer";

        $this->go("index.php");
    }

    public function login()
    {
        $user       = new User($this->db);
        $loggedUser = $user->login($this->post('email'), $this->post('password'));

        if (!$loggedUser) {
            $_SESSION['login_error'] = "Invalid email or password";
            $this->go("login.php");
        }

        $roleHint = $this->post('role_hint', '');

        $admin       = new Admin($this->db);
        $isAdminUser = $admin->isAdmin($loggedUser['user_id']);

        if ($roleHint === 'admin' && !$isAdminUser) {
            $_SESSION['login_error'] = "You are not authorized to login as Admin.";
            $this->go("login.php?role=admin");
        }

        $guestSessionId = session_id();

        session_regenerate_id(true);

        $_SESSION['user_id']    = $loggedUser['user_id'];
        $_SESSION['email']      = $loggedUser['email'];
        $_SESSION['first_name'] = $loggedUser['first_name'];

        $userCart = new Cart($this->db, $loggedUser['user_id'], session_id());
        $userCart->mergeGuestCart($guestSessionId);

        if ($isAdminUser) {
            $_SESSION['role'] = "admin";
            $this->go("admin.php");
        }

        $_SESSION['role'] = "customer";
        $this->go("index.php");
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        $this->go("login.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action     = $_POST['action'] ?? '';
    $controller = new AuthController();

    switch ($action) {
        case 'register':
            $controller->register();
            break;
        case 'login':
            $controller->login();
            break;
        case 'logout':
            $controller->logout();
            break;
        default:
            http_response_code(400);
            echo 'Invalid auth action';
            break;
    }
}