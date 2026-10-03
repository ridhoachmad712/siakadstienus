-- Jalankan setelah backup pada database tujuan, setelah pengujian staging.
-- Tidak menghapus/merapikan data otomatis. Duplikasi harus ditinjau manual.
-- DDL MySQL/MariaDB melakukan implicit commit; ulangi file untuk melanjutkan
-- bila penerapan terhenti. Constraint/index diperiksa sebelum ditambahkan.
DELIMITER $$
DROP PROCEDURE IF EXISTS siakad_integritas_20261003$$
CREATE PROCEDURE siakad_integritas_20261003()
BEGIN
    DECLARE selesai INT DEFAULT 0;
    DECLARE nama_tabel VARCHAR(64);
    DECLARE nama_fk VARCHAR(64);
    DECLARE kolom VARCHAR(64);
    DECLARE tabel_induk VARCHAR(64);
    DECLARE kolom_induk VARCHAR(64);
    DECLARE aturan_update VARCHAR(20);
    DECLARE fk_cursor CURSOR FOR
        SELECT k.TABLE_NAME,k.CONSTRAINT_NAME,k.COLUMN_NAME,k.REFERENCED_TABLE_NAME,k.REFERENCED_COLUMN_NAME,r.UPDATE_RULE
        FROM information_schema.KEY_COLUMN_USAGE k
        JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON r.CONSTRAINT_SCHEMA=k.CONSTRAINT_SCHEMA AND r.TABLE_NAME=k.TABLE_NAME AND r.CONSTRAINT_NAME=k.CONSTRAINT_NAME
        WHERE k.CONSTRAINT_SCHEMA=DATABASE() AND r.DELETE_RULE='CASCADE';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET selesai=1;

    IF EXISTS (SELECT 1 FROM krs_mhs GROUP BY nim_npm,id_jadwal,id_thn_akademik HAVING COUNT(*)>1)
       OR EXISTS (SELECT 1 FROM khs_mhs GROUP BY nim_npm,id_jadwal,id_thn_akademik HAVING COUNT(*)>1)
       OR EXISTS (SELECT 1 FROM user GROUP BY username,level HAVING COUNT(*)>1)
       OR EXISTS (SELECT 1 FROM pengaturan_sks_mhs GROUP BY nim_npm,id_thn_akademik HAVING COUNT(*)>1)
       OR EXISTS (SELECT 1 FROM jadwal_penawaran GROUP BY id_thn_akademik HAVING COUNT(*)>1)
       OR EXISTS (SELECT 1 FROM jadwal_input_nilai GROUP BY id_thn_akademik HAVING COUNT(*)>1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Data ganda ditemukan. Tinjau dan perbaiki manual sebelum migrasi; tidak ada data dihapus.';
    END IF;
    IF EXISTS (SELECT 1 FROM krs_mhs k LEFT JOIN jadwal_mengajar j ON j.id_jadwal=k.id_jadwal AND j.kode_prodi=k.kode_prodi AND j.id_thn_akademik=k.id_thn_akademik WHERE j.id_jadwal IS NULL)
       OR EXISTS (SELECT 1 FROM khs_mhs h LEFT JOIN krs_mhs k ON k.nim_npm=h.nim_npm AND k.id_jadwal=h.id_jadwal AND k.id_thn_akademik=h.id_thn_akademik AND k.kode_prodi=h.kode_prodi WHERE k.id_krs IS NULL) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Relasi KRS/KHS lama tidak konsisten. Perbaiki berdasarkan arsip sebelum migrasi.';
    END IF;
    -- Migrasi ini hanya menangani FK satu kolom seperti schema SIAKAD asal.
    IF EXISTS (SELECT 1 FROM information_schema.KEY_COLUMN_USAGE k JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON r.CONSTRAINT_SCHEMA=k.CONSTRAINT_SCHEMA AND r.TABLE_NAME=k.TABLE_NAME AND r.CONSTRAINT_NAME=k.CONSTRAINT_NAME WHERE k.CONSTRAINT_SCHEMA=DATABASE() AND r.DELETE_RULE='CASCADE' GROUP BY k.TABLE_NAME,k.CONSTRAINT_NAME HAVING COUNT(*)>1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Foreign key CASCADE majemuk ditemukan; tinjau schema khusus terlebih dahulu.';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='krs_mhs' AND INDEX_NAME='uq_krs_peserta_jadwal') THEN
        ALTER TABLE krs_mhs ADD UNIQUE KEY uq_krs_peserta_jadwal (nim_npm,id_jadwal,id_thn_akademik);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='khs_mhs' AND INDEX_NAME='uq_khs_peserta_jadwal') THEN
        ALTER TABLE khs_mhs ADD UNIQUE KEY uq_khs_peserta_jadwal (nim_npm,id_jadwal,id_thn_akademik);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='user' AND INDEX_NAME='uq_user_login') THEN
        ALTER TABLE user ADD UNIQUE KEY uq_user_login (username,level);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pengaturan_sks_mhs' AND INDEX_NAME='uq_sks_mahasiswa_periode') THEN
        ALTER TABLE pengaturan_sks_mhs ADD UNIQUE KEY uq_sks_mahasiswa_periode (nim_npm,id_thn_akademik);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jadwal_penawaran' AND INDEX_NAME='uq_penawaran_periode') THEN
        ALTER TABLE jadwal_penawaran ADD UNIQUE KEY uq_penawaran_periode (id_thn_akademik);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jadwal_input_nilai' AND INDEX_NAME='uq_nilai_periode') THEN
        ALTER TABLE jadwal_input_nilai ADD UNIQUE KEY uq_nilai_periode (id_thn_akademik);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='jadwal_mengajar' AND INDEX_NAME='uq_jadwal_lingkup') THEN
        ALTER TABLE jadwal_mengajar ADD UNIQUE KEY uq_jadwal_lingkup (id_jadwal,kode_prodi,id_thn_akademik);
    END IF;
    -- Hindari penghapusan berantai meskipun SQL dijalankan di luar aplikasi.
    OPEN fk_cursor;
    ubah_fk: LOOP
        FETCH fk_cursor INTO nama_tabel,nama_fk,kolom,tabel_induk,kolom_induk,aturan_update;
        IF selesai=1 THEN LEAVE ubah_fk; END IF;
        SET @siakad_ddl=CONCAT('ALTER TABLE `',REPLACE(nama_tabel,'`','``'),'` DROP FOREIGN KEY `',REPLACE(nama_fk,'`','``'),'`, ADD CONSTRAINT `',CONCAT('r_',LEFT(REPLACE(nama_fk,'`','``'),40),'_',LEFT(MD5(CONCAT(nama_tabel,nama_fk)),8)),'` FOREIGN KEY (`',REPLACE(kolom,'`','``'),'`) REFERENCES `',REPLACE(tabel_induk,'`','``'),'` (`',REPLACE(kolom_induk,'`','``'),'`) ON DELETE RESTRICT ON UPDATE ',aturan_update);
        PREPARE siakad_stmt_ddl FROM @siakad_ddl;
        EXECUTE siakad_stmt_ddl;
        DEALLOCATE PREPARE siakad_stmt_ddl;
    END LOOP;
    CLOSE fk_cursor;
    IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='krs_mhs' AND CONSTRAINT_NAME='fk_krs_jadwal_lingkup') THEN
        ALTER TABLE krs_mhs ADD CONSTRAINT fk_krs_jadwal_lingkup FOREIGN KEY (id_jadwal,kode_prodi,id_thn_akademik) REFERENCES jadwal_mengajar (id_jadwal,kode_prodi,id_thn_akademik) ON DELETE RESTRICT ON UPDATE RESTRICT;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='khs_mhs' AND CONSTRAINT_NAME='fk_khs_krs') THEN
        ALTER TABLE khs_mhs ADD CONSTRAINT fk_khs_krs FOREIGN KEY (nim_npm,id_jadwal,id_thn_akademik) REFERENCES krs_mhs (nim_npm,id_jadwal,id_thn_akademik) ON DELETE RESTRICT ON UPDATE RESTRICT;
    END IF;
END$$
CALL siakad_integritas_20261003()$$
DROP PROCEDURE siakad_integritas_20261003$$
DELIMITER ;
