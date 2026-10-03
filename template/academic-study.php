<?php
require_once __DIR__.'/academic-components.php';
$sk_transcript=$sk_page==='transkip';
$sk_results=in_array($sk_page,['khs','mhs_khs','transkip'],true);
$sk_pick=$sk_page==='ambil_jadwal';
$sk_schedule_only=$sk_page==='jadwal_kuliah';
if (!siakad_mahasiswa_diizinkan($koneksi,$siakad_user,$sk_subject)) siakad_tolak('Mahasiswa tidak ditemukan dalam lingkup akun Anda.');
$sk_student=siakad_baris($koneksi,'SELECT m.*,p.nama_prodi,p.jenjang FROM mahasiswa m JOIN prodi_has_mhs pm ON pm.nim_npm=m.nim_npm LEFT JOIN prodi p ON p.kode_prodi=pm.kode_prodi WHERE m.nim_npm=? AND pm.kode_prodi=?','ss',[$sk_subject,$kode_prodi]);
if (!$sk_student) siakad_tolak('Data mahasiswa belum terdaftar pada program studi.',404);
$sk_adviser=siakad_baris($koneksi,'SELECT d.nama_dosen FROM mhs_has_pa a LEFT JOIN dosen d ON d.nip=a.nip WHERE a.nim_npm=?','s',[$sk_subject]);
$sk_rows=sk_study_data($koneksi,$sk_subject,$kode_prodi,$id_thn_akademik,$sk_transcript,$sk_results);
$sk_total=sk_study_totals($sk_transcript?array_filter($sk_rows,fn($row)=>$row['kode_prodi']===$kode_prodi):$sk_rows);
$sk_limit=siakad_baris($koneksi,'SELECT sks FROM pengaturan_sks_mhs WHERE nim_npm=? AND id_thn_akademik=?','si',[$sk_subject,$id_thn_akademik]);
$sk_status=sk_period_status($koneksi,'jadwal_penawaran',$id_thn_akademik);
$sk_print=$sk_transcript?'transkip':($sk_results?'khs':($sk_schedule_only?'jadwalkuliah':'krs'));
$sk_print_url='cetak/'.$sk_print.'?qwe='.$id_thn_akademik.($level==='Jurusan/Prodi'?'&nim_npm='.rawurlencode($sk_subject):'');
$sk_krs_context=(!$sk_results && !$sk_schedule_only)?siakad_krs_konteks($koneksi,$sk_subject,$kode_prodi,$id_thn_akademik):null;
$sk_can_pick=$level==='mhs' && $sk_status['open'] && $sk_limit && $sk_krs_context && $sk_krs_context['valid'] && $sk_krs_context['active'];
$sk_review=[];
if ($sk_krs_context && $sk_krs_context['valid']) {
    $review_context=$sk_krs_context; $review_context['active']=true;
    foreach ($sk_rows as $row) if (siakad_krs_jenis_pilihan($review_context,$row)===null) $sk_review[]=$row;
}
?>
<?php if (!$sk_transcript && !$sk_pick) sk_period_filter($sk_periods,$id_thn_akademik,$level==='Jurusan/Prodi'?['qaz'=>$sk_subject]:[]); ?>
<section class="sk-identity card mb-4" aria-label="Identitas mahasiswa">
  <div><span>Mahasiswa</span><strong><?= sk_escape($sk_student['nama_mhs'] ?: $sk_subject); ?></strong><small><?= sk_escape($sk_subject); ?> · Angkatan <?= sk_escape($sk_student['thn_masuk']); ?></small></div>
  <div><span>Program studi</span><strong><?= sk_escape(trim($sk_student['jenjang'].' '.$sk_student['nama_prodi']) ?: $kode_prodi); ?></strong></div>
  <?php if ($sk_krs_context) { ?><div><span>Semester mahasiswa</span><strong><?= $sk_krs_context['valid']?(int)$sk_krs_context['semester']:'Belum ditentukan'; ?></strong><small><?= $sk_krs_context['override']?'Ditetapkan program studi':'Berdasarkan angkatan dan periode'; ?></small></div><?php } ?>
  <div><span>Penasihat akademik</span><strong><?= sk_escape(($sk_adviser['nama_dosen']??'') ?: 'Belum ditentukan'); ?></strong></div>
</section>
<?php if ($sk_krs_context && $sk_krs_context['message']) { ?><div class="alert alert-warning"><?= sk_escape($sk_krs_context['message']); ?></div><?php } ?>
<?php if ($sk_review) { ?><div class="alert alert-warning"><strong><?= count($sk_review); ?> mata kuliah perlu ditinjau program studi.</strong> Semester mata kuliah berbeda dan belum ada izin tercatat. KRS yang sudah tersimpan tetap dipertahankan.</div><?php } ?>
<?php if ($sk_krs_context && $level==='Jurusan/Prodi') { ?><p><a class="btn btn-outline-primary" href="pengaturan_krs?qwe=<?= $id_thn_akademik; ?>&qaz=<?= rawurlencode($sk_subject); ?>">Atur semester dan izin mata kuliah</a></p><?php } ?>
<?php if ($sk_results) { ?><div class="sk-inline-summary"><div><span>Total SKS</span><strong><?= $sk_total['sks']; ?></strong></div><div><span>SKS sudah dinilai</span><strong><?= $sk_total['graded']; ?></strong></div><div><span><?= $sk_transcript?'IP kumulatif':'Indeks prestasi'; ?></span><strong><?= $sk_total['ip']; ?></strong></div></div><?php } ?>
<div class="<?= !$sk_results && !$sk_schedule_only?'sk-study-grid':''; ?>">
  <section class="card">
    <div class="sk-section-head"><div><h2><?= $sk_pick?'Mata kuliah tersedia':($sk_results?'Hasil studi':($sk_schedule_only?'Jadwal perkuliahan':'Mata kuliah terpilih')); ?></h2><p><?= $sk_transcript?'Seluruh periode yang sudah dinilai':sk_escape(sk_period_label($sk_period)); ?></p></div><div class="sk-actions">
      <?php if (!$sk_pick) { ?><a class="btn btn-secondary" target="_blank" rel="noopener" href="<?= sk_escape($sk_print_url); ?>">Cetak <?= $sk_schedule_only?'jadwal':($sk_transcript?'transkrip':($sk_results?'KHS':'KRS')); ?></a><?php } else { ?><a class="btn btn-secondary" href="krs?qwe=<?= $id_thn_akademik; ?>">Kembali ke KRS</a><?php } ?>
    </div></div>
    <?php if ($sk_pick) {
      $sk_groups=siakad_krs_penawaran($koneksi,$username,$kode_prodi,$id_thn_akademik,$sk_krs_context);
      ?>
      <form method="post" id="course-selection" data-existing-sks="<?= $sk_total['sks']; ?>" data-limit="<?= (int)($sk_limit['sks']??0); ?>" data-open="<?= $sk_can_pick?'1':'0'; ?>">
        <?php siakad_csrf_field(); ?><input type="hidden" name="simpan" value="1">
        <?php foreach ($sk_groups as $group=>$sk_available) { ?>
        <div class="sk-section-head"><div><h3><?= $group==='reguler'?'Mata kuliah semester Anda':'Mata kuliah tambahan yang diizinkan'; ?></h3><p><?= $group==='reguler'?'Penawaran sesuai semester mahasiswa.':'Izin hanya berlaku pada periode ini; batas SKS dan bentrok jadwal tetap diperiksa.'; ?></p></div></div>
        <div class="table-responsive"><table class="table sk-data-table sk-mobile-rows"><thead><tr><th>Pilih</th><th>Mata kuliah</th><th>Jadwal / dosen</th><th>SKS</th></tr></thead><tbody>
        <?php if (!$sk_available) sk_empty_row(4,$group==='reguler'?'Tidak ada penawaran tambahan yang sesuai semester Anda.':'Belum ada izin mata kuliah tambahan dari program studi.'); ?>
        <?php foreach ($sk_available as $row) { ?><tr><td data-label="Pilih"><input type="checkbox" class="form-check-input" name="pilih[]" value="<?= (int)$row['id_jadwal']; ?>" data-sks="<?= (int)$row['sks']; ?>" aria-label="Pilih <?= sk_escape($row['nama_matkul'].' '.$row['nama_hari'].' '.$row['mulai_jam']); ?>" <?= !$sk_can_pick?'disabled':''; ?>></td><td data-label="Mata kuliah"><strong><?= sk_escape($row['nama_matkul']); ?></strong><small><?= sk_escape($row['kode_mk']); ?> · Semester <?= sk_escape($row['semester']); ?></small><?php if ($group==='tambahan') { ?><span class="sk-status"><?= sk_escape($row['jenis_izin']); ?></span><?php } ?></td><td data-label="Jadwal / dosen"><?= sk_escape(($row['nama_hari'] ?: 'Hari belum diatur').' · '.substr($row['mulai_jam'],0,5).'–'.substr($row['sampai_jam'],0,5)); ?><small><?= sk_escape($row['nama_dosen']); ?> · <?= sk_escape($row['nama_ruangan']); ?></small></td><td data-label="SKS"><?= (int)$row['sks']; ?></td></tr><?php } ?>
        </tbody></table></div>
        <?php } ?>
      </form>
    <?php } else { ?>
      <div class="table-responsive"><table class="table sk-data-table sk-mobile-rows"><thead><tr><th>Mata kuliah</th><th><?= $sk_results?'Periode / semester':'Jadwal / dosen'; ?></th><th>SKS</th><?php if ($sk_results) { ?><th>Nilai akhir</th><th>Grade</th><th>Bobot</th><?php } elseif (!$sk_schedule_only) { ?><th>Aksi</th><?php } ?></tr></thead><tbody>
      <?php if (!$sk_rows) sk_empty_row($sk_results?6:($sk_schedule_only?3:4),$sk_transcript?'Belum ada hasil studi yang sudah dinilai.':'Belum ada mata kuliah pada periode ini.'); ?>
      <?php foreach ($sk_rows as $row) { ?><tr><td data-label="Mata kuliah"><strong><?= sk_escape($row['nama_matkul'] ?: $row['kode_mk']); ?></strong><small><?= sk_escape($row['kode_mk']); ?><?= $sk_krs_context?' · Semester '.sk_escape($row['semester']):''; ?></small><?php if (isset($review_context) && siakad_krs_jenis_pilihan($review_context,$row)===null) { ?><span class="sk-status">Perlu ditinjau</span><?php } ?></td><td data-label="<?= $sk_results?'Periode / semester':'Jadwal / dosen'; ?>"><?php if ($sk_results) { ?><?= sk_escape(trim($row['thn_akademik'].' '.$row['ket'])); ?><small>Semester <?= sk_escape($row['semester']); ?></small><?php } else { ?><?= sk_escape(($row['nama_hari'] ?: 'Hari belum diatur').' · '.substr($row['mulai_jam'],0,5).'–'.substr($row['sampai_jam'],0,5)); ?><small><?= sk_escape($row['nama_dosen']); ?> · <?= sk_escape($row['nama_ruangan']); ?></small><?php } ?></td><td data-label="SKS"><?= (int)$row['sks']; ?></td>
        <?php if ($sk_results) { ?><td data-label="Nilai akhir"><?= sk_escape($row['nilai_akhir'] ?? '—'); ?></td><td data-label="Grade"><span class="sk-status"><?= sk_escape($row['grade'] ?: '—'); ?></span></td><td data-label="Bobot"><?= sk_escape($row['bobot'] ?? '—'); ?></td><?php } elseif (!$sk_schedule_only) { ?><td data-label="Aksi"><?php if ($sk_status['open'] && ($row['grade']??'-')==='-') { ?><a class="btn btn-outline-danger btn-sm" href="<?= sk_escape($sk_page.'?qwe='.$id_thn_akademik.'&id_krs='.$row['id_krs'].'&aksi=hapus&id_thn_akademik='.$id_thn_akademik.($level==='Jurusan/Prodi'?'&qaz='.rawurlencode($sk_subject):'')); ?>">Hapus</a><?php } else { ?><span class="text-muted">Terkunci</span><?php } ?></td><?php } ?>
      </tr><?php } ?></tbody></table></div>
    <?php } ?>
  </section>
  <?php if (!$sk_results && !$sk_schedule_only) { ?>
  <aside class="card sk-study-summary">
    <h2>Ringkasan KRS</h2><div class="sk-summary-value"><strong id="selected-sks"><?= $sk_total['sks']; ?></strong><span> / <?= $sk_limit?(int)$sk_limit['sks']:'—'; ?> SKS</span></div><p><?= $sk_pick?'Termasuk mata kuliah yang sudah diambil.':count($sk_rows).' mata kuliah terpilih.'; ?></p>
    <div class="sk-summary-status"><span class="sk-status"><?= sk_escape($sk_status['label']); ?></span><p><?= sk_escape($sk_status['note']); ?></p></div>
    <?php if (!$sk_limit) { ?><div class="alert alert-warning">Batas SKS belum diatur. Hubungi program studi sebelum mengambil mata kuliah.</div><?php } ?>
    <?php if ($sk_pick) { ?><p id="selection-message" aria-live="polite">Pilih mata kuliah untuk melihat jumlah SKS.</p><button form="course-selection" type="submit" class="btn btn-primary w-100" id="save-selection" disabled>Simpan pilihan</button><?php } elseif ($level==='mhs') { ?><?php if ($sk_can_pick) { ?><a class="btn btn-primary w-100" href="ambil_jadwal?qwe=<?= $id_thn_akademik; ?>">Pilih mata kuliah</a><?php } else { ?><button class="btn btn-secondary w-100" disabled>Pilihan belum tersedia</button><?php } ?><?php } ?>
  </aside>
  <?php } ?>
</div>
