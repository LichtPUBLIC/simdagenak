<?php
if (!defined('PATH_TEMPLATE')) define('PATH_TEMPLATE', 'template/smartadmin/');
ob_start();
session_start();
require_once dirname(__FILE__).'/lib/server/class.os.php';
$filter = ['update','delete','select','drop','insert','or','union'];
ini_set("display_errors", 0);
error_reporting(0);
$os = new Os();

function getMenu($return=0){
    $params = $_POST;
    $os = new Os();
    $sqlDataPilah = 'select * from data_pilah where aktif=1';
    $stmt = $os->conn->prepare($sqlDataPilah);
    $stmt->execute();
    $dataPilah = array();
    $menu = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        if(!isset($menu[$row['instansi']])){
            $menu[$row['instansi']] = array();
        }
        array_push($menu[$row['instansi']],$row);

    }
    return $menu;
}

if(isset($_POST['action'])){
    if($_POST['action'] == 'loadJenisData'){
        $instansi = $_POST['instansi'];
        $sql = "select * from data_pilah where instansi=:instansi";
        $stmt = $os->conn->prepare($sql);
        $stmt->bindParam(':instansi', $instansi, PDO::PARAM_STR);
        $stmt->execute();
        $dataPilahArr = array();
        while ($dataPilah = $stmt->fetch(PDO::FETCH_ASSOC)){
            array_push($dataPilahArr,$dataPilah);
        }
        print_r(json_encode($dataPilahArr));
    }
    else if($_POST['action'] == 'listInstansi'){
        $sql = "SELECT DISTINCT instansi FROM data_pilah WHERE aktif=1 AND instansi IS NOT NULL AND instansi != '' ORDER BY instansi ASC";
        $stmt = $os->conn->prepare($sql);
        $stmt->execute();
        $arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            array_push($arr, $row);
        }
        echo json_encode($arr);
    }
    else if($_POST['action'] == 'listTahun'){
        $sql = "SELECT tahun FROM ref_tahun WHERE aktif=1 ORDER BY tahun ASC";
        $stmt = $os->conn->prepare($sql);
        $stmt->execute();
        $arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            array_push($arr, $row);
        }
        echo json_encode($arr);
    }
}else if(isset($_POST['show_data']) && $_POST['show_data']==1){
    $params = $_POST;
    $kodeDatapilah = $params['kode_data_pilah'];
    $thn = array($params['tahun'],$params['tahun']);
//    $thn = array(2018,2018);
    // $sqlGenerate = "SELECT genQueryDataPilah ('$kodeDatapilah', $thn[0], $thn[1]) query";
    // $stmt = $os->conn->prepare($sqlGenerate);
    // $stmt->execute();
    // $sqlHasil = $stmt->fetch(PDO::FETCH_ASSOC);
    // $sqlHasil=$sqlHasil['query'];

    $sqlKolom = "select a.*,
                      (select count(*) from data_pilah_kolom where kode_data_pilah='$kodeDatapilah' and header_kolom = a.header_kolom) colspan,
                      (select count(*) from data_pilah_kolom where kode_data_pilah='$kodeDatapilah' ) colspanTahun,
                      (SELECT COUNT(*) FROM (SELECT header_kolom FROM data_pilah_kolom
                  WHERE kode_data_pilah = '$kodeDatapilah' GROUP BY header_kolom
                    )xx)+1 rowspan
                      from data_pilah_kolom a where kode_data_pilah='$kodeDatapilah' order by a.kode_kolom";
    $stmt = $os->conn->prepare($sqlKolom);
    $stmt->execute();
    $koloms = array();
    $kolomsingle = array();
    while($kolom = $stmt->fetch(PDO::FETCH_ASSOC)){
        array_push($koloms,$kolom);
        array_push($kolomsingle,$kolom['header_kolom'].' '.$kolom['nama_kolom']);
    }

    $th1 ='';
    $th2 ='';
    $curHeader = '';
//        print_r($kolomsingle);exit;
    $namaKolomArr = array();

    // DYNAMIC HEADER HEIGHT CALCULATION
    $hasSubheaders = false;
    $hasGenderSubcolumns = false;
    foreach ($koloms as $k) {
        if (!empty($k['header_kolom']) && $k['header_kolom'] !== '0') {
            $hasSubheaders = true;
        }
        $namaKolomUpper = strtoupper($k['nama_kolom']);
        if (in_array($namaKolomUpper, ['L', 'P', 'L+P'])) {
            $hasGenderSubcolumns = true;
        }
    }

    if ($hasSubheaders) {
        $totalRows = 3;
    } else {
        $totalRows = 2;
    }

    foreach ($thn as $th){
        $colspanTahun = (count($koloms) > 0) ? $koloms[0]['colspanTahun'] : 1;
        $th0="<th class='th-format' colspan='".$colspanTahun."'>".$th."</th>";
        foreach ($koloms as $val){
            $namaKolomArr[str_replace(".","_",$val['kode_kolom'])] = $val['nama_kolom'];
            
            $hasSub = !empty($val['header_kolom']) && $val['header_kolom'] !== '0';
            $namaKolomUpper = strtoupper($val['nama_kolom']);
            
            if (!$hasSubheaders) {
                // If there are no subheaders at all, all columns belong to th1 (Row 2)
                $th1 .= "<th class='th-format'>".$val['nama_kolom']."</th>";
            } else {
                // If there are subheaders, use multi-level rendering logic
                if($hasSub){
                    if($curHeader != $val['header_kolom']){
                        $th1.="<th class='th-format' colspan='".$val['colspan']."'>".$val['header_kolom']."</th>";
                        $curHeader = $val['header_kolom'];
                    }
                }else if(!$hasSub && $namaKolomUpper !='L' && $namaKolomUpper !='P' && $namaKolomUpper !='L+P'){
                    $thSpan = $totalRows - 1;
                    if ($thSpan > 0) {
                        $th1.="<th class='th-format' rowspan='".$thSpan."'>".@$val['nama_kolom']."</th>";
                    } else {
                        $th1.="<th class='th-format'>".@$val['nama_kolom']."</th>";
                    }
                    $curHeader = @$val['header_kolom'];
                }
                if($hasSub || $namaKolomUpper =='L' || $namaKolomUpper =='P' || $namaKolomUpper =='L+P'){
                    $th2.="<th class='th-format'>".$val['nama_kolom']."</th>";
                }
            }
        }
        if($thn[0] == $thn[1]){break;}
    }
    $tr1 = '';
    if($th1 !=''){
        $tr1 = "<tr class='bg-color-greenLight'>
                    $th1
                </tr>";
    }

    $tr2 = '';
    if($th2 !=''){
        $tr2 = "<tr class='bg-color-greenLight'>
                    $th2
                </tr>";
    }

    $rowspan = $totalRows;
    $head = "<tr class='bg-color-greenLight'>
                    <th valign='middle' class='th-format' rowspan='".$rowspan."' style='width: 50px;vertical-align: middle'>No</th>
                    <th valign='middle' class='th-format' rowspan='".$rowspan."' style='width: 150px;vertical-align: middle'>Kecamatan</th>                    
                    $th0
                </tr> 
                $tr1
                $tr2
                ";
//        echo $head;exit;

//    $stmt = $os->conn->prepare($sqlHasil);
//    $stmt->execute();
//    $data = array();
//    while($dataEach = $stmt->fetch(PDO::FETCH_ASSOC)){
//        array_push($data,$dataEach);
//    }

    // PIVOT MANUAL MENGGANTIKAN genQueryDataPilah YANG HILANG DARI DB
    $sqlBaris = "SELECT * FROM data_pilah_baris WHERE kode_data_pilah = :kode ORDER BY no_urut ASC";
    $stmtB = $os->conn->prepare($sqlBaris);
    $stmtB->execute([':kode' => $kodeDatapilah]);
    $barisList = $stmtB->fetchAll(PDO::FETCH_ASSOC);

    $sqlCell = "SELECT * FROM data_pilah_cell WHERE kode_data_pilah = :kode AND tahun = :tahun";
    $stmtC = $os->conn->prepare($sqlCell);
    $stmtC->execute([':kode' => $kodeDatapilah, ':tahun' => $thn[0]]);
    $cells = $stmtC->fetchAll(PDO::FETCH_ASSOC);

    $cellMap = array();
    foreach($cells as $c) {
        $cellMap[$c['kode_baris'] . '|' . $c['kode_kolom']] = $c['val'];
    }

    $data = array();
    foreach($barisList as $b) {
        $row = array();
        $row['nama_baris'] = $b['nama_baris'];
        $row['kode_baris'] = $b['kode_baris'];
        $row['kode_data_pilah'] = $kodeDatapilah;
        
        foreach($koloms as $k) {
            $key = $b['kode_baris'] . '|' . $k['kode_kolom'];
            $row[$k['kode_kolom']] = isset($cellMap[$key]) ? $cellMap[$key] : 0;
        }
        $data[] = $row;
    }
    $result = array();
    $result['head_table'] = $head;
    $result['kolom'] = $namaKolomArr;
    $result['kolomsingle'] = $kolomsingle;
    $result['success']=true;
    $result['result']=$data;
    print_r(json_encode($result));
}
else{
    $menu = getMenu(1);
}
