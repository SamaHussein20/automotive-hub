<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/user.php";
require_once __DIR__ . "/../model/customer.php";

class AccountController
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

    protected function post($key, $default = "")
    {
        return $_POST[$key] ?? $default;
    }

    protected function requireLogin()
    {
        if (empty($_SESSION['user_id'])) {
            $this->go("login.php");
        }
    }

    // Update profile
    public function updateProfile()
    {
        $this->requireLogin();

        $userId    = $_SESSION['user_id'];
        $nameParts = explode(" ", trim($this->post('name')), 2);
        $firstName = $nameParts[0] ?? "";
        $lastName  = $nameParts[1] ?? "";

        $user = new User($this->db);
        $user->updateProfile($userId, $firstName, $lastName, $this->post('phone'));

        $_SESSION['first_name'] = $firstName;
        $_SESSION['account_msg'] = "Profile updated successfully.";

        $this->go("account.php");
    }

    // Update delivery address
    public function updateAddress()
    {
        $this->requireLogin();

        $userId   = $_SESSION['user_id'];
        $customer = new Customer($this->db, $userId);
        $customer->updateAddress($userId, $this->post('delivery_address'));

        $_SESSION['account_msg'] = "Address updated successfully.";
        $this->go("account.php");
    }

    // Change password
    public function changePassword()
    {
        $this->requireLogin();

        $userId      = $_SESSION['user_id'];
        $newPassword = $this->post('new_password');
        $confirm     = $this->post('confirm_password');

        if (strlen($newPassword) < 6) {
            $_SESSION['account_msg'] = "Password must be at least 6 characters.";
            $this->go("account.php");
        }

        if ($newPassword !== $confirm) {
            $_SESSION['account_msg'] = "Passwords do not match.";
            $this->go("account.php");
        }

        $user = new User($this->db);
        $user->changePassword($userId, $newPassword);

        $_SESSION['account_msg'] = "Password changed successfully.";
        $this->go("account.php");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    $action     = $_POST['action'] ?? '';
    $controller = new AccountController();

    switch ($action) {
        case 'update_profile':
            $controller->updateProfile();
            break;
        case 'update_address':
            $controller->updateAddress();
            break;
        case 'change_password':
            $controller->changePassword();
            break;
        default:
            http_response_code(400);
            echo 'Invalid account action';
            break;
    }
}
