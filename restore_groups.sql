-- Table structure for table `group_has_modules`
--

DROP TABLE IF EXISTS `group_has_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_has_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` varchar(100) DEFAULT NULL,
  `module_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`) USING BTREE,
  KEY `module_id` (`module_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14874 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_has_modules`
--

LOCK TABLES `group_has_modules` WRITE;
/*!40000 ALTER TABLE `group_has_modules` DISABLE KEYS */;
INSERT INTO `group_has_modules` VALUES
(14851,'superadmin','format-datagender'),
(14852,'superadmin','instansi-sumberdata'),
(14853,'superadmin','format-datagenderfix'),
(14854,'superadmin','rekap-matrik'),
(14855,'superadmin','userinfo'),
(14856,'superadmin','userinstansi'),
(14857,'superadmin','userotoritas'),
(14858,'superadmin','logeduserinfo'),
(14859,'superadmin','usermanagement'),
(14860,'superadmin','datapilah'),
(14861,'superadmin','entrydatapilah'),
(14862,'superadmin','data-pilah-cell'),
(14863,'superadmin','data-pilah-baris'),
(14864,'superadmin','data-pilah-kolom'),
(14870,'operator','dashboard'),
(14871,'operator','format-datagender'),
(14872,'operator','userinfo'),
(14873,'operator','logeduserinfo');
/*!40000 ALTER TABLE `group_has_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
