<?php

class VerifikasiDataMatrix extends Database
{

    function __construct()
    {
        $numargs = func_num_args();
        if ($numargs === 1) {
            parent::__construct(func_get_arg(0));
        } else {
            parent::__construct();
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
            "judul_data_pilah"
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
        $x=$params['data'];
        // print_r($params['data']);exit;
        if (($x=='all') or ($x==0) ){
            $sql = "SELECT * FROM data_pilah a 
                
                where a.aktif=1 ";    
        }else{
            $sql = "SELECT * FROM data_pilah a 
                
                where a.aktif=1 and a.kode_instansi='$x'"; 


        }
        // $userId = $this->get_userId();
        // $sql = "SELECT * FROM data_pilah a 
        //         inner join reff_unit_kerja b on a.instansi=b.nama_instansi
        //         inner join user_has_instansi c on b.id_instansi=c.id_instansi
        //         where a.aktif=1 and c.user_id='$userId'";
        
        echo $this->dbDataSelectAndReturnAll($sql);
        // if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
        //     $keywords = strtolower($_POST['search']['value']);
        //     $findField = $this->findField();
        //     $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
        //    $sql .= " where ".$criteria;
        //     $sql .= " and LOWER(judul_data_pilah) like '%$keywords%'";
        // }
        // if (isset($_POST['start']) && $_POST['length'] != -1) {
        //     $start = $_POST['start'];
        //     $limit = $_POST['length'];
        //     $sql .= " limit $start,$limit ";
        // }
        // // print_r($sql); exit;
        // $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);
        // if($return){
        //     return $arrayData;
        // }
        // $array = array();
        // $sqlCount = "SELECT count(*) FROM data_pilah ";
        // if(isset($_POST['search']['value']) && $_POST['search']['value'] !=''){
        //     $keywords = strtolower($_POST['search']['value']);
        //     $findField = $this->findField();
        //     $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
        //     $sql .= " where ".$criteria;
        // }
        // $countData = $this->dbDataGetValue($sqlCount);
        // $array['recordsTotal'] = $countData;
        // $array['recordsFiltered'] = $countData;
        // $array['draw'] = $_POST['draw'];
        // $array['data'] = (array)$arrayData;
        // echo json_encode($array);
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
                $eachVal = "($idFormat,$tahun,'tahun','$val',0,0,-1)";
                array_push($values,$eachVal);
            }
            $values = implode(',',$values);
            $sqlInsert = "insert into data_per_format (id_format, tahun, kolom, baris, count_l, count_p, count_lp) VALUES 
                                                       $values
                                                       ";
            $this->dbDataExecute($sqlInsert);
        }
        $sql = "select 
                data_per_format.id_data,
                data_per_format.id_format,
                data_per_format.tahun,
                data_per_format.kolom,
                data_per_format.baris,
                data_per_format.count_l,
                data_per_format.count_p,
                (data_per_format.count_l + data_per_format.count_l) count_lp,
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
                $eachVal = "($idFormat,$tahun,'jml_panti','$val',-1,-1,0,'$rowId'),
                            ($idFormat,$tahun,'tahun','$val',0,0,0,'$rowId')";
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
                     (SELECT baris FROM data_per_format WHERE kolom='tahun' AND baris = a.`baris`) keterangan,
                     (SELECT count_lp FROM data_per_format WHERE kolom='jml_panti' AND baris = a.`baris`) jml_panti,
                     (SELECT count_l FROM data_per_format WHERE kolom='tahun' AND baris = a.`baris`) count_l,
                     (SELECT count_p FROM data_per_format WHERE kolom='tahun' AND baris = a.`baris`) count_p,
                     (SELECT count_lp FROM data_per_format WHERE kolom='tahun' AND baris = a.`baris`) count_lp,
                     b.title,a.row_id,a.tahun FROM data_per_format a
                INNER JOIN format_datagender b ON b.id_format = a.id_format
                where a.tahun=:tahun and a.id_format=:id_format group by a.baris";

        /*$sql = "select data_per_format.*,format_datagender.title from data_per_format a
                inner join format_datagender b on b.id_format = a.id_format
                where a.tahun=:tahun and a.id_format=:id_format";*/
        echo $this->dbDataSelectAndReturnAll($sql,$params);
    }



    public function ACTION_getDataFormat_old(){
        $params = isset($_GET) ? $_GET : $_POST;
        $formatClass = str_replace('-','_',$params['grup_format']);
        $this->$formatClass($params);
    }

    private function inser_baris ($params){
        $kodeDatPilah = $params;
        $sqlCek = "select count(*) from data_pilah_baris where kode_data_pilah='$kodeDatPilah'";
        $hasil = $this->dbDataGetValue($sqlCek,$params);
//        print_r($hasil);exit;
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
            $urut = 1;
            foreach ($kec as $val){
                $rowId = md5($val.date('YMD:his'));
                $kodeLast = ($urut<10)?"0$urut":$urut;
                $kodeBaris = $kodeDatPilah.'.'.$kodeLast;
                $eachVal = "('$kodeDatPilah','$urut','$kodeBaris','$val',1)
                            ";
                array_push($values,$eachVal);
                $urut++;
            }
            $values = implode(',',$values);
            $sqlInsert = "insert into data_pilah_baris (kode_data_pilah, no_urut, kode_baris, nama_baris, aktif) VALUES 
                                                       $values
                                                       ";
//            echo $sqlInsert;exit;
            $this->dbDataExecute($sqlInsert);
        }
        /*$sql = "SELECT
                     (SELECT baris FROM data_per_format WHERE kolom='keterangan' AND row_id = a.`row_id`) keterangan,
                     (SELECT baris FROM data_per_format WHERE kolom='forum_komunikasi' AND row_id = a.`row_id`) forum_komunikasi,
                     (SELECT count_l FROM data_per_format WHERE kolom='ypac' AND row_id = a.`row_id`) ypac,
                     (SELECT count_p FROM data_per_format WHERE kolom='aliansi_rbm' AND row_id = a.`row_id`) aliansi_rbm,
                     (SELECT count_lp FROM data_per_format WHERE kolom='jumlah' AND row_id = a.`row_id`) jumlah,
                     b.title,a.row_id,a.tahun FROM data_per_format a
                INNER JOIN format_datagender b ON b.id_format = a.id_format
                where a.tahun=:tahun and a.id_format=:id_format GROUP BY a.row_id";*/

        /*$sql = "select data_per_format.*,format_datagender.title from data_per_format a
                inner join format_datagender b on b.id_format = a.id_format
                where a.tahun=:tahun and a.id_format=:id_format";*/
//        echo $this->dbDataSelectAndReturnAll($sql,$params);
    }

    public function ACTION_getDataFormat(){
        $params = isset($_GET) ? $_GET : $_POST;
        $kodeDatapilah = $params['kode_data_pilah'];
        $this->inser_baris($kodeDatapilah);
        $thn = array($params['tahun'],$params['tahun']);
        $sqlGenerate = "SELECT genQueryDataPilah ('$kodeDatapilah', $thn[0], $thn[1])";
        $sqlHasil = $this->dbDataGetValue($sqlGenerate);

        $sqlKolom = "select a.*,
                      (select count(*) from data_pilah_kolom where kode_data_pilah='$kodeDatapilah' and header_kolom = a.header_kolom) colspan,
                      (select count(*) from data_pilah_kolom where kode_data_pilah='$kodeDatapilah' ) colspanTahun,
                      (SELECT COUNT(*) FROM (SELECT * FROM data_pilah_kolom
                  WHERE kode_data_pilah = '$kodeDatapilah' GROUP BY header_kolom
                    )xx)+1 rowspan
                      from data_pilah_kolom a where kode_data_pilah='$kodeDatapilah' and aktif=1 order by a.kode_kolom asc ";
        $koloms = $this->dbDataSelectAndReturnAll($sqlKolom,null,true);

        $th1 ='';
        $th2 ='';
        $curHeader = '';
        $kolomHead = array();
        $kolomParentHead = array();
        foreach ($koloms as $kol){
//            print_r($kol);exit;
            if($kol->header_kolom === '' || $kol->header_kolom === 0 || $kol->header_kolom === null){
                array_push($kolomParentHead,0);
                $kolomHead[0][] = $kol->nama_kolom;
            }else{
                array_push($kolomParentHead,$kol->header_kolom);
//                print_r($kol->header_kolom);exit;
                $kolomHead[$kol->header_kolom][] = $kol->nama_kolom;
            }
        }
//        print_r($kolomParentHead);exit;
        $namaKolomArr = array();
        foreach ($thn as $th){
            $th0="<th class='th-format' colspan='".$koloms[0]->colspanTahun."'>".$th."</th>";
            foreach ($koloms as $val){
                $namaKolomArr[str_replace(".","_",$val->kode_kolom)] = array(
                    'nama' => $val->nama_kolom,
                    'header' => $val->header_kolom ? $val->header_kolom : '',
                    'tipe' => $val->tipe_kolom ? $val->tipe_kolom : ''
                );
                if($val->header_kolom){
                    if($curHeader != $val->header_kolom){
                        $th1.="<th class='th-format' colspan='".$val->colspan."'>".$val->header_kolom."</th>";
                        $curHeader = $val->header_kolom;
                    }
                }else if(!$val->header_kolom && $val->nama_kolom !='L' && $val->nama_kolom !='P' && $val->nama_kolom !='L+P'){
                    $th1.="<th class='th-format' rowspan='".((int)$val->rowspan-1)."'>".$val->nama_kolom."</th>";
                    $curHeader = $val->header_kolom;
                }
                if(!empty($val->header_kolom) || $val->nama_kolom =='L' || $val->nama_kolom =='P' || $val->nama_kolom =='L+P'){
                    $th2.="<th class='th-format'>".$val->nama_kolom."</th>";
                }

            }
            if($thn[0] == $thn[1]){break;}
        }
        $tr1 = '';
        if($th1 !=''){
            $tr1 = "<tr>
                    $th1
                </tr>";
        }

        $rowspan = ($koloms[0]->rowspan==2)?$koloms[0]->rowspan:3;
        $head = "<tr>
                    <th class='th-format' rowspan='".$rowspan."'>No</th>
                    <th class='th-format' rowspan='".$rowspan."'>Kecamatan</th>                    
                    $th0
                </tr> 
                $tr1
                <tr>
                    $th2
                </tr>
                ";
//        echo $head;exit;
        $data = $this->dbDataSelectAndReturnAll($sqlHasil,null,true);
        $result = array();
        $result['head_table'] = $head;
        $result['kolom'] = $namaKolomArr;
//        $result['kolomParent'] = $kolomParentHead;
        $result['kolomHead'] = $kolomHead;
        $result['koloms_raw'] = $koloms;
        $result['success']=true;
        $result['result']=$data;
        print_r(json_encode($result));
    }

    public function ACTION_UpdateDataFormat(){
        $params = isset($_GET) ? $_POST : $_POST;
        $tahun = $params['data']['thun'];
        $kode_data_pilah = $params['data']['kode_data_pilah'];
        
        $sqlCekVerif = "SELECT count(*) FROM data_pilah_verifikasi WHERE kode_data_pilah='$kode_data_pilah' AND tahun=$tahun AND is_verified=1";
        $isVerif = $this->dbDataGetValue($sqlCekVerif);
        if ($isVerif > 0) {
            echo '{"success":false, "msg":"Data ini sudah diverifikasi dan tidak dapat diedit lagi."}';
            exit();
        }

        $getBaris = "select SUBSTRING_INDEX(kode_baris,'.',-1) id_baris from data_pilah_baris where kode_data_pilah = ".$kode_data_pilah;
        $getKolom = "select SUBSTRING_INDEX(kode_kolom,'.',-1) id_kolom from data_pilah_kolom where kode_data_pilah = ".$kode_data_pilah;
        $baris = $this->dbDataSelectAndReturnAll($getBaris,null,true);    
        $koloms = $this->dbDataSelectAndReturnAll($getKolom,null,true);    
        $i=0;
        foreach ($baris as $key) {

            $kodei = $key ->id_baris;
            $kodeBaris = "$kode_data_pilah.$kodei";

                foreach ($koloms as $key ) {
                    $kodeA =$key->id_kolom; 
                    $kodeKolom = "$kode_data_pilah.$kodeA";
                    $indexData = $kode_data_pilah."_".$kodeA."_".$tahun."_".($i);
                   // echo $indexData."<br>";
                    if(isset($params['data'][$indexData])){
                        $val = $params['data'][$indexData];
                        $sqlCek = "select count(*) from data_pilah_cell 
                                    where tahun=$tahun 
                                    and kode_data_pilah='$kode_data_pilah'
                                    and kode_kolom='$kodeKolom' and kode_baris='$kodeBaris'";
                                    // echo $sqlCek;
                        $hasil = $this->dbDataGetValue($sqlCek);
                        if (!isset($hasil)) {echo "NILL"; exit(); }
                        // print_r($hasil);
                        if($hasil==0){
                            $sql = "insert into data_pilah_cell (tahun, kode_data_pilah, kode_kolom, kode_baris, val) VALUES 
                                                                ($tahun,'$kode_data_pilah','$kodeKolom','$kodeBaris',$val)
                                                                ";
                        }else{
                            $sql="update data_pilah_cell set val=$val where tahun=$tahun 
                                    and kode_data_pilah='$kode_data_pilah'
                                    and kode_kolom='$kodeKolom' and kode_baris='$kodeBaris'";
                        }
                       // echo "$sql ;<br>";
                        $this->dbDataExecute($sql);
                    }
                   
                }
                // exit;
            $i++;
        }

        // exit();


        echo '{"success":true}';

      
    }
    
    public function ACTION_UpdateDataFormat2(){
        $params = isset($_GET) ? $_POST : $_POST;
       // print_r($params);exit;
        $tahun = $params['data']['thun'];
        $kode_data_pilah = $params['data']['kode_data_pilah'];

        for($i=0;$i<17;$i++){
            if($i<9){
                $kodei = "0".($i+1);
            }else{
                $kodei = $i+1;
            }

            $kodeBaris = "$kode_data_pilah.$kodei";
            $a=0;
            while($a<100){
                if($a<9){
                    $kodeA = "0".($a+1);
                }else{
                    $kodeA = $a+1;
                }
                $kodeKolom = "$kode_data_pilah.$kodeA";
                $indexData = $kode_data_pilah."_".$kodeA."_".$tahun."_".($i);
               // echo $indexData."<br>";
                if(!(isset($params['data'][$indexData]))){break;}
                $val = $params['data'][$indexData];
                $sqlCek = "select count(*) from data_pilah_cell 
                            where tahun=$tahun 
                            and kode_data_pilah='$kode_data_pilah'
                            and kode_kolom='$kodeKolom' and kode_baris='$kodeBaris'";
                            // echo $sqlCek;
                $hasil = $this->dbDataGetValue($sqlCek);
                // if (!isset($hasil)) echo "NILL";
                // print_r($hasil);
                if($hasil==0){
                    $sql = "insert into data_pilah_cell (tahun, kode_data_pilah, kode_kolom, kode_baris, val) VALUES 
                                                        ($tahun,'$kode_data_pilah','$kodeKolom','$kodeBaris',$val)
                                                        ";
                }else{
                    $sql="update data_pilah_cell set val=$val where tahun=$tahun 
                            and kode_data_pilah='$kode_data_pilah'
                            and kode_kolom='$kodeKolom' and kode_baris='$kodeBaris'";
                }
//                echo "$sql ;<br>";
                $this->dbDataExecute($sql);
                $a++;
            }
        }
        echo '{"success":true}';

        /*$idFormat = $params['data']['id_format'];
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

        $x=1;
        $a=1;
        while ($x==1){
            if(!isset($params['data']['kolom'.$a])){break;}
            $kolom = $params['data']['kolom'.$a];

            foreach ($kec as $val){
                $l = $params['data']['l'.$val]!=-1?$params['data']['l'.$val]:-1;
                $p = $params['data']['p'.$val]!=-1?$params['data']['p'.$val]:-1;
                $lp = $params['data']['lp'.$val]!=-1?$params['data']['lp'.$val]:-1;
                $sql = "update data_per_format set 
                         count_l = $l,
                         count_p = $p,
                         count_lp = $lp
                         where kolom='$kolom' and tahun=$tahun and baris='$val'
                        ";
                echo $sql;exit;
                $this->dbDataExecute($sql);
            }
            $a++;
        }
        echo '{"success":true}';*/

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

    public function ACTION_instansi(){
         $params = isset($_GET) ? $_GET : $_POST;

         $sql="SELECT * from reff_unit_kerja";
         echo $this->dbDataSelectAndReturnAll($sql);
    }

    public function ACTION_getVerifStatus(){
        $params = empty($_POST) ? $_GET : $_POST;
        $kode_data_pilah = $params['kode_data_pilah'];
        $tahun = $params['tahun'];
        
        $sql = "SELECT is_verified FROM data_pilah_verifikasi WHERE kode_data_pilah='$kode_data_pilah' AND tahun=$tahun";
        $is_verified = $this->dbDataGetValue($sql);
        
        echo json_encode(['success' => true, 'is_verified' => $is_verified ? 1 : 0]);
    }

    public function ACTION_toggleVerif(){
        $params = empty($_POST) ? $_GET : $_POST;
        $kode_data_pilah = $params['kode_data_pilah'];
        $tahun = $params['tahun'];
        $is_verified = $params['is_verified'];
        $id_instansi = isset($params['id_instansi']) ? $params['id_instansi'] : 0;
        
        $sqlCek = "SELECT count(*) FROM data_pilah_verifikasi WHERE kode_data_pilah='$kode_data_pilah' AND tahun=$tahun";
        $count = $this->dbDataGetValue($sqlCek);
        if($count > 0){
            $sql = "UPDATE data_pilah_verifikasi SET is_verified=$is_verified, verified_at=NOW(), id_instansi=$id_instansi WHERE kode_data_pilah='$kode_data_pilah' AND tahun=$tahun";
        }else{
            $sql = "INSERT INTO data_pilah_verifikasi (kode_data_pilah, tahun, is_verified, verified_at, id_instansi) VALUES ('$kode_data_pilah', $tahun, $is_verified, NOW(), $id_instansi)";
        }
        $this->dbDataExecute($sql);
        
        echo json_encode(['success' => true]);
    }

    public function PUBLIC_getDataFormat(){
        return $this->ACTION_getDataFormat();
    }

    public function PUBLIC_UpdateDataFormat(){
        return $this->ACTION_UpdateDataFormat();
    }
}




