<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db/db_connect.php';
require_once '../db/progress_functions.php';

// Initialize arrays to avoid undefined errors
$progress = [
    'level_progress' => [],
    'language' => []
];

// Fetch user data and progress
try {
    $stmt = $pdo->prepare("SELECT username, email, difficulty_level, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Get user progress data
    $progressData = getUserProgress($_SESSION['user_id']);
    
    // Initialize counters
    $totalChallenges = 0;
    $languagesExplored = [];

    // Calculate stats from progress data
    if (!empty($progressData['language'])) {
        foreach ($progressData['language'] as $lang) {
            $totalChallenges += $lang['correct_attempts'];
            $languagesExplored[] = $lang['language'];
        }
    }

    $languagesCount = count(array_unique($languagesExplored));
    $currentStreak = $progressData['streak'] ?? 0;

    // Define difficulty level descriptions
    $difficultyDescriptions = [
        'beginner' => 'Perfect for those just starting their coding journey.',
        'intermediate' => 'For developers with basic programming knowledge.',
        'professional' => 'For experienced developers seeking advanced challenges.'
    ];

    // Define available programming languages
    $languages = [
        'Python' => [
            'description' => 'A versatile, beginner-friendly language perfect for automation and data science.',
            'color' => '#3572A5'
        ],
        'JavaScript' => [
            'description' => 'The language of the web, essential for front-end and back-end development.',
            'color' => '#F7DF1E'
        ],
        'Java' => [
            'description' => 'A powerful, object-oriented language used in enterprise and Android development.',
            'color' => '#B07219'
        ],
        'PHP' => [
            'description' => 'A popular server-side scripting language, widely used in web development.',
            'color' => '#4F5D95'
        ]
    ];
} catch(PDOException $e) {
    error_log("Profile page error: " . $e->getMessage());
    $error = "An error occurred while loading your profile.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - BYTEMe</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="css/profile.css">
    <script src="../assets/js/main.js"></script>
    <script src="js/home.js" defer></script>
</head>
<body>
    <nav class="main-nav">
        <button class="nav-logo" onclick="window.location.href='index.php'">BYTEMe</button>
        <div class="nav-links">
            <a href="challenges/index.php">Daily Challenges</a>
            <a href="language/index.php">Languages</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="../admin.php">Admin</a>
            <?php endif; ?>
        </div>
        <div class="nav-profile">
            <a href="profile.php" class="nav-btn active">Profile</a>
            <a href="../logout.php" class="nav-btn logout">Logout</a>
        </div>
    </nav>    <script src="js/profile.js"></script>
    <main class="profile-container">
        <?php if (isset($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <section class="profile-section user-info">
            <div class="info-card">
                <h2 style="margin-top: 1300px;">Account Information</h2>
                <div class="user-details">
                    <h1 class="username"><?php echo htmlspecialchars($user['username'] ?? ''); ?></h1>
                    <p class="email"><i class="email-icon"></i><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    <div class="level-info">
                        <span class="level-badge"><?php echo ucfirst(htmlspecialchars($user['difficulty_level'] ?? 'beginner')); ?></span>
                        <p class="level-description"><?php echo htmlspecialchars($difficultyDescriptions[$user['difficulty_level'] ?? 'beginner'] ?? ''); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <section class="profile-section languages">
            <h2>Language Proficiency</h2>
            <div class="languages-grid">
                <?php foreach ($languages as $name => $info):                    // Initialize variables for this language
                    $progress = 0;
                    $attempts = 0;
                    $correct = 0;
                    $level_completed = 0;
                    $proficiency_level = 'beginner';
                    $last_attempt = null;
                    $min_attempts = null;
                    $success_rate = 0;
                    
                    error_log("Processing language: " . $name);
                      // Get progress from user_progress table
                    if (!empty($progressData['level_progress'])) {
                        foreach ($progressData['level_progress'] as $levelProgress) {
                            if (strtolower($levelProgress['language_name']) === strtolower($name)) {
                                $level_completed = $levelProgress['level_completed'];
                                $proficiency_level = $levelProgress['proficiency_level'];
                                $progress = $levelProgress['progress_percentage'];
                                error_log("Found level progress for {$name}: Level {$level_completed}, Progress {$progress}%");
                                break;
                            }
                        }
                    }
                    
                    // If no progress found in level_progress, check language array
                    if ($progress === 0 && !empty($progressData['language'])) {                        foreach ($progressData['language'] as $lang) {
                            if (strtolower($lang['language']) === strtolower($name)) {
                                $attempts = $lang['total_attempts'];
                                $correct = $lang['correct_attempts'];
                                $success_rate = $attempts > 0 ? number_format(($correct / $attempts) * 100, 2) : 0;
                                if ($attempts > 0) {
                                    $level_completed = floor($correct / 3) + 1;
                                    $progress = number_format(min(($level_completed / 9) * 100, 100), 2);
                                }
                                break;
                            }
                        }
                    }
                      // Get attempt statistics
                    if (!empty($progressData['language'])) {
                        foreach ($progressData['language'] as $lang) {
                            if (strtolower($lang['language']) === strtolower($name)) {
                                $attempts = $lang['total_attempts'];
                                $correct = $lang['correct_attempts'];
                                // Calculate success rate
                                $success_rate = $attempts > 0 ? number_format(($correct / $attempts) * 100, 2) : 0;
                                $last_attempt = $lang['last_attempt'] ?? null;
                                $min_attempts = $lang['min_attempts_to_success'] ?? null;
                                error_log("Found attempt stats for {$name}: {$correct}/{$attempts} attempts, {$success_rate}% success rate");
                                break;
                            }
                        }
                    }
                ?>                <div class="language-card" data-language="<?php echo htmlspecialchars($name); ?>">
                    <div class="language-header" style="background-color: <?php echo htmlspecialchars($info['color']); ?>">
                        <h3><?php echo htmlspecialchars($name); ?></h3>
                        <span class="progress-text"><?php echo $progress; ?>%</span>
                    </div>
                    <div class="language-info">
                        <p><?php echo htmlspecialchars($info['description']); ?></p>
                        <div class="progress-stats">                            <span class="stat" data-stat="completed">✓ Completed: <?php echo $correct; ?></span>
                            <span class="stat" data-stat="attempts">🎯 Attempts: <?php echo $attempts; ?></span>
                            <span class="stat" data-stat="success-rate">⚡ Success Rate: <?php echo number_format($success_rate, 2); ?>%</span>
                            <span class="stat" data-stat="level">📊 Level: <?php echo $level_completed; ?>/9</span>
                            <span class="proficiency-badge <?php echo $proficiency_level; ?>">
                                <?php echo ucfirst($proficiency_level); ?>
                            </span>
                            <?php if ($min_attempts): ?>
                            <span class="stat">🏆 Best Run: <?php echo $min_attempts; ?> attempts</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <?php if ($last_attempt): ?>
                        <div class="last-attempt">
                            Last attempt: <?php echo date('M j, Y', strtotime($last_attempt)); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="profile-section">
            <h2>Your Progress</h2>
            <div class="progress-stats">
                <div class="stat-card">
                    <h3>Challenges Completed</h3>
                    <p class="stat-number"><?php echo $totalChallenges; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Languages Explored</h3>
                    <p class="stat-number"><?php echo $languagesCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Current Streak</h3>
                    <p class="stat-number"><?php echo $currentStreak; ?> days</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p>BYTEMe</p>
    </footer>
</body>
</html>
