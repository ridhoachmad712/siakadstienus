<?php
session_start();
require_once __DIR__.'/../config/koneksi.php';
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../config/academic.php';
require_once __DIR__.'/ui.php';
require_once __DIR__.'/academic-components.php';
$sk_user=siakad_wajib_login($koneksi,null,'../login');
$sk_kind=sk_current_route();
$sk_nim=$sk_user['level']==='mhs'?$sk_user['username']:($_GET['nim_npm']??'');
if (!is_string($sk_nim)||!siakad_mahasiswa_diizinkan($koneksi,$sk_user,$sk_nim)) siakad_tolak('Mahasiswa tidak ditemukan dalam lingkup akun Anda.');
$sk_prodi=$sk_user['kode_prodi'];
$student=siakad_baris($koneksi,'SELECT m.*,p.nama_prodi,p.jenjang FROM mahasiswa m JOIN prodi_has_mhs pm ON pm.nim_npm=m.nim_npm LEFT JOIN prodi p ON p.kode_prodi=pm.kode_prodi WHERE m.nim_npm=? AND pm.kode_prodi=?','ss',[$sk_nim,$sk_prodi]);
if (!$student) siakad_tolak('Data mahasiswa tidak ditemukan.',404);
$period=sk_period($koneksi); $year=(int)($period['id_thn_akademik']??0);
$settings=siakad_baris($koneksi,'SELECT * FROM pengaturan WHERE id_pengaturan=1') ?: [];
$adviser=siakad_baris($koneksi,'SELECT d.nama_dosen FROM mhs_has_pa a LEFT JOIN dosen d ON d.nip=a.nip WHERE a.nim_npm=?','s',[$sk_nim]);
$transcript=$sk_kind==='transkip'; $results=$sk_kind==='khs'||$transcript; $schedule=$sk_kind==='jadwalkuliah';
$rows=sk_study_data($koneksi,$sk_nim,$sk_prodi,$year,$transcript,$results);
$total=sk_study_totals($transcript?array_filter($rows,fn($row)=>$row['kode_prodi']===$sk_prodi):$rows);
$title=['krs'=>'Kartu rencana studi','khs'=>'Kartu hasil studi','transkip'=>'Transkrip nilai','jadwalkuliah'=>'Jadwal kuliah'][$sk_kind];
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= sk_escape($title.' · '.$student['nama_mhs']); ?></title><?php require __DIR__.'/print-style.php'; ?></head>
<body class="sk-print-body">
  <div class="sk-print-toolbar"><span>Pratinjau dokumen · <?= sk_escape($title); ?></span><button type="button" onclick="window.print()">Cetak dokumen</button></div>
  <main class="sk-paper">
    <header class="sk-letterhead">
      <?php if (!empty($settings['logo_aplikasi'])) { ?><img src="../../img/<?= sk_escape(rawurlencode($settings['logo_aplikasi'])); ?>" alt="Logo kampus"><?php } ?>
      <div><h1><?= sk_escape($settings['nama_kampus'] ?? 'STIE Nusantara'); ?></h1><p><?= sk_escape($settings['alamat'] ?? ''); ?></p><p><?= sk_escape(trim(($settings['email']??'').' · '.($settings['no_telp']??''),' ·')); ?></p></div>
    </header>
    <div class="sk-document-title"><h2><?= sk_escape($title); ?></h2><p><?= $transcript?'Seluruh hasil studi yang sudah dinilai':sk_escape(sk_period_label($period)); ?></p></div>
    <dl class="sk-print-identity"><div><dt>Nama mahasiswa</dt><dd><?= sk_escape($student['nama_mhs'] ?: $sk_nim); ?></dd></div><div><dt>NIM / NPM</dt><dd><?= sk_escape($sk_nim); ?></dd></div><div><dt>Program studi</dt><dd><?= sk_escape(trim($student['jenjang'].' '.$student['nama_prodi'])); ?></dd></div><div><dt>Angkatan</dt><dd><?= sk_escape($student['thn_masuk']); ?></dd></div></dl>
    <table class="sk-print-table"><thead><tr><th>No.</th><th>Mata kuliah</th><th>SKS</th><?php if ($results) { ?><th>Nilai akhir</th><th>Grade</th><th>Bobot</th><?php } else { ?><th>Dosen</th><th>Jadwal / ruang</th><?php } ?></tr></thead><tbody>
    <?php if (!$rows) { ?><tr><td colspan="<?= $results?6:5; ?>">Belum ada data pada dokumen ini.</td></tr><?php } ?>
    <?php foreach ($rows as $index=>$row) { ?><tr><td><?= $index+1; ?></td><td><?= sk_escape($row['nama_matkul'] ?: $row['kode_mk']); ?><small><?= sk_escape($row['kode_mk']); ?><?= $transcript?' · '.sk_escape($row['thn_akademik'].' '.$row['ket']):''; ?></small></td><td class="sk-print-number"><?= (int)$row['sks']; ?></td><?php if ($results) { ?><td class="sk-print-number"><?= sk_escape($row['nilai_akhir']); ?></td><td class="sk-print-number"><?= sk_escape($row['grade']); ?></td><td class="sk-print-number"><?= sk_escape($row['bobot']); ?></td><?php } else { ?><td><?= sk_escape($row['nama_dosen']); ?></td><td><?= sk_escape($row['nama_hari'].' '.substr($row['mulai_jam'],0,5).'–'.substr($row['sampai_jam'],0,5)); ?><small><?= sk_escape($row['nama_ruangan']); ?></small></td><?php } ?></tr><?php } ?>
    </tbody></table>
    <div class="sk-print-totals"><span>Total SKS: <strong><?= $total['sks']; ?></strong></span><?php if ($results) { ?><span><?= $transcript?'IP kumulatif':'Indeks prestasi'; ?>: <strong><?= $total['ip']; ?></strong></span><?php } ?></div>
    <div class="sk-signatures"><div><p>Mengetahui,<br>Penasihat akademik</p><strong><?= sk_escape(($adviser['nama_dosen']??'') ?: 'Belum ditentukan'); ?></strong></div><div><p><?= sk_escape(($settings['kota']??'').' · '.date('d/m/Y')); ?><br>Mahasiswa</p><strong><?= sk_escape($student['nama_mhs'] ?: $sk_nim); ?></strong></div></div>
  </main>
</body></html>
