<?php
require_once __DIR__ . '/academic.php';

function siakad_hapus_aman($db,$table,$key,$value) {
    // Nama tabel/kolom hanya berasal dari daftar tetap pada dispatcher.
    mysqli_begin_transaction($db);
    try {
        $row=siakad_baris($db,"SELECT * FROM `$table` WHERE `$key`=? FOR UPDATE",'s',array($value));
        if (!$row) throw new DomainException('Data tidak ditemukan.');
        if ($table==='prodi_has_mhs') {
            foreach (array('krs_mhs','khs_mhs') as $child) if (siakad_baris($db,"SELECT nim_npm FROM $child WHERE nim_npm=? AND kode_prodi=? LIMIT 1 FOR UPDATE",'ss',array($row['nim_npm'],$row['kode_prodi']))) throw new DomainException('Mahasiswa sudah memiliki riwayat akademik pada program studi ini.');
        }
        if ($table==='prodi_has_dosen' && siakad_baris($db,'SELECT id_jadwal FROM jadwal_mengajar WHERE nip=? AND kode_prodi=? LIMIT 1 FOR UPDATE','ss',array($row['nip'],$row['kode_prodi']))) throw new DomainException('Dosen sudah digunakan pada jadwal program studi.');
        if ($table==='prodi_has_matkul' && siakad_baris($db,'SELECT id_jadwal FROM jadwal_mengajar WHERE kode_mk=? AND kode_prodi=? LIMIT 1 FOR UPDATE','ss',array($row['kode_matkul'],$row['kode_prodi']))) throw new DomainException('Mata kuliah sudah digunakan pada jadwal program studi.');
        if ($table==='tbl_grade' && siakad_baris($db,'SELECT nim_npm FROM khs_mhs WHERE grade=? LIMIT 1 FOR UPDATE','s',array($row['grade']))) throw new DomainException('Grade sudah digunakan pada nilai mahasiswa.');
        // Juga melindungi database lama yang masih menggunakan ON DELETE CASCADE.
        $refs=siakad_semua($db,'SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME=? AND REFERENCED_COLUMN_NAME=?','ss',array($table,$key));
        foreach ($refs as $ref) {
            $child=$ref['TABLE_NAME']; $column=$ref['COLUMN_NAME'];
            if (!preg_match('/^[a-zA-Z0-9_]+$/D',$child.$column)) throw new RuntimeException('Nama relasi tidak valid.');
            if (siakad_baris($db,"SELECT `$column` FROM `$child` WHERE `$column`=? LIMIT 1 FOR UPDATE",'s',array($value))) throw new DomainException('Data masih digunakan oleh '.$child.'. Hapus hanya data yang belum dipakai; riwayat akademik harus dipertahankan.');
        }
        siakad_ubah($db,"DELETE FROM `$table` WHERE `$key`=?",'s',array($value));
        if (in_array($table,array('mahasiswa','dosen'),true)) {
            siakad_ubah($db,'DELETE FROM user WHERE username=? AND level=?','ss',array($value,$table==='mahasiswa'?'mhs':'dosen'));
        }
        mysqli_commit($db);
    } catch (Throwable $e) { mysqli_rollback($db); throw $e; }
}

function siakad_hapus_request($db,$user,$page) {
    if (!isset($_POST['aksi'])) return;
    if (!in_array($_POST['aksi'],array('hapus','del'),true)) throw new DomainException('Aksi tidak valid.');
    $map=array(
        'mhs'=>array('mahasiswa','nim_npm','nim_npm'),
        'dosen'=>array('dosen','nip','nip'),
        'mata_kuliah'=>array('mata_kuliah','kode_matkul','kode_matkul'),
        'fakultas'=>array('tbl_fakultas','kode_fakultas','kode_fakultas'),
        'jurusan'=>array('prodi','kode_prodi','kode_prodi'),
        'ruangan'=>array('tbl_ruangan','kode_ruangan','kode_ruangan'),
        'grade'=>array('tbl_grade','id_grade','id_grade'),
        'thn_akademik'=>array('thn_akademik','id_thn_akademik','id'),
        'fak-has-jur'=>array('fakultas_has_jurusan','id','id'),
        'jurusan_has_mhs'=>array('prodi_has_mhs','id','id'),
        'jurusan_has_dosen'=>array('prodi_has_dosen','id','id'),
        'jurusan_has_matkul'=>array('prodi_has_matkul','id','id'),
        'buat_jadwal'=>array('jadwal_mengajar','id_jadwal','id'),
        'akun_admin'=>array('user','id_user','id_user'),
        'akun_dosen'=>array('user','id_user','id_user'),
        'akun_jurusan'=>array('user','id_user','id_user'),
        'akun_mhs'=>array('user','id_user','id_user')
    );
    $url=$page;
    if (isset($_GET['qwe']) && ctype_digit((string)$_GET['qwe'])) $url.='?qwe='.(int)$_GET['qwe'];
    if ($page==='mhs_krs' && isset($_GET['qaz']) && is_string($_GET['qaz'])) $url.=(strpos($url,'?')===false?'?':'&').'qaz='.rawurlencode($_GET['qaz']);
    if ($page==='ruangan') $url.='?kode_fakultas='.rawurlencode((string)($_GET['kode_fakultas']??''));
    if (in_array($page,array('krs','mhs_krs'),true)) {
        $id=filter_var($_POST['id_krs']??null,FILTER_VALIDATE_INT);
        if (!$id) throw new DomainException('ID KRS tidak valid.');
        siakad_hapus_krs($db,$user,$id);
    } elseif ($page==='dosen_has_mhs') {
        $nim=$_POST['nim_npm']??'';
        $nip=$user['level']==='dosen'?$user['username']:($_GET['nip']??'');
        if (!siakad_mahasiswa_diizinkan($db,$user,$nim)) throw new DomainException('Mahasiswa berada di luar lingkup Anda.');
        siakad_ubah($db,'DELETE FROM mhs_has_pa WHERE nim_npm=? AND nip=?','ss',array($nim,$nip));
        $url.='?nip='.rawurlencode($nip);
    } else {
        if (!isset($map[$page])) throw new DomainException('Halaman ini tidak menyediakan penghapusan.');
        list($table,$key,$field)=$map[$page];
        $value=$_POST[$field]??null;
        // Beberapa tautan lama grade menggunakan parameter id.
        if ($page==='grade' && $value===null) $value=$_POST['id']??null;
        if (!is_string($value) || $value==='') throw new DomainException('ID data tidak valid.');
        $row=siakad_baris($db,"SELECT * FROM `$table` WHERE `$key`=?",'s',array($value));
        if (!$row) throw new DomainException('Data tidak ditemukan.');
        if ($user['level']==='Jurusan/Prodi' && ($row['kode_prodi']??null)!==$user['kode_prodi']) throw new DomainException('Data berada di luar program studi Anda.');
        if ($table==='user') {
            $roles=array('akun_admin'=>'admin','akun_dosen'=>'dosen','akun_jurusan'=>'Jurusan/Prodi','akun_mhs'=>'mhs');
            if ($row['level']!==$roles[$page] || (int)$row['id_user']===(int)$user['id_user']) throw new DomainException('Akun tidak dapat dihapus melalui halaman ini.');
        }
        if ($table==='prodi_has_mhs') {
            foreach (array('krs_mhs','khs_mhs') as $child) if (siakad_baris($db,"SELECT nim_npm FROM $child WHERE nim_npm=? AND kode_prodi=? LIMIT 1",'ss',array($row['nim_npm'],$row['kode_prodi']))) throw new DomainException('Mahasiswa sudah memiliki riwayat akademik pada program studi ini.');
        }
        if ($table==='prodi_has_dosen' && siakad_baris($db,'SELECT id_jadwal FROM jadwal_mengajar WHERE nip=? AND kode_prodi=? LIMIT 1','ss',array($row['nip'],$row['kode_prodi']))) throw new DomainException('Dosen sudah digunakan pada jadwal program studi.');
        if ($table==='prodi_has_matkul' && siakad_baris($db,'SELECT id_jadwal FROM jadwal_mengajar WHERE kode_mk=? AND kode_prodi=? LIMIT 1','ss',array($row['kode_matkul'],$row['kode_prodi']))) throw new DomainException('Mata kuliah sudah digunakan pada jadwal program studi.');
        // Grade historis tersimpan sebagai teks, tanpa FK.
        if ($table==='tbl_grade' && siakad_baris($db,'SELECT nim_npm FROM khs_mhs WHERE grade=? LIMIT 1','s',array($row['grade']))) throw new DomainException('Grade sudah digunakan pada nilai mahasiswa.');
        siakad_hapus_aman($db,$table,$key,$value);
    }
    siakad_alihkan($url);
}
