<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../../login.php");
    exit();
}

require_once '../../../../db/db_connect.php';
require_once '../../../../db/progress_functions.php';

// Get user's current level for Python
$currentLevel = getCurrentLevel($_SESSION['user_id'], 'python');

// Define the levels and their challenges
$levels = [
    1 => [
        'title' => 'Hello World',
        'description' => 'Print "Hello, World!" to the console.',
        'difficulty' => 'Beginner'
    ],
    2 => [
        'title' => 'Variables and Data Types',
        'description' => 'Create variables of different data types and perform basic operations.',
        'difficulty' => 'Beginner'
    ],
    3 => [
        'title' => 'Control Flow',
        'description' => 'Work with if statements and loops to control program flow.',
        'difficulty' => 'Beginner'
    ],
    4 => [
        'title' => 'Functions',
        'description' => 'Create and use functions to organize your code.',
        'difficulty' => 'Intermediate'
    ],
    5 => [
        'title' => 'Lists and Arrays',
        'description' => 'Work with lists and perform array operations.',
        'difficulty' => 'Intermediate'
    ],
    6 => [
        'title' => 'Dictionaries',
        'description' => 'Use dictionaries to store and manipulate key-value pairs.',
        'difficulty' => 'Intermediate'
    ],
    7 => [
        'title' => 'File Handling',
        'description' => 'Read from and write to files using Python.',
        'difficulty' => 'Advanced'
    ],
    8 => [
        'title' => 'Exception Handling',
        'description' => 'Learn to handle errors and exceptions in Python.',
        'difficulty' => 'Advanced'
    ],
    9 => [
        'title' => 'Object-Oriented Programming',
        'description' => 'Create classes and objects in Python.',
        'difficulty' => 'Advanced'
    ],
    10 => [
        'title' => 'Advanced Python Features',
        'description' => 'Work with decorators, generators, and advanced Python concepts.',
        'difficulty' => 'Expert'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Python Learning Path - BYTEMe</title>
    <link rel="stylesheet" href="../../../../assets/css/style.css">
    <link rel="stylesheet" href="../../../css/home.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="main-nav">
        <button class="nav-logo" onclick="window.location.href='../../../index.php'">
            BYTEMe
        </button>
        <div class="nav-links">
            <a href="../../../challenges/index.php">Daily Challenges</a>
            <a href="../../index.php" class="active">Languages</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="../../../../admin.php">Admin</a>
            <?php endif; ?>
        </div>        <div class="nav-profile">
            <a href="../../../profile.php" class="nav-btn">Profile</a>
            <a href="../../../../logout.php" class="nav-btn logout">Logout</a>
        </div>
    </nav>    <main class="python-container">
        <button onclick="window.location.href='../../index.php'" style="position: absolute; top: 100px; left: 20px; padding: 8px 15px; border: none; border-radius: 5px; background: #f0f0f0; cursor: pointer; display: flex; align-items: center; gap: 5px;">
            <span style="font-size: 20px;">&larr;</span> Back to Languages
        </button>        <h1 style="margin-top: 640px;">Python Learning Path</h1>
        <p class="intro-text">Master Python through progressive challenges</p>
        <div class="levels-grid">            <?php foreach ($levels as $level => $info): ?>
            <div class="level-card unlocked" data-level="<?php echo $level; ?>">
                <div class="level-number">
                    <h2><?php echo $level; ?></h2>
                </div>
                <button onclick="window.location.href='lvlsquiz.php?level=<?php echo $level; ?>'" class="start-level">
                    Start
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer class="site-footer">
        <p>BYTEMe</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
