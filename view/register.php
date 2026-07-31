<?php

session_start();

$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Automotive Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="login.css">
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
        </ul>
        <div class="nav-right">
            <a href="login.php" class="login-text">Login</a>
            <a href="register.php" class="signup-btn">Sign Up</a>
        </div>
    </nav>

    <div class="login-container">
        <div class="login-box">
            <div class="header">
                <h2><a href="login.php" class="signup-link" style="text-decoration:none;color:#888;">Login</a> / Sign Up</h2>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background:rgba(239,68,68,0.15); border:1px solid #ef4444; border-radius:8px;
                            padding:12px; margin-bottom:16px; color:#ef4444; font-size:14px; text-align:center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="../controller/AuthController.php">
                <input type="hidden" name="action" value="register">
                <input type="text"     name="first_name"        placeholder="First Name" required>
                <input type="text"     name="last_name"         placeholder="Last Name" required>
                <input type="email"    name="email"             placeholder="Email Address" required>
                <input type="tel"      name="phone"             placeholder="Phone Number">
                <input type="text"     name="delivery_address"  placeholder="Delivery Address">
                <input type="password" name="password"          placeholder="Password" required>
                <input type="password" name="confirm_password"  placeholder="Confirm Password" required>
                <button type="submit" class="login-btn">Create Account</button>
            </form>

            <div class="divider"><span>OR</span></div>
            <p class="footer-text">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

</body>
</html>
