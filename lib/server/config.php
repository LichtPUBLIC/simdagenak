<?php
// Untuk Setting khusus Localhost, tambahkan di file config.localhost.php
// Setting online di config.online.php
if (!defined('APP_INDEX')) define('APP_INDEX', "main.php");

// Jika Aplikasi dijalankan di localhost, gunakan config.localhost.php
$locals = array($_SERVER['HTTP_HOST'],$_SERVER['SERVER_NAME'],$_SERVER['SERVER_ADDR'], $_SERVER['REMOTE_ADDR']);
// localhost string : localhost, 127.0.0.1, ::1, magang.test
if(in_array('localhost',$locals) || in_array('127.0.0.1', $locals) || in_array('::1', $locals) || in_array('magang.test', $locals)){
	require_once('config.localhost.php');
} else {
	require_once('config.online.php');
}

// Konstanta Aplikasi, lowercase
// Jika localhost dan online beda, silahkan di override di config masing2
if (!defined('APP_NAME')) define('APP_NAME', 'eOFFICE');
if (!defined('APP_REGION')) define('APP_REGION', 'Kominfo Sleman');
if (!defined('APP_REGION_SHORT')) define('APP_REGION_SHORT', 'kominfo');
if (!defined('APP_TITLE')) define('APP_TITLE', "SIMDAGENAK");

/*define path template*/
if (!defined('PATH_TEMPLATE')) define('PATH_TEMPLATE', 'template/smartadmin/');

// if (!defined('EXPORT_PATH_NAME')) define('EXPORT_PATH_NAME', "/export/tmp/");
// Setting Export data Excel, Global untuk Online atau Localhost
if (!defined('EXP_NAME')) define('EXP_NAME', APP_TITLE);
if (!defined('EXP_CREATOR')) define('EXP_CREATOR', EXP_NAME);
if (!defined('EXP_LAST_MOODIFIED_BY')) define('EXP_LAST_MOODIFIED_BY', EXP_NAME);
if (!defined('EXP_TITLE')) define('EXP_TITLE', EXP_NAME);
if (!defined('EXP_SUBJECT')) define('EXP_SUBJECT', EXP_NAME);
if (!defined('EXP_DESCRIPTION')) define('EXP_DESCRIPTION', EXP_NAME);
if (!defined('EXP_KEYWORDS')) define('EXP_KEYWORDS', EXP_NAME);
if (!defined('EXP_CATEGORY')) define('EXP_CATEGORY', EXP_NAME);

// Konstanta untuk nama Cookie yg tersimpan di Client Browser
$app_id = sprintf('%s-%s', APP_NAME, APP_REGION_SHORT);
if (!defined('COOKIE_KEY')) define('COOKIE_KEY', "$app_id-key");
if (!defined('COOKIE_UID')) define('COOKIE_UID', "$app_id-uid");

// Misal 1:30 berarti Sesi user login timeout dalam 1 jam 30 menit
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', '1:30');
