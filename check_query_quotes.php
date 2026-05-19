<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
$kode_data_pilah = '01';
$getBaris = "select SUBSTRING_INDEX(kode_baris,'.',-1) id_baris from data_pilah_baris where kode_data_pilah = ".$kode_data_pilah;
$res = $conn->query($getBaris);
if(!$res){
    echo "ERROR: " . $conn->error;
} else {
    echo "SUCCESS\n";
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
}
$conn->close();
?>
