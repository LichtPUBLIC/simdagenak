<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Satukan menu manajemen tabel ke 032;Manajemen Tabel/
    $stmt = $pdo->prepare("UPDATE modules SET menu = '032;Manajemen Tabel/' WHERE menu = '030;manajemen tabel/'");
    $stmt->execute();
    
    echo "Update successful. Combined " . $stmt->rowCount() . " items into '032;Manajemen Tabel/'.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
