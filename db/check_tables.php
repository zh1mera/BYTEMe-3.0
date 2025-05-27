<?php
require_once 'db_connect.php';

function tableExists($tableName) {
    global $pdo;
    try {
        $result = $pdo->query("SHOW TABLES LIKE '{$tableName}'");
        return $result->rowCount() > 0;
    } catch (PDOException $e) {
        echo "Error checking table {$tableName}: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "Checking database tables...\n\n";

// Check progress table
if (tableExists('progress')) {
    echo "✓ progress table exists\n";
    try {
        $result = $pdo->query("DESCRIBE progress");
        echo "progress table structure:\n";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['Field']}: {$row['Type']}\n";
        }
    } catch (PDOException $e) {
        echo "Error getting progress table structure: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ progress table does not exist\n";
}

echo "\n";

// Check user_progress table
if (tableExists('user_progress')) {
    echo "✓ user_progress table exists\n";
    try {
        $result = $pdo->query("DESCRIBE user_progress");
        echo "user_progress table structure:\n";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['Field']}: {$row['Type']}\n";
        }
    } catch (PDOException $e) {
        echo "Error getting user_progress table structure: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ user_progress table does not exist\n";
}
