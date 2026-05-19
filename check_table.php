<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
$res = $conn->query("DESCRIBE data_pilah_cell");
while($row = $res->fetch_assoc()){
    print_r($row);
}
$conn->close();
?>
