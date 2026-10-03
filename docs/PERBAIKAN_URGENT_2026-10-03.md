# Perbaikan prioritas SIAKAD — 3 Oktober 2026

## Perubahan aplikasi

1. **Hak akses:** daftar izin per halaman terpusat, default menolak halaman yang belum memiliki kebijakan. Akun hanya dikelola admin. Prodi hanya mengelola lingkupnya, dosen hanya kelas yang tercatat sebagai miliknya, dan mahasiswa hanya datanya sendiri. Detail mahasiswa/perwalian dan laporan cetak memakai pemeriksaan sesi serta lingkup data.
2. **Penghapusan:** handler GET dan handler salinan pada halaman baca dihapus. Tautan hapus diubah oleh script bersama menjadi POST bertoken CSRF. Dispatcher memeriksa tipe akun, lingkup data, kepemilikan KRS, periode, dan ketergantungan sebelum menghapus. KRS bernilai, jadwal yang digunakan, serta master yang masih memiliki relasi ditolak. Perlindungan aplikasi berlaku juga sebelum FK database lama dimigrasikan.
3. **Login/password:** satu alur login aktif, prepared statements, `password_hash`/`password_verify`, regenerasi ID sesi. Password plaintext dan MD5 lama dimigrasikan saat login berhasil. Hash MD5 sendiri tidak dapat digunakan sebagai password. Sesi menyimpan identitas dan HMAC versi autentikasi; tidak menyimpan password atau hash database. Perubahan password membatalkan sesi lain melalui pemeriksaan versi. Pembuatan akun baru memakai hash modern dan transaksi untuk batch; hash tidak ditampilkan di daftar akun. Sesi lama harus login ulang satu kali.
4. **KRS/KHS:** pengambilan KRS dan inisialisasi KHS dalam satu transaksi; kegagalan membatalkan seluruh pilihan. Validasi ulang program studi, tahun akademik, periode, pilihan ganda, mata kuliah yang sudah diambil, jadwal bentrok, dan batas SKS di server. Penghapusan pasangan KRS/KHS juga menggunakan transaksi dan menolak KRS bernilai.
5. **Nilai:** sesuai konfirmasi pengguna, dosen memasukkan nilai akhir langsung. Kolom `nilai_uas` lama tetap digunakan untuk kompatibilitas, tanpa menghitung ulang nilai historis. Grade/bobot diambil dari `tbl_grade`, nilai harus 0–100, peserta harus ada pada KRS kelas, dan periode input harus terbuka. Batch disimpan secara atomik. Pratinjau grade memakai konfigurasi yang sama. Pada dump lama, batas 40 dan 50 bertumpang tindih; batas awal grade tertinggi diprioritaskan secara deterministik. Handler nilai salinan pada halaman absensi dibuang.
6. **Jadwal:** satu handler pembuatan jadwal, pemeriksaan dosen/matkul/ruang/hari/periode, jam mulai sebelum selesai, dan penolakan sejak satu bentrok. Pemeriksaan waktu memakai `mulai_lama < selesai_baru AND selesai_lama > mulai_baru`, sehingga interval yang mencakup jadwal lama ikut tertangkap; jadwal yang berdampingan diperbolehkan. Penguncian tahun akademik menyerialkan penyimpanan jadwal bersamaan.

Formulir POST mendapat token CSRF dari server. Endpoint AJAX pencarian yang hanya membaca data tetap dilindungi autentikasi/level. Query tampilan yang memakai referensi agama/jenis kelamin opsional menggunakan LEFT JOIN agar data dengan referensi kosong tetap terlihat. Direktori database diblokir melalui `.htaccess`.

## Migrasi database

File: `database/migrations/2026-10-03_integritas_akademik.sql`.

- Menolak penerapan jika terdapat duplikasi akun, KRS/KHS, batas SKS, periode, atau relasi KRS/KHS yang tidak konsisten. Tidak menghapus atau memilih baris historis otomatis.
- Menambah unique constraint untuk akun, KRS/KHS, batas SKS, dan konfigurasi periode.
- Mengubah FK satu kolom yang masih menggunakan ON DELETE CASCADE menjadi RESTRICT agar penghapusan SQL langsung juga tidak menghapus riwayat berantai. Relasi SET NULL dari migrasi sebelumnya tetap dipertahankan.
- Menambah FK majemuk yang menjaga kecocokan jadwal/prodi/tahun pada KRS dan keberadaan KRS untuk KHS.
- Dapat dijalankan ulang. DDL MySQL/MariaDB melakukan implicit commit; kegagalan setelah preflight bisa meninggalkan sebagian perubahan schema. Simpan backup dan lanjutkan dengan file yang sama setelah penyebab kegagalan diperbaiki.

Penerapan yang disarankan:

1. Backup database dan file aplikasi; uji pemulihan backup.
2. Terapkan dahulu pada salinan database aktual. Jika preflight menolak data lama, tinjau menggunakan arsip KRS/nilai; jangan menghapus riwayat hanya agar migrasi lolos.
3. Periksa apakah migrasi `2026-09-11_perbaikan_import.sql` sebelumnya sudah diterapkan. Jangan menerapkannya ulang tanpa mencocokkan nama FK dan schema aktual.
4. Dalam jendela pemeliharaan, unggah perubahan aplikasi beserta seluruh helper di `config/`, `template/head.php`, dan `template/scripts.php`, lalu jalankan migrasi baru pada database tujuan.
5. Login ulang dan periksa alur empat hak akses memakai data kampus. Pastikan Apache mengaktifkan rewrite dan `.htaccess`.

Contoh dari terminal menggunakan mysql yang dikonfigurasi untuk database tujuan:

```text
mysql -u USER -p DATABASE
SOURCE D:/lokasi/2026-10-03_integritas_akademik.sql;
```

Database aktif dan `config/db_config.php` tidak diubah dalam pekerjaan ini. Tidak ada migrasi produksi yang dijalankan.

## Pengujian

Jalankan `python tests/run_security_tests.py --mysql-bin C:/xampp/mysql/bin`.

Runner membuat instance MariaDB pada port sementara, membangun schema tanpa data pribadi dari dump, mengisi data sintetis, dan menyalin PHP aplikasi dengan konfigurasi database uji. Server/direktori sementara dibersihkan setelah selesai; konfigurasi lokal aplikasi tidak dibaca/disalin.

- 50 pemeriksaan integrasi service: akses, migrasi password, sesi, CSRF, KRS, nilai, transaksi gagal, proteksi riwayat, akun batch, dan jadwal bentrok.
- 41 pemeriksaan HTTP/migrasi: login dan halaman empat level, penolakan akses silang, POST bertoken, laporan cetak, penyimpanan KRS/nilai, perlindungan penghapusan, unique/FK database, migrasi berulang, dan penolakan database yang memiliki data ganda tanpa menghapus barisnya.
- Pemeriksaan sintaks PHP dilakukan terpisah.

Pengujian memakai PHP 8.3.28 dan MariaDB 10.4.32 lokal. Belum merupakan verifikasi terhadap konfigurasi Apache/hosting produksi atau seluruh data historis kampus. Perubahan ini mencakup enam prioritas yang dibahas, bukan audit keamanan menyeluruh terhadap setiap modul lama.
