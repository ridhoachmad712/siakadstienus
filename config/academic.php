<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/krs_policy.php';

function siakad_periode_terbuka($periode,$tanggal) {
    // 0000-00-00 sebagai tanggal mulai adalah konvensi lama: selalu terbuka.
    if (!$periode) return false;
    if ($periode['dari_tgl']==='0000-00-00') return true;
    return $periode['dari_tgl']<=$tanggal && $tanggal<=$periode['sampai_tgl'];
}
function siakad_wajib_periode($db,$table,$tahun) {
    if (!in_array($table,array('jadwal_penawaran','jadwal_input_nilai'),true)) throw new InvalidArgumentException('Jenis periode tidak valid.');
    $rows=siakad_semua($db,"SELECT dari_tgl,sampai_tgl FROM $table WHERE id_thn_akademik=? FOR UPDATE",'i',array($tahun));
    if (count($rows)!==1 || !siakad_periode_terbuka($rows[0],date('Y-m-d'))) throw new DomainException('Periode pengisian belum dibuka atau sudah ditutup.');
}
function siakad_daftar_id($values) {
    if (!is_array($values) || !$values || count($values)>100) throw new DomainException('Pilih minimal satu mata kuliah (maksimal 100).');
    $ids=array();
    foreach ($values as $value) {
        if (!is_scalar($value) || ($id=filter_var($value,FILTER_VALIDATE_INT,array('options'=>array('min_range'=>1))))===false) throw new DomainException('ID jadwal tidak valid.');
        if (in_array($id,$ids,true)) throw new DomainException('Pilihan mata kuliah ganda.');
        $ids[]=$id;
    }
    sort($ids,SORT_NUMERIC);
    return $ids;
}
function siakad_jam_valid($jam) { return is_string($jam) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/D',$jam); }
function siakad_waktu_bentrok($awal,$akhir,$awal_lama,$akhir_lama) {
    return strtotime($awal)<strtotime($akhir_lama) && strtotime($akhir)>strtotime($awal_lama);
}
function siakad_ambil_krs($db,$user,$tahun,$pilihan) {
    if ($user['level']!=='mhs') throw new DomainException('Hanya mahasiswa yang dapat mengambil KRS.');
    $ids=siakad_daftar_id($pilihan);
    if (!filter_var($tahun,FILTER_VALIDATE_INT,array('options'=>array('min_range'=>1)))) throw new DomainException('Tahun akademik tidak valid.');
    mysqli_begin_transaction($db);
    try {
        // Satu mahasiswa hanya dapat memproses satu perubahan KRS sekaligus.
        if (!siakad_baris($db,'SELECT nim_npm FROM mahasiswa WHERE nim_npm=? FOR UPDATE','s',array($user['username']))) throw new DomainException('Data mahasiswa tidak ditemukan.');
        if (!siakad_baris($db,'SELECT id FROM prodi_has_mhs WHERE nim_npm=? AND kode_prodi=? FOR UPDATE','ss',array($user['username'],$user['kode_prodi']))) throw new DomainException('Mahasiswa belum terdaftar pada program studi.');
        siakad_wajib_periode($db,'jadwal_penawaran',$tahun);
        $context=siakad_krs_konteks($db,$user['username'],$user['kode_prodi'],$tahun);
        if (!$context['valid'] || !$context['active']) throw new DomainException($context['message']);
        $limits=siakad_semua($db,'SELECT sks FROM pengaturan_sks_mhs WHERE nim_npm=? AND id_thn_akademik=? FOR UPDATE','si',array($user['username'],$tahun));
        if (count($limits)!==1) throw new DomainException('Batas SKS belum diatur atau memiliki data ganda. Hubungi program studi.');
        $existing=siakad_semua($db,'SELECT j.*,m.sks,m.kode_matkul FROM krs_mhs k JOIN jadwal_mengajar j ON j.id_jadwal=k.id_jadwal JOIN mata_kuliah m ON m.kode_matkul=j.kode_mk WHERE k.nim_npm=? AND k.id_thn_akademik=?','si',array($user['username'],$tahun));
        $total=array_sum(array_column($existing,'sks'));
        $selected=array();
        foreach ($ids as $id) {
            $row=siakad_baris($db,'SELECT j.*,m.sks,m.kode_matkul,m.semester FROM jadwal_mengajar j JOIN mata_kuliah m ON m.kode_matkul=j.kode_mk WHERE j.id_jadwal=? FOR UPDATE','i',array($id));
            if (!$row || $row['kode_prodi']!==$user['kode_prodi'] || (int)$row['id_thn_akademik']!==(int)$tahun) throw new DomainException('Mata kuliah tidak sesuai program studi atau tahun akademik.');
            if (!siakad_baris($db,'SELECT id FROM prodi_has_matkul WHERE kode_prodi=? AND kode_matkul=?','ss',[$user['kode_prodi'],$row['kode_mk']]) || siakad_krs_jenis_pilihan($context,$row)===null) throw new DomainException('Mata kuliah tidak sesuai semester Anda dan belum memiliki izin program studi.');
            foreach (array_merge($existing,$selected) as $other) {
                if ($other['kode_mk']===$row['kode_mk']) throw new DomainException('Mata kuliah sudah diambil pada semester ini.');
                if ($other['id_hari']==$row['id_hari'] && siakad_waktu_bentrok($row['mulai_jam'],$row['sampai_jam'],$other['mulai_jam'],$other['sampai_jam'])) throw new DomainException('Jadwal mata kuliah bertabrakan.');
            }
            if (siakad_baris($db,'SELECT nim_npm FROM khs_mhs WHERE nim_npm=? AND id_jadwal=? AND id_thn_akademik=?','sii',array($user['username'],$id,$tahun))) throw new DomainException('Data KHS sudah ada. Hubungi program studi.');
            $total+=(int)$row['sks'];
            $selected[]=$row;
        }
        if ($total>(int)$limits[0]['sks']) throw new DomainException('Jumlah SKS melebihi batas yang ditetapkan.');
        foreach ($selected as $row) {
            $args=array($user['kode_prodi'],(int)$row['id_jadwal'],$user['username'],(int)$tahun);
            siakad_ubah($db,'INSERT INTO krs_mhs (kode_prodi,id_jadwal,nim_npm,id_thn_akademik) VALUES (?,?,?,?)','sisi',$args);
            siakad_ubah($db,"INSERT INTO khs_mhs (kode_prodi,id_jadwal,nim_npm,id_thn_akademik,nilai_tgs,nilai_uts,nilai_uas,nilai_akhir,bobot,grade) VALUES (?,?,?,?,'0','0','0','-','-','-')",'sisi',$args);
        }
        mysqli_commit($db);
    } catch (Throwable $e) { mysqli_rollback($db); throw $e; }
}
function siakad_hapus_krs($db,$user,$id) {
    mysqli_begin_transaction($db);
    try {
        $row=siakad_baris($db,'SELECT * FROM krs_mhs WHERE id_krs=? FOR UPDATE','i',array($id));
        if (!$row || !siakad_mahasiswa_diizinkan($db,$user,$row['nim_npm']) || !in_array($user['level'],array('mhs','Jurusan/Prodi'),true)) throw new DomainException('KRS tidak ditemukan atau bukan milik Anda.');
        if ($row['kode_prodi']!==$user['kode_prodi']) throw new DomainException('KRS berada di luar program studi.');
        siakad_baris($db,'SELECT nim_npm FROM mahasiswa WHERE nim_npm=? FOR UPDATE','s',array($row['nim_npm']));
        siakad_wajib_periode($db,'jadwal_penawaran',$row['id_thn_akademik']);
        $args=array($row['nim_npm'],$row['id_jadwal'],$row['id_thn_akademik'],$row['kode_prodi']);
        $khs=siakad_semua($db,'SELECT grade,nilai_akhir FROM khs_mhs WHERE nim_npm=? AND id_jadwal=? AND id_thn_akademik=? AND kode_prodi=? FOR UPDATE','siis',$args);
        foreach ($khs as $nilai) if ($nilai['grade']!=='-' || $nilai['nilai_akhir']!=='-') throw new DomainException('KRS sudah memiliki nilai dan tidak dapat dihapus.');
        siakad_ubah($db,'DELETE FROM khs_mhs WHERE nim_npm=? AND id_jadwal=? AND id_thn_akademik=? AND kode_prodi=?','siis',$args);
        siakad_ubah($db,'DELETE FROM krs_mhs WHERE id_krs=?','i',array($id));
        mysqli_commit($db);
    } catch (Throwable $e) { mysqli_rollback($db); throw $e; }
}
function siakad_grade($nilai,$grades) {
    if (!is_scalar($nilai) || !is_numeric($nilai) || !is_finite((float)$nilai) || $nilai<0 || $nilai>100) throw new DomainException('Nilai akhir harus berupa angka 0 sampai 100.');
    $match=null;
    foreach ($grades as $grade) {
        if (!is_numeric($grade['nilai_awal']) || !is_numeric($grade['nilai_akhir']) || !is_numeric($grade['bobot']) || (float)$grade['bobot']<0 || (float)$grade['bobot']>4) throw new DomainException('Konfigurasi grade tidak valid.');
        // Rentang lama biasanya 80-100,65-79: batas atas berlaku hingga sebelum integer berikutnya.
        if ((float)$nilai>=(float)$grade['nilai_awal'] && ((float)$grade['nilai_akhir']>=100 || (float)$nilai<(float)$grade['nilai_akhir']+1)) {
            if ($match!==null && (float)$match['nilai_awal']===(float)$grade['nilai_awal']) throw new DomainException('Batas awal grade ganda. Hubungi administrator.');
            // Dump lama bertumpang tindih pada 40 dan 50; batas awal tertinggi menang.
            if ($match===null || (float)$grade['nilai_awal']>(float)$match['nilai_awal']) $match=$grade;
        }
    }
    if (!$match) throw new DomainException('Nilai tidak memiliki grade yang sesuai. Hubungi administrator.');
    return $match;
}
function siakad_simpan_nilai($db,$user,$id,$nims,$values) {
    if (!is_array($nims) || !is_array($values) || !$nims || array_keys($nims)!==array_keys($values) || count($nims)>1000) throw new DomainException('Daftar nilai tidak valid.');
    foreach ($nims as $nim) if (!is_string($nim) || $nim==='') throw new DomainException('NIM tidak valid.');
    if (count(array_unique($nims,SORT_STRING))!==count($nims)) throw new DomainException('Mahasiswa tercantum lebih dari sekali.');
    mysqli_begin_transaction($db);
    try {
        $jadwal=siakad_baris($db,'SELECT * FROM jadwal_mengajar WHERE id_jadwal=? FOR UPDATE','i',array($id));
        if (!siakad_jadwal_diizinkan($user,$jadwal)) throw new DomainException('Anda tidak berhak mengubah nilai kelas ini.');
        siakad_wajib_periode($db,'jadwal_input_nilai',$jadwal['id_thn_akademik']);
        $grades=siakad_semua($db,'SELECT * FROM tbl_grade FOR UPDATE');
        foreach ($nims as $index=>$nim) {
            if (!is_string($nim) || !array_key_exists($index,$values)) throw new DomainException('Daftar nilai tidak valid.');
            $grade=siakad_grade($values[$index],$grades);
            $args=array($nim,$id,$jadwal['id_thn_akademik'],$jadwal['kode_prodi']);
            if (!siakad_baris($db,'SELECT id_krs FROM krs_mhs WHERE nim_npm=? AND id_jadwal=? AND id_thn_akademik=? AND kode_prodi=?','siis',$args)) throw new DomainException('Mahasiswa tidak terdaftar pada KRS kelas ini.');
            $rows=siakad_semua($db,'SELECT nim_npm FROM khs_mhs WHERE nim_npm=? AND id_jadwal=? AND id_thn_akademik=? AND kode_prodi=? FOR UPDATE','siis',$args);
            if (count($rows)!==1) throw new DomainException('Data KHS hilang atau ganda. Hubungi administrator.');
            // Kebijakan kampus: dosen memasukkan nilai akhir langsung, disimpan pada kolom UAS lama.
            siakad_ubah($db,'UPDATE khs_mhs SET nilai_uas=?,nilai_akhir=?,grade=?,bobot=? WHERE nim_npm=? AND id_jadwal=? AND id_thn_akademik=? AND kode_prodi=?','ddsssiis',array((float)$values[$index],(float)$values[$index],$grade['grade'],$grade['bobot'],$nim,$id,$jadwal['id_thn_akademik'],$jadwal['kode_prodi']));
        }
        mysqli_commit($db);
    } catch (Throwable $e) { mysqli_rollback($db); throw $e; }
}
function siakad_buat_jadwal($db,$user,$input) {
    if ($user['level']!=='Jurusan/Prodi') throw new DomainException('Penjadwalan hanya untuk program studi.');
    foreach (array('nip','kode_mk','kode_ruangan','id_hari','mulai_jam','sampai_jam','id_thn_akademik') as $key) if (!isset($input[$key]) || !is_string($input[$key]) || $input[$key]==='') throw new DomainException('Lengkapi data jadwal.');
    if (!siakad_jam_valid($input['mulai_jam']) || !siakad_jam_valid($input['sampai_jam']) || strtotime($input['mulai_jam'])>=strtotime($input['sampai_jam'])) throw new DomainException('Jam mulai harus sebelum jam selesai.');
    mysqli_begin_transaction($db);
    try {
        if (!siakad_baris($db,'SELECT id_thn_akademik FROM thn_akademik WHERE id_thn_akademik=? FOR UPDATE','i',array($input['id_thn_akademik']))) throw new DomainException('Tahun akademik tidak ditemukan.');
        if (!siakad_baris($db,'SELECT id FROM prodi_has_dosen WHERE kode_prodi=? AND nip=? FOR UPDATE','ss',array($user['kode_prodi'],$input['nip']))) throw new DomainException('Dosen tidak terdaftar pada program studi.');
        if (!siakad_baris($db,'SELECT id FROM prodi_has_matkul WHERE kode_prodi=? AND kode_matkul=? FOR UPDATE','ss',array($user['kode_prodi'],$input['kode_mk']))) throw new DomainException('Mata kuliah tidak terdaftar pada program studi.');
        if (!siakad_baris($db,'SELECT r.kode_ruangan FROM tbl_ruangan r JOIN fakultas_has_jurusan f ON f.kode_fakultas=r.kode_fakultas WHERE r.kode_ruangan=? AND f.kode_prodi=?','is',array($input['kode_ruangan'],$user['kode_prodi']))) throw new DomainException('Ruangan tidak sesuai institusi program studi.');
        if (!siakad_baris($db,'SELECT id_hari FROM tbl_hari WHERE id_hari=?','i',array($input['id_hari']))) throw new DomainException('Hari tidak valid.');
        $bentrok=siakad_baris($db,'SELECT id_jadwal FROM jadwal_mengajar WHERE id_thn_akademik=? AND id_hari=? AND mulai_jam<? AND sampai_jam>? AND (nip=? OR kode_ruangan=?) LIMIT 1','iisssi',array($input['id_thn_akademik'],$input['id_hari'],$input['sampai_jam'],$input['mulai_jam'],$input['nip'],$input['kode_ruangan']));
        if ($bentrok) throw new DomainException('Jadwal bertabrakan dengan jadwal dosen atau ruangan.');
        siakad_ubah($db,'INSERT INTO jadwal_mengajar (kode_prodi,nip,id_thn_akademik,kode_mk,kode_ruangan,id_hari,mulai_jam,sampai_jam) VALUES (?,?,?,?,?,?,?,?)','ssisiiss',array($user['kode_prodi'],$input['nip'],$input['id_thn_akademik'],$input['kode_mk'],$input['kode_ruangan'],$input['id_hari'],$input['mulai_jam'],$input['sampai_jam']));
        mysqli_commit($db);
    } catch (Throwable $e) { mysqli_rollback($db); throw $e; }
}
function siakad_form_proses($callback,$url) {
    try { $callback(); }
    catch (DomainException $e) { siakad_tolak($e->getMessage(),422); }
    catch (Throwable $e) { error_log('SIAKAD: '.$e->getMessage()); siakad_tolak('Penyimpanan gagal. Data tidak diubah; hubungi administrator.',500); }
    siakad_alihkan($url);
}
