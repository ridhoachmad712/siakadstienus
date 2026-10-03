# Tampilan SIAKAD — navigasi ringkas dan maroon

Implementasi mengikuti pilihan navigasi ringkas. Warna utama `#7b203a`, hover `#60182c`, dan permukaan aksen `#f8edf0`. Header putih, area kerja terang, navigasi mendatar di desktop, serta menu yang dapat dibuka/tutup pada ponsel.

## Perubahan

- Kerangka bersama: identitas kampus, akun, konteks periode, menu per peran, penanda halaman aktif, tautan eksternal, fokus keyboard, tabel, tombol, dan formulir.
- Beranda admin: jumlah mahasiswa, dosen, mata kuliah dan akses pekerjaan administrasi.
- Beranda prodi: jumlah mahasiswa/dosen dalam prodi, kelas pada periode terbaru, akses jadwal dan proses akademik.
- Beranda dosen: kelas pada periode terbaru, perwalian, nilai yang belum diisi, dan jadwal kelas.
- Beranda mahasiswa: SKS yang diambil, IP kumulatif, jumlah mata kuliah, jadwal, dan status pengisian KRS.
- Biodata, foto, dan password mahasiswa/dosen tersedia melalui menu akun → Profil saya (`dashboard?view=profil`). Formulir lama tetap menggunakan handler dan CSRF yang sama.
- Tombol tambah data pada master mahasiswa/dosen/mata kuliah menjadi aksi utama; impor dan template menjadi aksi pendukung. Fokus otomatis pencarian dihapus agar tidak membuka keyboard ponsel saat halaman dimuat.
- Skrip grafik contoh yang tidak memiliki elemen tujuan dihapus dari dashboard.

## Konteks periode

Aplikasi belum memiliki penanda semester aktif global. Beranda memakai periode terbaru berdasarkan tahun akademik dan ID. Header menyebutnya **Periode terbaru**, bukan semester aktif. Pada halaman yang menerima `qwe`, header menunjukkan periode halaman. Pada detail kelas, `qwe` adalah ID jadwal sehingga periode diambil dari jadwal tersebut. Pilihan semester dan aturan transaksi pada halaman akademik tetap menjadi sumber konteks operasi.

IP kumulatif memakai perhitungan SKS × bobot dari hasil studi yang sudah dinilai, mengikuti pola aplikasi. Tidak ada perubahan kebijakan pengulangan mata kuliah atau nilai lama.

## Verifikasi

Jalankan lint PHP, lalu pengujian pada salinan aplikasi dan MariaDB sementara:

```powershell
$env:NODE_PATH = '<folder node_modules yang menyediakan playwright>'
python tests/run_security_tests.py --mysql-bin C:/xampp/mysql/bin --ui
```

Pemeriksaan browser memakai Microsoft Edge headless: empat jenis akun, beranda, dropdown, navigasi ponsel 360 px, halaman akademik/data master, profil mahasiswa/dosen, dan error JavaScript. Screenshot data sintetis tersimpan di `tests/artifacts/ui/` yang diabaikan Git. Pengujian tidak membaca konfigurasi atau mengubah database produksi.

## Layout halaman akademik

17 halaman akademik memakai controller presentasi dan layout bersama: KRS/KHS, pilihan mata kuliah, transkrip, jadwal mahasiswa/dosen/prodi, daftar kelas untuk input nilai, detail nilai, daftar hasil studi mahasiswa, serta pengaturan SKS. Filter menggunakan URL GET agar pilihan periode dapat ditandai; pengiriman POST filter lama tetap diarahkan ke URL yang sesuai.

- KRS: identitas terpisah, tabel pilihan, ringkasan SKS, status periode, dan jumlah SKS yang berubah saat mata kuliah dicentang. Batas SKS tetap menjadi maksimum, bukan target.
- KHS/transkrip: identitas, total SKS, indeks prestasi, tabel nilai, dan akses cetak. Rumus KHS tetap mengikuti aplikasi lama: jumlah mutu dibagi seluruh SKS pada periode. Transkrip menampilkan riwayat yang sudah dinilai; ringkasan SKS/IP mengikuti program studi akun seperti sumber lama.
- Jadwal: satu pemilih periode dan kartu kelas yang dikelompokkan per hari; tambah jadwal melalui modal dua kolom yang menumpuk pada ponsel. Link cetak jadwal prodi lama yang menunjuk file tidak tersedia diganti pencetakan halaman jadwal yang sedang dipilih.
- Nilai: identitas kelas, status periode, input nilai akhir, pratinjau grade dari konfigurasi database, progres pengisian, dan tombol penyimpanan di akhir formulir. Nama field serta layanan transaksi nilai tetap sama.
- Daftar mahasiswa akademik: filter periode/angkatan/pencarian dan akses ke detail atau cetak. Batas SKS disimpan untuk daftar yang tampil, dalam satu transaksi.
- Pada ponsel, tabel akademik tampil sebagai baris kartu dengan label tiap kolom, sehingga tidak membutuhkan geser tabel ke samping.
- Profil mahasiswa/dosen memakai fieldset untuk data pribadi, akademik, kontak, serta keluarga mahasiswa. Field tetap memakai handler dan CSRF lama. Form tambah/edit panjang dalam offcanvas dikelompokkan setelah dirender, tanpa mengganti nama field.
- KRS, KHS, transkrip, dan jadwal cetak memakai layout A4 bersama, kop dari pengaturan kampus, tabel berulang antarmuka, ringkasan, serta area tanda tangan. Dokumen dibuka sebagai pratinjau dan tombol Cetak dokumen membuka dialog cetak. Laporan master cetak lainnya mendapat normalisasi lebar dan gaya tabel.

Status periode mengikuti layanan akademik yang sudah ada: tanggal mulai `0000-00-00` berarti terbuka tanpa batas tanggal. Tampilan status diselaraskan dengan aturan itu, tanpa mengubah aturan penyimpanan.

Pagination server diterapkan pada master mahasiswa/dosen/mata kuliah/prodi/institusi, akun, relasi prodi, perwalian, dan daftar mahasiswa akademik baca-saja. Daftar referensi kecil memakai pagination browser. Tidak ada perubahan kebijakan nilai/perulangan mata kuliah. Semua pengujian browser dan PDF cetak menggunakan data sintetis pada salinan aplikasi.

## Penyempurnaan UI menyeluruh

- Menu bar putih di atas pada desktop, dropdown berkelompok, ikon, dan penanda maroon untuk halaman aktif. Ponsel/tablet memakai menu yang dibuka di bawah header, dengan Escape dan pengembalian fokus.
- Dashboard admin menampilkan status periode KRS/nilai; prodi menampilkan jumlah mahasiswa yang belum diberi batas SKS; dosen menampilkan nilai yang belum diisi; mahasiswa mendapat ringkasan pengambilan SKS. Semua angka berasal dari data dalam lingkup akun.
- Tabel master dan daftar mahasiswa baca-saja memiliki pencarian (memakai AJAX lama jika tersedia), filter status/angkatan/semester sesuai kolom, pagination 15/25/50 baris, dan pilihan kolom pada tabel lebar. Form nilai, batas SKS, dan pemilihan KRS tetap menampilkan semua kontrol agar penyimpanan lengkap. Filter daftar utama diproses server sebelum LIMIT, dengan scope akun tetap berlaku.
- Jadwal prodi/dosen/mahasiswa memakai kartu per hari berisi waktu, mata kuliah, SKS, dosen, ruangan, serta peserta/aksi sesuai hak akun. Daftar kelas input nilai tetap memakai tabel dengan progres.
- Form mendapat label yang terhubung dengan kontrol, penanda wajib, pesan kesalahan dekat kolom, tombol simpan maroon, dan nama aksi untuk tombol ikon. Validasi serta nama field backend tetap dipertahankan.
- Status menggunakan warna hijau untuk aktif/terbuka, kuning untuk hal yang perlu perhatian, dan abu-abu untuk terkunci/belum dibuka. Notifikasi memakai peran aksesibilitas status/alert.
- Login tetap sederhana dengan latar putih. Tampilan cetak tidak memuat sidebar atau kontrol tabel.

Pengujian browser mencakup seluruh peran, desktop dan ponsel, menu atas/Escape/fokus, tabel AJAX/pagination/filter/pilihan kolom, pesan validasi, kartu jadwal, profil, pengisian KRS, izin semester, input nilai, serta cetak A4. Data pengujian sintetis dan database sementara terpisah dari database lokal maupun produksi.

## Penyempurnaan mobile 4 Oktober 2026

- Menu atas tetap dipertahankan. Panel mobile memiliki tinggi maksimum 65svh dan scroll internal, serta ditutup setelah memilih halaman.
- Input teks/angka/select 16px dan kontrol utama minimal 44px pada ponsel. KRS memakai label pilihan besar.
- Tabel master/akun/relasi prodi/perwalian menjadi kartu berlabel pada ponsel; informasi dan aksi tidak lagi berada di sisi kanan tabel. Pilihan kolom tersedia di desktop.
- Dashboard memakai tiga statistik ringkas. Detail identitas dan kelompok form dapat dibuka, dan otomatis dibuka ketika validasi menemukan kesalahan.
- Bar SKS/Simpan KRS serta tindakan nilai/batas SKS/profil tetap terlihat pada ponsel. Konten diberi ruang bawah; posisi mengikuti visualViewport saat keyboard mengubah viewport, dengan safe-area CSS. Form offcanvas memiliki footer simpan sendiri di luar bagian yang digulir.
- Tabel AJAX dibatasi server 15/25/50 baris, debounce 250ms, pembatalan permintaan lama, pesan loading dan retry. Dialog baris hanya dirender untuk halaman data yang dimuat.
- Form nilai/batas SKS/pilihan KRS tidak dipotong menjadi halaman, sehingga seluruh isian yang diperlukan tetap dikirim. Tidak ada perubahan rumus atau kebijakan akademik.
- Pengujian perangkat fisik Safari/iPhone dan keyboard native masih perlu dilakukan; emulasi viewport tidak menggantikannya.
