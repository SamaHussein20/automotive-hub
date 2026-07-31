<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/product.php";

$db = Database::getInstance()->getConnection();
$productModel = new Product($db);
$isLoggedIn   = isset($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Perfect Vehicle — Automotive Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #05070a;
            color: #fff;
            min-height: 100vh;
        }

        /* ── Nav ── */
        nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 16px 8%; position: sticky; top: 0; z-index: 100;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            background: rgba(5,7,10,0.96); backdrop-filter: blur(12px);
        }
        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-icon { font-size: 24px; font-weight: 800; color: #2563eb; letter-spacing: -1px; }
        .logo-text { font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .logo-text span { display: block; font-size: 11px; color: #475569; font-weight: 400; letter-spacing: .3px; }
        .nav-links { display: flex; gap: 32px; list-style: none; }
        .nav-links a { text-decoration: none; color: #94a3b8; font-size: 13.5px; font-weight: 500; transition: color .25s; letter-spacing: .2px; }
        .nav-links a:hover, .nav-links a.active { color: #fff; }
        .nav-actions { display: flex; align-items: center; gap: 20px; }
        .nav-icon-link { color: #94a3b8; font-size: 17px; text-decoration: none; transition: color .25s; }
        .nav-icon-link:hover { color: #fff; }
        .nav-text-link { text-decoration: none; color: #94a3b8; font-size: 13.5px; font-weight: 600; transition: color .25s; }
        .nav-text-link:hover { color: #fff; }
        .nav-btn {
            text-decoration: none; background: #2563eb; color: #fff;
            padding: 9px 22px; border-radius: 7px; font-size: 13.5px;
            font-weight: 600; border: none; cursor: pointer; transition: background .25s;
        }
        .nav-btn:hover { background: #1d4ed8; }

        /* ── Page Hero ── */
        .page-hero {
            text-align: center;
            padding: 72px 8% 56px;
            background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(37,99,235,0.12) 0%, transparent 70%);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            position: relative;
            overflow: hidden;
        }
        .page-hero::before {
            content: '';
            position: absolute; top: 0; left: 50%; transform: translateX(-50%);
            width: 1px; height: 72px;
            background: linear-gradient(to bottom, transparent, #2563eb, transparent);
        }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 11px; font-weight: 600; letter-spacing: 2.5px;
            text-transform: uppercase; color: #2563eb;
            margin-bottom: 20px;
        }
        .hero-eyebrow::before, .hero-eyebrow::after {
            content: ''; display: block; width: 24px; height: 1px; background: #2563eb; opacity: .5;
        }
        .page-hero h1 { font-size: 46px; font-weight: 800; letter-spacing: -1.5px; line-height: 1.1; margin-bottom: 14px; }
        .page-hero h1 span { color: #60a5fa; }
        .page-hero p { color: #64748b; font-size: 15px; font-weight: 400; letter-spacing: .2px; }

        /* ── Wizard Wrapper ── */
        .wizard-wrapper {
            display: flex; justify-content: center; align-items: flex-start;
            padding: 64px 8% 80px;
        }

        /* ── Wizard Card ── */
        .wizard-card {
            width: 100%; max-width: 500px;
            background: linear-gradient(170deg, #111827 0%, #0a0f1a 100%);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 48px 44px 40px;
            box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(37,99,235,0.06) inset;
        }

        /* ── Step Progress Bar ── */
        .step-progress {
            display: flex; align-items: center; justify-content: center;
            gap: 0; margin-bottom: 44px;
        }
        .sp-item { display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .sp-circle {
            width: 34px; height: 34px; border-radius: 50%;
            border: 1.5px solid #1e293b;
            background: #0d1117;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #475569;
            transition: all .35s ease;
            position: relative; z-index: 1;
        }
        .sp-circle.active {
            border-color: #2563eb;
            background: #2563eb;
            color: #fff;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.18);
        }
        .sp-circle.done {
            border-color: #16a34a;
            background: #16a34a;
            color: #fff;
        }
        .sp-circle.done::after { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: 11px; }
        .sp-circle.done .sp-num { display: none; }
        .sp-label { font-size: 10.5px; font-weight: 600; letter-spacing: .8px; text-transform: uppercase; color: #334155; transition: color .35s; }
        .sp-label.active { color: #60a5fa; }
        .sp-label.done { color: #4ade80; }
        .sp-line {
            width: 60px; height: 1px;
            background: #1e293b;
            margin: 0 4px;
            margin-bottom: 20px;
            transition: background .35s;
        }
        .sp-line.done { background: #16a34a; }

        /* ── Step Sections ── */
        .step { display: none; }
        .step.active { display: block; }

        .step-header { text-align: center; margin-bottom: 32px; }
        .step-header h2 { font-size: 22px; font-weight: 800; letter-spacing: -.5px; margin-bottom: 6px; }
        .step-header p { color: #475569; font-size: 13px; font-weight: 400; }

        /* ── Type Grid ── */
        .type-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }

        .type-btn {
            padding: 22px 10px 18px;
            border: 1px solid #1e293b;
            border-radius: 12px;
            background: rgba(255,255,255,0.02);
            color: #94a3b8;
            cursor: pointer;
            transition: all .25s;
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            font-size: 12px; font-weight: 600; letter-spacing: .3px;
        }
        .type-btn i { font-size: 20px; color: #334155; transition: color .25s; }
        .type-btn:hover {
            border-color: #2563eb;
            background: rgba(37,99,235,0.1);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37,99,235,0.2);
        }
        .type-btn:hover i { color: #60a5fa; }
        .type-btn:active { transform: translateY(0); }

        /* ── Budget Select ── */
        .field-label {
            font-size: 11px; font-weight: 600; letter-spacing: 1.2px;
            text-transform: uppercase; color: #475569;
            margin-bottom: 10px; display: block;
        }
        .budget-select {
            width: 100%; padding: 13px 16px;
            border-radius: 10px; border: 1px solid #1e293b;
            background: rgba(255,255,255,0.03); color: #fff;
            font-size: 14px; font-family: inherit; font-weight: 500;
            outline: none; cursor: pointer;
            transition: border-color .25s;
            margin-bottom: 24px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 16px center;
        }
        .budget-select:focus { border-color: #2563eb; }
        .budget-select option { background: #0f172a; }

        /* ── Submit Button ── */
        .submit-btn {
            width: 100%; padding: 14px;
            background: #2563eb; color: #fff;
            border: none; border-radius: 10px;
            font-size: 14px; font-weight: 700; font-family: inherit;
            letter-spacing: .3px; cursor: pointer;
            transition: all .25s;
            display: flex; align-items: center; justify-content: center; gap: 9px;
        }
        .submit-btn:hover { background: #1d4ed8; transform: translateY(-2px); box-shadow: 0 10px 28px rgba(37,99,235,0.38); }
        .submit-btn:active { transform: translateY(0); }

        /* ── Back Link ── */
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 18px; color: #334155; font-size: 12.5px;
            font-weight: 500; cursor: pointer; transition: color .25s;
        }
        .back-link:hover { color: #64748b; }

        /* ── Divider ── */
        .step-divider { height: 1px; background: rgba(255,255,255,0.05); margin: 28px 0; }

        /* ── Footer ── */
        footer {
            text-align: center; padding: 32px 8%;
            border-top: 1px solid rgba(255,255,255,0.05);
            color: #1e293b; font-size: 13px; letter-spacing: .2px;
        }

        @media (max-width: 520px) {
            .wizard-card { padding: 36px 24px 32px; }
            .page-hero h1 { font-size: 32px; }
            .sp-line { width: 36px; }
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
        <li><a href="index.php">Home</a></li>
        <li><a href="cars.php">Cars</a></li>
        <li><a href="motorcycles.php">Motorcycles</a></li>
        <li><a href="electric-vehicles.php">Electric</a></li>
        <li><a href="spare-parts.php">Spare Parts</a></li>
    </ul>
    <div class="nav-actions">
        <a href="cart.php" class="nav-icon-link"><i class="fa-solid fa-cart-shopping"></i></a>
        <?php if ($isLoggedIn): ?>
            <a href="wishlist.php" class="nav-text-link">Wishlist</a>
            <form method="POST" action="../Controller/AuthController.php" style="display:inline;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="nav-btn">Logout</button>
            </form>
        <?php else: ?>
            <a href="login.php" class="nav-text-link">Login</a>
            <a href="register.php" class="nav-btn">Sign Up</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Hero -->
<div class="page-hero">
    <div class="hero-eyebrow"><span>Vehicle Finder</span></div>
    <h1>Find Your <span>Perfect Vehicle</span></h1>
    <p>Answer three quick questions and we'll match you with the best options</p>
</div>

<!-- Wizard -->
<div class="wizard-wrapper">
    <div class="wizard-card">

        <!-- Step Progress -->
        <div class="step-progress">
            <div class="sp-item">
                <div class="sp-circle active" id="dot1"><span class="sp-num">1</span></div>
                <div class="sp-label active" id="lbl1">Category</div>
            </div>
            <div class="sp-line" id="line1"></div>
            <div class="sp-item">
                <div class="sp-circle" id="dot2"><span class="sp-num">2</span></div>
                <div class="sp-label" id="lbl2">Type</div>
            </div>
            <div class="sp-line" id="line2"></div>
            <div class="sp-item">
                <div class="sp-circle" id="dot3"><span class="sp-num">3</span></div>
                <div class="sp-label" id="lbl3">Budget</div>
            </div>
        </div>

        <!-- Step 1: Category -->
        <div class="step active" id="step1">
            <div class="step-header">
                <h2>What Are You Looking For?</h2>
                <p>Choose the vehicle category that interests you</p>
            </div>
            <div class="type-grid">
                <button class="type-btn" onclick="selectType('Cars')">
                    <i class="fa-solid fa-car"></i>Cars
                </button>
                <button class="type-btn" onclick="selectType('Motorcycles')">
                    <i class="fa-solid fa-motorcycle"></i>Motorcycles
                </button>
                <button class="type-btn" onclick="selectType('Electric')">
                    <i class="fa-solid fa-bolt"></i>Electric
                </button>
                <button class="type-btn" onclick="selectType('Spare Parts')" style="grid-column:span 3;">
                    <i class="fa-solid fa-screwdriver-wrench"></i>Spare Parts
                </button>
            </div>
        </div>

        <!-- Step 2: Sub-type -->
        <div class="step" id="step2">
            <div class="step-header">
                <h2>Select Vehicle Style</h2>
                <p>Pick your preferred type within this category</p>
            </div>
            <div class="type-grid" id="subTypeGrid"></div>
            <div class="step-divider"></div>
            <span class="back-link" onclick="goBack(1)">
                <i class="fa-solid fa-arrow-left" style="font-size:10px;"></i> Back to categories
            </span>
        </div>

        <!-- Step 3: Budget -->
        <div class="step" id="step3">
            <div class="step-header">
                <h2>Set Your Budget</h2>
                <p>Select the price range that works for you</p>
            </div>
            <form method="GET" action="results.php">
                <input type="hidden" name="type"    id="typeInput">
                <input type="hidden" name="subtype" id="subTypeFinal">

                <label class="field-label">Price Range</label>
                <select name="budget" class="budget-select" required>
                    <option value="">Select a range</option>
                    <option value="low">Under $30,000</option>
                    <option value="mid">$30,000 — $100,000</option>
                    <option value="high">Above $100,000</option>
                </select>

                <button type="submit" class="submit-btn">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:13px;"></i>
                    Show My Matches
                </button>
            </form>
            <div style="text-align:center;">
                <span class="back-link" onclick="goBack(2)">
                    <i class="fa-solid fa-arrow-left" style="font-size:10px;"></i> Back to vehicle type
                </span>
            </div>
        </div>

    </div>
</div>

<footer>
    <p>© 2026 Auto motive Hub Platform &nbsp;|&nbsp; All Rights Reserved</p>
</footer>

<script>
let selectedType    = "";
let selectedSubType = "";

const vehicleTypes = {
    Cars:         ["Sport", "Luxury", "SUV", "Sedan"],
    Motorcycles:  ["Sport", "Cruiser", "Adventure", "Electric"],
    Electric:     ["Sedan", "SUV", "Sport", "Truck"],
    "Spare Parts":["Engine", "Brakes", "Suspension", "Electrical"]
};

const typeIcons = {
    Cars:         "fa-car",
    Motorcycles:  "fa-motorcycle",
    Electric:     "fa-bolt",
    "Spare Parts":"fa-screwdriver-wrench",
    Sport:        "fa-gauge-high",
    Luxury:       "fa-gem",
    SUV:          "fa-truck-monster",
    Sedan:        "fa-car-side",
    Cruiser:      "fa-road",
    Adventure:    "fa-mountain",
    Electric:     "fa-bolt",
    Truck:        "fa-truck",
    Engine:       "fa-cog",
    Brakes:       "fa-circle-dot",
    Suspension:   "fa-arrows-up-down",
    Electrical:   "fa-plug"
};

function goToStep(to) {
    document.querySelectorAll(".step").forEach(s => s.classList.remove("active"));
    document.getElementById("step" + to).classList.add("active");
}

function setDot(id, state) {
    const d = document.getElementById("dot" + id);
    const l = document.getElementById("lbl" + id);
    d.classList.remove("active","done");
    l.classList.remove("active","done");
    if (state) { d.classList.add(state); l.classList.add(state); }
}

function setLine(id, done) {
    const el = document.getElementById("line" + id);
    if (done) el.classList.add("done");
    else el.classList.remove("done");
}

function selectType(type) {
    selectedType = type;
    document.getElementById("typeInput").value = type;

    const grid = document.getElementById("subTypeGrid");
    grid.innerHTML = "";
    vehicleTypes[type].forEach(item => {
        const icon = typeIcons[item] || "fa-circle";
        grid.innerHTML += `
            <button type="button" class="type-btn" onclick="selectSubType('${item}')">
                <i class="fa-solid ${icon}"></i>${item}
            </button>`;
    });

    goToStep(2);
    setDot(1,"done"); setLine(1,true);
    setDot(2,"active");
}

function selectSubType(sub) {
    selectedSubType = sub;
    document.getElementById("subTypeFinal").value = sub;

    goToStep(3);
    setDot(2,"done"); setLine(2,true);
    setDot(3,"active");
}

function goBack(to) {
    if (to === 1) {
        goToStep(1);
        setDot(1,"active"); setDot(2,""); setLine(1,false);
    } else {
        goToStep(2);
        setDot(2,"active"); setDot(3,""); setLine(2,false);
    }
}
</script>
</body>
</html>