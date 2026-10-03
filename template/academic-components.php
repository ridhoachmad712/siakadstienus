<?php
function sk_period_filter($periods,$selected,$extras=[]) { ?>
<form method="get" class="sk-filter-bar" aria-label="Filter akademik">
  <div><label for="academic-period" class="form-label">Tahun akademik</label><select id="academic-period" name="qwe" class="form-select" required>
    <?php foreach ($periods as $period) { ?><option value="<?= (int)$period['id_thn_akademik']; ?>" <?= (int)$selected===(int)$period['id_thn_akademik']?'selected':''; ?>><?= sk_escape(sk_period_label($period)); ?></option><?php } ?>
  </select></div>
  <?php foreach ($extras as $key=>$value) { ?><input type="hidden" name="<?= sk_escape($key); ?>" value="<?= sk_escape($value); ?>"><?php } ?>
  <button type="submit" class="btn btn-primary" <?= !$periods?'disabled':''; ?>>Tampilkan</button>
</form>
<?php }
function sk_period_status($db,$table,$year) {
    $row=siakad_baris($db,"SELECT dari_tgl,sampai_tgl FROM $table WHERE id_thn_akademik=?",'i',[$year]);
    if (!$row) return ['open'=>false,'label'=>'Periode belum diatur','note'=>'Hubungi program studi untuk pengaturan periode.'];
    if ($row['dari_tgl']==='0000-00-00') return ['open'=>true,'label'=>'Periode dibuka','note'=>'Pengisian tersedia tanpa batas tanggal sesuai pengaturan akademik.'];
    $open=siakad_periode_terbuka($row,date('Y-m-d'));
    return ['open'=>$open,'label'=>$open?'Periode dibuka':(date('Y-m-d')<$row['dari_tgl']?'Belum dibuka':'Periode ditutup'),'note'=>'Jadwal pengisian: '.date('d/m/Y',strtotime($row['dari_tgl'])).'–'.date('d/m/Y',strtotime($row['sampai_tgl'])).'.'];
}
function sk_empty_row($columns,$message='Belum ada data pada periode ini.') { ?><tr><td colspan="<?= $columns; ?>" class="sk-empty"><?= sk_escape($message); ?></td></tr><?php }
function sk_study_data($db,$nim,$prodi,$year,$transcript=false,$results=false) {
    $scope=$transcript?'': ' AND k.id_thn_akademik=?';
    // Transkrip lama menampilkan seluruh riwayat milik mahasiswa.
    $args=$transcript?[$nim]:[$nim,$prodi,$year];
    $types=$transcript?'s':'ssi';
    $programScope=$transcript?'':' AND k.kode_prodi=?';
    $table=$transcript||$results?'khs_mhs':'krs_mhs';
    $join=$transcript||$results?'': ' LEFT JOIN khs_mhs g ON g.nim_npm=k.nim_npm AND g.id_jadwal=k.id_jadwal AND g.id_thn_akademik=k.id_thn_akademik AND g.kode_prodi=k.kode_prodi';
    $grade=$transcript||$results?'k.nilai_akhir,k.grade,k.bobot':'k.id_krs,g.nilai_akhir,g.grade,g.bobot';
    $graded=$transcript?" AND k.grade<>'' AND k.grade<>'-'":'';
    return siakad_semua($db,"SELECT $grade,j.*,m.nama_matkul,m.sks,m.semester,d.nama_dosen,h.nama_hari,r.nama_ruangan,t.thn_akademik,t.ket FROM $table k JOIN jadwal_mengajar j ON j.id_jadwal=k.id_jadwal LEFT JOIN mata_kuliah m ON m.kode_matkul=j.kode_mk LEFT JOIN dosen d ON d.nip=j.nip LEFT JOIN tbl_hari h ON h.id_hari=j.id_hari LEFT JOIN tbl_ruangan r ON r.kode_ruangan=j.kode_ruangan LEFT JOIN thn_akademik t ON t.id_thn_akademik=j.id_thn_akademik $join WHERE k.nim_npm=? $programScope $scope $graded ORDER BY ".($transcript?'m.semester,j.id_thn_akademik':'j.id_hari,j.mulai_jam').",m.nama_matkul",$types,$args);
}
function sk_study_totals($rows) {
    $total=0; $graded=0; $mutu=0;
    foreach ($rows as $row) {
        $sks=(int)$row['sks']; $total+=$sks;
        if (($row['grade']??'-')!=='-' && ($row['grade']??'')!=='' && is_numeric($row['bobot']??null)) { $graded+=$sks; $mutu+=$sks*(float)$row['bobot']; }
    }
    // KHS lama membagi mutu dengan seluruh SKS periode; transkrip hanya memuat baris bernilai.
    return ['sks'=>$total,'graded'=>$graded,'ip'=>$total?number_format($mutu/$total,2,',','.'):'—'];
}
