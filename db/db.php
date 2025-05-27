<?php
$host = 'sql102.infinityfree.com';
$db   = 'if0_39054104_byteme';
$user = 'if0_39054104 ';
$pass = 'peterjay1816';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
