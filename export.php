<?php

require_once( dirname(__FILE__) . "/lib/server/class.os.php");
require_once( dirname(__FILE__) . "/lib/server/class.database.php");

$os = new Os();

$params = empty($_GET) ? $_POST : $_GET;

if(!isset($params['html2pdf'])) {
	$id_export = isset($params['id_export'])? $params['id_export'] : '';
	if($id_export === '') die('Maaf, parameter kosong dilarang masuk');
	$session = $os->getSessionId();

	$sql = "SELECT json_data FROM tm_export where id_export=:id AND session=:session";
	$sth = $os->conn->prepare($sql);
	$sth->bindParam(':id',$id_export,PDO::PARAM_STR);
	$sth->bindParam(':session',$session,PDO::PARAM_STR);
	if($sth->execute()) {
	$json_data = $sth->fetchColumn();
	if(!$json_data) die('Tidak ada object data yang dicari..');
	$params = json_decode($json_data,true);
	$params['output'] = 'tmpfile';
	$_GET = $params;
	} else {
	die('Gagal mengambil export parameter');
	}
} else {
	parse_str($_POST['params'],$params);
	$_GET = $params; // $_GET digunakan di class.html2pdf.php
}

$Module = $params["Module"];
$option = $params["option"];
$action = $params["action"];

$mod = $os->getModuleId(strtolower($Module));
require_once("./modules/$Module/$Module.php");

if (class_exists($Module)) {
	$obj = new $Module;
	$method = $option . "_$action";
	if (method_exists($obj, $method)) {
		// cek otoritas
		$skipped = ($option === 'PUBLIC');

		$act = $os->getAction($mod, $method);
		if(!$act){
            $act = array();
            $act['log'] = 0;
        }

		if ((count($act) > 0) || $skipped) {
			if ( $os->isAllowed($mod, $method) || $skipped) {
				$obj->$method();
				//Simpan ke log aktivitas
				if ($act['log'] == 1) {
					if (!$skipped) {
						$data_log = "";
						$params = isset($_GET) ? $_GET : $_POST;
						$data_log = json_encode($params);
						$os->logActivity($mod, $method, $data_log);
					}
				}
				//////////////
			} else {
				die('{"success" : false, "msg":"Anda tidak diperbolehkan melakukan akses '.$act['description'].'"}');
			}
		} else {
			die('{"success" : false,"msg":"Aksi '.$method.' di module '.$mod.' belum terdaftar di server"}');
		}
	} else {
		die('{"success" : false,"msg":"The method does not exist."}');
	}
} else {
	die('{"success" : false, "msg":"Module tidak ada"}');
}
