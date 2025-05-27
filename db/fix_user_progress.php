<?php
require_once 'db_connect.php';

try {
    // Drop existing table first
    $pdo->exec("DROP TABLE IF EXISTS user_progress");
    echo "Dropped existing user_progress table\n";
    
    // Create the table with correct schema
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
    echo "user_progress table created successfully\n";

    // Verify the table
    $result = $pdo->query("SHOW COLUMNS FROM user_progress");
    echo "\nTable structure:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
