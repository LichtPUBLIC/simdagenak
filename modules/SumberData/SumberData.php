<?php

class SumberData extends Database
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
            "kode_sumberdata","sumberdata","T2016","T2017"
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
        $sql = 'SELECT * FROM ref_sumberdata';
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
        $i=0;
        foreach ($arrayData as $val){
            $qTahun = "select * from data_per_tahun WHERE id_sumber_data = ".$val->id_sumberdata;
            $dataTahun = $this->dbDataSelectAndReturnAll($qTahun, $params, true);
            $th = 2016;
            $arrayData[$i]->datatahun=array();
            foreach ($dataTahun as $valTahun){
                $indextahun = "t$th";
                $arrayData[$i]->datatahun[$indextahun]=$valTahun->data;
                $th++;
            }
            $qIndikator = "select ref_indikator.indikator from indikator_has_sumberdata 
                              inner join ref_indikator on ref_indikator.kode_indikator=indikator_has_sumberdata.kode_indikator
                              WHERE indikator_has_sumberdata.simbol_sumber_data = ".$val->kode_sumberdata;
            $dataIndikator = $this->dbDataSelectAndReturnAll($qIndikator, $params, true);
            $indikatorArr = array();
            foreach ($dataIndikator as $val){
                $list = "<li>".$val->indikator."</li>";
                array_push($indikatorArr,$list);
            }
            $indikatorList = "<ul>".implode('',$indikatorArr)."</ul>";
            $arrayData[$i]->indikator_terlibat = $indikatorList;
//            print_r($indikatorList);exit();
            $i++;
        }
//        print_r($arrayData);exit();
        $array = array();
        $sqlCount = "SELECT count(*) FROM ref_sumberdata ";
        $countData = $this->dbDataGetValue($sqlCount);
        $array['recordsTotal'] = $countData;
        $array['recordsFiltered'] = $countData;
        $array['draw'] = $_POST['draw'];
        $array['data'] = (array)$arrayData;
        echo json_encode($array);
    }

    public function ACTION_listViewDataBulan(){
        $params = isset($_GET) ? $_GET : $_POST;
        $idSumberData = $_POST['id_sumber_data'];
        $sql = "select tahun from data_per_tahun group by tahun ORDER BY tahun ASC ";
        $dataTahun = $this->dbDataSelectAndReturnAll($sql,$_POST,true);
//        print_r($dataTahun);exit;
        $dataReturn = new stdClass();
        $result = array();
        foreach ($dataTahun as $thn){
            $tahun = $thn->tahun;
            $sqlGetData = "select $tahun tahun,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=1) jan,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=2) feb,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=3) mar,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=4) aprl,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=5) mei,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=6) jun,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=7) jul,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=8) agt,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=9) sept,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=10) okt,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=11) nov,
                           (select data from data_per_tahun where tahun=$tahun and id_sumber_data=$idSumberData and bulan=12) des
                           ";
            $dataku = $this->dbDataSelectAndReturnAll($sqlGetData,null,true);
            array_push($result,$dataku[0]);
        }
        $array = array();
        $countData = count($dataTahun);
        $array['recordsTotal'] = $countData;
        $array['recordsFiltered'] = $countData;
        $array['draw'] = $_POST['draw'];
        $array['data'] = $result;
        echo json_encode($array);

        /*$dataReturn->data = $result;
        $dataReturn->msg = 'sukses';
        $dataReturn->success = true;
        echo json_encode($dataReturn);*/
    }

    public function ACTION_listPrint($return = false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $sql = 'SELECT * FROM ref_sumberdata';
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
        $sql = "insert into ref_sumberdata (kode_sumberdata,sumberdata,T2016,T2017) VALUES 
                                          (:kode_sumberdata,:sumberdata,:T2016,:T2017)";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_update(){
        $params = isset($_GET) ? $_GET : $_POST;
        for($i=0;$i<12;$i++){
            $bulan = $i+1;
            $sqlCek = "select count(*) from data_per_tahun WHERE id_sumber_data=:id_sumberdata 
                       and tahun=:tahun and bulan=$bulan";
            $hasilCek = $this->dbDataGetValue($sqlCek,$params['data']);
//            echo $hasilCek;exit();
            if($hasilCek==0){
                $sql = "insert into data_per_tahun (tahun, data, id_sumber_data, bulan, lock_data, keterangan) VALUES 
                                                   (:tahun,:data$bulan,:id_sumberdata,$bulan,0,:ket$bulan)";
            }else{
                $sql = "update data_per_tahun set data = :data$bulan,
                                                  keterangan = :ket$bulan
                        WHERE id_sumber_data=:id_sumberdata AND tahun=:tahun AND bulan=$bulan";
            }
            $this->dbDataExecute($sql,$params['data']);
        }
        $sql = "update ref_sumberdata set 
                 kode_sumberdata=:kode_sumberdata,
                 sumberdata=:sumberdata
                 WHERE id_sumberdata=:id_sumberdata";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_delete(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "delete from ref_sumberdata WHERE id_sumberdata=:id_sumberdata";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function sumberData_kunci(){
        $params = isset($_GET) ? $_GET : $_POST;
        $lock = $params['locked'];
        if($lock=='true'){
            $lock = 1;
        }else{
            $lock=0;
        }
        $sql = "update data_per_tahun set lock_data=$lock 
                WHERE tahun=:tahun AND id_sumber_data=:id_sumberdata AND bulan=:bulan";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_getDataPerTahun(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "select * from data_per_tahun WHERE id_sumber_data=:id_sumber_data AND tahun=:tahun ORDER BY bulan ASC ";
        $data = $this->dbDataSelectAndReturnAll($sql,$params,true);
        $dataReturn = new stdClass();

        $result = array();
        for($i=0;$i<12;$i++){
            if(isset($data[$i])){
                $result["data".$data[$i]->bulan] = $data[$i]->data;
                $result["ket".$data[$i]->bulan] = $data[$i]->keterangan;
                $result["dikunci".$data[$i]->bulan] = $data[$i]->lock_data;
            }else{
                $index = $i+1;
                $result["data".$index] = 0;
                $result["ket".$index] = '';
                $result["dikunci".$index] = 0;
            }
        }
        $dataReturn->result = $result;
        $dataReturn->msg = 'sukses';
        $dataReturn->success = true;
        echo json_encode($dataReturn);
//        print_r($result);
    }
    /*cek apakah data tahun di lock atau enggak*/
    private function cek_lock($tahun,$bulan,$idSumberData){
        $sqlCek = "select lock_data from data_per_tahun 
                  WHERE tahun=$tahun and id_sumber_data=$idSumberData AND bulan=$bulan";
        return $this->dbDataGetValue($sqlCek);
    }
    public function ACTION_addDataTahun(){
        $params = isset($_GET) ? $_GET : $_POST;
        /*cek dulu apakah data sudah ada*/
        $sqlCek = "select count(*) from data_per_tahun WHERE tahun=:tahun and id_sumber_data=:id_sumberdata AND bulan=:bulan";
        $hasil = $this->dbDataGetValue($sqlCek,$params['data']);
        if($hasil==0){
            $sql = "insert into data_per_tahun (tahun, data, id_sumber_data,bulan,lock_data,keterangan) VALUES 
                                          (:tahun,:data_tahun,:id_sumberdata,:bulan,0,:keterangan)";
        }else{
            $tahun = $params['data']['tahun'];
            $bulan =$params['data']['bulan'];
            $idSumberData = $params['data']['id_sumberdata'];
            $cek = $this->cek_lock($tahun,$bulan,$idSumberData);
//            echo $cek;exit();
            if($cek){
                echo'{"success":true,"msg":"Data Sudah Dikunci tidak bisa diubah"}';
                exit();
            }
            $sql="update data_per_tahun set 
                   data=:data_tahun,
                   bulan=bulan,
                   keterangan=:keterangan
                  WHERE tahun=:tahun AND id_sumber_data=:id_sumberdata AND bulan=:bulan";
        }
//echo $sql;exit();
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

        $filename = "data-".substr(md5(date('y-m-d,h-i-s')),0,5);
        $fileatt = $pdf->Output(__DIR__."/../../pdf/$filename.pdf", 'F');

        /*set digital signature dengan apis*/
        $filePdf = 'simdagenak.slemankab.go.id_pdf_'.$filename.'.pdf';
        $dataApi['app'] = 'ds';
        $dataApi['module'] = 'set-ds';
        $dataApi['action'] = 'uploadSetDs';
        $dataApi['json'] = '{"nip":"183300582","file":"'.$filePdf.'"}';
        $response = $this->requestApi($dataApi);
        print_r($response);
        unlink(__DIR__."/../../pdf/$filename.pdf");
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




