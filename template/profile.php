<?php
require_once __DIR__.'/ui.php';
$sk_student_profile=$level==='mhs';
$sk_profile=$sk_student_profile?$tampil_mhs:$tampil_dosen;
$sk_photo=$sk_profile[$sk_student_profile?'foto_mhs':'foto_dosen'] ?? '';
$sk_full_name=$sk_profile[$sk_student_profile?'nama_mhs':'nama_dosen'] ?? $username;
$sk_genders=siakad_semua($koneksi,'SELECT id_jk,jenis_kelamin FROM tbl_jk ORDER BY id_jk');
$sk_religions=siakad_semua($koneksi,'SELECT id_agama,agama FROM tbl_agama ORDER BY id_agama');
function sk_profile_field($data,$key,$label,$type='text',$choices=[]) {
    $id='profile-'.$key; $value=$data[$key]??'';
    ?><div><label class="form-label" for="<?= $id; ?>"><?= sk_escape($label); ?></label><?php
    if ($type==='select') { ?><select class="form-select" id="<?= $id; ?>" name="<?= $key; ?>"><?php foreach ($choices as $code=>$text) { ?><option value="<?= sk_escape($code); ?>" <?= (string)$code===(string)$value?'selected':''; ?>><?= sk_escape($text); ?></option><?php } ?></select><?php }
    elseif ($type==='textarea') { ?><textarea class="form-control" id="<?= $id; ?>" name="<?= $key; ?>" rows="3"><?= sk_escape($value); ?></textarea><?php }
    else { ?><input class="form-control" type="<?= $type==='readonly'?'text':$type; ?>" id="<?= $id; ?>" name="<?= $key; ?>" value="<?= sk_escape($value); ?>" <?= $type==='readonly'?'readonly':''; ?>><?php } ?></div><?php
}
?>
<div class="sk-profile-grid">
  <aside class="card sk-profile-card">
    <?php if ($sk_photo) { ?><img class="sk-profile-photo" src="<?= $sk_student_profile?'foto_mhs':'foto_dosen'; ?>/<?= sk_escape(rawurlencode($sk_photo)); ?>" alt="Foto profil <?= sk_escape($sk_full_name); ?>"><?php } else { ?><span class="sk-profile-placeholder" aria-hidden="true"><?= sk_escape(function_exists('mb_substr')?mb_substr($sk_full_name,0,1):substr($sk_full_name,0,1)); ?></span><?php } ?>
    <h3><?= sk_escape($sk_full_name ?: $username); ?></h3><p><?= sk_escape($username); ?> · <?= sk_escape(sk_role_label($level)); ?></p>
    <div class="sk-actions"><button class="btn btn-secondary" type="button" data-bs-toggle="modal" data-bs-target="#profile-photo">Ubah foto</button><button class="btn btn-secondary" type="button" data-bs-toggle="modal" data-bs-target="#profile-password">Ubah password</button></div>
  </aside>
  <form method="post" class="card"><?php siakad_csrf_field(); ?><input type="hidden" name="<?= $sk_student_profile?'simpan_mhs':'simpan_dosen'; ?>" value="1">
    <div class="card-body">
      <fieldset class="sk-form-section"><legend>Data pribadi</legend><div class="sk-form-grid">
        <?php sk_profile_field($sk_profile,$sk_student_profile?'nim_npm':'nip',$sk_student_profile?'NIM / NPM':'NIP / NIDN','readonly'); sk_profile_field($sk_profile,$sk_student_profile?'nama_mhs':'nama_dosen','Nama lengkap'); ?>
        <?php sk_profile_field($sk_profile,'id_jk','Jenis kelamin','select',array_column($sk_genders,'jenis_kelamin','id_jk')); sk_profile_field($sk_profile,'id_agama','Agama','select',array_column($sk_religions,'agama','id_agama')); ?>
        <?php sk_profile_field($sk_profile,$sk_student_profile?'tempat_lhr':'tmp_lhr_dosen','Tempat lahir'); sk_profile_field($sk_profile,$sk_student_profile?'tgl_lhr_mhs':'tgl_lhr_dosen','Tanggal lahir','date'); ?>
      </div></fieldset>
      <?php if ($sk_student_profile) { ?><fieldset class="sk-form-section"><legend>Informasi akademik</legend><div class="sk-form-grid"><?php sk_profile_field($sk_profile,'thn_masuk','Angkatan','number'); sk_profile_field($sk_profile,'lulusan_jalur','Jalur masuk'); sk_profile_field($sk_profile,'sekolah_asal','Sekolah asal'); ?></div></fieldset><?php } ?>
      <fieldset class="sk-form-section"><legend>Kontak</legend><div class="sk-form-grid"><?php sk_profile_field($sk_profile,'email','Email','email'); sk_profile_field($sk_profile,$sk_student_profile?'no_telp_mhs':'no_telp','Nomor HP / WhatsApp','tel'); ?><div class="sk-form-wide"><?php sk_profile_field($sk_profile,$sk_student_profile?'alamat_mhs':'alamat','Alamat','textarea'); ?></div></div></fieldset>
      <?php if ($sk_student_profile) { ?>
      <fieldset class="sk-form-section"><legend>Data keluarga</legend><div class="sk-form-grid"><?php sk_profile_field($orgtua ?: [],'no_kk','Nomor kartu keluarga'); sk_profile_field($orgtua ?: [],'no_telp_orgtua','Nomor HP orang tua','tel'); ?><div class="sk-form-wide"><?php sk_profile_field($orgtua ?: [],'alamat_org_tua','Alamat orang tua','textarea'); ?></div></div></fieldset>
      <?php foreach (['ayah'=>'Ayah','ibu'=>'Ibu'] as $key=>$label) { ?><fieldset class="sk-form-section"><legend>Data <?= $label; ?></legend><div class="sk-form-grid"><?php foreach (['nama'=>'Nama lengkap','tmp_lhr'=>'Tempat lahir','tgl_lhr'=>'Tanggal lahir','pekerjaan'=>'Pekerjaan','penghasilan'=>'Penghasilan','pend'=>'Pendidikan'] as $prefix=>$title) sk_profile_field($orgtua ?: [],$prefix.'_'.$key,$title,$prefix==='tgl_lhr'?'date':'text'); ?></div></fieldset><?php } ?>
      <?php } ?>
    </div>
    <div class="sk-save-bar"><small>Pastikan biodata dan kontak Anda sudah sesuai.</small><button class="btn btn-primary" type="submit">Simpan biodata</button></div>
  </form>
</div>
<div class="modal fade" id="profile-photo" tabindex="-1" aria-labelledby="profile-photo-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post" enctype="multipart/form-data"><?php siakad_csrf_field(); ?><input type="hidden" name="<?= $sk_student_profile?'ubahfotomhs':'ubahfotodosen'; ?>" value="1"><div class="modal-header"><h3 class="modal-title" id="profile-photo-title">Ubah foto profil</h3><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><label class="form-label" for="profile-photo-input">Foto JPG atau PNG</label><input class="form-control" type="file" id="profile-photo-input" name="file_foto" accept=".jpg,.jpeg,.png" required></div><div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" type="submit">Simpan foto</button></div></form></div></div></div>
<div class="modal fade" id="profile-password" tabindex="-1" aria-labelledby="profile-password-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post"><?php siakad_csrf_field(); ?><input type="hidden" name="ubahpass" value="1"><div class="modal-header"><h3 class="modal-title" id="profile-password-title">Ubah password</h3><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label" for="new-password">Password baru</label><input class="form-control" id="new-password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required></div><div><label class="form-label" for="repeat-password">Konfirmasi password</label><input class="form-control" id="repeat-password" name="password2" type="password" minlength="8" maxlength="72" autocomplete="new-password" required></div></div><div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" type="submit">Simpan password</button></div></form></div></div></div>
