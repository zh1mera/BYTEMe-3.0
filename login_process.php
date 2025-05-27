<?php
session_start();
require_once 'db/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($user = $stmt->fetch()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role and whether difficulty is set
                if ($user['role'] === 'admin') {
                    header("Location: admin_home.php");
                } else {
                    // Check if user has set difficulty
                    $stmt = $pdo->prepare("SELECT difficulty_level FROM users WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    $result = $stmt->fetch();
                    
                    if ($result['difficulty_level'] === null) {
                        header("Location: select_difficulty.php");
                    } else {
                        header("Location: home/index.php");
                    }
                }
                exit();            } else {
                $_SESSION['login_error'] = "Invalid password";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['login_error'] = "User not found";
            header("Location: login.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['login_error'] = "Login failed: " . $e->getMessage();
        header("Location: login.php");
        exit();
    }
}
