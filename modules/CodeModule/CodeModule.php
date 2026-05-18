<?php

class CodeModule extends Database {

    function __construct() {
        parent::__construct();
    }

    public function module_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = 'SELECT * FROM modules Limit 40';
        echo $this->dbFwSelectAndReturnAll($sql, $params);
    }

    public function action_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = 'SELECT * FROM actions WHERE module_id=:par1';
        echo $this->dbFwSelectAndReturnAll($sql, $params);
    }
    public function insert_module() {
        $params = isset($_GET) ? $_GET : $_POST;
        if($params["data"]["onmenu"]=="true"){
            $params["data"]["onmenu"]=1;
        }
        if($params["data"]["active"]=="true"){
            $params["data"]["active"]=1;
        }
        $sql = "INSERT INTO modules(module_id,module,name,description,menu,iconcls,icon,active,onmenu) 
            VALUES(:module_id,:module,:name,:description,:menu,:iconcls,:icon,:active,:onmenu)";
        //echo $this->debugSql($sql,$params["data"]);
        //print_r($params["data"]["onmenu"]);exit();
        echo $this->dbDataExecute($sql,$params["data"]);
    }

    public function insert_action() {
        $params = isset($_GET) ? $_GET : $_POST;
        if($params["data"]["log"]=="true"){
            $params["data"]["log"]=1;
        }else{
            $params["data"]["log"]=0;
        }
        $sql = "INSERT INTO actions(module_id,action_id,`option`,`action`,description,`log`) 
            VALUES(:module_id,:action_id,:option,:action,:description,:log)";
        //echo $this->debugSql($sql,$params["data"]);exit();
        //print_r($params["data"]["onmenu"]);exit();
        echo $this->dbDataExecute($sql,$params["data"]);
    }

}
