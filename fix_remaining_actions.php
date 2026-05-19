<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');

$module_id = 'verifikasi-data-matrix';
$actions = [
    ['instansi', 'List Instansi', 'ACTION'],
    ['UpdateDataFormat', 'Update Data Format', 'PUBLIC'],
    ['pdf', 'Export PDF', 'ACTION'],
    ['excel', 'Export Excel', 'ACTION'],
    ['listPrint', 'Print List', 'ACTION']
];

foreach ($actions as $act) {
    $action_name = $act[0];
    $action_title = $act[1];
    $action_option = $act[2];
    
    // Insert into actions
    $sql = "INSERT IGNORE INTO `actions` (`module_id`, `action_id`, `option`, `action`, `description`, `log`) 
            VALUES ('$module_id', '$module_id-$action_name', '$action_option', '$action_name', '$action_title', '')";
    $conn->query($sql);
    
    // Assign to admin group
    $sql_group = "INSERT IGNORE INTO `group_has_actions` (`group_id`, `action_id`) VALUES (1, '$module_id-$action_name')";
    $conn->query($sql_group);

    // Assign to all users (just in case)
    $users = $conn->query("SELECT user_id FROM users");
    while($row = $users->fetch_assoc()) {
        $uid = $row['user_id'];
        $conn->query("INSERT IGNORE INTO `user_has_actions` (`user_id`, `action_id`) VALUES ('$uid', '$module_id-$action_name')");
    }
}

echo "Remaining actions registered.";
$conn->close();
?>
