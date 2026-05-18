<?php

$filename = str_replace('signedFile/','',$_GET['file']);
$url = 'http://ds.slemankab.go.id/signedFile/'.$filename;
file_put_contents("$filename",fopen($url,'r'));
//print_r("$filename");
//exit;
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($filename));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filename));
ob_clean();
flush();
readfile($filename);
unlink($filename);
