<?php

session_start();

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/admin.php";
require_once __DIR__ . "/../model/order.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

$adminModel = new Admin($db);
if (!isset($_SESSION['user_id']) || !$adminModel->isAdmin($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$orderModel = new Order($db);
$orders     = $orderModel->getAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-container">

    <aside class="admin-sidebar">
        <div class="logo">⚙ Admin Panel</div>
        <nav class="admin-nav">
            <a href="admin.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="admin-products.php"><i class="fa-solid fa-car"></i> Products</a>
            <a href="admin-orders.php" class="active"><i class="fa-solid fa-box"></i> Orders</a>
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

    <main class="admin-content">
        <h1 class="admin-title">Manage <span>Orders</span></h1>

        <div class="table-wrapper">
            <div class="table-header">
                <h3>All Orders (<?= count($orders) ?>)</h3>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o):
                        $statusClass = match(strtolower($o['status'])) {
                            'processing' => 'status-processing',
                            'shipped'    => 'status-shipped',
                            'delivered'  => 'status-delivered',
                            'cancelled'  => 'status-cancelled',
                            default      => 'status-processing'
                        };
                    ?>
                    <tr>
                        <td><?= $o['order_id'] ?></td>
                        <td><?= htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) ?></td>
                        <td><?= htmlspecialchars($o['email']) ?></td>
                        <td><?= htmlspecialchars($o['order_date']) ?></td>
                        <td>$<?= number_format($o['total_amount'], 2) ?></td>
                        <td><span class="order-status <?= $statusClass ?>"><?= htmlspecialchars($o['status']) ?></span></td>
                        <td>
                            <form method="POST" action="../controller/AdminController.php" style="display:flex; gap:6px; align-items:center;">
                                <input type="hidden" name="action" value="update_order_status">
                                <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                <select name="status" style="padding:5px 8px; background:#1e293b; border:1px solid #334155; color:white; border-radius:6px; font-size:12px;">
                                    <option value="Processing" <?= $o['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="Shipped"    <?= $o['status'] == 'Shipped'    ? 'selected' : '' ?>>Shipped</option>
                                    <option value="Delivered"  <?= $o['status'] == 'Delivered'  ? 'selected' : '' ?>>Delivered</option>
                                    <option value="Cancelled"  <?= $o['status'] == 'Cancelled'  ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="action-btn edit" style="padding:5px 10px;">Save</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                    <tr><td colspan="7" style="text-align:center; color:#64748b; padding:30px;">No orders yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="script.js"></script>
</body>
</html>
