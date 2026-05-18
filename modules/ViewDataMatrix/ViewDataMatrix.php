<?php
class ViewDataMatrix extends Database {
    function __construct() {
        parent::__construct();
    }

    public function ACTION_listAll() {
        $sql = "SELECT * FROM data_pilah WHERE aktif = 1";
        $data = $this->dbDataSelectAndReturnAll($sql, null, true);
        echo json_encode(array('success' => true, 'data' => $data));
    }
}
