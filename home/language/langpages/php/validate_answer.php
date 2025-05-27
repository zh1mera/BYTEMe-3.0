<?php
session_start();
require_once '../../../../db/db_connect.php';
require_once '../../../../db/progress_functions.php';

error_log("Starting validate_answer.php for PHP");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$userAnswer = trim($_POST['code']);
$level = isset($_POST['level']) ? (int)$_POST['level'] : 1;
$language = 'php';

error_log("Processing answer for user_id: {$_SESSION['user_id']}, level: {$level}, language: {$language}");

if (empty($userAnswer)) {
    error_log("Empty answer submitted");
    echo json_encode(['success' => false, 'message' => 'Please provide an answer.']);
    exit();
}

// Compare with correct answer from session
if (!isset($_SESSION['current_answer'])) {
    error_log("No current_answer in session");
    echo json_encode(['success' => false, 'message' => 'Session expired. Please reload the page.']);
    exit();
}

$correctAnswer = trim($_SESSION['current_answer']);

// Case-insensitive comparison after normalizing whitespace
$normalizedUserAnswer = preg_replace('/\s+/', ' ', strtolower($userAnswer));
$normalizedCorrectAnswer = preg_replace('/\s+/', ' ', strtolower($correctAnswer));

$isCorrect = $normalizedUserAnswer === $normalizedCorrectAnswer;

error_log("Answer comparison - isCorrect: " . ($isCorrect ? "true" : "false"));

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
    error_log("Progress insert result: " . ($insertResult ? "success" : "failed"));

    // If answer is correct, update their language progress
    if ($isCorrect) {
        $updateResult = updateLanguageProgress($_SESSION['user_id'], $language, $level, true);
        error_log("Language progress update result: " . ($updateResult ? "success" : "failed"));
    }

    echo json_encode([
        'success' => $isCorrect,
        'message' => $isCorrect ? 
            'Congratulations! Your answer is correct! 🎉 Redirecting to levels page...' : 
            'That\'s not quite right. Try again! 🤔'
    ]);
} catch (PDOException $e) {
    error_log("Database error in validate_answer.php: " . $e->getMessage());
    echo json_encode([
        'success' => $isCorrect,
        'message' => $isCorrect ? 
            'Congratulations! Your answer is correct! 🎉 Redirecting to levels page...' : 
            'That\'s not quite right. Try again! 🤔'
    ]);
}
?>
