<?php
require_once 'db_connect.php';

try {
    // Reset user_progress table
    $pdo->exec("TRUNCATE TABLE user_progress");
    
    // Create fresh user_progress table
    $sql = "CREATE TABLE IF NOT EXISTS user_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        language_name VARCHAR(50) NOT NULL,
        level_completed INT DEFAULT 1,
        proficiency_level ENUM('beginner', 'intermediate', 'professional') DEFAULT 'beginner',
        progress_percentage DECIMAL(5,2) DEFAULT 0,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        UNIQUE KEY unique_user_language (user_id, language_name)
    )";
    
    $pdo->exec($sql);
    echo "user_progress table reset successfully";
    
    // Create progress table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS progress (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        language VARCHAR(50) NOT NULL,
        difficulty VARCHAR(20) NOT NULL,
        is_correct BOOLEAN NOT NULL DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    $pdo->exec($sql);
    echo "\nprogress table verified";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
