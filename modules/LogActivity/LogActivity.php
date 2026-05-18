<?php

class LogActivity extends Database {

    function __construct() {
        parent::__construct();
    }

    public function session_list() {
        // $sql = "SELECT * FROM sessions ORDER BY time_login DESC";
        $sql = "SELECT
                    session_id, user_id, data, ip_address, user_agent,
                    time_login, time_updated, time_logout,
                    IF(time_logout<now(),'expired','active') session_status
                FROM sessions
                ORDER BY time_login DESC";
        echo $this->dbFwSelectAndReturnAll($sql,null);
    }

    public function log_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "SELECT session_id, user_id, username, date, remote_addr,
                client_addr, module, action, status, data
            FROM log_activity WHERE session_id = :par1";
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    }
}
