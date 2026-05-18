<?php
require_once dirname(__DIR__).'/lib/server/class.os.php';
require_once dirname(__DIR__).'/lib/server/class.database.php';
$db = new Database();
$res = $db->dbDataSelectAndReturnAll("DESC ref_tahun", array());
print_r($res);
