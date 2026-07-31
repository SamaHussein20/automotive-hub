<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/product.php";

$db = Database::getInstance()->getConnection();
$isLoggedIn = isset($_SESSION['user_id']);
$productModel = new Product($db);

// Get motorcycles from DB
$allMotorcycles = $productModel->getByCategory('Motorcycles');
$useStatic = empty($allMotorcycles);

// Static fallback data
$staticBikes = [
    ['product_id'=>0,'name'=>'Yamaha R1',               'brand'=>'Yamaha',           'category'=>'Motorcycles','sub_type'=>'Sport',    'price'=>17500, 'image'=>'images/Yamaha R1.jpg',            'description'=>'Top supersport bike with cutting-edge electronics.','average_rating'=>4.9,'review_count'=>0,'stock_count'=>10],
    ['product_id'=>0,'name'=>'Harley-Davidson Sportster S','brand'=>'Harley-Davidson','category'=>'Motorcycles','sub_type'=>'Cruiser',  'price'=>14999, 'image'=>'images/Harley Sportster S.jpg',   'description'=>'Iconic American cruiser with modern liquid-cooled engine.','average_rating'=>4.8,'review_count'=>0,'stock_count'=>7],
    ['product_id'=>0,'name'=>'Zero SR/F',               'brand'=>'Zero Motorcycles', 'category'=>'Motorcycles','sub_type'=>'Electric', 'price'=>21000, 'image'=>'images/Zero SRF.jpg',             'description'=>'Premium electric motorcycle with 259km range per charge.','average_rating'=>4.7,'review_count'=>0,'stock_count'=>5],
    ['product_id'=>0,'name'=>'BMW R 1250 GS',           'brand'=>'BMW Motorrad',     'category'=>'Motorcycles','sub_type'=>'Adventure','price'=>18200, 'image'=>'images/BMW R 1250 GS.jpg',        'description'=>'The world best adventure motorcycle for any terrain.','average_rating'=>4.9,'review_count'=>0,'stock_count'=>8],
    ['product_id'=>0,'name'=>'Kawasaki Ninja ZX-10R',   'brand'=>'Kawasaki',         'category'=>'Motorcycles','sub_type'=>'Sport',    'price'=>16999, 'image'=>'images/Kawasaki Ninja ZX-10R.jpg','description'=>'Race-derived superbike delivering track performance on the road.','average_rating'=>4.8,'review_count'=>0,'stock_count'=>6],
    ['product_id'=>0,'name'=>'Honda Gold Wing Tour',    'brand'=>'Honda',            'category'=>'Motorcycles','sub_type'=>'Cruiser',  'price'=>28500, 'image'=>'images/Honda Gold Wing.jpg',      'description'=>'Ultimate touring motorcycle with premium comfort and tech.','average_rating'=>4.9,'review_count'=>0,'stock_count'=>4],
    ['product_id'=>0,'name'=>'Energica Ego',            'brand'=>'Energica',         'category'=>'Motorcycles','sub_type'=>'Electric', 'price'=>23000, 'image'=>'images/Energica Ego.jpg',         'description'=>'Italian-made electric superbike with 400km range.','average_rating'=>4.6,'review_count'=>0,'stock_count'=>3],
    ['product_id'=>0,'name'=>'Ducati Panigale V4',      'brand'=>'Ducati',           'category'=>'Motorcycles','sub_type'=>'Sport',    'price'=>28995, 'image'=>'images/Ducati Panigale V4.jpg',   'description'=>'Italian masterpiece — the most powerful road-legal superbike.','average_rating'=>5.0,'review_count'=>0,'stock_count'=>5],
];

$bikes = $useStatic ? $staticBikes : $allMotorcycles;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motorcycles - Automotive Hub</title>
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
        <li><a href="motorcycles.php" class="active">Motorcycles</a></li>
        <li><a href="electric-vehicles.php">Electric</a></li>
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
    <h1>All <span>Motorcycles</span></h1>
    <p>Browse our full collection of sport, cruiser, electric, and adventure bikes</p>
</div>

<div class="filter-bar">
    <span>Filter:</span>
    <button class="filter-btn active" onclick="filterBikes('all', this)">All</button>
    <button class="filter-btn" onclick="filterBikes('sport', this)">Sport</button>
    <button class="filter-btn" onclick="filterBikes('cruiser', this)">Cruiser</button>
    <button class="filter-btn" onclick="filterBikes('electric', this)">Electric</button>
    <button class="filter-btn" onclick="filterBikes('adventure', this)">Adventure</button>
    <div class="search-filter">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Search motorcycles..." oninput="searchBikes(this.value)">
    </div>
</div>

<section class="bikes-section">
    <div class="section-info">
        <h2>Available Motorcycles</h2>
        <span id="bikeCount"><?= count($bikes) ?> motorcycles</span>
    </div>
    <div class="cars-grid" id="bikesGrid">
        <?php foreach ($bikes as $bike): ?>
        <?php
            $subType = strtolower($bike['sub_type'] ?? $bike['category'] ?? '');
            $badgeCls = in_array($subType, ['sport']) ? 'sport' :
                        (in_array($subType, ['electric']) ? 'electric' :
                        (in_array($subType, ['adventure']) ? 'new' : 'popular'));
        ?>
        <div class="car-card"
             data-type="<?= strtolower(htmlspecialchars($bike['sub_type'] ?? '')) ?>"
             data-name="<?= strtolower(htmlspecialchars($bike['name'])) ?>">
            <div class="car-image">
                <img src="<?= htmlspecialchars($bike['image'] ?? '') ?>"
                     alt="<?= htmlspecialchars($bike['name']) ?>"
                     onerror="this.style.display='none'">
                <span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($bike['sub_type'] ?? 'Bike') ?></span>
                <span class="price-tag">$<?= number_format($bike['price'], 0) ?></span>
            </div>
            <div class="car-info">
                <h3><?= htmlspecialchars($bike['name']) ?></h3>
                <div class="car-brand"><?= htmlspecialchars($bike['brand']) ?> • 2024</div>
                <div class="stars">
                    <?php for ($i=1; $i<=5; $i++) echo ($i <= round($bike['average_rating'] ?? 0)) ? '★' : '☆'; ?>
                    <span>(<?= $bike['average_rating'] ?? 0 ?>)</span>
                </div>
                <div class="card-actions">
                    <button class="btn-details"
                        onclick="showDetails(
                            '<?= addslashes(htmlspecialchars($bike['name'])) ?>',
                            '$<?= number_format($bike['price'], 0) ?>',
                            '<?= addslashes(htmlspecialchars($bike['description'] ?? '')) ?>',
                            <?= (int)($bike['product_id'] ?? 0) ?>
                        )">View Details</button>
                    <?php if ($isLoggedIn && !empty($bike['product_id'])): ?>
                    <form method="POST" action="../Controller/CartController.php">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $bike['product_id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="redirect_to" value="../View/motorcycles.php">
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
        <div style="font-size:50px; text-align:center; margin-bottom:20px;">🏍️</div>
        <h2 id="modalName" style="font-size:22px; font-weight:800; margin-bottom:6px;"></h2>
        <div id="modalPrice" style="color:#60a5fa; font-size:22px; font-weight:700; margin-bottom:18px;"></div>
        <p id="modalDesc" style="color:#64748b; line-height:1.7; margin-bottom:28px; font-size:14px;"></p>
        <div id="modalActions"></div>
    </div>
</div>

<footer><p>© 2026 Auto motive Hub Platform | All Rights Reserved</p></footer>

<script>
function filterBikes(type, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    let count = 0;
    document.querySelectorAll('.car-card').forEach(card => {
        const match = type === 'all' || card.dataset.type === type;
        card.classList.toggle('hidden', !match);
        if (match) count++;
    });
    document.getElementById('bikeCount').textContent = count + ' motorcycles';
}

function searchBikes(val) {
    val = val.toLowerCase();
    let count = 0;
    document.querySelectorAll('.car-card').forEach(card => {
        const match = !val || card.dataset.name.includes(val);
        card.classList.toggle('hidden', !match);
        if (match) count++;
    });
    document.getElementById('bikeCount').textContent = count + ' motorcycles';
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
            <input type="hidden" name="redirect_to" value="../View/motorcycles.php">
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