<?php

class ExportExcel extends Database
{
    function __construct()
    {
        parent::__construct();
    }

    public function ACTION_list()
    {
        // Dummy list satisfying framework routing validation
        echo json_encode(array('success' => true, 'result' => array()));
    }

    public function ACTION_getDinas()
    {
        $sql = "SELECT DISTINCT instansi FROM data_pilah WHERE instansi != '' AND instansi IS NOT NULL ORDER BY instansi ASC";
        $result = $this->dbDataSelectAndReturnAll($sql, array(), true);
        echo json_encode(array('success' => true, 'result' => $result));
    }

    public function ACTION_getTahun()
    {
        $sql = "SELECT tahun FROM ref_tahun WHERE aktif = 1 ORDER BY tahun DESC";
        $result = $this->dbDataSelectAndReturnAll($sql, array(), true);
        echo json_encode(array('success' => true, 'result' => $result));
    }

    public function ACTION_excel()
    {
        $params = empty($_GET) ? $_POST : $_GET;
        $dinas = isset($params['dinas']) ? $params['dinas'] : '';
        $tahun = isset($params['tahun']) ? $params['tahun'] : '';

        if (!$dinas || !$tahun) {
            die('Error: Dinas dan Tahun tidak boleh kosong.');
        }

        // Fetch all active data matrices for the selected Dinas
        $sql = "SELECT id_data_pilah, kode_data_pilah, judul_data_pilah, instansi, header_baris 
                FROM data_pilah 
                WHERE instansi = :dinas AND aktif = 1 
                ORDER BY kode_data_pilah ASC";
        
        $stmt = $this->dbDataConn->prepare($sql);
        $stmt->execute([':dinas' => $dinas]);
        $matrices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Load PhpSpreadsheet
        require_once dirname(__FILE__) . '/../../vendor/autoload.php';
        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $objPHPExcel->removeSheetByIndex(0); // Remove default first worksheet

        if (empty($matrices)) {
            // Fallback worksheet if no data exists
            $sheet = $objPHPExcel->createSheet();
            $sheet->setTitle('Kosong');
            $sheet->setCellValue('A1', 'Tidak ada data matriks aktif ditemukan untuk Dinas: ' . $dinas);
            $sheet->getStyle('A1')->getFont()->setItalic(true);
        } else {
            foreach ($matrices as $m) {
                $kode = $m['kode_data_pilah'];

                // 1. Fetch columns
                $sqlK = "SELECT * FROM data_pilah_kolom WHERE kode_data_pilah = :kode ORDER BY kode_kolom";
                $stmtK = $this->dbDataConn->prepare($sqlK);
                $stmtK->execute([':kode' => $kode]);
                $koloms = $stmtK->fetchAll(PDO::FETCH_ASSOC);

                // 2. Fetch rows
                $sqlB = "SELECT * FROM data_pilah_baris WHERE kode_data_pilah = :kode ORDER BY no_urut ASC";
                $stmtB = $this->dbDataConn->prepare($sqlB);
                $stmtB->execute([':kode' => $kode]);
                $barisList = $stmtB->fetchAll(PDO::FETCH_ASSOC);

                // 3. Fetch cells
                $sqlC = "SELECT * FROM data_pilah_cell WHERE kode_data_pilah = :kode AND tahun = :tahun";
                $stmtC = $this->dbDataConn->prepare($sqlC);
                $stmtC->execute([':kode' => $kode, ':tahun' => $tahun]);
                $cells = $stmtC->fetchAll(PDO::FETCH_ASSOC);

                $cellMap = array();
                foreach ($cells as $c) {
                    $cellMap[$c['kode_baris'] . '|' . $c['kode_kolom']] = $c['val'];
                }

                // 4. Create new Worksheet
                $sheet = $objPHPExcel->createSheet();
                
                // Clean worksheet title: max 31 chars, no special characters \ / ? * : [ ]
                $rawTitle = $m['judul_data_pilah'];
                $cleanTitle = preg_replace('/[\\\\\/\\?\\*\\:\\[\\]]/', '', $rawTitle);
                // Truncate to maximum 25 characters to allow appending " (XX)" and fitting within 31-char limit
                $cleanTitle = substr($cleanTitle, 0, 25) . ' (' . $kode . ')';
                $sheet->setTitle($cleanTitle);

                // ---- TITLE ROW ----
                $totalCols = count($koloms) + 2; // No + Nama Baris + Data columns
                $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

                $sheet->mergeCells('A1:' . $lastColLetter . '1');
                $sheet->setCellValue('A1', $m['judul_data_pilah'] . ' — Tahun ' . $tahun);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $sheet->mergeCells('A2:' . $lastColLetter . '2');
                $sheet->setCellValue('A2', 'Instansi: ' . $m['instansi']);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true);

                // ---- HEADER ROW ----
                $headerRow = 4;
                $sheet->setCellValue('A' . $headerRow, 'No');
                $sheet->setCellValue('B' . $headerRow, $m['header_baris'] ? $m['header_baris'] : 'Kecamatan');

                $colIdx = 3;
                foreach ($koloms as $k) {
                    $label = $k['header_kolom'] ? $k['header_kolom'] . ' ' . $k['nama_kolom'] : $k['nama_kolom'];
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $sheet->setCellValue($colLetter . $headerRow, $label);
                    $colIdx++;
                }

                // Style headers
                $headerRange = 'A' . $headerRow . ':' . $lastColLetter . $headerRow;
                $headerStyle = array(
                    'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF'), 'size' => 11),
                    'fill' => array(
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => array('rgb' => '1A5C5A') // Sleman Administrative Deep Teal
                    ),
                    'alignment' => array(
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ),
                    'borders' => array(
                        'allBorders' => array('borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    )
                );
                $sheet->getStyle($headerRange)->applyFromArray($headerStyle);
                $sheet->getRowDimension($headerRow)->setRowHeight(28);

                // ---- DATA ROWS ----
                $dataRow = $headerRow + 1;
                $no = 1;
                foreach ($barisList as $b) {
                    $sheet->setCellValue('A' . $dataRow, $no);
                    $sheet->setCellValue('B' . $dataRow, $b['nama_baris']);
                    $sheet->getStyle('B' . $dataRow)->getFont()->setBold(true);

                    $colIdx = 3;
                    foreach ($koloms as $k) {
                        $key = $b['kode_baris'] . '|' . $k['kode_kolom'];
                        $val = isset($cellMap[$key]) ? (float)$cellMap[$key] : 0;
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                        $sheet->setCellValue($colLetter . $dataRow, $val);
                        $colIdx++;
                    }
                    $no++;
                    $dataRow++;
                }

                // Style data rows
                $lastDataRow = $dataRow - 1;
                if ($lastDataRow >= ($headerRow + 1)) {
                    $dataRange = 'A' . ($headerRow + 1) . ':' . $lastColLetter . $lastDataRow;
                    $dataStyle = array(
                        'borders' => array(
                            'allBorders' => array('borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                        )
                    );
                    $sheet->getStyle($dataRange)->applyFromArray($dataStyle);
                    
                    // Center the No values
                    $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $lastDataRow)
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }

                // Auto size columns
                for ($i = 1; $i <= $totalCols; $i++) {
                    $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
                }
            }
        }

        // ---- DOWNLOAD / STREAM TO BROWSER ----
        $filename = 'Laporan_Ekspor_Excel_' . preg_replace('/[^A-Za-z0-9_]/', '_', $dinas) . '_' . $tahun . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $objWriter->save('php://output');

        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
        exit;
    }
}
