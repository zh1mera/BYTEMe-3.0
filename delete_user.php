<?php
session_start();
require_once 'db/db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    try {
        // Prevent admin from deleting themselves
        if ($_POST['user_id'] == $_SESSION['user_id']) {
            throw new Exception("You cannot delete your own admin account");
        }

        // Begin transaction
        $pdo->beginTransaction();

        // First delete progress records
        $stmt = $pdo->prepare("DELETE FROM progress WHERE user_id = ?");
        $stmt->execute([$_POST['user_id']]);

        // Then delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_POST['user_id']]);
        
        // Commit transaction
        $pdo->commit();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } catch(Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
