<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";

// Singleton Pattern: reuse one shared database connection.
$db = Database::getInstance()->getConnection();

$isLoggedIn = isset($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JS Playground - Automotive Hub</title>
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
        .page-header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); padding: 60px 8% 40px; border-bottom: 1px solid #1e293b; }
        .page-header h1 { font-size: 42px; font-weight: 800; margin-bottom: 10px; }
        .page-header h1 span { color: #60a5fa; }
        .page-header p { color: #64748b; font-size: 16px; }

        /* Playground Section */
        .playground { padding: 60px 8%; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px; }

        .demo-card {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #334155;
            border-radius: 20px;
            padding: 30px;
            transition: all 0.3s;
        }
        .demo-card:hover { border-color: #3b82f6; box-shadow: 0 10px 30px rgba(37,99,235,0.15); }
        .demo-card h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; }
        .demo-card h3 i { color: #60a5fa; }
        .demo-card p  { color: #64748b; font-size: 13px; margin-bottom: 20px; line-height: 1.6; }

        /* Demo output boxes */
        .demo-output {
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 15px;
            font-weight: 600;
            min-height: 48px;
            margin-bottom: 16px;
            transition: all 0.4s;
            color: #e2e8f0;
        }

        /* Buttons */
        .demo-btn {
            padding: 10px 20px;
            margin: 5px 5px 0 0;
            border: 1px solid #334155;
            border-radius: 8px;
            background: transparent;
            color: white;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .demo-btn:hover { background: #2563eb; border-color: #2563eb; }
        .demo-btn.primary { background: #2563eb; border-color: #2563eb; }
        .demo-btn.danger  { border-color: #dc2626; }
        .demo-btn.danger:hover { background: #dc2626; }
        .demo-btn.success { border-color: #16a34a; }
        .demo-btn.success:hover { background: #16a34a; }

        /* Input */
        .demo-input {
            width: 100%;
            padding: 11px 14px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: white;
            font-size: 13px;
            outline: none;
            margin-bottom: 10px;
            transition: 0.3s;
        }
        .demo-input:focus { border-color: #2563eb; }
        .demo-input::placeholder { color: #475569; }

        /* Counter display */
        .counter-display {
            font-size: 48px;
            font-weight: 800;
            color: #60a5fa;
            text-align: center;
            padding: 20px 0 10px;
        }

        /* Color swatch */
        .color-swatch {
            width: 100%;
            height: 60px;
            border-radius: 10px;
            margin-bottom: 16px;
            transition: background 0.4s;
            background: #2563eb;
        }

        footer { text-align: center; padding: 40px; border-top: 1px solid #1e293b; color: #475569; }
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
    <h1>JavaScript <span>Playground</span> ⚡</h1>
    <p>Interactive JavaScript demos — click the buttons and see the magic happen</p>
</div>

<!-- Demo Cards Grid -->
<section class="playground">

    <!-- Demo 1: Text Changer -->
    <div class="demo-card">
        <h3><i class="fa-solid fa-pen"></i> Text Changer</h3>
        <p>Click the button to cycle through different greetings dynamically.</p>
        <div class="demo-output" id="demoText">Hello AutoZone 👋</div>
        <button class="demo-btn primary" onclick="changeText()">Change Text</button>
        <button class="demo-btn danger"  onclick="resetText()">Reset</button>
    </div>

    <!-- Demo 2: Color Switcher -->
    <div class="demo-card">
        <h3><i class="fa-solid fa-palette"></i> Color Switcher</h3>
        <p>Click to cycle through different background colors on the swatch.</p>
        <div class="color-swatch" id="colorSwatch"></div>
        <button class="demo-btn primary" onclick="changeColor()">Next Color</button>
        <button class="demo-btn danger"  onclick="resetColor()">Reset</button>
    </div>

    <!-- Demo 3: Counter -->
    <div class="demo-card">
        <h3><i class="fa-solid fa-hashtag"></i> Click Counter</h3>
        <p>Increment, decrement, or reset the counter using the buttons below.</p>
        <div class="counter-display" id="counter">0</div>
        <button class="demo-btn success" onclick="increment()">+ Increment</button>
        <button class="demo-btn danger"  onclick="decrement()">− Decrement</button>
        <button class="demo-btn"         onclick="resetCounter()">Reset</button>
    </div>

    <!-- Demo 4: Character Counter -->
    <div class="demo-card">
        <h3><i class="fa-solid fa-keyboard"></i> Character Counter</h3>
        <p>Type in the input below and watch the character count update live.</p>
        <input type="text" class="demo-input" id="charInput" placeholder="Start typing here..." oninput="countChars()">
        <div class="demo-output" id="charCount">Characters: 0</div>
        <button class="demo-btn danger" onclick="clearInput()">Clear</button>
    </div>

    <!-- Demo 5: Random Number -->
    <div class="demo-card">
        <h3><i class="fa-solid fa-dice"></i> Random Number</h3>
        <p>Generate a random number between 1 and 100 with each click.</p>
        <div class="demo-output" id="randomNum" style="font-size:36px; font-weight:800; color:#f1c40f; text-align:center; padding:10px 0;">—</div>
        <button class="demo-btn primary" onclick="generateRandom()">Roll the Dice 🎲</button>
    </div>

    <!-- Demo 6: Uppercase Converter -->
    <div class="demo-card">
        <h3><i class="fa-solid fa-text-height"></i> Text Converter</h3>
        <p>Type text and convert it to uppercase, lowercase, or title case.</p>
        <input type="text" class="demo-input" id="convertInput" placeholder="Enter text to convert...">
        <button class="demo-btn primary" onclick="toUpper()">UPPERCASE</button>
        <button class="demo-btn"         onclick="toLower()">lowercase</button>
        <button class="demo-btn success" onclick="toTitle()">Title Case</button>
        <div class="demo-output" id="convertOutput" style="margin-top:12px;">Result will appear here...</div>
    </div>

</section>

<footer>
    <p>© 2026 Auto motive Hub Platform | All Rights Reserved</p>
</footer>

<script src="script.js"></script>

</body>
</html>
