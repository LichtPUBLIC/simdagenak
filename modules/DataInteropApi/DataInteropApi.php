<?php

class DataInteropApi extends Database {

    function __construct() {
        parent::__construct(true);
    }
    private function _stringDistinct($string, $delimiter=', ', $delimiterResturn = ', ') {
    	$hasil = new stdClass();
	    $arrInstansi = explode($delimiter, $string);
        $arrInstansiDistinct = array_unique($arrInstansi);
        $hasil->count = count($arrInstansiDistinct);
        $hasil->string = implode($delimiterResturn, $arrInstansiDistinct);
        return $hasil;
    }
    public function Test($params,$retResult = false) {

        //echo $sql;exit();
        echo 'Test';

  }
   public function dataCalendar($params,$retResult = false){
        $params = isset($_GET) ? $_GET : $_POST; 
        
        $sql = "SELECT sm.id_surat_masuk as id,sm.nomor_surat as no,sm.klasifikasi, unit.unit_kerja, sm.asal_surat, sm.tgl_surat,        
        sm.tgl_acara1,sm.tempat, sm.acara, sm.catatan,sm.tgl_acara1 AS tgl_acara2
        FROM 
        tm_surat_masuk sm

        LEFT JOIN
        tm_disposisi dis       
        ON sm.id_surat_masuk=dis.`id_surat_masuk`

        LEFT JOIN
        reff_unit_kerja unit
        ON sm.id_unit_kerja=unit.`id_unit_kerja`
         LEFT JOIN
        reff_file file
        ON sm.id_surat_masuk=file.`id_surat_masuk`
        WHERE 1=1  GROUP BY sm.id_surat_masuk 
        UNION
        SELECT id_kegiatan AS id,kegiatan AS `no`,klasifikasi,user_entry AS unit_kerja,
        user_entry AS asal_surat, tgl_kegiatan AS tgl_surat,tgl_kegiatan AS tgl_acara1,
        tempat,acara,catatan,tgl_kegiatan2 AS tgl_acara2 FROM reff_agenda_kegiatan 
        ";
        echo $this->dbDataSelectAndReturnAll($sql);
        // print_r($a);exit();
    }
}
