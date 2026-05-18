<?php
/**
 * Script untuk memulihkan Stored Functions dan Stored Procedures (MySQL Routines)
 * yang sangat penting bagi sistem SIMDAGENAK / SIGA.
 * 
 * Jalankan file ini melalui browser atau command line jika:
 * - Anda baru saja memigrasi database ke server baru.
 * - Halaman data matriks mengalami stuck loading atau error database.
 */

try {
    // Koneksi menggunakan kredensial database lokal (Default)
    $pdo = new PDO("mysql:host=localhost;dbname=sigas", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== MEMULAI PEMULIHAN MYSQL ROUTINES ===\n\n";

    echo "1. Mengaktifkan kepercayaan pembuat fungsi (log_bin_trust_function_creators)...\n";
    $pdo->exec("SET GLOBAL log_bin_trust_function_creators = 1");

    echo "2. Memulihkan Fungsi/Prosedur...\n\n";

    // --- 1. getCellValue ---
    $pdo->exec("DROP FUNCTION IF EXISTS `getCellValue`");
    $pdo->exec("
    CREATE FUNCTION `getCellValue`(ptahun integer, pkode_data_pilah varchar(10), pkode_kolom varchar(10), pkode_baris varchar(10)) RETURNS float
    begin
        declare hasil float;
        select tc.val into hasil from data_pilah_cell tc where tc.tahun=ptahun and tc.kode_baris=pkode_baris and tc.kode_kolom=pkode_kolom;
        return hasil;
    end
    ");
    echo "[SUKSES] Fungsi `getCellValue` berhasil dibuat.\n";

    // --- 2. getCellValue2 ---
    $pdo->exec("DROP FUNCTION IF EXISTS `getCellValue2`");
    $pdo->exec("
    CREATE FUNCTION `getCellValue2`(ptahun integer, pkode_data_pilah varchar(10), pkode_kolom varchar(10), pkode_baris varchar(10)) RETURNS float
    begin
        declare hasil float;
        select tc.val into hasil from data_pilah_cell tc where tc.tahun=ptahun and tc.kode_baris=pkode_baris and tc.kode_kolom=pkode_kolom;
        return hasil;
    end
    ");
    echo "[SUKSES] Fungsi `getCellValue2` berhasil dibuat.\n";

    // --- 3. removeSpacialChar ---
    $pdo->exec("DROP FUNCTION IF EXISTS `removeSpacialChar`");
    $pdo->exec("
    CREATE FUNCTION `removeSpacialChar`(`in_str` varchar(4096)) RETURNS varchar(4096) CHARSET utf8
    BEGIN
        DECLARE out_str VARCHAR(4096) DEFAULT '';
        DECLARE c VARCHAR(4096) DEFAULT '';
        DECLARE pointer INT DEFAULT 1;

        IF ISNULL(in_str) THEN
          RETURN NULL;
        ELSE
          WHILE pointer <= LENGTH(in_str) DO

            SET c = MID(in_str, pointer, 1);

            IF (ASCII(c) >= 48 AND ASCII(c) <= 57) OR (ASCII(c) >= 65 AND ASCII(c) <= 90) OR (ASCII(c) >= 97 AND ASCII(c) <= 122) THEN
              SET out_str = CONCAT(out_str, c);
            ELSE
              SET out_str = CONCAT(out_str, ' ');
            END IF;

            SET pointer = pointer + 1;
          END WHILE;
        END IF;

        RETURN out_str;
      END
    ");
    echo "[SUKSES] Fungsi `removeSpacialChar` berhasil dibuat.\n";

    // --- 4. removeSpacialChar2 ---
    $pdo->exec("DROP FUNCTION IF EXISTS `removeSpacialChar2`");
    $pdo->exec("
    CREATE FUNCTION `removeSpacialChar2`(`in_str` varchar(4096)) RETURNS varchar(4096) CHARSET utf8
    BEGIN
        DECLARE out_str VARCHAR(4096) DEFAULT '';
        DECLARE c VARCHAR(4096) DEFAULT '';
        DECLARE pointer INT DEFAULT 1;

        IF ISNULL(in_str) THEN
          RETURN NULL;
        ELSE
          WHILE pointer <= LENGTH(in_str) DO

            SET c = MID(in_str, pointer, 1);

            IF (ASCII(c) >= 48 AND ASCII(c) <= 57) OR (ASCII(c) >= 65 AND ASCII(c) <= 90) OR (ASCII(c) >= 97 AND ASCII(c) <= 122) THEN
              SET out_str = CONCAT(out_str, c);
            ELSE
              SET out_str = CONCAT(out_str, ' ');
            END IF;

            SET pointer = pointer + 1;
          END WHILE;
        END IF;

        RETURN out_str;
      END
    ");
    echo "[SUKSES] Fungsi `removeSpacialChar2` berhasil dibuat.\n";

    // --- 5. genQueryDataPilah ---
    $pdo->exec("DROP FUNCTION IF EXISTS `genQueryDataPilah`");
    $pdo->exec("
    CREATE FUNCTION `genQueryDataPilah`(pkode_data_pilah varchar(10), ptahun_awal integer, ptahun_akhir integer) RETURNS text CHARSET latin1
    BEGIN
    declare vkode_kolom varchar(255);
    declare vkolom_alias varchar(255);
    declare vheader_kolom varchar(255);
    declare vnama_kolom varchar(255);
    declare vtahun integer;
    DECLARE finished INTEGER DEFAULT 0;
    declare vsqlKolom text;

    declare cur cursor for select kode_kolom, header_kolom, nama_kolom  from data_pilah_kolom where aktif=1 and kode_data_pilah=pkode_data_pilah order by kode_kolom;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

    set vsqlKolom = '';

    set vtahun = ptahun_awal; 

    while vtahun <= ptahun_akhir do
        set finished = 0;
        open cur;
        get_kolom: LOOP
            FETCH cur INTO vkode_kolom, vheader_kolom, vnama_kolom;
            IF finished = 1 THEN 
            LEAVE get_kolom;
            END IF;
            set vkolom_alias = lower(concat(REPLACE(vkode_kolom, '.', '_'), '_', vtahun));
            set vsqlKolom = concat(vsqlKolom, ', getCellValue(', vtahun, ', \'', pkode_data_pilah, '\', \'', vkode_kolom, '\', tb.kode_baris) as ', replace(vkolom_alias,' ',''));
        END LOOP get_kolom;
        close cur;
        set vtahun = vtahun + 1;
    end WHILE;

    set vsqlKolom = IFNULL(vsqlKolom,'-');
    return concat('select tb.kode_data_pilah, tb.kode_baris, tb.nama_baris ', vsqlKolom, ' from data_pilah_baris tb where tb.kode_data_pilah=\'',pkode_data_pilah,'\'');
    end
    ");
    echo "[SUKSES] Fungsi `genQueryDataPilah` berhasil dibuat.\n";

    // --- 6. genQueryDataPilah2 ---
    $pdo->exec("DROP FUNCTION IF EXISTS `genQueryDataPilah2`");
    $pdo->exec("
    CREATE FUNCTION `genQueryDataPilah2`(pkode_data_pilah varchar(10), ptahun_awal integer, ptahun_akhir integer) RETURNS text CHARSET latin1
    BEGIN
    declare vkode_kolom varchar(255);
    declare vkolom_alias varchar(255);
    declare vheader_kolom varchar(255);
    declare vnama_kolom varchar(255);
    declare vtahun integer;
    DECLARE finished INTEGER DEFAULT 0;
    declare vsqlKolom text;

    declare cur cursor for select kode_kolom, header_kolom, nama_kolom  from data_pilah_kolom where aktif=1 and kode_data_pilah=pkode_data_pilah order by kode_kolom;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished = 1;

    set vsqlKolom = '';

    set vtahun = ptahun_awal; 

    while vtahun <= ptahun_akhir do
        set finished = 0;
        open cur;
        get_kolom: LOOP
            FETCH cur INTO vkode_kolom, vheader_kolom, vnama_kolom;
            IF finished = 1 THEN 
            LEAVE get_kolom;
            END IF;
            set vkolom_alias = lower(concat(REPLACE(vkode_kolom, '.', '_'), '_', vtahun));
            set vsqlKolom = concat(vsqlKolom, ', getCellValue(', vtahun, ', \'', pkode_data_pilah, '\', \'', vkode_kolom, '\', tb.kode_baris) as ', replace(vkolom_alias,' ',''));
        END LOOP get_kolom;
        close cur;
        set vtahun = vtahun + 1;
    end WHILE;

    set vsqlKolom = IFNULL(vsqlKolom,'-');
    return concat('select tb.kode_data_pilah, tb.kode_baris, tb.nama_baris ', vsqlKolom, ' from data_pilah_baris tb where tb.kode_data_pilah=\'',pkode_data_pilah,'\'');
    end
    ");
    echo "[SUKSES] Fungsi `genQueryDataPilah2` berhasil dibuat.\n";

    // --- 7. genDataPilahBarisKec ---
    $pdo->exec("DROP PROCEDURE IF EXISTS `genDataPilahBarisKec`");
    $pdo->exec("
    CREATE PROCEDURE `genDataPilahBarisKec`(pkode_data_pilah varchar(10))
    begin
    declare done integer;
    declare vkode_data_pilah varchar(10);
    declare cur1 cursor for select kode_data_pilah from data_pilah where id_data_pilah=pkode_data_pilah;
    declare continue handler for not found set done=1;

    set done = 0;
    open cur1;
        igmLoop: loop
            fetch cur1 into vkode_data_pilah;
        if done = 1 then leave igmLoop; 
        end if;
        insert into data_pilah_baris (kode_data_pilah, no_urut, kode_baris, nama_baris, aktif)
        values(vkode_data_pilah,01, concat(vkode_data_pilah, '.01'), 'Gamping',1),
        (vkode_data_pilah,02, concat(vkode_data_pilah, '.02'), 'Godean',1),
        (vkode_data_pilah,03, concat(vkode_data_pilah, '.03'), 'Moyudan',1),
        (vkode_data_pilah,04, concat(vkode_data_pilah, '.04'), 'Minggir',1),
        (vkode_data_pilah,05, concat(vkode_data_pilah, '.05'), 'Seyegan',1),
        (vkode_data_pilah,06, concat(vkode_data_pilah, '.06'), 'Mlati',1),
        (vkode_data_pilah,07, concat(vkode_data_pilah, '.07'), 'Depok',1),
        (vkode_data_pilah,08, concat(vkode_data_pilah, '.08'), 'Berbah',1),
        (vkode_data_pilah,09, concat(vkode_data_pilah, '.09'), 'Prambanan',1),
        (vkode_data_pilah,10, concat(vkode_data_pilah, '.10'), 'Kalasan',1),
        (vkode_data_pilah,11, concat(vkode_data_pilah, '.11'), 'Ngemplak',1),
        (vkode_data_pilah,12, concat(vkode_data_pilah, '.12'), 'Ngaglik',1),
        (vkode_data_pilah,13, concat(vkode_data_pilah, '.13'), 'Sleman',1),
        (vkode_data_pilah,14, concat(vkode_data_pilah, '.14'), 'Tempel',1),
        (vkode_data_pilah,15, concat(vkode_data_pilah, '.15'), 'Turi',1),
        (vkode_data_pilah,16, concat(vkode_data_pilah, '.16'), 'Pakem',1),
        (vkode_data_pilah,17, concat(vkode_data_pilah, '.17'), 'Cangkringan',1);

        end loop igmLoop;
        close cur1;
    end
    ");
    echo "[SUKSES] Prosedur `genDataPilahBarisKec` berhasil dibuat.\n";

    // --- 8. genDataPilahBarisKec2 ---
    $pdo->exec("DROP PROCEDURE IF EXISTS `genDataPilahBarisKec2`");
    $pdo->exec("
    CREATE PROCEDURE `genDataPilahBarisKec2`(pkode_data_pilah varchar(10))
    begin
    declare done integer;
    declare vkode_data_pilah varchar(10);
    declare cur1 cursor for select kode_data_pilah from data_pilah where id_data_pilah=pkode_data_pilah;
    declare continue handler for not found set done=1;

    set done = 0;
    open cur1;
        igmLoop: loop
            fetch cur1 into vkode_data_pilah;
        if done = 1 then leave igmLoop; 
        end if;
        insert into data_pilah_baris (kode_data_pilah, no_urut, kode_baris, nama_baris, aktif)
        values(vkode_data_pilah,01, concat(vkode_data_pilah, '.01'), 'Gamping',1),
        (vkode_data_pilah,02, concat(vkode_data_pilah, '.02'), 'Godean',1),
        (vkode_data_pilah,03, concat(vkode_data_pilah, '.03'), 'Moyudan',1),
        (vkode_data_pilah,04, concat(vkode_data_pilah, '.04'), 'Minggir',1),
        (vkode_data_pilah,05, concat(vkode_data_pilah, '.05'), 'Seyegan',1),
        (vkode_data_pilah,06, concat(vkode_data_pilah, '.06'), 'Mlati',1),
        (vkode_data_pilah,07, concat(vkode_data_pilah, '.07'), 'Depok',1),
        (vkode_data_pilah,08, concat(vkode_data_pilah, '.08'), 'Berbah',1),
        (vkode_data_pilah,09, concat(vkode_data_pilah, '.09'), 'Prambanan',1),
        (vkode_data_pilah,10, concat(vkode_data_pilah, '.10'), 'Kalasan',1),
        (vkode_data_pilah,11, concat(vkode_data_pilah, '.11'), 'Ngemplak',1),
        (vkode_data_pilah,12, concat(vkode_data_pilah, '.12'), 'Ngaglik',1),
        (vkode_data_pilah,13, concat(vkode_data_pilah, '.13'), 'Sleman',1),
        (vkode_data_pilah,14, concat(vkode_data_pilah, '.14'), 'Tempel',1),
        (vkode_data_pilah,15, concat(vkode_data_pilah, '.15'), 'Turi',1),
        (vkode_data_pilah,16, concat(vkode_data_pilah, '.16'), 'Pakem',1),
        (vkode_data_pilah,17, concat(vkode_data_pilah, '.17'), 'Cangkringan',1);

        end loop igmLoop;
        close cur1;
    end
    ");
    echo "[SUKSES] Prosedur `genDataPilahBarisKec2` berhasil dibuat.\n";

    // --- 9. getDataPilah ---
    $pdo->exec("DROP PROCEDURE IF EXISTS `getDataPilah`");
    $pdo->exec("
    CREATE PROCEDURE `getDataPilah`(pkode_data_pilah varchar(10), ptahun_awal integer, ptahun_akhir integer)
    begin
        set @s = genQueryDataPilah(pkode_data_pilah, ptahun_awal, ptahun_akhir);
        PREPARE stmt1 FROM @s; 
        EXECUTE stmt1; 
        DEALLOCATE PREPARE stmt1; 
    end
    ");
    echo "[SUKSES] Prosedur `getDataPilah` berhasil dibuat.\n";

    // --- 10. getDataPilah2 ---
    $pdo->exec("DROP PROCEDURE IF EXISTS `getDataPilah2`");
    $pdo->exec("
    CREATE PROCEDURE `getDataPilah2`(pkode_data_pilah varchar(10), ptahun_awal integer, ptahun_akhir integer)
    begin
        set @s = genQueryDataPilah(pkode_data_pilah, ptahun_awal, ptahun_akhir);
        PREPARE stmt1 FROM @s; 
        EXECUTE stmt1; 
        DEALLOCATE PREPARE stmt1; 
    end
    ");
    echo "[SUKSES] Prosedur `getDataPilah2` berhasil dibuat.\n\n";

    echo "=== SEMUA MYSQL ROUTINES BERHASIL DIPULIHKAN! ===\n";

} catch (Exception $e) {
    echo "ERROR: Gagal memulihkan routines. Pesan: " . $e->getMessage() . "\n";
}
