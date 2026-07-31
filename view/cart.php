<?php

session_start();

$isLoggedIn = isset($_SESSION['user_id']);

// If not logged in, show alert then redirect to login
if (!$isLoggedIn): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Required</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #05070a; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .box { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 40px 50px; text-align: center; max-width: 380px; width: 90%; }
        .icon { font-size: 48px; margin-bottom: 20px; }
        h2 { color: #fff; font-size: 22px; margin-bottom: 12px; }
        p { color: #94a3b8; font-size: 15px; margin-bottom: 30px; line-height: 1.6; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 12px 40px; border-radius: 8px; font-size: 15px; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .btn:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">🔒</div>
        <h2>Login Required</h2>
        <p>You need to log in to your account before you can view or use the cart.</p>
        <a href="login.php" class="btn">OK — Go to Login</a>
    </div>
</body>
</html>
<?php
    exit;
endif;

require_once __DIR__ . "/../model/Database.php";
require_once __DIR__ . "/../model/cart.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

$userId = $_SESSION['user_id'];

$cart  = new Cart($db, $userId, session_id());
$items = $cart->getItems();
$total = $cart->getTotal();

$currentUrl = "../view/cart.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Automotive Hub</title>
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
    <h1>Your <span>Cart</span></h1>
    <p>Review your selected vehicles and parts before checkout</p>
</div>

<div class="cart-section">

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">🛒</div>
            <h3>Your cart is empty</h3>
            <p>Browse our collection and add products to your cart</p>
            <a href="cars.php" class="btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>

        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:15px;">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="" class="cart-item-img">
                            <?php else: ?>
                                <div style="width:70px;height:55px;background:#1e293b;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:24px;">🚗</div>
                            <?php endif; ?>
                            <div>
                                <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="cart-item-brand"><?= htmlspecialchars($item['brand']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td>
                        <form method="POST" action="../controller/CartController.php" style="display:flex;align-items:center;gap:8px;">
                            <input type="hidden" name="action" value="update_cart_quantity">
                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>"
                                   min="1" max="<?= $item['stock_count'] ?>"
                                   class="qty-input"
                                   onchange="this.form.submit()">
                        </form>
                    </td>
                    <td style="font-weight:700; color:#60a5fa;">$<?= number_format($item['total'], 2) ?></td>
                    <td>
                        <form method="POST" action="../controller/CartController.php">
                            <input type="hidden" name="action" value="remove_cart_item">
                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                            <button type="submit" class="remove-btn" title="Remove">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="display:flex; justify-content:flex-end; gap:15px; margin-bottom:20px;">
            <form method="POST" action="../controller/CartController.php">
                <input type="hidden" name="action" value="clear_cart">
                <button type="submit" class="qty-btn" style="padding:10px 20px;">
                    <i class="fa-solid fa-trash"></i> Clear Cart
                </button>
            </form>
        </div>

        <div class="cart-summary">
            <h3>Order Summary</h3>

            <div class="summary-row">
                <span>Subtotal</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Shipping</span>
                <span>$50.00</span>
            </div>

            <!-- Coupon -->
            <div class="coupon-row">
                <input type="text" id="couponInput" class="coupon-input" placeholder="Coupon code...">
                <button onclick="applyCoupon()" class="coupon-btn">Apply</button>
            </div>
            <div id="couponMsg" style="font-size:13px; color:#60a5fa; margin-bottom:10px; display:none;"></div>

            <div class="summary-row total">
                <span>Total</span>
                <span id="finalTotal">$<?= number_format($total + 50, 2) ?></span>
            </div>

            <?php if ($isLoggedIn): ?>
                <form method="POST" action="../controller/OrderController.php" id="checkoutForm">
                    <input type="hidden" name="action" value="place_order">
                    <input type="hidden" name="coupon_code" id="couponCodeField" value="">
                    <button type="submit" class="checkout-btn">
                        <i class="fa-solid fa-lock"></i> Checkout
                    </button>
                </form>
            <?php else: ?>
                <a href="login.php" class="checkout-btn" style="display:block; text-align:center; text-decoration:none; padding:14px;">
                    Login to Checkout
                </a>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

<footer>
    <p>© 2026 Auto motive Hub Platform | All Rights Reserved</p>
</footer>

<script>
function applyCoupon() {
    const code = document.getElementById('couponInput').value.trim();
    if (!code) return;

    fetch('../controller/CartController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=apply_coupon&coupon_code=' + encodeURIComponent(code)
    })
    .then(r => r.json())
    .then(data => {
        const msg = document.getElementById('couponMsg');
        msg.style.display = 'block';
        if (data.success) {
            msg.style.color = '#4ade80';
            msg.textContent = data.discount_percent + '% discount applied! Saving $' + data.discount_amount.toFixed(2);
            document.getElementById('finalTotal').textContent = '$' + data.final_total.toFixed(2);
            document.getElementById('couponCodeField').value = code;
        } else {
            msg.style.color = '#f87171';
            msg.textContent = 'Invalid or expired coupon.';
        }
    })
    .catch(() => {});
}
</script>
<script src="script.js"></script>
</body>
</html>
