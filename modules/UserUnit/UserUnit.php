<?php

class UserUnit extends Database
{

    function __construct()
    {
        parent::__construct();
    }

    public function ACTION_userList($ret = false) {
        $sql  = "SELECT * FROM users";
        if($ret) {
            return $this->dbDataSelectAndReturnAll($sql,null,true);
        } else {
            echo $this->dbDataSelectAndReturnAll($sql);
        }
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
            "kode_instansi","kode_indikator"
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

    public function ACTION_list_old($return=false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        if(isset($_POST['kode_instansi'])){
            $kodeInstansi = $_POST['kode_instansi'];
        }else{
            $kodeInstansi = '';
        }
        $sql = 'SELECT * FROM ref_sumberdata';
        if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sql .= " where ".$criteria;
            $sql.=" and kode_sumberdata not in
                     (select kode_sumberdata from instansi_has_sumberdata 
                     where kode_instansi='$kodeInstansi')";
        }else{
            $sql.=" where kode_sumberdata not in
                     (select kode_sumberdata from instansi_has_sumberdata 
                     where kode_instansi='$kodeInstansi')";
        }
        if (isset($_POST['start'])) {
            $start = $_POST['start'];
            $limit = $_POST['length'];
            $sql .= " limit $start,$limit ";
        }
//        echo $sql;exit();
        $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);
        if($return){
            return $arrayData;
        }
        $array = array();
        $sqlCount = "SELECT count(*) FROM instansi_has_indikator ";
        if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sql .= " where ".$criteria;
            $sql.=" and kode_sumberdata not in
                     (select kode_sumberdata from instansi_has_sumberdata 
                     where kode_instansi='$kodeInstansi')";
        }else{
            $sql.=" where kode_sumberdata not in
                     (select kode_sumberdata from instansi_has_sumberdata 
                     where kode_instansi='$kodeInstansi')";
        }

        $countData = $this->dbDataGetValue($sqlCount);
        $array['recordsTotal'] = $countData;
        $array['recordsFiltered'] = $countData;
        $array['draw'] = $_POST['draw'];
        $array['data'] = (array)$arrayData;
        echo json_encode($array);
    }

    public function ACTION_list($return=false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $currYear = $_POST['year'];
//        $userUnit = $this->ACTION_getUserInstansi(true);
        $data['app'] = 'teppa';
        $data['module'] = 'getListInstansi';
        $data['action'] = 'read';
        $data['json'] = '{%22tahun%22:%22'.$currYear.'%22}';
        $response = $this->requestApi($data);
        $response = json_decode($response);
        $response = $response->result;
        $userId = $_POST['user_id'];

        $sqlUserInstansi = "select id_unit_kerja from user_has_unit where user_id='$userId'";
        $arrayDataInstansi = $this->dbDataSelectAndReturnAll($sqlUserInstansi, $params, true);
        $arrayDataInstansi2 = array();
        foreach ($arrayDataInstansi as $val){
            array_push($arrayDataInstansi2,$val->id_unit_kerja);
        }
        $newData = array();
//        print_r((array)$arrayDataInstansi2);exit;
        foreach ($response as $val){
            if(!in_array($val->id_instansi,$arrayDataInstansi2)){
                array_push($newData,$val);
            }
        }
//        print_r($newData);exit;
        $array= array();
        $countData = count($newData);
        $array['recordsTotal'] = $countData;
        $array['recordsFiltered'] = $countData;
        $array['draw'] = $_POST['draw'];
        $array['data'] = (array)$newData;
        echo json_encode($array);
    }

    public function ACTION_list2($return=false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $currYear = $_POST['year'];
//        $userUnit = $this->ACTION_getUserInstansi(true);
        $data['app'] = 'teppa';
        $data['module'] = 'getListInstansi';
        $data['action'] = 'read';
        $data['json'] = '{%22tahun%22:%22'.$currYear.'%22}';
        $response = $this->requestApi($data);
        $response = json_decode($response);
        $response = $response->result;
        $userId = $_POST['user_id'];

        $sqlUserInstansi = "select id_unit_kerja from user_has_unit where user_id='$userId'";
        $arrayDataInstansi = $this->dbDataSelectAndReturnAll($sqlUserInstansi, $params, true);
        $arrayDataInstansi2 = array();
        foreach ($arrayDataInstansi as $val){
            array_push($arrayDataInstansi2,$val->id_unit_kerja);
        }
        $newData = array();
//        print_r((array)$arrayDataInstansi2);exit;
        foreach ($response as $val){
            if(in_array($val->id_instansi,$arrayDataInstansi2)){
                array_push($newData,$val);
            }
        }
//        print_r($newData);exit;
        $array= array();
        $countData = count($newData);
        $array['recordsTotal'] = $countData;
        $array['recordsFiltered'] = $countData;
        $array['draw'] = $_POST['draw'];
        $array['data'] = (array)$newData;
        echo json_encode($array);
    }

    public function ACTION_list2_old($return=false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        if(isset($_POST['kode_instansi'])){
            $kodeInstansi = $_POST['kode_instansi'];
        }else{
            $kodeInstansi = '';
        }
        $sql = 'SELECT * FROM ref_sumberdata';
        if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sql .= " where ".$criteria;
            $sql.=" and kode_sumberdata in(select kode_sumberdata from instansi_has_sumberdata 
            where kode_instansi='$kodeInstansi')";
        }else{
            $sql.=" where kode_sumberdata in(select kode_sumberdata from instansi_has_sumberdata 
            where kode_instansi='$kodeInstansi')";

        }
//        echo $sql;exit();
        if (isset($_POST['start'])) {
            $start = $_POST['start'];
            $limit = $_POST['length'];
            $sql .= " limit $start,$limit ";
        }

        $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);
        if($return){
            return $arrayData;
        }
        $array = array();
        $sqlCount = "SELECT count(*) FROM instansi_has_sumberdata ";
        if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sql .= " where ".$criteria;
            $sql.=" and kode_sumberdata in(select kode_sumberdata from instansi_has_sumberdata 
            where kode_instansi='$kodeInstansi')";
        }else{
            $sql.=" where kode_sumberdata in(select kode_sumberdata from instansi_has_sumberdata 
            where kode_instansi='$kodeInstansi')";

        }
        $countData = $this->dbDataGetValue($sqlCount);
        $array['recordsTotal'] = $countData;
        $array['recordsFiltered'] = $countData;
        $array['draw'] = $_POST['draw'];
        $array['data'] = (array)$arrayData;
        echo json_encode($array);
    }


    public function ACTION_tambahkan(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "insert into user_has_unit (user_id,id_unit_kerja,kode_unit_kerja) VALUES 
                                          (:user_id,:id_instansi,:kode_instansi)";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_update(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "update instansi_has_indikator set kode_instansi=:kode_instansi,kode_indikator=:kode_indikator
                 WHERE id=:id";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_delete(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "delete from user_has_unit 
                 WHERE kode_unit_kerja=:kode_instansi 
                 AND id_unit_kerja=:id_instansi
                 and user_id=:user_id";
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

    public function ACTION_getUserInstansi() {
        $params = isset($_GET) ? $_GET : $_POST;
        $os =  new Os;
        $unit = $os->getUserUnits();
        $unit = json_decode($unit);
        $userUnit = array();

        for ($i = 0; $i < count($unit);$i++) {
            if ($unit[$i]->id_unit < 10) {
                $unit[$i]->id_unit = sprintf('%02d', $unit[$i]->id_unit);
            }
            array_push($userUnit, $unit[$i]->id_unit);
        }
        return $userUnit;
    }
    public function ACTION_getListInstansi() {
        $params = isset($_GET) ? $_GET : $_POST;
        $currYear = $params['year'];
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

        echo json_encode($return);
    }

    /*public function ACTION_pdf()
    {
        $data['value'] = $this->ACTION_list(true);
        $i=0;
        $data['judul'] = "Export data pdf";
        $pdf = $this->createHtml2Pdf();
        $pdf->setPageSize(210, 330, 'P'); // width, height, orientation = [P]ortrait [L]anscape
        $pdf->setMargins(7, 7, 10, 15); // left, right, top, bottom (milimeter)
        $pdf->mpdf->defaultfooterline = 0;
        $pdf->addHtmlFile('tpl_pdf.html', $data);
        $pdf->savePdf("Data-pdf.pdf");
    }*/
}




