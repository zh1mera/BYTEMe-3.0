<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../db/db_connect.php';

// Fetch user's difficulty level
try {
    $stmt = $pdo->prepare("SELECT difficulty_level FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Define available programming languages
$languages = [
    [
        'name' => 'Python',
        'description' => 'A versatile, beginner-friendly language perfect for automation and data science.',
        'icon' => 'python-icon.png'
    ],
    [
        'name' => 'JavaScript',
        'description' => 'The language of the web, essential for front-end and back-end development.',
        'icon' => 'javascript-icon.png'
    ],
    [
        'name' => 'Java',
        'description' => 'A powerful, object-oriented language used in enterprise and Android development.',
        'icon' => 'java-icon.png'
    ],
    [
        'name' => 'PHP',
        'description' => 'A popular server-side scripting language, widely used in web development.',
        'icon' => 'php-icon.png'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programming Languages - BYTEMe</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../css/home.css">    <link rel="stylesheet" href="css/languages.css">
    <script src="../../assets/js/main.js"></script>
    <script src="js/languages.js" defer></script>
</head>
<body>    <nav class="main-nav">
        <button class="nav-logo" onclick="window.location.href='../index.php'">
            BYTEMe
        </button>
        <div class="nav-links">
            <a href="../challenges/index.php">Daily Challenges</a>
            <a href="index.php" class="active">Languages</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="../../admin.php">Admin</a>
            <?php endif; ?>
        </div>        <div class="nav-profile">
            <a href="../profile.php" class="nav-btn">Profile</a>
            <a href="../../logout.php" class="nav-btn logout">Logout</a>
        </div>
    </nav>

    <main class="languages-container">
        <h1 style="margin-top: 420px; text-align: center;">Programming Languages</h1>
        <p class="intro-text">Choose a programming language to start learning</p>
        
        <div class="language-grid">
            <?php foreach ($languages as $lang): ?>
            <div class="language-card" data-language="<?php echo strtolower($lang['name']); ?>">
                <div class="language-icon">
                    <img src="images/<?php echo $lang['icon']; ?>" alt="<?php echo $lang['name']; ?> icon">
                </div>
                <h2><?php echo $lang['name']; ?></h2>
                <p><?php echo $lang['description']; ?></p>                <button onclick="window.location.href='langpages/' + '<?php echo strtolower($lang['name']); ?>' + '/index.php'" class="start-learning">Start Learning</button>
            </div>            <?php endforeach; ?>
        </div>
    </main>

    <footer class="site-footer">
        <p>BYTEMe</p>
    </footer>
</body>
</html>
