<?php
require_once 'db_connect.php';

try {
    // Add role column if it doesn't exist
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('admin', 'user') DEFAULT 'user'");
    
    // Check if admin exists
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    if ($stmt->rowCount() == 0) {
        // Create default admin account
        $adminUsername = 'admin';
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $adminEmail = 'admin@byteme.com';
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$adminUsername, $adminEmail, $adminPassword]);
        
        echo "Admin account created successfully!\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
        echo "Please change these credentials after first login.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
