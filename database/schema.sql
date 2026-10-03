-- Schema tanpa data pribadi atau akun.
SET NAMES utf8mb4;
CREATE TABLE `dosen` (
  `nip` varchar(20) NOT NULL,
  `nama_dosen` varchar(80) NOT NULL,
  `id_jk` int(11) NOT NULL,
  `id_agama` int(11) NOT NULL,
  `alamat` text NOT NULL,
  `foto_dosen` text NOT NULL,
  `tmp_lhr_dosen` varchar(30) NOT NULL,
  `tgl_lhr_dosen` date NOT NULL,
  `email` varchar(50) NOT NULL,
  `no_telp` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `dosen_has_matkul` (
  `id` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `fakultas_has_jurusan` (
  `id` int(11) NOT NULL,
  `kode_fakultas` varchar(10) NOT NULL,
  `kode_prodi` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `jadwal_input_nilai` (
  `id` int(11) NOT NULL,
  `id_thn_akademik` int(11) NOT NULL,
  `dari_tgl` date NOT NULL,
  `sampai_tgl` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `jadwal_mengajar` (
  `id_jadwal` int(11) NOT NULL,
  `kode_prodi` varchar(20) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `id_thn_akademik` int(11) NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `kode_ruangan` int(11) NOT NULL,
  `id_hari` int(11) NOT NULL,
  `mulai_jam` time NOT NULL,
  `sampai_jam` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `jadwal_penawaran` (
  `id` int(11) NOT NULL,
  `id_thn_akademik` int(11) NOT NULL,
  `dari_tgl` date NOT NULL,
  `sampai_tgl` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `khs_mhs` (
  `kode_prodi` varchar(20) NOT NULL,
  `nim_npm` varchar(20) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `id_thn_akademik` int(11) NOT NULL,
  `nilai_tgs` varchar(10) NOT NULL,
  `nilai_uts` varchar(10) NOT NULL,
  `nilai_uas` varchar(10) NOT NULL,
  `nilai_akhir` varchar(10) NOT NULL,
  `bobot` varchar(10) NOT NULL,
  `grade` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `krs_mhs` (
  `id_krs` int(11) NOT NULL,
  `kode_prodi` varchar(20) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `nim_npm` varchar(20) NOT NULL,
  `id_thn_akademik` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mahasiswa` (
  `nim_npm` varchar(20) NOT NULL,
  `thn_masuk` int(11) NOT NULL,
  `lulusan_jalur` varchar(20) NOT NULL,
  `sekolah_asal` varchar(50) NOT NULL,
  `nama_mhs` varchar(80) NOT NULL,
  `id_jk` int(11) NOT NULL,
  `tempat_lhr` varchar(50) NOT NULL,
  `tgl_lhr_mhs` date NOT NULL,
  `id_agama` int(11) NOT NULL,
  `email` text NOT NULL,
  `alamat_mhs` text NOT NULL,
  `no_telp_mhs` varchar(12) NOT NULL,
  `foto_mhs` text NOT NULL,
  `status_mhs` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mata_kuliah` (
  `kode_matkul` varchar(20) NOT NULL,
  `nama_matkul` varchar(50) NOT NULL,
  `sks` int(11) NOT NULL,
  `semester` varchar(11) NOT NULL,
  `id_jenis_mk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mhs_has_orgtua` (
  `id` int(11) NOT NULL,
  `nim_npm` varchar(20) NOT NULL,
  `no_kk` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mhs_has_pa` (
  `nip` varchar(20) NOT NULL,
  `nim_npm` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mhs_has_pembayaran_ukt` (
  `nim_npm` varchar(20) NOT NULL,
  `id_thn_akademik` int(11) NOT NULL,
  `bukti_pembayaran` text NOT NULL,
  `tgl_upload` date NOT NULL,
  `waktu` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pengaturan` (
  `id_pengaturan` int(11) NOT NULL,
  `nama_aplikasi` text NOT NULL,
  `nama_kampus` text NOT NULL,
  `logo_aplikasi` text NOT NULL,
  `copyright` text NOT NULL,
  `alamat` text NOT NULL,
  `email` varchar(80) NOT NULL,
  `no_telp` varchar(50) NOT NULL,
  `kota` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pengaturan_sks_mhs` (
  `id` int(11) NOT NULL,
  `id_thn_akademik` int(11) NOT NULL,
  `nim_npm` varchar(20) NOT NULL,
  `sks` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `prodi` (
  `kode_prodi` varchar(20) NOT NULL,
  `nama_prodi` varchar(100) NOT NULL,
  `ketua_prodi` varchar(20) NOT NULL,
  `jenis` varchar(20) NOT NULL,
  `jenjang` varchar(10) NOT NULL,
  `akreditasi` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `prodi_has_dosen` (
  `id` int(11) NOT NULL,
  `kode_prodi` varchar(20) NOT NULL,
  `nip` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `prodi_has_matkul` (
  `id` int(11) NOT NULL,
  `kode_prodi` varchar(20) NOT NULL,
  `kode_matkul` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `prodi_has_mhs` (
  `id` int(11) NOT NULL,
  `kode_prodi` varchar(20) NOT NULL,
  `nim_npm` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tbl_agama` (
  `id_agama` int(11) NOT NULL,
  `agama` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tbl_fakultas` (
  `kode_fakultas` varchar(10) NOT NULL,
  `nama_fakultas` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tbl_grade` (
  `id_grade` int(11) NOT NULL,
  `grade` varchar(5) NOT NULL,
  `bobot` varchar(5) NOT NULL,
  `nilai_awal` varchar(10) NOT NULL,
  `nilai_akhir` varchar(10) NOT NULL,
  `ket` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tbl_hari` (
  `id_hari` int(11) NOT NULL,
  `nama_hari` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tbl_jenis_mk` (
  `id_jenis_mk` int(11) NOT NULL,
  `jenis_mk` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tbl_jk` (
  `id_jk` int(11) NOT NULL,
  `jenis_kelamin` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tbl_org_tua` (
  `nim_npm` varchar(20) NOT NULL,
  `no_kk` varchar(20) NOT NULL,
  `nama_ayah` varchar(50) NOT NULL,
  `tmp_lhr_ayah` varchar(30) NOT NULL,
  `tgl_lhr_ayah` date NOT NULL,
  `pekerjaan_ayah` varchar(50) NOT NULL,
  `penghasilan_ayah` varchar(20) NOT NULL,
  `pend_ayah` varchar(50) NOT NULL,
  `nama_ibu` varchar(50) NOT NULL,
  `tmp_lhr_ibu` varchar(30) NOT NULL,
  `tgl_lhr_ibu` date NOT NULL,
  `pekerjaan_ibu` varchar(50) NOT NULL,
  `penghasilan_ibu` varchar(20) NOT NULL,
  `pend_ibu` varchar(50) NOT NULL,
  `alamat_org_tua` text NOT NULL,
  `no_telp_orgtua` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tbl_pekerjaan` (
  `id` int(11) NOT NULL,
  `pekerjaan` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `tbl_ruangan` (
  `kode_ruangan` int(11) NOT NULL,
  `kode_fakultas` varchar(10) NOT NULL,
  `nama_ruangan` varchar(80) NOT NULL,
  `lantai` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `thn_akademik` (
  `id_thn_akademik` int(11) NOT NULL,
  `ket` varchar(10) NOT NULL,
  `thn_akademik` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password` text NOT NULL,
  `kode_prodi` varchar(20) NOT NULL,
  `level` varchar(20) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `os` varchar(50) NOT NULL,
  `browser` varchar(50) NOT NULL,
  `tgl` date NOT NULL,
  `waktu` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `dosen`
  ADD PRIMARY KEY (`nip`),
  ADD KEY `id_jk` (`id_jk`),
  ADD KEY `id_agama` (`id_agama`);

ALTER TABLE `dosen_has_matkul`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_jadwal` (`id_jadwal`),
  ADD KEY `nip` (`nip`);

ALTER TABLE `fakultas_has_jurusan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_fakultas` (`kode_fakultas`),
  ADD KEY `kode_prodi` (`kode_prodi`);

ALTER TABLE `jadwal_input_nilai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_thn_akademik` (`id_thn_akademik`);

ALTER TABLE `jadwal_mengajar`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_hari` (`id_hari`),
  ADD KEY `kode_mk` (`kode_mk`),
  ADD KEY `id_thn_akademik` (`id_thn_akademik`),
  ADD KEY `kode_prodi` (`kode_prodi`),
  ADD KEY `kode_ruangan` (`kode_ruangan`),
  ADD KEY `nip` (`nip`);

ALTER TABLE `jadwal_penawaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_thn_akademik` (`id_thn_akademik`);

ALTER TABLE `khs_mhs`
  ADD KEY `id_thn_akademik` (`id_thn_akademik`),
  ADD KEY `nim_npm` (`nim_npm`),
  ADD KEY `id_jadwal` (`id_jadwal`),
  ADD KEY `kode_prodi` (`kode_prodi`);

ALTER TABLE `krs_mhs`
  ADD PRIMARY KEY (`id_krs`),
  ADD KEY `nim_npm` (`nim_npm`),
  ADD KEY `id_thn_akademik` (`id_thn_akademik`),
  ADD KEY `id_jadwal` (`id_jadwal`),
  ADD KEY `kode_prodi` (`kode_prodi`);

ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`nim_npm`),
  ADD KEY `id_agama` (`id_agama`),
  ADD KEY `id_jk` (`id_jk`);

ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`kode_matkul`),
  ADD KEY `id_jenis_mk` (`id_jenis_mk`);

ALTER TABLE `mhs_has_orgtua`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nim_npm` (`nim_npm`),
  ADD KEY `no_kk` (`no_kk`);

ALTER TABLE `mhs_has_pa`
  ADD KEY `nim_npm` (`nim_npm`),
  ADD KEY `nip` (`nip`);

ALTER TABLE `mhs_has_pembayaran_ukt`
  ADD KEY `nim_npm` (`nim_npm`),
  ADD KEY `id_thn_akademik` (`id_thn_akademik`);

ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id_pengaturan`);

ALTER TABLE `pengaturan_sks_mhs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_thn_akademik` (`id_thn_akademik`),
  ADD KEY `nim_npm` (`nim_npm`);

ALTER TABLE `prodi`
  ADD PRIMARY KEY (`kode_prodi`),
  ADD KEY `ketua_prodi` (`ketua_prodi`);

ALTER TABLE `prodi_has_dosen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_prodi` (`kode_prodi`),
  ADD KEY `nip` (`nip`);

ALTER TABLE `prodi_has_matkul`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_matkul` (`kode_matkul`),
  ADD KEY `kode_prodi` (`kode_prodi`);

ALTER TABLE `prodi_has_mhs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_prodi` (`kode_prodi`),
  ADD KEY `nim_npm` (`nim_npm`);

ALTER TABLE `tbl_agama`
  ADD PRIMARY KEY (`id_agama`);

ALTER TABLE `tbl_fakultas`
  ADD PRIMARY KEY (`kode_fakultas`);

ALTER TABLE `tbl_grade`
  ADD PRIMARY KEY (`id_grade`);

ALTER TABLE `tbl_hari`
  ADD PRIMARY KEY (`id_hari`);

ALTER TABLE `tbl_jenis_mk`
  ADD PRIMARY KEY (`id_jenis_mk`);

ALTER TABLE `tbl_jk`
  ADD PRIMARY KEY (`id_jk`);

ALTER TABLE `tbl_org_tua`
  ADD KEY `nim_npm` (`nim_npm`);

ALTER TABLE `tbl_pekerjaan`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tbl_ruangan`
  ADD PRIMARY KEY (`kode_ruangan`),
  ADD KEY `kode_fakultas` (`kode_fakultas`);

ALTER TABLE `thn_akademik`
  ADD PRIMARY KEY (`id_thn_akademik`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

ALTER TABLE `dosen_has_matkul`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `fakultas_has_jurusan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `jadwal_input_nilai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `jadwal_mengajar`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

ALTER TABLE `jadwal_penawaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `krs_mhs`
  MODIFY `id_krs` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

ALTER TABLE `mhs_has_orgtua`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pengaturan`
  MODIFY `id_pengaturan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `pengaturan_sks_mhs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

ALTER TABLE `prodi_has_dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `prodi_has_matkul`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

ALTER TABLE `prodi_has_mhs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `tbl_agama`
  MODIFY `id_agama` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `tbl_grade`
  MODIFY `id_grade` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `tbl_hari`
  MODIFY `id_hari` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `tbl_jenis_mk`
  MODIFY `id_jenis_mk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `tbl_jk`
  MODIFY `id_jk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `tbl_pekerjaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

ALTER TABLE `tbl_ruangan`
  MODIFY `kode_ruangan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `thn_akademik`
  MODIFY `id_thn_akademik` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

ALTER TABLE `dosen`
  ADD CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`id_jk`) REFERENCES `tbl_jk` (`id_jk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dosen_ibfk_2` FOREIGN KEY (`id_agama`) REFERENCES `tbl_agama` (`id_agama`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `dosen_has_matkul`
  ADD CONSTRAINT `dosen_has_matkul_ibfk_1` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_mengajar` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dosen_has_matkul_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `dosen` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `fakultas_has_jurusan`
  ADD CONSTRAINT `fakultas_has_jurusan_ibfk_1` FOREIGN KEY (`kode_fakultas`) REFERENCES `tbl_fakultas` (`kode_fakultas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fakultas_has_jurusan_ibfk_2` FOREIGN KEY (`kode_prodi`) REFERENCES `prodi` (`kode_prodi`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `jadwal_input_nilai`
  ADD CONSTRAINT `jadwal_input_nilai_ibfk_1` FOREIGN KEY (`id_thn_akademik`) REFERENCES `thn_akademik` (`id_thn_akademik`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `jadwal_mengajar`
  ADD CONSTRAINT `jadwal_mengajar_ibfk_1` FOREIGN KEY (`id_hari`) REFERENCES `tbl_hari` (`id_hari`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jadwal_mengajar_ibfk_2` FOREIGN KEY (`kode_mk`) REFERENCES `mata_kuliah` (`kode_matkul`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jadwal_mengajar_ibfk_4` FOREIGN KEY (`id_thn_akademik`) REFERENCES `thn_akademik` (`id_thn_akademik`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jadwal_mengajar_ibfk_5` FOREIGN KEY (`kode_prodi`) REFERENCES `prodi` (`kode_prodi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jadwal_mengajar_ibfk_6` FOREIGN KEY (`kode_ruangan`) REFERENCES `tbl_ruangan` (`kode_ruangan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `jadwal_mengajar_ibfk_7` FOREIGN KEY (`nip`) REFERENCES `dosen` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `jadwal_penawaran`
  ADD CONSTRAINT `jadwal_penawaran_ibfk_1` FOREIGN KEY (`id_thn_akademik`) REFERENCES `thn_akademik` (`id_thn_akademik`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `khs_mhs`
  ADD CONSTRAINT `khs_mhs_ibfk_1` FOREIGN KEY (`id_thn_akademik`) REFERENCES `thn_akademik` (`id_thn_akademik`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `khs_mhs_ibfk_3` FOREIGN KEY (`nim_npm`) REFERENCES `mahasiswa` (`nim_npm`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `khs_mhs_ibfk_4` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_mengajar` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `khs_mhs_ibfk_5` FOREIGN KEY (`kode_prodi`) REFERENCES `prodi` (`kode_prodi`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `krs_mhs`
  ADD CONSTRAINT `krs_mhs_ibfk_2` FOREIGN KEY (`nim_npm`) REFERENCES `mahasiswa` (`nim_npm`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `krs_mhs_ibfk_3` FOREIGN KEY (`id_thn_akademik`) REFERENCES `thn_akademik` (`id_thn_akademik`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `krs_mhs_ibfk_4` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_mengajar` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `krs_mhs_ibfk_5` FOREIGN KEY (`kode_prodi`) REFERENCES `prodi` (`kode_prodi`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`id_agama`) REFERENCES `tbl_agama` (`id_agama`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`id_jk`) REFERENCES `tbl_jk` (`id_jk`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mata_kuliah`
  ADD CONSTRAINT `mata_kuliah_ibfk_1` FOREIGN KEY (`id_jenis_mk`) REFERENCES `tbl_jenis_mk` (`id_jenis_mk`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mhs_has_pa`
  ADD CONSTRAINT `mhs_has_pa_ibfk_1` FOREIGN KEY (`nim_npm`) REFERENCES `mahasiswa` (`nim_npm`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mhs_has_pa_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `dosen` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mhs_has_pembayaran_ukt`
  ADD CONSTRAINT `mhs_has_pembayaran_ukt_ibfk_1` FOREIGN KEY (`nim_npm`) REFERENCES `mahasiswa` (`nim_npm`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mhs_has_pembayaran_ukt_ibfk_2` FOREIGN KEY (`id_thn_akademik`) REFERENCES `thn_akademik` (`id_thn_akademik`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pengaturan_sks_mhs`
  ADD CONSTRAINT `pengaturan_sks_mhs_ibfk_1` FOREIGN KEY (`id_thn_akademik`) REFERENCES `thn_akademik` (`id_thn_akademik`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pengaturan_sks_mhs_ibfk_2` FOREIGN KEY (`nim_npm`) REFERENCES `mahasiswa` (`nim_npm`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `prodi`
  ADD CONSTRAINT `prodi_ibfk_1` FOREIGN KEY (`ketua_prodi`) REFERENCES `dosen` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `prodi_has_dosen`
  ADD CONSTRAINT `prodi_has_dosen_ibfk_1` FOREIGN KEY (`kode_prodi`) REFERENCES `prodi` (`kode_prodi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prodi_has_dosen_ibfk_2` FOREIGN KEY (`nip`) REFERENCES `dosen` (`nip`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `prodi_has_matkul`
  ADD CONSTRAINT `prodi_has_matkul_ibfk_1` FOREIGN KEY (`kode_matkul`) REFERENCES `mata_kuliah` (`kode_matkul`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prodi_has_matkul_ibfk_2` FOREIGN KEY (`kode_prodi`) REFERENCES `prodi` (`kode_prodi`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `prodi_has_mhs`
  ADD CONSTRAINT `prodi_has_mhs_ibfk_1` FOREIGN KEY (`kode_prodi`) REFERENCES `prodi` (`kode_prodi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prodi_has_mhs_ibfk_2` FOREIGN KEY (`nim_npm`) REFERENCES `mahasiswa` (`nim_npm`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tbl_org_tua`
  ADD CONSTRAINT `tbl_org_tua_ibfk_3` FOREIGN KEY (`nim_npm`) REFERENCES `mahasiswa` (`nim_npm`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tbl_ruangan`
  ADD CONSTRAINT `tbl_ruangan_ibfk_1` FOREIGN KEY (`kode_fakultas`) REFERENCES `tbl_fakultas` (`kode_fakultas`) ON DELETE CASCADE ON UPDATE CASCADE;
