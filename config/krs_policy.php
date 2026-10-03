<?php
require_once __DIR__.'/security.php';

function siakad_semester_matkul($label) {
    if (is_int($label)) $label=(string)$label;
    if (!is_string($label) || !preg_match('/^([1-9][0-9]?)(?:[A-Za-z]+)?$/D',trim($label),$match)) return null;
    $number=(int)$match[1];
    return $number<=20 ? $number : null;
}
function siakad_semester_normal($angkatan,$tahun,$ket) {
    if (!is_numeric($angkatan) || !is_numeric($tahun) || $angkatan<1900 || $tahun<1900) return null;
    $parity=['ganjil'=>1,'genap'=>2][strtolower(trim((string)$ket))]??null;
    if ($parity===null) return null;
    $semester=2*((int)$tahun-(int)$angkatan)+$parity;
    return $semester>=1 && $semester<=20 ? $semester : null;
}
function siakad_krs_konteks($db,$nim,$prodi,$tahun) {
    $student=siakad_baris($db,'SELECT m.thn_masuk,m.status_mhs FROM mahasiswa m JOIN prodi_has_mhs p ON p.nim_npm=m.nim_npm WHERE m.nim_npm=? AND p.kode_prodi=?','ss',[$nim,$prodi]);
    $period=siakad_baris($db,'SELECT * FROM thn_akademik WHERE id_thn_akademik=?','i',[$tahun]);
    $override=siakad_baris($db,'SELECT semester,alasan FROM krs_semester_mahasiswa WHERE nim_npm=? AND id_thn_akademik=? AND kode_prodi=?','sis',[$nim,$tahun,$prodi]);
    $normal=$student&&$period ? siakad_semester_normal($student['thn_masuk'],$period['thn_akademik'],$period['ket']) : null;
    $semester=$override?(int)$override['semester']:$normal;
    $active=$student && strtolower(trim($student['status_mhs']))==='aktif';
    $valid=$student && $period && $semester!==null && $semester>=1 && $semester<=20;
    $message=!$active?'Status mahasiswa harus aktif untuk mengambil KRS.':(!$valid?'Semester mahasiswa belum dapat ditentukan. Hubungi program studi.':'');
    $permissions=siakad_semua($db,'SELECT * FROM krs_izin_matkul WHERE nim_npm=? AND id_thn_akademik=? AND kode_prodi=? AND aktif=1','sis',[$nim,$tahun,$prodi]);
    return ['semester'=>$semester,'normal'=>$normal,'override'=>$override,'active'=>$active,'valid'=>(bool)$valid,'message'=>$message,'permissions'=>array_column($permissions,null,'kode_matkul')];
}
function siakad_krs_jenis_pilihan($context,$row) {
    $semester=siakad_semester_matkul($row['semester']??null);
    if (!$context['valid'] || !$context['active'] || $semester===null) return null;
    if ($semester===$context['semester']) return 'reguler';
    $permit=$context['permissions'][$row['kode_mk']]??null;
    if (!$permit) return null;
    if ($permit['jenis']==='mengulang' && $semester<$context['semester']) return 'Mengulang';
    if ($permit['jenis']==='semester_atas' && $semester>$context['semester']) return 'Semester atas';
    return $permit['jenis']==='penyesuaian'?'Persetujuan khusus':null;
}
function siakad_krs_penawaran($db,$nim,$prodi,$tahun,$context) {
    $rows=siakad_semua($db,'SELECT j.*,m.nama_matkul,m.sks,m.semester,d.nama_dosen,h.nama_hari,r.nama_ruangan FROM jadwal_mengajar j JOIN mata_kuliah m ON m.kode_matkul=j.kode_mk JOIN prodi_has_matkul pm ON pm.kode_matkul=j.kode_mk AND pm.kode_prodi=j.kode_prodi LEFT JOIN dosen d ON d.nip=j.nip LEFT JOIN tbl_hari h ON h.id_hari=j.id_hari LEFT JOIN tbl_ruangan r ON r.kode_ruangan=j.kode_ruangan WHERE j.kode_prodi=? AND j.id_thn_akademik=? AND NOT EXISTS (SELECT 1 FROM krs_mhs k JOIN jadwal_mengajar old ON old.id_jadwal=k.id_jadwal WHERE k.nim_npm=? AND k.id_thn_akademik=j.id_thn_akademik AND old.kode_mk=j.kode_mk) ORDER BY m.nama_matkul,j.id_hari,j.mulai_jam','sis',[$prodi,$tahun,$nim]);
    $groups=['reguler'=>[],'tambahan'=>[]];
    foreach ($rows as $row) {
        $kind=siakad_krs_jenis_pilihan($context,$row);
        if ($kind===null) continue;
        $row['jenis_izin']=$kind;
        $groups[$kind==='reguler'?'reguler':'tambahan'][]=$row;
    }
    return $groups;
}
function siakad_krs_atur($db,$user,$nim,$tahun,$input) {
    if ($user['level']!=='Jurusan/Prodi' || !is_string($nim) || !siakad_mahasiswa_diizinkan($db,$user,$nim)) throw new DomainException('Pengaturan hanya untuk mahasiswa dalam program studi Anda.');
    if (!filter_var($tahun,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])) throw new DomainException('Periode tidak valid.');
    $action=$input['tindakan']??'';
    if (!is_string($action) || !in_array($action,['semester','izin','cabut'],true)) throw new DomainException('Tindakan tidak valid.');
    $reason=$input['alasan']??'';
    if (!is_string($reason) || strlen(trim($reason))<5 || strlen($reason)>500) throw new DomainException('Isi alasan 5 sampai 500 karakter.');
    $reason=trim($reason);
    mysqli_begin_transaction($db);
    try {
        siakad_baris($db,'SELECT nim_npm FROM mahasiswa WHERE nim_npm=? FOR UPDATE','s',[$nim]);
        if (!siakad_baris($db,'SELECT id FROM prodi_has_mhs WHERE nim_npm=? AND kode_prodi=? FOR UPDATE','ss',[$nim,$user['kode_prodi']])) throw new DomainException('Mahasiswa berada di luar program studi.');
        $context=siakad_krs_konteks($db,$nim,$user['kode_prodi'],$tahun);
        if (!siakad_baris($db,'SELECT id_thn_akademik FROM thn_akademik WHERE id_thn_akademik=?','i',[$tahun])) throw new DomainException('Periode tidak ditemukan.');
        if ($action==='semester') {
            $semester=$input['semester']??null;
            if ($semester==='') {
                if ($context['normal']===null) throw new DomainException('Semester otomatis belum dapat ditentukan. Isi semester mahasiswa.');
                siakad_ubah($db,'DELETE FROM krs_semester_mahasiswa WHERE nim_npm=? AND id_thn_akademik=? AND kode_prodi=?','sis',[$nim,$tahun,$user['kode_prodi']]);
                $details=['semester_sebelumnya'=>$context['semester'],'semester'=>$context['normal'],'otomatis'=>true,'alasan'=>$reason];
            } else {
                $semester=filter_var($semester,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>20]]);
                if ($semester===false) throw new DomainException('Semester harus 1 sampai 20.');
                siakad_ubah($db,'INSERT INTO krs_semester_mahasiswa (nim_npm,id_thn_akademik,kode_prodi,semester,alasan,id_pemberi) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE kode_prodi=VALUES(kode_prodi),semester=VALUES(semester),alasan=VALUES(alasan),id_pemberi=VALUES(id_pemberi)','sisisi',[$nim,$tahun,$user['kode_prodi'],$semester,$reason,$user['id_user']]);
                $details=['semester_sebelumnya'=>$context['semester'],'semester'=>$semester,'alasan'=>$reason];
            }
        } else {
            $mk=$input['kode_matkul']??null;
            if (!is_string($mk) || $mk==='') throw new DomainException('Pilih mata kuliah.');
            if ($action==='cabut') {
                if (siakad_ubah($db,'UPDATE krs_izin_matkul SET aktif=0,id_pemberi=?,alasan=? WHERE nim_npm=? AND id_thn_akademik=? AND kode_matkul=? AND kode_prodi=? AND aktif=1','ississ',[$user['id_user'],$reason,$nim,$tahun,$mk,$user['kode_prodi']])!==1) throw new DomainException('Izin aktif tidak ditemukan.');
                $details=['kode_matkul'=>$mk,'alasan'=>$reason];
            } else {
                $row=siakad_baris($db,'SELECT m.semester FROM mata_kuliah m JOIN prodi_has_matkul p ON p.kode_matkul=m.kode_matkul WHERE m.kode_matkul=? AND p.kode_prodi=? AND EXISTS (SELECT 1 FROM jadwal_mengajar j WHERE j.kode_mk=m.kode_matkul AND j.kode_prodi=p.kode_prodi AND j.id_thn_akademik=?)','ssi',[$mk,$user['kode_prodi'],$tahun]);
                $semester=$row?siakad_semester_matkul($row['semester']):null;
                $kind=$input['jenis']??null;
                if (!$context['valid'] || $semester===null || !is_string($kind) || !in_array($kind,['mengulang','semester_atas','penyesuaian'],true)) throw new DomainException('Semester atau penawaran mata kuliah tidak valid.');
                if ($semester===$context['semester'] || ($kind==='mengulang' && $semester>=$context['semester']) || ($kind==='semester_atas' && $semester<=$context['semester'])) throw new DomainException('Jenis izin tidak sesuai semester mata kuliah.');
                siakad_ubah($db,'INSERT INTO krs_izin_matkul (nim_npm,id_thn_akademik,kode_prodi,kode_matkul,jenis,alasan,id_pemberi,aktif) VALUES (?,?,?,?,?,?,?,1) ON DUPLICATE KEY UPDATE kode_prodi=VALUES(kode_prodi),jenis=VALUES(jenis),alasan=VALUES(alasan),id_pemberi=VALUES(id_pemberi),aktif=1','sissssi',[$nim,$tahun,$user['kode_prodi'],$mk,$kind,$reason,$user['id_user']]);
                $details=['kode_matkul'=>$mk,'jenis'=>$kind,'alasan'=>$reason];
            }
        }
        siakad_ubah($db,'INSERT INTO krs_kebijakan_log (nim_npm,id_thn_akademik,id_pemberi,aksi,rincian) VALUES (?,?,?,?,?)','siiss',[$nim,$tahun,$user['id_user'],$action,json_encode($details,JSON_UNESCAPED_UNICODE)]);
        mysqli_commit($db);
    } catch (Throwable $e) { mysqli_rollback($db); throw $e; }
}
