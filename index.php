<?php
require_once dirname(__FILE__).'/lib/server/class.os.php';
$os = new Os();
$session = $os->session_exist();
if (isset($_GET['publik']) && $_GET['publik']==1) {
    $title = APP_TITLE;
    include_once PATH_TEMPLATE."publik.php";
    exit();
}
// check session dulu , jika masih aktif maka redirect saja OKE
if ($session) {
    header('location: '.APP_INDEX);
    exit();
}

if (isset($_GET['daftar']) && $_GET['daftar']==1) {
    $title = APP_TITLE;
    include_once PATH_TEMPLATE."daftar.php";
    exit();
}
$title = APP_TITLE;
include_once PATH_TEMPLATE."login.php";
?>

<script src="app/login.js"></script>
<!-- MD5 library : https://github.com/blueimp/JavaScript-MD5 -->
<script src="plugins/md5/md5.min.js"></script>