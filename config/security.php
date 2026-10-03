<?php
function siakad_tolak($pesan, $status=403) {
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    echo '<p>'.htmlspecialchars($pesan,ENT_QUOTES,'UTF-8').'</p><p><a href="javascript:history.back()">Kembali</a></p>';
    exit;
}
function siakad_stmt($db,$sql,$types='',$params=array()) {
    $stmt=mysqli_prepare($db,$sql);
    if (!$stmt) throw new RuntimeException('Gagal menyiapkan query.');
    if ($types!=='') mysqli_stmt_bind_param($stmt,$types,...$params);
    if (!mysqli_stmt_execute($stmt)) throw new RuntimeException('Gagal menyimpan data.');
    return $stmt;
}
function siakad_baris($db,$sql,$types='',$params=array()) {
    $stmt=siakad_stmt($db,$sql,$types,$params);
    $row=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}
function siakad_semua($db,$sql,$types='',$params=array()) {
    $stmt=siakad_stmt($db,$sql,$types,$params);
    $rows=mysqli_fetch_all(mysqli_stmt_get_result($stmt),MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}
function siakad_ubah($db,$sql,$types='',$params=array()) {
    $stmt=siakad_stmt($db,$sql,$types,$params);
    $count=mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $count;
}
function siakad_csrf_token() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function siakad_csrf_field() { echo '<input type="hidden" name="csrf_token" value="'.siakad_csrf_token().'">'; }
function siakad_csrf_valid($token) { return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'],$token); }
function siakad_password_cocok($input,$stored) {
    if (!is_string($input) || !is_string($stored) || $input==='') return false;
    if (password_get_info($stored)['algo']!==null) return password_verify($input,$stored);
    // MD5 lama hanya diterima dengan password asal, bukan dengan nilai hash.
    if (preg_match('/^[a-f0-9]{32}$/i',$stored)) return hash_equals(strtolower($stored),md5($input));
    return hash_equals($stored,$input);
}
function siakad_auth_version($hash) {
    if (empty($_SESSION['auth_secret'])) $_SESSION['auth_secret']=bin2hex(random_bytes(32));
    return hash_hmac('sha256',$hash,$_SESSION['auth_secret']);
}
function siakad_level_halaman($page) {
    $admin=array('akun_admin','akun_jurusan','akun_dosen','akun_mhs','fakultas','jurusan','fak-has-jur','dosen','mhs','mata_kuliah','thn_akademik','grade','pengaturan','ruangan','search_dosen','search_mhs','search_matkul','search_fakultas','search_jurusan','search_fak_has_jur');
    $prodi=array('jurusan_has_mhs','jurusan_has_dosen','jurusan_has_matkul','add_mhs_jurusan','add_dosen_jurusan','add_matkul_jurusan','get_add_mhs','get_add_dosen','get_add_matkul','buat_jadwal','input_nilai','sks_mhs','pengaturan_krs','krs_mhs','khs_mhs','transkip_mhs','rekap_jadwal','mhs_krs','mhs_khs');
    if (in_array($page,$admin,true)) return array('admin');
    if (in_array($page,$prodi,true)) return array('Jurusan/Prodi');
    if (in_array($page,array('get_input_nilai','get_absen','get_daftar_mahasiswa','dosen_has_mhs','detail_mhs'),true)) return array('admin','Jurusan/Prodi','dosen');
    if (in_array($page,array('jadwal_mengajar','input_nilai_dosen'),true)) return array('dosen');
    if (in_array($page,array('ambil_jadwal','tambah_krs','krs','khs','transkip','jadwal_kuliah'),true)) return array('mhs');
    if ($page==='dashboard') return array('admin','Jurusan/Prodi','dosen','mhs');
    if (strpos($page,'cetak/')===0) {
        if (in_array($page,array('cetak/krs','cetak/khs','cetak/transkip','cetak/jadwalkuliah'),true)) return array('mhs','Jurusan/Prodi');
        return array('admin','Jurusan/Prodi');
    }
    return array();
}
function siakad_mahasiswa_diizinkan($db,$user,$nim) {
    if (!is_string($nim) || $nim==='') return false;
    if ($user['level']==='admin') return true;
    if ($user['level']==='mhs') return hash_equals($user['username'],$nim);
    if ($user['level']==='Jurusan/Prodi') return (bool)siakad_baris($db,'SELECT id FROM prodi_has_mhs WHERE nim_npm=? AND kode_prodi=?','ss',array($nim,$user['kode_prodi']));
    return (bool)siakad_baris($db,'SELECT nim_npm FROM mhs_has_pa WHERE nim_npm=? AND nip=?','ss',array($nim,$user['username']));
}
function siakad_jadwal_diizinkan($user,$jadwal) {
    if (!$jadwal) return false;
    if ($user['level']==='admin') return true;
    if ($user['level']==='Jurusan/Prodi') return $jadwal['kode_prodi']===$user['kode_prodi'];
    return $user['level']==='dosen' && $jadwal['nip']===$user['username'];
}
function siakad_otorisasi_request($db,$user,$levels=null) {
    $path=str_replace('\\','/',$_SERVER['SCRIPT_FILENAME']??'');
    $page=pathinfo($path,PATHINFO_FILENAME);
    if (basename(dirname($path))==='cetak') $page='cetak/'.$page;
    if (!in_array($user['level'],$levels??siakad_level_halaman($page),true)) siakad_tolak('Anda tidak berhak membuka halaman ini.');
    $post=($_SERVER['REQUEST_METHOD']??'GET')==='POST';
    $search=strpos($page,'search_')===0 || strpos($page,'get_add_')===0;
    if ($post && !$search && !siakad_csrf_valid($_POST['csrf_token']??null)) siakad_tolak('Token keamanan tidak valid. Muat ulang formulir.');
    if ($user['level']==='Jurusan/Prodi' && $user['kode_prodi']==='') siakad_tolak('Akun belum memiliki program studi. Hubungi administrator.');
    if (isset($_GET['aksi']) && in_array($_GET['aksi'],array('hapus','del'),true)) siakad_tolak('Penghapusan harus menggunakan POST.',405);
    foreach (array('nim_npm','qaz') as $key) {
        $nim=$_POST[$key]??$_GET[$key]??null;
        if ($nim!==null && !in_array($page,array('get_input_nilai','get_absen','get_daftar_mahasiswa'),true)) foreach ((array)$nim as $value) {
            if (!siakad_mahasiswa_diizinkan($db,$user,$value)) siakad_tolak('Mahasiswa berada di luar lingkup akun Anda.');
        }
    }
    if ($user['level']==='Jurusan/Prodi' && isset($_POST['kode_prodi']) && $_POST['kode_prodi']!==$user['kode_prodi']) siakad_tolak('Program studi tidak sesuai.');
    if (in_array($page,array('get_input_nilai','get_absen','get_daftar_mahasiswa'),true)) {
        $id=filter_var($_GET['qwe']??null,FILTER_VALIDATE_INT);
        $jadwal=$id?siakad_baris($db,'SELECT * FROM jadwal_mengajar WHERE id_jadwal=?','i',array($id)):null;
        if (!siakad_jadwal_diizinkan($user,$jadwal)) siakad_tolak('Jadwal berada di luar lingkup akun Anda.');
    }
    if ($page==='dashboard') foreach (array('simpan_mhs'=>'mhs','ubahfotomhs'=>'mhs','simpan_dosen'=>'dosen','ubahfotodosen'=>'dosen') as $action=>$role) {
        if (isset($_POST[$action]) && $user['level']!==$role) siakad_tolak('Perubahan profil tidak sesuai jenis akun.');
    }
    if (in_array($page,array('add_mhs_jurusan','add_dosen_jurusan','add_matkul_jurusan'),true) && isset($_POST['pilih'])) {
        $map=array('add_mhs_jurusan'=>array('prodi_has_mhs','nim_npm'),'add_dosen_jurusan'=>array('prodi_has_dosen','nip'),'add_matkul_jurusan'=>array('prodi_has_matkul','kode_matkul'));
        list($table,$key)=$map[$page];
        if (!is_array($_POST['pilih']) || !$_POST['pilih']) siakad_tolak('Pilih minimal satu data.',422);
        foreach ($_POST['pilih'] as $id) {
            if (!is_string($id)) siakad_tolak('Pilihan tidak valid.',422);
            if (siakad_baris($db,"SELECT id FROM $table WHERE $key=? AND kode_prodi<>?",'ss',array($id,$user['kode_prodi']))) siakad_tolak('Data sudah terdaftar pada program studi lain.');
        }
    }
    if ($page==='sks_mhs' && isset($_POST['simpan_sks'])) {
        if (!is_array($_POST['Pilih']??null) || !is_array($_POST['sks']??null) || count($_POST['Pilih'])!==count($_POST['sks'])) siakad_tolak('Daftar SKS tidak valid.',422);
        foreach ($_POST['Pilih'] as $index=>$nim) {
            if (!siakad_mahasiswa_diizinkan($db,$user,$nim)) siakad_tolak('Mahasiswa berada di luar program studi.');
            if (filter_var($_POST['sks'][$index],FILTER_VALIDATE_INT,array('options'=>array('min_range'=>0,'max_range'=>30)))===false) siakad_tolak('Batas SKS harus 0 sampai 30.',422);
        }
    }
    if ($page==='dosen_has_mhs') {
        $nip=$user['level']==='dosen'?$user['username']:($_GET['nip']??'');
        if ($user['level']==='Jurusan/Prodi' && !siakad_baris($db,'SELECT id FROM prodi_has_dosen WHERE nip=? AND kode_prodi=?','ss',array($nip,$user['kode_prodi']))) siakad_tolak('Dosen berada di luar program studi.');
        foreach ((array)($_POST['pilih']??array()) as $nim) {
            if (!is_string($nim) || !siakad_baris($db,'SELECT pm.id FROM prodi_has_mhs pm JOIN prodi_has_dosen pd ON pd.kode_prodi=pm.kode_prodi WHERE pm.nim_npm=? AND pd.nip=?','ss',array($nim,$nip))) siakad_tolak('Mahasiswa dan dosen harus berada pada program studi yang sama.');
        }
    }
    if ($post && isset($_POST['aksi'])) {
        require_once __DIR__.'/deletion.php';
        siakad_form_proses(function() use ($db,$user,$page) { siakad_hapus_request($db,$user,$page); },$page);
    }
}
