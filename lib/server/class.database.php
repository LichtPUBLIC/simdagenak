<?php

require_once('class.utility.php');
require_once('class.dbora.php');
require_once('class.query.builder.php');
require 'config.php';

class Database {
    private $os = null;
    private $isUpdateSession = true;
    // koneksi database ke MySQL (Framework)
    protected $dbFw = null;
    // koneksi database DATA, tergantung koneksi DB yg digunakan
    protected $dbDataConn = null;

    /**
    * @brief Konstruktor koneksi ke database, jika params kosong, akan mengambil config.php
    * @param [0] $conn_str = bisa Connection String (Oracle) atau DSN (mysql, postgre, dll)
    * untuk mysql dengan DSN misalnya:  mysql:dbname=my_db_name;host=127.0.0.1
    * untuk Oracle : (DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = 127.0.0.1)(PORT=1521)) (CONNECT_DATA = (SERVICE_NAME = XE)))
    * @param [1] $db_user username database
    * @param [2] $db_password password database
    * @param [0] $apiCall, apakah Class Database di panggil melalui Api Web Service, jika iya
    * maka tidak perlu pengecekan session, karena beda domain jadi tidak harus login dulu..
    */
    function __construct() {
        $this->os = new Os();
        $apiCall = false;
        $numargs = func_num_args();
        if($numargs === 3) {
            $conn_str = func_get_arg(0);
            $db_user = func_get_arg(1);
            $db_password = func_get_arg(2);
        } elseif ($numargs === 1) {
            $apiCall = true;
        }
        $session = $apiCall ? true : $this->os->session_exist();
        // print_r("session:$apiCall\n");

        if ($session==true) {
            // Custom connection to another Database
            if(isset($conn_str, $db_user, $db_password)) {
                $str = trim(strtolower($conn_str));
                if(strpos($str, 'mysql') === false) {
                    $this->dbDataConn = new DBORA();
                    $this->dbDataConn->Connect($conn_str , $db_user, $db_password);
                    $this->dbDataConn->SetAutoCommit(true);
                } else {
                    $this->dbDataConn = new PDO($conn_str, $db_user , $db_password);
                    $this->dbDataConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->dbDataConn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                }
            } else {
                // Inisialisasi koneksi ke Database DATA
                if(DB_DATA_USING_ORACLE) {
                    $this->dbDataConn = new DBORA();
                    $this->dbDataConn->Connect(DB_ORA_CONN_STR , DB_ORA_USER, DB_ORA_PWD);
                    $this->dbDataConn->SetAutoCommit(true);
                } elseif(DB_DATA_USING_MYSQL) {
                    $dsn = 'mysql:dbname='. DB_DATA_NAME. ';host='. DB_DATA_HOST;
                    try {
                        $this->dbDataConn = new PDO($dsn, DB_DATA_USER , DB_DATA_PASSWORD);
                        // To enable PDO Exceptions and disable emulated prepares:
                        // Jadi ketika menggunakan bindParam, dan ada Error akan ditampilkan
                        // Memudahkan debugging
                        $this->dbDataConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $this->dbDataConn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                        $this->dbDataConn->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
                    } catch (PDOException $e) {
                        $result = array('success' => false, 'error' => '0', 'msg' => $e->getMessage());
                        die(json_encode($result));
                    }
                }
            }
            // Koneksi ke database os (mysql) : kelas ini saja
            $this->dbFw = $this->os->conn;
            /* update session time_updated, time_logout - Jika bukan dari pemanggilan API */
            if(!$apiCall) {
                $sessid = $_COOKIE[COOKIE_KEY];
                $sql = "UPDATE sessions SET
                    time_updated=NOW(), time_logout=ADDTIME(NOW(),'".SESSION_TIMEOUT."')
                    WHERE session_id='$sessid' ";
                if ( $this->isUpdateSession ) {
                    $stmt = $this->dbFw->prepare($sql);
                    $stmt->execute();
                }
            }
        } else {
            echo '{"success": false, "msg":"Sesi login sudah habis, silahkan refresh browser"}';
            exit();
        }
    }

    /**
     *  @brief Mendebug sintaks SQL dengan paramater SQL dengan format titik dua dan Params
     *  @contributor Damar Suryo Sasono
     *
     *  @param [in] $sql Query yang berisi parameter2 binding
     *  @param [in] $params Array semua data (parameter) yg dikirim client
     * 		dengan atau tanpa underscore dianggap valid, mis. '_nama' akan menjadi 'nama'
     *  @return berisi paremeter $sql yang sudah di gabungkan dengan $params menjadi sintaks SQL yang dapat
     *		dijalankan
     *
     *  @details hanya akan mengambil parameter yang di query ada tanda ':'
     */
    public function debugSQL($sql, $params) {
        foreach($params as $k=>$v) {
                if ( ltrim($k,'_') !== $k ){
                    $params[ltrim($k,'_')] = $v;
                    // Hapus KEY lama di Array (yg ada underscore-nya)
                    unset($params[$k]);
                }
            }
        preg_match_all("/:\w+/", $sql, $output_array);

        // $output_array = array_multisort(array_map('strlen', $output_array[0]), $output_array);

        usort($output_array[0], function($a, $b) {
            return strlen($b) - strlen($a);
        });

        $out = str_replace(':','',$output_array[0]);
        // print_r($out);
        // var_dump($output_array[0]);


        $i = 0;
        foreach ($output_array[0] as $key) {
            $sql = str_replace($output_array[0][$i],'\''.$params[$out[$i]].'\'',$sql);
            // echo $output_array[0][$i].'<br>';
            // echo $params[$out[$i]].'<br>';
            $i++;
        }
        return $sql;
    }

    public function setUpdateSession($val) {
        return $this->isUpdateSession = $val;
    }

    public function getUserLogin() {
        return $this->os->getUserLogin();
    }

    public function getUserParams() {
        return $this->os->getUserParams();
    }

    function getUserGroupParams() {
        return $this->os->getUserGroupParams();
    }

    public function getModulePath($moduleName = null) {
        if(empty($moduleName)) {
            $moduleName = get_called_class();
        }
        $root = dirname(dirname(dirname(__FILE__)));
        $path = $root . "/modules/$moduleName";
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        return $path;
    }

    public function getTemplatePath($moduleName = null) {
        $modPath = $this->getModulePath($moduleName);
        $tplPath = $modPath . "/template/";
        $tplPath = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $tplPath);
        return $tplPath;
    }

    /**
     *  @brief Membuat parameter sesuai dengan parameter yg di bind di SQL
     *  @contributor Ebta,Wimbo
     *
     *  @param [in] $query Query yang berisi parameter2 binding
     *  @param [in] $allParams Array semua data (parameter) yg dikirim client
     * 		dengan atau tanpa underscore dianggap valid, mis. '_nama' akan menjadi 'nama'
     *  @return berisi paremeter $allParams yang hanya ada di Query
     *
     *  @details hanya akan mengambil parameter yang di query ada tanda ':'
     */
    public function createQueryParams($query, $allParams) {
        if(is_array($allParams) && (count($allParams) > 0) ) {
            foreach($allParams as $k=>$v) {
                if ( ltrim($k,'_') !== $k ){
                    $allParams[ltrim($k,'_')] = $v;
                    // Hapus KEY lama di Array (yg ada underscore-nya)
                    unset($allParams[$k]);
                }
            }
            // Cari field parameter yang merupakan binding
            preg_match_all("/:\w+/", $query, $out);
            // karena hasil array dari preg_match_all ada tanda ":" maka replace dulu
            $out = str_replace(':','',$out[0]);
            // Untuk mengecek bind di query yg belum ada parameter-nya
            $tmp = $out;

            // ambil nilai=>value $allParams yang ada di $out saja
            foreach($allParams as $k=>$v) {
                if(!in_array($k,$out)) {
                    unset($allParams[$k]);
                } else {
                    $key = array_search($k, $tmp);
                    unset($tmp[$key]);
                }
                }
            // Jika $tmp ada isinya, berarti itu parameter yg belum ada
            // dipastikan query akan error
            if(count($tmp) > 0) {
                die('{"success" : false,"msg":"Parameter query ada yg kurang, yaitu:<br />'.
                    implode(',', $tmp) .'"}');
            }

            return $allParams;
        } else {
            return $query;
        }
    }

    /**
     *  @brief Membuat Object Excel dari PHPExcel dan template xls atau xlsx
     *  @contributor Ebta
     *
     *  @param [in] $module nama module yang ingin dibuat reportnya
     *  @param [in] $template nama file di folder template yang akan digunakan
     *  @return mengembalikan object yg berisi method objPHPExcel dan sheet
     *
     *  @details lihat class.export.xls.php
     */
    public function createExcelExport($module, $template, $fileType = 'Excel2007' ) {
        require_once('class.export.xls.php');
        $tplDir = $this->getTemplatePath($module);
        try {
            $hasil  = new ExportXls($tplDir . $template, $fileType);
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            exit;
        }
        return $hasil;
    }

    /**
     *  @brief Membuat object tcPdf untuk export HTML ke PDF
     *  @contributor Arkan,..
     *  @return tcPdf object
     */
    public function createHtml2Pdf($moduleName, $arrData, $tpl) {
        require_once(dirname(dirname(__FILE__)) . '/tcpdf/tcpdf.php');
        require(dirname(dirname(__FILE__)) . '/Mustache/Autoloader.php');
        Mustache_Autoloader::register();
        $html = $this->getTemplatePath($moduleName).$tpl;
        $content = file_get_contents($html);
        if(is_array($arrData)) {
            $mus = new Mustache_Engine;
            $html = $mus->render($content,$arrData);
        }
//        echo $html;exit();
        // create new PDF document
        $pdfObj = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//        $pdfObj = new Html2pdf();
        $pdfObj->content = $html;

        /*
      NOTES:
       - To create self-signed signature: openssl req -x509 -nodes -days 365000 -newkey rsa:1024 -keyout tcpdf.crt -out tcpdf.crt
       - To export crt to p12: openssl pkcs12 -export -in tcpdf.crt -out tcpdf.p12
       - To convert pfx certificate to pem: openssl pkcs12 -in tcpdf.pfx -out tcpdf.crt -nodes
      */

// set certificate file
        $certificate = 'file://'.dirname(dirname(__FILE__)) . '/tcpdf/data/cert/tcpdf.crt';
//        echo $certificate;exit();
// set additional information
        $info = array(
            'Name' => 'SLEMANKAB',
            'Location' => 'Kominfo',
            'Reason' => 'dinas kominfo',
            'ContactInfo' => 'http://www.slemankab.go.id',
        );

// set document signature
//        $pdfObj->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info);
// define active area for signature appearance
        $pdfObj->setSignatureAppearance(180, 60, 15, 15);
        return $pdfObj;
    }

    public function createHtml2Pdf_old($moduleName = null) {
        require_once('class.html2pdf.php');
        $pdfObj = new Html2pdf();
        $pdfObj->templateDir = $this->getTemplatePath($moduleName);
        return $pdfObj;
    }

    /**
     *  @brief Membuat object mPDF untuk export HTML ke PDF
     *  @contributor Ebta,..
     *
     *  $param $moduleName Nama Module, jika null akan mengambil caller Class
     *  @return mPDF object
     *
     *  @details Lihat kelas class.html2pdf.php untuk lebih detailnya
     */
    public function createHtmlExport($moduleName = null) {
        require_once('class.export.html.php');
        $htmlObj = new ExportHtml();
        $htmlObj->templateDir = $this->getTemplatePath($moduleName);
        return $htmlObj;
    }

    // Digunakan untuk mengambil nama kolom, jika ada dot dengan nama tabel
    // misalnya tabelA.kolom1 --> kolom1
    // Juga menghapus tanda strip '_' sebelumnya jika ada
    private function trimLeftDot($str) {
        $dot = strpos($str,'.');
        if($dot !== false) {
            return substr($str,$dot+1);
        } else {
            return $str;
        }
    }

    // hilangkan tanda underscore '_' di awal param jika ada
    private function trimLeftChar($params,$chr='_') {
        if(is_array($params) && (count($params) > 0) ) {
            foreach($params as $k=>$v) {
                if ( ltrim($k,'_') !== $k ){
                    $params[ltrim($k,'_')] = $v;
                    // Hapus KEY lama di Array (yg ada underscore-nya)
                    unset($params[$k]);
                }
            }
            return $params;

        } else {
            die('Error parameter in trimLeftChar (Not Array)');
        }
    }

    // Digunakan di setiap execute $sql dan $param, tetapi $sql = QueryBuilder
    private function checkIfQueryBuilder($sqlObj,&$params = null) {
        if($sqlObj instanceof QueryBuilder) {
            $_qry = $sqlObj->getSql();
            $params = $sqlObj->getParams();
        } else {
            $_qry = $sqlObj;
        }
        return $_qry;
    }


    /**
     *  @brief Menambahkan Query Where =, multi parameter
     *  @contributor Ebta,..
     *
     *  @param [in] $params parameter yg dikirim, pass by reference
     *  @param [in] $colsName nama fields di query, pisahkan dg tanda koma jika
     *  * lebih dari satu nama. Bisa menggunakan dot. Misal : "nama,tabelA.alamat"
     *  @return query WHERE, dengan parameter bindingnya
     *  * misal " AND col1=:col1 AND col2:col2 AND col3=:col3 "
     *
     *  @details Jika $parsName kosong (null), maka parameter akan otomatis mengambil
     *  * dari $colsName, dengan melakukan trimLeftDot jika ada nama alias untuk tabel
     */
    public function addQueryEqualByParam(&$params, $colsName, $parsName = null) {
        $params = $this->trimLeftChar($params);

        $cols = explode(',',$colsName);
        if(empty($parsName)) {
            $parsName = $colsName;
        }
        $pars = explode(',', $parsName);

        if(count($cols) !== count($pars)) {
            die('Jumlah kolom dan parameter addQueryEqualByParam tidak sama');
        }

        $hasil = '';
        for($i=0;$i< count($cols); $i++) {
            $colName   = $cols[$i];
            $fieldName = $this->trimLeftDot($pars[$i]);
            $parValue  = trim(isset($params[$fieldName])? strval($params[$fieldName]) : '' );
            if($parValue != '') {
                $hasil .= " AND $colName=:$fieldName ";
            }
        }
        return $hasil;
    }

    // Mirip dengan fungsi sebelumnya dengan IN.
    public function addQueryIn(&$params,$colName,$parName = null) {
        $params = $this->trimLeftChar($params);
        if(empty($parName)) {
            // jika colName ada titik untuk table, misal tabel1.nama
            $parName = $this->trimLeftDot($colName);
        }
        $tmp = trim(isset($params[$parName])? $params[$parName] : '' );
        if ($tmp === '') {
            return '';
        } else {
            return " AND $colName IN ($tmp)";
            //return " AND $colName IN (:$parName)"; // --> Masih Bermasalah... !!
        }
    }

    /**
     *  @brief menghasilkan query : AND $colName LIKE '%val%'
     *  @contributor Ebta,..
        *
     *  @param [in] &$params $params, karena ada '&', berarti param by reference
     *  @param [in] $colName Nama kolom atau filed di Table
     *  @param [in] $value String atau nilai yg ingin dicari
     *  @return misalnya: AND $colName LIKE '%...%'
     */
    public function addQueryLike(&$params,$colName,$value,$operator = 'AND') {
        if(trim($value) === '') {
            return '';
        } else {
            $params["par_cari_x"] = '%' . $value . '%';
            return " $operator $colName LIKE :par_cari_x ";
        }
    }

    /**
     *  @brief Membuat Query LIKE dari beberapa kolom jika yg dicari sama
     *  @contributor Ebta,..
     *
     *  @param [in] $params variabel global (akan di alter), biasanya $_GET
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
    public function addQueryLikeByValue(&$params,$colsName,$value, $operator = 'OR') {
        if(trim($value) === '') {
            return '';
        } else {
            $cols = explode(',',$colsName);
            if(count($cols) < 2) {
                return $this->addQueryLike($params,$colName,$value);
            } else {
                for($i=0;$i< count($cols); $i++) {
                    $params["par_cari_$i"] = '%' . $value . '%';
                    $cols[$i] = $cols[$i] . " LIKE :par_cari_$i";
                }
                $operator = strtoupper(trim($operator));
                $hasil  = implode(" $operator ",$cols);
                if($operator === 'OR') {
                    $hasil = " AND ($hasil)";
                }
                return $hasil;
            }
        }
    }

    /**
     *  @brief Membuat Query LIKE dari satu atau lebih kolom tabel database
     *  @contributor Ebta,..
     *
     *  @param [in] $params variabel global (akan di alter), pass by reference
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
    public function addQueryLikeByParam(&$params, $colsName, $operator = 'OR', $parsName = null) {
        $params = $this->trimLeftChar($params);
        $cols = explode(',',$colsName);
        if(empty($parsName)) {
            $parsName = $colsName;
        }
        $pars = explode(',', $parsName);

        if(count($cols) !== count($pars)) {
            die('Jumlah kolom dan parameter addQueryEqualByParam tidak sama');
        }

        $args = array();
        for($i=0;$i< count($cols); $i++) {
            $colName   = $cols[$i];
            $fieldName = $this->trimLeftDot($pars[$i]);
            $parValue  = trim(isset($params[$fieldName])? $params[$fieldName] : '' );
            if(!empty($parValue)) {
                $params[$fieldName] = '%' . $parValue . '%';
                $args[] = "$colName LIKE :$fieldName";
            }
        }
        if(empty($args)) {
            return '';
        } else {
            $operator = strtoupper(trim($operator));
            $hasil = implode(" $operator ",$args);
            if($operator === 'OR') {
                return " AND ($hasil)";
            } else {
                return " AND $hasil";
            }
        }
    }

    /** =========================================================================
     *  BASE FUNCTION QUERY DENGAN PARAMETER
     *  -------------------------------------------------------------------------
     *  Digunakan untuk dasar pembuatan fungsi query Database MySQL dan ORACLE
     *  (fungsi-fungsi dibawah : dbData...dan dbFw... )
     *  =========================================================================
     */

    /**
     *  @brief Query ke database MySQL
     *
     *  @param [in] $dbh DB Connection, dibuat dengan new PDO();
     *  @param [in] $sqlObj SQL query atau QueryBuilder
     *  @param [in] $params Parameter yg akan di bind ke query
     *  @return PDOStatement
     *
     *  @details Details
     */
    private function dbMysqlQueryFilter($dbh, $sqlObj, $params = null) {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        $filteredParams = $this->createQueryParams($sql,$params);
        // var_dump($sql, $params, $filteredParams); exit;
        $sth = $dbh->prepare($sql);
        if(is_array($params) && (count($params) > 0) && (count($filteredParams) > 0) ) {
            foreach($filteredParams as $param => $value) {
                // jika awalnya angka 0, anggap sbg string
                $is_first_zero = substr((string)$value,0,1) === '0';
                if (!$is_first_zero && (is_int($value) || ctype_digit($value)) && ( strval($value) < '2147483647' ) ) {
                    $value = intval($value);
                    $type = PDO::PARAM_INT;
                } elseif ($value === NULL) {
                    $type = PDO::PARAM_NULL;
                } else {
                    $type = PDO::PARAM_STR;
                }

                if (is_int($param) || ctype_digit($param)) {
                    $param++;
                }
                $sth->bindValue($param, $value, $type);
            }
        }
        $sth->execute();
        // $sth->debugDumpParams();
        return $sth;
    }

    /**
     *  @brief Otomatis memfilter parameter sesuai dengan yg di bind di $sql
     *  @contributor Ebta,..
     *
     *  @param [in] $dbora = instant dari object DBORA();
     *  @param [in] $sql Query input ber-parameter. exp: SELECT * FROM x WHERE a=:abc
     *  @param [in] $params Array semua parameter yang otomatis difilter
     *  @return $this->dbOra->Select()
     *
     *  @details Query dari database di class DBORA (ORACLE)
     */
    private function dbOracleQueryFilter($sqlObj, $params = null) {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        if(is_array($params) && (count($params) > 0) ) {
            $filteredParams = $this->createQueryParams($sql,$params);
            return $this->dbDataConn->Select($sql, $filteredParams);
        } else {
            return $this->dbDataConn->Select($sql);
        }
    }

    /** =========================================================================
     *  FUNSI-FUNGSI Akses Database DATA (Aplikasi)
     *  -------------------------------------------------------------------------
     *  Sementara tersedia untuk database MySQL dan ORACLE
     *  Konfigurasi lihat di lib/server/config.php (konstanta DB_DATA_xx )
     *  =========================================================================
     */

    /**
     *  @brief Otomatis memfilter parameter sesuai dengan yg di bind di $sql
     *  @contributor Ebta,..
     *
     *  @param [in] $sqlObj bisa Query atau QueryBuilder object
     *  @param [in] $params Array semua parameter yang otomatis difilter
     *  @return statement id atau PDO Statement
     *
     *  @details Query dari database DATA (baik Oracle atau MySQL)
     */
    public function dbDataQuery($sqlObj, $params = null) {
        if(DB_DATA_USING_MYSQL) {
            return $this->dbMysqlQueryFilter($this->dbDataConn,$sqlObj, $params);
        } elseif(DB_DATA_USING_ORACLE) {
            return $this->dbOracleQueryFilter($sqlObj, $params);
        }
    }

    // Alias dari dbDataQuery
    public function dbDataSelect($sql,$params = null) {
        return $this->dbDataQuery($sql,$params);
    }

    /**
     *  @brief Wrapper untuk Execute Query : Insert, Update, Delete
     *  @contributor Ebta,..
     *
     *  @param [in] $sql Query-nya
     *  @param [in] $params parameter yang akan di binding
     *  @return JSON dengan property success, serta pesan
     *
     *  @details Pesan bisa ditambahkan di parameter $msgSuccess, $msgFail
     */
    public function dbDataExecute($sqlObj, $params = null, $msgSuccess = '', $msgFail = '') {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        $sth = $this->dbDataQuery($sql,$params);
        if($sth) {
            $msg = empty($msgSuccess) ? 'Berhasil menyimpan' : addslashes($msgSuccess);
            return '{"success" : true,"msg":"'. $msg .'"}';
        } else {
            $msg = empty($msgFail) ? 'Gagal menjalankan query' : addslashes($msgFail);
            return '{"success" : false,"msg":"' . $msg .'"}';
        }
    }

    /**
     *  @brief Mengambil value dari hasil kolom pertama baris pertama
     *  @contributor Ebta,..
     *
     *  @param [in] $sql
     *  @param [in] $params
     *  @return Return_Description
     *
     *  @details Details
     */
    public function dbDataGetValue($sql,$params = null) {
        $sth = $this->dbDataQuery($sql,$params);
        if($sth) {
            if(DB_DATA_USING_MYSQL) {
                return $sth->fetchColumn();
            } elseif(DB_DATA_USING_ORACLE) {
                $row = oci_fetch_array($sth,OCI_NUM);
                return $row[0];
            }
        } else {
            return '';
        }
    }

    // mengubah key object ke lowercase
    private function objKeysToLower($obj) {
        $type = (int) is_object($obj) - (int) is_array($obj);
        if ($type === 0) return $obj;
        reset($obj);
        while (($key = key($obj)) !== null)	{
            $element = $this->objKeysToLower(current($obj));
            switch ($type) {
                case 1:
                if (!is_int($key) && $key !== ($keyLowercase = strtolower($key))) {
                    unset($obj->{$key});
                    $key = $keyLowercase;
                }
                $obj->{$key} = $element;
                break;
                case -1:
                if (!is_int($key) && $key !== ($keyLowercase = strtolower($key))) {
                    unset($obj[$key]);
                    $key = $keyLowercase;
                }
                $obj[$key] = $element;
                break;
            }
            next($obj);
        }
        return $obj;
    }

    private function &keysToLower(&$obj) {
        $type = (int) is_object($obj) - (int) is_array($obj);
        if ($type === 0) return $obj;
        foreach ($obj as $key => &$val) {
            $element = $this->keysToLower($val);
            switch ($type) {
                case 1:
                if (!is_int($key) && $key !== ($keyLowercase = strtolower($key))) {
                    unset($obj->{$key});
                    $key = $keyLowercase;
                }
                $obj->{$key} = $element;
                break;
                case -1:
                if (!is_int($key) && $key !== ($keyLowercase = strtolower($key))) {
                    unset($obj[$key]);
                    $key = $keyLowercase;
                }
                $obj[$key] = $element;
                break;
            }
        }
        return $obj;
    }

    /**
     *  @brief Mengirim langsung semua result set ke browser
     *  @contributor Ebta,..
     *
     *  @param [in] $sth OCI statement identifier, misal dari dbSelectFilter
     *  @param [in] $total jika diisi akan digunakan, berguna untuk query Limit
     *  * yang totalnya tidak sama dengan hasil di result set
     *  @return JSON yang sudah di encode, langsung kirim ke client
     *
     *  @details Karena result dari Oracle nama field selalu UPPERCASE, maka di convert
     *  * dulu ke lowercase, jd di client (js), akses selalu dengan lowercase..
     */
    public function dbDataReturnAll($sth, $total = null, $returnAsArray = false) {
        if($sth) {
            $total_internal = 0;
            if(DB_DATA_USING_MYSQL) {
                $data  = $sth->fetchAll(PDO::FETCH_OBJ);
                $total_internal = count($data);
            } elseif(DB_DATA_USING_ORACLE) {
                $data = array();
                while($row = $this->dbDataConn->FetchObject($sth)) {
                    // $data[] = array_change_key_case($row, CASE_LOWER);
                    $data[] = $this->keysToLower($row);
                    $total_internal++;
                }
            }
            // jika hasil yg diinginkan dalam array, return $data;
            if ($returnAsArray) return $data;

            $total = empty($total) ? $total_internal : $total;
            $hasil = json_encode($data);
            if($total == 0) {
                return '{"success" : false,"msg":"Hasil Query kosong atau tidak ada"}';
            }
            return '{"success" : true, "total":'.$total .', "result":' . $hasil . '}';
        } else {
            if ($returnAsArray) return false;
            return '{"success" : false,"msg":"Hasil Query kosong atau tidak ada"}';
        }
    }

    /**
     *  @brief Otomatis melakukan select dari $sql dan return semua rows field-nya
     *  @contributor Ebta,..
     *
     *  @param [in] $sql Query SELECT
     *  @param [in] $params Query Paramenter Binding
     *  @param [in] $returnAsArray apakah hasilnya dalam JSON text atau Array
     *  @return JSON text atau Array, sesuai parameter $returnAsArray
     *
     *  @details Details
     */
    public function dbDataQueryAndReturnAll($sqlObj,$params = null, $returnAsArray = false) {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        $sth = $this->dbDataQuery($sql,$params);
        return $this->dbDataReturnAll($sth,null, $returnAsArray);
    }

    // Alias untuk fungsi dbDataQueryAndReturnAll
    public function dbDataSelectAndReturnAll($sqlObj,$params = null, $returnAsArray = false) {
        return $this->dbDataQueryAndReturnAll($sqlObj,$params, $returnAsArray);
    }

    public function dbDataRowsCount($sqlObj, $params = null) {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        if(DB_DATA_USING_MYSQL) {
            $sql = sprintf('SELECT Count(*) FROM (%s) xTableX',$sql);
        } elseif(DB_DATA_USING_ORACLE) {
            $sql = sprintf('SELECT COUNT(*) AS TOTAL FROM (%s)',$sql);
        }
        return $this->dbDataGetValue($sql,$params);
    }

    /**
     *  @brief Query Select dengan LIMIT (ORACLE)
     *  @contributor Ebta, AskTom tips
     *
     *  @param [in] $sql Query-nya
     *  @param [in] $params Paramenter yang akan di bind
     *  @param [in] $start mulai row ke
     *  @param [in] $limit jumlah row yang akan diambil
     *  @return OCI statement identifier
     *
     *  @details Jika start dan limit null, akan mengambil dari parameter $params
     */
    public function dbDataSelectLimit($sqlObj, $params, $start= null, $limit= null) {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        // jika $start dan $limit = null, maka ambil dari parameter $params
        $start = empty($start) ? $params['start'] : $start;
        $limit = empty($limit) ? $params['limit'] : $limit;

        if(DB_DATA_USING_MYSQL) {
            $sql .= ' LIMIT :start,:limit';
        } elseif(DB_DATA_USING_ORACLE) {
            $params['min_row'] = $start + 1;
            $params['max_row'] = $start + $limit;
            $sql = "
                SELECT *
                FROM (
                    SELECT ztbl.*, ROWNUM rnum
                    FROM ($sql) ztbl
                    WHERE ROWNUM <= :max_row
                    )
                WHERE rnum >= :min_row";
        }
        return $this->dbDataQuery($sql,$params);
    }


    /**
     *  @brief Wrapper untuk Query Select With Paging
     *  @contributor Ebta,..
     *
     *  @param [in] $sql Query-nya
     *  @param [in] $params parameter
     *  @param [in] $start jika null akan mengambil dari $params
     *  @param [in] $limit jika null akan mengambil dari $params
     *  @return JSON yang siap dikirim ke Client
     *
     *  @details Details
     */
    public function dbDataSelectPaging($sqlObj, $params = null, $start= null, $limit= null) {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        $total = $this->dbDataRowsCount($sql,$params);
        if($total > 0) {
            $sth = $this->dbDataSelectLimit($sql,$params,$start,$limit);
            return $this->dbDataReturnAll($sth,$total);
        } else {
            return '{"success" : false,"msg":"Data tidak ditemukan","result": ""}';
        }
    }

    public function dbDataLastInsertId() {
        if(DB_DATA_USING_MYSQL) {
            return $this->dbDataConn->lastInsertId();
        } else {
            return 'LastInsertId for MySQL Only';
        }
    }


    /** ============================================================================
     *  FUNGSI-FUNGSI ACCESS Database Framework (MYSQL)
     *  ----------------------------------------------------------------------------
     *  Akses menggunakan koneksi dari class Os(), metode seperti fungsi2 dbData...
     *  Hanya saja koneksi menggunakan akses Database MySQL dbFw.
     *  Setting ada di lib/server/config.php, konstanta yang diawali dengan DB_FW_
     *  ============================================================================
     */

    /**
     *  @brief Otomatis malakukan eksekusi dari koneksi dbFw (MySQL), dengan parameter $params
     *  @contributor Ebta,..
     *
     *  @param [in] $sql the query
     *  @param [in] $params array parameter yang akan di binding ke query, otomatis di filter
     *  @return return adalah PDOStatement
     *
     *  @details
     */
    public function dbFwQuery($sqlObj,$params = null) {
        return $this->dbMysqlQueryFilter($this->dbFw,$sqlObj, $params);
    }

    // Alias dari fungsi dbFwQuery
    public function dbFwSelect($sql,$params = null) {
        return $this->dbFwQuery($sql, $params);
    }

    public function dbFwExecute($sqlObj, $params =null, $msgSuccess = '', $msgFail = '') {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        $sth = $this->dbFwSelect($sql,$params);
        if($sth) {
            $msg = empty($msgSuccess) ? 'Berhasil menyimpan' : addslashes($msgSuccess);
            return '{"success" : true,"msg":"'. $msg .'"}';
        } else {
            $msg = empty($msgFail) ? 'Gagal menjalankan query' : addslashes($msgFail);
            return '{"success" : false,"msg":"' . $msg .'"}';
        }
    }

    public function dbFwGetValue($sql,$params = null) {
        $sth = $this->dbFwQuery($sql,$params);
        if($sth) {
            return $sth->fetchColumn();
        } else {
            return '';
        }
    }

    public function dbFwReturnAll($sth, $total = null, $returnAsArray = false) {
        if($sth) {
            $data  = $sth->fetchAll(PDO::FETCH_OBJ);
            if ($returnAsArray) return $data;
            $total = empty($total) ? count($data) : $total;
            $hasil = json_encode($data);
            return '{"success" : true, "total":'.$total .', "result":' . $hasil . '}';
        } else {
            if ($returnAsArray) return false;
            return '{"success" : false,"msg":"Hasil Query kosong atau tidak ada"}';
        }
    }

    public function dbFwQueryAndReturnAll($sqlObj,$params = null, $returnAsArray = false) {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        $sth = $this->dbFwQuery($sql,$params);
        return $this->dbFwReturnAll($sth,null, $returnAsArray);
    }

    public function dbFwSelectAndReturnAll($sqlObj,$params = null, $returnAsArray = false) {
        return $this->dbFwQueryAndReturnAll($sqlObj,$params,$returnAsArray);
    }

    /**
     *  @brief Melakukan query untuk menghitung jumlah record
     *  @contributor Ebta,..
     *
     *  @param [in] $sql the Query
     *  @param [in] $params parameter yang akan di filter
     *  @return total jumlah record
     *
     *  @details Details
     */
    public function dbFwRowsCount($sqlObj,$params = null) {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        $sqlMod = sprintf('SELECT Count(*) FROM (%s) tblTmp1',$sql);
        $sth = $this->dbFwQuery($sqlMod,$params);
        if($sth->execute()) {
            return (int)$sth->fetchColumn();
        } else {
            return 0;
        }
    }

    public function dbFwSelectLimit($sqlObj, $params = null, $start= null, $limit= null) {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        // jika $start dan $limi = null, maka ambil dari parameter $params
        $start = empty($start) ? $params['start'] : $start;
        $limit = empty($limit) ? $params['limit'] : $limit;

        $sql .= ' LIMIT :start,:limit';
        // print_r($sql); print_r($params); exit;
        return $this->dbFwSelect($sql,$params);
    }

    public function dbFwSelectPaging($sqlObj, $params = null, $start= null, $limit= null) {
        $sql = $this->checkIfQueryBuilder($sqlObj,$params);
        $total = $this->dbFwRowsCount($sql,$params);
        if($total > 0) {
            $sth = $this->dbFwSelectLimit($sql,$params,$start,$limit);
            return $this->dbFwReturnAll($sth,$total);
        } else {
            return '{"success" : false,"msg":"Data tidak ditemukan","result": ""}';
        }
    }

    public function dbFwLastInsertId() {
        return $this->os->conn->lastInsertId();
    }


    public function PUBLIC_HTML2PDF() {
        $pdf = $this->createHtml2Pdf_old();
        $pdf->setPageSize(210, 330, 'L'); // width, height, orientation = [P]ortrait [L]anscape
        $pdf->setMargins(7, 7, 10, 15); // left, right, top, bottom (milimeter)

        $html = urldecode($_POST['html']);
        // ambil title, tempatkan di footer dan hapus yg di head
        preg_match('/<title>(.+)<\\/title>/m', $html, $matches );
        if(count($matches) > 1) {
            $title = $matches[1];
            $html  = str_replace("<title>$title</title>", '<title></title>', $html);
        } else {
            $title = '';
        }

        $pdf->mpdf->defaultfooterline = 0;
        // Tanda | untuk mengatur posisi text -> Left|Center|Right (lih. doc. mPDF)
        $pdf->mpdf->setFooter("$title||hal {PAGENO} dari {nbpg}");
        $pdf->mpdf->WriteHTML($html);
        $pdf->savePdf("Save from HTML.pdf");
    }

    /** ============================================================================
     *  FUNGSI-FUNGSI LAINNYA
     *  ----------------------------------------------------------------------------
     *  Keterangan ...
     *  ============================================================================
     */

    /* User Otoritas Untuk menyimpan di tabel tertentu */
    public function isBolehSimpanData($jenis, $tahun, $user = null) {
        /* Rule: Cek Periode aktif user dulu, klo gak ada cek periode aktif umum*/
        $kode   = trim(strtoupper($jenis));
        $params = array('tahun' => $tahun, 'kode'=> $kode);
        if(!$user) {
            $params['user_id'] = $this->getUserLogin();
        } else {
            $params['user_id'] = $user;
        }

        $range = " AND (date_format(now(),'%Y%m%d') BETWEEN date_format(tgl_mulai,'%Y%m%d') AND date_format(tgl_selesai,'%Y%m%d') )";

        // Jika diluar periode aktif, cek periode_aktif_user
        $sql = "SELECT count(*) FROM periode_aktif_user
            WHERE upper(kode)=:kode AND tahun=:tahun AND user_id=:user_id AND aktif=1 ";
        $sql  = $sql . $range;
        //echo $sql; exit;
        $hasil =  $this->dbDataGetValue($sql,$params);
        if ($hasil > 0) {
            return true;
        }

        // cek apakah entry kode di periode_aktif di tahun yg bersangkutan sudah ada dan aktif
        $sql   = "SELECT count(*) FROM periode_aktif WHERE upper(kode)=:kode AND tahun=:tahun AND aktif=1 ";
        $sql   = $sql . $range;
        $hasil =  $this->dbDataGetValue($sql,$params);
        if ($hasil > 0) {
            return true;
        } else {
            return false;
        }

    }

    /*
	 * akses api client
	 * @contributor arkan*/
    public function requestApi($params) {
        include_once 'ApiClient.php';
        $apiclient = new ApiClient();
        $result = $apiclient->request($params);
        header('Content-Type: application/json');
        return $result;
    }

    public function getLocalListInstansi() {
        $sql = "SELECT id_instansi as kode_instansi, nama_instansi as instansi FROM reff_unit_kerja ORDER BY id_instansi ASC";
        return $this->dbDataSelectAndReturnAll($sql, null, true);
    }

}
