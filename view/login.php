<?php

session_start();

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

// Get selected role from URL param (after clicking User or Admin card)
$selectedRole = $_GET['role'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Automotive Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <style>
        /* Role selection cards */
        .role-selection {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 10px 0 20px;
        }

        .role-card {
            flex: 1;
            padding: 22px 10px;
            border: 2px solid #333;
            border-radius: 12px;
            background: rgba(255,255,255,0.04);
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            color: #ccc;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .role-card:hover {
            border-color: #c0a060;
            background: rgba(192,160,96,0.08);
            color: #fff;
            transform: translateY(-2px);
        }

        .role-card.active {
            border-color: #c0a060;
            background: rgba(192,160,96,0.12);
            color: #fff;
        }

        .role-card i {
            font-size: 28px;
            color: #c0a060;
        }

        .role-card span {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Back link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #888;
            font-size: 13px;
            cursor: pointer;
            margin-bottom: 18px;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: #c0a060; }

        /* Step label */
        .step-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(192,160,96,0.15);
            border: 1px solid #c0a060;
            color: #c0a060;
            border-radius: 6px;
            padding: 4px 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-brand">
            <div class="logo">AUTO MOTIVE</div>
            <div class="sub-logo">HUB PLATFORM</div>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="cars.php">Cars</a></li>
            <li><a href="motorcycles.php">Motorcycles</a></li>
            <li><a href="electric-vehicles.php">Electric</a></li>
            <li><a href="spare-parts.php">Spare Parts</a></li>
        </ul>
        <div class="nav-right">
            <a href="login.php" class="login-text">Login</a>
            <a href="register.php" class="signup-btn">Sign Up</a>
        </div>
    </nav>

    <div class="login-container">
        <div class="login-box">
            <div class="header">
                <h2>Login / <a href="register.php" class="signup-link">Sign Up</a></h2>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background:rgba(239,68,68,0.15); border:1px solid #ef4444; border-radius:8px;
                            padding:12px; margin-bottom:16px; color:#ef4444; font-size:14px; text-align:center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($selectedRole)): ?>
                <!-- STEP 1: Choose role -->
                <p class="step-label">Step 1 — Who are you?</p>
                <div class="role-selection">
                    <a href="login.php?role=user" class="role-card">
                        <i class="fas fa-user"></i>
                        <span>User</span>
                    </a>
                    <a href="login.php?role=admin" class="role-card">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin</span>
                    </a>
                </div>

            <?php else: ?>
                <!-- STEP 2: Enter credentials -->
                <a href="login.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back
                </a>

                <p class="step-label">Step 2 — Sign in as</p>
                <div style="margin-bottom:18px;">
                    <span class="role-badge">
                        <i class="fas <?= $selectedRole === 'admin' ? 'fa-user-shield' : 'fa-user' ?>"></i>
                        <?= $selectedRole === 'admin' ? 'Admin' : 'User' ?>
                    </span>
                </div>

                <form method="POST" action="../Controller/AuthController.php">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="role_hint" value="<?= htmlspecialchars($selectedRole) ?>">
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" class="login-btn">
                        Login as <?= $selectedRole === 'admin' ? 'Admin' : 'User' ?>
                    </button>
                </form>

                <div class="divider"><span>OR</span></div>
                <p class="footer-text">Don't have an account? <a href="register.php">Sign Up</a></p>

            <?php endif; ?>
        </div>
    </div>

</body>
</html>