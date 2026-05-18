<?php

class DetailEvent extends Database {

    function __construct() {
        parent::__construct();
    }

    function ACTION_sample() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = 'SELECT * FROM modules Limit 5';
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    }

}
