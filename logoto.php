<?php
/*
 * by arkan
 * */
ob_start();
session_start();
require_once dirname(__FILE__) . '/lib/server/class.os.php';
$os = new Os();

$auth = false;
if (isset($_GET['date']) && isset($_GET['session'])) {
    //"2018-10-31 14:57:14"
    //54e9aa6789f7a6c8f44232d4e41c4231
    //1540972634

    if($_GET['session'] == md5(date('Ymd'))){
        if($_GET['date'] !=0){
            if(is_numeric($_GET['date'])){
                $dateFilter = $_GET['date'];
            }else{
                $date = new DateTime($_GET['date']);
                $dateFilter = $date->getTimestamp();
            }
            $sql = "SELECT session_id, user_id, username, unix_timestamp(date) timestamp, date, remote_addr,
                client_addr, module, action, status, data
            FROM log_activity where unix_timestamp(date) > '$dateFilter'
            order by `date` ASC limit 1";
        }else{
            $sql = "SELECT session_id, user_id, username,unix_timestamp(date) timestamp, date, remote_addr,
                client_addr, module, action, status, data
            FROM log_activity 
            order by `date` DESC limit 10";
        }
        $stmt = $os->conn->prepare($sql);
        $stmt->execute();
        $data = array();
        while ($val = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $val);
        }
        $return = array();
        $return['success'] = true;
        $return['total'] = count($data);
        $return['result'] = $data;
    }else{
        $return = array();
        $return['success'] = false;
        $return['msg'] = "session tidak valid";
    }
}else{
    $return = array();
    $return['success'] = false;
    $return['msg'] = "Parameter tidak lengkap";
}

print_r(json_encode($return));