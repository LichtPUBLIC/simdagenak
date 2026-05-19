<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all groups
$result = $conn->query("SELECT id FROM `groups`");
while($row = $result->fetch_assoc()) {
    $id_grup = $row['id'];
    $sql = "INSERT IGNORE INTO `group_has_modules` (`group_id`, `module_id`) VALUES ('$id_grup', 'verifikasi-data-matrix');";
    $conn->query($sql);
}
$conn->close();
echo "Otoritas added.";
?>
