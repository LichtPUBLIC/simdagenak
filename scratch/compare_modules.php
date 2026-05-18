<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require 'lib/server/class.os.php';
require 'lib/server/class.database.php';
$db = new Database(true);

$db_modules = $db->dbDataSelectAndReturnAll("SELECT module_id, name, menu FROM modules ORDER BY menu");
$db_modules = json_decode($db_modules); // it returns json string if no true

echo "\n--- All Modules in DB with Menu ---\n";
foreach($db_modules as $mod) {
    if (strpos(strtolower($mod->menu), 'manajemen') !== false || strpos(strtolower($mod->name), 'manajemen') !== false) {
        echo "[MATCH] ";
    }
    echo str_pad($mod->module_id, 25) . " | " . str_pad($mod->name, 30) . " | " . $mod->menu . "\n";
}
