<?php
// di Javascript di variable MyApp.serverUrl
require_once dirname(__FILE__).'/lib/server/class.os.php';
require_once dirname(__FILE__).'/lib/server/class.database.php';
ini_set("display_errors", 0);
error_reporting(0);
if (isset($_GET['_init'])) {
    require_once './lib/server/class.module.php';
    $modl = new Module();
    $resl = $modl->checkOtoritas($_GET['_init']);
    // $os->logActivity($theModule, 'view', 'view module');
    if(empty($resl)) {
        echo '{"success": false, "msg" : "Maaf anda tidak boleh mengakses modul ini"}';
    } else {
        // User boleh mengakses modul
        // Load template file (html)
        $data = $resl['data'];
        $modCls = $data->module;
        $tpl = file_get_contents("modules/$modCls/$modCls.html");
        require_once './lib/server/class.utility.php';
        $os = new Os();
        $tpl = str_replace('{{{tahun_options}}}', Utility::getTahunOptions($os->conn), $tpl);
        $resl['tpl'] = $tpl;
        echo json_encode($resl);
    }
} else {
    $paramsApp = isset($_GET) ? $_GET : $_POST;
    if (empty($paramsApp)) {
        $paramsApp = $_POST;
    }

    $Module = $paramsApp['Module'];
    $option = $paramsApp['option'];
    $action = $paramsApp['action'];

    $os = new Os();
    $mod = $os->getModuleId(strtolower($Module));
//    $act=array();
    require_once "./modules/$Module/$Module.php";
    // print_r($paramsApp);exit;

    if (class_exists($Module)) {
        $obj = new $Module();
        $method = $option."_$action";
        if (method_exists($obj, $method)) {
            // cek otoritas
            $skipped = ($option === 'PUBLIC');

            $act = $os->getAction($mod, $method);
//            print_r($act.'oke oke oke');exit();
            if ((is_array($act)&&count($act) > 0) || $skipped) {
                if ($os->isAllowed($mod, $method) || $skipped) {
                    // cek apakah merupakan perintah export
                    $export_type = isset($paramsApp['export_type']) ? $paramsApp['export_type'] : '';
                    // khusus untuk export HTML ada opsi apakah akan return as JSON ?
                    // misalnya jika HTML akan di diaplay ke Panel
                    $html_json = isset($paramsApp['html_json']) ? true : false;
                    if ($export_type === 'stream' && !$html_json) {
                        $os->saveAndSendIdExport($paramsApp);
                        exit;
                    }
                    $obj->$method();
                    //Simpan ke log aktivitas
                    if (!$skipped && $act['log'] == 1) {
                        $data_log = '';
                        $params = isset($_GET) ? $_GET : $_POST;
                        $data_log = json_encode($params);
                        $os->logActivity($mod, $method, $data_log);
                    }
                    //////////////
                } else {
                    die('{"success" : false, "msg":"Anda tidak diperbolehkan melakukan akses modul/aksi ini"}');
                }
            } else {
                die('{"success" : false,"msg":"Aksi '.$method.' di module '.$mod.' belum terdaftar"}');
            }
        } else {
            die('{"success" : false,"msg":"Fungsi/method '.$method.' belum ada di module"}');
        }
    } else {
        die('{"success" : false, "msg":"Module tidak ada"}');
    }
}
