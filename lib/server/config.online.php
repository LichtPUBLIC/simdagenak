<?php
// Jika ada error PHP tampilkan di return value (browser) / console
ini_set('display_errors', '1');
// Title Aplikasi dan Index Framework (Tampil judul Window Web Browser)
// if (!defined('APP_TITLE')) define('APP_TITLE', "Sistem Informasi Manajemen Perencanaan Pembanguna Daerah (SIMPPD)");
// Jika aplikasi di pasang di folder dan diakses melalui localhost/windows
// maka lokasi gambar ketike export pdf (Html2Pdf) akan mengacu APP_ROOT_FOLDER ini
// Lihat lib/database/class.html2pdf.php ( method addHtmlFile)
if (!defined('APP_ROOT_FOLDER')) define('APP_ROOT_FOLDER', "eoffice");

// Setting Koneksi ke Database MySQL Framework
if (!defined('DB_FW_HOST'))     define('DB_FW_HOST',     'localhost');
if (!defined('DB_FW_DRIVER'))   define('DB_FW_DRIVER',   'mysql');
if (!defined('DB_FW_NAME'))     define('DB_FW_NAME',     'sigas');
if (!defined('DB_FW_USER'))     define('DB_FW_USER',     'zigas');
if (!defined('DB_FW_PASSWORD')) define('DB_FW_PASSWORD', 'archangantengyudhakalah');

// Database apa yg digunakan sebagai Data? set Config di bawahnya
if (!defined('DB_DATA_USING_MYSQL'))  define('DB_DATA_USING_MYSQL', true);
if (!defined('DB_DATA_USING_ORACLE')) define('DB_DATA_USING_ORACLE', false);

if(DB_DATA_USING_MYSQL) {
    if (!defined('DB_DATA_HOST'))       define('DB_DATA_HOST',     'localhost');
    if (!defined('DB_DATA_NAME'))       define('DB_DATA_NAME',     'sigas');
    if (!defined('DB_DATA_USER'))       define('DB_DATA_USER',     'zigas');
    if (!defined('DB_DATA_PASSWORD'))   define('DB_DATA_PASSWORD', 'archangantengyudhakalah');
}

if(DB_DATA_USING_ORACLE) {
	if (!defined('DB_ORA_USER'))     define('DB_ORA_USER', 'user');
	if (!defined('DB_ORA_PWD'))      define('DB_ORA_PWD',  'password');
	if (!defined('DB_ORA_PORT'))     define('DB_ORA_PORT', '1521');
	if (!defined('DB_ORA_CONN_STR')) define('DB_ORA_CONN_STR',
		'(DESCRIPTION =
			(ADDRESS =
				(PROTOCOL = TCP)
				(HOST = 127.0.0.1)
				(PORT=1521))
			(CONNECT_DATA =
				(SERVICE_NAME = XE)
		))');
}
