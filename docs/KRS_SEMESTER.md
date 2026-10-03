# Pembatasan KRS berdasarkan semester

## Alur mahasiswa

Semester dihitung dari **periode akademik yang dipilih**, bukan tanggal komputer atau ID periode. Untuk mahasiswa reguler:

- Ganjil: `2 × (tahun akademik − tahun masuk) + 1`.
- Genap: `2 × (tahun akademik − tahun masuk) + 2`.

Pada 2026 Ganjil, angkatan 2026 berada di semester 1, angkatan 2025 di semester 3, dan angkatan 2024 di semester 5. Label mata kuliah lama seperti `1MN` dan `3AK` tetap disimpan; layanan membaca nomor di depan label dan memeriksa program studi secara terpisah. Label yang tidak valid tidak ditawarkan.

Halaman KRS menampilkan semester mahasiswa, angkatan, periode, dan batas SKS. Pilihan dibagi menjadi **Mata kuliah semester Anda** dan **Mata kuliah tambahan yang diizinkan**. Mata kuliah yang sudah diambil pada periode tersebut tidak ditawarkan lagi.

Mahasiswa harus berstatus Aktif. Jika semester tidak dapat ditentukan, pilihan ditutup sampai prodi melengkapi pengaturan. Batas SKS, periode pengisian, mata kuliah ganda, dan bentrok waktu tetap diperiksa. Penyimpanan memeriksa ulang seluruh aturan di server; pengiriman ID jadwal manual tidak dapat melewati pembatasan semester. Pilihan campuran yang tidak valid ditolak seluruhnya tanpa menyimpan sebagian KRS/KHS.

## Pengaturan program studi

Buka **Akademik → Semester dan izin KRS**, atau tombol pengaturan pada halaman KRS seorang mahasiswa. Pilih periode dan mahasiswa.

1. **Semester mahasiswa**: gunakan perhitungan otomatis, atau tetapkan semester 1–20 dengan alasan untuk kasus cuti, transfer, dan penyesuaian. Penetapan hanya berlaku pada periode dan prodi tersebut. Pilihan Otomatis mengembalikan perhitungan berdasarkan angkatan.
2. **Izin mata kuliah tambahan**: pilih mata kuliah yang ditawarkan pada periode itu, tentukan jenis izin, dan isi alasan persetujuan. Mengulang digunakan untuk semester lebih rendah; Semester atas untuk semester lebih tinggi; Persetujuan khusus untuk penyesuaian lainnya. Prodi meninjau kelayakan pengulangan secara akademik sebelum memberikan izin.
3. **Pencabutan**: isi alasan pada izin aktif, lalu cabut. Mata kuliah tersebut tidak lagi dapat dipilih sebagai tambahan. KRS yang sudah tersimpan tetap dipertahankan untuk ditinjau terpisah.

Mahasiswa tidak dapat mengatur semester atau memberikan izin sendiri. Prodi hanya dapat mengatur mahasiswa dan penawaran dalam prodinya. Setiap penetapan semester, pemberian izin, dan pencabutan dicatat bersama petugas, alasan, serta waktu. Halaman menampilkan 50 catatan terbaru; catatan lain tetap tersimpan di database.

Pengaturan saat ini dilakukan oleh akun **Jurusan/Prodi**. Belum ada alur persetujuan terpisah untuk dosen PA maupun pemeriksaan prasyarat otomatis.

## KRS lama

Tidak ada penghapusan atau perubahan otomatis pada KRS/KHS lama. Mata kuliah lintas semester tanpa izin tercatat ditandai **Perlu ditinjau**. Prodi dapat mencatat izin apabila pilihan tersebut sah, atau meninjau perubahan KRS melalui prosedur yang berlaku.

Audit database lokal sebelum penerapan menemukan sembilan baris pada tiga mahasiswa di periode 2026 Ganjil. Temuan tersebut merupakan calon pengecualian untuk ditinjau, bukan otomatis dinyatakan salah. Jumlah data lokal tetap 1.410 KRS dan 1.410 KHS setelah penerapan.

## Migrasi dan pengujian

Migrasi baru: `database/migrations/2026-10-03_krs_semester.sql`. Migrasi menambahkan tiga tabel: `krs_semester_mahasiswa`, `krs_izin_matkul`, dan `krs_kebijakan_log`. Jalankan menggunakan akun migrasi setelah backup, sebelum memasang kode ini pada lingkungan lain. Migrasi sudah diterapkan pada `siakad2026_local`; user aplikasi tetap hanya memerlukan izin SELECT, INSERT, UPDATE, dan DELETE.

Pengujian menggunakan database fixture terpisah: `python tests/run_security_tests.py --ui`. Kasus mencakup pilihan lintas semester tanpa izin, penolakan batch secara atomik, izin semester atas/mengulang, pembatasan prodi dan periode, pencabutan yang mempertahankan KRS, penetapan semester, mahasiswa cuti, riwayat perubahan, serta formulir prodi pada desktop dan ponsel. Data pribadi dan password lokal tidak digunakan sebagai fixture.
