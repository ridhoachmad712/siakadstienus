<?php
require_once __DIR__.'/auth.php';
function siakad_login($db,$username,$password,$level) {
    if (!is_string($username) || !is_string($password) || !is_string($level) || $username==='' || $password==='' || !in_array($level,array('admin','Jurusan/Prodi','dosen','mhs'),true)) return false;
    $users=siakad_semua($db,'SELECT * FROM user WHERE username=? AND level=?','ss',array($username,$level));
    if (count($users)!==1 || !siakad_password_cocok($password,$users[0]['password'])) return false;
    $user=$users[0];
    $stored=$user['password'];
    if (password_get_info($stored)['algo']===null || password_needs_rehash($stored,PASSWORD_DEFAULT)) {
        $hash=password_hash($password,PASSWORD_DEFAULT);
        // Compare-and-swap menjaga reset password bersamaan tidak tertimpa.
        if (siakad_ubah($db,'UPDATE user SET password=? WHERE id_user=? AND password=?','sis',array($hash,$user['id_user'],$stored))!==1) return false;
        $stored=$hash;
    }
    session_regenerate_id(true);
    $_SESSION=array('id_user'=>(int)$user['id_user'],'username'=>$user['username'],'level'=>$user['level'],'kode_prodi'=>$user['kode_prodi'],'login'=>true);
    $_SESSION['auth_version']=siakad_auth_version($stored);
    siakad_csrf_token();
    $ip=substr($_SERVER['REMOTE_ADDR']??'',0,20);
    $os=substr(function_exists('getOS')?getOS():'',0,50);
    $browser=substr(function_exists('getBrowser')?getBrowser():'',0,50);
    siakad_ubah($db,'UPDATE user SET ip=?,os=?,browser=?,tgl=?,waktu=? WHERE id_user=?','sssssi',array($ip,$os,$browser,date('Y-m-d'),date('H:i:s'),$user['id_user']));
    return true;
}
function siakad_ganti_password($db,$input,$confirm) {
    if (!is_string($input) || !is_string($confirm) || $input!==$confirm) throw new DomainException('Konfirmasi password tidak sesuai.');
    if (strlen($input)<8 || strlen($input)>72) throw new DomainException('Password baru harus 8 sampai 72 karakter.');
    $hash=password_hash($input,PASSWORD_DEFAULT);
    siakad_ubah($db,'UPDATE user SET password=? WHERE id_user=?','si',array($hash,$_SESSION['id_user']));
    session_regenerate_id(true);
    $_SESSION['auth_version']=siakad_auth_version($hash);
}
function siakad_buat_akun($db,$username,$password,$level,$prodi='') {
    if (!is_string($username) || !preg_match('/^[a-zA-Z0-9_.@-]{1,80}$/D',$username)) throw new DomainException('Username harus 1–80 karakter huruf, angka, titik, @, garis bawah, atau tanda minus.');
    if (!is_string($password) || $password==='' || strlen($password)>72) throw new DomainException('Password wajib diisi dan maksimal 72 karakter.');
    if (in_array($level,array('admin','Jurusan/Prodi'),true) && strlen($password)<8) throw new DomainException('Password admin/program studi minimal 8 karakter.');
    if (!in_array($level,array('admin','Jurusan/Prodi','dosen','mhs'),true)) throw new DomainException('Jenis akun tidak valid.');
    if (siakad_baris($db,'SELECT id_user FROM user WHERE username=? AND level=?','ss',array($username,$level))) throw new DomainException('Akun sudah tersedia.');
    if ($level==='Jurusan/Prodi') {
        if (!is_string($prodi) || !siakad_baris($db,'SELECT kode_prodi FROM prodi WHERE kode_prodi=?','s',array($prodi))) throw new DomainException('Program studi tidak valid.');
        if (siakad_baris($db,"SELECT id_user FROM user WHERE kode_prodi=? AND level='Jurusan/Prodi'",'s',array($prodi))) throw new DomainException('Program studi sudah memiliki akun.');
    }
    if ($level==='mhs') {
        $rows=siakad_semua($db,'SELECT pm.kode_prodi FROM prodi_has_mhs pm JOIN mahasiswa m ON m.nim_npm=pm.nim_npm WHERE pm.nim_npm=?','s',array($username));
        if (count($rows)!==1) throw new DomainException('Mahasiswa harus memiliki satu program studi sebelum dibuatkan akun.');
        $prodi=$rows[0]['kode_prodi'];
    }
    if ($level==='dosen' && !siakad_baris($db,'SELECT nip FROM dosen WHERE nip=?','s',array($username))) throw new DomainException('Dosen tidak ditemukan.');
    siakad_ubah($db,'INSERT INTO user (username,password,kode_prodi,level,ip,os,browser,tgl,waktu) VALUES (?,?,?,?,?,?,?,CURDATE(),CURTIME())','sssssss',array($username,password_hash($password,PASSWORD_DEFAULT),$prodi,$level,'','',''));
}
function siakad_buat_akun_batch($db,$pilihan,$level) {
    if (!is_array($pilihan) || !$pilihan || count($pilihan)>1000) throw new DomainException('Pilih minimal satu akun.');
    mysqli_begin_transaction($db);
    try {
        foreach ($pilihan as $username) siakad_buat_akun($db,$username,$username,$level);
        mysqli_commit($db);
    } catch (Throwable $e) { mysqli_rollback($db); throw $e; }
}
