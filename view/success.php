<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$orderId = $_SESSION['last_order_id'] ?? null;
unset($_SESSION['last_order_id']);

$isLoggedIn = true;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed - Automotive Hub</title>
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
        <form method="POST" action="../controller/AuthController.php" style="display:inline;">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="signup-btn">Logout</button>
        </form>
    </div>
</nav>

<div class="success-section">
    <div class="success-box">
        <div class="success-icon">✅</div>
        <h2>Order Placed Successfully!</h2>
        <p>Thank you for your purchase from Automotive Hub Platform.</p>
        <p>Your order is now being processed and will be delivered soon.</p>

        <?php if ($orderId): ?>
            <div class="order-num">Order #<?= $orderId ?></div>
        <?php endif; ?>

        <p style="color:#64748b; font-size:13px;">A confirmation will be sent once your order is shipped.</p>

        <div class="success-actions">
            <a href="orders.php" class="btn-primary">View My Orders</a>
            <a href="cars.php" class="btn-primary" style="background:transparent; border:1px solid #334155; color:#94a3b8;">
                Continue Shopping
            </a>
        </div>
    </div>
</div>

<footer>
    <p>© 2026 Auto motive Hub Platform | All Rights Reserved</p>
</footer>

<script src="script.js"></script>
</body>
</html>
