<?php
chdir('../sigadefault');
$_SERVER['HTTP_HOST'] = 'localhost';
require 'lib/server/class.os.php';
require 'lib/server/class.database.php';
$db = new Database(true);
$res = $db->dbDataSelectAndReturnAll("SELECT module_id, name, menu FROM modules ORDER BY menu");
file_put_contents('../magang/scratch/remote_modules.json', json_encode($res, JSON_PRETTY_PRINT));
echo "OK";
