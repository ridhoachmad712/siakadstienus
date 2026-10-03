<?php
function sk_escape($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function sk_role_label($role) {
    return ['admin'=>'Admin akademik','Jurusan/Prodi'=>'Program studi','dosen'=>'Dosen','mhs'=>'Mahasiswa'][$role] ?? 'Pengguna';
}
function sk_navigation($role) {
    $menus = [
        'admin'=>[
            ['Beranda','dashboard'],
            ['Data master', [['Mahasiswa','mhs'],['Dosen','dosen'],['Mata kuliah','mata_kuliah'],['Program studi','jurusan'],['Institusi','fakultas'],['Ruangan','ruangan']]],
            ['Akademik', [['Tahun akademik','thn_akademik'],['Grade nilai','grade']]],
            ['Akun', [['Admin akademik','akun_admin'],['Program studi','akun_jurusan'],['Dosen','akun_dosen'],['Mahasiswa','akun_mhs']]],
            ['Pengaturan','pengaturan']
        ],
        'Jurusan/Prodi'=>[
            ['Beranda','dashboard'],['Mahasiswa','jurusan_has_mhs'],['Dosen','jurusan_has_dosen'],['Mata kuliah','jurusan_has_matkul'],
            ['Jadwal', [['Rekap perkuliahan','rekap_jadwal'],['Susun jadwal','buat_jadwal']]],
            ['Akademik', [['KRS mahasiswa','krs_mhs'],['KHS mahasiswa','khs_mhs'],['Input nilai','input_nilai'],['Transkrip','transkip_mhs'],['Batas SKS','sks_mhs'],['Semester dan izin KRS','pengaturan_krs']]]
        ],
        'mhs'=>[['Beranda','dashboard'],['Jadwal','jadwal_kuliah'],['KRS','krs'],['KHS','khs'],['Transkrip','transkip'],['Pengumuman','https://stienus.ac.id/pengumuman/']],
        'dosen'=>[['Beranda','dashboard'],['Jadwal mengajar','jadwal_mengajar'],['Perwalian','dosen_has_mhs'],['Input nilai','input_nilai_dosen'],
            ['Layanan dosen', [['SISTER','https://sister.kemdikbud.go.id/beranda'],['SiPinter','https://sipinter.lldikti9.id/'],['Sijafung','https://jafa.lldikti9.id/access'],['SINTA','https://sinta.kemdikbud.go.id/'],['BIMA','https://bima.kemdikbud.go.id/']]]]
    ];
    return $menus[$role] ?? [];
}
function sk_current_route() { return pathinfo($_SERVER['SCRIPT_NAME'] ?? 'dashboard', PATHINFO_FILENAME); }
function sk_route_parent($route) {
    $parents = ['search_mhs'=>'mhs','search_dosen'=>'dosen','search_matkul'=>'mata_kuliah','search_jurusan'=>'jurusan','search_fakultas'=>'fakultas','fak-has-jur'=>'fakultas',
        'add_mhs_jurusan'=>'jurusan_has_mhs','add_dosen_jurusan'=>'jurusan_has_dosen','add_matkul_jurusan'=>'jurusan_has_matkul','ambil_jadwal'=>'krs','tambah_krs'=>'krs',
        'mhs_krs'=>'krs_mhs','mhs_khs'=>'khs_mhs','detail_mhs'=>'dosen_has_mhs'];
    if (in_array($route,['get_input_nilai','get_absen','get_daftar_mahasiswa'],true)) return ($_SESSION['level'] ?? '')==='dosen' ? 'input_nilai_dosen' : 'input_nilai';
    return $parents[$route] ?? $route;
}
function sk_period($db) {
    $id = filter_var($_GET['qwe'] ?? null, FILTER_VALIDATE_INT);
    if ($id && in_array(sk_current_route(),['get_input_nilai','get_absen','get_daftar_mahasiswa'],true)) {
        $schedule = siakad_baris($db,'SELECT id_thn_akademik FROM jadwal_mengajar WHERE id_jadwal=?','i',[$id]);
        $id = (int)($schedule['id_thn_akademik'] ?? 0);
    }
    $row = $id ? siakad_baris($db,'SELECT * FROM thn_akademik WHERE id_thn_akademik=?','i',[$id]) : null;
    return $row ?: siakad_baris($db,'SELECT * FROM thn_akademik ORDER BY thn_akademik DESC, id_thn_akademik DESC LIMIT 1');
}
function sk_period_label($period) { return $period ? trim($period['thn_akademik'].' · '.$period['ket'],' ·') : 'Belum ada tahun akademik'; }
