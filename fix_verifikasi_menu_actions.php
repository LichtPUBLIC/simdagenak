<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');

// 1. Check the exact menu string for Data Pilah
$result = $conn->query("SELECT menu FROM modules WHERE name LIKE 'Data Pilah%' OR module_id LIKE '%data%pilah%' LIMIT 1");
$row = $result->fetch_assoc();
$correct_menu = $row['menu'] ? $row['menu'] : '032;Manajemen Tabel/';

// Update VerifikasiDataMatrix to use the EXACT same menu string
$conn->query("UPDATE modules SET menu = '$correct_menu' WHERE module_id = 'verifikasi-data-matrix'");

// 2. Register Actions
$actions = [
    ['verifikasi-data-matrix', 'getVerifStatus', 'Get Verifikasi Status', 'ACTION'],
    ['verifikasi-data-matrix', 'toggleVerif', 'Toggle Verifikasi', 'ACTION'],
    ['verifikasi-data-matrix', 'list', 'List Data', 'ACTION'],
    ['verifikasi-data-matrix', 'getDataFormat', 'Get Data Format', 'PUBLIC']
];

foreach ($actions as $act) {
    $module_id = $act[0];
    $action_name = $act[1];
    $action_title = $act[2];
    $action_option = $act[3];
    
    // Insert into actions
    $sql = "INSERT IGNORE INTO `actions` (`module_id`, `action_id`, `option`, `action`, `description`, `log`) 
            VALUES ('$module_id', '$module_id-$action_name', '$action_option', '$action_name', '$action_title', '')";
    $conn->query($sql);
    
    // Assign to admin group
    $sql_group = "INSERT IGNORE INTO `group_has_actions` (`group_id`, `action_id`) VALUES (1, '$module_id-$action_name')";
    $conn->query($sql_group);
}

echo "Menu updated to: $correct_menu\nActions registered.";
$conn->close();
?>
