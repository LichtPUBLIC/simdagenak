<?php

require_once(dirname(dirname(__FILE__)) . '/mpdf57/mpdf.php');
require_once(dirname(dirname(__FILE__)) . '/pdfmerger/PDFMerger.php');
require(dirname(dirname(__FILE__)) . '/Mustache/Autoloader.php');
Mustache_Autoloader::register();

class Html2pdf {
	private $rootDir =null;
	// object mPDF, yang bisa diakses langsung dari kelas turunan
	public $mpdf = null;

	// template directory
	public $templateDir = null;

	function __construct() {
		$this->rootDir = dirname(dirname(dirname(__FILE__)));
		// lihat dokumentasi di http://mpdf1.com/manual/index.php?tid=184
		// mPDF( mode, jenis_kertas, default_font_size (pt), default_font_name,
		// margin : left, right, top, bottom, header, footer (mm) ...
		$this->mpdf = new mPDF('c','A4','','',15,15,20,25,15,12);
		$this->mpdf->SetDisplayMode('fullpage');
		$this->mpdf->list_indent_first_level = 0;	// 1 or 0 - whether to indent the first level of a list

		$defaultCssTable = 'table {border-collapse: collapse; border-color: #666666;}
		th,td { border-width:1px; padding:1px 3px;} .money { text-align:right;}';

		// Parameter kedua di method WriteHTML :
		// 0 - Parses a whole html document (default)
		// 1 - Parses the html as styles and stylesheets only
		// 2 - Parses the html as output elements only
		$this->mpdf->WriteHTML($defaultCssTable,1);

		// Delete file-file temporary PDF di folder /export/tmp/
		// gulp-strip-comments ada masalah dengan string yg didalamnya ada /*
		$tmpfolder = '/export/tmp/';
		$files = glob( $this->rootDir . "$tmpfolder*.pdf"); // get all file names
		$now = time(); $ftm = null;
		foreach($files as $file){
			$ftm = filemtime($file);
			// Hapus file yang telah dibuat lebih dari 10 menit (600 detik)
			if(($now-$ftm) > 600) {
				unlink($file);
			}
		}
	}

	function addCss($css) {
		// The parameter 1 tells that this is css/style only and no body/html/text
		$this->mpdf->WriteHTML($css,1);
	}

	function addHtml($html,$arrData = 0) {
		if(is_array($arrData)) {
			$mus = new Mustache_Engine;
			$html = $mus->render($html,$arrData);
		}
		$this->mpdf->WriteHTML($html,2);
	}

	function addCssFile($cssfile, $fromTemplate = true) {
		if($fromTemplate) {
			$this->mpdf->WriteHTML(file_get_contents( $this->templateDir . $cssfile),1);
		} else {
			$this->mpdf->WriteHTML(file_get_contents($cssfile),1);
		}
	}

	/**
	 *  @brief Memasukkan file html kedalam object mpdf
	 *  @contributor Ebta,..
	 *
	 *  @param [in] $htmlFile nama file html
	 *  @param [in] $arrData data yang akan di render dalam bentuk array
	 *  @param [in] $fromTemplate true akan mengambil html di folder template
	 *  * sub folder dari current Module
	 *  @return
	 *
	 *  @details otomatis akan mereplace gambar dengan atribut img src="xx.jpg" sesuai
	 *  * dengan lokasi template module tersebut.. (jpg, gif dan png). Jadi letakkan
	 *  * gambar jadi satu dengan template misal: <img scr="nama_gambar.jpg" />
	 */
	function addHtmlFile($htmlFile,$arrData = 0, $fromTemplate = true) {
		if($fromTemplate) {
			$tplDir = $this->templateDir;
			$html = file_get_contents($tplDir . $htmlFile);
			// replace jika localhost mis. D:\htdocs\simarsip\modules\...
			// $tplDir = str_replace('D:\\xampp\\htdocs\\simarsip\\','',$tplDir);
			// $tplDir = str_replace('D:\\htdocs\\simarsip\\','',$tplDir);
			//print_r($tplDir); echo("\n");
			//$pattern = '/^[A-Z]\:\\\(.+)'. APP_ROOT_FOLDER . '/i';
			$pattern = '/^([A-Z]\:\\\\)?(.+)' . APP_ROOT_FOLDER . '/i';
			$tplDir = preg_replace($pattern,'',$tplDir);
			$tplDir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $tplDir);
			//print_r($tplDir); exit;
			// replace jika ada gambar (attribut src="x.png|jpg");
			$pattern = '/src="([a-zA-Z_\-]+\.(png|jpg|gif))"/i';
			$replace = 'src=".' . preg_quote($tplDir) . '$1"';
			$html = preg_replace($pattern,$replace,$html);
			//die($html);
		} else {
			$html = file_get_contents($htmlFile);
		}

		if(is_array($arrData)) {
			$mus = new Mustache_Engine;
			$html = $mus->render($html,$arrData);
		}

		// replace &lt;br&gt; menjadi <br>
		$html = str_replace('&lt;br&gt;', '<br>', $html);
		$this->mpdf->WriteHTML($html);
	}

	/**
	 *  @brief Mengatur ukuran kertas hasil PDF
	 *  @contributor Ebta,..
	 *
	 *  @param [in] $width width page dalam milimeter
	 *  @param [in] $height height page dalam milimeter
	 *  @param [in] $orientation 'P' = Portrait, 'L' = Landscape
	 *  @return
	 *
	 *  @details Details
	 */
	function setPageSize($width, $height, $orientation = 'P') {
		$this->mpdf->_setPageSize(array($width,$height), $orientation);
	}


	/**
	 *  @brief Untuk mengubah margin page
	 *  @contributor Ebta,..
	 *
	 *  @param [in] $left Parameter_Description
	 *  @param [in] $right Parameter_Description
	 *  @param [in] $top Parameter_Description
	 *  @param [in] $bottom Parameter_Description
	 *  @return Return_Description
	 *
	 *  @details alhamdulillah... akhirnya left dan right jalan
	 */
	function setMargins($left, $right, $top, $bottom) {

		$this->mpdf->DeflMargin = $left;
		$this->mpdf->DefrMargin = $right;

		$this->mpdf->orig_lMargin = $left;
		$this->mpdf->orig_rMargin = $right;

		//$this->mpdf->ResetMargins();
		$this->mpdf->SetMargins($left,$right,$top);

		// parameter pertama apakah auto pagebreak
		$this->mpdf->SetAutoPageBreak(true,$bottom);
	}

	/**
	 *  @brief Menyimpan object menjadi dokumen PDF
	 *  @contributor Ebta,..
	 *
	 *  @param [in] $filename nama file output
	 *  @param [in] $addRandomStr jika true akan ditambahkan string acak
	 *  @return lokasi file pdf yang dihasilkan
	 *
	 *  @details Jika parameter export_type = 'stream', langsung kirim pdf ke browser
	 */
	function savePdf($filename, $addRandomStr = true) {
		$params = isset($_GET) ? $_GET : $_POST;
		$export_type = isset($params['export_type']) ? $params['export_type'] : '';

		if($export_type === 'stream') {
			// Lihat keterangan/dokumentasi dibawah
			$this->mpdf->Output($filename,'I');
		} else {
			if($addRandomStr) $filename = Utility::addRandomFilename($filename,'pdf');
			$returnFile  = '/export/tmp/' . $filename;
			$outputFile  = $this->rootDir . $returnFile ;
			// Menyesuaikan Dir Separator, terutama agar kompatilebel di Windows
			$outputFile  = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $outputFile);

			// @par1 = name of the file
			// @par2 = Destination where to send the document
			//    I: send the file inline to the browser. The plug-in is used if available.
			//       The name given by filename is used when one selects the "Save as" option
			//       on the link generating the PDF.
			//    D: send to the browser and force a file download with the name given by filename.
			//    F: save to a local file with the name given by filename (may include a path).
			//    S: return the document as a string. filename is ignored.
			$this->mpdf->Output($outputFile,'F');
			echo '{"success" : true, "filename":"'. $returnFile .'"}';
		}
	}
}