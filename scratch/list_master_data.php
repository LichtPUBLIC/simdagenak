<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- All modules starting with '02' (Master Data) ---\n";
    $stmt = $pdo->query("SELECT * FROM modules WHERE menu LIKE '02%' ORDER BY menu");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
