<?php
require_once __DIR__.'/ui.php';
require_once __DIR__.'/academic-components.php';
$sk_year_id = (int)($sk_period['id_thn_akademik'] ?? 0);
$sk_count = function($sql,$types='',$params=[]) use ($koneksi) {
    return (int)(siakad_baris($koneksi,$sql,$types,$params)['n'] ?? 0);
};
$sk_schedule = [];
$sk_context = sk_period_label($sk_period);
if ($level==='admin') {
    $sk_title='Ringkasan akademik';
    $sk_description='Kelola data kampus dan aktivitas akademik dalam satu tempat.';
    $sk_metrics=[['Mahasiswa',$sk_count('SELECT COUNT(*) n FROM mahasiswa'),'Seluruh mahasiswa terdaftar'],['Dosen',$sk_count('SELECT COUNT(*) n FROM dosen'),'Pengajar terdaftar'],['Mata kuliah',$sk_count('SELECT COUNT(*) n FROM mata_kuliah'),'Data master kampus']];
    $sk_tasks=[['Data mahasiswa','Tambah, cari, dan impor data mahasiswa.','mhs'],['Tahun akademik','Atur periode pengisian KRS dan input nilai.','thn_akademik'],['Pengelolaan akun','Kelola akses pengguna aplikasi.','akun_mhs']];
    $sk_primary=['Kelola mahasiswa','mhs'];
    $sk_quick=[['Program studi','jurusan'],['Dosen','dosen'],['Mata kuliah','mata_kuliah'],['Pengaturan','pengaturan']];
} elseif ($level==='Jurusan/Prodi') {
    $sk_title='Ruang kerja program studi';
    $sk_description='Kelola jadwal dan pantau proses akademik program studi Anda.';
    $sk_metrics=[['Mahasiswa prodi',$sk_count('SELECT COUNT(*) n FROM prodi_has_mhs WHERE kode_prodi=?','s',[$kode_prodi]),'Mahasiswa dalam program studi'],['Kelas periode ini',$sk_count('SELECT COUNT(*) n FROM jadwal_mengajar WHERE kode_prodi=? AND id_thn_akademik=?','si',[$kode_prodi,$sk_year_id]),$sk_context],['Dosen prodi',$sk_count('SELECT COUNT(*) n FROM prodi_has_dosen WHERE kode_prodi=?','s',[$kode_prodi]),'Pengajar program studi']];
    $sk_tasks=[['Susun jadwal perkuliahan','Kelola dosen, ruangan, dan waktu kuliah.','buat_jadwal?qwe='.$sk_year_id],['KRS mahasiswa','Periksa rencana studi mahasiswa.','krs_mhs?qwe='.$sk_year_id],['Input nilai','Kelola nilai akhir per kelas.','input_nilai?qwe='.$sk_year_id]];
    $sk_primary=['Kelola jadwal','buat_jadwal?qwe='.$sk_year_id];
    $sk_quick=[['Mahasiswa','jurusan_has_mhs'],['Mata kuliah','jurusan_has_matkul'],['Rekap jadwal','rekap_jadwal'],['Batas SKS','sks_mhs']];
} elseif ($level==='dosen') {
    $sk_title='Selamat datang, '.($sk_person['nama'] ?: $username);
    $sk_description='Jadwal mengajar, mahasiswa perwalian, dan nilai semester ini.';
    $sk_metrics=[['Kelas mengajar',$sk_count('SELECT COUNT(*) n FROM jadwal_mengajar WHERE nip=? AND id_thn_akademik=?','si',[$username,$sk_year_id]),$sk_context],['Mahasiswa perwalian',$sk_count('SELECT COUNT(*) n FROM mhs_has_pa WHERE nip=?','s',[$username]),'Mahasiswa bimbingan akademik'],['Nilai belum diisi',$sk_count("SELECT COUNT(*) n FROM khs_mhs k JOIN jadwal_mengajar j ON j.id_jadwal=k.id_jadwal WHERE j.nip=? AND k.id_thn_akademik=? AND (k.grade='' OR k.grade='-')",'si',[$username,$sk_year_id]),'Baris nilai pada kelas Anda']];
    $sk_tasks=[['Input nilai akhir','Masukkan dan periksa nilai akhir mahasiswa.','input_nilai_dosen?qwe='.$sk_year_id],['Mahasiswa perwalian','Buka data mahasiswa bimbingan akademik.','dosen_has_mhs']];
    $sk_primary=['Buka input nilai','input_nilai_dosen?qwe='.$sk_year_id];
    $sk_quick=[['Jadwal mengajar','jadwal_mengajar?qwe='.$sk_year_id],['Perwalian','dosen_has_mhs'],['Profil saya','dashboard?view=profil']];
} else {
    $sk_title='Selamat datang, '.(($sk_person['nama'] ?? '') ?: $username);
    $sk_description='Susun rencana studi, lihat jadwal, dan pantau hasil perkuliahan.';
    $sk_limit=siakad_baris($koneksi,'SELECT sks FROM pengaturan_sks_mhs WHERE nim_npm=? AND id_thn_akademik=?','si',[$username,$sk_year_id]);
    $sk_sks=siakad_baris($koneksi,'SELECT COALESCE(SUM(m.sks),0) n FROM krs_mhs k JOIN jadwal_mengajar j ON j.id_jadwal=k.id_jadwal JOIN mata_kuliah m ON m.kode_matkul=j.kode_mk WHERE k.nim_npm=? AND k.id_thn_akademik=?','si',[$username,$sk_year_id]);
    $sk_ip=siakad_baris($koneksi,"SELECT SUM(m.sks * CAST(k.bobot AS DECIMAL(5,2))) mutu, SUM(m.sks) sks FROM khs_mhs k JOIN jadwal_mengajar j ON j.id_jadwal=k.id_jadwal JOIN mata_kuliah m ON m.kode_matkul=j.kode_mk WHERE k.nim_npm=? AND k.kode_prodi=? AND k.grade<>'' AND k.grade<>'-'",'ss',[$username,$kode_prodi]);
    $sk_metrics=[['SKS periode ini',(int)$sk_sks['n'],$sk_limit?'Batas pengambilan '.$sk_limit['sks'].' SKS':'Batas SKS belum diatur'],['IP kumulatif',($sk_ip['sks'] ?? 0)>0?number_format($sk_ip['mutu']/$sk_ip['sks'],2,',','.'):'—','Dari hasil studi yang sudah dinilai'],['Mata kuliah',$sk_count('SELECT COUNT(*) n FROM krs_mhs WHERE nim_npm=? AND id_thn_akademik=?','si',[$username,$sk_year_id]),$sk_context]];
    $sk_tasks=[['Rencana studi','Pilih mata kuliah dan periksa jumlah SKS.','krs?qwe='.$sk_year_id],['Hasil studi','Lihat nilai yang telah diberikan dosen.','khs?qwe='.$sk_year_id]];
    $sk_primary=['Buka KRS','krs?qwe='.$sk_year_id];
    $sk_quick=[['Jadwal kuliah','jadwal_kuliah?qwe='.$sk_year_id],['KHS','khs?qwe='.$sk_year_id],['Transkrip','transkip'],['Profil saya','dashboard?view=profil']];
}
if (in_array($level,['mhs','dosen'],true)) {
    $sk_join=$level==='mhs'?'JOIN krs_mhs k ON k.id_jadwal=j.id_jadwal':'';
    $sk_scope=$level==='mhs'?'k.nim_npm=?':'j.nip=?';
    $sk_schedule=siakad_semua($koneksi,"SELECT m.nama_matkul,h.nama_hari,r.nama_ruangan,j.mulai_jam,j.sampai_jam FROM jadwal_mengajar j $sk_join LEFT JOIN mata_kuliah m ON m.kode_matkul=j.kode_mk LEFT JOIN tbl_hari h ON h.id_hari=j.id_hari LEFT JOIN tbl_ruangan r ON r.kode_ruangan=j.kode_ruangan WHERE $sk_scope AND j.id_thn_akademik=? ORDER BY j.id_hari,j.mulai_jam LIMIT 5",'si',[$username,$sk_year_id]);
}
$sk_window=siakad_baris($koneksi,'SELECT dari_tgl,sampai_tgl FROM jadwal_penawaran WHERE id_thn_akademik=?','i',[$sk_year_id]);
$sk_krs_state=sk_period_status($koneksi,'jadwal_penawaran',$sk_year_id);
$sk_grade_state=sk_period_status($koneksi,'jadwal_input_nilai',$sk_year_id);
$sk_krs_status='KRS · '.$sk_krs_state['label'];
$sk_attention_title='Periode akademik';
$sk_attention_note=$sk_context;
$sk_attention_link=$sk_primary;
if ($level==='Jurusan/Prodi') {
    $sk_missing_limits=$sk_count('SELECT COUNT(*) n FROM prodi_has_mhs p LEFT JOIN pengaturan_sks_mhs s ON s.nim_npm=p.nim_npm AND s.id_thn_akademik=? WHERE p.kode_prodi=? AND s.nim_npm IS NULL','is',[$sk_year_id,$kode_prodi]);
    $sk_attention_title=$sk_missing_limits.' mahasiswa belum memiliki batas SKS';
    $sk_attention_note='Periksa batas SKS sebelum mahasiswa mengisi KRS pada periode ini.';
    $sk_attention_link=['Atur batas SKS','sks_mhs?qwe='.$sk_year_id];
} elseif ($level==='dosen') {
    $sk_attention_title=$sk_metrics[2][1].' nilai mahasiswa belum diisi';
    $sk_attention_note=$sk_grade_state['label'].' · '.$sk_grade_state['note'];
} elseif ($level==='mhs') {
    $sk_attention_title=$sk_limit?'Rencana studi: '.(int)$sk_sks['n'].' dari '.(int)$sk_limit['sks'].' SKS':'Batas SKS belum ditentukan';
    $sk_attention_note=$sk_limit?$sk_krs_state['note']:'Hubungi program studi untuk pengaturan batas SKS periode ini.';
} else {
    $sk_attention_note='KRS: '.$sk_krs_state['label'].' · Input nilai: '.$sk_grade_state['label'];
    $sk_attention_link=['Kelola periode','thn_akademik'];
}
?>
<section class="sk-dashboard-heading">
  <div><div class="sk-eyebrow">Beranda / <?= sk_escape(sk_role_label($level)); ?></div><h2><?= sk_escape($sk_title); ?></h2><p><?= sk_escape($sk_description); ?></p></div>
  <a class="btn btn-primary" href="<?= sk_escape($sk_primary[1]); ?>"><?= sk_escape($sk_primary[0]); ?> <span aria-hidden="true">↗</span></a>
</section>
<div class="card sk-dashboard-status"><div><strong><?= sk_escape($sk_attention_title); ?></strong><p><?= sk_escape($sk_attention_note); ?></p></div><a class="btn btn-outline-primary btn-sm" href="<?= sk_escape($sk_attention_link[1]); ?>"><?= sk_escape($sk_attention_link[0]); ?></a></div>
<div class="sk-metrics">
<?php foreach ($sk_metrics as [$label,$value,$note]) { ?>
  <dl class="card sk-metric mb-0"><dt><?= sk_escape($label); ?></dt><dd><?= sk_escape($value); ?></dd><small><?= sk_escape($note); ?></small></dl>
<?php } ?>
</div>
<div class="sk-home-grid">
  <section class="card">
    <div class="card-header"><h3 class="card-title"><?= in_array($level,['mhs','dosen'],true)?'Jadwal periode ini':'Pekerjaan utama'; ?></h3></div>
    <?php if (in_array($level,['mhs','dosen'],true)) { ?>
      <?php if (!$sk_schedule) { ?><div class="sk-work-item"><div><strong>Belum ada jadwal</strong><small>Jadwal untuk <?= sk_escape($sk_context); ?> akan tampil di sini.</small></div></div><?php } ?>
      <?php foreach ($sk_schedule as $class) { ?><div class="sk-work-item"><div><strong><?= sk_escape($class['nama_matkul'] ?: 'Mata kuliah'); ?></strong><small><?= sk_escape(($class['nama_hari'] ?: 'Hari belum diatur').' · '.substr($class['mulai_jam'],0,5).'–'.substr($class['sampai_jam'],0,5).' · '.($class['nama_ruangan'] ?: 'Ruangan belum diatur')); ?></small></div></div><?php } ?>
    <?php } ?>
    <?php foreach ($sk_tasks as [$label,$note,$href]) { ?><div class="sk-work-item"><div><strong><?= sk_escape($label); ?></strong><small><?= sk_escape($note); ?></small></div><a class="btn btn-outline-primary" href="<?= sk_escape($href); ?>" aria-label="Buka <?= sk_escape($label); ?>">Buka</a></div><?php } ?>
  </section>
  <section class="card">
    <div class="card-header"><h3 class="card-title">Akses cepat</h3></div>
    <div class="sk-shortcuts"><?php foreach ($sk_quick as [$label,$href]) { ?><a href="<?= sk_escape($href); ?>"><?= sk_escape($label); ?><span aria-hidden="true">›</span></a><?php } ?></div>
    <div class="sk-period-note"><div class="mb-2"><?= sk_escape($sk_context); ?></div><span class="sk-status"><?= sk_escape($sk_krs_status); ?></span><?php if ($sk_window && $sk_window['dari_tgl']>'0000-00-00') { ?><div class="mt-2"><?= sk_escape(date('d/m/Y',strtotime($sk_window['dari_tgl'])).'–'.date('d/m/Y',strtotime($sk_window['sampai_tgl']))); ?></div><?php } ?></div>
  </section>
</div>
