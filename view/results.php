<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/product.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

$productModel = new Product($db);
$isLoggedIn   = isset($_SESSION['user_id']);

// Read parameters from recommendation.php form
$type     = trim($_GET['type'] ?? '');
$subtype  = trim($_GET['subtype'] ?? '');
$budget   = trim($_GET['budget'] ?? '');

// Validate
$allowedTypes   = ['Cars', 'Motorcycles', 'Electric'];
$allowedBudgets = ['low', 'mid', 'high'];

if (!in_array($type, $allowedTypes) || !in_array($budget, $allowedBudgets)) {
    header("Location: recommendation.php");
    exit;
}

// Budget to price range
$minPrice = 0;
$maxPrice = PHP_INT_MAX;

if ($budget === 'low') {
    $maxPrice = 30000;
} elseif ($budget === 'mid') {
    $minPrice = 30000;
    $maxPrice = 100000;
} elseif ($budget === 'high') {
    $minPrice = 100000;
}

// Fetch products by category + price range from DB
$query = "SELECT * FROM Product
          WHERE category = :category
            AND sub_type = :subtype
            AND price BETWEEN :min_price AND :max_price
          ORDER BY average_rating DESC, price ASC";
$stmt = $db->prepare($query);
$stmt->execute([
    ':category'  => $type,
    ':subtype'   => $subtype,
    ':min_price' => $minPrice,
    ':max_price' => $maxPrice,
]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Budget label for display
$budgetLabels = [
    'low'  => 'Under $30,000',
    'mid'  => '$30,000 – $100,000',
    'high' => 'Above $100,000',
];
$budgetLabel = $budgetLabels[$budget];

// Badge color mapping
function getBadgeClass($subType) {
    $map = [
        'Sport'     => 'sport',
        'Luxury'    => 'luxury',
        'SUV'       => 'popular',
        'Sedan'     => 'new',
        'Cruiser'   => 'popular',
        'Adventure' => 'new',
        'Electric'  => 'limited',
        'Truck'     => 'popular',
    ];
    return $map[$subType] ?? 'popular';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommended Vehicles - Automotive Hub</title>
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

        /* Page Header */
        .page-header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); padding: 50px 8% 35px; border-bottom: 1px solid #1e293b; }
        .page-header h1 { font-size: 36px; font-weight: 800; margin-bottom: 8px; }
        .page-header h1 span { color: #60a5fa; }
        .page-header p   { color: #64748b; font-size: 15px; }

        .tags { display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap; }
        .tag { background: #1e293b; border: 1px solid #334155; border-radius: 20px; padding: 5px 14px; font-size: 12px; color: #94a3b8; }
        .tag span { color: #60a5fa; font-weight: 700; }

        /* Grid */
        .results-section { padding: 50px 8%; }
        .section-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .section-info h2 { font-size: 22px; font-weight: 700; }
        .section-info a  { color: #2563eb; font-size: 13px; text-decoration: none; }
        .section-info a:hover { text-decoration: underline; }

        .cars-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }

        .car-card { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); border: 1px solid #334155; border-radius: 20px; overflow: hidden; transition: all 0.4s cubic-bezier(0.165,0.84,0.44,1); }
        .car-card:hover { transform: translateY(-12px); border-color: #3b82f6; box-shadow: 0 20px 40px rgba(37,99,235,0.2); }

        .car-image { position: relative; height: 200px; overflow: hidden; background: #0f172a; }
        .car-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .car-card:hover .car-image img { transform: scale(1.08); }

        .badge { position: absolute; top: 15px; left: 15px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge.new      { background: #16a34a; color: white; }
        .badge.popular  { background: #d97706; color: white; }
        .badge.luxury   { background: #7c3aed; color: white; }
        .badge.sport    { background: #dc2626; color: white; }
        .badge.limited  { background: #0369a1; color: white; }

        .price-tag { position: absolute; bottom: 15px; right: 15px; background: #2563eb; color: white; padding: 6px 14px; border-radius: 8px; font-weight: 700; font-size: 15px; }

        .car-info { padding: 20px; }
        .car-info h3 { font-size: 18px; font-weight: 700; margin-bottom: 5px; }
        .car-brand { color: #60a5fa; font-size: 13px; margin-bottom: 12px; font-weight: 600; }
        .stars { color: #f1c40f; font-size: 13px; margin-bottom: 14px; }
        .stars span { color: #64748b; margin-left: 5px; }

        .card-actions { display: flex; gap: 10px; }
        .btn-details  { flex: 1; padding: 10px; background: transparent; border: 1px solid #2563eb; color: white; cursor: pointer; border-radius: 8px; font-size: 13px; transition: 0.3s; }
        .btn-details:hover { background: #2563eb; }
        .btn-wishlist { padding: 10px 14px; background: transparent; border: 1px solid #334155; color: #64748b; cursor: pointer; border-radius: 8px; font-size: 13px; transition: 0.3s; }
        .btn-wishlist:hover { border-color: #ef4444; color: #ef4444; }

        /* Empty state */
        .empty-state { text-align: center; padding: 100px 20px; }
        .empty-state .icon { font-size: 64px; margin-bottom: 20px; }
        .empty-state h2 { font-size: 26px; font-weight: 700; margin-bottom: 10px; }
        .empty-state p  { color: #64748b; margin-bottom: 30px; }
        .try-again-btn { display: inline-block; padding: 12px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: 0.3s; }
        .try-again-btn:hover { background: #1d4ed8; }

        /* Modal */
        #modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 999; align-items: center; justify-content: center; backdrop-filter: blur(8px); }
        .modal-box { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); border: 1px solid #334155; border-radius: 20px; padding: 40px; max-width: 460px; width: 90%; position: relative; }
        .modal-close { position: absolute; top: 16px; right: 20px; background: none; border: none; color: #64748b; font-size: 20px; cursor: pointer; }
        .modal-close:hover { color: #fff; }
        #modalName  { font-size: 24px; font-weight: 800; margin-bottom: 6px; }
        #modalPrice { color: #60a5fa; font-size: 22px; font-weight: 700; margin-bottom: 18px; }
        #modalDesc  { color: #64748b; line-height: 1.7; margin-bottom: 28px; font-size: 14px; }
        .modal-btn  { width: 100%; padding: 13px; background: #2563eb; border: none; color: white; font-size: 14px; font-weight: 600; cursor: pointer; border-radius: 8px; transition: 0.3s; }
        .modal-btn:hover { background: #1d4ed8; }

        footer { text-align: center; padding: 40px; border-top: 1px solid #1e293b; color: #475569; margin-top: 20px; }
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
        <li><a href="index.php">Home</a></li>
        <li><a href="cars.php">Cars</a></li>
        <li><a href="motorcycles.php">Motorcycles</a></li>
        <li><a href="electric-vehicles.php">Electric</a></li>
        <li><a href="spare-parts.php">Spare Parts</a></li>
    </ul>
    <div class="nav-actions">
        <a href="cart.php" class="login-link"><i class="fa-solid fa-cart-shopping"></i></a>
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

<!-- Page Header -->
<div class="page-header">
    <h1>Recommended <span>Vehicles</span></h1>
    <p>Here are the best matches based on your preferences</p>
    <div class="tags">
        <div class="tag">Category: <span><?= htmlspecialchars($type) ?></span></div>

<div class="tag">Type: <span><?= htmlspecialchars($subtype) ?></span></div>

<div class="tag">Budget: <span><?= htmlspecialchars($budgetLabel) ?></span></div>
    </div>
</div>

<!-- Results -->
<section class="results-section">

    <?php if (empty($results)): ?>

        <div class="empty-state">
            <div class="icon">😢</div>
            <h2>No Vehicles Found</h2>
            <p>We couldn't find any <?= htmlspecialchars($type) ?> in the <?= htmlspecialchars($budgetLabel) ?> range.<br>Try adjusting your preferences.</p>
            <a href="recommendation.php" class="try-again-btn">
                <i class="fa-solid fa-rotate-left"></i>&nbsp; Try Again
            </a>
        </div>

    <?php else: ?>

        <div class="section-info">
            <h2><?= count($results) ?> Match<?= count($results) !== 1 ? 'es' : '' ?> Found</h2>
            <a href="recommendation.php"><i class="fa-solid fa-rotate-left"></i> Search Again</a>
        </div>

        <div class="cars-grid">
            <?php foreach ($results as $item): ?>
            <?php $badgeCls = getBadgeClass($item['sub_type'] ?? ''); ?>
            <div class="car-card">
                <div class="car-image">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.style.display='none'">
                    <?php endif; ?>
                    <span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($item['sub_type'] ?? $item['category']) ?></span>
                    <span class="price-tag">$<?= number_format($item['price'], 0) ?></span>
                </div>
                <div class="car-info">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <div class="car-brand"><?= htmlspecialchars($item['brand']) ?></div>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++) echo ($i <= round($item['average_rating'] ?? 0)) ? '★' : '☆'; ?>
                        <span>(<?= $item['review_count'] ?? 0 ?>)</span>
                    </div>
                    <div class="card-actions">
                        <button class="btn-details"
                            onclick="showDetails(
                                '<?= addslashes(htmlspecialchars($item['name'])) ?>',
                                '$<?= number_format($item['price'], 0) ?>',
                                '<?= addslashes(htmlspecialchars($item['description'] ?? '')) ?>',
                                <?= (int)$item['product_id'] ?>
                            )">View Details</button>
                        <?php if ($isLoggedIn && !empty($item['product_id'])): ?>
                        <form method="POST" action="../Controller/CartController.php">
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="redirect_to" value="../View/results.php">
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

    <?php endif; ?>

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

<footer>
    <p>© 2026 Auto motive Hub Platform | All Rights Reserved</p>
</footer>

<script>
function showDetails(name, price, desc, productId) {
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
                <input type="hidden" name="redirect_to" value="../View/results.php">
                <button type="submit" class="modal-btn">Add to Cart</button>
            </form>`;
    } else {
        actionsHtml = '<button class="modal-btn" onclick="closeModal()">Contact for Price</button>';
    }
    <?php else: ?>
    actionsHtml = '<a href="login.php" class="modal-btn" style="display:block;text-align:center;text-decoration:none;">Login to Buy</a>';
    <?php endif; ?>

    document.getElementById('modalActions').innerHTML = actionsHtml;
    document.getElementById('modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
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