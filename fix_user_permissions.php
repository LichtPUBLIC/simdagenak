<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');

// Insert into user_has_modules
$result = $conn->query("SHOW TABLES LIKE 'user_has_modules'");
if ($result->num_rows > 0) {
    $users = $conn->query("SELECT user_id FROM users");
    while($row = $users->fetch_assoc()) {
        $uid = $row['user_id'];
        $conn->query("INSERT IGNORE INTO `user_has_modules` (`user_id`, `module_id`) VALUES ('$uid', 'verifikasi-data-matrix')");
        
        // Also assign actions to user
        $actions = ['getVerifStatus', 'toggleVerif', 'list', 'getDataFormat'];
        foreach($actions as $act) {
            $conn->query("INSERT IGNORE INTO `user_has_actions` (`user_id`, `action_id`) VALUES ('$uid', 'verifikasi-data-matrix-$act')");
        }
    }
}

// Add group_has_modules
$groups = $conn->query("SELECT id FROM `groups`");
while($row = $groups->fetch_assoc()) {
    $gid = $row['id'];
    $conn->query("INSERT IGNORE INTO `group_has_modules` (`group_id`, `module_id`) VALUES ('$gid', 'verifikasi-data-matrix')");
}

echo "Permissions added to users and groups.";
$conn->close();
?>
