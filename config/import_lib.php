<?php
/**
 * Library import data SIAKAD STIE Nusantara
 * ------------------------------------------------------------------
 * Menggantikan pemakaian langsung php-excel-reader di halaman master data.
 * Mendukung file .xls (Excel 97-2003), .xlsx (Excel 2007+) dan .csv
 * tanpa memerlukan composer / PhpSpreadsheet.
 *
 * Semua fungsi diberi prefix imp_ agar tidak bentrok dengan kode lama.
 */

if (!defined('IMP_MAX_UPLOAD_BYTES')) {
	// batas ukuran file yang boleh diproses (10 MB)
	define('IMP_MAX_UPLOAD_BYTES', 10 * 1024 * 1024);
}

/**
 * Menyalin file upload ke direktori sementara yang TIDAK bisa diakses browser.
 * Mengembalikan array(path, ext, nama) atau melempar Exception bila tidak valid.
 */
function imp_terima_upload($field, $ext_diizinkan = array('xls', 'xlsx', 'csv'))
{
	if (!isset($_FILES[$field])) {
		throw new Exception('Tidak ada file yang dikirim. Pastikan form memakai enctype="multipart/form-data".');
	}
	$f = $_FILES[$field];

	if (!isset($f['error']) || is_array($f['error'])) {
		throw new Exception('Parameter upload tidak valid.');
	}
	switch ($f['error']) {
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_NO_FILE:
			throw new Exception('Belum ada file yang dipilih.');
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			throw new Exception('Ukuran file melebihi batas upload server (upload_max_filesize).');
		case UPLOAD_ERR_PARTIAL:
			throw new Exception('File hanya terkirim sebagian, silakan ulangi upload.');
		case UPLOAD_ERR_NO_TMP_DIR:
		case UPLOAD_ERR_CANT_WRITE:
			throw new Exception('Server gagal menulis file sementara. Hubungi administrator hosting.');
		default:
			throw new Exception('Upload gagal dengan kode error ' . (int) $f['error'] . '.');
	}

	if ($f['size'] <= 0) {
		throw new Exception('File yang diupload kosong (0 byte).');
	}
	if ($f['size'] > IMP_MAX_UPLOAD_BYTES) {
		throw new Exception('Ukuran file terlalu besar. Maksimal ' . round(IMP_MAX_UPLOAD_BYTES / 1048576) . ' MB.');
	}

	$nama = basename($f['name']);
	$ext  = strtolower(pathinfo($nama, PATHINFO_EXTENSION));
	if (!in_array($ext, $ext_diizinkan, true)) {
		throw new Exception('Format file ".' . $ext . '" tidak didukung. Gunakan file ' . implode(' / ', $ext_diizinkan) . '.');
	}

	// simpan ke direktori temp sistem, BUKAN ke folder pages/ (agar tidak
	// menumpuk di dalam webroot dan tidak bisa diakses/dieksekusi publik)
	$tujuan = tempnam(sys_get_temp_dir(), 'siakad_imp_');
	if ($tujuan === false) {
		throw new Exception('Gagal membuat file sementara di server.');
	}
	$tujuan_ext = $tujuan . '.' . $ext;
	@unlink($tujuan);

	if (!move_uploaded_file($f['tmp_name'], $tujuan_ext)) {
		throw new Exception('Gagal memindahkan file upload ke direktori sementara.');
	}
	@chmod($tujuan_ext, 0600);

	return array('path' => $tujuan_ext, 'ext' => $ext, 'nama' => $nama);
}

/**
 * Membaca seluruh baris dari file spreadsheet.
 * Hasil: array baris; setiap baris array kolom dengan indeks mulai 1
 * (kompatibel dengan pola lama $data->val($baris, $kolom)).
 */
function imp_baca_spreadsheet($path, $ext)
{
	switch ($ext) {
		case 'xls':
			return imp_baca_xls($path);
		case 'xlsx':
			return imp_baca_xlsx($path);
		case 'csv':
			return imp_baca_csv($path);
	}
	throw new Exception('Format file tidak didukung: ' . $ext);
}

/** Pembaca .xls lewat php-excel-reader (sudah dipatch agar jalan di PHP 8). */
function imp_baca_xls($path)
{
	$reader = dirname(__FILE__) . '/../pages/php-excel-reader/excel_reader2.php';
	if (!file_exists($reader)) {
		throw new Exception('Library pembaca .xls tidak ditemukan di pages/php-excel-reader/.');
	}
	require_once $reader;

	// notice/warning dari library lama tidak perlu tampil ke user
	$level_lama = error_reporting();
	error_reporting($level_lama & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
	try {
		$data = new Spreadsheet_Excel_Reader($path, false);
	} catch (Throwable $e) {
		error_reporting($level_lama);
		throw new Exception('File .xls tidak bisa dibaca. Kemungkinan file bukan Excel 97-2003 asli (misal .xlsx atau HTML yang hanya diganti ekstensinya). Detail: ' . $e->getMessage());
	}
	error_reporting($level_lama);

	$jml_baris = (int) $data->rowcount(0);
	$jml_kolom = (int) $data->colcount(0);
	if ($jml_baris < 1) {
		throw new Exception('File .xls terbaca tetapi tidak berisi data pada sheet pertama.');
	}
	if ($jml_kolom < 1) {
		$jml_kolom = 1;
	}

	$hasil = array();
	for ($i = 1; $i <= $jml_baris; $i++) {
		$baris = array();
		for ($c = 1; $c <= $jml_kolom; $c++) {
			$baris[$c] = $data->val($i, $c, 0);
		}
		$hasil[$i] = $baris;
	}
	return $hasil;
}

/** Pembaca .xlsx memakai ZipArchive + SimpleXML (tanpa dependensi luar). */
function imp_baca_xlsx($path)
{
	if (!class_exists('ZipArchive')) {
		throw new Exception('Ekstensi PHP "zip" belum aktif sehingga file .xlsx tidak bisa dibaca. Aktifkan zip, atau simpan file sebagai .xls / .csv.');
	}
	$zip = new ZipArchive();
	if ($zip->open($path) !== true) {
		throw new Exception('File .xlsx tidak bisa dibuka (file rusak atau bukan .xlsx asli).');
	}

	// shared strings
	$shared = array();
	$ss = $zip->getFromName('xl/sharedStrings.xml');
	if ($ss !== false) {
		$xml = @simplexml_load_string($ss);
		if ($xml !== false) {
			foreach ($xml->si as $si) {
				$teks = '';
				if (isset($si->t)) {
					$teks = (string) $si->t;
				} else {
					foreach ($si->r as $r) {
						$teks .= (string) $r->t;
					}
				}
				$shared[] = $teks;
			}
		}
	}

	// cari sheet pertama sesuai urutan di workbook.xml
	$target = 'xl/worksheets/sheet1.xml';
	$wb   = $zip->getFromName('xl/workbook.xml');
	$rels = $zip->getFromName('xl/_rels/workbook.xml.rels');
	if ($wb !== false && $rels !== false) {
		$xwb  = @simplexml_load_string($wb);
		$xrel = @simplexml_load_string($rels);
		if ($xwb !== false && $xrel !== false && isset($xwb->sheets->sheet[0])) {
			$attr = $xwb->sheets->sheet[0]->attributes('r', true);
			$rid  = $attr ? (string) $attr->id : '';
			foreach ($xrel->Relationship as $rel) {
				if ($rid !== '' && (string) $rel['Id'] === $rid) {
					$t = (string) $rel['Target'];
					$t = preg_replace('#^/?xl/#', '', $t);
					$target = 'xl/' . ltrim($t, '/');
				}
			}
		}
	}

	$sheet = $zip->getFromName($target);
	if ($sheet === false) {
		$sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
	}
	$zip->close();
	if ($sheet === false) {
		throw new Exception('Sheet pertama tidak ditemukan di dalam file .xlsx.');
	}

	$xs = @simplexml_load_string($sheet);
	if ($xs === false) {
		throw new Exception('Struktur XML di dalam file .xlsx tidak valid.');
	}

	$hasil = array();
	$no_baris = 0;
	foreach ($xs->sheetData->row as $row) {
		$no_baris = isset($row['r']) ? (int) $row['r'] : ($no_baris + 1);
		$baris = array();
		foreach ($row->c as $c) {
			$ref  = isset($c['r']) ? (string) $c['r'] : '';
			$kol  = imp_ref_ke_kolom($ref);
			$tipe = isset($c['t']) ? (string) $c['t'] : 'n';
			$nilai = '';
			if ($tipe === 's') {
				$idx = (int) $c->v;
				$nilai = isset($shared[$idx]) ? $shared[$idx] : '';
			} elseif ($tipe === 'inlineStr') {
				if (isset($c->is->t)) {
					$nilai = (string) $c->is->t;
				} else {
					foreach ($c->is->r as $r) {
						$nilai .= (string) $r->t;
					}
				}
			} elseif ($tipe === 'b') {
				$nilai = ((string) $c->v === '1') ? '1' : '0';
			} else {
				$nilai = isset($c->v) ? (string) $c->v : '';
			}
			if ($kol > 0) {
				$baris[$kol] = $nilai;
			}
		}
		if ($baris) {
			ksort($baris);
		}
		$hasil[$no_baris] = $baris;
	}
	if (!$hasil) {
		throw new Exception('File .xlsx terbaca tetapi sheet pertama kosong.');
	}
	return $hasil;
}

/** Pembaca .csv (pemisah , atau ; dideteksi otomatis). */
function imp_baca_csv($path)
{
	$fh = fopen($path, 'r');
	if (!$fh) {
		throw new Exception('File CSV tidak bisa dibuka.');
	}
	$baris1 = fgets($fh);
	if ($baris1 === false) {
		fclose($fh);
		throw new Exception('File CSV kosong.');
	}
	$delim = (substr_count($baris1, ';') > substr_count($baris1, ',')) ? ';' : ',';
	rewind($fh);

	$hasil = array();
	$n = 0;
	while (($kolom = fgetcsv($fh, 0, $delim)) !== false) {
		$n++;
		$baris = array();
		foreach ($kolom as $i => $v) {
			if ($n === 1 && $i === 0) {
				// buang BOM UTF-8 di sel pertama
				$v = preg_replace('/^\xEF\xBB\xBF/', '', (string) $v);
			}
			$baris[$i + 1] = $v;
		}
		$hasil[$n] = $baris;
	}
	fclose($fh);
	if (!$hasil) {
		throw new Exception('File CSV tidak berisi data.');
	}
	return $hasil;
}

/** Ubah referensi sel ("C12") menjadi nomor kolom (3). */
function imp_ref_ke_kolom($ref)
{
	if (!preg_match('/^([A-Za-z]+)/', $ref, $m)) {
		return 0;
	}
	$huruf = strtoupper($m[1]);
	$kol = 0;
	for ($i = 0, $n = strlen($huruf); $i < $n; $i++) {
		$kol = $kol * 26 + (ord($huruf[$i]) - 64);
	}
	return $kol;
}

/** Ambil sel dari array baris hasil pembacaan, selalu string ter-trim. */
function imp_sel($baris, $kolom)
{
	if (!isset($baris[$kolom])) {
		return '';
	}
	$v = $baris[$kolom];
	if (is_array($v) || is_object($v)) {
		return '';
	}
	// rapikan spasi, termasuk non-breaking space hasil copy-paste dari web
	$v = str_replace(array("\xC2\xA0", "\xA0"), ' ', (string) $v);
	$v = preg_replace('/\s+/u', ' ', $v);
	return trim((string) $v);
}

/** Deteksi baris kosong (semua sel kosong). */
function imp_baris_kosong($baris)
{
	foreach ($baris as $v) {
		if (is_scalar($v) && trim((string) $v) !== '') {
			return false;
		}
	}
	return true;
}

/**
 * Normalisasi tanggal ke format Y-m-d.
 * Menerima: serial Excel (43101), Y-m-d, d/m/Y, d-m-Y, d.m.Y, "1 Januari 2005",
 * dan timestamp unix (bentuk yang dikembalikan php-excel-reader untuk sel tanggal).
 * Mengembalikan null bila kosong / tidak bisa dikenali.
 */
function imp_tanggal($v)
{
	$v = trim((string) $v);
	if ($v === '' || $v === '0' || $v === '0000-00-00') {
		return null;
	}

	if (preg_match('/^\d+(\.\d+)?$/', $v)) {
		$angka = (float) $v;
		if ($angka > 0 && $angka < 2958466) {
			// serial Excel (sistem tanggal 1900); batas atas = 31-12-9999
			$hari = (int) floor($angka);
			if ($hari >= 60) {
				$hari--; // kompensasi tahun kabisat palsu 1900 di Excel
			}
			$ts = mktime(12, 0, 0, 1, 1, 1900) + ($hari - 1) * 86400;
			return date('Y-m-d', $ts);
		}
		if ($angka >= 2958466) {
			// kemungkinan timestamp unix (dipakai php-excel-reader untuk sel tanggal)
			return date('Y-m-d', (int) $angka);
		}
	}

	$v2 = str_replace(array('.', '/'), '-', $v);
	// buang bagian jam bila ada
	$v2 = preg_replace('/\s+\d{1,2}:\d{2}(:\d{2})?$/', '', $v2);

	if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $v2, $m)) {
		return imp_cek_tanggal($m[1], $m[2], $m[3]);
	}
	if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $v2, $m)) {
		return imp_cek_tanggal($m[3], $m[2], $m[1]);
	}
	if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{2})$/', $v2, $m)) {
		$th = (int) $m[3];
		$th += ($th <= 30) ? 2000 : 1900;
		return imp_cek_tanggal($th, $m[2], $m[1]);
	}

	$bulan_id = array(
		'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
		'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
		'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6, 'jul' => 7,
		'agu' => 8, 'ags' => 8, 'aug' => 8, 'sep' => 9, 'okt' => 10, 'oct' => 10,
		'nov' => 11, 'des' => 12, 'dec' => 12, 'may' => 5, 'march' => 3, 'june' => 6, 'july' => 7,
	);
	if (preg_match('/^(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})$/u', $v, $m)) {
		$nb = strtolower($m[2]);
		if (isset($bulan_id[$nb])) {
			return imp_cek_tanggal($m[3], $bulan_id[$nb], $m[1]);
		}
	}
	return null;
}

function imp_cek_tanggal($th, $bl, $tg)
{
	$th = (int) $th;
	$bl = (int) $bl;
	$tg = (int) $tg;
	if (!checkdate($bl, $tg, $th)) {
		return null;
	}
	return sprintf('%04d-%02d-%02d', $th, $bl, $tg);
}

/** Normalisasi jenis kelamin ke id_jk (1 = Laki-laki, 2 = Perempuan). */
function imp_jk($v)
{
	$v = strtolower(trim((string) $v));
	if ($v === '') {
		return null;
	}
	if ($v === '1' || $v === 'l' || $v === 'lk' || $v === 'm' ||
		strpos($v, 'laki') !== false || strpos($v, 'pria') !== false || strpos($v, 'male') !== false) {
		return 1;
	}
	if ($v === '2' || $v === 'p' || $v === 'w' || $v === 'f' ||
		strpos($v, 'perempuan') !== false || strpos($v, 'wanita') !== false || strpos($v, 'female') !== false) {
		return 2;
	}
	return null;
}

/** Normalisasi agama ke id_agama sesuai tbl_agama. */
function imp_agama($v)
{
	$v = strtolower(trim((string) $v));
	if ($v === '') {
		return null;
	}
	if (preg_match('/^[1-6]$/', $v)) {
		return (int) $v;
	}
	if (strpos($v, 'islam') !== false || strpos($v, 'muslim') !== false) return 1;
	if (strpos($v, 'protestan') !== false) return 2;
	if (strpos($v, 'katolik') !== false || strpos($v, 'katholik') !== false) return 3;
	if (strpos($v, 'hindu') !== false) return 4;
	if (strpos($v, 'budha') !== false || strpos($v, 'buddha') !== false) return 5;
	if (strpos($v, 'konghucu') !== false || strpos($v, 'khonghucu') !== false) return 6;
	if (strpos($v, 'kristen') !== false) return 2; // "Kristen" saja dianggap Protestan
	return null;
}

/** Normalisasi status mahasiswa ke nilai yang dipakai aplikasi. */
function imp_status_mhs($v)
{
	$v = strtolower(trim((string) $v));
	if ($v === '') {
		return 'Aktif';
	}
	if (strpos($v, 'tidak') !== false || strpos($v, 'non') !== false || strpos($v, 'cuti') !== false) {
		return 'Tidak Aktif';
	}
	if (strpos($v, 'lulus') !== false || strpos($v, 'alumni') !== false) {
		return 'Lulus';
	}
	if (strpos($v, 'aktif') !== false) {
		return 'Aktif';
	}
	return imp_batas(ucwords($v), 20);
}

/** Ambil angka bulat dari sel; null bila kosong/bukan angka. */
function imp_int($v)
{
	$v = trim((string) $v);
	if ($v === '') {
		return null;
	}
	if (!preg_match('/-?\d+/', str_replace(array('.', ','), '', $v), $m)) {
		return null;
	}
	return (int) $m[0];
}

/** Rapikan nomor telepon agar tidak melebihi panjang kolom. */
function imp_telp($v, $maks = 12)
{
	$v = preg_replace('/[^0-9+]/', '', (string) $v);
	if ($v === '' || $v === '+') {
		return '';
	}
	if (strpos($v, '+62') === 0) {
		$v = '0' . substr($v, 3);
	} elseif (strpos($v, '62') === 0 && strlen($v) > 10) {
		$v = '0' . substr($v, 2);
	}
	$v = ltrim($v, '+');
	return substr($v, 0, $maks);
}

/** Potong string agar tidak melebihi panjang kolom (hindari error/truncate MySQL). */
function imp_batas($v, $maks)
{
	$v = (string) $v;
	if (function_exists('mb_substr')) {
		return mb_substr($v, 0, $maks, 'UTF-8');
	}
	return substr($v, 0, $maks);
}

/**
 * Tentukan baris awal data: bila baris pertama berisi teks header,
 * data dimulai dari baris berikutnya.
 */
function imp_baris_awal($rows, $kata_header = array())
{
	$kunci = array_keys($rows);
	if (!$kunci) {
		return 1;
	}
	$pertama = $rows[$kunci[0]];
	$gabung = strtolower(implode(' ', array_map(function ($x) {
		return is_scalar($x) ? (string) $x : '';
	}, $pertama)));

	$default = array('nim', 'npm', 'nama', 'kode', 'nip', 'nidn', 'sks', 'semester', 'fakultas', 'matkul');
	foreach (array_merge($default, $kata_header) as $k) {
		if ($k !== '' && strpos($gabung, strtolower($k)) !== false) {
			return $kunci[0] + 1;
		}
	}
	return $kunci[0];
}

/**
 * Ambil informasi kolom sebuah tabel: apakah boleh NULL dan tipe datanya.
 * Dipakai agar importer bisa mengisi NULL untuk data opsional yang kosong
 * hanya bila struktur tabel memang mengizinkannya.
 */
function imp_kolom_info($koneksi, $tabel)
{
	$info = array();
	$tabel_aman = str_replace('`', '', (string) $tabel);
	$q = @mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel_aman`");
	if (!$q) {
		return $info;
	}
	while ($r = mysqli_fetch_assoc($q)) {
		$info[$r['Field']] = array(
			'null' => (strtoupper($r['Null']) === 'YES'),
			'type' => strtolower($r['Type']),
		);
	}
	return $info;
}

/** Apakah kolom boleh NULL? (default false bila info tidak tersedia) */
function imp_boleh_null($info, $kolom)
{
	return isset($info[$kolom]) ? (bool) $info[$kolom]['null'] : false;
}

/** Hapus file sementara hasil upload. */
function imp_bersihkan($path)
{
	if ($path && file_exists($path)) {
		@unlink($path);
	}
}

/** Struktur hasil import yang kosong. */
function imp_hasil_baru()
{
	return array(
		'total'  => 0,
		'sukses' => 0,
		'update' => 0,
		'gagal'  => 0,
		'fatal'  => '',
		'detail' => array(),
	);
}

/** Catat keterangan per baris ke dalam hasil import. */
function imp_catat(&$hasil, $baris, $pesan)
{
	$hasil['detail'][] = array('baris' => $baris, 'pesan' => $pesan);
}

/**
 * Render hasil import sebagai kartu ringkasan (dipakai di halaman master data).
 */
function imp_render_hasil($hasil)
{
	if (!$hasil) {
		return;
	}
	$gagal = (int) $hasil['gagal'];
	$warna = $gagal > 0 ? 'warning' : 'success';
	if (!empty($hasil['fatal'])) {
		$warna = 'danger';
	}
	echo '<div class="col-12"><div class="card"><div class="card-status-top bg-' . $warna . '"></div><div class="card-body">';
	echo '<h3 class="card-title">Hasil Import Data</h3>';
	if (!empty($hasil['fatal'])) {
		echo '<p class="text-danger"><strong>Import dibatalkan:</strong> ' . htmlspecialchars($hasil['fatal'], ENT_QUOTES, 'UTF-8') . '</p>';
	} else {
		echo '<p>Baris data terbaca: <strong>' . (int) $hasil['total'] . '</strong> &middot; ';
		echo 'Berhasil ditambah: <strong class="text-success">' . (int) $hasil['sukses'] . '</strong> &middot; ';
		echo 'Diperbarui: <strong>' . (int) $hasil['update'] . '</strong> &middot; ';
		echo 'Gagal/dilewati: <strong class="text-danger">' . $gagal . '</strong></p>';
	}
	if (!empty($hasil['detail'])) {
		echo '<div class="table-responsive"><table class="table table-sm table-vcenter"><thead><tr><th style="width:110px">Baris Excel</th><th>Keterangan</th></tr></thead><tbody>';
		$n = 0;
		$jml = count($hasil['detail']);
		foreach ($hasil['detail'] as $d) {
			if ($n >= 200) {
				echo '<tr><td colspan="2"><em>... dan ' . ($jml - 200) . ' keterangan lainnya.</em></td></tr>';
				break;
			}
			$n++;
			echo '<tr><td>' . (int) $d['baris'] . '</td><td>' . htmlspecialchars($d['pesan'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
		}
		echo '</tbody></table></div>';
	}
	echo '</div></div></div>';
}
