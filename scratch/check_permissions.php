<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- PERMISSIONS FOR 'datapilah' ---\n";
    $stmt = $pdo->prepare("SELECT * FROM group_has_modules WHERE module_id = 'datapilah'");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\n--- PERMISSIONS FOR 'data-pilah' (Old) ---\n";
    $stmt = $pdo->prepare("SELECT * FROM group_has_modules WHERE module_id = 'data-pilah'");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
