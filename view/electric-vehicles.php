<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/product.php";

$db = Database::getInstance()->getConnection();
$isLoggedIn = isset($_SESSION['user_id']);
$productModel = new Product($db);

$allElectric = $productModel->getByCategory('Electric');
$useStatic = empty($allElectric);

$staticVehicles = [
    ['product_id'=>0,'name'=>'Tesla Model S Plaid',      'brand'=>'Tesla',        'sub_type'=>'Sedan', 'price'=>74990,  'image'=>'images/Model S Plaid.jpg',      'description'=>'The quickest production car ever made. Tri-motor AWD with 1020 HP and 652km range.','average_rating'=>4.9,'review_count'=>0,'stock_count'=>6],
    ['product_id'=>0,'name'=>'Rivian R1T Adventure',     'brand'=>'Rivian',       'sub_type'=>'Truck', 'price'=>69900,  'image'=>'images/Rivian R1T1.jpg',         'description'=>'The ultimate electric adventure truck. Quad-motor performance meets off-road capability.','average_rating'=>4.8,'review_count'=>0,'stock_count'=>5],
    ['product_id'=>0,'name'=>'Porsche Taycan Turbo S',   'brand'=>'Porsche',      'sub_type'=>'Sport', 'price'=>185000, 'image'=>'images/porsche-taycan.jpeg',     'description'=>'German precision meets electric performance. The benchmark for EV sports cars.','average_rating'=>5.0,'review_count'=>0,'stock_count'=>3],
    ['product_id'=>0,'name'=>'BMW iX M60',               'brand'=>'BMW',          'sub_type'=>'SUV',   'price'=>110900, 'image'=>'images/BMW iX.jpg',              'description'=>'BMW flagship electric SUV with M Performance DNA and 566km range.','average_rating'=>4.8,'review_count'=>0,'stock_count'=>4],
    ['product_id'=>0,'name'=>'Mercedes EQS 580 4MATIC',  'brand'=>'Mercedes-Benz','sub_type'=>'Sedan', 'price'=>125900, 'image'=>'images/Mercedes EQS.jpg',        'description'=>'The electric S-Class. 770km range, Hyperscreen dashboard and unmatched comfort.','average_rating'=>4.9,'review_count'=>0,'stock_count'=>4],
    ['product_id'=>0,'name'=>'Audi Q8 e-tron Sportback', 'brand'=>'Audi',         'sub_type'=>'SUV',   'price'=>87500,  'image'=>'images/Audi Q8 e-tron.jpg',      'description'=>'Audi flagship electric SUV blending quattro AWD with efficient 600km range.','average_rating'=>4.7,'review_count'=>0,'stock_count'=>6],
    ['product_id'=>0,'name'=>'Lucid Air Grand Touring',  'brand'=>'Lucid Motors', 'sub_type'=>'Sedan', 'price'=>138000, 'image'=>'images/Lucid Air.jpg',           'description'=>'World record 832km range. The most energy-efficient EV ever produced.','average_rating'=>4.9,'review_count'=>0,'stock_count'=>3],
    ['product_id'=>0,'name'=>'Ford F-150 Lightning Pro', 'brand'=>'Ford',         'sub_type'=>'Truck', 'price'=>55974,  'image'=>'images/Ford F-150 Lightning.jpg','description'=>'Americas best-selling truck goes electric. 580HP, bidirectional charging.','average_rating'=>4.6,'review_count'=>0,'stock_count'=>8],
];

$vehicles = $useStatic ? $staticVehicles : $allElectric;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electric Vehicles - Automotive Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .page-header { background: linear-gradient(135deg, #0a0f1a 0%, #0d1f35 40%, #0a1520 100%); }
        .page-header h1 span { color: #00e5ff; }
        .filter-btn:hover, .filter-btn.active { background: #0891b2 !important; border-color: #0891b2 !important; }
        .price-tag { background: #0891b2; }
        .ev-stat-num { color: #00e5ff; font-size: 32px; font-weight: 800; }
        .ev-stats { background: linear-gradient(135deg, #061020, #0a1a30); border-top: 1px solid #1e3a5f; border-bottom: 1px solid #1e3a5f; }
        .car-card { background: linear-gradient(180deg, #0d1a2e 0%, #070e1a 100%); border-color: #1e3a5f; }
        .car-card:hover { border-color: #00b8d4; box-shadow: 0 20px 40px rgba(0,184,212,0.2); }
        .car-brand { color: #00b8d4; }
        .btn-details { border-color: #0891b2; }
        .btn-details:hover { background: #0891b2; }
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
        <li><a href="electric-vehicles.php" class="active">Electric</a></li>
        <li><a href="spare-parts.php">Spare Parts</a></li>
    </ul>
    <div class="nav-actions">
        <a href="cart.php" class="cart-icon-link"><i class="fa-solid fa-cart-shopping"></i></a>
        <?php if ($isLoggedIn): ?>
            <a href="wishlist.php" class="login-link">Wishlist</a>
            <a href="orders.php" class="login-link">My Orders</a>
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

<div class="page-header">
    <h1>Electric <span>Vehicles</span></h1>
    <p>Discover the future of mobility — zero emissions, maximum performance</p>
</div>

<div class="ev-stats">
    <div class="ev-stat"><div class="ev-stat-num">680+</div><div class="ev-stat-label">km range</div></div>
    <div class="ev-stat"><div class="ev-stat-num">2.4s</div><div class="ev-stat-label">0-100 km/h</div></div>
    <div class="ev-stat"><div class="ev-stat-num">800 HP</div><div class="ev-stat-label">peak power</div></div>
    <div class="ev-stat"><div class="ev-stat-num">25 min</div><div class="ev-stat-label">fast charge</div></div>
</div>

<div class="filter-bar">
    <span>Filter:</span>
    <button class="filter-btn active" onclick="filterVehicles('all', this)">All</button>
    <button class="filter-btn" onclick="filterVehicles('sedan', this)">Sedan</button>
    <button class="filter-btn" onclick="filterVehicles('suv', this)">SUV</button>
    <button class="filter-btn" onclick="filterVehicles('sport', this)">Sport</button>
    <button class="filter-btn" onclick="filterVehicles('truck', this)">Truck</button>
    <div class="search-filter">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search electric vehicles..." oninput="searchVehicles(this.value)">
    </div>
</div>

<section class="vehicles-section">
    <div class="section-info">
        <h2>Available Electric Vehicles</h2>
        <span id="vehicleCount"><?= count($vehicles) ?> vehicles</span>
    </div>
    <div class="cars-grid" id="vehiclesGrid">
        <?php foreach ($vehicles as $v): ?>
        <?php
            $subType = strtolower($v['sub_type'] ?? '');
            $badgeCls = in_array($subType, ['sport']) ? 'luxury' :
                        (in_array($subType, ['truck']) ? 'new' :
                        (in_array($subType, ['suv']) ? 'popular' : 'electric'));
        ?>
        <div class="car-card"
             data-type="<?= strtolower(htmlspecialchars($v['sub_type'] ?? '')) ?>"
             data-name="<?= strtolower(htmlspecialchars($v['name'])) ?>">
            <div class="car-image">
                <img src="<?= htmlspecialchars($v['image'] ?? '') ?>"
                     alt="<?= htmlspecialchars($v['name']) ?>"
                     onerror="this.style.display='none'">
                <span class="badge <?= $badgeCls ?>">⚡ <?= htmlspecialchars($v['sub_type'] ?? 'EV') ?></span>
                <span class="price-tag">$<?= number_format($v['price'], 0) ?></span>
            </div>
            <div class="car-info">
                <h3><?= htmlspecialchars($v['name']) ?></h3>
                <div class="car-brand"><?= htmlspecialchars($v['brand']) ?> • 2024</div>
                <div class="stars">
                    <?php for ($i=1; $i<=5; $i++) echo ($i <= round($v['average_rating'] ?? 0)) ? '★' : '☆'; ?>
                    <span>(<?= $v['average_rating'] ?? 0 ?>)</span>
                </div>
                <div class="card-actions">
                    <button class="btn-details"
                        onclick="showDetails(
                            '<?= addslashes(htmlspecialchars($v['name'])) ?>',
                            '$<?= number_format($v['price'], 0) ?>',
                            '<?= addslashes(htmlspecialchars($v['description'] ?? '')) ?>',
                            <?= (int)($v['product_id'] ?? 0) ?>
                        )">View Details</button>
                    <?php if ($isLoggedIn && !empty($v['product_id'])): ?>
                    <form method="POST" action="../Controller/CartController.php">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $v['product_id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="redirect_to" value="../View/electric-vehicles.php">
                        <button type="submit" class="btn-wishlist" title="Add to Cart"><i class="fa-solid fa-cart-plus"></i></button>
                    </form>
                    <?php else: ?>
                    <button class="btn-wishlist" onclick="requireLogin()"><i class="fa-solid fa-cart-plus"></i></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Modal -->
<div id="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:999; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
    <div style="background:linear-gradient(180deg,#0d1a2e,#07101f); border:1px solid #1e3a5f; border-radius:20px; padding:40px; max-width:460px; width:90%; position:relative; box-shadow:0 0 60px rgba(0,180,216,0.15);">
        <button onclick="closeModal()" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#64748b;font-size:20px;cursor:pointer;">✕</button>
        <div style="font-size:50px; text-align:center; margin-bottom:20px;">⚡</div>
        <h2 id="modalName" style="font-size:22px; font-weight:800; margin-bottom:6px;"></h2>
        <div id="modalPrice" style="color:#00b8d4; font-size:22px; font-weight:700; margin-bottom:18px;"></div>
        <p id="modalDesc" style="color:#64748b; line-height:1.7; margin-bottom:28px; font-size:14px;"></p>
        <div id="modalActions"></div>
    </div>
</div>

<footer><p>© 2026 Auto motive Hub Platform | All Rights Reserved</p></footer>

<script>
function filterVehicles(type, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    let count = 0;
    document.querySelectorAll('.car-card').forEach(card => {
        const match = type === 'all' || card.dataset.type === type;
        card.classList.toggle('hidden', !match);
        if (match) count++;
    });
    document.getElementById('vehicleCount').textContent = count + ' vehicles';
}

function searchVehicles(val) {
    val = val.toLowerCase();
    let count = 0;
    document.querySelectorAll('.car-card').forEach(card => {
        const match = !val || card.dataset.name.includes(val);
        card.classList.toggle('hidden', !match);
        if (match) count++;
    });
    document.getElementById('vehicleCount').textContent = count + ' vehicles';
}

function showDetails(name, price, desc, productId) {
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalPrice').textContent = price;
    document.getElementById('modalDesc').textContent = desc;
    let actionsHtml = '';
    <?php if ($isLoggedIn): ?>
    if (productId > 0) {
        actionsHtml = `<form method="POST" action="../Controller/CartController.php">
            <input type="hidden" name="action" value="add_to_cart">
            <input type="hidden" name="product_id" value="${productId}">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="redirect_to" value="../View/electric-vehicles.php">
            <button type="submit" style="width:100%;padding:13px;background:#0891b2;border:none;color:white;font-size:14px;font-weight:600;cursor:pointer;border-radius:8px;">Add to Cart</button></form>`;
    } else {
        actionsHtml = '<button onclick="closeModal()" style="width:100%;padding:13px;background:#0891b2;border:none;color:white;font-size:14px;font-weight:600;cursor:pointer;border-radius:8px;">Contact for Price</button>';
    }
    <?php else: ?>
    actionsHtml = '<a href="login.php" style="display:block;text-align:center;padding:13px;background:#0891b2;color:white;border-radius:8px;text-decoration:none;font-weight:600;">Login to Buy</a>';
    <?php endif; ?>
    document.getElementById('modalActions').innerHTML = actionsHtml;
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() { document.getElementById('modal').style.display = 'none'; }
document.getElementById('modal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
</script>
<script>
// Cart Toast
window.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('cart_added') === '1') {
        const toast = document.createElement('div');
        toast.innerHTML = '<i class="fa-solid fa-circle-check"></i> Item added to cart successfully!';
        toast.style.cssText = 'position:fixed;bottom:30px;right:30px;background:#16a34a;color:white;padding:14px 24px;border-radius:10px;font-size:14px;font-weight:600;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.4);display:flex;align-items:center;gap:10px;';
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.transition='0.4s'; toast.style.opacity='0'; setTimeout(()=>toast.remove(),400); }, 3000);
        // Clean URL
        const url = new URL(window.location);
        url.searchParams.delete('cart_added');
        window.history.replaceState({}, '', url);
    }
});
</script>

<script>
function showLoginToast() {
    const existing = document.getElementById('loginToast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id = 'loginToast';
    toast.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Please log in first to add items to your cart!';
    toast.style.cssText = 'position:fixed;bottom:30px;right:30px;background:#d97706;color:white;padding:14px 24px;border-radius:10px;font-size:14px;font-weight:600;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.4);display:flex;align-items:center;gap:10px;';
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.transition='0.4s'; toast.style.opacity='0'; setTimeout(()=>toast.remove(),400); }, 3000);
}
function requireLogin() {
    showLoginToast();
}
</script>
</body>
</html>