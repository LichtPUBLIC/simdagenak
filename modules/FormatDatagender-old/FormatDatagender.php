<?php

class FormatDatagender extends Database
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
            "title","grup_format"
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
        $sql = 'SELECT * FROM format_datagender';
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
        if($return){
            return $arrayData;
        }
        $array = array();
        $sqlCount = "SELECT count(*) FROM format_datagender ";
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
        $sql = 'SELECT * FROM format_datagender';
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
        $sql = "insert into format_datagender (title,grup_format) VALUES 
                                          (:title,:grup_format)";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    private function format_1 ($params){
//        print_r($params);exit;
        $tahun = $params['tahun'];
        $idFormat = $params['id_format'];
        $sqlCek = "select count(*) from data_per_format where tahun=:tahun and id_format=:id_format";
        $hasil = $this->dbDataGetValue($sqlCek,$params);
        if($hasil == 0){
            $kec = array('Berbah',
                'Cangkringan',
                'Depok',
                'Gamping',
                'Godean',
                'Kalasan',
                'Minggar',
                'Mlati',
                'Moyudan',
                'Ngaglik',
                'Ngemplak',
                'Pakem',
                'Prambanan',
                'Sayegan',
                'Sleman',
                'Tempel',
                'Turi');
            $values = array();
            foreach ($kec as $val){
                $rowId = md5($val.date('YMD:his'));
                $eachVal = "($idFormat,$tahun,'keterangan','$val',0,0,-1,'$rowId')";
                array_push($values,$eachVal);
            }
            $values = implode(',',$values);
            $sqlInsert = "insert into data_per_format (id_format, tahun, kolom, baris, count_l, count_p, count_lp,row_id) VALUES 
                                                       $values
                                                       ";
            $this->dbDataExecute($sqlInsert);
        }
        $sql = "select 
                data_per_format.id_data,
                data_per_format.id_format,
                data_per_format.tahun,
                data_per_format.kolom,
                data_per_format.baris keterangan,
                data_per_format.count_l,
                data_per_format.count_p,
                data_per_format.count_lp,
                data_per_format.row_id,
                format_datagender.title from data_per_format 
                inner join format_datagender on format_datagender.id_format = data_per_format.id_format
                where data_per_format.tahun=:tahun and data_per_format.id_format=:id_format";
        echo $this->dbDataSelectAndReturnAll($sql,$params);
    }

    private function format_2 ($params){
//        print_r($params);exit;
        $tahun = $params['tahun'];
        $idFormat = $params['id_format'];
        $sqlCek = "select count(*) from data_per_format where tahun=:tahun and id_format=:id_format";
        $hasil = $this->dbDataGetValue($sqlCek,$params);
        $rowId='';
        $kec = array('Berbah',
                     'Cangkringan',
                     'Depok',
                     'Gamping',
                     'Godean',
                     'Kalasan',
                     'Minggar',
                     'Mlati',
                     'Moyudan',
                     'Ngaglik',
                     'Ngemplak',
                     'Pakem',
                     'Prambanan',
                     'Sayegan',
                     'Sleman',
                     'Tempel',
                     'Turi');
        $values = array();

        if($hasil == 0){
            foreach ($kec as $val){
                $rowId = md5($val.date('YMD:his'));
                $eachVal = "($idFormat,$tahun,'keterangan','".$val."',0,0,0,'$rowId'),
                         ($idFormat,$tahun,'jml_panti',0,0,0,0,'$rowId')";
                array_push($values,$eachVal);
            }
            $values = implode(',',$values);
            $sqlInsert = "insert into data_per_format (id_format, tahun, kolom, baris, count_l, count_p, count_lp,row_id) VALUES 
                                                       $values
                                                       ";
//            echo $sqlInsert;exit;
            $this->dbDataExecute($sqlInsert);
        }
        $sql = "SELECT 
                     (SELECT baris FROM data_per_format WHERE kolom='keterangan' AND row_id = a.`row_id`) keterangan,
                     (SELECT baris FROM data_per_format WHERE kolom='jml_panti' AND row_id = a.`row_id`) jml_panti,
                     (SELECT count_l FROM data_per_format WHERE kolom='jml_panti' AND row_id = a.`row_id`) count_l,
                     (SELECT count_p FROM data_per_format WHERE kolom='jml_panti' AND row_id = a.`row_id`) count_p,
                     (SELECT count_lp FROM data_per_format WHERE kolom='jml_panti' AND row_id = a.`row_id`) count_lp,
                     b.title,a.row_id,a.tahun FROM data_per_format a
                INNER JOIN format_datagender b ON b.id_format = a.id_format
                where a.tahun=:tahun and a.id_format=:id_format GROUP BY a.row_id";

        /*$sql = "select data_per_format.*,format_datagender.title from data_per_format a
                inner join format_datagender b on b.id_format = a.id_format
                where a.tahun=:tahun and a.id_format=:id_format";*/
        echo $this->dbDataSelectAndReturnAll($sql,$params);
    }

    private function format_3 ($params){
//        print_r($params);exit;
        $tahun = $params['tahun'];
        $idFormat = $params['id_format'];
        $sqlCek = "select count(*) from data_per_format where tahun=:tahun and id_format=:id_format";
        $hasil = $this->dbDataGetValue($sqlCek,$params);
        $rowId='';
        $kec = array('Berbah',
                     'Cangkringan',
                     'Depok',
                     'Gamping',
                     'Godean',
                     'Kalasan',
                     'Minggar',
                     'Mlati',
                     'Moyudan',
                     'Ngaglik',
                     'Ngemplak',
                     'Pakem',
                     'Prambanan',
                     'Sayegan',
                     'Sleman',
                     'Tempel',
                     'Turi');
        $values = array();

        if($hasil == 0){
            foreach ($kec as $val){
                $rowId = md5($val.date('YMD:his'));
                $eachVal = "($idFormat,$tahun,'keterangan','".$val."',0,0,0,'$rowId'),
                            ($idFormat,$tahun,'forum_komunikasi',0,0,0,0,'$rowId'),
                            ($idFormat,$tahun,'ypac',0,0,0,0,'$rowId'),
                            ($idFormat,$tahun,'aliansi_rbm',0,0,0,0,'$rowId'),
                            ($idFormat,$tahun,'jumlah',0,0,0,0,'$rowId')
                            ";
                array_push($values,$eachVal);
            }
            $values = implode(',',$values);
            $sqlInsert = "insert into data_per_format (id_format, tahun, kolom, baris, count_l, count_p, count_lp,row_id) VALUES 
                                                       $values
                                                       ";
//            echo $sqlInsert;exit;
            $this->dbDataExecute($sqlInsert);
        }
        $sql = "SELECT 
                     (SELECT baris FROM data_per_format WHERE kolom='keterangan' AND row_id = a.`row_id`) keterangan,
                     (SELECT baris FROM data_per_format WHERE kolom='forum_komunikasi' AND row_id = a.`row_id`) forum_komunikasi,
                     (SELECT count_l FROM data_per_format WHERE kolom='ypac' AND row_id = a.`row_id`) ypac,
                     (SELECT count_p FROM data_per_format WHERE kolom='aliansi_rbm' AND row_id = a.`row_id`) aliansi_rbm,
                     (SELECT count_lp FROM data_per_format WHERE kolom='jumlah' AND row_id = a.`row_id`) jumlah,
                     b.title,a.row_id,a.tahun FROM data_per_format a
                INNER JOIN format_datagender b ON b.id_format = a.id_format
                where a.tahun=:tahun and a.id_format=:id_format GROUP BY a.row_id";

        /*$sql = "select data_per_format.*,format_datagender.title from data_per_format a
                inner join format_datagender b on b.id_format = a.id_format
                where a.tahun=:tahun and a.id_format=:id_format";*/
        echo $this->dbDataSelectAndReturnAll($sql,$params);
    }

    public function ACTION_getDataFormat(){
        $params = isset($_GET) ? $_GET : $_POST;
        $formatClass = str_replace('-','_',$params['grup_format']);
        $this->$formatClass($params);
    }
/*
 * id_data1
 * id_data1*/
    public function ACTION_UpdateDataFormat(){
        $params = isset($_GET) ? $_GET : $_POST;
        $tahun = $params['data']['tahun'];
        $idFormat = $params['data']['id_format'];
        $x=1;
        $a=1;
        while ($x==1){
            if(!isset($params['data']['keterangan'.$a])){break;}
            $rowId = $params['data']['row_id'.$a];
            $l = $params['data']['l'.$a];
            $p = $params['data']['p'.$a];
            $lp =$l+$p ;
            $sqlselectCol = "select kolom from data_per_format where row_id='$rowId'";
            $kolom = $this->dbDataSelectAndReturnAll($sqlselectCol,null,true);
            foreach ($kolom as $col){
                $baris = $params['data'][$col->kolom.$a];
                $sql = "update data_per_format set 
                          baris='$baris',
                          count_l=$l,
                          count_p=$p,
                          count_lp=$lp
                 WHERE row_id='$rowId' and kolom = '$col->kolom'";
                $this->dbDataExecute($sql,$params['data']);
            }
        $a++;
        }
        echo '{"success":true}';

    }

    public function ACTION_update(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "update format_datagender set title=:title,grup_format=:grup_format
                 WHERE id_format=:id_format";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_delete(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "delete from format_datagender WHERE id_format=:id_format";
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




