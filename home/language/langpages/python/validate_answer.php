<?php
session_start();
require_once '../../../../db/db_connect.php';
require_once '../../../../db/progress_functions.php';

// Set up custom logging
$logFile = dirname(__FILE__) . '/debug.log';
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

writeLog("Starting validate_answer.php for Python");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    writeLog("User not logged in");
    echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
    exit();
}

$userAnswer = trim($_POST['code']);
$level = isset($_POST['level']) ? (int)$_POST['level'] : 1;
$language = 'python';

// Verify we're in the right language validator
if (!isset($_SESSION['current_language']) || $_SESSION['current_language'] !== $language) {
    writeLog("Language mismatch: Expected {$language}, got {$_SESSION['current_language']}");
    echo json_encode(['success' => false, 'message' => 'Please reload the page and try again.']);
    exit();
}

writeLog("Processing answer for user_id: {$_SESSION['user_id']}, level: {$level}, language: {$language}");
writeLog("Session data: " . print_r($_SESSION, true));
writeLog("POST data: " . print_r($_POST, true));

if (empty($userAnswer)) {
    writeLog("Empty answer submitted");
    echo json_encode(['success' => false, 'message' => 'Please provide an answer.']);
    exit();
}

// Compare with correct answer from session
if (!isset($_SESSION['current_answer'])) {
    writeLog("No current_answer in session");
    echo json_encode(['success' => false, 'message' => 'Session expired. Please reload the page.']);
    exit();
}

$correctAnswer = trim($_SESSION['current_answer']);

// Case-insensitive comparison after normalizing whitespace
$normalizedUserAnswer = preg_replace('/\s+/', ' ', strtolower($userAnswer));
$normalizedCorrectAnswer = preg_replace('/\s+/', ' ', strtolower($correctAnswer));

$isCorrect = $normalizedUserAnswer === $normalizedCorrectAnswer;

writeLog("Answer comparison - User answer: " . $normalizedUserAnswer);
writeLog("Answer comparison - Correct answer: " . $normalizedCorrectAnswer);
writeLog("Answer comparison - isCorrect: " . ($isCorrect ? "true" : "false"));

try {
    // Insert the attempt into progress table
    $stmt = $pdo->prepare("
        INSERT INTO progress (user_id, language, difficulty, is_correct) 
        VALUES (?, ?, 
            CASE 
                WHEN ? <= 3 THEN 'beginner'
                WHEN ? <= 6 THEN 'intermediate'
                ELSE 'professional'
            END, 
            ?)
    ");
    
    $insertResult = $stmt->execute([$_SESSION['user_id'], $language, $level, $level, $isCorrect]);
    writeLog("Progress insert result: " . ($insertResult ? "success" : "failed"));    // If answer is correct, update their language progress
    if ($isCorrect) {
        writeLog("Attempting to update language progress");
        
        // First check if we have a record
        $stmt = $pdo->prepare("SELECT * FROM user_progress WHERE user_id = ? AND language_name = ?");
        $stmt->execute([$_SESSION['user_id'], $language]);
        $existingProgress = $stmt->fetch(PDO::FETCH_ASSOC);
        writeLog("Existing progress record: " . ($existingProgress ? json_encode($existingProgress) : "none"));

        $updateResult = updateLanguageProgress($_SESSION['user_id'], $language, $level, true);
        writeLog("Language progress update result: " . ($updateResult ? "success" : "failed"));
        
        // Get updated progress data for response
        $progressData = getUserProgress($_SESSION['user_id']);
        
        // Format the language data for response
        $languageData = [
            'name' => $language,
            'progress_percentage' => 0,
            'total_attempts' => 0,
            'correct_attempts' => 0,
            'success_rate' => 0,
            'level_completed' => 1,
            'proficiency_level' => 'beginner',
            'last_attempt' => null
        ];
        
        // Get level progress
        if (!empty($progressData['level_progress'])) {
            foreach ($progressData['level_progress'] as $progress) {
                if (strtolower($progress['language_name']) === strtolower($language)) {
                    $languageData['level_completed'] = $progress['level_completed'];
                    $languageData['proficiency_level'] = $progress['proficiency_level'];
                    $languageData['progress_percentage'] = $progress['progress_percentage'];
                    break;
                }
            }
        }
        
        // Get attempt statistics
        if (!empty($progressData['language'])) {
            foreach ($progressData['language'] as $lang) {
                if (strtolower($lang['language']) === strtolower($language)) {                    $languageData['total_attempts'] = $lang['total_attempts'];
                    $languageData['correct_attempts'] = $lang['correct_attempts'];
                    $languageData['success_rate'] = number_format(($lang['correct_attempts'] / max(1, $lang['total_attempts'])) * 100, 2);
                    $languageData['last_attempt'] = $lang['last_attempt'];
                    break;
                }
            }
        }
    }

    echo json_encode([
        'success' => $isCorrect,
        'message' => $isCorrect ? 
            'Congratulations! Your answer is correct! 🎉 Redirecting to levels page...' : 
            'That\'s not quite right. Try again! 🤔',
        'progress' => $isCorrect ? $languageData : null
    ]);
} catch (PDOException $e) {
    writeLog("Database error: " . $e->getMessage());
    writeLog("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => $isCorrect,
        'message' => $isCorrect ? 
            'Congratulations! Your answer is correct! 🎉 Redirecting to levels page...' : 
            'That\'s not quite right. Try again! 🤔'
    ]);
}
?>
