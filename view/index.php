<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/product.php";

$db = Database::getInstance()->getConnection();

$productModel    = new Product($db);
$allProducts     = $productModel->getAll();
$featuredProducts = array_merge(
    array_slice($allProducts, 2, 1), 
    array_slice($allProducts, 0, 0), 
    array_slice($allProducts, 3, 2),
    array_slice($allProducts, 6, 1)  
);

$isLoggedIn = isset($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto motive Hub Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .hero-content h1 span { color: #2563eb; }
        .discover-btn {
            margin-top: 20px;
            padding: 10px 22px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 200pxpx;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.25s, transform 0.25s;
            letter-spacing: 0.3px;
            display: inline-block;
        }
        .discover-btn:hover {
            background: #000006;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav>
        <div class="logo-section">
            <div class="logo-icon">A</div>
            <div class="logo-text">Auto motive <span>Hub Platform</span></div>
        </div>

        <ul class="nav-links">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="cars.php">Cars</a></li>
            <li><a href="motorcycles.php">Motorcycles</a></li>
            <li><a href="electric-vehicles.php">Electric</a></li>
            <li><a href="spare-parts.php">Spare Parts</a></li>
        </ul>

        <div class="nav-actions">
            <a href="cart.php" class="cart-icon-link">
                <span class="search-icon"><i class="fa-solid fa-cart-shopping"></i></span>
            </a>

            <?php if ($isLoggedIn): ?>
                <a href="orders.php" class="login-link">My Orders</a>
                <a href="wishlist.php" class="login-link">Wishlist</a>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "admin"): ?>
                    <a href="admin.php" class="login-link">Admin</a>
                <?php endif; ?>

                <form method="POST" action="../Controller/AuthController.php" style="display:inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="signup-btn">Logout</button>
                </form>
            <?php else: ?>
                <a href="login.php" class="login-link">Login</a>
                <a href="register.php" class="signup-btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <img class="hero-video" src="images/car.png" alt="Luxury Car">
        <div class="overlay"></div>
        <div class="hero-content">
            <h1>High-end Electric <br><span>Hub Platform</span></h1>
            <p>High-end creative interior with stunning engine vehicles and luxury connection.</p>
            <button onclick="window.location.href='recommendation.php'" style="margin-top:20px; padding:10px 22px; background:#2563eb; color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; letter-spacing:0.3px; width:auto; display:inline-block; max-width:220px;">
                Find Your Perfect Vehicle
            </button>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories">
        <div class="cards-container">
            <div class="card" onclick="window.location.href='cars.php'" style="cursor:pointer;">
                <div class="img-box"><img src="images/car1.jpg" alt="Cars"></div>
                <h3>Cars</h3>
            </div>
            <div class="card" onclick="window.location.href='motorcycles.php'" style="cursor:pointer;">
                <div class="img-box"><img src="images/bike.jpg" alt="Motorcycles"></div>
                <h3>Motorcycles</h3>
            </div>
            <div class="card" onclick="window.location.href='electric-vehicles.php'" style="cursor:pointer;">
                <div class="img-box"><img src="images/electric.png" alt="Electric Vehicles"></div>
                <h3>Electric Vehicles</h3>
            </div>
            <div class="card" onclick="window.location.href='spare-parts.php'" style="cursor:pointer;">
                <div class="img-box"><img src="images/parts.png" alt="Spare Parts"></div>
                <h3>Spare Parts</h3>
            </div>
        </div>
    </section>

    <!-- Featured Products from DB -->
    <?php if (!empty($featuredProducts)): ?>
    <section style="padding: 60px 8%;">
        <h2 style="font-size:28px; font-weight:800; margin-bottom:30px; color:#fff;">
            Featured <span style="color:#2563eb;">Products</span>
        </h2>
        <div class="cars-grid">
            <?php foreach ($featuredProducts as $p): ?>
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
                        <span>(<?= $p['review_count'] ?>)</span>
                    </div>
                    <div class="card-actions">
                        <?php if ($isLoggedIn): ?>
                            <form method="POST" action="../Controller/CartController.php">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="redirect_to" value="../View/index.php">
                                <button type="submit" class="btn-details">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn-details" style="text-align:center;text-decoration:none;display:block;padding:10px;">Login to Buy</a>
                        <?php endif; ?>
                        <form method="POST" action="../Controller/WishlistController.php">
                            <input type="hidden" name="action" value="add_to_wishlist">
                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                            <button type="submit" class="btn-wishlist"><i class="fa-regular fa-heart"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Features Section -->
    <section class="features-luxury">
        <div class="feature-card" onclick="window.location.href='quality.php'" style="cursor:pointer;">
            <div class="content">
                <span class="number">01</span>
                <h3>Premium Quality</h3>
                <p>Crafted for those who demand excellence in every detail.</p>
            </div>
        </div>
        <div class="feature-card" onclick="window.location.href='fast-delivery.php'" style="cursor:pointer;">
            <div class="content">
                <span class="number">02</span>
                <h3>Fast Delivery</h3>
                <p>Global logistics handled with precision and speed.</p>
            </div>
        </div>
        <div class="feature-card" onclick="window.location.href='best-prices.php'" style="cursor:pointer;">
            <div class="content">
                <span class="number">03</span>
                <h3>Best Prices</h3>
                <p>Unmatched value for the pinnacle of automotive luxury.</p>
            </div>
        </div>
    </section>

    <footer>
        <p>© 2026 Auto motive Hub Platform | All Rights Reserved</p>
    </footer>

</body>
</html>