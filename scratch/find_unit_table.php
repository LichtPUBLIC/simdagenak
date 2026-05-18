<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once dirname(__DIR__).'/lib/server/class.os.php';
require_once dirname(__DIR__).'/lib/server/class.database.php';
$db = new Database(true);
$res = $db->dbDataSelectAndReturnAll("SHOW TABLES LIKE '%user%unit%'", array(), true);
if (empty($res)) {
    $res = $db->dbDataSelectAndReturnAll("SHOW TABLES LIKE '%unit%'", array(), true);
}
echo json_encode($res);
