<?php

session_start();

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/order.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId     = $_SESSION['user_id'];
$isLoggedIn = true;

$orderModel = new Order($db);
$orders     = $orderModel->getByUser($userId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Automotive Hub</title>
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
    <h1>My <span>Orders</span></h1>
    <p>Track and manage your vehicle and parts orders</p>
</div>

<div class="orders-section">

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h3>No orders yet</h3>
            <p>Your order history will appear here once you make a purchase</p>
            <a href="cars.php" class="btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>

        <?php foreach ($orders as $order):
            $items = $orderModel->getItems($order['order_id']);
            $statusClass = match(strtolower($order['status'])) {
                'processing' => 'status-processing',
                'shipped'    => 'status-shipped',
                'delivered'  => 'status-delivered',
                'cancelled'  => 'status-cancelled',
                default      => 'status-processing'
            };
        ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <div class="order-id">Order #<?= $order['order_id'] ?></div>
                    <div class="order-date"><?= htmlspecialchars($order['order_date']) ?></div>
                </div>
                <span class="order-status <?= $statusClass ?>"><?= htmlspecialchars($order['status']) ?></span>
            </div>

            <?php if (!empty($items)): ?>
            <div class="order-items">
                <?php foreach ($items as $item): ?>
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="order-item-img" title="<?= htmlspecialchars($item['name']) ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div style="font-size:13px; color:#94a3b8; margin-bottom:10px;">
                <?php foreach ($items as $item): ?>
                    <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>&nbsp;
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="order-footer">
                <div>
                    <?php if ($order['discount'] > 0): ?>
                        <span style="color:#64748b; font-size:13px;">Discount: -$<?= number_format($order['discount'], 2) ?></span><br>
                    <?php endif; ?>
                    <span style="color:#64748b; font-size:13px;">Shipping: $<?= number_format($order['shipping_cost'], 2) ?></span>
                </div>
                <div class="order-total">Total: $<?= number_format($order['total_amount'], 2) ?></div>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<footer>
    <p>© 2026 Auto motive Hub Platform | All Rights Reserved</p>
</footer>

<script src="script.js"></script>
</body>
</html>
