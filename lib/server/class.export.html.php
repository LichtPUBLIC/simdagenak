<?php

require(dirname(dirname(__FILE__)) . '/Mustache/Autoloader.php');
Mustache_Autoloader::register();

class ExportHtml {
	private $rootDir =null;
	private $css = null;
	private $html = null;
	private $htmlOutput = null;
	
	// template directory, diisi di class.database ketika inisialisasi class ExportHtml
	public $templateDir = null;
	
	function __construct() {
		$this->rootDir = dirname(dirname(dirname(__FILE__)));
		// default CSS included
		$this->css = 'body {background-color:white}
			h1,h2,h3,h4,h5 {margin:0;}
			table { background-color:white; border-collapse: collapse; border-color: #666666;} 
			th,td { border-width:1px; padding:1px 3px;} .money { text-align:right;}';		
		// Delete file-file temporary html dan CSS (jika ada) di folder /export/tmp/
		$files = glob( $this->rootDir . '/export/tmp/*.html'); // get all file names		
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
		if(is_string($css)) {
			$this->css .= $css;
		} else {
			throw new Exception('Error, $css variable must be string (in addCss function)');
		}
	}
	
	function addHtml($html) {
		if(is_string($html)) {
			$this->html .= $html;
		} else {
			throw new Exception('Error, $html variable must be string (in addHtml function)');
		}
	}
	
	function addCssFile($cssfile, $fromTemplate = true) {		
		if($fromTemplate) {
			$this->css .= file_get_contents( $this->templateDir . $cssfile);
		} else {
			$this->css .= file_get_contents($cssfile);			
		}
	}

	/**
	 *  @brief Memasukkan file html kedalam variabel html
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
	function addHtmlFile($htmlFile, $arrData = 0,  $fromTemplate = true) {
		if($fromTemplate) {
			$tplDir = $this->templateDir;
			$html = file_get_contents($tplDir . $htmlFile);
			// replace jika localhost mis. D:\htdocs\simarsip\modules\...
			// $tplDir = str_replace('D:\\xampp\\htdocs\\simarsip\\','',$tplDir);			
			// $tplDir = str_replace('D:\\htdocs\\simarsip\\','',$tplDir);	
			//print_r($tplDir); echo("\n");
			//$pattern = '/^[A-Z]\:\\\(.+)'. APP_ROOT_FOLDER . '/i';
			// konstanta APP_ROOT_FOLDER ada di config.php
			$pattern = '/^([A-Z]\:\\\\)?(.+)' . APP_ROOT_FOLDER . '/i';
			$tplDir = preg_replace($pattern,'',$tplDir);
			$tplDir = str_replace(array('/', '\\'), '/', $tplDir);
			//print_r($tplDir); exit;
			// replace jika ada gambar (attribut src="x.png|jpg");	
			$pattern = '/src="([a-zA-Z_\-]+\.(png|jpg|gif))"/i';

			$params = isset($_GET) ? $_GET : $_POST;
			if(isset($params['html_json'])) {
				$replace = 'src=".' . preg_quote($tplDir) . '$1"';
			} else {
				$replace = 'src="./../..' . preg_quote($tplDir) . '$1"';
			}
			// echo "$pattern,\n$replace,\n$html"; exit;
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
		$this->html .= $html;
	}

	private function insertCssToHtml() {
		$headPos =  stripos($this->html, '</head>');
		if($headPos !== false) {
			$replacer = '<style>'.  $this->css . '</style></head>';
			$this->htmlOutput = str_replace('</head>', $replacer , $this->html);
		} else {
			$this->htmlOutput = $this->css . $this->html;
		}
	}
	
	/**
	 *  @brief Menyimpan object menjadi dokumen HTML
	 *  @contributor Ebta,..
	 *  
	 *  @param [in] $filename nama file output
	 *  @param [in] $addRandomStr jika true akan ditambahkan string acak
	 *  @return lokasi file html yang dihasilkan
	 *  
	 *  @details Jika parameter export_type = 'stream', langsung kirim pdf ke browser
	 */
	function saveHtml($filename, $addRandomStr = true) {
		$params = isset($_GET) ? $_GET : $_POST;
		$export_type = isset($params['export_type']) ? $params['export_type'] : '';

		$this->insertCssToHtml();

		if($export_type === 'stream') {
			$htmldata = preg_replace("/\r\n|\r|\n/",'',$this->htmlOutput);
			echo '{"success" : true, "html":"'. htmlspecialchars($htmldata) .'"}';	
		} else {
			if($addRandomStr) $filename = Utility::addRandomFilename($filename,'html');
			$returnFile  = '/export/tmp/' . $filename;
			$outputFile  = $this->rootDir . $returnFile ;
			// Menyesuaikan Dir Separator, terutama agar kompatilebel di Windows
			$outputFile  = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $outputFile);
			file_put_contents($outputFile, $this->htmlOutput);	
			echo '{"success" : true, "filename":"'. $returnFile .'"}';			
		}
	}

	// Fungsi mirip saveHtml, tetapi hasilnya bukan echo, melainkan langsung kode HTML
	function returnHtml($encodeSpecialChars = false) {
		$this->insertCssToHtml();
		$htmldata = preg_replace("/\r\n|\r|\n/",'',$this->htmlOutput);
		if($encodeSpecialChars) {
			return htmlspecialchars($htmldata);
		} else {
			return $htmldata;
		}
	}
}