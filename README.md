# SIAKAD STIE Nusantara

Aplikasi akademik berbasis PHP native dan MariaDB/MySQL, dengan peran admin, program studi, dosen, dan mahasiswa.

## Menjalankan aplikasi

1. Siapkan PHP 8.3 dengan MySQLi, serta MariaDB/MySQL dan Apache dengan mod_rewrite.
2. Pulihkan database kampus dari backup privat. Repository ini tidak memuat data mahasiswa, foto, password akun, atau kredensial koneksi.
3. Salin `config/db_config.sample.php` menjadi `config/db_config.php`, lalu isi koneksi database tujuan.
4. Setelah backup dan pemeriksaan staging, jalankan migrasi yang diperlukan dalam `database/migrations/`. Untuk pembatasan semester KRS, jalankan `2026-10-03_krs_semester.sql` sebelum menggunakan kode ini. Migrasi integritas akademik menolak data ganda atau relasi KRS/KHS yang tidak konsisten dan tidak merapikannya otomatis.
5. Buka halaman `pages/login` dari URL aplikasi dan masuk dengan akun pada database yang dipulihkan.

`database/schema.sql` berisi struktur awal tanpa data dan digunakan oleh pengujian. Database kosong belum memiliki akun atau pengaturan operasional. File unggahan foto dan konfigurasi koneksi disiapkan terpisah pada server.

## Perubahan aplikasi

- Tema maroon dan layout responsif untuk halaman akademik, profil, serta cetak.
- Otorisasi per peran, CSRF, password modern dengan dukungan migrasi login lama, dan transaksi akademik.
- Pilihan KRS berdasarkan semester mahasiswa; izin mengulang/semester atas dan penetapan semester khusus oleh prodi.
- Riwayat alasan dan petugas untuk pengaturan KRS. KRS/KHS lama dipertahankan untuk peninjauan.

Petunjuk fitur KRS: [docs/KRS_SEMESTER.md](docs/KRS_SEMESTER.md). Dokumentasi tampilan: [docs/UI_UX_MAROON.md](docs/UI_UX_MAROON.md).

## Pengujian

Jalankan `python tests/run_security_tests.py --mysql-bin C:/xampp/mysql/bin --ui` dengan PHP tersedia di PATH serta Node.js dan Playwright tersedia untuk pemeriksaan browser. Runner memakai database sementara dan data sintetis, tanpa mengakses database operasional. Validasi terakhir: 72 pemeriksaan integrasi, 134 pemeriksaan browser, dan 42 pemeriksaan HTTP/migrasi.
