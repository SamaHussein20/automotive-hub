<?php

session_start();

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/user.php";
require_once __DIR__ . "/../model/customer.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId  = $_SESSION['user_id'];
$user    = new User($db);
$userRow = $user->getUserById($userId);

$customer    = new Customer($db, $userId);
$customerRow = $customer->getById($userId);

$msg = $_SESSION['account_msg'] ?? '';
unset($_SESSION['account_msg']);

$isLoggedIn = true;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Automotive Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <div class="logo-section">
        <div class="logo-icon">A</div>
        <div class="logo-text">Auto motive <span>Hub Platform</span></div>
    </div>
    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="cars.php">Cars</a></li>
        <li><a href="motorcycles.php">Motorcycles</a></li>
        <li><a href="electric-vehicles.php">Electric</a></li>
        <li><a href="spare-parts.php">Spare Parts</a></li>
    </ul>
    <div class="nav-actions">
        <a href="cart.php" class="cart-icon-link"><i class="fa-solid fa-cart-shopping"></i></a>
        <a href="orders.php" class="login-link">My Orders</a>
        <a href="wishlist.php" class="login-link">Wishlist</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "admin"): ?>
            <a href="admin.php" class="login-link">Admin</a>
        <?php endif; ?>
        <form method="POST" action="../controller/AuthController.php" style="display:inline;">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="signup-btn">Logout</button>
        </form>
    </div>
</nav>

<div class="page-header">
    <h1>My <span>Account</span></h1>
    <p>Welcome back, <?= htmlspecialchars($userRow['first_name'] ?? 'User') ?>!</p>
</div>

<div style="padding:50px 8%; max-width:700px;">

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success" style="margin-bottom:20px;"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Profile Info -->
    <div style="background:linear-gradient(180deg,#1e293b,#0f172a); border:1px solid #334155; border-radius:16px; padding:30px; margin-bottom:25px;">
        <h3 style="font-size:18px; font-weight:700; margin-bottom:20px; color:#60a5fa;">
            <i class="fa-solid fa-user" style="margin-right:8px;"></i> Profile Information
        </h3>
        <form method="POST" action="../controller/AccountController.php">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars(($userRow['first_name'] ?? '') . ' ' . ($userRow['last_name'] ?? '')) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= htmlspecialchars($userRow['email'] ?? '') ?>" disabled style="opacity:0.5;">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($userRow['phone'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-primary">Save Profile</button>
        </form>
    </div>

    <!-- Delivery Address -->
    <div style="background:linear-gradient(180deg,#1e293b,#0f172a); border:1px solid #334155; border-radius:16px; padding:30px; margin-bottom:25px;">
        <h3 style="font-size:18px; font-weight:700; margin-bottom:20px; color:#60a5fa;">
            <i class="fa-solid fa-location-dot" style="margin-right:8px;"></i> Delivery Address
        </h3>
        <form method="POST" action="../controller/AccountController.php">
            <input type="hidden" name="action" value="update_address">
            <div class="form-group">
                <label>Address</label>
                <textarea name="delivery_address"><?= htmlspecialchars($customerRow['delivery_address'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn-primary">Save Address</button>
        </form>
    </div>

    <!-- Change Password -->
    <div style="background:linear-gradient(180deg,#1e293b,#0f172a); border:1px solid #334155; border-radius:16px; padding:30px;">
        <h3 style="font-size:18px; font-weight:700; margin-bottom:20px; color:#60a5fa;">
            <i class="fa-solid fa-lock" style="margin-right:8px;"></i> Change Password
        </h3>
        <form method="POST" action="../controller/AccountController.php">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Min. 6 characters" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Repeat new password" required>
            </div>
            <button type="submit" class="btn-primary">Change Password</button>
        </form>
    </div>

</div>

<footer>
    <p>© 2026 Auto motive Hub Platform | All Rights Reserved</p>
</footer>

<script src="script.js"></script>
</body>
</html>
