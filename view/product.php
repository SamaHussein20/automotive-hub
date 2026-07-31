<?php

session_start();

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/product.php";
require_once __DIR__ . "/../model/review.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $_SESSION['user_id'] ?? null;

$productId    = (int)($_GET['id'] ?? 0);
$productModel = new Product($db);
$p            = $productModel->getById($productId);

if (!$p) {
    header("Location: cars.php");
    exit;
}

$reviewModel = new Review($db);
$reviews     = $reviewModel->getByProduct($productId);
$hasReviewed = $isLoggedIn ? $reviewModel->hasReviewed($productId, $userId) : false;

$reviewMsg = $_SESSION['review_msg'] ?? '';
unset($_SESSION['review_msg']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($p['name']) ?> - Automotive Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .product-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; padding: 60px 8%; align-items: start; }
        @media (max-width: 768px) { .product-layout { grid-template-columns: 1fr; } }
        .product-img { width: 100%; border-radius: 20px; object-fit: cover; max-height: 400px; border: 1px solid #334155; }
        .product-img-placeholder { width: 100%; height: 400px; background: #1e293b; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 100px; border: 1px solid #334155; }
        .product-title { font-size: 32px; font-weight: 800; margin-bottom: 8px; }
        .product-brand { color: #60a5fa; font-size: 15px; font-weight: 600; margin-bottom: 15px; }
        .product-price { font-size: 36px; font-weight: 800; color: #2563eb; margin-bottom: 20px; }
        .product-desc { color: #94a3b8; line-height: 1.8; font-size: 15px; margin-bottom: 25px; }
        .product-stock { font-size: 13px; color: #64748b; margin-bottom: 25px; }
        .product-stock.in-stock { color: #4ade80; }
        .product-stock.out-stock { color: #f87171; }
        .add-cart-form { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .qty-select { padding: 10px 14px; background: #1e293b; border: 1px solid #334155; color: white; border-radius: 8px; font-size: 14px; width: 80px; }
        .reviews-section { padding: 0 8% 60px; }
        .review-card { background: linear-gradient(180deg, #1e293b, #0f172a); border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 15px; }
        .review-author { font-weight: 700; font-size: 14px; margin-bottom: 6px; }
        .review-rating { color: #f1c40f; margin-bottom: 8px; font-size: 13px; }
        .review-comment { color: #94a3b8; font-size: 14px; line-height: 1.6; }
        .add-review-box { background: linear-gradient(180deg, #1e293b, #0f172a); border: 1px solid #334155; border-radius: 16px; padding: 25px; margin-bottom: 30px; }
    </style>
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

<!-- Product Detail -->
<div class="product-layout">
    <div>
        <?php if (!empty($p['image'])): ?>
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-img">
        <?php else: ?>
            <div class="product-img-placeholder">🚗</div>
        <?php endif; ?>
    </div>

    <div>
        <div class="product-brand"><?= htmlspecialchars($p['brand']) ?> · <?= htmlspecialchars($p['category']) ?></div>
        <h1 class="product-title"><?= htmlspecialchars($p['name']) ?></h1>

        <div class="stars" style="font-size:16px; margin-bottom:10px;">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <?= $i <= round($p['average_rating']) ? '★' : '☆' ?>
            <?php endfor; ?>
            <span style="color:#64748b; font-size:14px; margin-left:8px;">(<?= $p['review_count'] ?> reviews)</span>
        </div>

        <div class="product-price">$<?= number_format($p['price'], 2) ?></div>

        <p class="product-desc"><?= nl2br(htmlspecialchars($p['description'] ?? '')) ?></p>

        <?php if ($p['stock_count'] > 0): ?>
            <p class="product-stock in-stock"><i class="fa-solid fa-circle-check"></i> In Stock (<?= $p['stock_count'] ?> available)</p>
        <?php else: ?>
            <p class="product-stock out-stock"><i class="fa-solid fa-circle-xmark"></i> Out of Stock</p>
        <?php endif; ?>

        <?php if ($isLoggedIn && $p['stock_count'] > 0): ?>
            <div class="add-cart-form">
                <form method="POST" action="../controller/CartController.php" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                    <input type="hidden" name="redirect_to" value="../view/cart.php">
                    <select name="quantity" class="qty-select">
                        <?php for ($q = 1; $q <= min(10, $p['stock_count']); $q++): ?>
                            <option value="<?= $q ?>"><?= $q ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-cart-plus"></i> Add to Cart
                    </button>
                </form>

                <form method="POST" action="../controller/WishlistController.php">
                    <input type="hidden" name="action" value="add_to_wishlist">
                    <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                    <input type="hidden" name="redirect_to" value="../view/product.php?id=<?= $p['product_id'] ?>">
                    <button type="submit" class="btn-primary" style="background:transparent; border:1px solid #334155; color:#94a3b8;">
                        <i class="fa-regular fa-heart"></i> Wishlist
                    </button>
                </form>
            </div>
        <?php elseif (!$isLoggedIn): ?>
            <a href="login.php" class="btn-primary">Login to Buy</a>
        <?php else: ?>
            <button class="btn-primary" disabled style="opacity:0.5; cursor:not-allowed;">Out of Stock</button>
        <?php endif; ?>
    </div>
</div>

<!-- Reviews -->
<div class="reviews-section">
    <h2 style="font-size:24px; font-weight:800; margin-bottom:25px;">
        Customer <span style="color:#60a5fa;">Reviews</span>
        <span style="color:#64748b; font-size:16px; font-weight:400;">(<?= count($reviews) ?>)</span>
    </h2>

    <?php if (!empty($reviewMsg)): ?>
        <div class="alert alert-success" style="margin-bottom:20px;"><?= htmlspecialchars($reviewMsg) ?></div>
    <?php endif; ?>

    <!-- Add Review -->
    <?php if ($isLoggedIn && !$hasReviewed): ?>
    <div class="add-review-box">
        <h3 style="font-size:16px; font-weight:700; margin-bottom:15px;">Write a Review</h3>
        <form method="POST" action="../controller/ReviewController.php">
            <input type="hidden" name="action" value="add_review">
            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
            <div class="form-group">
                <label>Rating</label>
                <select name="rating" class="qty-select" style="width:100%;">
                    <option value="5">⭐⭐⭐⭐⭐ (5 - Excellent)</option>
                    <option value="4">⭐⭐⭐⭐ (4 - Good)</option>
                    <option value="3">⭐⭐⭐ (3 - Average)</option>
                    <option value="2">⭐⭐ (2 - Poor)</option>
                    <option value="1">⭐ (1 - Terrible)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Comment</label>
                <textarea name="comment" placeholder="Share your experience with this product..." style="width:100%; padding:12px; background:#0f172a; border:1px solid #334155; border-radius:8px; color:white; font-size:14px; min-height:80px; resize:vertical;"></textarea>
            </div>
            <button type="submit" class="btn-primary">Submit Review</button>
        </form>
    </div>
    <?php elseif ($isLoggedIn && $hasReviewed): ?>
        <div class="alert alert-info" style="margin-bottom:20px;">You have already reviewed this product.</div>
    <?php else: ?>
        <div class="alert alert-info" style="margin-bottom:20px;">
            <a href="login.php" style="color:#60a5fa;">Login</a> to write a review.
        </div>
    <?php endif; ?>

    <!-- Reviews List -->
    <?php if (empty($reviews)): ?>
        <div class="empty-state" style="padding:40px 0;">
            <div class="empty-icon" style="font-size:40px;">💬</div>
            <h3>No reviews yet</h3>
            <p>Be the first to review this product</p>
        </div>
    <?php else: ?>
        <?php foreach ($reviews as $r): ?>
        <div class="review-card">
            <div class="review-author"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></div>
            <div class="review-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?= $i <= $r['rating'] ? '★' : '☆' ?>
                <?php endfor; ?>
            </div>
            <?php if (!empty($r['comment'])): ?>
                <div class="review-comment"><?= htmlspecialchars($r['comment']) ?></div>
            <?php endif; ?>
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
