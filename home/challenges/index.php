<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Get difficulty level from session or use default
$difficulty = isset($_SESSION['difficulty']) ? $_SESSION['difficulty'] : 'beginner';

// Check if we already have a puzzle in session
$currentQuestion = isset($_SESSION['current_question']) ? $_SESSION['current_question'] : null;
$currentAnswer = isset($_SESSION['current_answer']) ? $_SESSION['current_answer'] : '';
$currentLanguage = isset($_SESSION['current_language']) ? $_SESSION['current_language'] : null;
$currentDifficulty = isset($_SESSION['current_difficulty']) ? $_SESSION['current_difficulty'] : null;

require_once '../../db/db_connect.php';

// Available programming languages and their badge colors
$languageColors = [
    'python' => '#3572A5',
    'javascript' => '#F7DF1E',
    'java' => '#B07219',
    'php' => '#4F5D95'
];

// Function to get random puzzle from file
function getRandomPuzzle($difficulty) {    // Get a random language
    $availableLanguages = ['python', 'javascript', 'java', 'php'];
    $language = $availableLanguages[array_rand($availableLanguages)];
    
    $filePath = "../../puzzles/" . strtolower($difficulty) . "/" . strtolower($language) . ".txt";
    if (!file_exists($filePath)) {
        return null;
    }
    
    $content = file_get_contents($filePath);
    $puzzles = [];
    $currentPuzzle = [];
    
    foreach(explode("\n", $content) as $line) {
        $line = trim($line);
        
        // Skip empty lines and file path comments
        if (empty($line) || strpos($line, '// filepath:') === 0) {
            continue;
        }

        // Match both "Level X:" and "X." formats
        if (preg_match('/^(Level \d+:|\d+\.)/', $line)) {
            if (!empty($currentPuzzle)) {
                $puzzles[] = $currentPuzzle;
            }
            $currentPuzzle = ['level' => preg_replace('/[^0-9]/', '', $line)];
            
            // Handle formats where question is on the same line (like professional/intermediate)
            $question = preg_replace('/^(Level \d+:|\d+\.)\s*/', '', $line);
            if ($question) {
                $currentPuzzle['question'] = $question;
            }
        } elseif (strpos($line, 'Q:') === 0) {
            $currentPuzzle['question'] = trim(substr($line, 2));
        } elseif (strpos($line, 'A:') === 0 || strpos($line, 'Answer:') === 0) {
            $currentPuzzle['answer'] = trim(strpos($line, 'A:') === 0 ? substr($line, 2) : substr($line, 7));
        } elseif (!isset($currentPuzzle['question']) && !empty($line)) {
            // If no Q: prefix and line not empty, it's the question (for intermediate/professional)
            $currentPuzzle['question'] = $line;
        } elseif (isset($currentPuzzle['question']) && !isset($currentPuzzle['answer'])) {
            // Look for answer in different formats
            if (strpos($line, 'Answer:') === 0) {
                $currentPuzzle['answer'] = trim(substr($line, 7));
            } elseif (preg_match('/^[A-D]\)\s+.*✅/', $line)) {
                // Multiple choice format (intermediate)
                $currentPuzzle['answer'] = trim(preg_replace('/^[A-D]\)\s+/', '', $line));
                $currentPuzzle['answer'] = trim(str_replace('✅', '', $currentPuzzle['answer']));            }
        }
    }
    
    if (!empty($currentPuzzle)) {
        $puzzles[] = $currentPuzzle;
    }
    
    if (!empty($puzzles)) {
        $puzzle = $puzzles[array_rand($puzzles)];
        return [
            'puzzle' => $puzzle,
            'language' => $language
        ];    }
    return null;
}

// Fetch user's difficulty level
try {
    $stmt = $pdo->prepare("SELECT difficulty_level FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
      // Get today's puzzle only if we don't have one in session or if difficulty changed
    if (!$currentQuestion || $currentDifficulty !== $user['difficulty_level']) {
        $puzzle = getRandomPuzzle($user['difficulty_level']);
        if ($puzzle) {
            $currentQuestion = $puzzle['puzzle']['question'];
            $currentLanguage = $puzzle['language'];
            $_SESSION['current_question'] = $currentQuestion;
            $_SESSION['current_language'] = $currentLanguage;
            $_SESSION['current_difficulty'] = $user['difficulty_level'];
        }
    }
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Challenges - BYTEMe</title>    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../daily/css/challenges.css">
    <script src="../../assets/js/main.js"></script>
    <script src="js/challenges.js" defer></script>
</head>
<body>
    <nav class="main-nav">
        <button class="nav-logo" onclick="window.location.href='../index.php'">
            BYTEMe
        </button>
        <div class="nav-links">
            <a href="index.php" class="active">Daily Challenges</a>
            <a href="../language/index.php">Languages</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="../../admin.php">Admin</a>
            <?php endif; ?>
        </div>        <div class="nav-profile">
            <a href="../profile.php" class="nav-btn">Profile</a>
            <a href="../../logout.php" class="nav-btn logout">Logout</a>
        </div>
    </nav>

    <main class="challenges-container">
        <h1 style="margin-top: 215px; text-align: center;">Daily Coding Challenge</h1>
        <div class="challenge-card">
            <div class="challenge-header">
                <h2>Today's Challenge</h2>
                <div class="challenge-info">
                    <?php if ($currentLanguage): ?>
                    <span class="language-badge" style="background-color: <?php echo htmlspecialchars($languageColors[strtolower($currentLanguage)]); ?>">
                        <?php echo ucfirst(htmlspecialchars($currentLanguage)); ?>
                    </span>
                    <?php endif; ?>
                    <span class="difficulty-badge"><?php echo ucfirst(htmlspecialchars($user['difficulty_level'])); ?></span>
                </div>
            </div>
            <div class="challenge-content">
                <?php if ($currentQuestion): ?>
                <p class="challenge-description">
                    <?php echo htmlspecialchars($currentQuestion); ?>
                </p>                <div class="code-editor">
                    <textarea placeholder="Write your code here..." <?php echo isset($_SESSION['challenge_completed']) ? 'disabled' : ''; ?>><?php echo htmlspecialchars($currentAnswer); ?></textarea>
                </div>
                <div class="challenge-controls">
                    <button class="button run-btn" <?php echo isset($_SESSION['challenge_completed']) ? 'disabled' : ''; ?>>Run</button>
                    <button class="button submit-btn" <?php echo isset($_SESSION['challenge_completed']) ? 'disabled' : ''; ?>>Submit</button>
                </div>
                <?php else: ?>
                <p class="challenge-description">
                    No puzzles available for your difficulty level at the moment.
                    Please try again later or contact an administrator.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <p>BYTEMe</p>
    </footer>
</body>
</html>
