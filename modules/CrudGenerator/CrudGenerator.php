<?php
date_default_timezone_set('Asia/Jakarta');
define('DB_NAME', 'fwsmartnew');

class CrudGenerator extends Database
{

    function __construct()
    {
        parent::__construct();
    }

    public function ACTION_getModul()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "SELECT * FROM modules ";
        echo $this->dbDataSelectAndReturnAll($sql);
    }

    public function ACTION_getTable()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema='" . DB_DATA_NAME . "'";
        echo $this->dbDataSelectAndReturnAll($sql);
    }

    /*get detail modul*/
    private function get_module_info($modul)
    {
        $sql = "select * from modules WHERE module_id='$modul'";
        return $this->dbDataSelectAndReturnAll($sql, null, true);
    }

    /*get table field*/
    private function get_table_field($tabel)
    {
        $sql = "SELECT `COLUMN_NAME` 
                    FROM `INFORMATION_SCHEMA`.`COLUMNS` 
                    WHERE `TABLE_SCHEMA`='" . DB_DATA_NAME . "' 
                        AND `TABLE_NAME`='$tabel';";
        return $this->dbDataSelectAndReturnAll($sql, null, true);
    }

    /*
     *
     * stdClass Object
(
    [module_id] => tes-generate
    [module] => TesGenerate
    [name] => Tes Hasil Generate Modul
    [description] => Percobaan generate modul
    [menu] => 026;Percobaan/
    [iconcls] => angle-double-right
    [icon] => thumbs-o-up
    [active] => 1
    [onmenu] => 1
    [onview] => tabpanel
)

    Array
(
    [0] => stdClass Object
        (
            [COLUMN_NAME] => id
        )

    [1] => stdClass Object
        (
            [COLUMN_NAME] => nama
        )

    [2] => stdClass Object
        (
            [COLUMN_NAME] => alamat
        )

    [3] => stdClass Object
        (
            [COLUMN_NAME] => jenis_kelamin
        )

)

*/
    /*proses generate html*/
    private function generate_html($modulInfo, $tableField)
    {
        $html = file_get_contents('modules/CrudGenerator/template/template.html');
        $html = str_replace('<!--judul-module-->', $modulInfo->name, $html);
        $th = '';
        $formInput = '';
        foreach ($tableField as $tabel) {
            $th .= "<th data-class=\"expand\">" . $tabel->COLUMN_NAME . "</th>";
            $formInput .= '<div class="form-group nopadding">
                        <label class="col-sm-2"><strong>' . $tabel->COLUMN_NAME . '</strong></label>
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="' . $tabel->COLUMN_NAME . '">
                        </div>
                    </div>';
        }
        $html = str_replace('<!--th-tabel-->', $th, $html);
        $html = str_replace('<!--form-input-field-->', $formInput, $html);
        if (!file_exists('modules/' . $modulInfo->module)) {
            mkdir('modules/' . $modulInfo->module . '/');
        }
        file_put_contents('modules/' . $modulInfo->module . '/' . $modulInfo->module . '.html', $html);
    }

    /*proses generate js*/
    private function generate_js($modulInfo, $tableField)
    {
        $js = file_get_contents('modules/CrudGenerator/template/template.js');
        $collumn = '';
        $perulanganTabelPrint = "data2[i]={};";
        foreach ($tableField as $tabel) {
            $collumn .= '{"data": "' . $tabel->COLUMN_NAME . '"},';
            $perulanganTabelPrint .= "data2[i].".$tabel->COLUMN_NAME." = nama=resp.result[i].".$tabel->COLUMN_NAME.";";
        }

        $js = str_replace('/*<!--datatable-collumn-->*/', $collumn, $js);
        $js = str_replace('/*perulangan-tabel-print*/', $perulanganTabelPrint, $js);
        $js = str_replace('/*untuk-nomor*/', $tableField[0]->COLUMN_NAME, $js);
        if (!file_exists('modules/' . $modulInfo->module)) {
            mkdir('modules/' . $modulInfo->module . '/');
        }
        file_put_contents('modules/' . $modulInfo->module . '/' . $modulInfo->module . '.js', $js);
    }

    /*proses generate php*/
    private function generate_php($modulInfo, $tableField, $namaTabel)
    {
        $php = file_get_contents('modules/CrudGenerator/template/template.php');
        $php = str_replace('namamodulclass', $modulInfo->module, $php);

        $fieldArr = array();
        $addField = array();
        $addFieldValue = array();
        $setUpdate = array();
        $where = '';
        $i = 0;
        foreach ($tableField as $tabel) {
            if ($i != 0) {
                $fieldArr[]= '"' . $tabel->COLUMN_NAME . '"';
                $addField[] = $tabel->COLUMN_NAME;
                $addFieldValue[] = ':' . $tabel->COLUMN_NAME;
                $setUpdate[] = $tabel->COLUMN_NAME . '=' . ':' . $tabel->COLUMN_NAME;
            } else {
                $where = $tabel->COLUMN_NAME . '=' . ':' . $tabel->COLUMN_NAME;
            }
            $i++;
        }
        $php = str_replace('/*array-field*/', implode(',', $fieldArr), $php);
        $php = str_replace('namatabel', $namaTabel, $php);
        $php = str_replace('tablefield', implode(',', $addField), $php);
        $php = str_replace('addFieldValue', implode(',', $addFieldValue), $php);
        $php = str_replace('setUpdate', implode(',', $setUpdate), $php);
        $php = str_replace('whereKondisi', $where, $php);

        $php = str_replace('/*untuk-nomor*/', $tableField[0]->COLUMN_NAME, $php);
        if (!file_exists('modules/' . $modulInfo->module)) {
            mkdir('modules/' . $modulInfo->module . '/');
        }
        file_put_contents('modules/' . $modulInfo->module . '/' . $modulInfo->module . '.php', $php);
    }

    /*proses generate template print*/
    private function generate_tpl_print($modulInfo, $tableField, $namaTabel)
    {
        $tpl = file_get_contents('modules/CrudGenerator/template/tpl-print.html');
        $thead = "";
        $record = "";
        foreach ($tableField as $tabel) {
            $thead .="<th>".$tabel->COLUMN_NAME."</th>";
            $record .= "<td>{{".$tabel->COLUMN_NAME."}}</td>";
        }
        $tpl = str_replace('<!--thead-tabel-->', $thead, $tpl);
        $tpl = str_replace('<!--mustache-record-->', $record, $tpl);

        if (!file_exists('modules/' . $modulInfo->module."/template")) {
            mkdir('modules/' . $modulInfo->module . '/template');
        }
        file_put_contents('modules/' . $modulInfo->module . '/template/tpl_pdf.html', $tpl);
    }

    public function ACTION_generate()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $moduleInfo = $this->get_module_info($params['data']['modul']);
        $tableField = $this->get_table_field($params['data']['tabel']);
//        print_r($tableField);exit();
        $this->generate_html($moduleInfo[0], $tableField);
        $this->generate_js($moduleInfo[0], $tableField);
        $this->generate_php($moduleInfo[0], $tableField, $params['data']['tabel']);
        $this->generate_tpl_print($moduleInfo[0], $tableField, $params['data']['tabel']);
        echo '{"success":true}';
    }
}
