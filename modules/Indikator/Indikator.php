<?php

class Indikator extends Database
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
            "kode_aspek","kode_sub_aspek","kode_indikator","indikator"
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

    private function calcString($str)
    {
        $patten = '/[\*\/\+-]/';
        preg_match($patten,$str, $operator);
        $arr = preg_split($patten,$str);

        switch($operator[0]){
            case '-':
                return $arr[0] - $arr[1];
            case '+':
                return $arr[0] + $arr[1];
            case '*':
                return $arr[0] * $arr[1];
            case '/':
                return $arr[0] / $arr[1];
        }
    }

    public function ACTION_list($return=false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $sql = 'SELECT ref_indikator.*,
                       ref_kategori.kategori,
                       ref_aspek.aspek,
                       ref_sub_aspek.sub_aspek
                  FROM ref_indikator 
                  inner JOIN ref_kategori on ref_kategori.kode_kategori = ref_indikator.kode_kategori
                  INNER JOIN ref_aspek ON ref_aspek.kode_aspek = ref_indikator.kode_aspek
                  INNER JOIN ref_sub_aspek on ref_sub_aspek.kode_sub_aspek = ref_indikator.kode_sub_aspek
                   ';

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
//        echo $sql;exit();
        $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);

        $s=0;
        foreach ($arrayData as $val){
            /*if($val->operasi_math_query){
                $q2016 = '('.str_replace('paramtahun','2016',$val->operasi_math_query).')';
                $arrayData[$s]->t2016 = number_format((float)$this->dbDataGetValue($q2016), 2, '.', '');
                $q2017 = '('.str_replace('paramtahun','2017',$val->operasi_math_query).')';
                $arrayData[$s]->t2017 = number_format((float)$this->dbDataGetValue($q2017), 2, '.', '');
            }else{
                $arrayData[$s]->t2016 = 0.00;
                $arrayData[$s]->t2017 = 0.00;
            }*/
            $arrayData[$s]->t2016 = 0.00;
            $arrayData[$s]->t2017 = 0.00;
            $s++;
        }
//        print_r($return);exit();
        if($return){

            return $arrayData;
        }
        $array = array();
        $sqlCount = "SELECT count(*) FROM ref_indikator ";
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
        $sql = 'SELECT * FROM ref_indikator limit 5';
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
        $data = $this->dbDataSelectAndReturnAll($sql, $params,true);
        if($return){
            return $data;
            exit();
        }
        $i=0;
        foreach ($data as $val){
            $sqlKet = "select indikator_has_sumberdata.*,ref_sumberdata.sumberdata 
                          from indikator_has_sumberdata 
                          INNER JOIN ref_sumberdata ON ref_sumberdata.kode_sumberdata = indikator_has_sumberdata.simbol_sumber_data
                          WHERE kode_indikator='".$val->kode_indikator."'";
            $dataKet = $this->dbDataSelectAndReturnAll($sqlKet,null,true);
            $data[$i]->keterangan = $dataKet;
            $i++;
        }
        $returnData = array();
        $returnData['success']=true;
        $returnData['msg']="berhasil";
        $returnData['result']=$data;
            echo json_encode($returnData);

    }

    public function ACTION_getKategori(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "select * from ref_kategori";
        echo $this->dbDataSelectAndReturnAll($sql);
    }

    public function ACTION_getAspek(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "select * from ref_aspek";
        echo $this->dbDataSelectAndReturnAll($sql);
    }

    public function ACTION_getSubAspek(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "select * from ref_sub_aspek";
        echo $this->dbDataSelectAndReturnAll($sql);
    }

    public function ACTION_add(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "insert into ref_indikator (kode_aspek,kode_sub_aspek,kode_indikator,indikator) VALUES 
                                          (:kode_aspek,:kode_sub_aspek,:kode_indikator,:indikator)";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_update(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "update ref_indikator set kode_aspek=:kode_aspek,kode_sub_aspek=:kode_sub_aspek,kode_indikator=:kode_indikator,indikator=:indikator
                 WHERE kode_kategori=:kode_kategori";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_delete(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "delete from ref_indikator WHERE kode_kategori=:kode_kategori";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_pdf()
    {
        $data['value'] = $this->ACTION_listPrint(true);
        $i = 0;
        $data['judul'] = "Export data pdf";

        $i=0;
        foreach ($data['value'] as $val){
            $sqlKet = "select indikator_has_sumberdata.*,ref_sumberdata.sumberdata 
                          from indikator_has_sumberdata 
                          INNER JOIN ref_sumberdata ON ref_sumberdata.kode_sumberdata = indikator_has_sumberdata.simbol_sumber_data
                          WHERE kode_indikator='".$val->kode_indikator."'";
            $dataKet = $this->dbDataSelectAndReturnAll($sqlKet,null,true);

            $keterangan = '<ul>';
            foreach($dataKet as $ket){
                $keterangan .= '<li><b>'.$ket->simbol_temp.' : </b>'.$ket->sumberdata.'</li>';
            }
            $keterangan .= '</ul>';
            $data['value'][$i]->keterangan = $keterangan;
            $i++;
        }
//        print_r($data);
//        exit();

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


    public function ACTION_pdf2()
    {
        $data['value'] = $this->ACTION_list(true);
        $i=0;
        $data['judul'] = "Export data pdf";
        $pdf = $this->createHtml2Pdf_old();
        $pdf->setPageSize(210, 330, 'P'); // width, height, orientation = [P]ortrait [L]anscape
        $pdf->setMargins(7, 7, 10, 15); // left, right, top, bottom (milimeter)
        $pdf->mpdf->defaultfooterline = 0;
        $pdf->addHtmlFile('tpl_pdf.html', $data);
        $pdf->savePdf("Data-pdf.pdf");
    }
}




