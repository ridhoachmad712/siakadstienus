<?php
require_once __DIR__.'/academic-components.php';
$sk_limits=$sk_page==='sks_mhs';
$sk_transcript=$sk_page==='transkip_mhs';
$sk_cohort=$_GET['angkatan']??$_GET['thn_masuk']??'';
$sk_search=$_GET['search']??'';
if (!is_string($sk_cohort)||!is_string($sk_search)) siakad_tolak('Filter mahasiswa tidak valid.',422);
$sk_cohorts=siakad_semua($koneksi,'SELECT DISTINCT m.thn_masuk FROM mahasiswa m JOIN prodi_has_mhs p ON p.nim_npm=m.nim_npm WHERE p.kode_prodi=? ORDER BY m.thn_masuk DESC','s',[$kode_prodi]);
$args=[$id_thn_akademik,$kode_prodi]; $types='is'; $where='';
if ($sk_cohort!=='') { $where.=' AND m.thn_masuk=?'; $args[]=$sk_cohort; $types.='s'; }
if ($sk_search!=='') { $where.=' AND (m.nama_mhs LIKE ? OR m.nim_npm LIKE ?)'; $args[]='%'.$sk_search.'%'; $args[]='%'.$sk_search.'%'; $types.='ss'; }
$sk_students=siakad_semua($koneksi,'SELECT m.nim_npm,m.nama_mhs,m.thn_masuk,m.status_mhs,l.sks FROM mahasiswa m JOIN prodi_has_mhs p ON p.nim_npm=m.nim_npm LEFT JOIN pengaturan_sks_mhs l ON l.nim_npm=m.nim_npm AND l.id_thn_akademik=? WHERE p.kode_prodi=?'.$where.' ORDER BY m.nim_npm',$types,$args);
?>
<form method="get" class="sk-filter-bar" aria-label="Filter mahasiswa">
  <?php if (!$sk_transcript) { ?><div><label class="form-label" for="academic-period">Tahun akademik</label><select class="form-select" name="qwe" id="academic-period"><?php foreach ($sk_periods as $row) { ?><option value="<?= (int)$row['id_thn_akademik']; ?>" <?= (int)$row['id_thn_akademik']===$id_thn_akademik?'selected':''; ?>><?= sk_escape(sk_period_label($row)); ?></option><?php } ?></select></div><?php } ?>
  <div><label class="form-label" for="cohort-filter">Angkatan</label><select name="angkatan" id="cohort-filter" class="form-select"><option value="">Semua angkatan</option><?php foreach ($sk_cohorts as $row) { ?><option value="<?= sk_escape($row['thn_masuk']); ?>" <?= (string)$row['thn_masuk']===$sk_cohort?'selected':''; ?>><?= sk_escape($row['thn_masuk']); ?></option><?php } ?></select></div>
  <div class="sk-filter-search"><label class="form-label" for="student-search">Cari mahasiswa</label><input id="student-search" type="search" name="search" class="form-control" placeholder="Nama atau NIM" value="<?= sk_escape($sk_search); ?>"></div>
  <button class="btn btn-primary" type="submit">Tampilkan</button>
</form>
<?php if (isset($_GET['saved'])&&$sk_limits) { ?><div class="alert alert-success" role="status">Batas SKS berhasil disimpan.</div><?php } ?>
<?php if ($sk_limits) { ?><form method="post"><?php siakad_csrf_field(); ?><input type="hidden" name="simpan_sks" value="1"><input type="hidden" name="id_thn_akademik" value="<?= $id_thn_akademik; ?>"><input type="hidden" name="angkatan" value="<?= sk_escape($sk_cohort); ?>"><?php } ?>
<section class="card"><div class="sk-section-head"><div><h2>Daftar mahasiswa</h2><p><?= count($sk_students); ?> mahasiswa · <?= sk_escape(($prodi['nama_prodi'] ?? '') ?: $kode_prodi); ?></p></div></div>
  <div class="table-responsive"><table class="table sk-data-table sk-mobile-rows"><thead><tr><th>Mahasiswa</th><th>Angkatan</th><th>Status</th><th><?= $sk_limits?'Batas SKS':'Aksi'; ?></th></tr></thead><tbody>
  <?php if (!$sk_students) sk_empty_row(4,'Tidak ada mahasiswa yang sesuai dengan filter.'); ?>
  <?php foreach ($sk_students as $index=>$row) { ?><tr><td data-label="Mahasiswa"><strong><?= sk_escape($row['nama_mhs'] ?: $row['nim_npm']); ?></strong><small><?= sk_escape($row['nim_npm']); ?></small></td><td data-label="Angkatan"><?= sk_escape($row['thn_masuk']); ?></td><td data-label="Status"><?= sk_escape($row['status_mhs'] ?: 'Belum diatur'); ?></td><td data-label="<?= $sk_limits?'Batas SKS':'Aksi'; ?>">
    <?php if ($sk_limits) { ?><input type="hidden" name="Pilih[]" value="<?= sk_escape($row['nim_npm']); ?>"><label class="visually-hidden" for="limit-<?= $index; ?>">Batas SKS <?= sk_escape($row['nama_mhs'] ?: $row['nim_npm']); ?></label><input class="form-control sk-grade-input" type="number" id="limit-<?= $index; ?>" name="sks[]" min="0" max="24" required value="<?= (int)($row['sks']??0); ?>"><?php } elseif ($sk_transcript) { ?><a class="btn btn-outline-primary btn-sm" href="cetak/transkip?nim_npm=<?= rawurlencode($row['nim_npm']); ?>" target="_blank" rel="noopener">Lihat transkrip</a><?php } else { ?><a class="btn btn-outline-primary btn-sm" href="<?= $sk_page==='krs_mhs'?'mhs_krs':'mhs_khs'; ?>?qwe=<?= $id_thn_akademik; ?>&qaz=<?= rawurlencode($row['nim_npm']); ?>">Lihat <?= $sk_page==='krs_mhs'?'KRS':'KHS'; ?></a><?php } ?>
  </td></tr><?php } ?></tbody></table></div>
  <?php if ($sk_limits) { ?><div class="sk-save-bar"><div><strong>Pengaturan periode ini</strong><small>Nilai 0 berarti belum tersedia SKS untuk diambil.</small></div><button class="btn btn-primary" type="submit" <?= !$sk_students||!$id_thn_akademik?'disabled':''; ?>>Simpan batas SKS</button></div><?php } ?>
</section>
<?php if ($sk_limits) { ?></form><?php } ?>
