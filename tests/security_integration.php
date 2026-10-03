<?php
// Hanya server pengujian terpisah; tidak pernah memakai db_config.php aplikasi.
if (PHP_SAPI!=='cli') exit(1);
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db=mysqli_connect('127.0.0.1','root','','siakad_security_test',(int)(getenv('SIAKAD_TEST_PORT')?:33316));
mysqli_set_charset($db,'utf8mb4');
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../config/academic.php';
require_once __DIR__.'/../config/deletion.php';
require_once __DIR__.'/../config/login_service.php';
$checks=0;
function check($condition,$label) { global $checks; if (!$condition) throw new RuntimeException('FAILED: '.$label); $checks++; }
function denied($callback,$label) { try { $callback(); } catch (DomainException $e) { check(true,$label); return; } throw new RuntimeException('FAILED: '.$label); }
function seed($table,$override) {
    global $db;
    $data=array();
    foreach (siakad_semua($db,"SHOW COLUMNS FROM `$table`") as $col) {
        $key=$col['Field'];
        if (strpos($col['Extra'],'auto_increment')!==false && !isset($override[$key])) continue;
        if (isset($override[$key])) $data[$key]=$override[$key];
        elseif (strpos($col['Type'],'int')!==false) $data[$key]=0;
        elseif ($col['Type']==='date') $data[$key]='2000-01-01';
        elseif ($col['Type']==='time') $data[$key]='00:00:00';
        else $data[$key]='';
    }
    $columns=implode(',',array_map(fn($key)=>'`'.$key.'`',array_keys($data)));
    siakad_ubah($db,"INSERT INTO `$table` ($columns) VALUES (".implode(',',array_fill(0,count($data),'?')).')',str_repeat('s',count($data)),array_values($data));
}
seed('tbl_jk',array('id_jk'=>1)); seed('tbl_agama',array('id_agama'=>1)); seed('tbl_jenis_mk',array('id_jenis_mk'=>1));
foreach (array('D1','D2') as $nip) seed('dosen',array('nip'=>$nip,'id_jk'=>1,'id_agama'=>1));
seed('tbl_fakultas',array('kode_fakultas'=>'F1'));
foreach (array('P1','P2') as $p) { seed('prodi',array('kode_prodi'=>$p,'ketua_prodi'=>'D1')); seed('fakultas_has_jurusan',array('kode_fakultas'=>'F1','kode_prodi'=>$p)); }
foreach (array(1,2) as $id) { seed('thn_akademik',array('id_thn_akademik'=>$id,'thn_akademik'=>2026,'ket'=>'Ganjil')); seed('jadwal_penawaran',array('id_thn_akademik'=>$id,'dari_tgl'=>'0000-00-00','sampai_tgl'=>'0000-00-00')); seed('jadwal_input_nilai',array('id_thn_akademik'=>$id,'dari_tgl'=>'0000-00-00','sampai_tgl'=>'0000-00-00')); }
foreach (array('S1','S2','S3') as $nim) { seed('mahasiswa',array('nim_npm'=>$nim,'nama_mhs'=>'Mahasiswa Uji','thn_masuk'=>2026,'status_mhs'=>'Aktif','id_jk'=>1,'id_agama'=>1)); seed('prodi_has_mhs',array('nim_npm'=>$nim,'kode_prodi'=>'P1')); seed('pengaturan_sks_mhs',array('nim_npm'=>$nim,'id_thn_akademik'=>1,'sks'=>$nim==='S1'?6:30)); }
seed('tbl_hari',array('id_hari'=>1)); seed('tbl_hari',array('id_hari'=>2));
seed('tbl_ruangan',array('kode_ruangan'=>1,'kode_fakultas'=>'F1')); seed('tbl_ruangan',array('kode_ruangan'=>2,'kode_fakultas'=>'F1'));
foreach (array('M1','M2','M3','M4','M5') as $mk) { seed('mata_kuliah',array('kode_matkul'=>$mk,'nama_matkul'=>$mk,'semester'=>'1MN','sks'=>3,'id_jenis_mk'=>1)); seed('prodi_has_matkul',array('kode_prodi'=>'P1','kode_matkul'=>$mk)); }
seed('prodi_has_dosen',array('kode_prodi'=>'P1','nip'=>'D1'));
$schedules=array(
    1=>array('kode_mk'=>'M1','mulai_jam'=>'08:00:00','sampai_jam'=>'09:00:00'),
    2=>array('kode_mk'=>'M2','mulai_jam'=>'09:00:00','sampai_jam'=>'10:00:00'),
    3=>array('kode_mk'=>'M3','mulai_jam'=>'08:30:00','sampai_jam'=>'09:30:00'),
    4=>array('kode_mk'=>'M4','id_thn_akademik'=>2,'mulai_jam'=>'10:00:00','sampai_jam'=>'11:00:00'),
    5=>array('kode_mk'=>'M5','kode_prodi'=>'P2','mulai_jam'=>'11:00:00','sampai_jam'=>'12:00:00')
);
foreach ($schedules as $id=>$props) seed('jadwal_mengajar',array_merge(array('id_jadwal'=>$id,'nip'=>'D1','kode_prodi'=>'P1','id_thn_akademik'=>1,'kode_ruangan'=>1,'id_hari'=>1),$props));
foreach (array(array('E',0,40,0),array('D',40,50,1),array('C-',50,54,1.7),array('C',55,59,2),array('C+',60,64,2.3),array('B-',65,69,2.7),array('B',70,74,3),array('B+',75,79,3.3),array('A-',80,84,3.7),array('A',85,100,4)) as $g) seed('tbl_grade',array('grade'=>$g[0],'nilai_awal'=>$g[1],'nilai_akhir'=>$g[2],'bobot'=>$g[3]));
seed('pengaturan',array('id_pengaturan'=>1,'nama_aplikasi'=>'SIAKAD Pengujian','nama_kampus'=>'Kampus Uji'));
foreach (array(array('admin','admin','',md5('password123')),array('P1','Jurusan/Prodi','P1','password123'),array('D1','dosen','',password_hash('password123',PASSWORD_DEFAULT)),array('S1','mhs','P1','password123'),array('S2','mhs','P1','password123')) as $u) seed('user',array('username'=>$u[0],'level'=>$u[1],'kode_prodi'=>$u[2],'password'=>$u[3]));
$student=array('username'=>'S1','level'=>'mhs','kode_prodi'=>'P1','id_user'=>4);
$student2=array('username'=>'S2','level'=>'mhs','kode_prodi'=>'P1','id_user'=>5);
$lecturer=array('username'=>'D1','level'=>'dosen','kode_prodi'=>'','id_user'=>3);
$wrongLecturer=array('username'=>'D2','level'=>'dosen','kode_prodi'=>'','id_user'=>99);
$prodi=array('username'=>'P1','level'=>'Jurusan/Prodi','kode_prodi'=>'P1','id_user'=>2);
check(!in_array('mhs',siakad_level_halaman('akun_admin'),true),'student cannot create admin');
check(!in_array('dosen',siakad_level_halaman('buat_jadwal'),true),'lecturer cannot manage schedules');
check(siakad_level_halaman('unknown')===array(),'unknown endpoint deny by default');
$_SESSION['csrf_token']='test-token';
check(siakad_csrf_valid('test-token') && !siakad_csrf_valid('bad') && !siakad_csrf_valid(array()),'CSRF valid and invalid tokens');
check(!siakad_password_cocok(md5('password123'),md5('password123')),'MD5 hash cannot be used as password');
check(siakad_login($db,'admin','password123','admin'),'MD5 legacy login');
check(password_verify('password123',siakad_baris($db,"SELECT password FROM user WHERE username='admin'")['password']),'MD5 migrated to modern hash');
check(!isset($_SESSION['password']) && siakad_user_sesi($db)!==null,'session contains identity, no password');
check(siakad_login($db,'S1','password123','mhs'),'plaintext legacy login');
check(siakad_login($db,'D1','password123','dosen'),'modern password login');
check(!siakad_login($db,'D1','wrong','dosen'),'invalid password rejected');
siakad_ganti_password($db,'newpassword123','newpassword123');
check(siakad_user_sesi($db)!==null,'password change keeps current session');
siakad_ubah($db,"UPDATE user SET password=? WHERE username='D1'",'s',array(password_hash('resetpassword',PASSWORD_DEFAULT)));
check(siakad_user_sesi($db)===null,'other password reset revokes stale session');
check(!siakad_mahasiswa_diizinkan($db,$student,'S2'),'student cannot access other student');
check(siakad_mahasiswa_diizinkan($db,$prodi,'S1'),'prodi can access own student');
denied(fn()=>siakad_ambil_krs($db,$student,1,array(1,1)),'duplicate selection rejected');
denied(fn()=>siakad_ambil_krs($db,$student,1,array(4)),'other year rejected');
denied(fn()=>siakad_ambil_krs($db,$student,1,array(5)),'other prodi rejected');
denied(fn()=>siakad_ambil_krs($db,$student,1,array(1,3)),'overlapping student schedules rejected');
siakad_ambil_krs($db,$student,1,array(1,2));
check((int)siakad_baris($db,"SELECT COUNT(*) n FROM krs_mhs WHERE nim_npm='S1'")['n']===2,'KRS saved');
check((int)siakad_baris($db,"SELECT COUNT(*) n FROM khs_mhs WHERE nim_npm='S1'")['n']===2,'KHS initialized atomically');
denied(fn()=>siakad_ambil_krs($db,$student,1,array(1)),'existing course rejected');
seed('jadwal_mengajar',array('id_jadwal'=>6,'nip'=>'D1','kode_mk'=>'M3','kode_prodi'=>'P1','id_thn_akademik'=>1,'kode_ruangan'=>1,'id_hari'=>1,'mulai_jam'=>'12:00:00','sampai_jam'=>'13:00:00'));
denied(fn()=>siakad_ambil_krs($db,$student,1,array(6)),'SKS limit enforced');
$db->query("CREATE TRIGGER fail_khs BEFORE INSERT ON khs_mhs FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='forced test failure'");
try { siakad_ambil_krs($db,$student2,1,array(1)); throw new RuntimeException('Expected DB failure'); } catch (mysqli_sql_exception $e) { check(true,'forced KHS write failure'); }
$db->query('DROP TRIGGER fail_khs');
check((int)siakad_baris($db,"SELECT COUNT(*) n FROM krs_mhs WHERE nim_npm='S2'")['n']===0,'KRS rollback on KHS failure');
denied(fn()=>siakad_simpan_nilai($db,$wrongLecturer,1,array('S1'),array(80)),'wrong lecturer rejected');
denied(fn()=>siakad_simpan_nilai($db,$lecturer,1,array('S2'),array(80)),'nonparticipant rejected');
denied(fn()=>siakad_simpan_nilai($db,$lecturer,1,array('S1'),array(101)),'out of range score rejected');
siakad_simpan_nilai($db,$lecturer,1,array('S1'),array(85));
$nilai=siakad_baris($db,"SELECT * FROM khs_mhs WHERE nim_npm='S1' AND id_jadwal=1");
check((float)$nilai['nilai_akhir']===85.0 && $nilai['grade']==='A' && (float)$nilai['bobot']===4.0,'direct final score and grade persisted');
denied(fn()=>siakad_simpan_nilai($db,$lecturer,1,array('S1','S2'),array(40,90)),'batch invalid participant rejected');
check((float)siakad_baris($db,"SELECT nilai_akhir FROM khs_mhs WHERE nim_npm='S1' AND id_jadwal=1")['nilai_akhir']===85.0,'grade batch rolled back including earlier row');
denied(fn()=>siakad_simpan_nilai($db,$lecturer,1,array(array('S1')),array(90)),'malformed NIM array rejected');
$grades=siakad_semua($db,'SELECT * FROM tbl_grade');
check(siakad_grade(0,$grades)['grade']==='E' && siakad_grade(40,$grades)['grade']==='D' && siakad_grade(50,$grades)['grade']==='C-' && siakad_grade(84.99,$grades)['grade']==='A-' && siakad_grade(100,$grades)['grade']==='A','grade zero, boundaries and fractional scores');
$id=siakad_baris($db,"SELECT id_krs FROM krs_mhs WHERE nim_npm='S1' AND id_jadwal=1")['id_krs'];
denied(fn()=>siakad_hapus_krs($db,$student2,$id),'other student cannot delete KRS');
denied(fn()=>siakad_hapus_krs($db,$student,$id),'graded KRS protected');
denied(fn()=>siakad_hapus_aman($db,'jadwal_mengajar','id_jadwal',1),'schedule with academic history protected before migration');
denied(fn()=>siakad_hapus_aman($db,'mahasiswa','nim_npm','S1'),'student with dependencies protected before migration');
$ungraded=siakad_baris($db,"SELECT id_krs FROM krs_mhs WHERE nim_npm='S1' AND id_jadwal=2")['id_krs'];
siakad_hapus_krs($db,$student,$ungraded);
check(!siakad_baris($db,"SELECT * FROM khs_mhs WHERE nim_npm='S1' AND id_jadwal=2"),'ungraded KRS and KHS deleted together');
siakad_ubah($db,"UPDATE jadwal_penawaran SET dari_tgl='2000-01-01',sampai_tgl='2000-01-02' WHERE id_thn_akademik=1");
denied(fn()=>siakad_ambil_krs($db,$student2,1,array(1)),'closed registration period enforced');
siakad_ubah($db,"UPDATE jadwal_penawaran SET dari_tgl='0000-00-00' WHERE id_thn_akademik=1");
siakad_ubah($db,"UPDATE jadwal_input_nilai SET dari_tgl='2000-01-01',sampai_tgl='2000-01-02' WHERE id_thn_akademik=1");
denied(fn()=>siakad_simpan_nilai($db,$lecturer,1,array('S1'),array(90)),'closed grade period enforced');
siakad_ubah($db,"UPDATE jadwal_input_nilai SET dari_tgl='0000-00-00' WHERE id_thn_akademik=1");
$schedule=array('nip'=>'D1','kode_mk'=>'M4','kode_ruangan'=>'1','id_hari'=>'1','mulai_jam'=>'07:00','sampai_jam'=>'11:00','id_thn_akademik'=>'1');
denied(fn()=>siakad_buat_jadwal($db,$prodi,$schedule),'enclosing time interval rejected');
$schedule['mulai_jam']='08:15';$schedule['sampai_jam']='08:45';
denied(fn()=>siakad_buat_jadwal($db,$prodi,$schedule),'single conflict rejected');
$schedule['mulai_jam']='14:00';$schedule['sampai_jam']='15:00';
siakad_buat_jadwal($db,$prodi,$schedule);
check((int)siakad_baris($db,"SELECT COUNT(*) n FROM jadwal_mengajar WHERE mulai_jam='14:00:00'")['n']===1,'nonconflicting schedule saved');
$schedule['sampai_jam']='13:00';
denied(fn()=>siakad_buat_jadwal($db,$prodi,$schedule),'reversed time rejected');
check(!siakad_waktu_bentrok('09:00','10:00','08:00','09:00'),'adjacent classes permitted');
siakad_buat_akun($db,'newadmin','newpassword123','admin');
check(password_verify('newpassword123',siakad_baris($db,"SELECT password FROM user WHERE username='newadmin'")['password']),'new accounts use modern hashes');
denied(fn()=>siakad_buat_akun($db,'newadmin','newpassword123','admin'),'duplicate account rejected');
denied(fn()=>siakad_buat_akun($db,'badusername\'','newpassword123','admin'),'unsafe username rejected');
denied(fn()=>siakad_buat_akun_batch($db,array('S3','S1'),'mhs'),'account batch rolls back if an account exists');
check(!siakad_baris($db,"SELECT id_user FROM user WHERE username='S3'"),'partial account batch not retained');
require __DIR__.'/krs_policy_integration.php';

echo "PASS: $checks integration checks\n";
