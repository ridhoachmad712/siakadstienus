-- ==================================================================
-- Migrasi perbaikan import data master - SIAKAD STIE Nusantara
-- Tanggal: 2026-09-11
--
-- Tujuan:
--  1. Mengizinkan data opsional (jenis kelamin, agama, tanggal lahir)
--     kosong, supaya berkas mahasiswa baru yang belum lengkap tetap
--     bisa diimport dan dilengkapi kemudian.
--  2. Melebarkan kolom yang sering terpotong (nama, sekolah asal, telepon).
--  3. Mengganti FOREIGN KEY ... ON DELETE CASCADE pada tabel referensi
--     menjadi ON DELETE SET NULL. Dengan aturan lama, menghapus satu baris
--     di tbl_agama / tbl_jk / tbl_jenis_mk akan ikut MENGHAPUS seluruh
--     mahasiswa, dosen, atau mata kuliah yang memakainya.
--
-- CARA PAKAI (jalankan pada database produksi setelah BACKUP):
--   mysql -u USER -p NAMA_DATABASE < 2026-09-11_perbaikan_import.sql
--   atau import lewat phpMyAdmin -> tab SQL.
-- ==================================================================

-- ---------- 1. Tabel mahasiswa ----------
ALTER TABLE `mahasiswa`
  MODIFY `lulusan_jalur` varchar(50)  NOT NULL DEFAULT '',
  MODIFY `sekolah_asal`  varchar(150) NOT NULL DEFAULT '',
  MODIFY `nama_mhs`      varchar(150) NOT NULL,
  MODIFY `tempat_lhr`    varchar(50)  NOT NULL DEFAULT '',
  MODIFY `no_telp_mhs`   varchar(20)  NOT NULL DEFAULT '',
  MODIFY `id_jk`         int(11)      NULL DEFAULT NULL,
  MODIFY `id_agama`      int(11)      NULL DEFAULT NULL,
  MODIFY `tgl_lhr_mhs`   date         NULL DEFAULT NULL;

ALTER TABLE `mahasiswa` DROP FOREIGN KEY `mahasiswa_ibfk_1`;
ALTER TABLE `mahasiswa` DROP FOREIGN KEY `mahasiswa_ibfk_2`;
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`id_agama`) REFERENCES `tbl_agama` (`id_agama`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`id_jk`)    REFERENCES `tbl_jk`    (`id_jk`)    ON DELETE SET NULL ON UPDATE CASCADE;

-- ---------- 2. Tabel dosen ----------
ALTER TABLE `dosen`
  MODIFY `nama_dosen`    varchar(150) NOT NULL,
  MODIFY `tmp_lhr_dosen` varchar(50)  NOT NULL DEFAULT '',
  MODIFY `email`         varchar(100) NOT NULL DEFAULT '',
  MODIFY `no_telp`       varchar(20)  NOT NULL DEFAULT '',
  MODIFY `id_jk`         int(11)      NULL DEFAULT NULL,
  MODIFY `id_agama`      int(11)      NULL DEFAULT NULL,
  MODIFY `tgl_lhr_dosen` date         NULL DEFAULT NULL;

ALTER TABLE `dosen` DROP FOREIGN KEY `dosen_ibfk_1`;
ALTER TABLE `dosen` DROP FOREIGN KEY `dosen_ibfk_2`;
ALTER TABLE `dosen`
  ADD CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`id_jk`)    REFERENCES `tbl_jk`    (`id_jk`)    ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `dosen_ibfk_2` FOREIGN KEY (`id_agama`) REFERENCES `tbl_agama` (`id_agama`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ---------- 3. Tabel mata_kuliah ----------
ALTER TABLE `mata_kuliah`
  MODIFY `nama_matkul` varchar(150) NOT NULL,
  MODIFY `id_jenis_mk` int(11)      NULL DEFAULT NULL;

ALTER TABLE `mata_kuliah` DROP FOREIGN KEY `mata_kuliah_ibfk_1`;
ALTER TABLE `mata_kuliah`
  ADD CONSTRAINT `mata_kuliah_ibfk_1` FOREIGN KEY (`id_jenis_mk`) REFERENCES `tbl_jenis_mk` (`id_jenis_mk`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Catatan: bila salah satu nama FOREIGN KEY di atas berbeda pada database
-- Anda, cek dengan:  SHOW CREATE TABLE `mahasiswa`;  lalu sesuaikan namanya.
