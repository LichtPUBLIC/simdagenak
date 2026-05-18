<?php

class RekapMatrik extends Database {
    private $var_global;

    function __construct() {
        parent::__construct();
    }

    public function rekap_list(){

        $params = isset($_GET) ? $_GET : $_POST;
        
        $sql = "SELECT instansi, count(*) jml from data_pilah where aktif=1 group by instansi order by jml desc ";
        // echo $this->debugSQL($sql);exit();

        // echo $this->dbFwSelectAndReturnAll($sql);
        echo $this->dbDataSelectAndReturnAll($sql);

    }

    

    

    function lapjiwa_exportExcel() {
        $params = isset($_GET) ? $_GET : $_POST;
        $InputFileName = "template.xlsx";
        $xls  = $this->createExcelExport($params['Module'],$InputFileName);

        $arr_hasil = $this->lapjiwa_list(true);
        //echo $sql; exit;
        $total = count($arr_hasil);
        $i = 5;
        $xls->sheet->insertNewRowBefore($i+1, $total);
        if ($total > 0) {
            $no = 1;
            foreach ($arr_hasil as $row) {
                $xls->sheet->setCellValue("B$i", $no);
                $xls->sheet->setCellValue("C$i", $row->id_periode);
                $xls->sheet->setCellValue("D$i", $row->nama_kepala_kk);
                $xls->sheet->setCellValue("E$i", "'" . $row->no_kk);
                $xls->sheet->setCellValue("F$i", $row->nama);
                $xls->sheet->setCellValue("G$i", "'" . $row->nik);

                //$xls->sheet->setCellValue("H$i",  $this->gethubKeluarga($row->id_hub_rumah_tangga));
                //$xls->sheet->setCellValue("I$i",  $this->getkelamin($row->id_kelamin));
                $xls->sheet->setCellValue("J$i", $row->kota_lahir);
                $xls->sheet->setCellValue("K$i", $row->tgl_lahir);
                $xls->sheet->setCellValue("L$i", $row->alamat);
                $xls->sheet->setCellValue("M$i", $row->kecamatan);
                $xls->sheet->setCellValue("N$i", $row->kelurahan);
                $xls->sheet->setCellValue("O$i", $row->dusun);

                //$xls->sheet->setCellValue("P$i",  $this->getPendidikan($row->id_pendidikan));
                //$xls->sheet->setCellValue("Q$i",  $this->getPartisipasiSekolah($row->id_partisipasi_sekolah));
                //$xls->sheet->setCellValue("R$i",  $this->getPenyakitKronis($row->id_penyakit_kronis));
                //$xls->sheet->setCellValue("S$i",  $this->getkepemilikanKartu($row->id_kepemilikan_identitas));
                //$xls->sheet->setCellValue("T$i",  $this->getPekerjaan($row->id_kerja));
                //$xls->sheet->setCellValue("U$i",  $this->getStatusPekerjaan($row->id_status_dalam_pekerjaan));
                $i++;
                $no++;
            }
        }
        $xls->saveReport('LaporanJiwa');
    }
   
}
