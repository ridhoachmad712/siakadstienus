<?php 
// Kredensial database dibaca dari config/db_config.php (tidak ikut ke git).
// Bila file itu belum ada, salin dari config/db_config.sample.php.
$file_konfigurasi = dirname(__FILE__) . '/db_config.php';
if (!file_exists($file_konfigurasi)) {
	http_response_code(500);
	exit('Konfigurasi database belum ada. Salin config/db_config.sample.php menjadi config/db_config.php lalu isi datanya.');
}
$db = require $file_konfigurasi;

try {
	$koneksi = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);
} catch (Throwable $e) {
	http_response_code(500);
	error_log('Koneksi database gagal: ' . $e->getMessage());
	exit('Koneksi ke database gagal. Silakan hubungi administrator.');
}
if (!$koneksi) {
	http_response_code(500);
	error_log('Koneksi database gagal: ' . mysqli_connect_error());
	exit('Koneksi ke database gagal. Silakan hubungi administrator.');
}
// Catatan: charset koneksi sengaja dibiarkan seperti semula (tidak dipaksa
// ke utf8mb4), karena data lama tersimpan dengan koneksi default. Mengubahnya
// akan mengubah tampilan karakter non-ASCII yang sudah terlanjur tersimpan.

// Di server produksi, pesan error PHP tidak boleh tampil ke pengguna; cukup
// dicatat ke error_log. Saat mengembangkan/menelusuri masalah, ubah sementara
// display_errors di bawah ini menjadi '1'.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/**
 * Ambil satu kolom dari hasil query baris tunggal dengan aman.
 * ------------------------------------------------------------------
 * mysqli_fetch_array() mengembalikan NULL bila data tidak ditemukan
 * (mis. mahasiswa belum punya penasihat akademik, atau jatah SKS-nya
 * belum diatur). Mengakses $baris['kolom'] pada NULL memunculkan
 * "Warning: Trying to access array offset on null" di PHP 8.
 *
 * Fungsi ini mengembalikan $default (bawaan NULL, bukan string kosong,
 * supaya operasi hitung seperti pengurangan SKS tetap aman di PHP 8).
 */
function nilai($baris, $kolom, $default = null)
{
	if (is_array($baris) && array_key_exists($kolom, $baris) && $baris[$kolom] !== null) {
		return $baris[$kolom];
	}
	if ($baris instanceof ArrayAccess && isset($baris[$kolom])) {
		return $baris[$kolom];
	}
	return $default;
}

/**
 * Versi tampilan dari nilai(): mengembalikan teks pengganti bila data
 * belum ada, misalnya "Belum ditentukan".
 */
function nilai_teks($baris, $kolom, $default = '-')
{
	$v = nilai($baris, $kolom);
	if ($v === null || trim((string) $v) === '') {
		return $default;
	}
	return $v;
}

/**
 * Amankan nilai dari $_GET/$_POST sebelum disisipkan ke dalam query.
 * Mendukung array (mis. checkbox name="pilih[]") dan nilai kosong/null.
 * Dipakai di halaman-halaman lama yang masih menyusun SQL dengan string.
 */
function sql_aman($koneksi, $nilai)
{
	if (is_array($nilai)) {
		$hasil = array();
		foreach ($nilai as $k => $v) {
			$hasil[$k] = sql_aman($koneksi, $v);
		}
		return $hasil;
	}
	if ($nilai === null || is_bool($nilai)) {
		return '';
	}
	return mysqli_real_escape_string($koneksi, (string) $nilai);
}

date_default_timezone_set('Asia/Jakarta');
function time_since($original)
{
	$chunks = array(
		array(60 * 60 * 24 * 365, 'Tahun'),
		array(60 * 60 * 24 * 30, 'bulan'),
		array(60 * 60 * 24 * 7, 'minggu'),
		array(60 * 60 * 24, 'hari'),
		array(60 * 60, 'jam'),
		array(60, 'menit'),
	);

	$today = time();
	$since = $today - $original;

	if ($since > 604800)
	{
		$print = date("M jS" , $original);
		if ($since > 31536000)
		{
			$print .= ", " . date("Y", $original);
		}
		return $print;
	}
	for ($i = 0, $j = count($chunks); $i < $j; $i++)
	{
		$seconds = $chunks[$i][0];
		$name = $chunks[$i][1];

		if (($count = floor($since / $seconds)) != 0)
			break;
	}

	$print = ($count == 1) ? '1 ' . $name : "$count {$name}";
	return $print . ' yang lalu';
}


$ip      = $_SERVER['REMOTE_ADDR'];
$user_agent     =   $_SERVER['HTTP_USER_AGENT'];
function getOS() { 
	global $user_agent;
	$os_platform    =   "Unknown";
	$os_array       =   array(
		'/windows nt 10/i'     =>  'Windows 10',
		'/windows nt 6.3/i'     =>  'Windows 8.1',
		'/windows nt 6.2/i'     =>  'Windows 8',
		'/windows nt 6.1/i'     =>  'Windows 7',
		'/windows nt 6.0/i'     =>  'Windows Vista',
		'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
		'/windows nt 5.1/i'     =>  'Windows XP',
		'/windows xp/i'         =>  'Windows XP',
		'/windows nt 5.0/i'     =>  'Windows 2000',
		'/windows me/i'         =>  'Windows ME',
		'/win98/i'              =>  'Windows 98',
		'/win95/i'              =>  'Windows 95',
		'/win16/i'              =>  'Windows 3.11',
		'/macintosh|mac os x/i' =>  'Mac OS X',
		'/mac_powerpc/i'        =>  'Mac OS 9',
		'/linux/i'              =>  'Linux',
		'/ubuntu/i'             =>  'Ubuntu',
		'/iphone/i'             =>  'iPhone',
		'/ipod/i'               =>  'iPod',
		'/ipad/i'               =>  'iPad',
		'/android/i'            =>  'Android',
		'/blackberry/i'         =>  'BlackBerry',
		'/webos/i'              =>  'Mobile'
	);

	foreach ($os_array as $regex => $value) { 
		if (preg_match($regex, $user_agent)) {
			$os_platform    =   $value;
		}
	}   
	return $os_platform;
}

function getBrowser() {
	global $user_agent;
	$browser        =   "Unknown";
	$browser_array  =   array(
		'/msie/i'       =>  'Explorer',
		'/firefox/i'    =>  'Firefox',
		'/safari/i'     =>  'Safari',
		'/chrome/i'     =>  'Chrome',
		'/opera/i'      =>  'Opera',
		'/netscape/i'   =>  'Netscape',
		'/maxthon/i'    =>  'Maxthon',
		'/konqueror/i'  =>  'Konqueror',
		'/mobile/i'     =>  'Handheld'
	);

	foreach ($browser_array as $regex => $value) { 
		if (preg_match($regex, $user_agent)) {
			$browser    =   $value;
		}
	}
	return $browser;
}

$user_os        =   getOS();
$user_browser   =   getBrowser();


 // finally get the correct version number
$known = array('Version', $user_browser, 'other');
$pattern = '#(?<browser>' . join('|', $known) .
')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
if (!preg_match_all($pattern, $user_agent, $matches)) {
		        // we have no matching number just continue
}

$versi_ditemukan = isset($matches['version']) ? array_values(array_filter($matches['version'], 'strlen')) : array();
$i = isset($matches['browser']) ? count($matches['browser']) : 0;
if ($i > 1 && strripos($user_agent, "Version") >= strripos($user_agent, $user_browser) && isset($versi_ditemukan[1])) {
	// nama browser muncul sebelum kata "Version", ambil angka versi kedua
	$version = $versi_ditemukan[1];
} elseif (isset($versi_ditemukan[0])) {
	$version = $versi_ditemukan[0];
} else {
	$version = '';
}

function get_client_ip() {
	$ipaddress = '';
	if (getenv('HTTP_CLIENT_IP'))
		$ipaddress = getenv('HTTP_CLIENT_IP');
	else if(getenv('HTTP_X_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	else if(getenv('HTTP_X_FORWARDED'))
		$ipaddress = getenv('HTTP_X_FORWARDED');
	else if(getenv('HTTP_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_FORWARDED_FOR');
	else if(getenv('HTTP_FORWARDED'))
		$ipaddress = getenv('HTTP_FORWARDED');
	else if(getenv('REMOTE_ADDR'))
		$ipaddress = getenv('REMOTE_ADDR');
	else
		$ipaddress = 'IP tidak dikenali';
	return $ipaddress;
}

//menampilkan ip address menggunakan function $_SERVER
function get_client_ip_2() {
	$ipaddress = '';
	if (isset($_SERVER['HTTP_CLIENT_IP']))
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_X_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if(isset($_SERVER['HTTP_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if(isset($_SERVER['REMOTE_ADDR']))
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'IP tidak dikenali';
	return $ipaddress;
}
?>