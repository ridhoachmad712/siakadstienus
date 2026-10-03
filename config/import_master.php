<?php
/**
 * Proses import data master SIAKAD STIE Nusantara.
 * ------------------------------------------------------------------
 * Berisi satu fungsi untuk tiap tabel master. Semua fungsi menerima
 * array baris hasil imp_baca_spreadsheet() dan mengembalikan struktur
 * hasil (imp_hasil_baru) yang siap dirender imp_render_hasil().
 *
 * Semua query memakai prepared statement, jadi nama seperti
 * "Cedric Rif'Aa Muamar" tidak lagi merusak query.
 */

require_once dirname(__FILE__) . '/import_lib.php';

const IMP_MIGRASI = 'database/migrations/2026-09-11_perbaikan_import.sql';

/**
 * Import tabel `mahasiswa`.
 * Kolom file: 1 NIM, 2 THN MASUK, 3 JALUR, 4 SEKOLAH ASAL, 5 NAMA, 6 JK,
 * 7 TMP LAHIR, 8 TGL LAHIR, 9 AGAMA, 10 EMAIL, 11 ALAMAT, 12 NO TELP,
 * 13 FOTO, 14 STATUS.
 */
function imp_proses_mahasiswa($koneksi, $rows, $mode_update = false)
{
	$hasil = imp_hasil_baru();
	$awal  = imp_baris_awal($rows, array('thn masuk', 'jalur masuk', 'status mahasiswa'));
	$kolom = imp_kolom_info($koneksi, 'mahasiswa');

	$stmt_cek = mysqli_prepare($koneksi, "SELECT nim_npm FROM mahasiswa WHERE nim_npm=?");
	$stmt_ins = mysqli_prepare($koneksi, "INSERT INTO mahasiswa
		(nim_npm, thn_masuk, lulusan_jalur, sekolah_asal, nama_mhs, id_jk, tempat_lhr,
		 tgl_lhr_mhs, id_agama, email, alamat_mhs, no_telp_mhs, foto_mhs, status_mhs)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
	$stmt_upd = mysqli_prepare($koneksi, "UPDATE mahasiswa SET
		 thn_masuk=?, lulusan_jalur=?, sekolah_asal=?, nama_mhs=?, id_jk=?, tempat_lhr=?,
		 tgl_lhr_mhs=?, id_agama=?, email=?, alamat_mhs=?, no_telp_mhs=?, status_mhs=?
		WHERE nim_npm=?");
	if (!$stmt_cek || !$stmt_ins || !$stmt_upd) {
		throw new Exception('Gagal menyiapkan query import: ' . mysqli_error($koneksi));
	}

	$kunci_file = array();

	foreach ($rows as $no => $baris) {
		if ($no < $awal || imp_baris_kosong($baris)) {
			continue;
		}
		$hasil['total']++;

		$nim          = imp_sel($baris, 1);
		$thn_masuk    = imp_int(imp_sel($baris, 2));
		$jalur        = imp_batas(imp_sel($baris, 3), 20);
		$sekolah_asal = imp_batas(imp_sel($baris, 4), 50);
		$nama_mhs     = imp_batas(imp_sel($baris, 5), 80);
		$id_jk        = imp_jk(imp_sel($baris, 6));
		$tmp_lhr      = imp_batas(imp_sel($baris, 7), 50);
		$tgl_mentah   = imp_sel($baris, 8);
		$tgl_lhr      = imp_tanggal($tgl_mentah);
		$id_agama     = imp_agama(imp_sel($baris, 9));
		$email        = imp_sel($baris, 10);
		$alamat       = imp_sel($baris, 11);
		$no_telp      = imp_telp(imp_sel($baris, 12), imp_panjang($kolom, 'no_telp_mhs', 12));
		$foto_mhs     = imp_sel($baris, 13);
		$status_mhs   = imp_status_mhs(imp_sel($baris, 14));

		// ---------- validasi wajib ----------
		if ($nim === '') {
			imp_gagal($hasil, $no, 'Dilewati: kolom NIM/NPM kosong.');
			continue;
		}
		if (strlen($nim) > 20) {
			imp_gagal($hasil, $no, 'Dilewati: NIM "' . $nim . '" lebih dari 20 karakter.');
			continue;
		}
		if ($nama_mhs === '') {
			imp_gagal($hasil, $no, 'Dilewati: nama mahasiswa kosong (NIM ' . $nim . ').');
			continue;
		}
		if ($thn_masuk === null || $thn_masuk < 1900 || $thn_masuk > 2100) {
			imp_gagal($hasil, $no, 'Dilewati: tahun masuk tidak valid (NIM ' . $nim . ').');
			continue;
		}
		if (isset($kunci_file[$nim])) {
			imp_gagal($hasil, $no, 'Dilewati: NIM ' . $nim . ' ganda di dalam file (sudah ada di baris ' . $kunci_file[$nim] . ').');
			continue;
		}
		$kunci_file[$nim] = $no;

		// ---------- data opsional ----------
		if ($tgl_mentah !== '' && $tgl_lhr === null) {
			imp_catat($hasil, $no, 'Peringatan: tanggal lahir "' . $tgl_mentah . '" tidak dikenali (NIM ' . $nim . ').');
		}
		if ($tgl_lhr === null && !imp_boleh_null($kolom, 'tgl_lhr_mhs')) {
			imp_gagal($hasil, $no, 'Dilewati: tanggal lahir kosong/tidak valid, sedangkan kolom tgl_lhr_mhs masih wajib diisi (NIM ' . $nim . '). Isi tanggalnya, atau jalankan migrasi ' . IMP_MIGRASI . '.');
			continue;
		}
		if ($id_jk === null && !imp_boleh_null($kolom, 'id_jk')) {
			imp_gagal($hasil, $no, 'Dilewati: jenis kelamin kosong/tidak dikenali, sedangkan kolom id_jk masih wajib diisi (NIM ' . $nim . '). Isi 1/2 atau L/P, atau jalankan migrasi ' . IMP_MIGRASI . '.');
			continue;
		}
		if ($id_agama === null && !imp_boleh_null($kolom, 'id_agama')) {
			imp_gagal($hasil, $no, 'Dilewati: agama kosong/tidak dikenali, sedangkan kolom id_agama masih wajib diisi (NIM ' . $nim . '). Isi 1-6 atau nama agama, atau jalankan migrasi ' . IMP_MIGRASI . '.');
			continue;
		}

		try {
			$sudah_ada = imp_sudah_ada($stmt_cek, $nim);
			if ($sudah_ada && !$mode_update) {
				imp_gagal($hasil, $no, 'Dilewati: NIM ' . $nim . ' sudah ada di database. Centang "Perbarui data bila sudah ada" bila ingin menimpa.');
				continue;
			}
			if ($sudah_ada) {
				mysqli_stmt_bind_param(
					$stmt_upd,
					'isssississsss',
					$thn_masuk, $jalur, $sekolah_asal, $nama_mhs, $id_jk, $tmp_lhr,
					$tgl_lhr, $id_agama, $email, $alamat, $no_telp, $status_mhs, $nim
				);
				mysqli_stmt_execute($stmt_upd);
				$hasil['update']++;
			} else {
				mysqli_stmt_bind_param(
					$stmt_ins,
					'sisssississsss',
					$nim, $thn_masuk, $jalur, $sekolah_asal, $nama_mhs, $id_jk, $tmp_lhr,
					$tgl_lhr, $id_agama, $email, $alamat, $no_telp, $foto_mhs, $status_mhs
				);
				mysqli_stmt_execute($stmt_ins);
				$hasil['sukses']++;
			}
		} catch (Throwable $e) {
			imp_gagal($hasil, $no, 'Gagal simpan NIM ' . $nim . ': ' . imp_pesan_db($e));
		}
	}

	mysqli_stmt_close($stmt_cek);
	mysqli_stmt_close($stmt_ins);
	mysqli_stmt_close($stmt_upd);
	return $hasil;
}

/**
 * Import tabel `dosen`.
 * Kolom file: 1 NIDN/NIP, 2 NAMA, 3 JK, 4 AGAMA, 5 ALAMAT, 6 FOTO,
 * 7 TMP LAHIR, 8 TGL LAHIR, 9 EMAIL, 10 NO TELP.
 */
function imp_proses_dosen($koneksi, $rows, $mode_update = false)
{
	$hasil = imp_hasil_baru();
	$awal  = imp_baris_awal($rows, array('nidn', 'nama dosen'));
	$kolom = imp_kolom_info($koneksi, 'dosen');

	$stmt_cek = mysqli_prepare($koneksi, "SELECT nip FROM dosen WHERE nip=?");
	$stmt_ins = mysqli_prepare($koneksi, "INSERT INTO dosen
		(nip, nama_dosen, id_jk, id_agama, alamat, foto_dosen, tmp_lhr_dosen, tgl_lhr_dosen, email, no_telp)
		VALUES (?,?,?,?,?,?,?,?,?,?)");
	$stmt_upd = mysqli_prepare($koneksi, "UPDATE dosen SET
		 nama_dosen=?, id_jk=?, id_agama=?, alamat=?, tmp_lhr_dosen=?, tgl_lhr_dosen=?, email=?, no_telp=?
		WHERE nip=?");
	if (!$stmt_cek || !$stmt_ins || !$stmt_upd) {
		throw new Exception('Gagal menyiapkan query import: ' . mysqli_error($koneksi));
	}

	$kunci_file = array();

	foreach ($rows as $no => $baris) {
		if ($no < $awal || imp_baris_kosong($baris)) {
			continue;
		}
		$hasil['total']++;

		$nip        = imp_sel($baris, 1);
		$nama       = imp_batas(imp_sel($baris, 2), 80);
		$id_jk      = imp_jk(imp_sel($baris, 3));
		$id_agama   = imp_agama(imp_sel($baris, 4));
		$alamat     = imp_sel($baris, 5);
		$foto       = imp_sel($baris, 6);
		$tmp_lhr    = imp_batas(imp_sel($baris, 7), 30);
		$tgl_mentah = imp_sel($baris, 8);
		$tgl_lhr    = imp_tanggal($tgl_mentah);
		$email      = imp_batas(imp_sel($baris, 9), 50);
		$no_telp    = imp_telp(imp_sel($baris, 10), imp_panjang($kolom, 'no_telp', 12));

		if ($nip === '') {
			imp_gagal($hasil, $no, 'Dilewati: kolom NIDN/NIP kosong.');
			continue;
		}
		if (strlen($nip) > 20) {
			imp_gagal($hasil, $no, 'Dilewati: NIDN/NIP "' . $nip . '" lebih dari 20 karakter.');
			continue;
		}
		if ($nama === '') {
			imp_gagal($hasil, $no, 'Dilewati: nama dosen kosong (NIDN ' . $nip . ').');
			continue;
		}
		if (isset($kunci_file[$nip])) {
			imp_gagal($hasil, $no, 'Dilewati: NIDN ' . $nip . ' ganda di dalam file (baris ' . $kunci_file[$nip] . ').');
			continue;
		}
		$kunci_file[$nip] = $no;

		if ($tgl_mentah !== '' && $tgl_lhr === null) {
			imp_catat($hasil, $no, 'Peringatan: tanggal lahir "' . $tgl_mentah . '" tidak dikenali (NIDN ' . $nip . ').');
		}
		if ($tgl_lhr === null && !imp_boleh_null($kolom, 'tgl_lhr_dosen')) {
			imp_gagal($hasil, $no, 'Dilewati: tanggal lahir kosong/tidak valid, sedangkan kolom tgl_lhr_dosen masih wajib diisi (NIDN ' . $nip . '). Jalankan migrasi ' . IMP_MIGRASI . ' bila ingin mengizinkan kosong.');
			continue;
		}
		if ($id_jk === null && !imp_boleh_null($kolom, 'id_jk')) {
			imp_gagal($hasil, $no, 'Dilewati: jenis kelamin kosong/tidak dikenali (NIDN ' . $nip . '). Isi 1/2 atau L/P, atau jalankan migrasi ' . IMP_MIGRASI . '.');
			continue;
		}
		if ($id_agama === null && !imp_boleh_null($kolom, 'id_agama')) {
			imp_gagal($hasil, $no, 'Dilewati: agama kosong/tidak dikenali (NIDN ' . $nip . '). Isi 1-6 atau nama agama, atau jalankan migrasi ' . IMP_MIGRASI . '.');
			continue;
		}

		try {
			$sudah_ada = imp_sudah_ada($stmt_cek, $nip);
			if ($sudah_ada && !$mode_update) {
				imp_gagal($hasil, $no, 'Dilewati: NIDN/NIP ' . $nip . ' sudah ada di database. Centang "Perbarui data bila sudah ada" bila ingin menimpa.');
				continue;
			}
			if ($sudah_ada) {
				mysqli_stmt_bind_param(
					$stmt_upd,
					'siissssss',
					$nama, $id_jk, $id_agama, $alamat, $tmp_lhr, $tgl_lhr, $email, $no_telp, $nip
				);
				mysqli_stmt_execute($stmt_upd);
				$hasil['update']++;
			} else {
				mysqli_stmt_bind_param(
					$stmt_ins,
					'ssiissssss',
					$nip, $nama, $id_jk, $id_agama, $alamat, $foto, $tmp_lhr, $tgl_lhr, $email, $no_telp
				);
				mysqli_stmt_execute($stmt_ins);
				$hasil['sukses']++;
			}
		} catch (Throwable $e) {
			imp_gagal($hasil, $no, 'Gagal simpan NIDN ' . $nip . ': ' . imp_pesan_db($e));
		}
	}

	mysqli_stmt_close($stmt_cek);
	mysqli_stmt_close($stmt_ins);
	mysqli_stmt_close($stmt_upd);
	return $hasil;
}

/**
 * Import tabel `mata_kuliah`.
 * Kolom file: 1 kode_matkul, 2 nama_matkul, 3 sks, 4 semester, 5 id_jenis_mk.
 */
function imp_proses_matkul($koneksi, $rows, $mode_update = false)
{
	$hasil = imp_hasil_baru();
	$awal  = imp_baris_awal($rows, array('kode_matkul', 'nama_matkul'));
	$kolom = imp_kolom_info($koneksi, 'mata_kuliah');

	$stmt_cek = mysqli_prepare($koneksi, "SELECT kode_matkul FROM mata_kuliah WHERE kode_matkul=?");
	$stmt_ins = mysqli_prepare($koneksi, "INSERT INTO mata_kuliah
		(kode_matkul, nama_matkul, sks, semester, id_jenis_mk) VALUES (?,?,?,?,?)");
	$stmt_upd = mysqli_prepare($koneksi, "UPDATE mata_kuliah SET
		 nama_matkul=?, sks=?, semester=?, id_jenis_mk=? WHERE kode_matkul=?");
	if (!$stmt_cek || !$stmt_ins || !$stmt_upd) {
		throw new Exception('Gagal menyiapkan query import: ' . mysqli_error($koneksi));
	}

	$kunci_file = array();

	foreach ($rows as $no => $baris) {
		if ($no < $awal || imp_baris_kosong($baris)) {
			continue;
		}
		$hasil['total']++;

		$kode      = imp_sel($baris, 1);
		$nama      = imp_batas(imp_sel($baris, 2), 50);
		$sks       = imp_int(imp_sel($baris, 3));
		$smt_asli  = imp_sel($baris, 4);
		$semester  = imp_int($smt_asli);
		$jenis     = imp_int(imp_sel($baris, 5));

		if ($kode === '') {
			imp_gagal($hasil, $no, 'Dilewati: kode mata kuliah kosong.');
			continue;
		}
		if (strlen($kode) > 20) {
			imp_gagal($hasil, $no, 'Dilewati: kode "' . $kode . '" lebih dari 20 karakter.');
			continue;
		}
		if ($nama === '') {
			imp_gagal($hasil, $no, 'Dilewati: nama mata kuliah kosong (kode ' . $kode . ').');
			continue;
		}
		if ($sks === null) {
			imp_gagal($hasil, $no, 'Dilewati: SKS kosong/bukan angka (kode ' . $kode . ').');
			continue;
		}
		if ($semester === null) {
			imp_gagal($hasil, $no, 'Dilewati: semester kosong/bukan angka (kode ' . $kode . ').');
			continue;
		}
		if ($smt_asli !== (string) $semester) {
			imp_catat($hasil, $no, 'Peringatan: semester "' . $smt_asli . '" dibaca sebagai ' . $semester . ' (kode ' . $kode . ').');
		}
		if ($jenis === null && !imp_boleh_null($kolom, 'id_jenis_mk')) {
			imp_gagal($hasil, $no, 'Dilewati: id_jenis_mk kosong, sedangkan kolomnya wajib diisi (kode ' . $kode . '). Isi 1=Wajib, 2=Umum, 3=Pilihan, atau jalankan migrasi ' . IMP_MIGRASI . '.');
			continue;
		}
		if (isset($kunci_file[$kode])) {
			imp_gagal($hasil, $no, 'Dilewati: kode ' . $kode . ' ganda di dalam file (baris ' . $kunci_file[$kode] . ').');
			continue;
		}
		$kunci_file[$kode] = $no;

		try {
			$sudah_ada = imp_sudah_ada($stmt_cek, $kode);
			if ($sudah_ada && !$mode_update) {
				imp_gagal($hasil, $no, 'Dilewati: kode ' . $kode . ' sudah ada di database. Centang "Perbarui data bila sudah ada" bila ingin menimpa.');
				continue;
			}
			if ($sudah_ada) {
				mysqli_stmt_bind_param($stmt_upd, 'siiis', $nama, $sks, $semester, $jenis, $kode);
				mysqli_stmt_execute($stmt_upd);
				$hasil['update']++;
			} else {
				mysqli_stmt_bind_param($stmt_ins, 'ssiii', $kode, $nama, $sks, $semester, $jenis);
				mysqli_stmt_execute($stmt_ins);
				$hasil['sukses']++;
			}
		} catch (Throwable $e) {
			imp_gagal($hasil, $no, 'Gagal simpan kode ' . $kode . ': ' . imp_pesan_db($e));
		}
	}

	mysqli_stmt_close($stmt_cek);
	mysqli_stmt_close($stmt_ins);
	mysqli_stmt_close($stmt_upd);
	return $hasil;
}

/**
 * Import tabel `tbl_fakultas`.
 * Kolom file: 1 kode_fakultas, 2 nama_fakultas.
 */
function imp_proses_fakultas($koneksi, $rows, $mode_update = false)
{
	$hasil = imp_hasil_baru();
	$awal  = imp_baris_awal($rows, array('kode fakultas', 'nama fakultas'));

	$stmt_cek = mysqli_prepare($koneksi, "SELECT kode_fakultas FROM tbl_fakultas WHERE kode_fakultas=?");
	$stmt_ins = mysqli_prepare($koneksi, "INSERT INTO tbl_fakultas (kode_fakultas, nama_fakultas) VALUES (?,?)");
	$stmt_upd = mysqli_prepare($koneksi, "UPDATE tbl_fakultas SET nama_fakultas=? WHERE kode_fakultas=?");
	if (!$stmt_cek || !$stmt_ins || !$stmt_upd) {
		throw new Exception('Gagal menyiapkan query import: ' . mysqli_error($koneksi));
	}

	$kunci_file = array();

	foreach ($rows as $no => $baris) {
		if ($no < $awal || imp_baris_kosong($baris)) {
			continue;
		}
		$hasil['total']++;

		$kode = imp_sel($baris, 1);
		$nama = imp_sel($baris, 2);

		if ($kode === '') {
			imp_gagal($hasil, $no, 'Dilewati: kode fakultas/institusi kosong.');
			continue;
		}
		if (strlen($kode) > 10) {
			imp_gagal($hasil, $no, 'Dilewati: kode "' . $kode . '" lebih dari 10 karakter.');
			continue;
		}
		if ($nama === '') {
			imp_gagal($hasil, $no, 'Dilewati: nama fakultas/institusi kosong (kode ' . $kode . ').');
			continue;
		}
		if (isset($kunci_file[$kode])) {
			imp_gagal($hasil, $no, 'Dilewati: kode ' . $kode . ' ganda di dalam file (baris ' . $kunci_file[$kode] . ').');
			continue;
		}
		$kunci_file[$kode] = $no;

		try {
			$sudah_ada = imp_sudah_ada($stmt_cek, $kode);
			if ($sudah_ada && !$mode_update) {
				imp_gagal($hasil, $no, 'Dilewati: kode ' . $kode . ' sudah ada di database. Centang "Perbarui data bila sudah ada" bila ingin menimpa.');
				continue;
			}
			if ($sudah_ada) {
				mysqli_stmt_bind_param($stmt_upd, 'ss', $nama, $kode);
				mysqli_stmt_execute($stmt_upd);
				$hasil['update']++;
			} else {
				mysqli_stmt_bind_param($stmt_ins, 'ss', $kode, $nama);
				mysqli_stmt_execute($stmt_ins);
				$hasil['sukses']++;
			}
		} catch (Throwable $e) {
			imp_gagal($hasil, $no, 'Gagal simpan kode ' . $kode . ': ' . imp_pesan_db($e));
		}
	}

	mysqli_stmt_close($stmt_cek);
	mysqli_stmt_close($stmt_ins);
	mysqli_stmt_close($stmt_upd);
	return $hasil;
}

// ------------------------------------------------------------------
// Fungsi bantu bersama
// ------------------------------------------------------------------

/** Catat satu baris gagal/dilewati. */
function imp_gagal(&$hasil, $baris, $pesan)
{
	$hasil['gagal']++;
	imp_catat($hasil, $baris, $pesan);
}

/** Cek keberadaan kunci utama memakai prepared statement. */
function imp_sudah_ada($stmt, $kunci)
{
	mysqli_stmt_bind_param($stmt, 's', $kunci);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_store_result($stmt);
	$ada = (mysqli_stmt_num_rows($stmt) > 0);
	mysqli_stmt_free_result($stmt);
	return $ada;
}

/** Panjang maksimum kolom varchar dari hasil imp_kolom_info(). */
function imp_panjang($kolom, $nama, $default)
{
	if (isset($kolom[$nama]['type']) && preg_match('/varchar\((\d+)\)/', $kolom[$nama]['type'], $m)) {
		return (int) $m[1];
	}
	return $default;
}

/** Terjemahkan error MySQL yang umum ke bahasa yang mudah dimengerti. */
function imp_pesan_db($e)
{
	$pesan = $e->getMessage();
	if (strpos($pesan, 'foreign key constraint') !== false) {
		return 'nilai jenis kelamin / agama / jenis mata kuliah tidak ada di tabel referensi.';
	}
	if (strpos($pesan, 'Duplicate entry') !== false) {
		return 'data dengan kunci yang sama sudah ada di database.';
	}
	if (strpos($pesan, 'Incorrect date value') !== false || strpos($pesan, 'Incorrect datetime') !== false) {
		return 'format tanggal tidak diterima database.';
	}
	if (strpos($pesan, 'Data too long') !== false) {
		return 'isi salah satu kolom terlalu panjang untuk struktur tabel.';
	}
	return $pesan;
}
