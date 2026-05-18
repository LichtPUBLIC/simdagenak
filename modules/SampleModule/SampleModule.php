<?php

class SampleModule extends Database {

    function __construct() {
        parent::__construct();
    }

    public function sample_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = 'SELECT * FROM modules Limit 5';
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    }

    public function sample_edit() {
        echo '{"success": true,"msg" : "Dummy Edit berhasil.."}';
    }

    public function ACTION_moduleList() {
        $sql  = "SELECT * FROM modules";
        echo $this->dbFwSelectAndReturnAll($sql);
    }

    public function ACTION_actionList() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql  = "SELECT * FROM actions";
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    }
}
