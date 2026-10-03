<?php
require_once __DIR__.'/academic-components.php';
$sk_grades=in_array($sk_page,['input_nilai','input_nilai_dosen'],true);
$sk_edit=$sk_page==='buat_jadwal';
$sk_scope=$level==='dosen'?'j.nip=?':'j.kode_prodi=?';
$sk_owner=$level==='dosen'?$username:$kode_prodi;
$sk_classes=siakad_semua($koneksi,"SELECT j.*,m.nama_matkul,m.sks,m.semester,d.nama_dosen,h.nama_hari,r.nama_ruangan,(SELECT COUNT(*) FROM krs_mhs k WHERE k.id_jadwal=j.id_jadwal AND k.id_thn_akademik=j.id_thn_akademik) peserta,(SELECT COUNT(*) FROM khs_mhs g WHERE g.id_jadwal=j.id_jadwal AND g.id_thn_akademik=j.id_thn_akademik AND g.grade<>'' AND g.grade<>'-') dinilai FROM jadwal_mengajar j LEFT JOIN mata_kuliah m ON m.kode_matkul=j.kode_mk LEFT JOIN dosen d ON d.nip=j.nip LEFT JOIN tbl_hari h ON h.id_hari=j.id_hari LEFT JOIN tbl_ruangan r ON r.kode_ruangan=j.kode_ruangan WHERE $sk_scope AND j.id_thn_akademik=? ORDER BY j.id_hari,j.mulai_jam,m.nama_matkul",'si',[$sk_owner,$id_thn_akademik]);
$sk_status=sk_period_status($koneksi,'jadwal_input_nilai',$id_thn_akademik);
sk_period_filter($sk_periods,$id_thn_akademik);
?>
<?php if ($sk_grades) { ?><div class="sk-notice"><span class="sk-status"><?= sk_escape($sk_status['label']); ?></span><p><?= sk_escape($sk_status['note']); ?> Nilai akhir dimasukkan langsung oleh dosen.</p></div><?php } ?>
<section class="card">
  <div class="sk-section-head"><div><h2><?= $sk_grades?'Daftar kelas':'Jadwal perkuliahan'; ?></h2><p><?= count($sk_classes); ?> kelas · <?= sk_escape(sk_period_label($sk_period)); ?></p></div><div class="sk-actions">
    <?php if ($sk_edit) { ?><button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#schedule-editor" <?= !$id_thn_akademik?'disabled':''; ?>>Tambah jadwal</button><?php } ?>
    <?php if (!$sk_grades) { ?><button class="btn btn-secondary d-print-none" type="button" onclick="window.print()">Cetak jadwal</button><?php } ?>
  </div></div>
  <?php if (!$sk_grades) { sk_schedule_cards($sk_classes,$sk_edit,$id_thn_akademik); } else { ?>
  <div class="table-responsive"><table class="table sk-data-table sk-mobile-rows"><thead><tr><th>Mata kuliah</th><th>Jadwal</th><th>Dosen / ruangan</th><th>Peserta</th><?php if ($sk_grades) { ?><th>Pengisian nilai</th><?php } ?><th>Aksi</th></tr></thead><tbody>
  <?php if (!$sk_classes) sk_empty_row($sk_grades?6:5,'Belum ada jadwal pada periode ini.'); ?>
  <?php foreach ($sk_classes as $row) { ?><tr><td data-label="Mata kuliah"><strong><?= sk_escape($row['nama_matkul'] ?: $row['kode_mk']); ?></strong><small><?= sk_escape($row['kode_mk']); ?> · <?= (int)$row['sks']; ?> SKS · Semester <?= sk_escape($row['semester']); ?></small></td><td data-label="Jadwal"><?= sk_escape($row['nama_hari'] ?: 'Hari belum diatur'); ?><small><?= sk_escape(substr($row['mulai_jam'],0,5).'–'.substr($row['sampai_jam'],0,5)); ?></small></td><td data-label="Dosen / ruangan"><?= sk_escape($row['nama_dosen'] ?: $row['nip']); ?><small><?= sk_escape($row['nama_ruangan'] ?: 'Ruangan belum diatur'); ?></small></td><td data-label="Peserta"><?= (int)$row['peserta']; ?> mahasiswa</td><?php if ($sk_grades) { ?><td data-label="Pengisian nilai"><?= (int)$row['dinilai']; ?> / <?= (int)$row['peserta']; ?><small>mahasiswa sudah dinilai</small><progress class="sk-class-progress" value="<?= min((int)$row['dinilai'],(int)$row['peserta']); ?>" max="<?= max(1,(int)$row['peserta']); ?>" aria-label="Progres nilai <?= sk_escape($row['nama_matkul'] ?: $row['kode_mk']); ?>"></progress></td><?php } ?><td data-label="Aksi"><div class="sk-actions">
    <?php if ($sk_grades) { ?><a class="btn btn-outline-primary btn-sm" href="get_input_nilai?qwe=<?= (int)$row['id_jadwal']; ?>"><?= $sk_status['open']?'Input nilai':'Lihat nilai'; ?></a><?php } else { ?><a class="btn btn-secondary btn-sm" href="get_daftar_mahasiswa?qwe=<?= (int)$row['id_jadwal']; ?>">Peserta</a><?php } ?>
    <?php if ($sk_edit) { ?><a class="btn btn-outline-danger btn-sm" href="buat_jadwal?qwe=<?= $id_thn_akademik; ?>&id=<?= (int)$row['id_jadwal']; ?>&aksi=hapus&id_thn_akademik=<?= $id_thn_akademik; ?>">Hapus</a><?php } ?>
  </div></td></tr><?php } ?></tbody></table></div>
  <?php } ?>
</section>
<?php if ($sk_edit) {
    $sk_courses=siakad_semua($koneksi,'SELECT m.* FROM prodi_has_matkul p JOIN mata_kuliah m ON m.kode_matkul=p.kode_matkul WHERE p.kode_prodi=? ORDER BY m.semester,m.nama_matkul','s',[$kode_prodi]);
    $sk_lecturers=siakad_semua($koneksi,'SELECT d.* FROM prodi_has_dosen p JOIN dosen d ON d.nip=p.nip WHERE p.kode_prodi=? ORDER BY d.nama_dosen','s',[$kode_prodi]);
    $sk_rooms=siakad_semua($koneksi,'SELECT DISTINCT r.* FROM tbl_ruangan r JOIN fakultas_has_jurusan f ON f.kode_fakultas=r.kode_fakultas WHERE f.kode_prodi=? ORDER BY r.nama_ruangan','s',[$kode_prodi]);
    $sk_days=siakad_semua($koneksi,'SELECT * FROM tbl_hari ORDER BY id_hari');
?>
<div class="modal fade" id="schedule-editor" tabindex="-1" aria-labelledby="schedule-editor-title" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
  <form method="post"><?php siakad_csrf_field(); ?><input type="hidden" name="id_thn_akademik" value="<?= $id_thn_akademik; ?>"><input type="hidden" name="simpan" value="1">
    <div class="modal-header"><div><h2 class="modal-title" id="schedule-editor-title">Tambah jadwal kuliah</h2><small class="text-muted"><?= sk_escape(sk_period_label($sk_period)); ?></small></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body"><div class="sk-form-grid">
      <div class="sk-form-wide"><label class="form-label" for="schedule-course">Mata kuliah</label><select id="schedule-course" class="form-select" name="kode_mk" required><option value="">Pilih mata kuliah</option><?php foreach ($sk_courses as $row) { ?><option value="<?= sk_escape($row['kode_matkul']); ?>"><?= sk_escape($row['kode_matkul'].' · '.$row['nama_matkul'].' · '.$row['sks'].' SKS'); ?></option><?php } ?></select></div>
      <div><label class="form-label" for="schedule-lecturer">Dosen</label><select id="schedule-lecturer" name="nip" class="form-select" required><option value="">Pilih dosen</option><?php foreach ($sk_lecturers as $row) { ?><option value="<?= sk_escape($row['nip']); ?>"><?= sk_escape($row['nama_dosen'] ?: $row['nip']); ?></option><?php } ?></select></div>
      <div><label class="form-label" for="schedule-room">Ruangan</label><select id="schedule-room" name="kode_ruangan" class="form-select" required><option value="">Pilih ruangan</option><?php foreach ($sk_rooms as $row) { ?><option value="<?= (int)$row['kode_ruangan']; ?>"><?= sk_escape($row['nama_ruangan'] ?: $row['kode_ruangan']); ?></option><?php } ?></select></div>
      <div class="sk-form-wide"><label class="form-label" for="schedule-day">Hari</label><select id="schedule-day" name="id_hari" class="form-select" required><option value="">Pilih hari</option><?php foreach ($sk_days as $row) { ?><option value="<?= (int)$row['id_hari']; ?>"><?= sk_escape($row['nama_hari']); ?></option><?php } ?></select></div>
      <div><label class="form-label" for="schedule-start">Jam mulai</label><input class="form-control" id="schedule-start" name="mulai_jam" type="time" required></div><div><label class="form-label" for="schedule-end">Jam selesai</label><input class="form-control" id="schedule-end" name="sampai_jam" type="time" required></div>
    </div><p class="form-hint mt-3 mb-0">Waktu kuliah diperiksa terhadap jadwal dosen dan penggunaan ruangan.</p></div>
    <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan jadwal</button></div>
  </form>
</div></div></div>
<?php } ?>
