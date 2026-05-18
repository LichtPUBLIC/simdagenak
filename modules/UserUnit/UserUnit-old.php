<?php

class UserUnit extends Database {

    function __construct() {
        parent::__construct();
    }

    public function user_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = 'SELECT * FROM users';
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    }

    public function unit_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = 'SELECT
                	g.id gid, g.user_id, g.nama, g.active,
                	u.id_unit_kerja, u.nama_unit_kerja_lengkap, u.caption_kepala_unit_kerja,u.kode_unit_kerja
                FROM users g
                LEFT JOIN user_has_unit gu USING (user_id)
                LEFT JOIN reff_unit_kerja u USING (id_unit_kerja)
                WHERE g.user_id = :par1
                ORDER BY g.id, u.id_unit_kerja';
         //echo $this->debugSql($sql,$params);
        // print_r($params);exit();
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    }

    public function ACTION_insert() {
        $params = isset($_GET) ? $_GET : $_POST;
        
        //print_r($params['data']);exit();
        $params['user_id']=$params['data']['user_id'];
        $params['id_unit_kerja']=$params['data']['id_unit_kerja'];
        $params['kode_unit_kerja']=$params['data']['kode_unit_kerja'];
     
        $sql = "INSERT INTO user_has_unit(id_unit_kerja,user_id,kode_unit_kerja) VALUES(:id_unit_kerja,:user_id,:kode_unit_kerja)";
        echo $this->dbDataExecute($sql,$params);
       
    }
    public function ACTION_delete() {
        $params = isset($_GET) ? $_GET : $_POST;
        
        //print_r($params['data']);exit();
        $params['user_id']=$params['data']['user_id'];
        $params['id_unit_kerja']=$params['data']['id_unit_kerja'];
     
        $sql = "DELETE FROM user_has_unit WHERE id_unit_kerja=:id_unit_kerja and user_id=:user_id";
        if($this->dbDataExecute($sql,$params)){
            echo '{"success" : true,"msg":"Berhasil Menghapus Data"}';
        }
       
    }
    public function ACTION_userList($ret = false) {
        $sql  = "SELECT * FROM users";
        if($ret) {
            return $this->dbDataSelectAndReturnAll($sql,null,true);
        } else {
            echo $this->dbDataSelectAndReturnAll($sql);
        }
    }
    public function ACTION_unitList() {
        $params = isset($_GET) ? $_GET : $_POST;
        $start=$params['start'];
        $limit=$params['limit'];
        $sql  = "SELECT * FROM reff_unit_kerja WHERE level_unit_kerja=1 AND id_unit_kerja NOT IN (SELECT b.id_unit_kerja FROM user_has_unit a
                 LEFT JOIN reff_unit_kerja b ON a.id_unit_kerja = b.id_unit_kerja
                 WHERE a.user_id=:data)";
        //echo $this->debugSql($sql,$params);print_r($params);exit();
        echo $this->dbDataSelectPaging($sql,$params,$start,$limit);
    }
    // public function ACTION_unitList2() {
    //     $params = isset($_GET) ? $_GET : $_POST;
    //     $sql  = "SELECT * FROM reff_unit_kerja WHERE id_unit NOT IN (SELECT b.id_unit FROM user_has_unit a
    //              LEFT JOIN reff_unit b ON a.id_unit = b.id_unit
    //              WHERE a.user_id=:par1)";
    //     //echo $this->debugSql($sql,$params);print_r($params);exit();
    //     echo $this->dbFwSelectAndReturnAll($sql,$params);
    // }
    public function ACTION_loadAllData($ret = false) {
        $result = array();
        $result['UserDS']=$this->ACTION_userList(true);        
        echo '{"success" : true, "total":'.count($result) .', "result":' .  json_encode($result) . '}';
        exit();
    }
}
