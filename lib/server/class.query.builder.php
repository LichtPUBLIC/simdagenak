<?php

// Class QueryBuilder ... sample comment
class QueryBuilder {
	private $sql = null;
	public $params = null;
	
	/**
	 *  @brief Constructor class
	 *  @contributor Ebta,..
	 *  
	 *  @param [in] $sql the Query
	 *  @param [in] $usingWhere TRUE akan menambah WHERE 1=1
	 *  @return clear
	 *  
	 *  @details Jika $sql sudah ada WHERE mana $usingWhere isikan false
	 */
	function __construct($sql,$usingWhere = false) {
		$this->sql = $sql;		
		if($usingWhere) $this->sql .= ' WHERE 1=1 ';
		$this->params = isset($_GET) ? $_GET : $_POST;
		if(empty($this->params)) {
			$this->params = $_POST;
		}
		
		// hilangkan tanda underscore '_' di awal param jika ada
		// jadi nanti parameter param mendukung yg diawali underscore jg
		foreach($this->params as $k=>$v) {
			if ( ltrim($k,'_') !== $k ){
				$this->params[ltrim($k,'_')] = $v;
				// Hapus KEY lama di Array (yg ada underscore-nya)              
				unset($this->params[$k]);
			}
		}
	}
	
	public function getSql() {
		return $this->sql;
	}
	
	public function getParams() {
		return $this->params;
	}
	
	public function addQuery($statement) {
		$this->sql .= ' ' . $statement;
	}
	
	// mengubah parameter (key) uppercase atau lowercase
	// key bisa satu field atau banyak dipisah dengan koma
	public function changeParamCase($key, $case = CASE_LOWER) {
		$keys = explode(',',$key);
		for($i=0; $i < count($keys); $i++) {
			$k = $keys[$i];
			if(array_key_exists($k,$this->params)) {
				if($case === CASE_LOWER) {
					$this->params[$k] = strtolower($this->params[$k]);				
				} elseif( $case === CASE_UPPER ) {
					$this->params[$k] = strtoupper($this->params[$k]);
				} 
			}			
		}
	}
	
	// Untuk mengambil nama kolom jika menggunakan nama alias table
	// misalnya tabelA.kolom1 -- diambil --> kolom1
	private function trimLeftDot($str) {
		$dot = strpos($str,'.');
		if($dot !== false) {
			return substr($str,$dot+1);
		} else {
			return $str;
		}
	}
	
	/**
	 *  @brief Menambahkan Query Where =, multi parameter
	 *  @contributor Ebta,..
	 *  
	 *  @param [in] $colsName nama fields di query, pisahkan dg tanda koma jika
	 *  * lebih dari satu nama. Bisa menggunakan dot. Misal : "nama,tabelA.alamat"
	 *  @return query WHERE, dengan parameter bindingnya
	 *  * misal " AND col1=:col1 AND col2:col2 AND col3=:col3 "
	 *  
	 *  @details Jika $parsName kosong (null), maka parameter akan otomatis mengambil
	 *  * dari $colsName, dengan melakukan trimLeftDot jika ada nama alias untuk tabel
	 */	
	public function addQueryEqualByParam($colsName, $parsName = null) {
		$cols = explode(',',$colsName);
		if(empty($parsName)) {
			$parsName = $colsName;
		}
		$pars = explode(',', $parsName);
		
		if(count($cols) !== count($pars)) {
			die('Jumlah kolom dan parameter addQueryEqualByParam tidak sama');
		}
		
		for($i=0;$i< count($cols); $i++) {
			$colName   = $cols[$i];
			$fieldName = $this->trimLeftDot($pars[$i]);
			$parValue  = trim(isset($this->params[$fieldName])? strval($this->params[$fieldName]) : '' );
			if($parValue != '') {
				$this->sql .= " AND $colName=:$fieldName ";				
			}
		}		
	}
	
    // Mirip dengan fungsi sebelumnya dengan IN.
    public function addQueryIn($colName,$parName = null) {
		if(empty($parName)) {
			// jika colName ada titik untuk table, misal tabel1.nama
			$parName = $this->trimLeftDot($colName);
		}
        $tmp = trim(isset($this->params[$parName])? $this->params[$parName] : '' );
        if (!empty($tmp)) {
            $this->sql .= " AND $colName IN ($tmp)";
            //return " AND $colName IN (:$parName)"; // --> Masih Bermasalah... !!
        }
    }
	
    /**
     *  @brief menghasilkan single query : AND $colName LIKE '%val%'
     *  @contributor Ebta,..
     *  @param [in] $colName Nama kolom atau filed di Table
     *  @param [in] $value String atau nilai yg ingin dicari
     *  @return misalnya: AND $colName LIKE '%...%'
     */
    public function addQueryLike($colName,$value,$operator = 'AND') {
		if(trim($value) === '') {
			return '';
		} else {
			$this->params["par_cari_x"] = '%' . $value . '%';
			$this->sql .= " $operator $colName LIKE :par_cari_x ";
		}
    }
	
	/**
	 *  @brief Membuat Query LIKE dari beberapa kolom jika yg dicari sama
	 *  @contributor Ebta,..
	 *  
	 *  @param [in] $colsName Nama-nama colom, pisahkan dengan tanda koma
	 *  @param [in] $value nilai yg dicari
	 *  @param [in] $operator 'OR' atau 'AND'
	 *  @return Tergantung $operator, contoh dihasilkan query semisal :
	 *  * ' AND (kolom1 LIKE :par_cari_0 OR kolom2 LIKE :par_cari_1)'
	 *  * ' AND kolom1 LIKE :par_cari_0 AND kolom2 LIKE :par_cari_1'
	 *  
	 *  @details variabel $params akan ditambah dengan nilai baru par_cari_0,
	 *  * par_cari_1, dst... sebanyak jumlah kolom.
	 *  * untuk $colsName bisa diisi dengan fungsi, misal 'LOWER(col1),col2'
	 */
	public function addQueryLikeByValue($colsName,$value, $operator = 'OR') {
		$value = trim($value);
		//print_r($colsName); print_r($value); exit;
		if(!empty($value)) {
			$cols = explode(',',$colsName);
			if(count($cols) < 2) {
				$this->addQueryLike($colName,$value);
			} else {
				for($i=0;$i< count($cols); $i++) {
					$this->params["par_cari_$i"] = '%' . $value . '%';
					$cols[$i] = $cols[$i] . " LIKE :par_cari_$i";
				}
				$operator = strtoupper(trim($operator));
				$hasil  = implode(" $operator ",$cols);
				if($operator === 'OR') {
					$this->sql .= " AND ($hasil)";
				}
			}			
		}
	}
	
	/**
	 *  @brief Membuat Query LIKE dari satu atau lebih kolom tabel database
	 *  @contributor Ebta,..
	 *  
	 *  @param [in] $colsName Nama kolom, pisahkan dengan tanda koma jika lebih dari satu
	 *  @param [in] $operator 'OR' atau 'AND'
	 *  @param [in] $parsName Nama parameter di variabel $params, jika null akan otomatis
	 *  * mengambil dari $colsName, menghilangkan alias table.	 
	 *  @return Tergantung $operator, contoh dihasilkan query semisal :
	 *  * ' AND (kolom1 LIKE :par_cari_0 OR kolom2 LIKE :par_cari_1)'
	 *  * ' AND kolom1 LIKE :par_cari_0 AND kolom2 LIKE :par_cari_1'
	 *  
	 *  @details
	 */	
	public function addQueryLikeByParam($colsName, $parsName = null, $operator = 'OR') {
		$cols = explode(',',$colsName);
		if(empty($parsName)) {
			$parsName = $colsName;
		}
		$pars = explode(',', $parsName);
		if(count($cols) !== count($pars)) {
			if(count($pars) === 1) {
				// Jika parsName hanya satu saja, maka isi kekurangan dg nilai yg sama
				for($i=0;$i<count($cols); $i++) {
					$value = isset($this->params[$parsName]) ? $this->params[$parsName] : '' ;
					$pars[$i] ="par_cari_$i";
					$this->params["par_cari_$i"] = $value;
				}				
			} else {
				die('Jumlah colsName dan parsName aneh, tidak bisa diproses');				
			}
		}
		
		$args = array();
		for($i=0;$i< count($cols); $i++) {
			$colName   = $cols[$i];
			$fieldName = $this->trimLeftDot($pars[$i]);
			$parValue  = trim(isset($this->params[$fieldName])? $this->params[$fieldName] : '' );
			if(!empty($parValue)) {
				$this->params[$fieldName] = '%' . $parValue . '%';
				$args[] = "$colName LIKE :$fieldName";				
			}
		}
		if(!empty($args)) {
			$operator = strtoupper(trim($operator));
			$hasil = implode(" $operator ",$args);
			if($operator === 'OR') {
				$this->sql .= " AND ($hasil)";
			} else {
				$this->sql .= " AND $hasil";
			}
		}		
	}
	
	public function debug() {
		echo "SQL Statement is:\n";
		print_r($this->sql);
		echo "\nAnd the parameters is:\n";
		print_r($this->params);
		die;
	}

	/* Add By Wimbo , 2015 04 10, setParams : memberikan params dari variabel*/
	public function setParams($params) {
		$this->params = $params;
		// hilangkan tanda underscore '_' di awal param jika ada
		// jadi nanti parameter param mendukung yg diawali underscore jg
		foreach($this->params as $k=>$v) {
			if ( ltrim($k,'_') !== $k ){
				$this->params[ltrim($k,'_')] = $v;
				// Hapus KEY lama di Array (yg ada underscore-nya)              
				unset($this->params[$k]);
			}
		}		
	}	
}
