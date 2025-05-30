<?php
session_start();
require_once 'db/db_connect.php';
require_once 'mail_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['step']) && $_POST['step'] === 'send_verification') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Store data in session
        $_SESSION['signup_data'] = [
            'username' => $username,
            'email' => $email,
            'password' => $password
        ];
        $_SESSION['verification_code'] = '12345'; // As requested, using static code '12345'
        $_SESSION['verification_pending'] = true;

        // Send verification email
        if (send_verification_email($email, $username, '12345')) {
            header("Location: signup.php");
            exit();
        } else {
            die("Failed to send verification email. Please try again. If the problem persists, contact support.");
        }
    }
    
    elseif (isset($_POST['step']) && $_POST['step'] === 'verify_code') {
        if (!isset($_SESSION['verification_code']) || !isset($_SESSION['signup_data'])) {
            die("Invalid session. Please start the signup process again.");
        }

        $submitted_code = $_POST['verification_code'];
        if ($submitted_code === $_SESSION['verification_code']) {
            // Code is correct, create the user account
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([
                    $_SESSION['signup_data']['username'],
                    $_SESSION['signup_data']['email'],
                    $_SESSION['signup_data']['password']
                ]);
                
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $_SESSION['signup_data']['username'];
                
                // Clean up session variables
                unset($_SESSION['signup_data']);
                unset($_SESSION['verification_code']);
                unset($_SESSION['verification_pending']);
                
                header("Location: select_difficulty.php");
                exit();
            } catch(PDOException $e) {
                die("Registration failed: " . $e->getMessage());
            }
        } else {
            $_SESSION['error'] = "Invalid verification code. Please try again.";
            header("Location: signup.php");
            exit();
        }
    }
}
?>
