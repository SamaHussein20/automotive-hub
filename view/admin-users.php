<?php

session_start();

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/admin.php";
require_once __DIR__ . "/../model/user.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

$adminModel = new Admin($db);
if (!isset($_SESSION['user_id']) || !$adminModel->isAdmin($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userModel = new User($db);
$users     = $userModel->getAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin</title>
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
            <a href="admin-orders.php"><i class="fa-solid fa-box"></i> Orders</a>
            <a href="admin-users.php" class="active"><i class="fa-solid fa-users"></i> Users</a>
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
        <h1 class="admin-title">Manage <span>Users</span></h1>

        <div class="table-wrapper">
            <div class="table-header">
                <h3>All Users (<?= count($users) ?>)</h3>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['user_id'] ?></td>
                        <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($u['registration_date'] ?? '-') ?></td>
                        <td>
                            <?php if ($u['account_status'] === null): ?>
                                <span style="color:#60a5fa; font-size:12px; font-weight:700;">Admin</span>
                            <?php elseif ($u['account_status']): ?>
                                <span class="order-status status-delivered">Active</span>
                            <?php else: ?>
                                <span class="order-status status-cancelled">Blocked</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['account_status'] !== null && $u['user_id'] != $_SESSION['user_id']): ?>
                                <form method="POST" action="../controller/AdminController.php" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_user_status">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <button type="submit" class="action-btn <?= $u['account_status'] ? 'delete' : 'edit' ?>">
                                        <?= $u['account_status'] ? 'Block' : 'Unblock' ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="color:#475569; font-size:12px;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="7" style="text-align:center; color:#64748b; padding:30px;">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="script.js"></script>
</body>
</html>
