<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Difficulty - BYTEMe</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js"></script>
</head>
<body>    <div class="difficulty-container">
        <h1 class="difficulty-title">How hard do you BYTE?</h1>
        <div class="difficulty-options">
            <button onclick="selectDifficulty('beginner')" class="difficulty-button">Beginner</button>
            <button onclick="selectDifficulty('intermediate')" class="difficulty-button">Intermediate</button>
            <button onclick="selectDifficulty('professional')" class="difficulty-button">Professional</button>
        </div>
    </div>

    <div id="difficultyModal" class="modal">
        <div class="modal-content">            <p class="modal-message"></p>
            <a href="home/index.php" class="return-button">Continue to Homepage</a>
        </div>
    </div>
</body>
</html>
