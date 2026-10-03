<?php
require_once __DIR__.'/academic-components.php';
$students=siakad_semua($koneksi,'SELECT m.nim_npm,m.nama_mhs,m.thn_masuk FROM mahasiswa m JOIN prodi_has_mhs p ON p.nim_npm=m.nim_npm WHERE p.kode_prodi=? ORDER BY m.thn_masuk DESC,m.nama_mhs','s',[$kode_prodi]);
?>
<form method="get" class="sk-filter-bar">
  <div><label class="form-label" for="policy-period">Tahun akademik</label><select class="form-select" id="policy-period" name="qwe" required><?php foreach ($sk_periods as $p) { ?><option value="<?= (int)$p['id_thn_akademik']; ?>" <?= (int)$p['id_thn_akademik']===$id_thn_akademik?'selected':''; ?>><?= sk_escape(sk_period_label($p)); ?></option><?php } ?></select></div>
  <div class="sk-filter-search"><label class="form-label" for="policy-student">Mahasiswa</label><select class="form-select" id="policy-student" name="qaz" required><option value="">Pilih mahasiswa</option><?php foreach ($students as $s) { ?><option value="<?= sk_escape($s['nim_npm']); ?>" <?= $sk_subject===$s['nim_npm']?'selected':''; ?>><?= sk_escape($s['nama_mhs'].' · '.$s['nim_npm'].' · '.$s['thn_masuk']); ?></option><?php } ?></select></div>
  <button type="submit" class="btn btn-primary">Tampilkan</button>
</form>
<?php
if ($sk_subject==='') { ?><div class="card card-body"><p class="mb-0">Pilih mahasiswa dan periode untuk mengatur semester serta izin pengambilan mata kuliah.</p></div><?php return; }
$context=siakad_krs_konteks($koneksi,$sk_subject,$kode_prodi,$id_thn_akademik);
$student=siakad_baris($koneksi,'SELECT nama_mhs,thn_masuk FROM mahasiswa WHERE nim_npm=?','s',[$sk_subject]);
$courses=siakad_semua($koneksi,'SELECT DISTINCT m.* FROM mata_kuliah m JOIN prodi_has_matkul p ON p.kode_matkul=m.kode_matkul JOIN jadwal_mengajar j ON j.kode_mk=m.kode_matkul AND j.kode_prodi=p.kode_prodi WHERE p.kode_prodi=? AND j.id_thn_akademik=? ORDER BY m.semester,m.nama_matkul','si',[$kode_prodi,$id_thn_akademik]);
$extras=array_filter($courses,fn($c)=>$context['valid'] && siakad_semester_matkul($c['semester'])!==null && siakad_semester_matkul($c['semester'])!==$context['semester']);
$permits=siakad_semua($koneksi,'SELECT i.*,m.nama_matkul,m.semester FROM krs_izin_matkul i JOIN mata_kuliah m ON m.kode_matkul=i.kode_matkul WHERE i.nim_npm=? AND i.id_thn_akademik=? AND i.kode_prodi=? ORDER BY i.aktif DESC,m.nama_matkul','sis',[$sk_subject,$id_thn_akademik,$kode_prodi]);
$history=siakad_semua($koneksi,'SELECT l.*,u.username pemberi FROM krs_kebijakan_log l JOIN user u ON u.id_user=l.id_pemberi WHERE l.nim_npm=? AND l.id_thn_akademik=? ORDER BY l.id DESC LIMIT 50','si',[$sk_subject,$id_thn_akademik]);
$hidden=function($action) use ($sk_subject,$id_thn_akademik) { siakad_csrf_field(); ?><input type="hidden" name="simpan_kebijakan" value="1"><input type="hidden" name="tindakan" value="<?= sk_escape($action); ?>"><input type="hidden" name="nim_npm" value="<?= sk_escape($sk_subject); ?>"><input type="hidden" name="id_thn_akademik" value="<?= $id_thn_akademik; ?>"><?php };
?>
<?php if (isset($_GET['saved'])) { ?><div class="alert alert-success" role="status">Pengaturan berhasil disimpan. KRS yang sudah tersimpan tetap dipertahankan.</div><?php } ?>
<section class="sk-identity card mb-4"><div><span>Mahasiswa</span><strong><?= sk_escape($student['nama_mhs']); ?></strong><small><?= sk_escape($sk_subject); ?> · Angkatan <?= (int)$student['thn_masuk']; ?></small></div><div><span>Semester mahasiswa</span><strong><?= $context['valid']?(int)$context['semester']:'Belum ditentukan'; ?></strong><small><?= $context['override']?'Penetapan prodi':'Otomatis berdasarkan angkatan'; ?></small></div><div><span>Semester otomatis</span><strong><?= $context['normal']??'Belum dapat dihitung'; ?></strong></div><div><a class="btn btn-outline-primary" href="mhs_krs?qwe=<?= $id_thn_akademik; ?>&qaz=<?= rawurlencode($sk_subject); ?>">Lihat KRS</a></div></section>
<div class="sk-policy-grid mb-4">
  <section class="card card-body"><h2 class="h3">Semester mahasiswa</h2><p>Gunakan penetapan khusus untuk cuti, transfer, atau penyesuaian. Pengaturan berlaku hanya pada periode yang dipilih.</p>
    <form method="post"><?php $hidden('semester'); ?>
      <label class="form-label" for="policy-semester">Semester</label><select class="form-select mb-3" name="semester" id="policy-semester"><option value="" <?= !$context['override']?'selected':''; ?>>Otomatis<?= $context['normal']?' · semester '.$context['normal']:''; ?></option><?php for ($i=1;$i<=20;$i++) { ?><option value="<?= $i; ?>" <?= $context['override'] && $context['semester']===$i?'selected':''; ?>>Semester <?= $i; ?></option><?php } ?></select>
      <label class="form-label" for="semester-reason">Alasan perubahan</label><textarea class="form-control mb-3" name="alasan" id="semester-reason" minlength="5" maxlength="500" required rows="3" placeholder="Jelaskan dasar penetapan semester."></textarea>
      <button class="btn btn-primary" type="submit">Simpan semester</button>
    </form>
  </section>
  <section class="card card-body"><h2 class="h3">Izin mata kuliah tambahan</h2><p>Izin hanya untuk mata kuliah yang ditawarkan prodi pada periode ini. Batas SKS, periode pengisian, dan bentrok jadwal tetap berlaku.</p>
    <form method="post"><?php $hidden('izin'); ?>
      <label class="form-label" for="permit-course">Mata kuliah</label><select class="form-select mb-3" id="permit-course" name="kode_matkul" required><option value="">Pilih mata kuliah</option><?php foreach ($extras as $c) { ?><option value="<?= sk_escape($c['kode_matkul']); ?>"><?= sk_escape($c['nama_matkul'].' · semester '.$c['semester'].' · '.$c['sks'].' SKS'); ?></option><?php } ?></select>
      <label class="form-label" for="permit-kind">Jenis izin</label><select class="form-select mb-3" id="permit-kind" name="jenis" required><option value="">Pilih jenis izin</option><option value="mengulang">Mengulang (semester lebih rendah)</option><option value="semester_atas">Mengambil semester atas</option><option value="penyesuaian">Persetujuan khusus</option></select>
      <label class="form-label" for="permit-reason">Alasan persetujuan</label><textarea class="form-control mb-3" id="permit-reason" name="alasan" minlength="5" maxlength="500" required rows="3" placeholder="Jelaskan alasan izin dan dasar persetujuan."></textarea>
      <?php if (!$extras) { ?><p class="text-muted">Tidak ada penawaran lintas semester yang dapat diizinkan. Pastikan semester mahasiswa sudah ditentukan.</p><?php } ?>
      <button class="btn btn-primary" type="submit" <?= !$extras?'disabled':''; ?>>Berikan izin</button>
    </form>
  </section>
</div>
<section class="card mb-4"><div class="sk-section-head"><div><h2>Izin tercatat</h2><p>Pencabutan menghentikan pilihan baru. KRS yang sudah tersimpan perlu ditinjau terpisah.</p></div></div><div class="table-responsive"><table class="table sk-data-table sk-mobile-rows"><thead><tr><th>Mata kuliah</th><th>Izin / alasan</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php if (!$permits) sk_empty_row(4,'Belum ada izin mata kuliah tambahan.'); ?>
<?php foreach ($permits as $index=>$permit) { ?><tr><td data-label="Mata kuliah"><strong><?= sk_escape($permit['nama_matkul']); ?></strong><small><?= sk_escape($permit['kode_matkul'].' · semester '.$permit['semester']); ?></small></td><td data-label="Izin / alasan"><?= sk_escape(['mengulang'=>'Mengulang','semester_atas'=>'Semester atas','penyesuaian'=>'Persetujuan khusus'][$permit['jenis']]??$permit['jenis']); ?><small><?= sk_escape($permit['alasan']); ?></small></td><td data-label="Status"><?= $permit['aktif']?'Aktif':'Dicabut'; ?></td><td data-label="Aksi"><?php if ($permit['aktif']) { ?><form method="post"><?php $hidden('cabut'); ?><input type="hidden" name="kode_matkul" value="<?= sk_escape($permit['kode_matkul']); ?>"><label class="form-label" for="revoke-reason-<?= $index; ?>">Alasan pencabutan</label><input class="form-control mb-2" name="alasan" id="revoke-reason-<?= $index; ?>" minlength="5" maxlength="500" required><button class="btn btn-outline-danger btn-sm" type="submit">Cabut izin</button></form><?php } else { ?>—<?php } ?></td></tr><?php } ?>
</tbody></table></div></section>
<section class="card"><div class="sk-section-head"><div><h2>Riwayat pengaturan</h2><p>50 perubahan terbaru pada periode ini.</p></div></div><div class="table-responsive"><table class="table sk-data-table sk-mobile-rows"><thead><tr><th>Waktu / petugas</th><th>Perubahan</th><th>Alasan</th></tr></thead><tbody>
<?php if (!$history) sk_empty_row(3,'Belum ada perubahan tercatat.'); ?>
<?php foreach ($history as $entry) { $detail=json_decode($entry['rincian'],true)?:[]; ?><tr><td data-label="Waktu / petugas"><?= sk_escape($entry['dibuat_pada']); ?><small><?= sk_escape($entry['pemberi']); ?></small></td><td data-label="Perubahan"><?= sk_escape(['semester'=>'Penetapan semester','izin'=>'Pemberian izin','cabut'=>'Pencabutan izin'][$entry['aksi']]??$entry['aksi']); ?><small><?= sk_escape($entry['aksi']==='semester'?'Semester '.($detail['semester_sebelumnya']??'belum ditentukan').' → '.$detail['semester']:($detail['kode_matkul']??'')); ?></small></td><td data-label="Alasan"><?= sk_escape($detail['alasan']??''); ?></td></tr><?php } ?>
</tbody></table></div></section>
