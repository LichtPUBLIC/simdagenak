<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');

$module_id = 'verifikasi-data-matrix';

// Clean up the incorrectly formatted actions
$conn->query("DELETE FROM actions WHERE module_id = '$module_id'");
$conn->query("DELETE FROM group_has_actions WHERE module_id = '$module_id'");
$conn->query("DELETE FROM user_has_actions WHERE module_id = '$module_id'");

// Define all actions needed for the module
$actions = [
    ['instansi', 'List Instansi', 'ACTION'],
    ['list', 'List Data', 'ACTION'],
    ['getVerifStatus', 'Get Verifikasi Status', 'ACTION'],
    ['toggleVerif', 'Toggle Verifikasi', 'ACTION'],
    ['UpdateDataFormat', 'Update Data Format', 'ACTION'],
    ['getDataFormat', 'Get Data Format', 'ACTION'],
    ['UpdateDataFormat', 'Update Data Format', 'PUBLIC'],
    ['getDataFormat', 'Get Data Format', 'PUBLIC'],
    ['pdf', 'Export PDF', 'ACTION'],
    ['excel', 'Export Excel', 'ACTION'],
    ['listPrint', 'Print List', 'ACTION']
];

foreach ($actions as $act) {
    $action_name = $act[0];
    $action_title = $act[1];
    $action_option = $act[2];
    
    // Construct the proper action_id
    $proper_action_id = $action_option . "_" . $action_name;
    
    // Insert into actions
    $sql = "INSERT IGNORE INTO `actions` (`module_id`, `action_id`, `option`, `action`, `description`, `log`) 
            VALUES ('$module_id', '$proper_action_id', '$action_option', '$action_name', '$action_title', '1')";
    $conn->query($sql);
    
    // Assign to groups (Admin = 1)
    $groups = $conn->query("SELECT id FROM `groups`");
    while($row = $groups->fetch_assoc()) {
        $gid = $row['id'];
        $conn->query("INSERT IGNORE INTO `group_has_actions` (`group_id`, `module_id`, `action_id`) VALUES ('$gid', '$module_id', '$proper_action_id')");
    }

    // Assign to all users explicitly
    $users = $conn->query("SELECT user_id FROM users");
    while($row = $users->fetch_assoc()) {
        $uid = $row['user_id'];
        $conn->query("INSERT IGNORE INTO `user_has_actions` (`user_id`, `module_id`, `action_id`) VALUES ('$uid', '$module_id', '$proper_action_id')");
    }
}

echo "All actions re-registered with correct proper IDs.";
$conn->close();
?>
