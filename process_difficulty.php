<?php
session_start();
require_once 'db/db_connect.php';

// First, check if the difficulty_level column exists
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'difficulty_level'");
    if ($stmt->rowCount() == 0) {
        // Column doesn't exist, so create it
        $pdo->exec("ALTER TABLE users ADD COLUMN difficulty_level ENUM('beginner', 'intermediate', 'professional') NULL");
    }
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database structure error: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $difficulty = $_POST['difficulty'];
    $user_id = $_SESSION['user_id'];    try {
        // Validate the difficulty level
        $allowed_difficulties = ['beginner', 'intermediate', 'professional'];
        if (!in_array($difficulty, $allowed_difficulties)) {
            throw new Exception("Invalid difficulty level selected");
        }

        $stmt = $pdo->prepare("UPDATE users SET difficulty_level = ? WHERE id = ?");
        $result = $stmt->execute([$difficulty, $user_id]);
        
        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => "Welcome, " . $_SESSION['username'] . "! You've selected " . ucfirst($difficulty) . " difficulty.",
            ]);
        } else {
            throw new Exception("Failed to update difficulty level");
        }
    } catch(Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
