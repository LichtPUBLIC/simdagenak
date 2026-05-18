<?php

class UserInfo  extends Database {
    private $active_user_id = null;

    public function __construct() {
        parent::__construct();
    }

    public function getUserID() {
        $os = new Os();
        $user_id = $os->getUserLogin();

        return $user_id;
    }

    public function userinfo_getDataUser() {
        $params = isset($_GET) ? $_GET : $_POST;
        $params['user_id'] = $this->getUserID();
        $sql = '
            SELECT
            pp.id, pp.user_id, pp.username, pp.password, pp.nama, pp.email, pp.level_id, pp.level_name, pp.wallpaper, pp.theme, pp.active, pp.param01, pp.param02, pp.param03, pp.param04, pp.param05, pp.param06, pp.param07, pp.param08, pp.param09, pp.param10, pp.isadmin , ppp.group_id , p.time_login
            FROM users AS pp
            LEFT JOIN group_has_users AS ppp ON pp.user_id = ppp.user_id
            LEFT JOIN sessions AS p ON pp.user_id = p.user_id
            WHERE pp.user_id = :user_id
            GROUP BY pp.user_id
            ORDER BY p.time_login DESC';

        echo $this->dbFwSelectAndReturnAll($sql, $params);
    }

    public function ACTION_saveUser() {
        $params = isset($_GET) ? $_GET : $_POST;
        $params['user_id'] = $this->getUserID();
        $pstate = strtolower($params['state']);     
        /* cek apakah user sudah ada di server ? */
        foreach ($params['data'] as $r) {
            //var_dump($r);
            $params[$r['name']]=$r['value'];
            if ($r['value']=='') {
                $params[$r['name']]=0;
            }
        }
         // print_r($params);exit();
        if ($pstate === 'edit') {
            $sql = '
                UPDATE users
                SET nama = :nama, email = :email
                WHERE user_id = :user_id
                ';
        }elseif ($pstate === 'update') {
            $sql = '
                UPDATE users
                SET param01 = :tahun
                WHERE user_id = :user_id
                ';
        } else {
            die('{"success" : true, "msg":"Parameter state tidak dikenal"}');
        }
        //print_r($sql); print_r($params); exit;
     echo $this->dbDataExecute($sql, $params);
        
    }
    
    public function userinfo_savePassword() {
        $params = isset($_GET) ? $_GET : $_POST;
        $params['user_id'] = $this->getUserID();
        /* cek apakah user sudah ada di server ? */
        $sql = ' UPDATE users SET password = md5(:password) WHERE user_id = :user_id';
        echo $this->dbFwExecute($sql, $params, 'Berhasil mengupdate password', 'Gagal update password');
    }
}
