<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require 'lib/server/config.php';
require 'lib/server/class.os.php';
$os = new Os();
$db = $os->conn;

echo "--- data_pilah ---\n";
print_r($db->query("SELECT * FROM data_pilah")->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- New Modules ---\n";
print_r($db->query("SELECT module_id, name FROM modules WHERE module_id IN ('MappingMatrixUnit', 'InputDataMatrix', 'ViewDataMatrix', 'MasterUnit', 'DataPilah')")->fetchAll(PDO::FETCH_ASSOC));
