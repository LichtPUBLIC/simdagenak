<?php

$path = dirname(dirname(__FILE__)) . '/phpexcel/PHPExcel.php';
$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
require_once($path);

class ExportXls {

    private $fileType = null;
    private $rootDir = null;
    private $objReader = null;
	
    public $objPHPExcel = null;
    // active Sheet from $objPHPExcel
    public $sheet = null;

    function __construct($fileTemplate, $fileType) {
        $this->rootDir = dirname(dirname(dirname(__FILE__)));

        include($this->rootDir . "/lib/phpexcel/PHPExcel/IOFactory.php");
        include($this->rootDir . "/lib/phpexcel/PHPExcel/Settings.php");

        $fileTemplate = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $fileTemplate);
        if (!file_exists($fileTemplate)) {
            exit("Template does not exist. [".$fileTemplate."]");
        }

		
        $this->fileType = $fileType;		
        $this->objReader = PHPExcel_IOFactory::createReader($fileType);	
        $this->objPHPExcel = $this->objReader->load($fileTemplate);
        $this->sheet = $this->objPHPExcel->getActiveSheet();		
    }

    function __destruct() {
        $this->objPHPExcel->disconnectWorksheets();
        unset($this->objPHPExcel);
        unset($this->objReader);
    }
	
	/**
	 *   * Helper untuk mempersingkat syntax Styling
	 *  * Sepertinya tidak semua RGB di support, lihat color ref di :
	 *  * http://dmcritchie.mvps.org/excel/colors.htm
	 */
	function setBgColor($range, $colorRgb) {
		$color  = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => $colorRgb)
			)
		);			
		$this->sheet->getStyle($range)->applyFromArray($color);
	}
	
	function clearBgColor($range) {
		$color  = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_NONE
			)
		);			
		$this->sheet->getStyle($range)->applyFromArray($color);		
	}

    /**
     *  @brief Menyimpan object Excel menjadi file di lokasi /export/tmp/
     *  @contributor Ebta, Wimbo
     *  
     *  @param [in] $filename nama parameter input, bisa dengan ekstensi atau tidak
     *  @param [in] $addRandomStr jika True berarti nama file ditambah string random
     *  @return Menghasilkan nama file yang berhasil disimpan
     *  
     *  @details Jika $filename dengan ekstensi, itu yg akan digunakan. Jika tidak
     *  * akan mendeteksi fileType ( jika Excel2007 = .xlsx selainnya .xls )
     */
    function saveReport($filename, $addRandomStr = true) {
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, $this->fileType);	
		if($this->fileType === 'Excel2007') {
			$ext = '.xlsx';
		} else {
			$ext = '.xls';
		}
		if($addRandomStr) $filename = Utility::addRandomFilename($filename,$ext);
		
		$params = isset($_GET) ? $_GET : $_POST;
		$export_type = isset($params['export_type']) ? $params['export_type'] : '';
		if($export_type === 'stream') {
			// redirect output to client browser
			if($this->fileType === 'Excel2007') {				
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			} else {
				header('Content-Type: application/vnd.ms-excel');
			}
			header('Content-Disposition: attachment;filename="'. $filename .'"');
			header('Cache-Control: max-age=0');
			$objWriter->save('php://output');
		} else {
			$returnFile  = '/export/tmp/' . $filename;
			$outputFile  = $this->rootDir . $returnFile ;
			// Menyesuaikan Dir Separator, terutama agar kompatilebel di Windows
			$outputFile  = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $outputFile);
			
			$objWriter->save($outputFile);	
			echo '{"success" : true, "filename":"'. $returnFile .'"}';			
		}
    }
	
	/**
	 *  @brief Konversi tanggal[jam] menjadi date[time] di Excel, 
	 *  @contributor Ebta,..
	 *  
	 *  @param [in] $tgl tanggal dalam format yyyy-mm-dd atau yyyy-mm-dd hh:mm:ss
	 *  @return Nilai date/time di Excel
	 *  
	 *  @details jadi hasilnya bisa di entry ke PHPExcel, dan di format tampilannya
	 *  
	 *  $xlsExp = $this->createExcelExport($params['Module'],'namaTemplate.xlsx');
	 *  // Didalam Loop data, tambahkan disetiap nilai date/tanggal [jam]
	 *  $tglnew = $xlsExp->strToDateTimeXls($var_tgl_jam);
		$xlsExp->sheet->setCellValue("ColNum", $tglnew);				
		$xlsExp->sheet->getStyle("ColNum")->getNumberFormat()->setFormatCode("dd/mm/yyyy hh:mm");
	 *  
	 */
	function strToDateTimeXls($tgl) {
		$ln = strlen($tgl);
		if($ln === 10) {
			$a = explode('-', $tgl);
			return PHPExcel_Shared_Date::FormattedPHPToExcel($a[0], $a[1], $a[2]);
		} else {
			$a = explode(' ', $tgl);
			$dt = $a[0];
			$tm = $a[1];
			
			$a = explode('-', $dt);
			$h = explode(':', $tm);
			
			return PHPExcel_Shared_Date::FormattedPHPToExcel($a[0], $a[1], $a[2], $h[0], $h[1], $h[2]);
		}
	}

}