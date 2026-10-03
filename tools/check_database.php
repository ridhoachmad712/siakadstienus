<?php
// Pemeriksaan pra-migrasi: hanya SELECT, tanpa data pribadi atau password.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
ini_set('display_errors','0');
$stage='konfigurasi';
try {
    $config=require dirname(__DIR__).'/config/db_config.php';
    if (!is_array($config)) throw new RuntimeException();
    foreach (['host','user','password','database'] as $key) if (!isset($config[$key]) || !is_string($config[$key])) throw new RuntimeException();
    $stage='koneksi';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db=new mysqli($config['host'],$config['user'],$config['password'],$config['database'],(int)($config['port']??3306));
    $db->set_charset('utf8mb4');
    echo "Koneksi database: OK\n";
    echo 'Server: '.$db->query('SELECT VERSION()')->fetch_row()[0]."\n";
    $stage='pemeriksaan data';
    foreach (['mahasiswa','krs_mhs','khs_mhs','user'] as $table) echo $table.': '.$db->query('SELECT COUNT(*) FROM `'.$table.'`')->fetch_row()[0]." baris\n";
    $queries=[
        'Duplikasi KRS'=>'SELECT COUNT(*) FROM (SELECT 1 FROM krs_mhs GROUP BY nim_npm,id_jadwal,id_thn_akademik HAVING COUNT(*)>1) q',
        'Duplikasi KHS'=>'SELECT COUNT(*) FROM (SELECT 1 FROM khs_mhs GROUP BY nim_npm,id_jadwal,id_thn_akademik HAVING COUNT(*)>1) q',
        'Duplikasi akun'=>'SELECT COUNT(*) FROM (SELECT 1 FROM user GROUP BY username,level HAVING COUNT(*)>1) q',
        'Duplikasi batas SKS'=>'SELECT COUNT(*) FROM (SELECT 1 FROM pengaturan_sks_mhs GROUP BY nim_npm,id_thn_akademik HAVING COUNT(*)>1) q',
        'Duplikasi periode KRS'=>'SELECT COUNT(*) FROM (SELECT 1 FROM jadwal_penawaran GROUP BY id_thn_akademik HAVING COUNT(*)>1) q',
        'Duplikasi periode nilai'=>'SELECT COUNT(*) FROM (SELECT 1 FROM jadwal_input_nilai GROUP BY id_thn_akademik HAVING COUNT(*)>1) q',
        'Relasi KRS tidak sesuai'=>'SELECT COUNT(*) FROM krs_mhs k LEFT JOIN jadwal_mengajar j ON j.id_jadwal=k.id_jadwal AND j.kode_prodi=k.kode_prodi AND j.id_thn_akademik=k.id_thn_akademik WHERE j.id_jadwal IS NULL',
        'Relasi KHS tidak sesuai'=>'SELECT COUNT(*) FROM khs_mhs h LEFT JOIN krs_mhs k ON k.nim_npm=h.nim_npm AND k.id_jadwal=h.id_jadwal AND k.id_thn_akademik=h.id_thn_akademik AND k.kode_prodi=h.kode_prodi WHERE k.id_krs IS NULL',
        'FK CASCADE majemuk'=>'SELECT COUNT(*) FROM (SELECT k.TABLE_NAME,k.CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE k JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON r.CONSTRAINT_SCHEMA=k.CONSTRAINT_SCHEMA AND r.TABLE_NAME=k.TABLE_NAME AND r.CONSTRAINT_NAME=k.CONSTRAINT_NAME WHERE k.CONSTRAINT_SCHEMA=DATABASE() AND r.DELETE_RULE="CASCADE" GROUP BY k.TABLE_NAME,k.CONSTRAINT_NAME HAVING COUNT(*)>1) q'
    ];
    $problems=0;
    foreach ($queries as $label=>$query) { $count=(int)$db->query($query)->fetch_row()[0]; echo $label.': '.$count."\n"; $problems+=$count; }
    $sql="SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('krs_semester_mahasiswa','krs_izin_matkul','krs_kebijakan_log')";
    echo 'Tabel pengaturan KRS: '.$db->query($sql)->fetch_row()[0]." / 3\n";
    echo $problems?"PERLU DITINJAU: jangan jalankan migrasi dahulu.\n":"PEMERIKSAAN AWAL BERHASIL: tidak ada masalah pada prasyarat data migrasi.\n";
    echo "Tidak ada data yang diubah.\n";
    exit($problems?2:0);
} catch (Throwable $error) {
    fwrite(STDERR,'Pemeriksaan gagal pada tahap '.$stage.'. Kode: '.(int)$error->getCode().". Tidak ada data yang diubah.\n");
    exit(1);
}
