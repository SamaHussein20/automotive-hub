<?php

session_start();

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/wishlist.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $_SESSION['user_id'] ?? null;

$items = [];
if ($isLoggedIn) {
    $wishlist = new Wishlist($db, $userId);
    $items    = $wishlist->getItems();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - Automotive Hub</title>
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
        <?php if ($isLoggedIn): ?>
            <a href="orders.php" class="login-link">My Orders</a>
            <a href="wishlist.php" class="login-link">Wishlist</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "admin"): ?>
                <a href="admin.php" class="login-link">Admin</a>
            <?php endif; ?>
            <form method="POST" action="../controller/AuthController.php" style="display:inline;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="signup-btn">Logout</button>
            </form>
        <?php else: ?>
            <a href="login.php" class="login-link">Login</a>
            <a href="register.php" class="signup-btn">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<div class="page-header">
    <h1>My <span>Wishlist</span></h1>
    <p>Vehicles and parts you saved for later</p>
</div>

<?php if (!$isLoggedIn): ?>
    <div class="empty-state">
        <div class="empty-icon">❤️</div>
        <h3>Login to view your wishlist</h3>
        <p>Save your favorite vehicles and parts</p>
        <a href="login.php" class="btn-primary">Login</a>
    </div>

<?php elseif (empty($items)): ?>
    <div class="empty-state">
        <div class="empty-icon">❤️</div>
        <h3>Your wishlist is empty</h3>
        <p>Browse our collection and save your favorites</p>
        <a href="cars.php" class="btn-primary">Browse Products</a>
    </div>

<?php else: ?>
    <div class="wishlist-grid">
        <?php foreach ($items as $p): ?>
        <div class="car-card">
            <div class="car-image">
                <?php if (!empty($p['image'])): ?>
                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                <?php else: ?>
                    <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:60px;">🚗</div>
                <?php endif; ?>
                <span class="badge popular"><?= htmlspecialchars($p['category']) ?></span>
                <span class="price-tag">$<?= number_format($p['price'], 2) ?></span>
            </div>
            <div class="car-info">
                <h3><?= htmlspecialchars($p['name']) ?></h3>
                <div class="car-brand"><?= htmlspecialchars($p['brand']) ?></div>
                <div class="stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?= $i <= round($p['average_rating']) ? '★' : '☆' ?>
                    <?php endfor; ?>
                    <span>(<?= $p['review_count'] ?? 0 ?>)</span>
                </div>
                <div style="color:#64748b; font-size:13px; margin-bottom:15px;">
                    Stock: <?= $p['stock_count'] ?>
                </div>
                <div class="card-actions">
                    <?php if ($p['stock_count'] > 0): ?>
                        <form method="POST" action="../controller/CartController.php">
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="redirect_to" value="../view/wishlist.php">
                            <button type="submit" class="btn-details">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <button class="btn-details" disabled style="opacity:0.5;cursor:not-allowed;">Out of Stock</button>
                    <?php endif; ?>

                    <form method="POST" action="../controller/WishlistController.php">
                        <input type="hidden" name="action" value="remove_from_wishlist">
                        <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                        <button type="submit" class="btn-wishlist" title="Remove from wishlist">
                            <i class="fa-solid fa-heart" style="color:#ef4444;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<footer>
    <p>© 2026 Auto motive Hub Platform | All Rights Reserved</p>
</footer>

<script src="script.js"></script>
</body>
</html>
