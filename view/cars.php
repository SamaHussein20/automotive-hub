<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/product.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

$isLoggedIn = isset($_SESSION['user_id']);

$productModel = new Product($db);

// Fetch all Cars by main category (category = 'Cars', sub_type = Sport/Luxury/SUV/Sedan)
$allCars = [];
$found = $productModel->getByMainCategory('Cars');
foreach ($found as $p) {
    $allCars[$p['product_id']] = $p;
}

// If no DB products yet, fall back to static data
$useStatic = empty($allCars);

$staticCars = [
    ['product_id'=>0, 'name'=>'Lamborghini Huracán EVO',    'brand'=>'Lamborghini', 'category'=>'Sport',  'price'=>248000, 'image'=>'images/Lamborghini Huracan.jpg',   'description'=>'Naturally aspirated V10 supercar delivering a raw, thrilling driving experience.',          'average_rating'=>5, 'review_count'=>0, 'stock_count'=>3,  'badge'=>'Sport',   'badge_cls'=>'sport'],
    ['product_id'=>0, 'name'=>'Ferrari F8 Tributo',          'brand'=>'Ferrari',    'category'=>'Sport',  'price'=>276550, 'image'=>'images/Ferrari F8 Tributo.jpg',      'description'=>'A tribute to the most powerful V8 in Ferrari history — pure Maranello passion.',             'average_rating'=>5, 'review_count'=>0, 'stock_count'=>2,  'badge'=>'Sport',   'badge_cls'=>'sport'],
    ['product_id'=>0, 'name'=>'Porsche 911 GT3',             'brand'=>'Porsche',    'category'=>'Sport',  'price'=>169700, 'image'=>'images/Porsche 911 GT3.jpg',         'description'=>'Pure racing DNA on the road — naturally aspirated flat-six revving to 9,000 rpm.',           'average_rating'=>5, 'review_count'=>0, 'stock_count'=>4,  'badge'=>'Sport',   'badge_cls'=>'sport'],
    ['product_id'=>0, 'name'=>'McLaren 720S',                'brand'=>'McLaren',    'category'=>'Sport',  'price'=>299000, 'image'=>'images/McLaren 720S.jpg',            'description'=>'Carbon-fiber supercar with active aerodynamics and mind-bending 0-100 in 2.9 seconds.',     'average_rating'=>5, 'review_count'=>0, 'stock_count'=>2,  'badge'=>'Limited', 'badge_cls'=>'limited'],
    ['product_id'=>0, 'name'=>'Rolls-Royce Ghost',           'brand'=>'Rolls-Royce','category'=>'Luxury', 'price'=>332500, 'image'=>'images/Rolls-Royce Ghost.jpg',       'description'=>'The pinnacle of automotive luxury — hand-crafted perfection with a twin-turbo V12.',        'average_rating'=>5, 'review_count'=>0, 'stock_count'=>1,  'badge'=>'Luxury',  'badge_cls'=>'luxury'],
    ['product_id'=>0, 'name'=>'Mercedes-Benz S 580 4MATIC',  'brand'=>'Mercedes',   'category'=>'Luxury', 'price'=>114500, 'image'=>'images/Mercedes S-Class.jpg',        'description'=>'The most technologically advanced luxury sedan with MBUX Hyperscreen.',                      'average_rating'=>4, 'review_count'=>0, 'stock_count'=>5,  'badge'=>'Luxury',  'badge_cls'=>'luxury'],
    ['product_id'=>0, 'name'=>'Bentley Continental GT',      'brand'=>'Bentley',    'category'=>'Luxury', 'price'=>224900, 'image'=>'images/Bentley Continental GT.jpg',  'description'=>'Grand touring excellence — hand-stitched leather, W12 power, and effortless refinement.',  'average_rating'=>5, 'review_count'=>0, 'stock_count'=>3,  'badge'=>'Luxury',  'badge_cls'=>'luxury'],
    ['product_id'=>0, 'name'=>'BMW X5 xDrive40i',            'brand'=>'BMW',        'category'=>'SUV',    'price'=>67300,  'image'=>'images/BMW X5.jpg',                  'description'=>'The benchmark luxury SUV combining performance, tech, and practicality.',                     'average_rating'=>4, 'review_count'=>0, 'stock_count'=>8,  'badge'=>'Popular', 'badge_cls'=>'popular'],
    ['product_id'=>0, 'name'=>'Range Rover Sport',           'brand'=>'Range Rover','category'=>'SUV',    'price'=>89900,  'image'=>'images/Range Rover Sport.jpg',        'description'=>'Combines luxury, performance, and legendary off-road capability in one stunning package.', 'average_rating'=>4, 'review_count'=>0, 'stock_count'=>6,  'badge'=>'Popular', 'badge_cls'=>'popular'],
    ['product_id'=>0, 'name'=>'Porsche Cayenne Turbo',       'brand'=>'Porsche',    'category'=>'SUV',    'price'=>131050, 'image'=>'images/Porsche Cayenne Turbo.jpg',   'description'=>'The fastest Cayenne ever — sports car performance in an SUV body.',                          'average_rating'=>5, 'review_count'=>0, 'stock_count'=>4,  'badge'=>'New',     'badge_cls'=>'new'],
    ['product_id'=>0, 'name'=>'Toyota Camry',                'brand'=>'Toyota',     'category'=>'Sedan',  'price'=>28400,  'image'=>'images/Toyota Camry.jpg',             'description'=>'The reliable, refined sedan trusted by millions worldwide with sleek modern styling.',       'average_rating'=>4, 'review_count'=>0, 'stock_count'=>15, 'badge'=>'New',     'badge_cls'=>'new'],
    ['product_id'=>0, 'name'=>'BMW 7 Series',                'brand'=>'BMW',        'category'=>'Sedan',  'price'=>93300,  'image'=>'images/BMW 7 Series.jpg',             'description'=>'BMW flagship executive sedan with cutting-edge technology and supreme comfort.',             'average_rating'=>5, 'review_count'=>0, 'stock_count'=>5,  'badge'=>'Popular', 'badge_cls'=>'popular'],
];

$cars = $useStatic ? $staticCars : array_values($allCars);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cars - Automotive Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { background: #05070a; color: white; }
        nav { display: flex; justify-content: space-between; align-items: center; padding: 15px 8%; position: sticky; top: 0; width: 100%; z-index: 100; border-bottom: 1px solid rgba(255,255,255,0.1); background: rgba(5,7,10,0.95); backdrop-filter: blur(10px); }
        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-icon { font-size: 26px; font-weight: 800; color: #2563eb; }
        .logo-text { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .logo-text span { display: block; font-size: 12px; color: #64748b; font-weight: normal; }
        .nav-links { display: flex; gap: 35px; list-style: none; }
        .nav-links a { text-decoration: none; color: #94a3b8; font-size: 14px; transition: 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: #fff; }
        .nav-actions { display: flex; align-items: center; gap: 25px; }
        .login-link { text-decoration: none; color: #94a3b8; font-size: 14px; font-weight: 600; }
        .signup-btn { text-decoration: none; background: #2563eb; color: white; padding: 10px 25px; border-radius: 6px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; }

        .page-header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); padding: 60px 8% 40px; border-bottom: 1px solid #1e293b; }
        .page-header h1 { font-size: 42px; font-weight: 800; margin-bottom: 10px; }
        .page-header h1 span { color: #60a5fa; }
        .page-header p { color: #64748b; font-size: 16px; }

        .filter-bar { display: flex; align-items: center; gap: 15px; padding: 25px 8%; background: #0a0d12; border-bottom: 1px solid #1e293b; flex-wrap: wrap; }
        .filter-bar span { color: #64748b; font-size: 14px; font-weight: 600; }
        .filter-btn { background: transparent; border: 1px solid #334155; color: #94a3b8; padding: 8px 20px; border-radius: 20px; cursor: pointer; font-size: 13px; transition: 0.3s; }
        .filter-btn:hover, .filter-btn.active { background: #2563eb; border-color: #2563eb; color: white; }
        .search-filter { margin-left: auto; display: flex; align-items: center; background: #1e293b; border: 1px solid #334155; border-radius: 25px; padding: 8px 16px; gap: 10px; }
        .search-filter input { background: transparent; border: none; outline: none; color: white; font-size: 14px; width: 180px; }
        .search-filter input::placeholder { color: #475569; }

        .cars-section { padding: 50px 8%; }
        .section-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
        .section-info h2 { font-size: 22px; font-weight: 700; }
        .section-info span { color: #64748b; font-size: 14px; }
        .cars-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }

        .car-card { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); border: 1px solid #334155; border-radius: 20px; overflow: hidden; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); cursor: pointer; }
        .car-card:hover { transform: translateY(-12px); border-color: #3b82f6; box-shadow: 0 20px 40px rgba(37,99,235,0.2); }
        .car-image { position: relative; height: 200px; overflow: hidden; background: #0f172a; }
        .car-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .car-card:hover .car-image img { transform: scale(1.08); }

        .badge { position: absolute; top: 15px; left: 15px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge.new { background: #16a34a; color: white; }
        .badge.popular { background: #d97706; color: white; }
        .badge.luxury { background: #7c3aed; color: white; }
        .badge.sport { background: #dc2626; color: white; }
        .badge.limited { background: #0369a1; color: white; }

        .price-tag { position: absolute; bottom: 15px; right: 15px; background: #2563eb; color: white; padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 15px; }

        .car-info { padding: 20px; }
        .car-info h3 { font-size: 18px; font-weight: 700; margin-bottom: 5px; }
        .car-brand { color: #60a5fa; font-size: 13px; margin-bottom: 12px; font-weight: 600; }
        .stars { color: #f1c40f; font-size: 13px; margin-bottom: 14px; }
        .stars span { color: #64748b; margin-left: 5px; }
        .card-actions { display: flex; gap: 10px; }
        .btn-details { flex: 1; padding: 10px; background: transparent; border: 1px solid #2563eb; color: white; cursor: pointer; border-radius: 8px; font-size: 13px; transition: 0.3s; }
        .btn-details:hover { background: #2563eb; }
        .btn-wishlist { padding: 10px 14px; background: transparent; border: 1px solid #334155; color: #64748b; cursor: pointer; border-radius: 8px; font-size: 13px; transition: 0.3s; }
        .btn-wishlist:hover { border-color: #ef4444; color: #ef4444; }

        footer { text-align: center; padding: 40px; border-top: 1px solid #1e293b; color: #475569; margin-top: 20px; }
        .car-card.hidden { display: none; }

        #modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 999; align-items: center; justify-content: center; backdrop-filter: blur(8px); }
        .modal-box { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); border: 1px solid #334155; border-radius: 20px; padding: 40px; max-width: 460px; width: 90%; position: relative; }
        .modal-close { position: absolute; top: 16px; right: 20px; background: none; border: none; color: #64748b; font-size: 20px; cursor: pointer; }
        .modal-close:hover { color: #fff; }
        #modalName { font-size: 24px; font-weight: 800; margin-bottom: 6px; }
        #modalPrice { color: #60a5fa; font-size: 22px; font-weight: 700; margin-bottom: 18px; }
        #modalDesc { color: #64748b; line-height: 1.7; margin-bottom: 28px; font-size: 14px; }
        .modal-btn { width: 100%; padding: 13px; background: #2563eb; border: none; color: white; font-size: 14px; font-weight: 600; cursor: pointer; border-radius: 8px; transition: 0.3s; }
        .modal-btn:hover { background: #1d4ed8; }
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
        <li><a href="cars.php" class="active">Cars</a></li>
        <li><a href="motorcycles.php">Motorcycles</a></li>
        <li><a href="electric-vehicles.php">Electric</a></li>
        <li><a href="spare-parts.php">Spare Parts</a></li>
    </ul>
    <div class="nav-actions">
        <a href="cart.php" class="login-link" <?php if (!$isLoggedIn): ?>onclick="requireLogin(); return false;"<?php endif; ?>><i class="fa-solid fa-cart-shopping"></i></a>
        <?php if ($isLoggedIn): ?>
            <a href="wishlist.php" class="login-link">Wishlist</a>
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
    <h1>Premium <span>Cars</span></h1>
    <p>Browse our exclusive collection of luxury and performance vehicles</p>
</div>

<div class="filter-bar">
    <span>Filter:</span>
    <button class="filter-btn active" onclick="filterCars('all', this)">All</button>
    <button class="filter-btn" onclick="filterCars('sport', this)">Sport</button>
    <button class="filter-btn" onclick="filterCars('luxury', this)">Luxury</button>
    <button class="filter-btn" onclick="filterCars('suv', this)">SUV</button>
    <button class="filter-btn" onclick="filterCars('sedan', this)">Sedan</button>
    <div class="search-filter">
        <i class="fa-solid fa-magnifying-glass" style="color:#475569;"></i>
        <input type="text" id="searchInput" placeholder="Search cars..." oninput="searchCars(this.value)">
    </div>
</div>

<section class="cars-section">
    <div class="section-info">
        <h2>Available Cars</h2>
        <span id="carCount"><?= count($cars) ?> vehicles</span>
    </div>
    <div class="cars-grid" id="carsGrid">
        <?php foreach ($cars as $car): ?>
        <?php
            // Use sub_type for badge class (DB data), fallback to category (static data)
            $subType  = strtolower($car['sub_type'] ?? $car['category'] ?? '');
            if ($subType == 'sport')  $badgeCls = 'sport';
            elseif ($subType == 'luxury') $badgeCls = 'luxury';
            elseif ($subType == 'suv')    $badgeCls = 'popular';
            elseif ($subType == 'sedan')  $badgeCls = 'new';
            else $badgeCls = isset($car['badge_cls']) ? $car['badge_cls'] : 'popular';
        ?>
        <div class="car-card"
             data-type="<?= strtolower(htmlspecialchars($car['sub_type'] ?? $car['category'] ?? '')) ?>"
             data-name="<?= strtolower(htmlspecialchars($car['name'])) ?>">
            <div class="car-image">
                <img src="<?= htmlspecialchars($car['image'] ?? '') ?>"
                     alt="<?= htmlspecialchars($car['name']) ?>"
                     onerror="this.style.display='none'">
                <span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($car['sub_type'] ?? $car['category'] ?? 'Car') ?></span>
                <span class="price-tag">$<?= number_format($car['price'], 0) ?></span>
            </div>
            <div class="car-info">
                <h3><?= htmlspecialchars($car['name']) ?></h3>
                <div class="car-brand"><?= htmlspecialchars($car['brand']) ?></div>
                <div class="stars">
                    <?php for ($i=1; $i<=5; $i++) echo ($i <= round($car['average_rating']??0)) ? '★' : '☆'; ?>
                    <span>(<?= $car['review_count']??0 ?>)</span>
                </div>
                <div class="card-actions">
                    <button class="btn-details"
                        onclick="showDetails(
                            '<?= addslashes(htmlspecialchars($car['name'])) ?>',
                            '$<?= number_format($car['price'],0) ?>',
                            '<?= addslashes(htmlspecialchars($car['description'] ?? '')) ?>',
                            <?= (int)($car['product_id'] ?? 0) ?>
                        )">View Details</button>
                    <?php if ($isLoggedIn && !empty($car['product_id'])): ?>
                    <form method="POST" action="../Controller/CartController.php">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $car['product_id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="redirect_to" value="../View/cars.php">
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
<div id="modal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal()">✕</button>
        <div id="modalName"></div>
        <div id="modalPrice"></div>
        <div id="modalDesc"></div>
        <div id="modalActions"></div>
    </div>
</div>

<footer><p>© 2026 Auto motive Hub Platform | All Rights Reserved</p></footer>

<script>
let activeFilter = 'all';

function filterCars(type, btn) {
    activeFilter = type;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

function searchCars(val) {
    applyFilters(val);
}

function applyFilters(searchVal) {
    if (searchVal === undefined) {
        searchVal = document.getElementById('searchInput').value;
    }
    searchVal = searchVal.toLowerCase().trim();

    let count = 0;
    document.querySelectorAll('.car-card').forEach(card => {
        const cardType = card.dataset.type || '';
        const cardName = card.dataset.name || '';

        const typeMatch = activeFilter === 'all' || cardType.includes(activeFilter);
        const nameMatch = !searchVal || cardName.includes(searchVal);

        const visible = typeMatch && nameMatch;
        card.classList.toggle('hidden', !visible);
        if (visible) count++;
    });
    document.getElementById('carCount').textContent = count + ' vehicles';
}

let currentProductId = 0;

function showDetails(name, price, desc, productId) {
    currentProductId = productId;
    document.getElementById('modalName').textContent  = name;
    document.getElementById('modalPrice').textContent = price;
    document.getElementById('modalDesc').textContent  = desc;

    let actionsHtml = '';
    <?php if ($isLoggedIn): ?>
    if (productId > 0) {
        actionsHtml = `
            <form method="POST" action="../Controller/CartController.php">
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="redirect_to" value="../View/cars.php">
                <button type="submit" class="modal-btn">Add to Cart</button>
            </form>`;
    } else {
        actionsHtml = '<button class="modal-btn" onclick="closeModal()">Contact for Price</button>';
    }
    <?php else: ?>
    actionsHtml = '<button class="modal-btn" onclick="requireLogin()">Add to Cart</button>';
    <?php endif; ?>

    document.getElementById('modalActions').innerHTML = actionsHtml;
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

function showLoginToast() {
    const existing = document.getElementById('loginToast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.id = 'loginToast';
    toast.innerHTML = '<i class=\"fa-solid fa-triangle-exclamation\"></i> Please log in first to add items to your cart!';
    toast.style.cssText = 'position:fixed;bottom:30px;right:30px;background:#d97706;color:white;padding:14px 24px;border-radius:10px;font-size:14px;font-weight:600;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.4);display:flex;align-items:center;gap:10px;';
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.transition='0.4s'; toast.style.opacity='0'; setTimeout(()=>toast.remove(),400); }, 3000);
}
function requireLogin() {
    showLoginToast();
}

document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<!-- Login Required Overlay -->
<div id="loginOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
    <div style="background:#1e293b; border:1px solid #334155; border-radius:16px; padding:40px 50px; text-align:center; max-width:380px; width:90%;">
        <div style="font-size:48px; margin-bottom:20px;">🔒</div>
        <h2 style="color:#fff; font-size:22px; margin-bottom:12px;">Login Required</h2>
        <p style="color:#94a3b8; font-size:15px; margin-bottom:30px; line-height:1.6;">You need to log in to your account before you can use the cart.</p>
        <a href="login.php" style="display:inline-block; background:#2563eb; color:#fff; padding:12px 40px; border-radius:8px; font-size:15px; font-weight:600; text-decoration:none;">OK — Go to Login</a>
    </div>
</div>

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
</body>
</html>