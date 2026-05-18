<?php

class EntryDataPilah extends Database { 
    private $active_user_id = null;

    function __construct() {
        parent::__construct();
    }
   
    
    public function save_data() {
        $params = isset($_GET) ? $_GET : $_POST;
        
        $userParams = $this->getUserParams();
        $userParams = json_decode($userParams, true);
        $params['user']=$userParams[0]['user_id'];
        $params['periode']=$userParams[0]['param01'];
        $params['user_id']='';
        if(!empty($params)){
            
            foreach ($params['data'] as $r) {
              
                $params[$r['name']]=$r['value'];
                if ($r['value']=='') {
                    $params[$r['name']]=0;
                }
            }
        }
         // print_r($params);exit();
        if(empty($params['user_id'])){
            $params['user_id']=$params['username'];

            // print_r($params);exit();
            $sql="INSERT INTO `users`(`user_id`, `username`, `password`, `nama`,`email`,`active`, `param01`,`param03`,param04,param05) VALUES (:user_id,:username,md5(:password),:nama,:email,1,:param01,:param03,:param04,:param05)";
             // echo $this->debugSql($sql,$params);exit();
            if($this->dbDataExecute($sql,$params)){
                $sql2  = "SELECT kode_unit_kerja from reff_unit_kerja where id_unit_kerja=:id_unit_kerja";
                         // echo $this->debugSql($sql2,$params);exit();
                $kode=$this->dbDataGetValue($sql2,$params);//echo $kepada;
                $sql="INSERT INTO `user_has_unit`(`user_id`, `id_unit_kerja`, `kode_unit_kerja`) VALUES (:username,:id_unit_kerja,'$kode')";
                 // echo $this->debugSql($sql,$params);exit();
                if($this->dbDataExecute($sql,$params)){
                    $sql="INSERT INTO `group_has_users`(`group_id`,`user_id`) VALUES (:group_id,:username)";
                    echo $this->dbDataExecute($sql,$params);
                    $path= "upload/files/".$params['user_id'];
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);

                        $file = "upload/files/master/index.html";
                        $newfile = $path."/index.html";

                        if (!copy($file, $newfile)) {
                            echo "failed to copy";
                        }
                    }

                }
                
            }   
        }else{
            // print_r($params);exit();
            $user_id=$params['user_id'];
            $sql="UPDATE users SET username=:username,nama=:nama,email=:email,param01=:param01,param03=:param03,param04=:param04,param05=:param05 WHERE user_id=:user_id";
              // echo $this->debugSql($sql,$params);//exit();
            if($this->dbDataExecute($sql,$params)){
                $sql2  = "SELECT kode_unit_kerja from reff_unit_kerja where id_unit_kerja=:id_unit_kerja";
                // echo $this->debugSql($sql2,$params);//exit();
                $kode=$this->dbDataGetValue($sql2,$params);//echo $kepada;
                $sql3="UPDATE user_has_unit SET id_unit_kerja=:id_unit_kerja,kode_unit_kerja=$kode WHERE user_id='$user_id'";
                 // echo $this->debugSql($sql3,$params);//exit();
                if($this->dbDataExecute($sql3,$params)){
                    $sql4="UPDATE group_has_users SET group_id=:group_id WHERE user_id='$user_id'";
                    echo $this->dbDataExecute($sql4,$params);                  

                }
                
            }
        }
    }


    public function ACTION_bidangList($ret = false) {
        $params = isset($_GET) ? $_GET : $_POST;
        $userParams = $this->getUserParams();
        $userParams = json_decode($userParams, true);
        $sql  = "SELECT * FROM tr_bidang";
        if($ret) {
            return $this->dbDataSelectAndReturnAll($sql,null,true);
        } else {
            echo $this->dbDataSelectAndReturnAll($sql);
        }
    }
    public function ACTION_groupList($ret = false) {
        $params = isset($_GET) ? $_GET : $_POST;
        $userParams = $this->getUserParams();
        $userParams = json_decode($userParams, true);
        $sql  = "SELECT * FROM `groups` ";
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
            return $this->dbDataSelectAndReturnAll($sql,null,true);
        } else {
            echo $this->dbDataSelectAndReturnAll($sql);
        }
    }
    public function ACTION_unitkerjaList2() {
        $params = isset($_GET) ? $_GET : $_POST;
        $os = new Os();
        $user_id = $os->getUserLogin();
        $sql  = "SELECT kode_unit_kerja from user_has_unit where user_id='$user_id'";
        //echo $this->debugSql($sql,$params);exit();
        $kode_unit_kerja=$this->dbDataGetValue($sql,null);
        $kode_unit_kerja=substr($kode_unit_kerja,0,2);
      
        $sql  = "
                SELECT *, reff.id AS id_unit_kerja, reff.nama_instansi AS `text`
                    FROM reff_unit_kerja reff
                    LEFT JOIN user_has_unit a
                    ON a.`id_unit_kerja`=reff.`id`
                  
                    WHERE reff.id_instansi LIKE '$kode_unit_kerja%'
               ";
        // echo $this->debugSql($sql,$params);exit();
        echo $this->dbDataSelectAndReturnAll($sql,$params);
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
        $pengguna=$this->dbDataSelectAndReturnAll($sql,$params,true);
       
        $result = array();
        $result['PenggunaDS'] = $pengguna;
        echo '{"success" : true, "total":'.count($result) .', "result":' .  json_encode($result) . '}';

    }
     

}
