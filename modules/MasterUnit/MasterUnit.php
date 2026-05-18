<?php
class MasterUnit extends Database {
    function __construct() { parent::__construct(); }

    public function ACTION_list() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "SELECT id, id_instansi, nama_instansi FROM reff_unit_kerja";
        
        $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);
        $array = array();
        $countData = count($arrayData);
        $array['recordsTotal'] = $countData;
        $array['recordsFiltered'] = $countData;
        $array['draw'] = isset($_POST['draw']) ? $_POST['draw'] : 1;
        $array['data'] = (array)$arrayData;
        echo json_encode($array);
    }

    public function ACTION_add() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "INSERT INTO reff_unit_kerja (id_instansi, nama_instansi) VALUES (:id_instansi, :nama_instansi)";
        echo $this->dbDataExecute($sql, $params['data']);
    }

    public function ACTION_update() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "UPDATE reff_unit_kerja SET id_instansi=:id_instansi, nama_instansi=:nama_instansi WHERE id=:id";
        echo $this->dbDataExecute($sql, $params['data']);
    }

    public function ACTION_delete() {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "DELETE FROM reff_unit_kerja WHERE id=:id";
        echo $this->dbDataExecute($sql, $params['data']);
    }
}
