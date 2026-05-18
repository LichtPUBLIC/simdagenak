<?php

class UserManagement extends Database
{

    public function __construct()
    {
        parent::__construct();
    }

    public function  ACTION_gList()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql    = 'SELECT g.*, COUNT(m.group_id) AS user
                FROM `groups`  AS g
                LEFT JOIN group_has_users AS m USING(group_id)
                GROUP BY g.group_id order by g.id';
        echo $this->dbFwSelectAndReturnAll($sql, $params);
    }

    public function user_list()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql    = 'SELECT
                    g.id gid, g.group_id, g.description, g.active,
                    u.id uid, u.user_id, u.username, u.nama, u.active, u.email ,u.param01,u.level_id,u.isadmin
                FROM `groups`  g
                LEFT JOIN group_has_users gu USING (group_id)
                LEFT JOIN users u USING (user_id)
                WHERE g.group_id = :par1 AND username IS NOT null
                ORDER BY g.id, u.id';
        echo $this->dbFwSelectAndReturnAll($sql, $params);
    }

    public function ACTION_userList()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql    = 'SELECT * from users
                   WHERE user_id NOT IN (SELECT user_id FROM group_has_users WHERE group_id=:group_id)
                   ORDER BY nama ASC ';
        echo $this->dbFwSelectAndReturnAll($sql, $params);
    }

    public function group_add()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql    = "INSERT INTO `groups`  (
                  group_id,
                  description,
                  active
                ) 
                VALUES
                  (
                    :group_id,
                    :description,
                    :active
                  )
                ";
        echo $this->dbDataExecute($sql, $params["data"]);
    }

    public function PUBlIC_addUserTerdaftar()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $sqlcek = "select count(*) from group_has_users WHERE group_id=:group_id and user_id=:userid";
        $hasil = $this->dbDataGetValue($sqlcek, $params);
        if($hasil ==0){
            $sql2 = "INSERT INTO group_has_users (group_id, user_id) VALUES (:group_id,:userid)";
            echo $this->dbDataExecute($sql2, $params);
        }else{
            echo '{"success":true}';
        }

    }

    public function group_edit()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $params["data"]["active"]=="true" ? $params["data"]["active"]=1 : $params["data"]["active"]=0;
        $sql    = "UPDATE `groups`  
                    SET
                      group_id = :group_id,
                      description = :description,
                      active = :active 
                    WHERE id = :id;
                ";
        echo $this->dbDataExecute($sql, $params["data"]);
    }

    public function group_delete() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sqlCek = "select count(*) from group_has_users WHERE group_id=:group_id";
        $hasil = $this->dbDataGetValue($sqlCek, $params["data"]);
        if($hasil==0){
            $sql = "DELETE FROM `groups`  WHERE id=:id  ";
            echo $this->dbDataExecute($sql, $params["data"]);
        }else{
            echo '{"success":true,"msg":"Grup ini tidak bisa dihapus karena ada user masuk grup ini"}';
        }

    }

    public function ACTION_datapegawai() {
        $params = isset($_GET) ? $_GET : $_POST;
        $nipPegawai = $params['nip'];
        $dataPegawai = file_get_contents('http://simpeg.slemankab.go.id/_i.php/getProfilPegawai/%7B%22nip%22:"' . $nipPegawai . '"%7D');
        echo $dataPegawai;
    }

    public function user_add()
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

        if ($addUser) {
            $sql2 = "INSERT INTO group_has_users (group_id, user_id) VALUES (:group_id,:userid)";
            echo $this->dbDataExecute($sql2, $params);
        }
    }

    public function user_edit()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        foreach ($params['data'] as $r) {
            $params[$r['name']]=$r['value'];
            if ($r['value']=='') {
                $params[$r['name']]=0;
            }
        }
        if(!isset($params['isadmin'])){
            $params['isadmin']=0;
        }
        $params['userid'] = $params['username'];
        $params['param06']=substr(md5($params['userid']),0,12);
        if ($params['password'] == 0) {
                $sql= "UPDATE users SET
                username = :username,
                nama = :nama,
                email = :email,
                param01  = :param01,
                active = :active,
                isadmin = :isadmin,
                level_id = :level_id,
                param06 = :param06
                WHERE id = :uid;
                ";
        } else {
                $params['password']= md5($params['password']);
                $sql= "UPDATE users SET
                username = :username,
                nama = :nama,
                email = :email,
                password = :password,
                param01  = :param01,
                active = :active,
                isadmin = :isadmin,
                level_id = :level_id,
                param06 = :param06
                WHERE id = :uid;
                "; 
        }
//        echo $sql;exit();
        echo $this->dbDataExecute($sql, $params);
    }
    public function user_delete() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "DELETE FROM users WHERE id=:uid  ";
        $sql2 ="DELETE FROM group_has_users WHERE user_id=:user_id";
        $this->dbDataExecute($sql, $params["data"]);
        echo $this->dbDataExecute($sql2, $params["data"]);
    }

}
