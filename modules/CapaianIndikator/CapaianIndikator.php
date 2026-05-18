<?php

class CapaianIndikator extends Database
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
            "indikator","keterangan","kode_indikator"
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

    private function hitung_data_pertahun($kode_indikator)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $sql = 'SELECT operasi_math_query,id_capaian FROM capaian_indikator WHERE kode_indikator="'.$kode_indikator.'"';
        $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);
        $queryRumus = $arrayData[0]->operasi_math_query;
        $idCapaian = $arrayData[0]->id_capaian;
//        print_r($arrayData);exit();
        /*if($return){
            return $arrayData;
        }*/
        $arrayData=array();
        $i=0;
        for($thn=2013;$thn<2018;$thn++){
            if($queryRumus!=''){
                $q= str_replace('paramtahun',$thn,$queryRumus);
                $q= str_replace('parambulan',12,$q);

                $sql = "select coalesce(data_fix,($q)) dataku,keterangan from data_capaian_indikator 
                              where id_capaian_indikator=$idCapaian and bulan=12 and tahun=$thn";
                try {
                    $dataCurrent = $this->dbDataGetValue($sql);
                } catch (Exception $e) {
                    $sql = "select coalesce(data_fix) dataku,keterangan from data_capaian_indikator
                              where id_capaian_indikator=$idCapaian and bulan=12 and tahun=$thn";
                    $dataCurrent = $this->dbDataGetValue($sql);
                }
                if($dataCurrent){
                    $arrayData[$thn] = $dataCurrent;
                }else{
                    $arrayData[$thn] = 0;
                }
            }else{
                $arrayData[$thn] = 0;
            }
        }
        return $arrayData;

        /*$array = array();
        $sqlCount = "SELECT count(*) FROM indikator ";
        $countData = $this->dbDataGetValue($sqlCount);
        $array['recordsTotal'] = count($arrayData);
        $array['recordsFiltered'] = count($arrayData);
        $array['draw'] = $_POST['draw'];
        $array['data'] = (array)$arrayData;
        echo json_encode($array);*/
    }

    public function ACTION_list($return=false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $sql = 'SELECT * FROM capaian_indikator';
        if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sql .= " where ".$criteria;
        }
        $sql.=" order by kode_indikator asc ";
        if (isset($_POST['start'])) {
            $start = $_POST['start'];
            $limit = $_POST['length'];
            $sql .= " limit $start,$limit ";
        }
        $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);
        if($return){
            return $arrayData;
        }
        $idx=0;
        foreach ($arrayData as $val){
            $kode_indikator = $val->kode_indikator;
            $q = "select indikator_has_sumberdata.*,ref_sumberdata.sumberdata from indikator_has_sumberdata 
                   inner join ref_sumberdata on ref_sumberdata.kode_sumberdata = indikator_has_sumberdata.simbol_sumber_data
                   where kode_indikator='$kode_indikator'";
            $dataSumber = $this->dbDataSelectAndReturnAll($q,null,true);
            $arrayData[$idx]->sumberdata = $dataSumber;

            $dataTahun = $this->hitung_data_pertahun($kode_indikator);
            for($thn=2013;$thn<2018;$thn++){
                $idxThn = "t$thn";
                $arrayData[$idx]->$idxThn = $dataTahun[$thn];
            }
            $idx++;
        }
//        print_r($arrayData);exit();
        $array = array();
        $sqlCount = "SELECT count(*) FROM capaian_indikator ";
        if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sqlCount .= " where ".$criteria;
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
        $sql = 'SELECT * FROM capaian_indikator';
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
//        print_r($userId);exit();
        $levelArr = explode('.',$params['data']['kode_indikator']);
        $level = count($levelArr);
//        echo $level;exit;
        $sql = "insert into capaian_indikator 
                      (indikator,
                       keterangan,
                       satuan,
                       input_date,
                       user_input,
                       kode_indikator,
                       level) 
                       VALUES 
                       (:indikator,
                       :keterangan,
                       :satuan,
                       now(),
                       '$userId',
                       :kode_indikator,
                       $level)";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_update(){
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $sql = "update capaian_indikator set 
                    indikator=:indikator,
                    id_urusan=:id_urusan,
                    keterangan=:keterangan,
                    update_date=now(),
                    user_update='$userId',
                    kode_indikator=:kode_indikator
                 WHERE id_capaian=:id_capaian";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_delete(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "delete from capaian_indikator WHERE id_capaian=:id_capaian";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_getUrusan(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "select * from capaian_indikator where level=1 order by kode_indikator asc ";
        echo $this->dbDataSelectAndReturnAll($sql);
    }

    public function ACTION_listViewDataBulan(){
        $params = isset($_GET) ? $_GET : $_POST;
        $idCapaian = $_POST['id_capaian_indikator'];
        $kode_indikator = $_POST['kode_indikator'];
        $sql = "select tahun from data_capaian_indikator group by tahun ORDER BY tahun ASC ";
        $dataTahun = $this->dbDataSelectAndReturnAll($sql,$_POST,true);

        $dataReturn = new stdClass();
        $sql = 'SELECT operasi_math_query FROM capaian_indikator WHERE id_capaian="'.$idCapaian.'"';
        $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);
        $queryRumus = $arrayData[0]->operasi_math_query;
        $result = array();
        foreach ($dataTahun as $thn){
            $dataRumus = array();
            $tahun = $thn->tahun;
            for($b=1;$b<13;$b++){
                $q= str_replace('paramtahun',$tahun,$queryRumus);
                $q= str_replace('parambulan',$b,$q);
                array_push($dataRumus,$q);
            }
//            print_r($dataRumus);exit;
            $sqlGetData = "select $tahun tahun,
                           (select coalesce(data_fix,(".$dataRumus[0].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=1) jan,
                           (select coalesce(data_fix,(".$dataRumus[1].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=2) feb,
                           (select coalesce(data_fix,(".$dataRumus[2].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=3) mar,
                           (select coalesce(data_fix,(".$dataRumus[3].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=4) aprl,
                           (select coalesce(data_fix,(".$dataRumus[4].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=5) mei,
                           (select coalesce(data_fix,(".$dataRumus[5].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=6) jun,
                           (select coalesce(data_fix,(".$dataRumus[6].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=7) jul,
                           (select coalesce(data_fix,(".$dataRumus[7].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=8) agt,
                           (select coalesce(data_fix,(".$dataRumus[8].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=9) sept,
                           (select coalesce(data_fix,(".$dataRumus[9].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=10) okt,
                           (select coalesce(data_fix,(".$dataRumus[10].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=11) nov,
                           (select coalesce(data_fix,(".$dataRumus[11].")) from data_capaian_indikator where tahun=$tahun and id_capaian_indikator=$idCapaian and bulan=12) des
                           ";
            $dataku = $this->dbDataSelectAndReturnAll($sqlGetData,null,true);
//            print_r($sqlGetData);exit;
            if(isset($dataku[0])) {
                array_push($result, $dataku[0]);
            }

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




