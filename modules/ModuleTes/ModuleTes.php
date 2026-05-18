<?php

class ModuleTes extends Database
{

    function __construct()
    {
        parent::__construct();
    }

    private function get_userId()
    {
        $user = new os;
        $userData = $user->getUserData();
        $userDataArr = json_decode($userData);
        $userId = $userDataArr->user_id;
        return $userId;
    }

    private function findField()
    {
        $findField = [
            "nama",
            "alamat",
            "jenis_kelamin"
        ];
        return $findField;
    }

    private function buildSqlSearchingCriteria($keywords, $findField)
    {
        $arrayKata = explode(' ', $keywords);
        foreach ($arrayKata as $hasil) {
            foreach ($findField as $fieldName) {
                $criteria[] = "LOWER($fieldName) like '%$hasil%'";
            }
            $queryCriteria[] = implode(" OR ", $criteria);
        }
        $resultCriteria = implode(" OR ", $queryCriteria);
        return $resultCriteria;
    }

    public function ACTION_list($return = false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $sql = 'SELECT * FROM sampel_tabel';
        if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sql .= " where " . $criteria;
        }
        if (isset($_POST['start'])) {
            $start = $_POST['start'];
            $limit = $_POST['length'];
            $sql .= " limit $start,$limit ";
        }
        $arrayData = $this->dbDataSelectAndReturnAll($sql, $params, true);
        if ($return) {
            return $arrayData;
        }
        $array = array();
        $sqlCount = "SELECT count(*) FROM sampel_tabel ";
        $countData = $this->dbDataGetValue($sqlCount);
        $array['recordsTotal'] = $countData;
        $array['recordsFiltered'] = $countData;
        $array['draw'] = $_POST['draw'];
        $array['data'] = (array)$arrayData;
        echo json_encode($array);
    }

    public function ACTION_listPrint($return = false)
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $userId = $this->get_userId();
        $sql = 'SELECT * FROM sampel_tabel';
        if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
            $keywords = strtolower($_POST['search']['value']);
            $findField = $this->findField();
            $criteria = $this->buildSqlSearchingCriteria($keywords, $findField);
            $sql .= " where " . $criteria;
        }
        if (isset($_POST['start'])) {
            $start = $_POST['start'];
            $limit = $_POST['length'];
            $sql .= " limit $start,$limit ";
        }
        echo $this->dbDataSelectAndReturnAll($sql, $params);

    }


    public function ACTION_add()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "INSERT INTO sampel_tabel (nama, alamat, jenis_kelamin) VALUES 
                                          (:nama,:alamat,:jenis_kelamin)";
        echo $this->dbDataExecute($sql, $params['data']);
    }

    public function ACTION_update()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "UPDATE sampel_tabel SET nama = :nama, 
                                        alamat = :alamat, 
                                        jenis_kelamin = :jenis_kelamin
                 WHERE id=:id";
        echo $this->dbDataExecute($sql, $params['data']);
    }

    public function ACTION_delete()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $sql = "DELETE FROM sampel_tabel WHERE id=:id";
        echo $this->dbDataExecute($sql, $params['data']);
    }

    public function ACTION_pdf()
    {
        $data['value'] = $this->ACTION_list(true);
        $i = 0;
        $data['judul'] = "Export data pdf";

        /**
         * Untuk memanggil fungsi create html2pdf, ada 3 parameter yang dikirimkan yaitu :
         *  - module name
         *  - data
         *  - template html
         *
         * @contributor arkan
         * */
        $pdf = $this->createHtml2Pdf(null, $data, 'tpl_pdf.html');
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Arkan Herawan');
        $pdf->SetTitle('Contoh export pdf');
        $pdf->SetSubject('export pdf dengan digital signature');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

// set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_RIGHT, PDF_MARGIN_TOP);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// add a page
        $pdf->AddPage();

// print a some of text
        $pdf->writeHTML($pdf->content, true, 0, true, 0);

//Close and output PDF document
        $pdf->Output('export_pdf_file.pdf', 'D');
    }

    public function ACTION_excel()
    {
        $params = isset($_GET) ? $_GET : $_POST;
        $data = $this->ACTION_listPrint(true);
        //print_r($data);
        //exit;
        $xlsExp = $this->createExcelExport($params['Module'], 'DaftarTanahTematik2.xlsx');

        $total = count($data);
        if ($total > 0) {
            $rowStart = 7;
            for ($i = 0; $i < $total; $i++) {
                $xlsExp->sheet->insertNewRowBefore($rowStart);
                $xlsExp->sheet->setCellValue("A$rowStart", $i + 1);
                $xlsExp->sheet->setCellValue("B$rowStart", $data[$i]->kode_tematik);
                $xlsExp->sheet->setCellValue("C$rowStart", $data[$i]->kelurahan);
                $xlsExp->sheet->setCellValue("D$rowStart", $data[$i]->kecamatan);
                $xlsExp->sheet->setCellValue("E$rowStart", $data[$i]->luas_perkiraan);
                $xlsExp->sheet->setCellValue("F$rowStart", $data[$i]->tahun);
                $xlsExp->sheet->setCellValue("G$rowStart", $data[$i]->keterangan);
                $rowStart++;
            }

            echo $xlsExp->saveReport('Daftar_Tanah_tematik_', true);
        } else {
            echo '{"success" : false, "msg":"Gagal export data tanah tematik"}';
        }
//        echo $this->dbFwSelectAndReturnAll($sql, $params);
    }


    /*upload file*/
    public function ACTION_upload()
    {
//        echo "ini $icon";exit();
//        echo $_FILES['file']['tmp_name'];exit();
        if (isset($_FILES["file"]["name"])) {
            if (!file_exists("data/")) {
                mkdir("data/");
            }
            if (isset($_FILES["file"]["type"])) {
                $temporary = explode(".", $_FILES["file"]["name"]);
                $file_extension = end($temporary);
                if (file_exists("data/" . $_FILES["file"]["name"])) {
                    echo $_FILES["file"]["name"] . " <span id='invalid'><b>already exists.</b></span> ";
                } else {
                    $array_files = array();
                    $shortname = date('d-m-Y-H-i-s') . '-' . $_FILES["file"]["name"];
                    $sourcePath = $_FILES['file']['tmp_name']; // Storing source path of the file in a variable
                    $targetPath = "data/" . $shortname; // Target path where file is to be stored
                    $array_files['nama_file_asli'] = $_FILES['file']['name'];
                    $array_files['nama_file_uploaded'] = $shortname;
                    $array_files['path_file'] = $targetPath;
                    $array_files['ekstensi_file'] = $file_extension;
                    if (move_uploaded_file($sourcePath, $targetPath)) {
                        /*koding disini ketika berhasil di upload*/
                        echo json_encode(
                            array(
                                'success' => true,
                                'result' => $array_files,
                            )
                        );
//                        echo '{"success": true,"msg" : "simpan berhasil upload"}';exit();
                        /*$sql = "insert into file_pendukung (file, id_item_indikator, tahun, keterangan, sumber,kode_item_indikator) VALUES
                                                           ('$file','$idItem','$tahun','$keterangan','$sumber','$kodeItem')";
                        echo $this->dbDataExecute($sql);*/
                    } else {
                        echo '{"success": false,"msg" : "gagal simpan.."}';
                        exit();
                    }
                }
            }
        } else {
            echo '{"success": false,"msg" : "Edit gagal.."}';
        }
    }
}




