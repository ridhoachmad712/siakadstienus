<?php
require_once __DIR__.'/ui.php';
$sk_period = sk_period($koneksi);
$sk_person = null;
// Beberapa halaman lama memakai profil yang disediakan header.
// Pertahankan kontrak itu tanpa menimpa profil yang sudah dimuat halaman.
if ($level==='mhs') {
    if (!isset($tampil_mhs)) $tampil_mhs = siakad_baris($koneksi,'SELECT m.*, jk.jenis_kelamin, a.agama FROM mahasiswa m LEFT JOIN tbl_jk jk ON jk.id_jk=m.id_jk LEFT JOIN tbl_agama a ON a.id_agama=m.id_agama WHERE m.nim_npm=?','s',[$username]);
    $foto_mhs = $tampil_mhs['foto_mhs'] ?? '';
    $sk_person = ['nama'=>$tampil_mhs['nama_mhs'] ?? '', 'foto'=>$foto_mhs];
}
if ($level==='dosen') {
    if (!isset($tampil_dosen)) $tampil_dosen = siakad_baris($koneksi,'SELECT d.*, jk.jenis_kelamin, a.agama FROM dosen d LEFT JOIN tbl_jk jk ON jk.id_jk=d.id_jk LEFT JOIN tbl_agama a ON a.id_agama=d.id_agama WHERE d.nip=?','s',[$username]);
    $foto_dosen = $tampil_dosen['foto_dosen'] ?? '';
    $sk_person = ['nama'=>$tampil_dosen['nama_dosen'] ?? '', 'foto'=>$foto_dosen];
}
$sk_name = ($sk_person['nama'] ?? '') ?: $username;
$sk_photo = $sk_person['foto'] ?? '';
$sk_campus = ($r_pengaturan['nama_kampus'] ?? '') ?: 'STIE Nusantara';
?>
<header class="navbar navbar-expand-md sk-header d-print-none">
  <div class="container-xl sk-header-inner">
    <a href="dashboard" class="sk-brand" aria-label="Beranda SIAKAD">
      <?php if (!empty($r_pengaturan['logo_aplikasi'])) { ?>
        <img src="../img/<?= sk_escape(rawurlencode($r_pengaturan['logo_aplikasi'])); ?>" alt="" class="sk-brand-logo">
      <?php } else { ?><span class="sk-brand-mark" aria-hidden="true">S</span><?php } ?>
      <span><strong>SIAKAD</strong><small><?= sk_escape($sk_campus); ?></small></span>
    </a>
    <div class="sk-header-actions">
      <div class="sk-period"><span><?= isset($_GET['qwe']) ? 'Periode halaman' : 'Periode terbaru'; ?></span><strong><?= sk_escape(sk_period_label($sk_period)); ?></strong></div>
      <div class="nav-item dropdown">
        <button type="button" class="sk-account" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu akun <?= sk_escape($sk_name); ?>">
          <?php if ($sk_photo !== '') { ?><img class="sk-account-photo" src="<?= $level==='mhs'?'foto_mhs':'foto_dosen'; ?>/<?= sk_escape(rawurlencode($sk_photo)); ?>" alt=""><?php } else { ?><span class="sk-account-initial" aria-hidden="true"><?= sk_escape(function_exists('mb_substr')?mb_substr($sk_name,0,1):substr($sk_name,0,1)); ?></span><?php } ?>
          <span class="sk-account-label"><strong><?= sk_escape($sk_name); ?></strong><small><?= sk_escape(sk_role_label($level)); ?></small></span><span aria-hidden="true">⌄</span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
          <div class="sk-account-info"><?= sk_escape($username); ?><small><?= sk_escape(sk_role_label($level)); ?></small></div>
          <?php if (in_array($level,['mhs','dosen'],true)) { ?><a class="dropdown-item" href="dashboard?view=profil">Profil saya</a><?php } ?>
          <a class="dropdown-item" href="logout">Keluar</a>
        </div>
      </div>
      <button class="navbar-toggler sk-menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Buka menu navigasi"><span aria-hidden="true">☰</span></button>
    </div>
  </div>
</header>
