<?php
/**
 * Script untuk sinkronisasi data dari tabel backup ke tabel utama.
 * Sesuai instruksi teknis user.
 */

// Mock $_SERVER for CLI context
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require_once('lib/server/config.php');

$conn = new mysqli(DB_DATA_HOST, DB_DATA_USER, DB_DATA_PASSWORD, DB_DATA_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Memulai proses sinkronisasi...\n";

// 1. Matikan pengecekan Foreign Key sementara
echo "1. Mematikan Foreign Key Checks...\n";
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// 2. INSERT IGNORE dari data_pilah_backup ke data_pilah
echo "2. Melakukan INSERT IGNORE ke data_pilah...\n";
$sql1 = "INSERT IGNORE INTO data_pilah SELECT * FROM data_pilah_backup";
if ($conn->query($sql1)) {
    echo "   Berhasil: " . $conn->affected_rows . " baris baru ditambahkan.\n";
} else {
    echo "   Gagal: " . $conn->error . "\n";
}

// 3. Mengosongkan dan mengisi kembali data_pilah_baris
echo "3. Refresh data_pilah_baris dari backup...\n";
$conn->query("TRUNCATE TABLE data_pilah_baris");
$sql2 = "INSERT INTO data_pilah_baris SELECT * FROM data_pilah_baris_backup";
if ($conn->query($sql2)) {
    echo "   Berhasil: " . $conn->affected_rows . " baris dipulihkan.\n";
} else {
    echo "   Gagal: " . $conn->error . "\n";
}

// 4. Mengosongkan dan mengisi kembali data_pilah_kolom
echo "4. Refresh data_pilah_kolom dari backup...\n";
$conn->query("TRUNCATE TABLE data_pilah_kolom");
$sql3 = "INSERT INTO data_pilah_kolom SELECT * FROM data_pilah_kolom_backup";
if ($conn->query($sql3)) {
    echo "   Berhasil: " . $conn->affected_rows . " kolom dipulihkan.\n";
} else {
    echo "   Gagal: " . $conn->error . "\n";
}

// 5. Aktifkan kembali pengecekan Foreign Key
echo "5. Mengaktifkan kembali Foreign Key Checks...\n";
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "\nProses sinkronisasi selesai.\n";

$conn->close();
?>
