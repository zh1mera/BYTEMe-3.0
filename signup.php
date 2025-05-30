<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - BYTEMe</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js"></script>
</head>
<body>
    <div class="form-container">
        <h1 class="form-title">Sign Up</h1>
        <?php if (!isset($_SESSION['verification_pending'])): ?>
        <form action="signup_process.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="show-password" onclick="togglePassword('password')">👁️</button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="password-container">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="show-password" onclick="togglePassword('confirm_password')">👁️</button>
                </div>
            </div>
            <button type="submit" name="step" value="send_verification" class="button">GET VERIFICATION CODE</button>
        </form>
        <?php else: ?>
        <p>A verification code has been sent to your email. Please check your inbox and enter the code below.</p>
        <form action="signup_process.php" method="POST">
            <div class="form-group">
                <label for="verification_code">Verification Code</label>
                <input type="text" id="verification_code" name="verification_code" required>
            </div>
            <button type="submit" name="step" value="verify_code" class="button">VERIFY & COMPLETE SIGNUP</button>
        </form>
        <?php endif; ?>
        <a href="welcome.php" class="back-link">← Back to Welcome Page</a>
    </div>
</body>
</html>
