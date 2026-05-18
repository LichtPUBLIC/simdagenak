<?php

class DataPerInstansi extends Database
{

    function __construct()
    {
        parent::__construct();
    }

    private function get_userId()
    {
        $user = new os;
        $userData = $user->getUserData();
        $userDataArr = json_decode($userData);
        $userId = $userDataArr->user_id;
        return $userId;
    }

    private function findField() {
        $findField = [
            "deskripsi","id_instansi","jenis_data"
        ];
        return $findField;
    }

    private function buildSqlSearchingCriteria($keywords, $findField) {
        $arrayKata = explode(' ', $keywords);
        foreach ($arrayKata as $hasil) {
            foreach ($findField as $fieldName) {
                $criteria[] = "LOWER($fieldName) like '%$hasil%'";
            }
            $queryCriteria[] = implode(" OR ", $criteria);
        }
        $resultCriteria = implode(" OR ", $queryCriteria);
        return $resultCriteria;
    }

    public function ACTION_list($return=false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $instansiList = $this->ACTION_getListInstansi(true);
        $instansi = array();
        foreach ($instansiList as $val){
            $instansi[$val->id_instansi] = $val->instansi;
        }
//        print_r($instansi);exit;
        $sql = 'SELECT * FROM data_per_instansi';
        if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sql .= " where ".$criteria;
        }
        if (isset($_POST['start'])) {
            $start = $_POST['start'];
            $limit = $_POST['length'];
            $sql .= " limit $start,$limit ";
        }

        $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);
//        print_r($arrayData);exit;
        $c=0;
        foreach ($arrayData as $data){
            $arrayData[$c]->instansi = $instansi[$data->id_instansi];
            $c++;
        }
        if($return){
            return $arrayData;
        }
        $array = array();
        $sqlCount = "SELECT count(*) FROM data_per_instansi ";
        if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sql .= " where ".$criteria;
        }

        $countData = $this->dbDataGetValue($sqlCount);
        $array['recordsTotal'] = $countData;
        $array['recordsFiltered'] = $countData;
        $array['draw'] = $_POST['draw'];
        $array['data'] = (array)$arrayData;
        echo json_encode($array);
    }

    public function ACTION_listPrint($return = false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $sql = 'SELECT * FROM data_per_instansi';
        if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sql .= " where ".$criteria;
        }
        if (isset($_POST['start'])) {
            $start = $_POST['start'];
            $limit = $_POST['length'];
            $sql .= " limit $start,$limit ";
        }
        echo $this->dbDataSelectAndReturnAll($sql, $params);
    }

    public function ACTION_add(){
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
//        print_r($userId);exit;
        $sql = "insert into data_per_instansi (deskripsi,
                                                id_instansi,
                                                jenis_data,
                                                user_input
                                                  ) VALUES 
                                              (:deskripsi,
                                              :id_instansi,
                                              :jenis_data,
                                              '$userId')";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_update(){
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $sql = "update data_per_instansi set 
                    deskripsi=:deskripsi,
                    id_instansi=:id_instansi,
                    jenis_data=:jenis_data,
                    user_update='$userId',
                    tgl_update=now()
                 WHERE id_data_per_instansi=:id_data_per_instansi";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_delete(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "delete from data_per_instansi WHERE id_data_per_instansi=:id_data_per_instansi";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_pdf()
    {
        $data['value'] = $this->ACTION_list(true);
        $i = 0;
        $data['judul'] = "Export data pdf";

        /**
         * Untuk memanggil fungsi create html2pdf, ada 3 parameter yang dikirimkan yaitu :
         *  - module name
         *  - data
         *  - template html
         *
         * @contributor arkan
         * */
        $pdf = $this->createHtml2Pdf(null, $data, 'tpl_pdf.html');
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Arkan Herawan');
        $pdf->SetTitle('Contoh export pdf');
        $pdf->SetSubject('export pdf dengan digital signature');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

// set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_RIGHT, PDF_MARGIN_TOP);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// add a page
        $pdf->AddPage();

// print a some of text
        $pdf->writeHTML($pdf->content, true, 0, true, 0);

//Close and output PDF document
        $pdf->Output('export_pdf_file.pdf', 'D');
    }

    public function ACTION_getListInstansi($returnData = false) {
        $params = isset($_GET) ? $_GET : $_POST;
        $currYear = isset($params['year']) ? $params['year'] : 2018;
//        $userUnit = $this->ACTION_getUserInstansi(true);
        $data['app'] = 'teppa';
        $data['module'] = 'getListInstansi';
        $data['action'] = 'read';
        $data['json'] = '{%22tahun%22:%22'.$currYear.'%22}';
        $response = $this->requestApi($data);
        $response = json_decode($response);
        $response = isset($response->result) ? $response->result : null;
        if (empty($response)) {
            $response = $this->getLocalListInstansi();
        }
        $return = new stdClass();
//        $return->user_instansi = $userUnit;
        $return->list_instansi = $response;
        if($returnData){
            return $response;
        }else{
            echo json_encode($return);
        }
    }

}




