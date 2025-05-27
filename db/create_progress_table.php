<?php
require_once 'db_connect.php';

try {
    // First check if table exists
    $result = $pdo->query("SHOW TABLES LIKE 'progress'");
    $tableExists = $result->rowCount() > 0;

    if ($tableExists) {
        // Drop the existing table
        $pdo->exec("DROP TABLE IF EXISTS progress");
        echo "Dropped existing progress table\n";
    }

    $sql = "CREATE TABLE progress (
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
    echo "progress table created successfully\n";

    // Insert a test record to verify
    $stmt = $pdo->prepare("INSERT INTO progress (user_id, language, difficulty, is_correct) VALUES (?, ?, ?, ?)");
    $stmt->execute([1, 'test', 'beginner', true]);
    echo "Test record inserted successfully\n";
    
    // Verify the record
    $stmt = $pdo->query("SELECT * FROM progress LIMIT 1");
    if ($stmt->fetch()) {
        echo "Test record verified successfully\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
