<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BYTEMe</title>    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js"></script>
</head>
<body>
    <?php
    session_start();
    ?>    <div class="welcome-text">WELCOME</div>
    
    <div class="button-container">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="signup.php" class="button">SIGN UP</a>
            <a href="login.php" class="button">LOG IN</a>
        <?php else: ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="button">ADMIN DASHBOARD</a>
            <?php endif; ?>
            <a href="logout.php" class="button">LOG OUT</a>
        <?php endif; ?>
    </div>
</body>
</html>