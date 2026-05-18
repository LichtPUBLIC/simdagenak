<?php

class UserGroups extends Database {

    function __construct() {
        parent::__construct();
    }

    public function group_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "SELECT * FROM `groups` ";
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    }

    public function user_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = 'SELECT
                	g.id gid, g.group_id, g.description, g.active,
                	u.id uid, u.user_id, u.username, u.nama, u.active, u.email
                FROM `groups` g
                LEFT JOIN group_has_users gu USING (group_id)
                LEFT JOIN users u USING (user_id)
                WHERE g.group_id = :par1 AND username IS NOT null
                ORDER BY g.id, u.id';
        echo $this->dbFwSelectAndReturnAll($sql,$params);
    }

    public function sample_edit() {
        echo '{"success": true,"msg" : "Dummy Edit berhasil.."}';
    }
}
