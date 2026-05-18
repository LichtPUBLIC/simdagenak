<?php

class Module extends Database {
    private $active_user_id = null;

    // method dbFw sudah di deklarasikan di Class Database sebagai protected // sehingga untuk akses koneksi db tinggal menggunakan dbOS saja
    function __construct() {
        $this->setUpdateSession(false);
        parent::__construct();
    }

    private function set_icon_image($urlIcon, $type = 'menu') {
        $url = 'http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $root_path = dirname(parse_url($url, PHP_URL_PATH));
        if ($root_path == '/') $root_path = '';
        //echo $_SERVER["DOCUMENT_ROOT"].$root_path.$urlIcon.'::';
        //if (file_exists($_SERVER["DOCUMENT_ROOT"].$root_path.$urlIcon)) {
        if (@getimagesize($_SERVER["DOCUMENT_ROOT"].$root_path.$urlIcon)) {
            //echo $_SERVER["DOCUMENT_ROOT"].$root_path.$urlIcon.'::';
            return $root_path.$urlIcon;
        } else {
            if ($type == 'menu') {
                return $root_path.'/resources/menu24/folder.png';
            } else {
                return $root_path.'/resources/images/go-next.png';
            }
        }
    }

    private function hilangkan_no_urut($text) {
        /* Hilangkan karakter pengurutan pada menu (harus looping array), contoh menu : '01;Administrator','02;Utilitas,'03;Masterdata'' */
        $pos = strpos($text, ';');
        if ($pos !== false) {
            return substr($text, $pos + 1, 400);
        } else {
            return $text;
        }
    }

    function checkOtoritas($module_id) {
        // untuk security, replace selain huruf didalam []
        $module = preg_replace('/[^0-9a-zA-Z_-]/','',$module_id);
        $user_id = $this->getUserLogin();
        $sql = "SELECT * FROM (
            SELECT gm.module_id
            FROM group_has_modules gm
            WHERE gm.group_id IN (
                    SELECT gu.group_id
                    FROM group_has_users gu
                    WHERE gu.user_id = '$user_id'
                    )
                AND gm.module_id = '$module'
            UNION
                SELECT um.module_id
                FROM user_has_modules um
                WHERE um.user_id = '$user_id'
                    AND um.module_id = '$module'
            ) tm LEFT JOIN modules USING(module_id)";

        $stmt = $this->dbFw->prepare($sql);
        $stmt->execute();
        $result = array();
        if ($stmt->rowCount() > 0) {
            $result['success'] = true;
            $result['data'] = current($stmt->fetchAll(PDO::FETCH_OBJ));
        }
        return $result;
    }

    function isAdmin() {
        $res = 'false';
        $user_id = $this->getUserLogin();
        if ($user_id == 'admin') {
            $res = 'true';
        }
        echo '{"success" : '.$res.',"msg":"Ini adalah cek admin"}';
    }


    public function get_user_modules($user_id, $returnArray) {
        $sql_otoritas = "SELECT module_id FROM user_has_modules
            WHERE user_id = :user1
            UNION DISTINCT
            SELECT module_id FROM group_has_modules
            WHERE group_id IN (
                    SELECT group_id
                    FROM group_has_users
                    WHERE user_id = :user2
                    )";

        $sql = "SELECT * FROM modules
            WHERE module_id IN ($sql_otoritas)
                AND active = 1
                AND onmenu = 1
                ORDER BY menu, name;
            ";

        $params = array('user1' => $user_id, 'user2' => $user_id);
        $modules = $this->dbFwSelectAndReturnAll($sql, $params, true);
        // print_r($menus); exit;

        // hitung maksimal jumlah kedalaman sub menu, mulai dari 0
        $max = 0;
        foreach($modules as $mod) {
            $max = max($max, count(explode('/', $mod->menu)) - 2);
        }
        // inisialisasi current index parent dengan 0
        $parents = array_fill(0, $max + 1, 0);

        $menus = array();
        $path_tmp = array();
        foreach($modules as $mod) {
            // explode('/','abc/def/') = Array ( [0] => abc [1] => def [2] => )
            $paths = explode('/', $mod->menu);
            $deep = count($paths) - 2;
            for ($i = 1; $i < count($paths); $i++) {
                if (!in_array($mod->menu, $path_tmp)) {
                    $path_tmp[] = $mod->menu;
                    $obj = new stdClass();
                    // Ambil Nama menu parent yg paling belakang
                    $obj->text = $this->hilangkan_no_urut($paths[$deep]);
                    // $obj->deep = $deep;
                    // $obj->cur = $parents[$deep];
                    // $obj->leaf = false;
                    // Flag bahwa current object adalah parent
                    $p = 'parent'.($deep + 1);
                    $obj->$p = $parents[$deep] + 1;
                    $obj->icon_dir = $mod->icon;
                    $obj->children = array();
                    if ($deep > 0) {
                        $menus[$parents[$deep - 1] - 1]->children[] = $obj;
                    } else {
                        $menus[] = $obj;
                    }
                    $parents[$deep]++;
                    $parents[$deep + 1] = 0;
                }
            }

            // masukkan property sesuai kebutuhan
            $obj2 = new stdClass();
            $obj2->module = $mod->module;
            $obj2->module_id = $mod->module_id;
            $obj2->qtip = $mod->description;
            $obj2->icon_dir = $mod->icon;
            $obj2->icon_mod = $mod->iconcls;
            // $obj2->onview = $mod->onview;
            // $obj2->leaf = true;
            $obj2->child = 1;
            $obj2->text = $this->hilangkan_no_urut($mod->name);
            // agar koding dibawahnya tidak terlalu panjang
            $p0 = $parents[0] - 1;
            if ($deep > 0) $p1 = $parents[1] - 1;
            // Ambil sampai kedalaman sub menu 3 tingkat, kalau lebih
            // tinggal tambah elseif lagi
            if ($deep === 0) {
                $menus[$p0]->children[] = $obj2;
            }
            elseif($deep === 1) {
                $menus[$p0]->children[$p1]->children[] = $obj2;
            }
            elseif($deep === 2) {
                $menus[$p0]->children[$p1]->children[] = $obj2;
            }

            $parents[$deep + 1]++;
        }
        // echo "<pre>" . print_r($menus,true) .  "</pre>"; exit;

        if ($returnArray) {
            return $menus;
        } else {
            echo json_encode($menus);
        }
    }

    function get_user_modules_old($user_id, $returnArray) {
        $params = isset($_GET) ? $_GET : $_POST;
        // $idtree=$params['idtree'];
        // $user_id = addslashes($params['_user_id']);
        // $user_id = $user_id = $os->getUserLogin();
        $idtree = APP_NAME;
        $arr_hasil = array();
        $sql_where_in_module_id = "
            SELECT module_id FROM user_has_modules
            WHERE user_id = '$user_id'
            UNION DISTINCT
            SELECT module_id FROM group_has_modules
            WHERE group_id IN (
                    SELECT group_id
                    FROM group_has_users
                    WHERE user_id = '$user_id'
                    )
            ";

        /* check jika ada module tanpa menu */
        $sql_module = "
            SELECT * FROM modules
            WHERE module_id IN ($sql_where_in_module_id)
                AND menu = 'Root'
                AND active = 1
                AND onmenu = 1
            ORDER BY NAME
            ";
        $stmt = $this->dbFw->prepare($sql_module);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $hasil = new stdclass();
            $hasil->id = $row['module_id'];
            $hasil->text = $row['name'];
            $hasil->module = $row['module'];
            $hasil->qtip = $row['description'];
            $hasil->icon = $row['icon'];
            $hasil->leaf = true;
            $arr_hasil[] = $hasil;
        }


        $sql_menu = "
            SELECT DISTINCT menu, iconcls
            FROM modules
            WHERE module_id IN ($sql_where_in_module_id)
                AND active = 1
                AND onmenu = 1
                AND menu <> 'Root'
                ORDER BY menu
            ";
        $stmt = $this->dbFw->prepare($sql_menu);
        if (!$stmt) {
            print_r($this->dbFw->errorInfo());
        }
        $stmt->execute();
        $armenu = array();
        $armenu_icon = array();
        $armenuall = array();
        while ($rowmenu = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $arr_path = explode('/', $rowmenu['menu']);
            $armenu[] = $arr_path[0];
            $armenu_icon[] = $rowmenu['iconcls'];
            $armenuall[] = $rowmenu['menu'];
        }
        $armenu = array_unique($armenu);
        sort($armenu);

        $i = 0;

        // Agar lokasi relatif terhadap file meski aplikasi jadi subdomain, domain atau dari path
        $url = 'http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $root_path = dirname(parse_url($url, PHP_URL_PATH));
        if ($root_path == '/') $root_path = '';

        foreach($armenu as $path) {
            if (strtolower($path) !== 'root') {
                $hasil = new stdclass();
                $hasil->id = $idtree.'.'.$i;
                $hasil->text = $this->hilangkan_no_urut($path);
                $hasil->expanded = false;
                $hasil->iconcls = false;
                $hasil->leaf = false;
                $hasil->parent1 = 1;
                $i++;
                $arr_hasil2 = array();
                foreach($armenuall as $value) {
                    $arr_path = explode('/', $value);
                    if ($arr_path[0] == $path) {
                        if ($arr_path[1]) {
                            $hasil2 = new stdclass();
                            $hasil2->id = $idtree.'.'.$i;
                            $hasil2->text = $this->hilangkan_no_urut($arr_path[1]);
                            /*$hasil2->iconCls = 'icon-folder-app';*/
                            if ($hasil2->text === 'RKPD') {
                                $hasil2->icon = $root_path.'/resources/menu24/news-orange.png';
                            }
                            elseif($hasil2->text === 'PPAS') {
                                $hasil2->icon = $root_path.'/resources/menu24/news-green.png';
                            } else {
                                $hasil2->icon = $root_path.'/resources/menu24/folder.png';
                            }


                            $hasil2->expanded = false;
                            $hasil2->leaf = false;
                            $hasil2->parent2 = 1;
                            $i++;
                            $sql_module = "SELECT * from modules where module_id in ($sql_where_in_module_id)  and active=1 and onmenu=1 and menu='".$path.'/'.$arr_path[1].'/'."' ORDER BY NAME";
                            $stmt = $this->dbFw->prepare($sql_module);
                            $stmt->execute();
                            $arr_hasil3 = array();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $hasil3 = new stdclass();
                                $hasil3->id = $row['module_id'];
                                $hasil3->text = $this->hilangkan_no_urut($row['name']);
                                $hasil3->qtip = $row['description'];
                                $hasil3->module = $row['module'];
                                $hasil3->onview = $row['onview'];
                                $hasil3->child = 2;
                                /*$hasil3->iconCls = $row['iconcls'];*/
                                //$hasil3->icon = $root_path . '/modules/'.$row['module'].'/icon.png'; //$row['iconcls'];
                                $hasil3->icon = $root_path.'/modules/'.$row['module'].'/'.$row['icon'];
                                $hasil3->leaf = true;
                                $arr_hasil3[] = $hasil3;
                            }
                            if (count($arr_hasil3) > 0) {
                                $hasil2->children = $arr_hasil3;
                            }
                            $arr_hasil2[] = $hasil2;
                        } else {
                            $sql_module = "SELECT * from modules where module_id in ($sql_where_in_module_id)  and onmenu=1 and menu='".$path.'/'."' ORDER BY NAME";
                            /*echo $sql_module;*/
                            $stmt = $this->dbFw->prepare($sql_module);
                            $stmt->execute();
                            $arr_hasil2 = array();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $hasil2 = new stdclass();
                                $hasil2->id = $row['module_id'];
                                $hasil2->text = $this->hilangkan_no_urut($row['name']);
                                $hasil2->module = $row['module'];
                                $hasil2->qtip = $row['description'];
                                $hasil2->onview = $row['onview'];
                                $hasil2->child = 1;
                                /*$hasil2->iconCls = $row['iconcls'];*/
                                //$hasil2->icon = $root_path . '/modules/'.$row['module'].'/icon.png'; /*$row['iconcls'];*/
                                $hasil2->icon = $root_path.'/modules/'.$row['module'].'/'.$row['icon'];
                                $hasil2->leaf = true;
                                $arr_hasil2[] = $hasil2;
                            }
                        }
                    }
                }
                if (count($arr_hasil2) > 0) {
                    $hasil->children = $arr_hasil2;
                }
                $arr_hasil[] = $hasil;
            }
        }


        $data = json_encode($arr_hasil);
        if ($returnArray) {
            return $arr_hasil;
        } else {
            echo $data;
        }
    }

    function get_user_oto() {
        $params = isset($_GET) ? $_GET : $_POST;
        $user_id = $params['_user_id'];
        /* $user_id = $this->getUserLogin(); */

        $arr_hasil = array();
        $sql = "SELECT id, module_id, action_id
                FROM user_has_actions
                WHERE user_id = '$user_id'

                UNION

                SELECT id, module_id, action_id
                FROM group_has_actions
                WHERE group_id IN (
                        SELECT group_id
                        FROM group_has_users
                        WHERE user_id = '$user_id'
                        )
                ";
        $stmt = $this->dbFw->prepare($sql);
        $stmt->execute();
        $total = $stmt->rowCount();
        if ($total > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hasil = new stdclass();
                $hasil->id = $row['id'];
                // $hasil->user_id = $row['user_id'];
                $hasil->module_id = $row['module_id'];
                $hasil->action_id = $row['action_id'];
                $arr_hasil[] = $hasil;
            }
            $data = json_encode($arr_hasil);
            $result = '{"success" : true, "total":'.$total.', "result":'.$data.'}';
        } else {
            $result = '{"success" : false, "msg":"Gagal mengambil data otoritas aksi user"}';
        }
        echo $result;
    }

    function getMessagePublic() {
        $params = isset($_GET) ? $_GET : $_POST;
        $user_id = $params['_user_id'];
        /*$user_id = $this->getUserLogin();*/
        $arr_hasil = array();
        $sqlMessage = "select * from message where time_end >= NOW() LIMIT 0,1 ";
        $stmt = $this->dbFw->prepare($sqlMessage);
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $msgID = $row['msg_id'];
                $isSeen = true;
                $sql = "select * from user_has_messages where user_id='$user_id' and msg_id=$msgID and is_seen=0 ";
                //echo $sql; exit();
                $stmt2 = $this->dbFw->prepare($sql);
                $stmt2->execute();

                while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                    $isSeen = false;
                    $sql2 = "update user_has_messages set is_seen=1, time_seen=NOW() where user_id='$user_id' and msg_id=$msgID and is_seen=0 ";
                    //echo $sql2; exit();
                    $stmt3 = $this->dbFw->prepare($sql2);
                    $stmt3->execute();
                }
                if (!$isSeen) {
                    $msg = $row['msg'];
                    $result = '{"success" : true, "msg":"'.$msg.'"}';
                } else {
                    $result = '{"success" : false}';
                }
            }
        } else {
            $result = '{"success" : false}';
        }
        echo $result;
    }

}
