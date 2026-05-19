<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SELECT DISTINCT menu FROM modules ORDER BY menu");
while($row = $result->fetch_assoc()) {
    echo $row['menu'] . "\n";
}
$conn->close();
?>
