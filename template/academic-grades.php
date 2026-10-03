<?php
require_once __DIR__.'/academic-components.php';
$sk_class=siakad_baris($koneksi,'SELECT j.*,m.nama_matkul,m.sks,m.semester,d.nama_dosen,h.nama_hari,r.nama_ruangan FROM jadwal_mengajar j LEFT JOIN mata_kuliah m ON m.kode_matkul=j.kode_mk LEFT JOIN dosen d ON d.nip=j.nip LEFT JOIN tbl_hari h ON h.id_hari=j.id_hari LEFT JOIN tbl_ruangan r ON r.kode_ruangan=j.kode_ruangan WHERE j.id_jadwal=?','i',[(int)($_GET['qwe']??0)]);
if (!siakad_jadwal_diizinkan($siakad_user,$sk_class)) siakad_tolak('Kelas tidak ditemukan dalam lingkup akun Anda.');
$sk_back=($level==='dosen'?'input_nilai_dosen':'input_nilai').'?qwe='.(int)$sk_class['id_thn_akademik'];
$sk_status=sk_period_status($koneksi,'jadwal_input_nilai',(int)$sk_class['id_thn_akademik']);
$sk_students=siakad_semua($koneksi,'SELECT g.*,m.nama_mhs FROM khs_mhs g LEFT JOIN mahasiswa m ON m.nim_npm=g.nim_npm WHERE g.id_jadwal=? AND g.id_thn_akademik=? AND g.kode_prodi=? ORDER BY g.nim_npm','iis',[(int)$sk_class['id_jadwal'],(int)$sk_class['id_thn_akademik'],$sk_class['kode_prodi']]);
$sk_grades=siakad_semua($koneksi,'SELECT * FROM tbl_grade');
$sk_done=count(array_filter($sk_students,fn($s)=>$s['grade']!=='' && $s['grade']!=='-'));
?>
<a href="<?= sk_escape($sk_back); ?>" class="sk-back-link">← Daftar kelas</a>
<section class="card sk-identity mb-4"><div><span>Mata kuliah</span><strong><?= sk_escape($sk_class['nama_matkul'] ?: $sk_class['kode_mk']); ?></strong><small><?= sk_escape($sk_class['kode_mk']); ?> · <?= (int)$sk_class['sks']; ?> SKS</small></div><div><span>Dosen</span><strong><?= sk_escape($sk_class['nama_dosen'] ?: $sk_class['nip']); ?></strong></div><div><span>Jadwal</span><strong><?= sk_escape(($sk_class['nama_hari'] ?: 'Hari belum diatur').' · '.substr($sk_class['mulai_jam'],0,5).'–'.substr($sk_class['sampai_jam'],0,5)); ?></strong><small><?= sk_escape($sk_class['nama_ruangan']); ?></small></div></section>
<div class="sk-notice"><span class="sk-status"><?= sk_escape($sk_status['label']); ?></span><p><?= sk_escape($sk_status['note']); ?></p></div>
<form method="post" id="grade-form" data-grade-config="<?= sk_escape(json_encode($sk_grades)); ?>">
  <?php siakad_csrf_field(); ?><input type="hidden" name="simpan_nilai" value="1">
  <section class="card"><div class="sk-section-head"><div><h2>Nilai mahasiswa</h2><p><?= count($sk_students); ?> peserta · <?= $sk_done; ?> sudah dinilai</p></div></div>
    <div class="table-responsive"><table class="table sk-data-table sk-mobile-rows"><thead><tr><th>Mahasiswa</th><th>Nilai akhir</th><th>Grade</th></tr></thead><tbody>
    <?php if (!$sk_students) sk_empty_row(3,'Belum ada peserta kelas.'); ?>
    <?php foreach ($sk_students as $index=>$row) { ?><tr><td data-label="Mahasiswa"><strong><?= sk_escape($row['nama_mhs'] ?: $row['nim_npm']); ?></strong><small><?= sk_escape($row['nim_npm']); ?></small><input type="hidden" name="nim_npm[]" value="<?= sk_escape($row['nim_npm']); ?>"></td><td data-label="Nilai akhir"><label class="visually-hidden" for="grade-<?= $index; ?>">Nilai akhir <?= sk_escape($row['nama_mhs'] ?: $row['nim_npm']); ?></label><input type="number" id="grade-<?= $index; ?>" name="nilai_uas[]" class="form-control sk-grade-input" value="<?= $row['grade']==='-'?'':sk_escape($row['nilai_uas']); ?>" min="0" max="100" step="0.01" placeholder="0–100" required <?= !$sk_status['open']?'disabled':''; ?>></td><td data-label="Grade"><span class="sk-status" data-grade-preview><?= sk_escape($row['grade'] ?: '—'); ?></span></td></tr><?php } ?>
    </tbody></table></div>
    <div class="sk-save-bar"><div><strong>Periksa nilai sebelum menyimpan</strong><small id="grade-progress" aria-live="polite">Nilai akhir berasal langsung dari dosen.</small></div><div class="sk-actions"><a class="btn btn-secondary" href="<?= sk_escape($sk_back); ?>">Kembali</a><button type="submit" class="btn btn-primary" <?= !$sk_status['open']||!$sk_students?'disabled':''; ?>>Simpan nilai</button></div></div>
  </section>
</form>
