<?php
$conn = new mysqli('127.0.0.1', 'root', '', 'sigas');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Create table `data_pilah_verifikasi`
$sql = "CREATE TABLE IF NOT EXISTS `data_pilah_verifikasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_data_pilah` varchar(50) NOT NULL,
  `id_instansi` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `verified_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_verif_matriks` (`kode_data_pilah`, `id_instansi`, `tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($sql);

// 2. Register module `VerifikasiDataMatrix`
$sql = "INSERT IGNORE INTO `modules` (`module_id`, `module`, `name`, `description`, `menu`, `iconcls`, `icon`, `active`, `onmenu`, `onview`) VALUES 
('verifikasi-data-matrix', 'VerifikasiDataMatrix', 'Verifikasi Data Matriks', 'Verifikasi Data Matriks', '032;Manajemen Tabel/', 'angle-double-right', 'check-square-o', 1, 1, 'tabpanel');";
$conn->query($sql);

$conn->close();
echo "Done.";
?>
