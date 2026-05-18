<?php

require_once( dirname(__FILE__) .'/config.php');

class Os {
    // Koneksi database ke MYSQL
    private $dbh = null;
    public $conn = null;

    function __construct() {
        $server   = DB_FW_HOST;
        $driver   = DB_FW_DRIVER;
        $user     = DB_FW_USER;
        $password = DB_FW_PASSWORD;
        $dbname   = DB_FW_NAME;
        $dsn = "$driver:dbname=$dbname;host=$server";

        try {
            $this->dbh = new PDO($dsn, $user, $password);

            // To enable PDO Exceptions and disable emulated prepares:
            // Jadi ketika menggunakan bindParam, dan ada Error akan ditampilkan
            // Memudahkan debugging
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $this->conn = $this->dbh;
            $this->dbh->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        } catch (PDOException $e) {
            $result = array('success' => false, 'error' => '0', 'msg' => $e->getMessage());
            die(json_encode($result));
        }
    }

    function session_exist() {
        /* check session */

        if (isset($_COOKIE[COOKIE_KEY])) {
            /** check session ke database */
            $sessid = $this->getSessionId();
            $sql = "SELECT * FROM sessions
                    WHERE session_id = :sessid AND NOW() < time_logout AND logout_status IS NULL";

            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(":sessid", $sessid, PDO::PARAM_STR);
            $stmt->execute();
            $total = $stmt->rowCount();
            if ($total > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function checkSession() {
        if ($this->session_exist()) {
            return $this->getSessionId();
        } else {
            die ('session tidak aktif');
        }
    }

    // Koneksi sudah dipindah langsung di __construct
    // function connectDB($driver=DB_FW_DRIVER, $server=DB_FW_HOST, $user=DB_FW_USER, $password=DB_FW_PASSWORD, $dbname=DB_FW_NAME) {
    // $dsn = "$driver:dbname=$dbname;host=$server";

    // try {
    // $dbh = new PDO($dsn, $user, $password);
    // return $dbh;
    // } catch (PDOException $e) {
    // $result = array('success' => false, 'error' => '0', 'msg' => $e->getMessage());
    // die(json_encode($result));
    // }
    // }

    function getSessionId() {
        if(isset($_GET['sso']) && isset($_GET['session_id'])) {
            return preg_replace('/[^0-9a-zA-Z]/','',$_GET['session_id']);
        } else {
            // selain 0-9, a-z, A-Z replace, untuk security
            return preg_replace('/[^0-9a-zA-Z]/','',$_COOKIE[COOKIE_KEY]);
        }
    }

    function getUserLogin() {
        $sessid = $this->getSessionId();
        $sql = "SELECT user_id from sessions where session_id='$sessid'";
        //echo $sql; exit();
        //$dbh = $this->connectDB();
        $row = $this->dbh->query($sql)->fetch();
        $user_id = $row ? current($row) : null;
        return $user_id;
    }
    function getUserUnit() {
        $user_id = $this->getUserLogin();
        $sql = "SELECT id_unit from user_has_unit where user_id='$user_id'";
        //echo $sql; exit();
        //$dbh = $this->connectDB();
        $row = $this->dbh->query($sql)->fetch();
        $id_unit = $row ? current($row) : null;
        return $id_unit;
    }

    function getUserData() {
        $sessid = $this->getSessionId();
        //echo 'session= '.$sessid; exit;
        $sql = "SELECT user_id, username, nama, isadmin, param01, param02, param03, param04, param05,
               param06, param07, param08, param09, param10,email
               FROM users
               WHERE user_id IN (
               SELECT user_id
               FROM sessions
               WHERE session_id = '$sessid'
               AND NOW() < time_logout
               AND logout_status IS NULL
               )";
        //$dbh = $this->connectDB();
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return  json_encode($result);
        } else {
            return '';
        }

    }

    function isAllowed($module_id, $action_id) {
        $user_id = $this->getUserLogin();
        $sql =
            "SELECT ga.module_id, ga.action_id
             FROM group_has_actions ga
             WHERE ga.group_id in (
                 SELECT gu.group_id
                 FROM group_has_users gu
                 where gu.user_id='$user_id')
             AND ga.module_id='$module_id' and ga.action_id='$action_id'

             UNION

             SELECT ua.module_id, ua.action_id
             FROM user_has_actions ua
             WHERE ua.user_id='$user_id'
               and ua.module_id='$module_id' and ua.action_id='$action_id' ";

       $sth = $this->dbh->prepare($sql);
       $sth->execute();
       $hasil = $sth->fetchAll(PDO::FETCH_OBJ);
       return count($hasil) > 0;
    }

    function getModuleId($module) {
        $mod = strtolower($module);
        $sql =
            "SELECT module_id
            FROM modules
            WHERE lower(module_id)='$mod' OR lower(module)='$mod'";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->fetchColumn();
    }

    function getAction($module, $action_id) {
        $mod = strtolower($module);
        $act = strtolower($action_id);
        $sql = "SELECT * FROM actions
            WHERE lower(module_id)='$mod' AND lower(action_id)='$act'";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        if(count($data)) {
            return $data[0];
        } else {
            return '';
        }
    }


    function logActivity($mod, $method, $data_log) {
        $user_id = $this->getUserLogin();
        $session_id = $this->getSessionId();
        $remote_addr = $_SERVER['REMOTE_ADDR'];
        // print_r($_SERVER); exit;
        // $client_addr = $_SERVER["HTTP_CLIENT_IP"];
        if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
            $client_addr = $_SERVER["HTTP_CLIENT_IP"];
        } else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
            $client_addr = $_SERVER["HTTP_X_FORWARDED_FOR"] ;
        } else if ( isset($_SERVER["REMOTE_ADDR"]) )    {
            $client_addr = $_SERVER["REMOTE_ADDR"];
        }
        $sql = "INSERT INTO log_activity (session_id, user_id, username, DATE, remote_addr, client_addr, module, action, status, data)
                VALUES (:session_id, :user_id, :username, now(), :remote_addr, :client_addr, :module, :action, '', :data)";
        //echo $sql; exit();
        //$dbh = $this->connectDB();
        $stmt = $this->dbh->prepare($sql);


        $stmt->bindParam(":session_id", $session_id, PDO::PARAM_STR);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);
        $stmt->bindParam(":username", $user_id, PDO::PARAM_STR);
        $stmt->bindParam(":module", $mod, PDO::PARAM_STR);
        $stmt->bindParam(":action", $method, PDO::PARAM_STR);
        $stmt->bindParam(":data", $data_log, PDO::PARAM_STR);
        $stmt->bindParam(":remote_addr", $remote_addr, PDO::PARAM_STR);
        $stmt->bindParam(":client_addr", $client_addr, PDO::PARAM_STR);
        $stmt->execute();

    }

    function getUserParams() {
        $params = isset($_GET) ? $_GET : $_POST;
        $user_id = $this->getUserLogin();
        $sql = "SELECT * from users where user_id='$user_id'";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        $hasil = $sth->fetchAll(PDO::FETCH_OBJ);
        return json_encode($hasil);
    }

    function getModuleByModuleId($module) {
        $mod = strtolower($module);
        $sql = "SELECT module FROM modules
            WHERE lower(module_id)= '$mod' or lower(module)='$mod'";
        $sth = $this->dbh->prepare($sql);
        $sth->execute();
        return $sth->fetchColumn();
    }

    function getPathByModuleId($module) {
        $path = "";
        $mod="";
        $mod = $this->getModuleByModuleId($module);
        $path = dirname(dirname(__FILE__)) ."/modules/$mod/Template/";
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        return $path;
    }

    function saveAndSendIdExport($params) {
        $sql = "INSERT INTO tm_export(session,json_data) VALUES(:session,:json_data)";
        $stmt = $this->dbh->prepare($sql);

        $sessid = $this->getSessionId();
        $json = json_encode($params);
        // print_r($params);
        // print_r($json);
        // print_r(json_decode($json,true)); exit;
        $stmt->bindParam(':session', $sessid, PDO::PARAM_STR);
        $stmt->bindParam(':json_data',$json, PDO::PARAM_STR);
        if($stmt->execute()) {
            $id_export =  $this->dbh->lastInsertId();
            //echo '{"success" : true, "id_export":'.$id_export.'}';
            echo '{"success" : true, "filename":"/export.php?id_export='. $id_export .'"}';
        } else {
            echo '{"success" : false, "msg":"Menyimpan parameter untuk export gagal"}';
        }
    }

}
