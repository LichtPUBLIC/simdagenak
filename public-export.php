<?php
/**
 * public-export.php
 * Endpoint untuk ekspor data matriks dari dashboard publik ke PDF atau Excel.
 * Dipanggil via GET: public-export.php?type=pdf&kode=01&tahun=2024
 */
// ini_set("display_errors", 0);
// error_reporting(0);

if (!defined('PATH_TEMPLATE')) define('PATH_TEMPLATE', 'template/smartadmin/');
require_once dirname(__FILE__).'/lib/server/class.os.php';

$os  = new Os();
$conn = $os->conn;

$type  = isset($_GET['type'])  ? $_GET['type']  : '';
$kode  = isset($_GET['kode'])  ? $_GET['kode']  : '';
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : '';

if (!$kode || !$tahun || !in_array($type, ['pdf','excel'])) {
    die('Parameter tidak lengkap. Gunakan: ?type=pdf|excel&kode=XX&tahun=YYYY');
}

// =====================================================================
// AMBIL DATA DARI DATABASE (sama persis dengan public-service.php)
// =====================================================================

// Judul data pilah
$sqlJudul = "SELECT judul_data_pilah, instansi, header_baris FROM data_pilah WHERE kode_data_pilah = :kode";
$stmtJ = $conn->prepare($sqlJudul);
$stmtJ->execute([':kode' => $kode]);
$dataPilah = $stmtJ->fetch(PDO::FETCH_ASSOC);
$judul   = $dataPilah ? $dataPilah['judul_data_pilah'] : 'Data Pilah';
$instansi = $dataPilah ? $dataPilah['instansi'] : '-';
$headerBaris = ($dataPilah && $dataPilah['header_baris']) ? $dataPilah['header_baris'] : 'Kecamatan';

// Kolom
$sqlKolom = "SELECT * FROM data_pilah_kolom WHERE kode_data_pilah = :kode ORDER BY kode_kolom";
$stmtK = $conn->prepare($sqlKolom);
$stmtK->execute([':kode' => $kode]);
$koloms = $stmtK->fetchAll(PDO::FETCH_ASSOC);

// Baris
$sqlBaris = "SELECT * FROM data_pilah_baris WHERE kode_data_pilah = :kode ORDER BY no_urut ASC";
$stmtB = $conn->prepare($sqlBaris);
$stmtB->execute([':kode' => $kode]);
$barisList = $stmtB->fetchAll(PDO::FETCH_ASSOC);

// Cell values
$sqlCell = "SELECT * FROM data_pilah_cell WHERE kode_data_pilah = :kode AND tahun = :tahun";
$stmtC = $conn->prepare($sqlCell);
$stmtC->execute([':kode' => $kode, ':tahun' => $tahun]);
$cells = $stmtC->fetchAll(PDO::FETCH_ASSOC);

$cellMap = array();
foreach($cells as $c) {
    $cellMap[$c['kode_baris'] . '|' . $c['kode_kolom']] = $c['val'];
}

// =====================================================================
// BUILD HTML TABLE (digunakan oleh PDF & sebagai referensi Excel)
// =====================================================================
function buildHtmlTable($judul, $instansi, $tahun, $headerBaris, $koloms, $barisList, $cellMap) {
    $html  = '<h3 style="text-align:center; margin-bottom:2px;">' . htmlspecialchars($judul) . '</h3>';
    $html .= '<p style="text-align:center; font-size:12px; color:#666; margin-top:0;">Instansi: ' . htmlspecialchars($instansi) . ' — Tahun ' . htmlspecialchars($tahun) . '</p>';
    $html .= '<table border="1" cellpadding="4" cellspacing="0" style="width:100%; border-collapse:collapse; font-size:12px;">';
    
    // Header
    $html .= '<thead>';
    $html .= '<tr style="background-color:#3276b1; color:#fff;">';
    $html .= '<th style="width:40px; text-align:center;">No</th>';
    $html .= '<th style="min-width:120px;">' . htmlspecialchars($headerBaris) . '</th>';
    foreach ($koloms as $k) {
        $label = $k['header_kolom'] ? $k['header_kolom'] . ' ' . $k['nama_kolom'] : $k['nama_kolom'];
        $html .= '<th style="text-align:center;">' . htmlspecialchars($label) . '</th>';
    }
    $html .= '</tr>';
    $html .= '</thead>';
    
    // Body
    $html .= '<tbody>';
    $no = 1;
    foreach ($barisList as $b) {
        $html .= '<tr>';
        $html .= '<td style="text-align:center;">' . $no . '</td>';
        $html .= '<td style="font-weight:bold;">' . htmlspecialchars($b['nama_baris']) . '</td>';
        foreach ($koloms as $k) {
            $key = $b['kode_baris'] . '|' . $k['kode_kolom'];
            $val = isset($cellMap[$key]) ? $cellMap[$key] : 0;
            $html .= '<td style="text-align:right;">' . number_format((float)$val, 0, ',', '.') . '</td>';
        }
        $html .= '</tr>';
        $no++;
    }
    $html .= '</tbody>';
    $html .= '</table>';
    
    return $html;
}


// =====================================================================
// EXPORT PDF (menggunakan mPDF via vendor autoloader)
// =====================================================================
if ($type === 'pdf') {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
    
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4-L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10
    ]);
    
    $css = '
        body { font-family: Arial, sans-serif; }
        h3 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 6px; font-size: 11px; }
        th { background-color: #3276b1; color: #fff; text-align: center; }
        td { text-align: right; }
        td:first-child { text-align: center; }
        td:nth-child(2) { text-align: left; font-weight: bold; }
    ';
    
    $tableHtml = buildHtmlTable($judul, $instansi, $tahun, $headerBaris, $koloms, $barisList, $cellMap);
    
    $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($tableHtml, \Mpdf\HTMLParserMode::HTML_BODY);
    
    $filename = 'Laporan_' . preg_replace('/[^A-Za-z0-9_]/', '_', $judul) . '_' . $tahun . '.pdf';
    
    // Stream langsung ke browser
    $mpdf->Output($filename, 'I');
    exit;
}


// =====================================================================
// EXPORT EXCEL (menggunakan PhpSpreadsheet native — tanpa template)
// =====================================================================
if ($type === 'excel') {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
    
    $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Data Pilah');
    
    // ---- TITLE ROW ----
    $totalCols = count($koloms) + 2; // No + Nama Baris + Kolom data
    $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
    
    $sheet->mergeCells('A1:' . $lastColLetter . '1');
    $sheet->setCellValue('A1', $judul . ' — Tahun ' . $tahun);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    $sheet->mergeCells('A2:' . $lastColLetter . '2');
    $sheet->setCellValue('A2', 'Instansi: ' . $instansi);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A2')->getFont()->setSize(11)->setItalic(true);
    
    // ---- HEADER ROW ----
    $headerRow = 4;
    $sheet->setCellValue('A' . $headerRow, 'No');
    $sheet->setCellValue('B' . $headerRow, $headerBaris);
    
    $colIdx = 3; // Start from column C (index 3 in PhpSpreadsheet)
    foreach ($koloms as $k) {
        $label = $k['header_kolom'] ? $k['header_kolom'] . ' ' . $k['nama_kolom'] : $k['nama_kolom'];
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
        $sheet->setCellValue($colLetter . $headerRow, $label);
        $colIdx++;
    }
    
    // Style header
    $headerRange = 'A' . $headerRow . ':' . $lastColLetter . $headerRow;
    $headerStyle = array(
        'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF'), 'size' => 11),
        'fill' => array(
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => array('rgb' => '3276B1')
        ),
        'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER),
        'borders' => array(
            'allBorders' => array('borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
        )
    );
    $sheet->getStyle($headerRange)->applyFromArray($headerStyle);
    
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
    
    // Style data area
    $lastDataRow = $dataRow - 1;
    $dataRange = 'A' . ($headerRow + 1) . ':' . $lastColLetter . $lastDataRow;
    $dataStyle = array(
        'borders' => array(
            'allBorders' => array('borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
        )
    );
    $sheet->getStyle($dataRange)->applyFromArray($dataStyle);
    $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $lastDataRow)->getAlignment()
        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
    // Auto-size columns
    for ($i = 1; $i <= $totalCols; $i++) {
        $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
    }
    
    // ---- OUTPUT (stream ke browser) ----
    $filename = 'Laporan_' . preg_replace('/[^A-Za-z0-9_]/', '_', $judul) . '_' . $tahun . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xlsx');
    $objWriter->save('php://output');
    
    $objPHPExcel->disconnectWorksheets();
    unset($objPHPExcel);
    exit;
}
