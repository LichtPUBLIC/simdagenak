<?php

class Utility {

    // dapat langsung diakses dari luar dengan cara, mis. Utility::$namaBulan[3]
    public static $namaBulan = array('', 'Januari', 'Februari', 'Maret', 'April',
                                     'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober',
                                     'November', 'Desember');

    // langsung diakses dengan cara mis. Utility::namaBulanSebelum[3]
    public static function namaBulanSebelum($bulan) {
        $bulan = (int)$bulan;
        if ($bulan = 1) {
            return self::$namaBulan[12];
        } elseif($bulan < 13) {
            return self::$namaBulan[$bulan-1];
        } else {
            return 'Index bulan invalid';
        }
    }
    
    public static function namaBulanIni($bulan) {
        return self::$namaBulan[$bulan];
    }	

    public static function nvl($val, $replace){
        if( is_null($val) || $val === '' )  
		return $replace;
        else  
		return $val;
    }    
	
    public static function nvl2($val, $notNullValue, $nullValue){
        if( is_null($val) || $val === '' )  
		return $nullValue;
        else  
		return $notNullValue;
    } 
	
	public static function tanggalHariIni() {
		$t = getdate();
		return $t['mday'] . ' ' . self::$namaBulan[$t['mon']] . ' ' . $t['year'];
	}

	// Menghasilkan random string, dengan panjang sesuai dg $length
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
	
	/**
	 *  @brief Otomatis menambahkan string random setelah $filename
	 *  @contributor Ebta,..
	 *  
	 *  @param [in] $filename nama file, bisa dengan ekstensi atau tidak
	 *  @param [in] $ext ekstensi default filename
	 *  @param [in] $rnd_length panjang string random yg akan ditambahkan
	 *  @return Mengembalikan nama file dan tambahan random string serta extensi
	 *  
	 *  @details Jika $filename dan $ext tidak ada, return tanpa extensi
	 */
	public static function addRandomFilename($filename,$ext = '',$rnd_length = 12) {
		$paths = pathinfo($filename);	
		
		$ext = trim($ext);
		if( !empty($ext) && (substr($ext,0,1) !== '.')) {
			$ext = ".$ext";
		}		
		// jika $filename dengan ekstensi, gunakan ini, abaikan $ext
		if( isset($paths['extension']) ) {
			$ext = '.' . $paths['extension'];
		}
		return $paths['filename'] . Utility::generateRandomString($rnd_length) . $ext ;					
	}
	
	/**
	 *  @brief Konversi dari array ke CSV
	 *  @contributor Ebta, php.net
	 *  
	 *  @param [in] $array array of object
	 *  @param [in] $use_header apakah akan menambahkan header di baris pertama
	 *  @param [in] $delimiter pemisah antar kolom
	 *  @param [in] $quote quote setiap kolom
	 *  @return hasilnya adalah string CSV per baris
	 *  
	 *  @details format $array = Array( (stdClass ...), (stdClass ...), ...);
	 *  @ToDo	jika format array = Array( (Array ...), (Array... ), ...)
	 */
	public static function arrayToCsv($array, $use_header= true, $delimiter = ',', $quote = '"') {
		# Check if $array is an array
		if (!is_array($array)) return '';		
		
		$hasil = '';
		if($use_header) {
			$tmp = array();
			foreach($array[0] as $key => $val) {			
				$tmp[] = $quote . ucwords(str_replace('_',' ',$key)) . $quote;
			}
			$hasil = implode(',',$tmp) . "\n";
		}		
		
		for ($i = 0, $n = count($array); $i < $n; $i ++) {
			$tmp = array();
			foreach($array[$i] as $key => $val) {
				
				# Only 'correct' non-numeric values
				if (!is_numeric($val)) {
					$tmp[] = $quote . str_replace($quote, $quote . $quote, $val) . $quote;
				} else {
				// if (($delimiter == '.') && (is_numeric($val))) {
					// $tmp[] = "'" .$val;
					$tmp[] = $quote .$val . $quote;
				}				
			}
			
			$hasil .= implode(',',$tmp) . "\n";
		}
		return $hasil;
	}
	
    /**
     *  @brief Mengubah data array ke filename dalam CSV
     *  @contributor Ebta,..
     *  
     *  @param [in] $filename nama file
     *  @param [in] $array array data
     *  @param [in] $add_random_str apakah menambahkan random string ke nama file
     *  @param [in] $use_header apakah akan menambahkan header di baris pertama
     *  @param [in] $col_sep pemisah kolom
     *  @param [in] $quote quote setiap kolom
     *  @return mengembalikan text JSON dengan nama file yg berhasil disimpan
     *  
     *  @details Details
     */
    public static function arrayToCsvFile($filename, $array, $add_random_str = false, $use_header = true, $col_sep = ",", $quote = '"') {
        $output = self::arrayToCsv($array, $use_header, $col_sep , $quote);
		if($add_random_str) {
			$filename = self::addRandomFilename($filename,'csv');
		}
        $returnFile  = '/export/tmp/' . $filename;
        $outputFile  = dirname(dirname(dirname(__FILE__))) . $returnFile ;
        // Menyesuaikan Dir Separator, terutama agar kompatilebel di Windows
        $outputFile  = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $outputFile);
        file_put_contents( "$outputFile", $output);
        echo '{"success" : true, "filename":"'. $returnFile.'"}';
    }

    /**
     *  @brief Membalik string tanggal yyyy-mm-dd menjadi dd-mm-yyyy
     *  @contributor Ebta,..
     *
     *  @param [in] $tgl tanggal dalam format yyyy-mm-dd atau yyyy/mm/dd
     *  @return string tanggal dalam format dd-mm-yyyy atau sesuai $outSep
     *
     *  @details cara akses dengan Utility::reverseDate(...);
     */
    public static function reverseDate($tgl, $outSep = '-') {
		if(empty($tgl)) return '';
        $_tgl = substr(trim($tgl),0,10);
        if (strpos($_tgl,'-') !== false) {
            $sep = '-';
        } elseif(strpos($_tgl,'/') !== false) {
            $sep = '/';
        } elseif(strpos($_tgl,'_') !== false) {
            $sep = '_';
        } else {
            $sep = ' ';
        }

        $a = explode($sep,$_tgl);
        return $a[2] . $outSep . $a[1] . $outSep . $a[0];
    }

	// Membalik string tanggal jam
	// Misal $tgl="2014-05-30 08:34:12" hasilnya "30-05-2014 Jam 08:34" (sesuai parameter)
    public static function reverseDateTime($tgl, $textBeforeJam = ' Jam ', $seconds = false, $outSep = '-') {
		if(empty($tgl)) return '';
        $tmp = trim($tgl);
        $sep = substr($tmp,10,1);
        $a   = explode($sep,$tmp);
        $idx = ($seconds) ? 8 : 5;
        return self::reverseDate($a[0]) . "$textBeforeJam" . substr($a[1],0,$idx);
    }


    public static function getTahunOptions($db = null) {
        if ($db === null) {
            return '';
        }
        try {
            $sql = "SELECT tahun FROM ref_tahun WHERE aktif = 1 ORDER BY tahun ASC";
            $stmt = $db->query($sql);
            $options = "";
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $options .= "<option value=\"" . htmlspecialchars($row['tahun']) . "\">" . htmlspecialchars($row['tahun']) . "</option>\n";
            }
            return $options;
        } catch (Exception $e) {
            return '';
        }
    }

}
