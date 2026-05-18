<?php

class UserOtoritas extends Database {

    private $active_user_id=null;

    function __construct() {
        parent::__construct();
    }

    function isAllowedAction($iduser, $idgroup, $moduleid, $actionid) {
        $ret = 0;
        if ($actionid === '0') {
            $sql = "SELECT 1 allowed FROM group_has_modules ga
                   WHERE ga.group_id='$idgroup' and ga.module_id='$moduleid'
                   union
                   SELECT 1 allowed FROM user_has_modules ua
                   WHERE ua.user_id='$iduser' and ua.module_id='$moduleid' ";
            $stmt = $this->dbFw->prepare($sql);
            //$stmt = $this->dbFwSelect($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $ret = count($result);
        }
        else
        {
            $sql = "SELECT 1 allowed FROM group_has_actions ga
                   WHERE ga.group_id='$idgroup' and ga.module_id='$moduleid' and ga.action_id='$actionid'
                   union
                   SELECT 1 allowed FROM user_has_actions ua
                   WHERE ua.user_id='$iduser' and ua.module_id='$moduleid' and ua.action_id='$actionid' ";

            //echo $sql;
            $stmt = $this->dbFw->prepare($sql);
            //$stmt = $this->dbFwSelect($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $ret = count($result);
        }
        //echo $sql;
        return $ret;
    }

    function userotoritas_rowCount($psql) {
        $sql = "SELECT count(*) FROM ($psql) a";
        //echo $sql; exit;
        $stmt = $this->dbFw->prepare($sql);
        //$stmt = $this->dbFwSelect($sql);
        $stmt->execute();
        $rows = $stmt->fetch(PDO::FETCH_NUM);
        return $rows[0];
    }

    function userotoritas_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        //$search = $_GET['query'];
        $gid = isset($params['par1']) ? $params['par1'] : '';
        $uid = isset($params['par2']) ? $params['par2'] : '';

        $sql = "SELECT m.module_id, m.module, m.name, m.description, m.menu,
					m.iconcls, m.icon, ifnull(a.action_id, '0') action_id,
					ifnull(a.OPTION, 'module') `option`, ifnull(a.action, 'view') action,
					ifnull(a.description, 'Otoritas untuk akses membuka module') action_desc
				FROM modules m
				LEFT JOIN actions a ON (a.module_id = m.module_id)
				WHERE m.active = 1 ORDER BY m.menu -- module_id
				";

        //echo $sql; exit();
        $stmt = $this->dbFw->prepare($sql);
        //$stmt = $this->dbFwSelect($sql,$params);
        $stmt->execute();
        $total = $stmt->rowCount();
        $arr_hasil= array();
        $new_module = '';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hasil = new stdClass();
            // ambil jika uid atau gid salah satu ada isinya
            if(!empty($gid) | !empty($uid) ) {
                $hasil->selected= $this->isAllowedAction($uid, $gid, $row['module_id'], $row['action_id']);
                $hasil->module_id = $row['module_id'];
                $hasil->action_id = $row['action_id'];
            } else {
                if($new_module !== $row['module_id']) {
                    $hasil->new_module = 1;
                    $new_module = $row['module_id'];
                }
                $hasil->module_id = $row['module_id'];
                $hasil->name = $row['name'];
                $hasil->description = $row['description'];
                $hasil->menu = $row['menu'];
                $hasil->module = $row['module'];
                $hasil->iconcls = $row['iconcls'];
                $hasil->icon = $row['icon'];
                $hasil->action_id = $row['action_id'];
                $hasil->option = $row['option'];
                $hasil->action = $row['action'];
                $hasil->action_desc = $row['action_desc'];
            }
            $arr_hasil[]=$hasil;
        }

        $data = json_encode($arr_hasil);
        $result = '{"success" : true, "total":'.$total.', "result":' . $data . '}';
        echo $result;
    }

    function userotoritas_save() {
        $params = isset($_GET) ? $_GET : $_POST;
        $jsonData = file_get_contents("php://input");
        $pdata = json_decode(stripslashes($jsonData), true);
        // echo print_r($pdata); exit;
        // jika tidak ada modul actions yg di set
        if(!$pdata) {
            $tipe = $params['mode'];
            $id   = $params['mode_id'];
        }

        if($pdata) {
            $sql_val_arr = array();
            $sql_mod_arr = array();
            $modules = array();

            foreach ($pdata as $key => $data) {
                $tipe = $data['mode'];
                $id = $data['mode_id'];
                // skip jika module tidak mempunyai actions
                if($data['action_id'] == '0') continue;
                array_push($sql_val_arr, "('".$data['mode_id']."','".$data['module_id']."','".$data['action_id']."')");
                if (!in_array($data['module_id'],$modules)) {
                    array_push($modules,$data['module_id']);
                    array_push($sql_mod_arr, "('".$data['mode_id']."','".$data['module_id']."')");
                }

            }

            $sql_mod_arr = array_unique($sql_mod_arr);

            $sql_val = implode(',',$sql_val_arr);
            $sql_mod = implode(',',$sql_mod_arr);
        }


        if ($tipe=='group') {
            /*Olah data otoritas group*/
            $sql_del = "DELETE FROM group_has_actions WHERE  group_id='$id'";
            $sql_del_m = "DELETE FROM group_has_modules WHERE  group_id='$id'";
            if($pdata) {
                $sql_insert="INSERT into group_has_actions(group_id, module_id, action_id) values $sql_val ";
                $sql_insert_m="INSERT into group_has_modules(group_id, module_id) values $sql_mod ";
            }
        } else {
            $sql_del = "DELETE FROM user_has_actions WHERE  user_id='$id'";
            $sql_del_m = "DELETE FROM user_has_modules WHERE  user_id='$id'";
            if($pdata) {
                $sql_insert="INSERT into user_has_actions(user_id, module_id, action_id) values $sql_val ";
                $sql_insert_m="INSERT into user_has_modules(user_id, module_id) values $sql_mod ";
            }
        }

        //echo $sql_del_m; echo "\n";echo $sql_del; exit;

        $stmt_del = $this->dbFw->prepare($sql_del);
        //$stmt_del = $this->dbFwSelect($sql_del);
        $stmt_del->execute();

        if($pdata) {
            $stmt_insert = $this->dbFw->prepare($sql_insert);
            //$stmt_insert = $this->dbFwSelect($sql_insert);
            $stmt_insert->execute();
        }

        $stmt_del_m = $this->dbFw->prepare($sql_del_m);
        //$stmt_del_m = $this->dbFwSelect($sql_del_m);
        $stmt_del_m->execute();

        if($pdata) {
            $stmt_insert_m = $this->dbFw->prepare($sql_insert_m);
            //$stmt_insert_m = $this->dbFwSelect($sql_insert_m);
            $stmt_insert_m->execute();
        }


        $result = '{"success" : true,"msg":"Berhasil menyimpan data"}';

        echo $result;
    }

    function userotoritas_del() {

        $params = isset($_GET) ? $_GET : $_POST;
        $pid_placemark = $params['id_placemark'];
        $sql = " delete FROM placemark WHERE id_placemark=$pid_placemark  ";
        //echo $sql; exit();
        $sql = " delete FROM placemark WHERE id_placemark=:id_placemark  ";
        //echo $sql; exit();
        $stmt = $this->dbFw->prepare($sql);
        $stmt->bindParam(":id_placemark",$pid_placemark, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $result = '{"success" : true,"msg":"Berhasil menghapus data"}';
        } else {
            $result = '{"success" : false,"msg":"'.implode('<br>',$stmt->errorInfo()).'"}';
        }
        echo $result;
    }

    function userotoritas_listUserGroup() {

        $params = isset($_GET) ? $_GET : $_POST;
        //$idTree = $params['idtree'];
        //$idTree = 'thetree';
        $sql = " SELECT g.group_id, g.description FROM `groups`  g WHERE g.active=1";
        $stmt = $this->dbFw->prepare($sql);
        $stmt->execute();
        $arr_hasil= array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $hasil = new stdClass();
            //$hasil->id= $idTree.'.'.$row['group_id'];
            $hasil->group_id= $row['group_id'];
            //$hasil->text= '<div style="white-space:normal !important;padding-left: 3.3em   !important;padding-right: 1em  !important;">'
            //			  .$row['description'].'</div>';
            $hasil->text= $row['description'];
            $hasil->qtip= $row['description'];
            $hasil->leaf= false;

            //$hasil->icon= '';
            $sql_user = "SELECT u.user_id, u.username, u.nama FROM users u
            WHERE u.active=1 and u.user_id in (
                SELECT distinct ug.user_id
                FROM group_has_users ug
                WHERE ug.group_id='".$row['group_id']."')";
            $stmt_user = $this->dbFw->prepare($sql_user);
            $stmt_user->execute();
            $arr_hasil_user = array();
            while ($row_user = $stmt_user->fetch(PDO::FETCH_ASSOC)) {
                $hasil_user = new stdClass();
                //$hasil_user->id= $idTree.'.'.$row['group_id'].'.'.$row_user['user_id'];
                $hasil_user->user_id= $row_user['user_id'];
                $hasil_user->text= $row_user['user_id'] . ' ['.$row_user['nama'] .']';
                $hasil_user->qtip= $row_user['nama'];
                $hasil_user->leaf= true;
                //$hasil_user->icon= '';
                //$hasil_user->children = array();
                $arr_hasil_user[]=	$hasil_user;
            }

            if (count($arr_hasil_user) > 0) {
                /* $hasil->expanded= true; */
                $hasil->expanded= false;
            } else {
                $hasil->expanded= false;
            }
            $hasil->children= $arr_hasil_user;
            $arr_hasil[]=$hasil;
        }
        $data = json_encode($arr_hasil);

        //$result = '{"success" : true,"data":' . $data . '}';

        //echo $result;

        echo $data;
    }


    function group_simpan($status) {
        $params = isset($_GET) ? $_GET : $_POST;
        $pgroup_id = $params['_group_id'];
        $pdescription = $params['_group_description'];
        $pactive = $params['_group_active'];
        if ($status=='add') {
            $sql = "INSERT INTO `groups`  (group_id, description, active) values (:group_id, :description, :active)";
            $stmt = $this->dbFw->prepare($sql);
        } else if ($status=='edit') {
            $sql = "UPDATE `groups`  set
                description=:description,
                active=:active
                WHERE 	id=:id and  group_id=:group_id ";
            $stmt = $this->dbFw->prepare($sql);
            $pid = $params['_id'];
            $stmt->bindParam(":id", $pid, PDO::PARAM_STR);
        }
        $stmt->bindParam(":group_id", $pgroup_id, PDO::PARAM_STR);
        $stmt->bindParam(":description", $pdescription, PDO::PARAM_STR);
        $stmt->bindParam(":active", $pactive, PDO::PARAM_STR);
        if ($stmt->execute()) {
            $result = '{"success" : true,"msg":"Berhasil menyimpan data"}';
        } else {
            $result = '{"success" : false,"msg":"'.implode('<br>',$stmt->errorInfo()).'"}';
        }
        //$result = '{"success" : true,"msg":"Berhasil menyimpan data"}';
        echo $result;
    }

	function group_del() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = " delete FROM `groups`  WHERE id=:id  ";
        $stmt = $this->dbFw->prepare($sql);
        $stmt->bindParam(":id",$params['id'], PDO::PARAM_STR);
        if ($stmt->execute()) {
            $result = '{"success" : true,"msg":"Berhasil menghapus data"}';
        } else {
            $result = '{"success" : false,"msg":"'.implode('<br>',$stmt->errorInfo()).'"}';
        }        echo $result;
    }

	function group_add() {
        $this->group_simpan('add');
    }

	function group_edit() {
        $this->group_simpan('edit');
    }

	function group_list() {
        $params = isset($_GET) ? $_GET : $_POST;
		$WHERE = '';
        if (array_key_exists('query', $params)) {
            $search=strtoupper($params['query']);
            $WHERE = "AND ((upper(group_id) like '%".$search."%' ) ";
            $WHERE .= "OR ( upper(description) like '%".$search."%' )) ";
        }

		$sqlGroup = "SELECT a.* FROM `groups`  a WHERE 1=1 $WHERE";
        $total = $this->userotoritas_rowCount($sqlGroup);
        $arr_hasil= array();
        if ( $total > 0 ) {
            $start = $params['start'];
            $limit = $params['limit'];
            $sql = $sqlGroup . " limit $start, $limit ";
            $stmt = $this->dbFw->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hasil = new stdClass();
                $hasil->id = $row['id'];
                $hasil->group_id = $row['group_id'];
                $hasil->description = $row['description'];
                $hasil->active = $row['active'];
                $arr_hasil[]=$hasil;
            }
        }		$data = json_encode($arr_hasil);
        $result = '{"success" : true, "total":'.$total.', "result":' . $data . '}';
        echo $result;
    }

	function group_listUser() {
        $params = isset($_GET) ? $_GET : $_POST;
        $WHERE = "";
        if (array_key_exists('query', $params)) {
            $search=strtoupper($params['query']);
            $WHERE = "AND ((upper(a.nama) like '%".$search."%' ) ";
            $WHERE .= "OR ( upper(a.email) like '%".$search."%' ) ";
            $WHERE .= "OR ( upper(a.user_id) like '%".$search."%' ) )";
        }
        $pgroup_id = $params['group_id'];
        $sqlMember = "SELECT a.* FROM users a, group_has_users b WHERE a.user_id=b.user_id and b.group_id='$pgroup_id' $WHERE";
        $total = $this->userotoritas_rowCount($sqlMember);
        $arr_hasil= array();
        if ( $total > 0 ) {
            $start = $params['start'];
            $limit = $params['limit'];
            $sql = $sqlMember." limit $start, $limit ";
            $stmt = $this->dbFw->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hasil = new stdClass();
                $hasil->id = $row['id'];
                $hasil->user_id = $row['user_id'];
                $hasil->username = $row['username'];
                $hasil->nama = $row['nama'];
                $hasil->email = $row['email'];
                $hasil->active = $row['active'];
                $hasil->isadmin = $row['isadmin'];
                $arr_hasil[]=$hasil;
            }
        }		$data = json_encode($arr_hasil);
        $result = '{"success" : true, "total":'.$total.', "result":' . $data . '}';
        echo $result;
    }

	function group_listUserAll() {
        $params = isset($_GET) ? $_GET : $_POST;
        $WHERE = "";
        if (array_key_exists('query', $params)) {
            $search=strtoupper($params['query']);
            $WHERE = "AND ((upper(a.nama) like '%".$search."%' ) ";
            $WHERE .= "OR ( upper(a.email) like '%".$search."%' ) ";
            $WHERE .= "OR ( upper(a.user_id) like '%".$search."%' ) ) ";
        }
        $sqlMember = "SELECT a.* FROM users a WHERE 1=1 $WHERE";
        $total = $this->userotoritas_rowCount($sqlMember);
        $arr_hasil= array();
        if ( $total > 0 ) {
            $start = $params['start'];
            $limit = $params['limit'];
            $sql = $sqlMember." limit $start, $limit ";
            $stmt = $this->dbFw->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hasil = new stdClass();
                $hasil->id = $row['id'];
                $hasil->user_id = $row['user_id'];
                $hasil->username = $row['username'];
                $hasil->nama = $row['nama'];
                $hasil->email = $row['email'];
                $hasil->active = $row['active'];
                $hasil->isadmin = $row['isadmin'];
                $arr_hasil[]=$hasil;
            }
        }		$data = json_encode($arr_hasil);
        $result = '{"success" : true, "total":'.$total.', "result":' . $data . '}';
        echo $result;
    }

    function group_addUser() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = " insert into group_has_users (group_id, user_id) values (:group_id, :user_id)";
        $stmt = $this->dbFw->prepare($sql);
        $stmt->bindParam(":group_id",$params['group_id'], PDO::PARAM_STR);
        $stmt->bindParam(":user_id",$params['user_id'], PDO::PARAM_STR);
        if ($stmt->execute()) {
            $result = '{"success" : true,"msg":"Berhasil menyimpan data"}';
        } else {
            $result = '{"success" : false,"msg":"'.implode('<br>',$stmt->errorInfo()).'"}';
        }        echo $result;
    }

    function group_delUser() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = " delete FROM group_has_users WHERE group_id=:group_id  and user_id=:user_id";
        $stmt = $this->dbFw->prepare($sql);
        $stmt->bindParam(":group_id",$params['group_id'], PDO::PARAM_STR);
        $stmt->bindParam(":user_id",$params['user_id'], PDO::PARAM_STR);
        if ($stmt->execute()) {
            $result = '{"success" : true,"msg":"Berhasil menghapus data"}';
        } else {
            $result = '{"success" : false,"msg":"'.implode('<br>',$stmt->errorInfo()).'"}';
        }        echo $result;
    }
}
