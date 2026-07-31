<?php

// Singleton Pattern: reuse one shared database connection.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Best Prices — Automotive Hub Platform</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    :root {
      --bg: #05070a;
      --surface: #0f172a;
      --card: #1e293b;
      --border: #334155;
      --blue: #2563eb;
      --blue-light: #60a5fa;
      --text: #f1f5f9;
      --muted: #94a3b8;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', Tahoma, sans-serif; }

    /* NAV */
    nav {
      display: flex; justify-content: space-between; align-items: center;
      padding: 16px 8%;
      background: rgba(0,0,0,0.97);
      border-bottom: 1px solid rgba(255,255,255,0.06);
      position: sticky; top: 0; z-index: 100;
    }
    .logo-section { display: flex; align-items: center; gap: 12px; }
    .logo-icon { font-size: 26px; font-weight: 800; color: var(--blue); }
    .logo-text { font-size: 16px; font-weight: bold; text-transform: uppercase; }
    .logo-text span { display: block; font-size: 11px; color: #444; font-weight: 400; letter-spacing: 2px; }
    .nav-links { display: flex; gap: 35px; list-style: none; }
    .nav-links a { text-decoration: none; color: #555; font-size: 13px; letter-spacing: 0.5px; transition: 0.3s; text-transform: uppercase; font-weight: 500; }
    .nav-links a:hover, .nav-links a.active { color: #fff; }
    .nav-actions { display: flex; align-items: center; gap: 20px; }
    .login-link { text-decoration: none; color: #555; font-size: 13px; font-weight: 600; text-transform: uppercase; }
    .signup-btn { text-decoration: none; background: var(--blue); color: white; padding: 10px 26px; border-radius: 4px; font-size: 13px; font-weight: 600; text-transform: uppercase; }
    .logout-btn { background: var(--blue); color: white; border: none; padding: 10px 26px; border-radius: 4px; font-size: 13px; font-weight: 600; text-transform: uppercase; cursor: pointer; }

    /* HEADER */
    .page-header {
      background: #000; padding: 70px 8% 50px;
      border-bottom: 1px solid #111; position: relative; overflow: hidden;
    }
    .page-header::before {
      content: ''; position: absolute; top: 0; right: 0;
      width: 600px; height: 100%;
      background: radial-gradient(ellipse at right, rgba(37,99,235,0.1) 0%, transparent 70%);
    }
    .ph-label { font-size: 10px; letter-spacing: 4px; color: var(--blue); text-transform: uppercase; margin-bottom: 14px; font-weight: 500; position: relative; }
    .ph-title { font-size: 56px; font-weight: 800; line-height: 1; letter-spacing: 2px; position: relative; }
    .ph-title span { color: var(--blue); }
    .ph-sub { color: #444; font-size: 15px; font-weight: 300; margin-top: 12px; position: relative; }

    /* FILTER BAR */
    .filter-bar {
      display: flex; align-items: center; gap: 12px;
      padding: 20px 8%; background: #050505;
      border-bottom: 1px solid #111; flex-wrap: wrap;
    }
    .filter-label { color: #333; font-size: 10px; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; margin-right: 4px; }
    .fbtn {
      background: transparent; border: 1px solid #1a1a1a; color: #555;
      padding: 7px 18px; border-radius: 20px; cursor: pointer;
      font-size: 12px; letter-spacing: 0.5px; text-transform: uppercase; font-weight: 500; transition: 0.25s;
    }
    .fbtn:hover { border-color: #333; color: #aaa; }
    .fbtn.active { background: var(--blue); border-color: var(--blue); color: #fff; }

    /* SECTION TITLE */
    .section-wrap { padding: 50px 8%; }
    .section-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .section-head h2 { font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: #fff; }
    .section-head span { color: #222; font-size: 12px; }

    /* GRID */
    .items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }

    /* CARD */
    .item-card {
      background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
      border: 1px solid #334155; border-radius: 20px; overflow: hidden;
      transition: all 0.4s cubic-bezier(0.165,0.84,0.44,1); cursor: pointer;
    }
    .item-card:hover { transform: translateY(-12px); border-color: #3b82f6; box-shadow: 0 20px 40px rgba(37,99,235,0.2); }
    .item-card.hidden { display: none; }

    .card-img { position: relative; height: 210px; overflow: hidden; background: #0f172a; }
    .card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
    .item-card:hover .card-img img { transform: scale(1.08); }
    .card-img::after {
      content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 70px;
      background: linear-gradient(to top, rgba(15,23,42,0.9), transparent);
      pointer-events: none;
    }

    .cat-badge {
      position: absolute; top: 14px; left: 14px; z-index: 2;
      padding: 4px 12px; border-radius: 20px; font-size: 11px;
      font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .badge-car      { background: #dc2626; color: #fff; }
    .badge-moto     { background: #d97706; color: #fff; }
    .badge-electric { background: #16a34a; color: #fff; }
    .badge-spare    { background: #7c3aed; color: #fff; }

    .prime-tag {
      position: absolute; top: 14px; right: 14px; z-index: 2;
      background: var(--blue); color: #fff;
      padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: 700;
    }

    .price-tag {
      position: absolute; bottom: 14px; right: 14px; z-index: 2;
      background: rgba(37,99,235,0.9); color: #fff;
      padding: 5px 14px; border-radius: 8px; font-weight: 700; font-size: 14px;
    }

    .card-body { padding: 20px; }
    .card-body h3 { font-size: 18px; font-weight: 800; margin-bottom: 4px; }
    .card-sub { color: var(--blue-light); font-size: 12px; font-weight: 600; margin-bottom: 6px; }
    .card-stars { color: #f1c40f; font-size: 13px; margin-bottom: 14px; }
    .card-stars span { color: #475569; margin-left: 4px; }

    .specs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 18px; }
    .spec-item { display: flex; align-items: center; gap: 6px; color: #94a3b8; font-size: 12px; }
    .spec-item i { color: var(--blue); width: 14px; }

    .card-actions { display: flex; gap: 10px; }
    .btn-details {
      flex: 1; padding: 10px; background: transparent;
      border: 1px solid var(--blue); color: #fff; cursor: pointer;
      border-radius: 8px; font-size: 13px; transition: 0.3s;
    }
    .btn-details:hover { background: var(--blue); }
    .btn-wish {
      padding: 10px 14px; background: transparent;
      border: 1px solid #334155; color: #64748b; cursor: pointer;
      border-radius: 8px; font-size: 14px; transition: 0.3s;
    }
    .btn-wish:hover { border-color: #ef4444; color: #ef4444; }

    /* SIDE PANEL */
    #panelOverlay {
      display: none; position: fixed; top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.75); z-index: 998; backdrop-filter: blur(6px);
    }
    #sidePanel {
      position: fixed; top: 0; right: -520px; width: 480px; height: 100%;
      background: linear-gradient(180deg, #0f172a 0%, #05070a 100%);
      border-left: 1px solid #1e293b; z-index: 999;
      overflow-y: auto; transition: right 0.4s cubic-bezier(0.165,0.84,0.44,1);
      box-shadow: -20px 0 60px rgba(0,0,0,0.6);
    }
    .panel-topbar {
      display: flex; justify-content: space-between; align-items: center;
      padding: 20px 28px; border-bottom: 1px solid #1e293b;
      background: rgba(0,0,0,0.5); position: sticky; top: 0; z-index: 2;
      backdrop-filter: blur(12px);
    }
    .panel-label { font-size: 10px; letter-spacing: 4px; color: var(--blue); text-transform: uppercase; margin-bottom: 3px; }
    .panel-brand { color: #64748b; font-size: 12px; }
    .panel-close {
      background: transparent; border: 1px solid #1e293b; color: #64748b;
      width: 36px; height: 36px; border-radius: 50%; cursor: pointer; font-size: 16px;
      display: flex; align-items: center; justify-content: center; transition: 0.2s;
    }
    .panel-close:hover { border-color: #ef4444; color: #ef4444; }
    .panel-img { position: relative; height: 260px; overflow: hidden; background: #0f172a; }
    .panel-img img { width: 100%; height: 100%; object-fit: cover; opacity: 0.85; }
    .panel-img::after {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(to bottom, transparent 40%, #0f172a 100%);
    }
    .panel-img-badge {
      position: absolute; top: 16px; left: 16px; z-index: 2;
      padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;
    }
    .panel-img-price {
      position: absolute; bottom: 16px; right: 16px; z-index: 2;
      background: var(--blue); color: #fff; padding: 7px 16px;
      border-radius: 8px; font-weight: 700; font-size: 16px;
    }
    .panel-body { padding: 28px; }
    .panel-name { font-size: 26px; font-weight: 800; margin-bottom: 6px; letter-spacing: 1px; }
    .panel-stars { color: #f1c40f; font-size: 14px; margin-bottom: 18px; }
    .panel-desc { color: #64748b; line-height: 1.75; font-size: 14px; margin-bottom: 28px; }
    .panel-divider { height: 1px; background: #1e293b; margin-bottom: 24px; }
    .panel-section-title { font-size: 10px; letter-spacing: 3px; color: var(--blue); text-transform: uppercase; margin-bottom: 16px; font-weight: 600; }
    .panel-specs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 28px; }
    .panel-spec-box {
      background: #0f172a; border: 1px solid #1e293b; border-radius: 10px; padding: 14px 16px;
    }
    .panel-spec-icon { font-size: 20px; margin-bottom: 6px; }
    .panel-spec-label { color: #475569; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px; }
    .panel-spec-val { color: #f1f5f9; font-size: 13px; font-weight: 600; }
    .panel-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 10px 0; border-bottom: 1px solid #0f172a;
    }
    .panel-row-label { color: #475569; font-size: 13px; }
    .panel-row-val { color: #e2e8f0; font-size: 13px; font-weight: 600; }
    .panel-actions { display: flex; gap: 12px; margin-top: 32px; }
    .panel-btn-main {
      flex: 1; padding: 13px; background: var(--blue); border: none;
      color: #fff; font-size: 13px; font-weight: 600; cursor: pointer;
      border-radius: 8px; letter-spacing: 1px; text-transform: uppercase; transition: 0.3s;
    }
    .panel-btn-main:hover { background: #1d4ed8; }
    .panel-btn-wish {
      padding: 13px 18px; background: transparent;
      border: 1px solid #334155; color: #64748b; cursor: pointer;
      border-radius: 8px; font-size: 16px; transition: 0.3s;
    }
    .panel-btn-wish:hover { border-color: #ef4444; color: #ef4444; }

    footer {
      text-align: center; padding: 40px; border-top: 1px solid #0a0a0a;
      color: #222; background: #000; font-size: 11px;
      letter-spacing: 2px; text-transform: uppercase; margin-top: 20px;
    }
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
    <a href="cart.php" class="login-link"><i class="fa-solid fa-cart-shopping"></i></a>

    <?php if ($isLoggedIn): ?>
      <a href="orders.php" class="login-link">My Orders</a>
      <a href="wishlist.php" class="login-link">Wishlist</a>

      <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="admin.php" class="login-link">Admin</a>
      <?php endif; ?>

      <form method="POST" action="../controller/AuthController.php" style="display:inline;">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="logout-btn">Logout</button>
      </form>
    <?php else: ?>
      <a href="login.php" class="login-link">Login</a>
      <a href="register.php" class="signup-btn">Sign Up</a>
    <?php endif; ?>
  </div>
</nav>

<div class="page-header">
  <div class="ph-label">Automotive Hub Platform — Unbeatable Value</div>
  <h1 class="ph-title">Best <span>Prices</span></h1>
  <p class="ph-sub">Top-rated vehicles &amp; parts at the best prices — quality you can afford</p>
</div>

<div class="filter-bar">
  <span class="filter-label">Filter</span>
  <button class="fbtn active" onclick="filterItems('all', this)">All</button>
  <button class="fbtn" onclick="filterItems('car', this)">Cars</button>
  <button class="fbtn" onclick="filterItems('moto', this)">Motorcycles</button>
  <button class="fbtn" onclick="filterItems('electric', this)">Electric</button>
  <button class="fbtn" onclick="filterItems('spare', this)">Spare Parts</button>
</div>

<div class="section-wrap">
  <div class="section-head">
    <h2>Best Value Picks</h2>
    <span id="countLabel">Showing all items</span>
  </div>
  <div class="items-grid" id="itemsGrid"></div>
</div>

<div id="panelOverlay" onclick="closePanel()"></div>
<div id="sidePanel">
  <div class="panel-topbar">
    <div>
      <div class="panel-label">Item Details</div>
      <div class="panel-brand" id="pBrand"></div>
    </div>
    <button class="panel-close" onclick="closePanel()">✕</button>
  </div>
  <div class="panel-img">
    <img id="pImg" src="" alt="">
    <div class="panel-img-badge" id="pBadge"></div>
    <div class="panel-img-price" id="pPrice"></div>
  </div>
  <div class="panel-body">
    <div class="panel-name" id="pName"></div>
    <div class="panel-stars" id="pStars"></div>
    <p class="panel-desc" id="pDesc"></p>
    <div class="panel-divider"></div>
    <div class="panel-section-title">Specifications</div>
    <div class="panel-specs-grid" id="pSpecs"></div>
    <div class="panel-divider"></div>
    <div class="panel-section-title">Overview</div>
    <div id="pRows"></div>
    <div class="panel-actions">
      <?php if ($isLoggedIn): ?>
        <button class="panel-btn-main" onclick="window.location.href='spare-parts.php'">Shop Now</button>
      <?php else: ?>
        <button class="panel-btn-main" onclick="window.location.href='login.php'">Login to Buy</button>
      <?php endif; ?>
      <button class="panel-btn-wish">♡</button>
    </div>
  </div>
</div>

<footer>© 2026 Automotive Hub Platform — All Rights Reserved</footer>

<script>
const items = [
  // ── CARS ──
  {
    cat: 'car', catLabel: 'Car', badgeClass: 'badge-car',
    name: 'Lamborghini Huracán EVO',
    brand: 'Lamborghini • 2024',
    stars: '★★★★★', rating: '5.0',
    img: 'images/Lamborghini Huracan.jpg',
    price: '$248,000',
    desc: 'Naturally aspirated V10 supercar delivering a raw, thrilling driving experience with AWD precision.',
    specs: [
      { icon: '⚡', label: 'Engine', val: '5.2L V10 NA' },
      { icon: '💨', label: 'Power', val: '640 HP' },
      { icon: '🏎', label: '0–100 km/h', val: '2.9 sec' },
      { icon: '🔝', label: 'Top Speed', val: '325 km/h' },
      { icon: '⛽', label: 'Fuel', val: 'Petrol' },
      { icon: '⚙️', label: 'Gearbox', val: '7-Speed DCT' },
      { icon: '🛞', label: 'Drive', val: 'AWD' },
      { icon: '📦', label: 'Body', val: 'Coupe' },
    ],
    rows: [
      { label: 'Category', val: 'Supercar' },
      { label: 'Origin', val: 'Italy' },
      { label: 'Warranty', val: '3 Years' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  {
    cat: 'car', catLabel: 'Car', badgeClass: 'badge-car',
    name: 'Ferrari F8 Tributo',
    brand: 'Ferrari • 2024',
    stars: '★★★★★', rating: '5.0',
    img: 'images/Ferrari F8 Tributo.jpg',
    price: '$276,550',
    desc: 'A tribute to the most powerful V8 in Ferrari history — pure passion from Maranello.',
    specs: [
      { icon: '⚡', label: 'Engine', val: '3.9L Twin-V8' },
      { icon: '💨', label: 'Power', val: '710 HP' },
      { icon: '🏎', label: '0–100 km/h', val: '2.9 sec' },
      { icon: '🔝', label: 'Top Speed', val: '340 km/h' },
      { icon: '⛽', label: 'Fuel', val: 'Petrol' },
      { icon: '⚙️', label: 'Gearbox', val: '7-Speed F1 DCT' },
      { icon: '🛞', label: 'Drive', val: 'RWD' },
      { icon: '📦', label: 'Body', val: 'Coupe' },
    ],
    rows: [
      { label: 'Category', val: 'Supercar' },
      { label: 'Origin', val: 'Italy' },
      { label: 'Warranty', val: '3 Years' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  {
    cat: 'car', catLabel: 'Car', badgeClass: 'badge-car',
    name: 'Toyota Camry XSE',
    brand: 'Toyota • 2024',
    stars: '★★★★☆', rating: '4.7',
    img: 'images/Toyota Camry.jpg',
    price: '$28,400',
    desc: 'Redesigned for 2024 with a bold new look, hybrid powertrain, and top safety ratings.',
    specs: [
      { icon: '⚡', label: 'Engine', val: '2.5L I4 Hybrid' },
      { icon: '💨', label: 'Power', val: '203 HP' },
      { icon: '🏎', label: '0–100 km/h', val: '7.4 sec' },
      { icon: '🔝', label: 'Top Speed', val: '200 km/h' },
      { icon: '⛽', label: 'Fuel', val: 'Hybrid' },
      { icon: '⚙️', label: 'Gearbox', val: 'eCVT' },
      { icon: '🛞', label: 'Drive', val: 'FWD' },
      { icon: '📦', label: 'Body', val: 'Sedan' },
    ],
    rows: [
      { label: 'Category', val: 'Sedan' },
      { label: 'Origin', val: 'Japan' },
      { label: 'Warranty', val: '3 Years' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  {
    cat: 'car', catLabel: 'Car', badgeClass: 'badge-car',
    name: 'BMW X5 xDrive40i',
    brand: 'BMW • 2024',
    stars: '★★★★★', rating: '4.8',
    img: 'images/BMW X5.jpg',
    price: '$67,300',
    desc: 'The benchmark luxury SUV combining performance, tech, and practicality.',
    specs: [
      { icon: '⚡', label: 'Engine', val: '3.0L I6' },
      { icon: '💨', label: 'Power', val: '335 HP' },
      { icon: '🏎', label: '0–100 km/h', val: '5.3 sec' },
      { icon: '🔝', label: 'Top Speed', val: '250 km/h' },
      { icon: '⛽', label: 'Fuel', val: 'Petrol' },
      { icon: '⚙️', label: 'Gearbox', val: '8-Speed Auto' },
      { icon: '🛞', label: 'Drive', val: 'xDrive AWD' },
      { icon: '📦', label: 'Body', val: 'SUV' },
    ],
    rows: [
      { label: 'Category', val: 'Luxury SUV' },
      { label: 'Origin', val: 'Germany' },
      { label: 'Warranty', val: '4 Years' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  // ── MOTORCYCLES ──
  {
    cat: 'moto', catLabel: 'Motorcycle', badgeClass: 'badge-moto',
    name: 'Yamaha R1',
    brand: 'Yamaha • 2024',
    stars: '★★★★★', rating: '4.9',
    img: 'images/Yamaha R1.jpg',
    price: '$17,500',
    desc: 'Top supersport bike with cutting-edge electronics and MotoGP-derived tech.',
    specs: [
      { icon: '⚡', label: 'Engine', val: '998cc Inline-4' },
      { icon: '💨', label: 'Power', val: '200 HP' },
      { icon: '🏎', label: '0–100 km/h', val: '2.8 sec' },
      { icon: '🔝', label: 'Top Speed', val: '299 km/h' },
      { icon: '⛽', label: 'Fuel', val: 'Petrol' },
      { icon: '⚙️', label: 'Gearbox', val: '6-Speed' },
      { icon: '🛞', label: 'Drive', val: 'Chain' },
      { icon: '📦', label: 'Body', val: 'Supersport' },
    ],
    rows: [
      { label: 'Category', val: 'Sport' },
      { label: 'Origin', val: 'Japan' },
      { label: 'Warranty', val: '1 Year' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  {
    cat: 'moto', catLabel: 'Motorcycle', badgeClass: 'badge-moto',
    name: 'Harley-Davidson Sportster S',
    brand: 'Harley-Davidson • 2024',
    stars: '★★★★★', rating: '4.8',
    img: 'images/Harley Sportster S.jpg',
    price: '$14,999',
    desc: 'Iconic American cruiser with modern liquid-cooled Revolution Max engine.',
    specs: [
      { icon: '⚡', label: 'Engine', val: 'Rev Max 1250cc' },
      { icon: '💨', label: 'Power', val: '121 HP' },
      { icon: '🏎', label: '0–100 km/h', val: '3.9 sec' },
      { icon: '🔝', label: 'Top Speed', val: '220 km/h' },
      { icon: '⛽', label: 'Fuel', val: 'Petrol' },
      { icon: '⚙️', label: 'Gearbox', val: '6-Speed' },
      { icon: '🛞', label: 'Drive', val: 'Belt' },
      { icon: '📦', label: 'Body', val: 'Cruiser' },
    ],
    rows: [
      { label: 'Category', val: 'Cruiser' },
      { label: 'Origin', val: 'USA' },
      { label: 'Warranty', val: '2 Years' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  // ── ELECTRIC ──
  {
    cat: 'electric', catLabel: 'Electric', badgeClass: 'badge-electric',
    name: 'Tesla Model S Plaid',
    brand: 'Tesla • 2024',
    stars: '★★★★★', rating: '4.9',
    img: 'images/Model S Plaid.jpg',
    price: '$74,990',
    desc: 'The quickest production car ever made. Tri-motor AWD with 1020 HP and 652 km range.',
    specs: [
      { icon: '⚡', label: 'Motor', val: 'Tri-Motor EV' },
      { icon: '💨', label: 'Power', val: '1020 HP' },
      { icon: '🏎', label: '0–100 km/h', val: '2.1 sec' },
      { icon: '🔝', label: 'Top Speed', val: '322 km/h' },
      { icon: '🔋', label: 'Battery', val: '100 kWh' },
      { icon: '📡', label: 'Range', val: '652 km' },
      { icon: '🛞', label: 'Drive', val: 'Tri AWD' },
      { icon: '📦', label: 'Body', val: 'Sedan' },
    ],
    rows: [
      { label: 'Category', val: 'Electric Sedan' },
      { label: 'Origin', val: 'USA' },
      { label: 'Warranty', val: '4 Years' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  {
    cat: 'electric', catLabel: 'Electric', badgeClass: 'badge-electric',
    name: 'Ford F-150 Lightning Pro',
    brand: 'Ford • 2024',
    stars: '★★★★☆', rating: '4.6',
    img: 'images/Ford F-150 Lightning.jpg',
    price: '$55,974',
    desc: "America's best-selling truck goes electric. 580 HP, bidirectional charging, towing up to 4.5 tons.",
    specs: [
      { icon: '⚡', label: 'Motor', val: 'Dual EV' },
      { icon: '💨', label: 'Power', val: '580 HP' },
      { icon: '🏎', label: '0–100 km/h', val: '4.5 sec' },
      { icon: '🔝', label: 'Top Speed', val: '180 km/h' },
      { icon: '🔋', label: 'Battery', val: '131 kWh' },
      { icon: '📡', label: 'Range', val: '515 km' },
      { icon: '🛞', label: 'Drive', val: '4WD' },
      { icon: '📦', label: 'Body', val: 'Truck' },
    ],
    rows: [
      { label: 'Category', val: 'Electric Truck' },
      { label: 'Origin', val: 'USA' },
      { label: 'Warranty', val: '3 Years' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  // ── SPARE PARTS ──
  {
    cat: 'spare', catLabel: 'Spare Part', badgeClass: 'badge-spare',
    name: 'Brembo GT Brake Kit 6-Piston',
    brand: 'Brembo • Performance',
    stars: '★★★★★', rating: '4.9',
    img: 'images/GT Brake Kit 6-Piston.jpg',
    price: '$1,850',
    desc: 'World-class Italian braking performance. 380mm slotted disc with 6-piston monoblock caliper for extreme stopping power.',
    specs: [
      { icon: '🔧', label: 'Type', val: '6-Piston Mono' },
      { icon: '📏', label: 'Disc Size', val: '380mm Slotted' },
      { icon: '🌡️', label: 'Heat Resist.', val: '900°C' },
      { icon: '⚖️', label: 'Material', val: 'Aluminum' },
      { icon: '🏎', label: 'Use', val: 'Track & Road' },
      { icon: '🔩', label: 'Pistons', val: '6 per caliper' },
      { icon: '✅', label: 'Certified', val: 'TÜV / DOT' },
      { icon: '📦', label: 'Kit', val: 'Full Front Set' },
    ],
    rows: [
      { label: 'Category', val: 'Braking System' },
      { label: 'Origin', val: 'Italy' },
      { label: 'Warranty', val: '2 Years' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  {
    cat: 'spare', catLabel: 'Spare Part', badgeClass: 'badge-spare',
    name: 'K&N High-Flow Air Filter',
    brand: 'K&N • Performance',
    stars: '★★★★★', rating: '4.9',
    img: 'images/K&N Air Filter.jpg',
    price: '$89',
    desc: 'Lifetime washable performance air filter — increases airflow up to 50% for more power and better fuel economy.',
    specs: [
      { icon: '🔧', label: 'Type', val: 'High-Flow Cotton' },
      { icon: '💨', label: 'Airflow', val: '+50% vs OEM' },
      { icon: '🔄', label: 'Reusable', val: 'Yes — Lifetime' },
      { icon: '🛡️', label: 'Filtration', val: '99% efficiency' },
      { icon: '🏎', label: 'Power Gain', val: '+3–5 HP' },
      { icon: '📏', label: 'Fit', val: 'Universal Sizes' },
      { icon: '✅', label: 'Certified', val: 'CARB Legal' },
      { icon: '📦', label: 'Includes', val: 'Filter + Oil' },
    ],
    rows: [
      { label: 'Category', val: 'Engine Air System' },
      { label: 'Origin', val: 'USA' },
      { label: 'Warranty', val: 'Million Mile' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  {
    cat: 'spare', catLabel: 'Spare Part', badgeClass: 'badge-spare',
    name: 'NGK Iridium Spark Plugs',
    brand: 'NGK • OEM Grade',
    stars: '★★★★★', rating: '4.9',
    img: 'images/NGK Spark Plugs.jpg',
    price: '$75',
    desc: 'Iridium fine-wire tip for ultra-precise ignition — OEM supplier to Toyota, Honda, BMW, and more.',
    specs: [
      { icon: '🔧', label: 'Type', val: 'Iridium IX' },
      { icon: '⚡', label: 'Tip', val: '0.6mm Fine Wire' },
      { icon: '🌡️', label: 'Heat Range', val: 'Multi-range' },
      { icon: '🔄', label: 'Lifespan', val: '100,000 km' },
      { icon: '🏎', label: 'Gain', val: 'Smoother idle' },
      { icon: '📏', label: 'Gap', val: 'Pre-gapped' },
      { icon: '✅', label: 'OEM', val: 'Toyota / BMW' },
      { icon: '📦', label: 'Pack', val: '4 or 6 plugs' },
    ],
    rows: [
      { label: 'Category', val: 'Ignition System' },
      { label: 'Origin', val: 'Japan' },
      { label: 'Warranty', val: '1 Year' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
  {
    cat: 'spare', catLabel: 'Spare Part', badgeClass: 'badge-spare',
    name: 'Enkei Alloy Wheels',
    brand: 'Enkei • Racing',
    stars: '★★★★★', rating: '4.8',
    img: 'images/Enkei Alloy Wheels.jpg',
    price: '$1,200',
    desc: 'JWL/VIA certified flow-formed alloy wheels — lighter, stronger, and better looking than OEM.',
    specs: [
      { icon: '🔧', label: 'Type', val: 'Flow-Formed' },
      { icon: '📏', label: 'Size', val: '18" / 19" / 20"' },
      { icon: '⚖️', label: 'Weight', val: '8.5 kg each' },
      { icon: '💪', label: 'Material', val: 'A356 Aluminum' },
      { icon: '🎨', label: 'Finish', val: 'Gloss / Matte' },
      { icon: '🔩', label: 'PCD', val: '5x114.3' },
      { icon: '✅', label: 'Certified', val: 'JWL / VIA' },
      { icon: '📦', label: 'Set', val: '4 wheels' },
    ],
    rows: [
      { label: 'Category', val: 'Wheels & Tyres' },
      { label: 'Origin', val: 'Japan' },
      { label: 'Warranty', val: '3 Years' },
      { label: 'Availability', val: 'In Stock' },
    ]
  },
];

let currentCat = 'all';

// ===============================
// Render Cards
// ===============================
function renderCards(list) {
  const grid = document.getElementById('itemsGrid');
  grid.innerHTML = list.map((item, i) => `
    <div class="item-card ${item.cat}" style="animation-delay:${i * 0.05}s">
      <div class="card-img">
        <img src="${item.img}" alt="${item.name}" loading="lazy">
        <span class="cat-badge ${item.badgeClass}">${item.catLabel}</span>
        <span class="prime-tag">💰 Best Price</span>
        <span class="price-tag">${item.price}</span>
      </div>
      <div class="card-body">
        <h3>${item.name}</h3>
        <div class="card-sub">${item.brand}</div>
        <div class="card-stars">${item.stars} <span>(${item.rating})</span></div>
        <div class="specs-grid">
          <div class="spec-item"><i class="fa-solid fa-bolt"></i> ${item.specs[0].val}</div>
          <div class="spec-item"><i class="fa-solid fa-gauge-high"></i> ${item.specs[1].val}</div>
          <div class="spec-item"><i class="fa-solid fa-stopwatch"></i> ${item.specs[2].val}</div>
          <div class="spec-item"><i class="fa-solid fa-flag-checkered"></i> ${item.specs[3].val}</div>
        </div>
        <div class="card-actions">
          <button class="btn-details" onclick="openPanel(${i})">View Details</button>
          <button class="btn-wish">♡</button>
        </div>
      </div>
    </div>
  `).join('');
  document.getElementById('countLabel').textContent = `Showing ${list.length} items`;
}

// ===============================
// Open Side Panel
// ===============================
function openPanel(idx) {
  const filtered = currentCat === 'all' ? items : items.filter(i => i.cat === currentCat);
  const it = filtered[idx] || items[idx];

  document.getElementById('pBrand').textContent  = it.brand;
  document.getElementById('pName').textContent   = it.name;
  document.getElementById('pStars').textContent  = it.stars + ' (' + it.rating + ')';
  document.getElementById('pDesc').textContent   = it.desc;
  document.getElementById('pPrice').textContent  = it.price;
  document.getElementById('pImg').src            = it.img;
  document.getElementById('pImg').alt            = it.name;

  const badge = document.getElementById('pBadge');
  badge.textContent  = it.catLabel;
  badge.className    = 'panel-img-badge ' + it.badgeClass;

  document.getElementById('pSpecs').innerHTML = it.specs.map(function (s) {
    return `
      <div class="panel-spec-box">
        <div class="panel-spec-icon">${s.icon}</div>
        <div class="panel-spec-label">${s.label}</div>
        <div class="panel-spec-val">${s.val}</div>
      </div>`;
  }).join('');

  document.getElementById('pRows').innerHTML = it.rows.map(function (r) {
    return `
      <div class="panel-row">
        <span class="panel-row-label">${r.label}</span>
        <span class="panel-row-val">${r.val}</span>
      </div>`;
  }).join('');

  document.getElementById('panelOverlay').style.display = 'block';
  document.getElementById('sidePanel').style.right      = '0';
  document.body.style.overflow = 'hidden';
}

// ===============================
// Close Side Panel
// ===============================
function closePanel() {
  document.getElementById('panelOverlay').style.display = 'none';
  document.getElementById('sidePanel').style.right      = '-520px';
  document.body.style.overflow = '';
}

// ===============================
// Filter — Best Prices page keeps cheapest per category
// ===============================
function parsePriceVal(p) {
  return parseFloat(p.replace(/[^0-9.]/g, '')) || 999999;
}

const cheapestPerCat = {};
items.forEach(function (it) {
  const p = parsePriceVal(it.price);
  if (!cheapestPerCat[it.cat] || p < parsePriceVal(cheapestPerCat[it.cat].price)) {
    cheapestPerCat[it.cat] = it;
  }
});

const budgetItems = items
  .filter(function (it) {
    return parsePriceVal(it.price) <= 30000 || it === cheapestPerCat[it.cat];
  })
  .sort(function (a, b) {
    return parsePriceVal(a.price) - parsePriceVal(b.price);
  })
  .filter(function (it, idx, arr) {
    return arr.indexOf(it) === idx;
  });

renderCards(budgetItems);

function filterItems(cat, btn) {
  document.querySelectorAll('.fbtn').forEach(function (b) {
    b.classList.remove('active');
  });
  btn.classList.add('active');
  currentCat = cat;
  const filtered = cat === 'all' ? budgetItems : budgetItems.filter(function (i) {
    return i.cat === cat;
  });
  renderCards(filtered);
}
</script>

</body>
</html>
