<?php
class InputDataMatrix extends Database {
    function __construct() {
        parent::__construct();
    }

    public function ACTION_listAssigned() {
        $os = new Os();
        $id_instansi = $os->getUserUnit();
        
        // List matrices mapped to this unit
        $sql = "SELECT m.*, i.nama_instansi as instansi
                FROM data_pilah m
                JOIN mapping_matrix_unit map ON m.id_data_pilah = map.id_data_pilah
                LEFT JOIN reff_unit_kerja i ON i.id = map.id_instansi
                WHERE map.id_instansi = $id_instansi";
        
        $data = $this->dbDataSelectAndReturnAll($sql, null, true);
        echo json_encode(array('success' => true, 'data' => $data));
    }
}
