<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=dbcmgldptkmgag', 'uxhc7qjwxxfub', 'g4t0vezqttq6');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Connection failed: ' . htmlspecialchars($e->getMessage()));
}
?>
