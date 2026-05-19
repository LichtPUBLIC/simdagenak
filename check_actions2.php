<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
$res = $conn->query("SELECT * FROM actions WHERE module_id = 'verifikasi-data-matrix'");
while($r = $res->fetch_assoc()){
    print_r($r);
}

echo "\n--- user_has_actions ---\n";
$res2 = $conn->query("SELECT * FROM user_has_actions WHERE action_id LIKE 'verifikasi-data-matrix%'");
while($r = $res2->fetch_assoc()){
    print_r($r);
}

echo "\n--- group_has_actions ---\n";
$res3 = $conn->query("SELECT * FROM group_has_actions WHERE action_id LIKE 'verifikasi-data-matrix%'");
while($r = $res3->fetch_assoc()){
    print_r($r);
}
$conn->close();
?>
