<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
$result = $conn->query("SHOW TABLES");
while($row = $result->fetch_row()) {
    echo $row[0] . "\n";
}
$conn->close();
?>
