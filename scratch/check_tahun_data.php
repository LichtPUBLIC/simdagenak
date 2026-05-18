<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- Tables related to 'tahun' ---\n";
    $stmt = $pdo->query("SHOW TABLES LIKE '%tahun%'");
    print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
    
    // Cek isi tabel 'tahun' jika ada
    $stmt = $pdo->query("SHOW TABLES LIKE 'tahun'");
    if ($stmt->rowCount() > 0) {
        echo "\n--- Content of 'tahun' table ---\n";
        $stmt = $pdo->query("SELECT * FROM tahun");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
