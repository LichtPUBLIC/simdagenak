<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');

// Update group_has_actions
$sql1 = "UPDATE group_has_actions 
         SET module_id = 'verifikasi-data-matrix' 
         WHERE action_id LIKE 'verifikasi-data-matrix-%'";
$conn->query($sql1);

// Update user_has_actions
$sql2 = "UPDATE user_has_actions 
         SET module_id = 'verifikasi-data-matrix' 
         WHERE action_id LIKE 'verifikasi-data-matrix-%'";
$conn->query($sql2);

echo "Permissions table module_id fixed.";
$conn->close();
?>
