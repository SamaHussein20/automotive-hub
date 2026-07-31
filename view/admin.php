<?php

session_start();

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/admin.php";
require_once __DIR__ . "/../model/product.php";
require_once __DIR__ . "/../model/user.php";
require_once __DIR__ . "/../model/order.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

// Admin check
$adminModel = new Admin($db);
if (!isset($_SESSION['user_id']) || !$adminModel->isAdmin($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$productModel = new Product($db);
$userModel    = new User($db);
$orderModel   = new Order($db);

$allProducts = $productModel->getAll();
$allUsers    = $userModel->getAll();
$allOrders   = $orderModel->getAll();

$totalProducts = count($allProducts);
$totalUsers    = count($allUsers);
$totalOrders   = count($allOrders);
$totalRevenue  = array_sum(array_column($allOrders, 'total_amount'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Automotive Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-container">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="logo">⚙ Admin Panel</div>
        <nav class="admin-nav">
            <a href="admin.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="admin-products.php"><i class="fa-solid fa-car"></i> Products</a>
            <a href="admin-orders.php"><i class="fa-solid fa-box"></i> Orders</a>
            <a href="admin-users.php"><i class="fa-solid fa-users"></i> Users</a>
            <a href="index.php"><i class="fa-solid fa-house"></i> Back to Site</a>
            <form method="POST" action="../controller/AuthController.php" style="padding:12px 20px;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" style="background:transparent;border:none;color:#94a3b8;cursor:pointer;font-size:14px;text-align:left;width:100%;">
                    <i class="fa-solid fa-right-from-bracket" style="width:20px;margin-right:8px;"></i> Logout
                </button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h1 class="admin-title">Dashboard <span>Overview</span></h1>
            <form method="POST" action="../controller/AuthController.php">
                <input type="hidden" name="action" value="logout">
                <button type="submit" style="background:#ef4444; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-size:14px; font-weight:bold;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🚗</div>
                <div class="stat-num"><?= $totalProducts ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-num"><?= $totalUsers ?></div>
                <div class="stat-label">Registered Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-num"><?= $totalOrders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-num">$<?= number_format($totalRevenue, 0) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Quick Action Cards -->
        <div class="action-cards-grid">

            <div class="action-card action-card--edit">
                <div class="action-card__icon"><i class="fas fa-pen-to-square"></i></div>
                <div class="action-card__body">
                    <h3>Edit</h3>
                    <p>Update existing products, prices, stock, or details</p>
                </div>
                <a href="admin-products.php" class="action-card__btn">Go to Edit <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="action-card action-card--add">
                <div class="action-card__icon"><i class="fas fa-plus-circle"></i></div>
                <div class="action-card__body">
                    <h3>Add New</h3>
                    <p>Add a brand new product to the store catalog</p>
                </div>
                <a href="admin-products.php" class="action-card__btn">Go to Add <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="action-card action-card--delete">
                <div class="action-card__icon"><i class="fas fa-trash-can"></i></div>
                <div class="action-card__body">
                    <h3>Delete</h3>
                    <p>Remove a product permanently from the catalog</p>
                </div>
                <a href="admin-products.php" class="action-card__btn">Go to Delete <i class="fas fa-arrow-right"></i></a>
            </div>

        </div>

        <!-- Recent Orders -->
        <div class="table-wrapper">
            <div class="table-header">
                <h3>Recent Orders</h3>
                <a href="admin-orders.php" class="action-btn edit">View All</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($allOrders, 0, 8) as $o): ?>
                    <tr>
                        <td><?= $o['order_id'] ?></td>
                        <td><?= htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) ?></td>
                        <td><?= htmlspecialchars($o['order_date']) ?></td>
                        <td>$<?= number_format($o['total_amount'], 2) ?></td>
                        <td>
                            <?php
                            $cls = match(strtolower($o['status'])) {
                                'processing' => 'status-processing',
                                'shipped'    => 'status-shipped',
                                'delivered'  => 'status-delivered',
                                'cancelled'  => 'status-cancelled',
                                default      => 'status-processing'
                            };
                            ?>
                            <span class="order-status <?= $cls ?>"><?= htmlspecialchars($o['status']) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($allOrders)): ?>
                    <tr><td colspan="5" style="text-align:center; color:#64748b; padding:30px;">No orders yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Products -->
        <div class="table-wrapper">
            <div class="table-header">
                <h3>Recent Products</h3>
                <a href="admin-products.php" class="action-btn add">+ Add Product</a>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($allProducts, 0, 6) as $p): ?>
                    <tr>
                        <td>
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="">
                            <?php else: ?>
                                <div style="width:55px;height:45px;background:#1e293b;border-radius:6px;display:flex;align-items:center;justify-content:center;">🚗</div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['brand']) ?></td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td><?= $p['stock_count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<script src="script.js"></script>
</body>
</html>