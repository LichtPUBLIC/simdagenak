<?php
$pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');

echo "=== ALL MODULES ===\n";
$stmt = $pdo->query('SELECT * FROM modules ORDER BY module_id');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo sprintf("ID: %-20s | Module: %-25s | Name: %-30s | Menu: %-40s | Active: %s\n",
        $row['module_id'], $row['module'], $row['name'], $row['menu'], $row['active']);
}

// Check menu_tree or directories if exists
echo "\n=== TABLES WITH 'menu' OR 'dir' IN NAME ===\n";
$stmt = $pdo->query("SHOW TABLES LIKE '%menu%'");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) { echo $row[0] . "\n"; }

$stmt = $pdo->query("SHOW TABLES LIKE '%dir%'");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) { echo $row[0] . "\n"; }

$stmt = $pdo->query("SHOW TABLES LIKE '%group%'");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) { echo $row[0] . "\n"; }
