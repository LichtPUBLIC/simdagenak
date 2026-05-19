<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
$res = $conn->query("SELECT * FROM actions WHERE module_id = 'verifikasi-data-matrix' LIMIT 1");
print_r($res->fetch_assoc());
$res2 = $conn->query("SELECT * FROM group_has_actions WHERE action_id = 'verifikasi-data-matrix-instansi' LIMIT 1");
print_r($res2->fetch_assoc());
$conn->close();
?>
