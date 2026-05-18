<?php

class EntryPengguna extends Database { 
    private $active_user_id = null;

    function __construct() {
        parent::__construct();
    }
   
    
    public function save_data() {
        $params = isset($_GET) ? $_GET : $_POST;
        
        $userParams = $this->getUserParams();
        $userParams = json_decode($userParams, true);
        // $params['user']=$userParams[0]['user_id'];
        // $params['periode']=$userParams[0]['param01'];
        // $params['user_id']='';
        if(!empty($params)){
            
            foreach ($params['data'] as $r) {
              
                $params[$r['name']]=$r['value'];
                // if ($r['value']=='') {
                //     $params[$r['name']]=0;
                // }
            }
        }
        $params['f_admin']=empty($params['f_admin']) ? 0 : 1;
         // print_r($params);exit();
        if(empty($params['user_id'])){

            if ($params['f_admin']==1){

                $sqladmin="INSERT into group_has_users (group_id,user_id) VALUES ('admin',:f_username)";
                $this->dbFwExecute($sqladmin,$params);
            }

            // $params['group_id']=$params['group_id'];            

            $sqlgroup="INSERT into group_has_users (group_id,user_id) VALUES (:group_id,:f_username)";
            $this->dbFwExecute($sqlgroup,$params);


            $params['user_id']=$params['f_username'];

            // print_r($params);exit();
            $sql="INSERT INTO `users`(`user_id`, `username`, `password`, `nama`,`email`,`active`, `param01`,`param03`,param04,param05,param06,param10) VALUES (:user_id,:f_username,md5(:f_password),:f_nama,:f_email,1,:param01,:param03,:param04,:param05,:param06,:param10)";


             // echo $this->debugSql($sql,$params);exit();
            echo $this->dbFwExecute($sql,$params);
        
        }else {
             // echo "ehlo";exit;
            
            if (!empty($params['password'])){
             $sqlpassword="UPDATE users set password=:password where user_id=:user_id";

                $this->dbFwExecute($sqlpassword,$params);   
            }
            if(!empty($params['group_id'])){
             $sqlgroup="UPDATE group_has_users set group_id=:group_id where user_id=:user_id";
             $this->dbFwExecute($sqlgroup,$params);
            }
            
            $sql="UPDATE users set nama=:f_nama, email=:f_email, active=:f_aktif, isadmin=:f_admin,
                param01=:param01,param02=:param02,param03=:param03,param04=:param04,param05=:param05,
                param06=:param06,param07=:param07,param08=:param08,param09=:param09,param10=:param10 where user_id=:user_id";


            
            echo $this->dbFwExecute($sql,$params);    
        }

    }


    public function ACTION_bidangList($ret = false) {
        $params = isset($_GET) ? $_GET : $_POST;
        $userParams = $this->getUserParams();
        $userParams = json_decode($userParams, true);
        $sql  = "SELECT * FROM tr_bidang";
        if($ret) {
            return $this->dbFwSelectAndReturnAll($sql,null,true);
        } else {
            echo $this->dbFwSelectAndReturnAll($sql);
        }
    }
    public function ACTION_groupList($ret = false) {
        $params = isset($_GET) ? $_GET : $_POST;
        $userParams = $this->getUserParams();
        $userParams = json_decode($userParams, true);
        $sql  = "SELECT * FROM `groups` ";
        if($ret) {
            return $this->dbFwSelectAndReturnAll($sql,null,true);
        } else {
            echo $this->dbFwSelectAndReturnAll($sql);
        }
    }
    public function ACTION_providerList($ret = false) {
        $params = isset($_GET) ? $_GET : $_POST;
        $userParams = $this->getUserParams();
        $userParams = json_decode($userParams, true);
        $sql  = "SELECT * FROM provider_2019";
        if($ret) {
            return $this->dbDataSelectAndReturnAll($sql,null,true);
        } else {
            echo $this->dbDataSelectAndReturnAll($sql);
        }
    }
    public function ACTION_sifatList($ret = false) {
        $params = isset($_GET) ? $_GET : $_POST;
        $userParams = $this->getUserParams();
        $userParams = json_decode($userParams, true);
        $sql  = "SELECT * FROM tr_sifat_surat";
        if($ret) {
            return $this->dbFwSelectAndReturnAll($sql,null,true);
        } else {
            echo $this->dbFwSelectAndReturnAll($sql);
        }
    }
    public function ACTION_unitkerjaList2() {
        $params = isset($_GET) ? $_GET : $_POST;
        $os = new Os();
        $user_id = $os->getUserLogin();
        $sql  = "SELECT kode_unit_kerja from user_has_unit where user_id='$user_id'";
        //echo $this->debugSql($sql,$params);exit();
        $kode_unit_kerja=$this->dbFwGetValue($sql,null);
        $kode_unit_kerja=substr($kode_unit_kerja,0,2);
      
        $sql  = "
                SELECT *, reff.id_unit_kerja, reff.nama_unit_kerja_lengkap AS `text`
                    FROM reff_unit_kerja reff
                    LEFT JOIN user_has_unit a
                    ON a.`id_unit_kerja`=reff.`id_unit_kerja`
                  
                    WHERE reff.kode_unit_kerja_parent LIKE '$kode_unit_kerja%'
               ";
        // echo $this->debugSql($sql,$params);exit();
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    } 
    public function ACTION_dataList() {
        $params = isset($_GET) ? $_GET : $_POST;      
        
        $sql  = "SELECT c.id_unit_kerja,reff.user_id,reff.username,reff.nama,reff.email,reff.param01,reff.param03,reff.param04,reff.param05,b.group_id FROM users reff
                LEFT JOIN group_has_users a
                ON reff.`user_id`=a.`user_id`
                LEFT JOIN `groups` b
                ON a.`group_id`=b.`group_id`
                LEFT JOIN user_has_unit c
                ON reff.user_id=c.user_id
                WHERE reff.`user_id`=:user_id GROUP BY user_id";
        $pengguna=$this->dbFwSelectAndReturnAll($sql,$params,true);
       
        $result = array();
        $result['PenggunaDS'] = $pengguna;
        echo '{"success" : true, "total":'.count($result) .', "result":' .  json_encode($result) . '}';

    }
     

}
