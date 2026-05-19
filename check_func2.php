<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
$res = $conn->query("SHOW CREATE FUNCTION getCellValue");
if($res){
    $row = $res->fetch_assoc();
    print_r($row['Create Function']);
} else {
    echo $conn->error;
}
$conn->close();
?>
