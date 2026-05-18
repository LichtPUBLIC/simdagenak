<?php
$pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
$sql = file_get_contents('restore_modules.sql');
// remove BOM
if (substr($sql, 0, 3) == "\xEF\xBB\xBF") {
    $sql = substr($sql, 3);
}
$pdo->exec($sql);
echo "Database modules restored successfully.\n";
