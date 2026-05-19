<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
echo "FormatDatagender actions:\n";
$res = $conn->query("SELECT * FROM actions WHERE module_id = 'format-datagender' LIMIT 5");
while($row = $res->fetch_assoc()) print_r($row);
$conn->close();
?>
