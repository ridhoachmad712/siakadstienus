# Konteks SIAKAD STIE Nusantara

Tanggal peninjauan: 3 Oktober 2026. Analisis statis atas checkout lokal, bukan hasil pengujian server produksi. Dokumen ini menjadi peta awal untuk perbaikan berikutnya; temuan perlu diperbarui setelah implementasi berubah.

**Pembaruan:** enam prioritas berikut telah ditangani pada source. Lihat `PERBAIKAN_URGENT_2026-10-03.md` untuk implementasi, pengujian, dan migrasi yang belum diterapkan pada produksi. Temuan di dokumen ini merekam kondisi sebelum perbaikan.

## Arsitektur dan struktur

- PHP native/prosedural, MySQL melalui MySQLi, sesi PHP, halaman yang merender HTML langsung. Tidak ditemukan Composer atau framework backend.
- Halaman PHP menggabungkan pemeriksaan sesi, query, penanganan formulir, dan tampilan. Banyak pola disalin antarmodul.
- `index.php` mengarahkan ke `pages/login` atau dashboard. `.htaccess` root memetakan URL tanpa ekstensi ke PHP/HTML; memerlukan konfigurasi Apache yang mendukung rewrite.
- `config/koneksi.php`: koneksi, penanganan error, helper `nilai`, `nilai_teks`, `sql_aman`, informasi browser/IP, dan zona waktu `Asia/Jakarta`. Charset koneksi sengaja tidak dipaksa karena pertimbangan data lama.
- `config/db_config.php`: konfigurasi lokal yang diabaikan Git. Gunakan `db_config.sample.php` sebagai referensi; jangan mencatat kredensial dalam dokumentasi.
- `config/auth.php`: pemeriksaan sesi terpusat; mendukung daftar level yang diizinkan serta respons AJAX 401/403.
- `config/import_lib.php` dan `import_master.php`: pembacaan XLS/XLSX/CSV, normalisasi, validasi, dan prepared statements untuk impor master. Upload sementara di direktori temp sistem, batas 10 MB, mendukung pembaruan data yang sudah ada.
- `pages/`: modul bisnis, endpoint pencarian/partial, dan sejumlah halaman demo HTML Tabler.
- `pages/cetak/`: laporan HTML/CSS untuk pencetakan browser.
- `template/`: head, header, menu berdasarkan level, footer, dan scripts bersama.
- `assets/siakad.css`: tema aplikasi yang dipanggil setelah CSS dasar Tabler; `dist/` berisi aset siap pakai.
- `src/`, Gulp, Gemfile, dan package.json berasal dari template Tabler. package.json menyebut Tabler 1.0.0-beta4 dan Bootstrap 5.1.3; bukan manifest backend SIAKAD. Jangan mengasumsikan build frontend dapat berjalan hanya berdasarkan manifest.
- `pages/foto_mhs`, `pages/foto_dosen`, berkas spreadsheet, dan dump SQL perlu diperlakukan sebagai berkas data.

## Pengguna dan modul

Level disimpan sebagai `admin`, `Jurusan/Prodi`, `dosen`, dan `mhs`. Gunakan nilai persis ini ketika menelusuri otorisasi. Username mahasiswa dipakai sebagai NIM, username dosen dipakai sebagai `nip`, dan sesi memiliki `kode_prodi`.

| Area | Berkas utama |
| --- | --- |
| Login, profil, penggantian password/foto | `login.php`, `logout.php`, `dashboard.php` |
| Master institusi/prodi/dosen/mahasiswa/matkul | `fakultas.php`, `jurusan.php`, `dosen.php`, `mhs.php`, `mata_kuliah.php` |
| Relasi prodi dan penasihat akademik | `jurusan_has_*.php`, `add_*_jurusan.php`, `dosen_has_mhs.php` |
| Akun per level | `akun_admin.php`, `akun_jurusan.php`, `akun_dosen.php`, `akun_mhs.php` |
| Periode, SKS, ruang, grade | `thn_akademik.php`, `sks_mhs.php`, `ruangan.php`, `grade.php` |
| Jadwal | `buat_jadwal.php`, `jadwal_kuliah.php`, `jadwal_mengajar.php`, `rekap_jadwal.php` |
| KRS | `krs.php`, `krs_mhs.php`, `ambil_jadwal.php`, `mhs_krs.php` |
| Nilai/KHS/transkrip | `input_nilai.php`, `input_nilai_dosen.php`, `get_input_nilai.php`, `khs*.php`, `transkip*.php` |
| Tampilan/identitas kampus | `pengaturan.php`, `template/`, `assets/siakad.css` |

Nama berkas saja tidak cukup untuk menyimpulkan fungsi: beberapa halaman memiliki blok lama hasil salin-tempel. `tambah_krs.php` terlihat masih berupa tabel kosong; alur pemilihan yang berisi penyimpanan ditemukan di `ambil_jadwal.php`. `get_absen.php` juga memiliki handler penyimpanan nilai.

## Database dan alur akademik

Dump `database/e-siakad.sql` mendefinisikan 30 tabel InnoDB dengan charset utf8mb4.

1. Institusi (`tbl_fakultas`) terhubung ke `prodi` melalui `fakultas_has_jurusan`.
2. `prodi_has_mhs`, `prodi_has_dosen`, dan `prodi_has_matkul` menghubungkan data master dengan prodi. `mhs_has_pa` menghubungkan mahasiswa dan penasihat akademik.
3. `jadwal_mengajar` menghubungkan prodi, dosen, mata kuliah, tahun akademik, hari, ruangan, dan jam.
4. `jadwal_penawaran` mengatur periode pengisian KRS; `pengaturan_sks_mhs` menyimpan batas SKS mahasiswa per periode.
5. `ambil_jadwal.php` menghitung SKS dan menyimpan pilihan ke `krs_mhs` sekaligus menginisialisasi `khs_mhs`.
6. `jadwal_input_nilai` menyimpan periode input nilai. Halaman nilai mengisi nilai/grade/bobot pada `khs_mhs`; KHS dan transkrip menggabungkannya dengan jadwal serta mata kuliah.
7. IPK pada `transkip.php` dihitung sebagai jumlah SKS × bobot dibagi jumlah SKS untuk baris yang sudah memiliki grade. Kebijakan mata kuliah ulang belum diverifikasi.

Tabel tambahan mencakup referensi agama/jenis kelamin/jenis mata kuliah/pekerjaan, orang tua, pembayaran UKT, dan pengaturan aplikasi. Adanya tabel belum membuktikan seluruh fiturnya aktif.

Dump masih memakai kolom master dengan panjang lama dan sejumlah foreign key `ON DELETE CASCADE`. Migrasi `database/migrations/2026-09-11_perbaikan_import.sql` memperlebar kolom, mengizinkan data opsional NULL, dan mengubah sebagian relasi referensi menjadi `SET NULL`. Belum diverifikasi apakah migrasi sudah diterapkan pada database yang dipakai aplikasi.

## Temuan untuk perbaikan berikutnya

1. **Otorisasi belum merata.** Banyak pemanggilan `siakad_wajib_login` tidak memakai batas level. `get_input_nilai.php` mengambil jadwal dari parameter lalu mengganti kode prodi tanpa pemeriksaan kepemilikan jadwal sebelum handler update. Menu yang tersembunyi tidak menggantikan pemeriksaan endpoint. `pages/cetak/transkip.php` belum menggunakan auth terpusat dan blok pemeriksaan login lama dikomentari.
2. **Password tidak konsisten.** Login aktif membandingkan password secara langsung dengan kolom database, sementara pembuatan admin memakai MD5. Sesi menyimpan nilai password. Perlu audit semua jalur pembuatan/reset/perubahan akun sebelum migrasi ke `password_hash`/`password_verify`.
3. **Mutasi melalui GET dan pemeriksaan aksi keliru.** Pola `isset($_GET['aksi']) == 'hapus'` membandingkan boolean dengan string, bukan nilai aksi. Ditemukan pada beberapa handler hapus, termasuk salinan dalam halaman cetak. Token CSRF tidak ditemukan dalam pencarian source PHP.
4. **KRS/KHS belum atomik.** Dua INSERT di `ambil_jadwal.php` tidak dibungkus transaksi. Dump tidak mendefinisikan unique constraint untuk kombinasi mahasiswa/jadwal/periode pada KRS/KHS. Perlu pencegahan pilihan ganda dan pemeriksaan jadwal, prodi, periode, serta SKS langsung pada handler penyimpanan.
5. **Rumus nilai berbeda.** `get_input_nilai.php` memakai nilai UAS sebagai nilai akhir 100%; handler `get_absen.php` memakai tugas 30%, UTS 35%, UAS 35%. Pastikan kebijakan akademik yang berlaku sebelum menyeragamkan rumus. Validasi rentang dan grade perlu ditinjau.
6. **NULL dari migrasi belum selaras dengan semua query.** Sejumlah query profil masih INNER JOIN ke agama/jenis kelamin sehingga data mahasiswa dengan referensi kosong dapat tidak muncul. Formulir manual dan impor juga perlu konsisten.
7. **Query dan rendering masih bercampur.** Banyak query memakai interpolasi string dan helper escaping; prepared statements belum diterapkan menyeluruh. Escaping SQL berbeda dari escaping HTML; audit output dan query per modul.
8. **Unggah foto perlu ditinjau.** Dashboard memeriksa ekstensi/ukuran dan memakai nama asli, menghapus foto lama sebelum memastikan penyimpanan foto baru berhasil. Perlu validasi isi file dan penanganan gagal.
9. **Perbedaan schema dan data lama.** Nilai/bobot pada dump menggunakan varchar dan penanda `-`. Konversi tipe, aturan cascade, serta charset harus mempertimbangkan data aktual dan relasi akademik.
10. **Sisa template dan fitur kosong.** Berkas cetak `mhs.php`, `fakultas.php`, dan `jurusan_prodi.php` berukuran nol. Banyak aset/demo Tabler tersisa. Pembersihan harus menelusuri pemakaian terlebih dahulu.

## Verifikasi dan batasan

- PHP CLI tersedia di `C:\xampp\php\php.exe`, versi 8.3.28; ekstensi mysqli, SimpleXML, zip, dan mbstring tersedia.
- Pemeriksaan `php -l` atas 78 berkas PHP di root/config/template/pages: seluruhnya lolos. Ini hanya memverifikasi sintaks.
- Tidak ditemukan suite pengujian khusus SIAKAD melalui pencarian nama berkas pengujian. Workflow GitHub yang tersedia bernama `codeql-analysis.yml`.
- Belum menjalankan web app, login, koneksi database, impor, migrasi, atau pengujian alur pengguna. Tidak membaca isi kredensial lokal.
- Checkout sudah memiliki perubahan sebelum peninjauan: 64 berkas termodifikasi, 203 terhapus, dan 238 untracked. Jangan reset, clean, atau menimpa perubahan tersebut tanpa meninjau konteks.
- Peninjauan ini tidak mengubah kode aplikasi atau database; hanya menambahkan dokumen konteks ini.

## Pendekatan perbaikan

Lakukan perubahan bertahap per modul. Mulai dari handler yang menyimpan/menghapus data dan pemeriksaan level/kepemilikan. Untuk perubahan KRS, nilai, dan schema, gunakan database pengujian serta skenario per hak akses, periode tertutup, data opsional kosong, pilihan ganda, dan kegagalan penyimpanan. Tetapkan rumus nilai, kebijakan SKS, serta mata kuliah ulang berdasarkan aturan kampus sebelum mengubah hasil akademik.
