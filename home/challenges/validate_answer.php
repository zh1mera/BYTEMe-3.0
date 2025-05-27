<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../db/db_connect.php';

function getAnswer($difficulty, $language, $question) {
    $filePath = "../../puzzles/" . strtolower($difficulty) . "/" . strtolower($language) . ".txt";
    if (!file_exists($filePath)) {
        return null;
    }
    
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $foundQuestion = false;
      foreach($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'Q:') === 0) {
            $currentQuestion = trim(substr($line, 2));
            if ($currentQuestion === $question) {
                $foundQuestion = true;
            }
        } elseif ($foundQuestion && (strpos($line, 'A:') === 0 || strpos($line, 'Answer:') === 0)) {
            return trim(strpos($line, 'A:') === 0 ? substr($line, 2) : substr($line, 7));
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['answer']) || !isset($data['language']) || !isset($data['question'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid input']);
        exit();
    }
    
    // Handle saving answer to session
    if (isset($data['action']) && $data['action'] === 'save_answer') {
        $_SESSION['current_answer'] = $data['answer'];
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    // Handle validation action explicitly
    if (isset($data['action']) && $data['action'] === 'validate') {
        try {
            $stmt = $pdo->prepare("SELECT difficulty_level FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            $correctAnswer = getAnswer(
                $user['difficulty_level'],
                $data['language'],
                $data['question']
            );

            // Compare answers (ignoring whitespace and case)
            $userAnswer = preg_replace('/\s+/', '', strtolower($data['answer']));
            $correctAnswer = preg_replace('/\s+/', '', strtolower($correctAnswer));

            $result = [
                'correct' => $userAnswer === $correctAnswer
            ];

            // Save answer and mark challenge as attempted regardless of correctness
            $_SESSION['current_answer'] = $data['answer'];
            $_SESSION['challenge_completed'] = true;

            // Save progress to database
            try {
                $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, language, difficulty, question, answer, is_correct) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $data['language'],
                    $user['difficulty_level'],
                    $data['question'],
                    $data['answer'],
                    $result['correct']
                ]);
            } catch(PDOException $e) {
                error_log("Error saving progress: " . $e->getMessage());
            }

            header('Content-Type: application/json');
            echo json_encode($result);
            exit();

        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }
    }

    // If action is not recognized
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid action']);
    exit();
}
