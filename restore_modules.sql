-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `module_id` varchar(80) NOT NULL,
  `module` varchar(45) DEFAULT NULL COMMENT 'Mjd nama folder, file php&js, class php&js',
  `name` varchar(45) DEFAULT NULL,
  `description` mediumtext NOT NULL,
  `menu` varchar(255) NOT NULL,
  `iconcls` varchar(45) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  `onmenu` int(10) DEFAULT '1',
  `onview` varchar(50) NOT NULL DEFAULT 'tabpanel',
  PRIMARY KEY (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES
('aplikasi-terintegrasi','AplikasiTerintegrasi','Aplikasi Terintegrasi','Dashboard berisi Aplikasi yang terintegrasi','021;Sistem Terintegrasi/',NULL,NULL,1,0,'tabpanel'),
('aspek','Aspek','Aspek','101;aspek','026;Master Data/','angle-double-right','database',1,0,'tabpanel'),
('capaian-indikator','CapaianIndikator','Capaian Indikator','Capain Indikator per tahun','027;Capaian Indikator/','angle-double-right','angle-double-right',1,1,'tabpanel'),
('code-generator','CodeGenerator','Code Generator','Generate otomatis code dalam module','025;Developer/','angle-double-right','code',0,1,'tabpanel'),
('code-module','CodeModule','Code Module','Mengelola Modules dan Actions','025;Developer/','angle-double-right','code',1,1,'tabpanel'),
('crud-generator','CrudGenerator','Crud Generator','crud generator','025;Developer/','angle-double-right','code',1,1,'tabpanel'),
('daftar-pengguna','DaftarPengguna','Daftar Pengguna','Daftar Pengguna','029;Admin/','circle-o','user',1,1,'tabpanel'),
('dashboard','Dashboard','Dashboard','Dashboard Aplikasi','011;Dashboard/','circle-o','dashboard',1,0,'tabpanel'),
('data-per-instansi','DataPerInstansi','Data Gender Per Instansi','Data gender per instansi','026;Master Data/','angle-double-right','database',1,1,'tabpanel'),
('data-pilah','DataPilah','data pilah','data pilah','030;manajemen tabel/','angle-double-right','angle-double-right',1,1,'tabpanel'),
('data-pilah-baris','DataPilahBaris','Data Pilah Baris','data pilah baris','032;Manajemen Tabel/','angle-double-right','angle-double-right',1,1,'tabpanel'),
('data-pilah-cell','DataPilahCell','Data Pilah Cell','data pilah cell','032;Manajemen Tabel/','angle-double-right','angle-double-right',1,1,'tabpanel'),
('data-pilah-kolom','DataPilahKolom','Data Pilah Kolom','data pilah kolom','032;Manajemen Tabel/','angle-double-right','angle-double-right',1,1,'tabpanel'),
('data-umum-pertahun','DataUmumPertahun','Data Gender Per Tahun','Data Gender Per Tahun','026;Master Data/','angle-double-right','database',1,0,'tabpanel'),
('data-urusan','DataUrusan','Data Urusan','Data Urusan','027;Capaian Indikator/','angle-double-right','angle-double-right',1,0,'tabpanel'),
('datapilah','DataPilah','Data Pilah','mengelola data pilah','032;Manajemen Tabel/','angle-double-right','angle-double-right',1,1,'tabpanel'),
('drawer-app','DrawerModule','Drawer Apps','Drawer Aplikasi pada header','021;Sistem Terintegrasi/','','',1,0,'tabpanel'),
('drawer-module','DrawerModule','Drawer','Drawer','','','',1,0,'tabpanel'),
('entry-pengguna','EntryPengguna','Entry Pengguna','Menambahkan user','029;Admin/','circle-o','user',1,1,'tabpanel'),
('entrydatapilah','EntryDataPilah','Entry Data Pilah','Entry Data Pilah','032;Manajemen Tabel/','angle-double-right','angle-double-right',1,1,'tabpanel'),
('format-datagender','FormatDatagender','Format Data Gender','Format Data Gender','026;Master Data/','angle-double-right','database',1,1,'tabpanel'),
('format-datagenderfix','FormatDatagenderFix','Format Data Gender Lihat','Format Data Gender','026;Master Data/','angle-double-right','database',1,1,'tabpanel'),
('hitung-rumus','HitungRumus','Hitung Rumus','Menghitung dengan rumus','027;Hitung/','angle-double-right','calc',1,0,'window'),
('indikator','Indikator','Data Indikator','103;Indikator','026;Master Data/','angle-double-right','database',0,1,'tabpanel'),
('instansi-sumberdata','InstansiSumberdata','101;Mapping Sumber Data Instansi','Mapping Sumber Data Instansi','026;Master Data/','angle-double-right','database',1,1,'tabpanel'),
('kategori','Kategori','Kategori','100;kategori','026;Master Data/','angle-double-right','database',1,0,'tabpanel'),
('log-activity','LogActivity','Log Aktifitas User Akses','Log/history akses oleh user','030;Administrator/','angle-double-right','user',1,1,'tabpanel'),
('logeduserinfo','LogedUserInfo','Informasi User Yang Login','pengolahan data user','030;Administrator/','angle-double-right','user',1,0,'window'),
('message-private','MessagePrivate','Kirim Pesan (Chat)','Kirim pesan dari user ke user (Chating)','100;Utility/','angle-double-right','cogs',0,1,'window'),
('message-public','MessagePublic','Kirim Pesan ke User','Kirim pesan ke user','100;Utility/','angle-double-right','cogs',0,1,'window'),
('rekap-matrik','RekapMatrik','Rekap Data Matrik','Rekap Data Matrik per Dinas','029;Rekap/','angle-double-right','bars',1,1,'tabpanel'),
('sample-module','SampleModule','Sample Module','Contoh','025;Developer/','angle-double-right','user-secret',1,1,'tabpanel'),
('sample-window','SampleWindow','Sample Window','Contoh','025;Developer/','angle-double-right','user-secret',1,0,'tabpanel'),
('setting-aplikasi','SettingAplikasi','Setting Aplikasi','Setting Daftar Aplikasi yang terintegrasi','030;Administrator/','angle-double-right','user',1,1,'tabpanel'),
('skpd-has-indikator','SkpeHasIndikator','SKPD Has Indikator','skpd has indikator','026;Master Data/','angle-double-right','database',1,0,'tabpanel'),
('sub-aspek','SubAspek','Sub Aspek','102;Sub Aspek','026;Master Data/','angle-double-right','database',1,0,'tabpanel'),
('sumber-data','SumberData','100;Sumber Data','Sumber data','026;Master Data/','angle-double-right','database',1,1,'tabpanel'),
('tambah-kolom','TambahKolom','Tambah Kolom','tambah kolom','030;manajemen tabel/','angle-double-right','angle-double-right',1,1,'tabpanel'),
('user-info','User-Info','030;User Info','Pengaturan user login','031;User/','user','user',1,0,'tabpanel'),
('user-instansi','UserInstansi','User Instansi','Pengolahan data user per instansi','030;Administrator/','angle-double-right','user',1,1,'tabpanel'),
('userinfo','UserInfo','Informasi User','Pengolahan data user','030;Administrator/','angle-double-right','user',1,0,'window'),
('userinstansi','UserInstansi','User Instansi','Pengolahan data user per instansi','030;Administrator/','angle-double-right','user',1,1,'tabpanel'),
('usermanagement','UserManagement','User Management','Pengolahan data  user','030;Administrator/','angle-double-right','user',1,1,'tabpanel'),
('userotoritas','UserOtoritas','User Otoritas','Pengolahan data otoritas user','030;Administrator/','angle-double-right','user',1,1,'tabpanel'),
('userunit','UserUnit','User Unit','Pengelolaan kepemilikan Unit','030;Administrator/','angle-double-right','user',1,1,'tabpanel');
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;
