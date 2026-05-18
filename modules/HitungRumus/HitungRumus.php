<?php

class HitungRumus extends Database
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
            "kode_indikator","indikator"
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

    private function cek_data_capaian($id_indikator,$tahun){
        $userId = $this->get_userId();
        $thnCurrent = date('Y');
        for($thn=2013;$thn<$thnCurrent;$thn++){
            $sql = "select count(*) from data_capaian_indikator where id_capaian_indikator = $id_indikator and tahun=$thn";
            $hasil = $this->dbDataGetValue($sql);

            if($hasil <= 0){
                $values = array();
                for ($bulan=1;$bulan<13;$bulan++){
                    $val = "('$id_indikator',$bulan,$thn,'$userId')";
                    array_push($values,$val);
                }
                $dataInput = implode(',',$values);
                $sqlInsert = "insert into data_capaian_indikator (id_capaian_indikator,bulan,tahun,user_input) values $dataInput";
                $this->dbDataExecute($sqlInsert);
            }
        }
    }

    public function ACTION_list($return=false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $tahun = $params['tahun'];
        $id_capaian = $params['id_capaian'];
        $this->cek_data_capaian($id_capaian,$tahun);
        $sql = 'SELECT operasi_math_query FROM capaian_indikator WHERE kode_indikator=:kode_indikator';
        $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);
        $queryRumus = $arrayData[0]->operasi_math_query;
//        print_r($arrayData);exit();
        /*if($return){
            return $arrayData;
        }*/
        $arrayData=array();
        $i=0;
        for($bulan=1;$bulan<13;$bulan++){
            if($queryRumus!=''){
                $q= str_replace('paramtahun',$tahun,$queryRumus);
                $q= str_replace('parambulan',$bulan,$q);
                $sql = "select coalesce(data_fix,($q)) dataku,keterangan from data_capaian_indikator 
                              where id_capaian_indikator=$id_capaian and bulan=$bulan and tahun=$tahun";
            }else{
                $sql = "select coalesce(data_fix) dataku,keterangan from data_capaian_indikator 
                              where id_capaian_indikator=$id_capaian and bulan=$bulan and tahun=$tahun";
            }

            try {
                $dataCapaian = $this->dbDataSelectAndReturnAll($sql,null,true);
            } catch (Exception $e) {
                $sql = "select coalesce(data_fix) dataku,keterangan from data_capaian_indikator 
                              where id_capaian_indikator=$id_capaian and bulan=$bulan and tahun=$tahun";
                $dataCapaian = $this->dbDataSelectAndReturnAll($sql,null,true);
            }


            if(!isset($dataCapaian[0])){
                $dataCapaian = new stdClass();
                $dataCapaian->dataku=0;
                $dataCapaian->keterangan='';
                array_push($arrayData,$dataCapaian);
            }else{
                array_push($arrayData,$dataCapaian[0]);
            }
            $i++;
        }
//        print_r($arrayData);exit;
        $array = array();
        /*$sqlCount = "SELECT count(*) FROM indikator ";
        $countData = $this->dbDataGetValue($sqlCount);
        $array['recordsTotal'] = count($arrayData);
        $array['recordsFiltered'] = count($arrayData);
        $array['draw'] = $_POST['draw'];*/
        $array['data'] = (array)$arrayData;
        echo json_encode($array);
    }

    public function ACTION_listPrint($return = false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $sql = 'SELECT * FROM indikator';
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
        $sql = "insert into indikator (kode_indikator,indikator) VALUES 
                                          (:kode_indikator,:indikator)";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_simpanDataCapaian(){
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $cek = "select count(*) from data_capaian_indikator where id_capaian_indikator=:id_capaian and bulan=:bulan and tahun=:tahun";
        $hasil = $this->dbDataGetValue($cek,$params);
//        echo $hasil;exit;
        if($hasil>0){
            $sql = "update data_capaian_indikator set 
                  data_fix =:data_fix,
                  keterangan =:data_ket,
                  user_update='$userId',
                  tgl_update=now()
                  where id_capaian_indikator=:id_capaian and bulan=:bulan and tahun=:tahun";

            if(isset($params['set_null'])){
                $sql = "update data_capaian_indikator set 
                  data_fix =NULL,
                  keterangan =:data_ket,
                  user_update='$userId',
                  tgl_update=now()
                  where id_capaian_indikator=:id_capaian and bulan=:bulan and tahun=:tahun";
            }
        }else{
            $sql="insert into data_capaian_indikator (id_capaian_indikator, 
                                        bulan, tahun, 
                                        data_fix, 
                                        keterangan, user_input, 
                                        tgl_input) VALUES 
                                        (:id_capaian,:bulan,:tahun,:data_fix,:data_ket,'$userId',now())
";
        }
        echo $this->dbDataExecute($sql,$params);
    }

    public function ACTION_addSbData(){
        $params = isset($_GET) ? $_GET : $_POST;
        $cek = "select simbol_temp from indikator_has_sumberdata where kode_indikator=:kode_indikator order by simbol_temp desc limit 1";
        $hasil = $this->dbDataGetValue($cek,$params['data']);
        $ascii = ord($hasil);
        $simbolTemp = 'a';
        if($ascii!=0){
            $simbolTemp = chr($ascii+1);
        }
        $sql = "insert into indikator_has_sumberdata 
                     (simbol_temp, kode_indikator, simbol_sumber_data) VALUES 
                     ('$simbolTemp',:kode_indikator,:simbol)";
        if($this->dbDataExecute($sql,$params['data'])){
            $select = "select indikator_has_sumberdata.*,ref_sumberdata.sumberdata from indikator_has_sumberdata 
                          INNER JOIN ref_sumberdata on ref_sumberdata.kode_sumberdata = indikator_has_sumberdata.simbol_sumber_data
                          where kode_indikator=:kode_indikator order by simbol_temp desc limit 1";
            echo $this->dbDataSelectAndReturnAll($select,$params['data']);
        }else{
            echo "gagal";
        }

    }

    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public function ACTION_updateIndikator(){
        $params = isset($_GET) ? $_GET : $_POST;
//        print_r($_POST);exit();
        $rumus = $_POST['rumus'];
        $rumusQuery = $rumus;
        $sqlGetSimbol = "select simbol_sumber_data,simbol_temp,tahun from indikator_has_sumberdata WHERE kode_indikator=:kode_indikator";
        $datasimbol = $this->dbDataSelectAndReturnAll($sqlGetSimbol,$_POST,true);
//        print_r($datasimbol);exit();

        foreach ($datasimbol as $val){
            $simbolTemp = $val->simbol_temp;
            $kode = $val->simbol_sumber_data;
            $rumusQuery = str_replace([$simbolTemp],$kode,$rumusQuery);
        }
        foreach ($datasimbol as $val){
            $simbolTemp = $val->simbol_temp;
            $kode = $val->simbol_sumber_data;
            $tahun = $val->tahun;
            $query = "(select b.data from ref_sumberdata a 
                       inner join data_per_tahun b on a.id_sumberdata = b.id_sumber_data
                       WHERE a.kode_sumberdata='$kode' and b.tahun=(paramtahun+$tahun) and b.bulan=parambulan)";
            $rumusQuery = str_replace([$kode],$query,$rumusQuery);
//            echo $rumusQuery;exit();
        }
//        echo $rumusQuery;exit();
        $rumusQuery = "select (".str_replace(['x','X'],'*',$rumusQuery).")";

        $_POST['operasi_math_query'] = $rumusQuery;
//        print_r($rumusQuery);exit();
        $sql = "update capaian_indikator set 
                        operasi_math = :rumus,
                        json_rumus = :stringJson,
                        operasi_math_query=:operasi_math_query,
                        canvas=:canvas,
                        canvas2=:canvas2
                WHERE kode_indikator=:kode_indikator";
        echo $this->dbDataExecute($sql,$_POST);
    }

    public function ACTION_sumberList(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "select id_sumberdata,kode_sumberdata,removeSpacialChar(sumberdata) sumberdata,
                 t2016,t2017 from ref_sumberdata";
        $data = $this->dbDataSelectAndReturnAll($sql);
        echo $data;

    }
    public function ACTION_listSimbol(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "select indikator_has_sumberdata.*,ref_sumberdata.sumberdata from indikator_has_sumberdata    
                INNER JOIN ref_sumberdata on ref_sumberdata.kode_sumberdata = indikator_has_sumberdata.simbol_sumber_data
                WHERE indikator_has_sumberdata.kode_indikator=:kode_indikator";
        $data = $this->dbDataSelectAndReturnAll($sql,$params);
        echo $data;

    }

    public function ACTION_updateTahunRelatif(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "update indikator_has_sumberdata set tahun=:tahun
                 WHERE id=:id";
        echo $this->dbDataExecute($sql,$params);
    }

    public function ACTION_update(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "update indikator set kode_indikator=:kode_indikator,indikator=:indikator
                 WHERE id_indikator=:id_indikator";
        echo $this->dbDataExecute($sql,$params['data']);
    }

    public function ACTION_delete(){
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "delete from indikator_has_sumberdata WHERE id=:id";
        echo $this->dbDataExecute($sql,$params);
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




