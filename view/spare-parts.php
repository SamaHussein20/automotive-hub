<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/product.php";

$db = Database::getInstance()->getConnection();
$isLoggedIn = isset($_SESSION['user_id']);
$productModel = new Product($db);

$allParts = $productModel->getByCategory('Spare Parts');
$useStatic = empty($allParts);

$staticParts = [
    ['product_id'=>0,'name'=>'Brembo GT Brake Kit 6-Piston',        'brand'=>'Brembo',    'sub_type'=>'Brakes',     'price'=>1850,  'image'=>'images/GT Brake Kit 6-Piston.jpg','description'=>'World-class Italian braking performance. 380mm slotted disc with 6-piston monoblock caliper.','average_rating'=>4.9,'review_count'=>0,'stock_count'=>20,'badge'=>'premium'],
    ['product_id'=>0,'name'=>'K&N High-Flow Air Filter',             'brand'=>'K&N',       'sub_type'=>'Engine',     'price'=>89,    'image'=>'images/K&N Air Filter.jpg',       'description'=>'Drop-in replacement filter increasing airflow by up to 15%. Washable with million-mile warranty.','average_rating'=>4.8,'review_count'=>0,'stock_count'=>50,'badge'=>'popular'],
    ['product_id'=>0,'name'=>'Bilstein B6 Sport Shock Absorbers',    'brand'=>'Bilstein',  'sub_type'=>'Suspension', 'price'=>620,   'image'=>'images/Bilstein Shocks.jpg',      'description'=>'German-engineered monotube gas pressure shocks. OEM replacement with sport-tuned damping.','average_rating'=>4.9,'review_count'=>0,'stock_count'=>15,'badge'=>'oem'],
    ['product_id'=>0,'name'=>'Bosch Alternator 180A Remanufactured', 'brand'=>'Bosch',     'sub_type'=>'Electrical', 'price'=>340,   'image'=>'images/Bosch Alternator.jpg',     'description'=>'Professional-grade remanufactured alternator. OEM specifications with 100% testing.','average_rating'=>4.7,'review_count'=>0,'stock_count'=>25,'badge'=>'oem'],
    ['product_id'=>0,'name'=>'NGK Iridium IX Spark Plugs',           'brand'=>'NGK',       'sub_type'=>'Engine',     'price'=>75,    'image'=>'images/NGK Spark Plugs.jpg',      'description'=>'Ultra-fine 0.6mm iridium center electrode for improved ignitability. Lasts up to 100,000km.','average_rating'=>4.8,'review_count'=>0,'stock_count'=>60,'badge'=>'popular'],
    ['product_id'=>0,'name'=>'Enkei RPF1 Alloy Wheels 18"',          'brand'=>'Enkei',     'sub_type'=>'Body',       'price'=>1200,  'image'=>'images/Enkei Alloy Wheels.jpg',   'description'=>'Legendary lightweight forged alloy wheel. Flow-form construction for maximum strength.','average_rating'=>5.0,'review_count'=>0,'stock_count'=>12,'badge'=>'premium'],
    ['product_id'=>0,'name'=>'EBC Yellowstuff Street & Track Pads',  'brand'=>'EBC Brakes','sub_type'=>'Brakes',     'price'=>145,   'image'=>'images/EBC Brake Pads.jpg',       'description'=>'High-friction brake pads rated to 500°C. Low dust formula with chamfered and slotted design.','average_rating'=>4.6,'review_count'=>0,'stock_count'=>30,'badge'=>'sale'],
    ['product_id'=>0,'name'=>'Denso OEM Starter Motor 1.4kW',        'brand'=>'Denso',     'sub_type'=>'Electrical', 'price'=>210,   'image'=>'images/Denso Starter Motor.jpg',  'description'=>'Original equipment quality starter motor built to exact OEM specifications.','average_rating'=>4.8,'review_count'=>0,'stock_count'=>18,'badge'=>'oem'],
];

$parts = $useStatic ? $staticParts : $allParts;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spare Parts - Automotive Hub</title>
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
        <li><a href="spare-parts.php" class="active">Spare Parts</a></li>
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
    <h1>Spare <span>Parts</span></h1>
    <p>Genuine OEM & premium aftermarket parts for all makes and models</p>
</div>

<div class="parts-stats">
    <div class="parts-stat"><div class="parts-stat-num">10,000+</div><div class="parts-stat-label">Parts in Stock</div></div>
    <div class="parts-stat"><div class="parts-stat-num">500+</div><div class="parts-stat-label">Brands Available</div></div>
    <div class="parts-stat"><div class="parts-stat-num">24h</div><div class="parts-stat-label">Fast Delivery</div></div>
    <div class="parts-stat"><div class="parts-stat-num">2 Years</div><div class="parts-stat-label">Warranty</div></div>
</div>

<div class="filter-bar">
    <span>Filter:</span>
    <button class="filter-btn active" onclick="filterParts('all', this)">All</button>
    <button class="filter-btn" onclick="filterParts('engine', this)">Engine</button>
    <button class="filter-btn" onclick="filterParts('brakes', this)">Brakes</button>
    <button class="filter-btn" onclick="filterParts('suspension', this)">Suspension</button>
    <button class="filter-btn" onclick="filterParts('electrical', this)">Electrical</button>
    <button class="filter-btn" onclick="filterParts('body', this)">Body</button>
    <div class="search-filter">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search spare parts..." oninput="searchParts(this.value)">
    </div>
</div>

<section class="parts-section">
    <div class="section-info">
        <h2>Available Spare Parts</h2>
        <span id="partCount"><?= count($parts) ?> parts</span>
    </div>
    <div class="cars-grid" id="partsGrid">
        <?php foreach ($parts as $part): ?>
        <?php
            $subType = strtolower($part['sub_type'] ?? '');
            $badgeCls = $part['badge'] ?? ($subType === 'brakes' ? 'sale' : ($subType === 'engine' ? 'popular' : ($subType === 'body' ? 'premium' : 'oem')));
        ?>
        <div class="car-card"
             data-type="<?= strtolower(htmlspecialchars($part['sub_type'] ?? '')) ?>"
             data-name="<?= strtolower(htmlspecialchars($part['name'])) ?>">
            <div class="car-image">
                <img src="<?= htmlspecialchars($part['image'] ?? '') ?>"
                     alt="<?= htmlspecialchars($part['name']) ?>"
                     onerror="this.style.display='none'">
                <span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($part['sub_type'] ?? 'Part') ?></span>
                <span class="price-tag">$<?= number_format($part['price'], 0) ?></span>
            </div>
            <div class="car-info">
                <h3><?= htmlspecialchars($part['name']) ?></h3>
                <div class="car-brand"><?= htmlspecialchars($part['brand']) ?> • <?= htmlspecialchars($part['sub_type'] ?? '') ?></div>
                <div class="stars">
                    <?php for ($i=1; $i<=5; $i++) echo ($i <= round($part['average_rating'] ?? 0)) ? '★' : '☆'; ?>
                    <span>(<?= $part['average_rating'] ?? 0 ?>)</span>
                </div>
                <div class="card-actions">
                    <button class="btn-details"
                        onclick="showDetails(
                            '<?= addslashes(htmlspecialchars($part['name'])) ?>',
                            '$<?= number_format($part['price'], 0) ?>',
                            '<?= addslashes(htmlspecialchars($part['description'] ?? '')) ?>',
                            <?= (int)($part['product_id'] ?? 0) ?>
                        )">View Details</button>
                    <?php if ($isLoggedIn && !empty($part['product_id'])): ?>
                    <form method="POST" action="../Controller/CartController.php">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $part['product_id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="redirect_to" value="../View/spare-parts.php">
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
    <div style="background:linear-gradient(180deg,#1e293b,#0f172a); border:1px solid #334155; border-radius:20px; padding:40px; max-width:460px; width:90%; position:relative;">
        <button onclick="closeModal()" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#64748b;font-size:20px;cursor:pointer;">✕</button>
        <div style="font-size:50px; text-align:center; margin-bottom:20px;">🔧</div>
        <h2 id="modalName" style="font-size:22px; font-weight:800; margin-bottom:6px;"></h2>
        <div id="modalPrice" style="color:#60a5fa; font-size:22px; font-weight:700; margin-bottom:18px;"></div>
        <p id="modalDesc" style="color:#64748b; line-height:1.7; margin-bottom:28px; font-size:14px;"></p>
        <div id="modalActions"></div>
    </div>
</div>

<footer><p>© 2026 Auto motive Hub Platform | All Rights Reserved</p></footer>

<script>
function filterParts(type, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    let count = 0;
    document.querySelectorAll('.car-card').forEach(card => {
        const match = type === 'all' || card.dataset.type === type;
        card.classList.toggle('hidden', !match);
        if (match) count++;
    });
    document.getElementById('partCount').textContent = count + ' parts';
}

function searchParts(val) {
    val = val.toLowerCase();
    let count = 0;
    document.querySelectorAll('.car-card').forEach(card => {
        const match = !val || card.dataset.name.includes(val);
        card.classList.toggle('hidden', !match);
        if (match) count++;
    });
    document.getElementById('partCount').textContent = count + ' parts';
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
            <input type="hidden" name="redirect_to" value="../View/spare-parts.php">
            <button type="submit" style="width:100%;padding:13px;background:#2563eb;border:none;color:white;font-size:14px;font-weight:600;cursor:pointer;border-radius:8px;">Add to Cart</button></form>`;
    } else {
        actionsHtml = '<button onclick="closeModal()" style="width:100%;padding:13px;background:#2563eb;border:none;color:white;font-size:14px;font-weight:600;cursor:pointer;border-radius:8px;">Contact for Price</button>';
    }
    <?php else: ?>
    actionsHtml = '<a href="login.php" style="display:block;text-align:center;padding:13px;background:#2563eb;color:white;border-radius:8px;text-decoration:none;font-weight:600;">Login to Buy</a>';
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