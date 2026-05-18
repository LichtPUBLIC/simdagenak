<?php

require_once dirname(__FILE__).'/lib/server/class.os.php';
$os = new Os();
// Cek apakah session ada
if (!$os->session_exist()) {
    // session habis atau tidak ada, ke halaman login
    header('location: index.php');
    exit();
} else {
    // User berhasil atau sudah login
    require(dirname(__FILE__) . '/lib/Mustache/Autoloader.php');
    Mustache_Autoloader::register();
    // getUserLogin ada di class.os.php
    $user_id = $os->getUserLogin();
    $user_data = $os->getUserData();


    require_once( dirname(__FILE__) . "/lib/server/class.database.php");
    require_once( dirname(__FILE__) . "/lib/server/class.module.php");

    $module = new Module();
    $userModules = $module->get_user_modules($user_id,true);

    $data = new StdClass;
    $data->modules = $userModules;
    // userData dalam object
    $data->userData = json_decode($user_data);
    // userData encoded (dalam json string)
    $data->userDataEnc = $user_data;
    $data->templatepath = PATH_TEMPLATE;

    $mus = new Mustache_Engine;
    echo $mus->render(file_get_contents(PATH_TEMPLATE.'dashboard.html'),$data);
}
