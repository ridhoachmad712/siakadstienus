<?php
// Controller tampilan bersama. Handler akademik tetap memakai layanan transaksi.
session_start();
require_once __DIR__.'/../config/koneksi.php';
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../config/academic.php';
require_once __DIR__.'/ui.php';
$siakad_user=siakad_wajib_login($koneksi);
$username=$_SESSION['username'];
$level=$_SESSION['level'];
$kode_prodi=$_SESSION['kode_prodi'];
$sk_page=sk_current_route();
if ($sk_page==='ambil_jadwal' && isset($_POST['simpan'])) {
    siakad_form_proses(function() use ($koneksi,$siakad_user) { siakad_ambil_krs($koneksi,$siakad_user,$_GET['qwe']??null,$_POST['pilih']??null); },'krs?qwe='.(int)($_GET['qwe']??0));
}
if ($sk_page==='pengaturan_krs' && isset($_POST['simpan_kebijakan'])) {
    $sk_year=(int)($_POST['id_thn_akademik']??0);
    $sk_nim=$_POST['nim_npm']??null;
    siakad_form_proses(function() use ($koneksi,$siakad_user,$sk_year,$sk_nim) { siakad_krs_atur($koneksi,$siakad_user,$sk_nim,$sk_year,$_POST); },'pengaturan_krs?qwe='.$sk_year.'&qaz='.rawurlencode(is_string($sk_nim)?$sk_nim:'').'&saved=1');
}
if ($sk_page==='buat_jadwal' && isset($_POST['simpan'])) {
    siakad_form_proses(function() use ($koneksi,$siakad_user) { siakad_buat_jadwal($koneksi,$siakad_user,$_POST); },'buat_jadwal?qwe='.(int)($_POST['id_thn_akademik']??0));
}
if ($sk_page==='get_input_nilai' && isset($_POST['simpan_nilai'])) {
    siakad_form_proses(function() use ($koneksi,$siakad_user) { siakad_simpan_nilai($koneksi,$siakad_user,(int)($_GET['qwe']??0),$_POST['nim_npm']??null,$_POST['nilai_uas']??null); },'get_input_nilai?qwe='.(int)($_GET['qwe']??0));
}
if ($sk_page==='sks_mhs' && isset($_POST['simpan_sks'])) {
    $sk_target_year=(int)($_POST['id_thn_akademik']??0);
    siakad_form_proses(function() use ($koneksi,$sk_target_year) {
        if (!siakad_baris($koneksi,'SELECT id_thn_akademik FROM thn_akademik WHERE id_thn_akademik=?','i',[$sk_target_year])) throw new DomainException('Tahun akademik tidak ditemukan.');
        mysqli_begin_transaction($koneksi);
        try {
            foreach ($_POST['Pilih'] as $index=>$nim) {
                $existing=siakad_baris($koneksi,'SELECT id FROM pengaturan_sks_mhs WHERE nim_npm=? AND id_thn_akademik=?','si',[$nim,$sk_target_year]);
                if ($existing) siakad_ubah($koneksi,'UPDATE pengaturan_sks_mhs SET sks=? WHERE id=?','ii',[(int)$_POST['sks'][$index],(int)$existing['id']]);
                else siakad_ubah($koneksi,'INSERT INTO pengaturan_sks_mhs (nim_npm,id_thn_akademik,sks) VALUES (?,?,?)','sii',[$nim,$sk_target_year,(int)$_POST['sks'][$index]]);
            }
            mysqli_commit($koneksi);
        } catch (Throwable $e) { mysqli_rollback($koneksi); throw $e; }
    },'sks_mhs?qwe='.$sk_target_year.'&saved=1&angkatan='.rawurlencode((string)($_POST['angkatan']??'')));
}
// Form filter lama tetap diterima dan diarahkan ke URL yang dapat ditandai.
if (isset($_POST['filter'])) {
    $query=['qwe'=>(int)($_POST['id_thn_akademik']??0)];
    foreach (['qaz','angkatan','thn_masuk'] as $key) if (is_scalar($_POST[$key]??$_GET[$key]??null)) $query[$key]=$_POST[$key]??$_GET[$key];
    siakad_alihkan($sk_page.'?'.http_build_query($query));
}
$r_pengaturan=siakad_baris($koneksi,'SELECT * FROM pengaturan WHERE id_pengaturan=1') ?: [];
$prodi=siakad_baris($koneksi,'SELECT * FROM prodi WHERE kode_prodi=?','s',[$kode_prodi]) ?: [];
$sk_periods=siakad_semua($koneksi,'SELECT * FROM thn_akademik ORDER BY thn_akademik DESC,id_thn_akademik DESC');
$sk_period=sk_period($koneksi);
$id_thn_akademik=(int)($sk_period['id_thn_akademik']??0);
$sk_subject=$level==='mhs'?$username:($_GET['qaz']??'');
if (!is_string($sk_subject)) siakad_tolak('Mahasiswa tidak valid.',422);
$sk_titles=[
    'krs'=>['Kartu rencana studi','Periksa pilihan mata kuliah dan jumlah SKS semester Anda.'],
    'ambil_jadwal'=>['Pilih mata kuliah','Pilih jadwal yang sesuai, lalu periksa ringkasan sebelum menyimpan.'],
    'khs'=>['Kartu hasil studi','Nilai akhir dan hasil studi pada periode yang dipilih.'],
    'transkip'=>['Transkrip nilai','Ringkasan hasil studi dari seluruh periode yang sudah dinilai.'],
    'jadwal_kuliah'=>['Jadwal kuliah','Hari, waktu, dosen, dan ruangan untuk mata kuliah yang Anda ambil.'],
    'mhs_krs'=>['KRS mahasiswa','Periksa rencana studi mahasiswa dalam program studi Anda.'],
    'mhs_khs'=>['KHS mahasiswa','Periksa hasil studi mahasiswa dalam program studi Anda.'],
    'jadwal_mengajar'=>['Jadwal mengajar','Kelas dan peserta perkuliahan pada periode yang dipilih.'],
    'buat_jadwal'=>['Penjadwalan kuliah','Susun jadwal dosen, mata kuliah, ruangan, dan waktu perkuliahan.'],
    'rekap_jadwal'=>['Rekap jadwal','Daftar perkuliahan program studi pada periode yang dipilih.'],
    'input_nilai'=>['Nilai mahasiswa','Pilih kelas untuk memeriksa dan memasukkan nilai akhir.'],
    'input_nilai_dosen'=>['Input nilai','Pilih kelas mengajar untuk memasukkan nilai akhir mahasiswa.'],
    'get_input_nilai'=>['Nilai akhir kelas','Masukkan nilai akhir langsung dari dosen, dalam rentang 0–100.'],
    'krs_mhs'=>['KRS mahasiswa','Cari mahasiswa dan buka rencana studi pada periode yang dipilih.'],
    'khs_mhs'=>['KHS mahasiswa','Cari mahasiswa dan buka hasil studi pada periode yang dipilih.'],
    'transkip_mhs'=>['Transkrip mahasiswa','Cari mahasiswa untuk melihat dan mencetak transkrip nilai.'],
    'pengaturan_krs'=>['Semester dan izin KRS','Atur semester mahasiswa dan persetujuan mata kuliah di luar semester reguler.'],
    'sks_mhs'=>['Batas SKS mahasiswa','Atur batas pengambilan SKS sesuai periode dan angkatan.']
];
[$judul_halaman,$sk_description]=$sk_titles[$sk_page];
