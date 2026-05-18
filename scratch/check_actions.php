<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- ACTIONS FOR 'datapilah' ---\n";
    $stmt = $pdo->prepare("SELECT * FROM actions WHERE module_id = 'datapilah'");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
    
    echo "\n--- ACTIONS FOR 'data-pilah' ---\n";
    $stmt = $pdo->prepare("SELECT * FROM actions WHERE module_id = 'data-pilah'");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
