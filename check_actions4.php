<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
$res2 = $conn->query("SELECT * FROM group_has_actions WHERE action_id = 'format-datagender-instansi' LIMIT 1");
print_r($res2->fetch_assoc());
$conn->close();
?>
