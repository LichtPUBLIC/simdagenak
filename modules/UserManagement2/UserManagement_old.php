<?php
date_default_timezone_set ('Asia/Jakarta');
class UserManagement extends Database {

    function __construct() {
        parent::__construct();
    }

    public function user_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = 'SELECT * FROM users WHERE 1=1 ';
        if($params['par1'] !==0){
            $grup = $params['par1'];
            $sql .=" and user_id not in (select user_id from group_has_users where group_id = '$grup')";
        }

        if($params['par2'] !==''){
            $cari = strtolower($params['par2']);
            $sql .=" and (lower(nama) like '%$cari%' or lower(username) like '%$cari%')";
        }
        $sql .="order by nama asc";
//        echo $sql;exit();
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    }

    public function ACTION_userListOwned() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = 'SELECT * FROM users WHERE 1=1 ';
        $grup = $params['par1'];
        $sql .=" and user_id in (select user_id from group_has_users where group_id = '$grup')";
        if($params['par2'] !==''){
            $cari = strtolower($params['par2']);
            $sql .=" and (lower(nama) like '%$cari%' or lower(username) like '%$cari%')";
        }

        $sql .="order by nama asc";
//        echo $sql;exit();
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    }

    // public function unit_list() {
    //     $params = isset($_GET) ? $_GET : $_POST;
    //     $response = file_get_contents('http://teppa.slemankab.go.id/_i.php/RekapSerapanAnggaran/{"tahun":2016,"bulan":7}');
    //     echo $response;
    // }

    public function ACTION_insert() {
        $params = isset($_GET) ? $_GET : $_POST;
        
        //print_r($params['data']);exit();
        $params['user_id']=$params['data']['user_id'];
        $params['group_id']=$params['data']['group'];
     
        $sql = "INSERT INTO group_has_users(group_id, user_id) VALUES(:group_id,:user_id)";
        // echo $this->debugSQL($sql,$params);exit;
        echo $this->dbDataExecute($sql,$params);
       
    }
    public function ACTION_delete() {
        $params = isset($_GET) ? $_GET : $_POST;
        
        //print_r($params['data']);exit();
        $params['user_id']=$params['data']['user_id'];
        $params['group']=$params['data']['group'];
     
        $sql = "DELETE FROM group_has_users WHERE group_id=:group AND user_id=:user_id";
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
        $tahun = date('Y');
        $response = file_get_contents('http://teppa.slemankab.go.id/_i.php/getListInstansi/{"tahun":"'.$tahun.'"}');
        $response = json_decode($response);
        $response = $response->result;
        $sql = "SELECT * FROM user_has_unit WHERE user_id = :par1";
        $units = json_decode($this->dbFwSelectAndReturnAll($sql,$params));
        $units = $units->result;
        $userUnit = array_map(function($units) { return $units->id_unit; }, $units);
        $all = array();
        $user = array();
        for ($i=0;$i<count($response);$i++) {    
                    $obj = new stdClass();
                    $obj->kode_instansi = $response[$i]->kode_instansi;
                    $obj->instansi = $response[$i]->instansi;
                    $obj->singkat = $response[$i]->instansi_singkat;          
                    if(in_array($response[$i]->kode_instansi, $userUnit)){
                        array_push($user, $obj);
                    } else {
                        array_push($all,$obj);
                    }
        }
        $return = new stdClass();
        $return->all = $all;
        $return->user = $user;
        echo json_encode($return);
    }
    public function ACTION_groupList($ret = false) {
        $sql  = "SELECT * FROM `groups` ";
        if($ret) {
            return $this->dbDataSelectAndReturnAll($sql,null,true);
        } else {
            echo $this->dbDataSelectAndReturnAll($sql);
        }
    }
    public function ACTION_loadAllData($ret = false) {
        $result = array();
        $result['UserDS']=$this->ACTION_userList(true);
        $result['GroupDS']=$this->ACTION_groupList(true);
        echo '{"success" : true, "total":'.count($result) .', "result":' .  json_encode($result) . '}';
        exit();
    }

    public function ACTION_addUser()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        foreach ($params['data'] as $r) {
            $params[$r['name']]=$r['value'];
            if ($r['value']=='') {
                $params[$r['name']]=0;
            }
        }
        $params['userid'] = $params['username'];
        $params['password']= md5($params['password']);
        if(!isset($params['isadmin'])){
            $params['isadmin']=0;
        }
        $params['param06']=substr(md5($params['userid']),0,12);
//        echo $params['param06'];exit();
        $sqlgetuser = "select count(*) from users WHERE user_id=:userid or email=:email";
        $hasil = $this->dbDataGetValue($sqlgetuser,$params);
//        print_r($hasil);exit();
        if($hasil==0){
            $sql= "INSERT INTO users (
                  user_id,
                  username,
                  password,
                  nama,
                  email,
                  param01,
                  active,
                  isadmin,
                  level_id,
                  param06
                  ) 
                VALUES
                  (
                    :userid,
                    :username,
                    :password,
                    :nama,
                    :email,
                    :param01,
                    :active,
                    :isadmin,
                    :level_id,
                    :param06
                  )
                ";
//        echo $sql;exit();
            echo $this->dbDataExecute($sql, $params);
        }else{
            echo '{"success":true,"msg","Username atau email sudah dipakai"}';
        }

        /*if ($addUser) {
            $sql2 = "INSERT INTO group_has_users (group_id, user_id) VALUES (:group_id,:userid)";
            echo $this->dbDataExecute($sql2, $params);
        }*/
    }

    public function ACTION_addGrup()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        exit();
        $params['param06']=substr(md5($params['userid']),0,12);
//        echo $params['param06'];exit();
        $sqlgetuser = "select count(*) from users WHERE user_id=:userid";
        $hasil = $this->dbDataGetValue($sqlgetuser,$params);
//        print_r($hasil);exit();
        if($hasil==0){
            $sql= "INSERT INTO users (
                  user_id,
                  username,
                  password,
                  nama,
                  email,
                  param01,
                  active,
                  isadmin,
                  level_id,
                  param06
                  ) 
                VALUES
                  (
                    :userid,
                    :username,
                    :password,
                    :nama,
                    :email,
                    :param01,
                    :active,
                    :isadmin,
                    :level_id,
                    :param06
                  )
                ";
//        echo $sql;exit();
            $addUser = $this->dbDataExecute($sql, $params);
        }else{
            $addUser=true;
        }
    }

}
