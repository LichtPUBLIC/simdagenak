<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
echo "group_has_actions for list:\n";
$res2 = $conn->query("SELECT * FROM group_has_actions WHERE action_id = 'verifikasi-data-matrix-list'");
while($row = $res2->fetch_assoc()) print_r($row);

echo "\nuser_has_actions for list:\n";
$res3 = $conn->query("SELECT * FROM user_has_actions WHERE action_id = 'verifikasi-data-matrix-list'");
while($row = $res3->fetch_assoc()) print_r($row);

$conn->close();
?>
