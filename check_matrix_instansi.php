<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
$res = $conn->query("SELECT kode_data_pilah, kode_instansi, instansi FROM data_pilah LIMIT 5");
while($row = $res->fetch_assoc()){
    print_r($row);
}
$conn->close();
?>
