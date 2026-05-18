<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once dirname(__DIR__).'/lib/server/class.os.php';
require_once dirname(__DIR__).'/lib/server/class.database.php';
$db = new Database(true);
$res = $db->dbDataSelectAndReturnAll("SELECT module_id, name, menu FROM modules ORDER BY menu", array(), true);
foreach($res as $mod) {
    if (strpos(strtolower($mod->menu), 'manajemen') !== false || strpos(strtolower($mod->name), 'manajemen') !== false) {
        echo "[MATCH] ";
    }
    echo str_pad($mod->module_id, 25) . " | " . str_pad($mod->name, 30) . " | " . $mod->menu . "\n";
}
