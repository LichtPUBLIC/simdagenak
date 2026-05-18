<?php

class DataInteropApi extends Database
{

    function __construct()
    {
        parent::__construct(true);
    }

    private function _stringDistinct($string, $delimiter = ', ', $delimiterResturn = ', ')
    {
        $hasil = new stdClass();
        $arrInstansi = explode($delimiter, $string);
        $arrInstansiDistinct = array_unique($arrInstansi);
        $hasil->count = count($arrInstansiDistinct);
        $hasil->string = implode($delimiterResturn, $arrInstansiDistinct);
        return $hasil;
    }

    public function Test($params, $retResult = false)
    {

        //echo $sql;exit();
        echo 'Test';

    }
}