<?php

session_start();

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/admin.php";
require_once __DIR__ . "/../model/product.php";
require_once __DIR__ . "/../model/user.php";
require_once __DIR__ . "/../model/order.php";

$db = Database::getInstance()->getConnection();

$adminModel = new Admin($db);
if (!isset($_SESSION['user_id']) || !$adminModel->isAdmin($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$productModel  = new Product($db);
$userModel     = new User($db);
$orderModel    = new Order($db);

$totalProducts = count($productModel->getAll());
$totalUsers    = count($userModel->getAll());
$totalOrders   = count($orderModel->getAll());
$adminName     = $_SESSION['first_name'] ?? 'Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Automotive Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .dash-welcome { margin-bottom: 12px; }
        .dash-welcome p { color: #64748b; font-size: 15px; margin-top: 6px; }

        .mini-stats {
            display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 48px;
        }
        .mini-stat {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            border: 1px solid #334155; border-radius: 14px;
            padding: 18px 26px; display: flex; align-items: center;
            gap: 14px; flex: 1; min-width: 160px;
            transition: transform 0.25s, box-shadow 0.25s;
        }
        .mini-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .mini-stat-icon { font-size: 24px; }
        .mini-stat-info .num { font-size: 24px; font-weight: 800; color: #60a5fa; }
        .mini-stat-info .lbl { font-size: 12px; color: #64748b; margin-top: 2px; }

        .action-cards-section { margin-bottom: 50px; }
        .action-cards-section h2 {
            font-size: 18px; color: #94a3b8; font-weight: 600;
            margin-bottom: 24px; letter-spacing: 0.05em; text-transform: uppercase;
        }
        .action-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 26px;
        }

        .action-card {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            border: 1px solid #334155; border-radius: 22px;
            padding: 36px 30px 30px; display: flex; flex-direction: column;
            gap: 20px; position: relative; overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }
        .action-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0;
            height: 4px; border-radius: 22px 22px 0 0;
        }
        .action-card::after {
            content: ''; position: absolute; top: -40px; right: -40px;
            width: 150px; height: 150px; border-radius: 50%;
            opacity: 0.06; transition: opacity 0.3s;
        }

        /* Edit – Blue */
        .edit-card::before  { background: linear-gradient(90deg, #2563eb, #60a5fa); }
        .edit-card::after   { background: #2563eb; }
        .edit-card:hover    { transform: translateY(-8px); box-shadow: 0 24px 60px rgba(37,99,235,0.22); border-color: rgba(37,99,235,0.45); }
        .edit-card:hover::after { opacity: 0.12; }

        /* Add – Green */
        .add-card::before   { background: linear-gradient(90deg, #16a34a, #4ade80); }
        .add-card::after    { background: #16a34a; }
        .add-card:hover     { transform: translateY(-8px); box-shadow: 0 24px 60px rgba(22,163,74,0.22); border-color: rgba(22,163,74,0.45); }
        .add-card:hover::after { opacity: 0.12; }

        /* Delete – Red */
        .delete-card::before  { background: linear-gradient(90deg, #dc2626, #f87171); }
        .delete-card::after   { background: #dc2626; }
        .delete-card:hover    { transform: translateY(-8px); box-shadow: 0 24px 60px rgba(220,38,38,0.22); border-color: rgba(220,38,38,0.45); }
        .delete-card:hover::after { opacity: 0.12; }

        .card-icon {
            width: 64px; height: 64px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; position: relative; z-index: 1;
        }
        .edit-card   .card-icon { background: rgba(37,99,235,0.15); color: #60a5fa; }
        .add-card    .card-icon { background: rgba(22,163,74,0.15);  color: #4ade80; }
        .delete-card .card-icon { background: rgba(220,38,38,0.15); color: #f87171; }

        .card-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 11px; font-weight: 700; padding: 4px 12px;
            border-radius: 20px; text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 6px;
        }
        .edit-card   .card-badge { background: rgba(37,99,235,0.15); color: #60a5fa; }
        .add-card    .card-badge { background: rgba(22,163,74,0.15);  color: #4ade80; }
        .delete-card .card-badge { background: rgba(220,38,38,0.15); color: #f87171; }

        .card-body { position: relative; z-index: 1; }
        .card-body h3 { font-size: 22px; font-weight: 800; margin-bottom: 10px; }
        .edit-card   .card-body h3 { color: #60a5fa; }
        .add-card    .card-body h3 { color: #4ade80; }
        .delete-card .card-body h3 { color: #f87171; }
        .card-body p { color: #64748b; font-size: 14px; line-height: 1.7; }

        .card-features {
            list-style: none; padding: 0; margin: 0;
            display: flex; flex-direction: column; gap: 7px;
            position: relative; z-index: 1;
        }
        .card-features li { font-size: 13px; color: #94a3b8; display: flex; align-items: center; gap: 8px; }
        .card-features li i { font-size: 10px; width: 16px; }
        .edit-card   .card-features li i { color: #60a5fa; }
        .add-card    .card-features li i { color: #4ade80; }
        .delete-card .card-features li i { color: #f87171; }

        .card-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 13px 26px; border-radius: 11px; font-size: 14px;
            font-weight: 700; text-decoration: none;
            transition: background 0.25s, box-shadow 0.25s, transform 0.2s;
            align-self: flex-start; margin-top: auto; position: relative; z-index: 1;
        }
        .edit-card   .card-btn { background: #2563eb; color: #fff; }
        .edit-card   .card-btn:hover { background: #1d4ed8; box-shadow: 0 8px 24px rgba(37,99,235,0.45); transform: scale(1.03); }
        .add-card    .card-btn { background: #16a34a; color: #fff; }
        .add-card    .card-btn:hover { background: #15803d; box-shadow: 0 8px 24px rgba(22,163,74,0.45); transform: scale(1.03); }
        .delete-card .card-btn { background: #dc2626; color: #fff; }
        .delete-card .card-btn:hover { background: #b91c1c; box-shadow: 0 8px 24px rgba(220,38,38,0.45); transform: scale(1.03); }

        .quick-nav { display: flex; gap: 12px; flex-wrap: wrap; padding: 20px 0 4px; }
    </style>
</head>
<body>

<div class="admin-container">

    <aside class="admin-sidebar">
        <div class="logo">⚙ Admin Panel</div>
        <nav class="admin-nav">
            <a href="admin-dashboard.php" class="active"><i class="fa-solid fa-house-chimney"></i> Dashboard</a>
            <a href="admin.php"><i class="fa-solid fa-chart-line"></i> Overview</a>
            <a href="admin-products.php"><i class="fa-solid fa-car"></i> Products</a>
            <a href="admin-orders.php"><i class="fa-solid fa-box"></i> Orders</a>
            <a href="admin-users.php"><i class="fa-solid fa-users"></i> Users</a>
            <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Back to Site</a>
            <form method="POST" action="../controller/AuthController.php" style="padding:12px 20px;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" style="background:transparent;border:none;color:#94a3b8;cursor:pointer;font-size:14px;text-align:left;width:100%;">
                    <i class="fa-solid fa-right-from-bracket" style="width:20px;margin-right:8px;"></i> Logout
                </button>
            </form>
        </nav>
    </aside>

    <main class="admin-content">

        <div class="dash-welcome">
            <h1 class="admin-title">Welcome back, <span><?= htmlspecialchars($adminName) ?></span> 👋</h1>
            <p>What would you like to do today? Pick an action below to get started.</p>
        </div>

        <!-- Mini Stats -->
        <div class="mini-stats">
            <div class="mini-stat">
                <div class="mini-stat-icon">🚗</div>
                <div class="mini-stat-info">
                    <div class="num"><?= $totalProducts ?></div>
                    <div class="lbl">Products</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon">👥</div>
                <div class="mini-stat-info">
                    <div class="num"><?= $totalUsers ?></div>
                    <div class="lbl">Users</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon">📦</div>
                <div class="mini-stat-info">
                    <div class="num"><?= $totalOrders ?></div>
                    <div class="lbl">Orders</div>
                </div>
            </div>
        </div>

        <!-- 3 Action Cards -->
        <div class="action-cards-section">
            <h2><i class="fa-solid fa-bolt" style="margin-right:8px;color:#f59e0b;"></i>Quick Actions</h2>

            <div class="action-cards-grid">

                <!-- Card 1: Edit -->
                <div class="action-card edit-card">
                    <div class="card-icon"><i class="fa-solid fa-pen-to-square"></i></div>
                    <div class="card-body">
                        <span class="card-badge"><i class="fa-solid fa-circle" style="font-size:6px;"></i> Edit</span>
                        <h3>Edit Something</h3>
                        <p>Update existing products, modify order statuses, or edit user information.</p>
                    </div>
                    <ul class="card-features">
                        <li><i class="fa-solid fa-check"></i> Edit product name, price & stock</li>
                        <li><i class="fa-solid fa-check"></i> Update order status</li>
                        <li><i class="fa-solid fa-check"></i> Modify user details</li>
                    </ul>
                    <a href="admin-products.php" class="card-btn">
                        <i class="fa-solid fa-pen"></i> Go to Edit
                    </a>
                </div>

                <!-- Card 2: Add -->
                <div class="action-card add-card">
                    <div class="card-icon"><i class="fa-solid fa-circle-plus"></i></div>
                    <div class="card-body">
                        <span class="card-badge"><i class="fa-solid fa-circle" style="font-size:6px;"></i> Add</span>
                        <h3>Add Something</h3>
                        <p>List a new vehicle, add spare parts, or register new categories to the store.</p>
                    </div>
                    <ul class="card-features">
                        <li><i class="fa-solid fa-check"></i> Add new products & vehicles</li>
                        <li><i class="fa-solid fa-check"></i> Upload product images</li>
                        <li><i class="fa-solid fa-check"></i> Set pricing & stock levels</li>
                    </ul>
                    <a href="admin-products.php#add-form" class="card-btn">
                        <i class="fa-solid fa-plus"></i> Go to Add
                    </a>
                </div>

                <!-- Card 3: Delete -->
                <div class="action-card delete-card">
                    <div class="card-icon"><i class="fa-solid fa-trash-can"></i></div>
                    <div class="card-body">
                        <span class="card-badge"><i class="fa-solid fa-circle" style="font-size:6px;"></i> Remove</span>
                        <h3>Remove Something</h3>
                        <p>Review and decide what to keep or permanently remove from your platform.</p>
                    </div>
                    <ul class="card-features">
                        <li><i class="fa-solid fa-check"></i> Delete outdated products</li>
                        <li><i class="fa-solid fa-check"></i> Remove inactive users</li>
                        <li><i class="fa-solid fa-check"></i> Cancel old orders</li>
                    </ul>
                    <a href="admin-products.php" class="card-btn">
                        <i class="fa-solid fa-trash"></i> Go to Remove
                    </a>
                </div>

            </div>
        </div>

        <!-- Quick Nav -->
        <div class="table-wrapper">
            <div class="table-header"><h3>Quick Navigation</h3></div>
            <div class="quick-nav">
                <a href="admin.php" class="action-btn edit"><i class="fa-solid fa-chart-line"></i> &nbsp;Full Overview</a>
                <a href="admin-products.php" class="action-btn edit"><i class="fa-solid fa-car"></i> &nbsp;Manage Products</a>
                <a href="admin-orders.php" class="action-btn edit"><i class="fa-solid fa-box"></i> &nbsp;Manage Orders</a>
                <a href="admin-users.php" class="action-btn edit"><i class="fa-solid fa-users"></i> &nbsp;Manage Users</a>
            </div>
        </div>

    </main>
</div>

<script src="script.js"></script>
</body>
</html>