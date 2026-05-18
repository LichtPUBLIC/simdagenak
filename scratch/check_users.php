<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once dirname(__DIR__).'/lib/server/class.os.php';
require_once dirname(__DIR__).'/lib/server/class.database.php';
$db = new Database(true);
$res = $db->dbDataSelectAndReturnAll("DESC users", array(), true);
echo json_encode($res);
