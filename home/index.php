<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../db/db_connect.php';

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = '';
}

// Fetch user data including difficulty level
try {
    $stmt = $pdo->prepare("SELECT username, difficulty_level FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BYTEMe - Home</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/home.css">
    <script src="js/home.js" defer></script>
</head>
<body>
    <nav class="main-nav">
        <button class="nav-logo" onclick="window.location.href='index.php'">BYTEMe</button>
        <div class="nav-links">
            <a href="challenges/index.php">Daily Challenges</a>
            <a href="language/index.php">Languages</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="../admin.php">Admin</a>
            <?php endif; ?>
        </div>        <div class="nav-profile">
            <a href="profile.php" class="nav-btn">Profile</a>
            <a href="../logout.php" class="nav-btn logout">Logout</a>
        </div>
    </nav>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="../admin.php" class="menu-item">Admin Dashboard</a>
            <?php endif; ?>
            <div class="menu-divider"></div>
            <a href="../logout.php" class="menu-item logout">Logout</a>
        </div>
    </div>    <main class="home-container">
        <section class="welcome-section">
            <h1 style="margin-top: 2200px;">Welcome to Your Coding Journey</h1>
            <p>Level: <strong><?php echo ucfirst(htmlspecialchars($user['difficulty_level'])); ?></strong></p>
        </section>        <section class="quick-actions">
            <div class="action-card progress-card">
                <div class="progress-form">                <div class="progress-user-info">
                    <div class="welcome">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</div>
                    <div class="difficulty">Difficulty Level: <?php echo ucfirst(htmlspecialchars($user['difficulty_level'])); ?></div>
                    <div class="description">Track your progress as you explore different programming languages and complete daily challenges. Start your coding journey today!</div>
                </div>                    <div class="progress-display">
                        <?php
                        require_once '../db/progress_functions.php';
                        $progress = getUserProgress($_SESSION['user_id']);
                        if (empty($progress['language'])) {
                            echo '<div class="no-progress">
                                    <p>No Progress yet :(</p>
                                    <p>Start with a language to track your progress!</p>
                                </div>';
                        } else {
                        ?>
                            <div class="charts-container">
                                <div class="chart-section">
                                    <h4>Language Progress</h4>
                                    <canvas id="languageChart"></canvas>
                                </div>
                                <div class="chart-section">
                                    <h4>Difficulty Progress</h4>
                                    <canvas id="difficultyChart"></canvas>
                                </div>
                            </div>
                            <div class="recent-activity">
                                <h4>Recent Activity</h4>
                                <?php foreach (array_slice($progress['recent'], 0, 3) as $activity): ?>
                                <div class="activity-item <?php echo $activity['is_correct'] ? 'correct' : 'incorrect'; ?>">
                                    <span class="language"><?php echo ucfirst(htmlspecialchars($activity['language'])); ?></span>
                                    <span class="difficulty"><?php echo ucfirst(htmlspecialchars($activity['difficulty'])); ?></span>
                                    <span class="result"><?php echo $activity['is_correct'] ? '✓' : '✗'; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                            <script>
                            const languageData = <?php echo json_encode($progress['language']); ?>;
                            const difficultyData = <?php echo json_encode($progress['difficulty']); ?>;

                            new Chart(document.getElementById('languageChart'), {
                                type: 'bar',
                                data: {
                                    labels: languageData.map(d => d.language),
                                    datasets: [{
                                        label: 'Success Rate (%)',
                                        data: languageData.map(d => d.success_rate),
                                        backgroundColor: ['#3572A5', '#F7DF1E', '#B07219']
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: { y: { beginAtZero: true, max: 100 } }
                                }
                            });

                            new Chart(document.getElementById('difficultyChart'), {
                                type: 'bar',
                                data: {
                                    labels: difficultyData.map(d => d.difficulty),
                                    datasets: [{
                                        label: 'Success Rate (%)',
                                        data: difficultyData.map(d => d.success_rate),
                                        backgroundColor: ['#4CAF50', '#FFC107', '#F44336']
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: { y: { beginAtZero: true, max: 100 } }
                                }
                            });
                            </script>
                        <?php } ?>
                    </div>
                    <a href="profile.php" class="button">View Profile</a>
                </div>
            </div>            <div class="action-card">
                <h3>Daily Challenge</h3>
                <p>Take on today's coding challenge!</p>
                <a href="challenges/index.php" class="button">Start Challenge</a>
            </div>

            <div class="action-card">
                <h3>Programming Languages</h3>
                <p>Explore different programming languages</p>
                <a href="language/index.php" class="button">Explore Languages</a>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <p>BYTEMe</p>
    </footer>
</body>
</html>
