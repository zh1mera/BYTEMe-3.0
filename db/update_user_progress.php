<?php
require_once 'db_connect.php';

try {
    // First drop the existing table if it exists
    $pdo->exec("DROP TABLE IF EXISTS user_progress");
    
    // Create the updated user_progress table
    $sql = "CREATE TABLE user_progress (
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
    echo "user_progress table updated successfully";
} catch(PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
?>
