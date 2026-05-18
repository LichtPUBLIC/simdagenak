<?php
class MappingMatrixUnit extends Database {
    function __construct() {
        parent::__construct();
    }

    public function ACTION_listAvailable() {
        $params = isset($_GET) ? $_GET : $_POST;
        $id_instansi = (int)$params['id_instansi'];
        
        $sql = "SELECT id_data_pilah as id, judul_data_pilah as text 
                FROM data_pilah 
                WHERE id_data_pilah NOT IN (
                    SELECT id_data_pilah FROM mapping_matrix_unit WHERE id_instansi = $id_instansi
                )";
        echo $this->dbDataSelectAndReturnAll($sql);
    }

    public function ACTION_listAssigned() {
        $params = isset($_GET) ? $_GET : $_POST;
        $id_instansi = (int)$params['id_instansi'];
        
        $sql = "SELECT m.id_data_pilah as id, m.judul_data_pilah as text 
                FROM data_pilah m
                JOIN mapping_matrix_unit map ON m.id_data_pilah = map.id_data_pilah
                WHERE map.id_instansi = $id_instansi";
        echo $this->dbDataSelectAndReturnAll($sql);
    }

    public function ACTION_assign() {
        $params = isset($_GET) ? $_GET : $_POST;
        $id_data_pilah = (int)$params['id_data_pilah'];
        $id_instansi = (int)$params['id_instansi'];
        
        $sql = "INSERT IGNORE INTO mapping_matrix_unit (id_data_pilah, id_instansi) VALUES ($id_data_pilah, $id_instansi)";
        echo $this->dbDataExecute($sql);
    }

    public function ACTION_unassign() {
        $params = isset($_GET) ? $_GET : $_POST;
        $id_data_pilah = (int)$params['id_data_pilah'];
        $id_instansi = (int)$params['id_instansi'];
        
        $sql = "DELETE FROM mapping_matrix_unit WHERE id_data_pilah = $id_data_pilah AND id_instansi = $id_instansi";
        echo $this->dbDataExecute($sql);
    }
}
