<?php
$_SERVER['HTTP_HOST'] = 'localhost'; // Force localhost config
require_once dirname(__DIR__).'/lib/server/class.os.php';
require_once dirname(__DIR__).'/lib/server/class.database.php';
$db = new Database(true);
$res = $db->dbDataSelectAndReturnAll("SHOW TABLES LIKE 'ref_tahun'", array());
if (empty($res)) {
    echo "Table ref_tahun does NOT exist\n";
    $res = $db->dbDataSelectAndReturnAll("SHOW TABLES", array());
    print_r($res);
} else {
    echo "Table ref_tahun exists. Columns:\n";
    $res = $db->dbDataSelectAndReturnAll("DESC ref_tahun", array());
    print_r($res);
}
