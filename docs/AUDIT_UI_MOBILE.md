# Audit UI/UX mobile SIAKAD

Tanggal: 4 Oktober 2026. Acuan implementasi: menu bar atas, commit 3742be4.

## Kesimpulan

Halaman utama sudah responsif dalam hal lebar dan alur akademik tetap berjalan. Pengalaman mobile belum efisien: tabel lama menyembunyikan informasi/aksi di sisi kanan, konteks halaman terlalu panjang sebelum pengguna mencapai isian, kontrol sentuh kecil, dan tindakan penyimpanan jauh dari daftar yang sedang diisi.

Menu bar atas sesuai pilihan pengguna dan tetap menjadi arah desain desktop. Penyempurnaan mobile dapat dilakukan tanpa mengubah pilihan tersebut.

## Metode dan batas pemeriksaan

- Empat peran: admin, program studi, dosen, mahasiswa.
- 42 halaman/alur utama dari navigasi peran ditambah detail KRS/KHS, pilihan mata kuliah, input nilai, dan profil.
- Lima lebar viewport: 320, 360, 390, 414, dan 768 px; tinggi 844 px.
- 210 observasi halaman/viewport: seluruhnya HTTP 200, tidak ditemukan overflow pada lebar halaman, tidak ada error JavaScript yang tertangkap.
- 56 observasi memiliki tabel yang masih harus digeser horizontal. Ini adalah jumlah kombinasi halaman/viewport, bukan 56 halaman berbeda.
- Inspeksi screenshot, ukuran kontrol, ukuran font input, posisi tombol utama, modal/offcanvas tambah mahasiswa/mata kuliah/jadwal, serta menu terbuka.
- Menggunakan Microsoft Edge headless, data sintetis, dan MariaDB sementara. Data lokal/hosting tidak digunakan untuk transaksi pengujian.
- Belum merupakan uji perangkat fisik, Safari iPhone, keyboard virtual, pembaca layar, koneksi lambat, seluruh halaman cetak, atau seluruh dialog edit/hapus. Login dipakai untuk autentikasi; bukan bagian dari 210 observasi mobile.

## Temuan dan rekomendasi

### 1. Tinggi: aksi tabel master berada di luar layar

Pada lebar 390 px, tabel mahasiswa admin melebihi wadah sekitar 334 px; daftar mahasiswa prodi 486 px; daftar dosen prodi 381 px. Nama/NIM terlihat, tetapi status dan aksi di kolom kanan memerlukan geser. Tidak ada petunjuk geser yang jelas.

Ubah representasi mobile daftar master menjadi kartu: nama sebagai judul, NIM/NIDN/kode sebagai identitas, status dan informasi inti di bawahnya, kemudian Detail/Edit. Informasi sekunder dapat dibuka lewat Detail. Desktop tetap tabel. Untuk tabel yang memang perlu dipertahankan, sediakan penanda geser dan kolom identitas yang tetap terlihat.

### 2. Tinggi: ringkasan dan tombol KRS terpisah dari pilihan

Halaman pilihan KRS pada 390 px tingginya 2732 px dengan data uji hanya empat pilihan reguler. Tombol Simpan pilihan berada sekitar y=992, sebelum daftar mata kuliah. Setelah pengguna menggulir dan memilih, ringkasan SKS serta tombol simpan berada di atas dan tidak terlihat. Checkbox berukuran 24 × 24 px.

Gunakan bar tindakan mobile yang tetap terlihat di bawah: SKS terpilih/batas, jumlah pilihan, dan Simpan. Sediakan ruang bawah agar tidak menutupi kartu terakhir dan dukung safe-area perangkat. Identitas mahasiswa dipadatkan, detail PA/prodi dapat dibuka. Seluruh area pilihan atau label besar menjadi target sentuh, tanpa mengubah aturan semester, bentrok, maupun batas SKS.

### 3. Tinggi: beberapa kontrol sentuh dan font input terlalu kecil

Menu hamburger terukur 32 × 32 px, avatar/menu akun tinggi 38 px, sejumlah tombol utama tinggi 35–38 px. Banyak input teks/angka pada profil, pengaturan, pencarian master, input nilai, dan batas SKS terukur 13,6 px; beberapa select filter/pagination 12 px.

Tetapkan target sentuh minimal 44 × 44 px untuk kontrol utama, font input teks/angka/select 16 px pada mobile, serta jarak antartombol. Risiko pembesaran otomatis input di iPhone perlu diverifikasi di Safari fisik; audit ini belum membuktikannya.

Penyebab yang terlihat di kode: aturan input[type=...] di assets/siakad.css memakai .85rem dan lebih spesifik daripada aturan mobile .form-control 16px. Jadi aturan mobile yang sudah ada tidak memenangkan cascade untuk banyak input.

### 4. Sedang: form panjang dan tombol simpan jauh di bawah

Profil mahasiswa pada 390 px tingginya 3463 px; Simpan biodata sekitar y=3295. Input nilai dengan satu peserta saja memiliki tombol Simpan nilai sekitar y=1151. Batas SKS dengan tiga mahasiswa memiliki tombol Simpan batas SKS sekitar y=1600. Ini menunjukkan overhead layout; data riil yang lebih banyak akan memperpanjang daftar.

Gunakan footer tindakan yang tetap terlihat pada mobile, ringkas identitas/konteks, dan tampilkan progres pengisian. Form profil/tambah mahasiswa dapat dibagi menjadi bagian yang dapat dibuka atau langkah identitas, akademik, dan kontak. Pertahankan seluruh field/validasi backend. Uji kondisi keyboard terbuka agar tombol tetap terjangkau dan tidak menutupi input.

### 5. Sedang: menu terbuka mendorong isi halaman terlalu jauh

Pada admin, membuka Data master memperpanjang menu dengan enam submenu dan mendorong dashboard di bawahnya. Header juga cukup tinggi pada ponsel. Pengguna perlu menggulir kembali untuk menutup atau berganti menu.

Pertahankan menu di atas. Pada mobile, batasi tinggi panel di bawah header, izinkan scroll di dalam panel, tampilkan kontrol tutup yang jelas, dan tutup otomatis setelah memilih halaman. Tampilkan penanda halaman aktif. Hindari perpindahan posisi konten yang terlalu besar.

### 6. Sedang: dashboard dan konteks akademik terlalu panjang

Dashboard empat peran pada 390 px sekitar 1660–1711 px. Tiga kartu statistik ditumpuk penuh. Pada KRS/KHS, identitas dan filter panjang membuat daftar utama muncul jauh di bawah viewport pertama.

Padatkan statistik menjadi kartu ringkas, prioritaskan satu tugas utama, letakkan jadwal/status yang relevan lebih awal, dan sederhanakan identitas menjadi nama/NIM/semester dengan detail tambahan yang dapat dibuka. Kurangi padding vertikal secara terukur, bukan mengecilkan teks.

### 7. Sedang: struktur aksi/filter dan gaya belum konsisten

Sebagian daftar prodi menampilkan pencarian/pilihan kolom sebelum tombol tambah; beberapa tombol tambah masih hijau. Pagination di ponsel pecah menjadi beberapa baris. Judul/toolbar lama dan halaman akademik modern memakai struktur berbeda.

Samakan urutan: judul, aksi utama, pencarian/filter, hasil. Gunakan maroon untuk aksi utama, warna netral untuk tindakan sekunder, merah untuk penghapusan. Pagination mobile bisa lebih ringkas dengan jumlah hasil dan kontrol sebelumnya/berikutnya. Pilihan kolom lebih berguna di desktop; mobile mengutamakan kartu dan detail.

### 8. Sedang: daftar besar masih dimuat seluruhnya

Pagination master berjalan di browser setelah semua hasil dimuat. Dengan data lebih besar atau jaringan ponsel lambat, pengguna tetap mengunduh seluruh tabel dan dialog per baris. Audit ini tidak mengukur kinerja jaringan nyata.

Tambahkan pencarian/filter/pagination di server untuk daftar baca-saja. Muat dialog edit sesuai kebutuhan. Jangan memotong form nilai/batas SKS secara sembarang karena penyimpanan harus tetap mencakup data yang diharapkan dan menjaga perubahan yang belum disimpan.

## Bagian yang sudah baik

- Tidak ada halaman melebar melewati viewport pada sampel yang diuji.
- Jadwal kartu per hari nyaman dibaca dan tidak membutuhkan geser horizontal.
- KRS/KHS/transkrip/input nilai sudah memiliki baris mobile berlabel.
- Filter akademik menjadi satu kolom, pesan status dan validasi sudah tersedia.
- Warna maroon, kartu putih, dan hirarki judul lebih konsisten daripada layout lama.
- Alur autentikasi serta pemeriksaan HTTP/integritas pada database uji tetap lolos.

## Urutan implementasi yang disarankan

1. KRS: ringkasan/tombol tetap terlihat, pilihan dengan target sentuh besar.
2. Kontrol global: ukuran sentuh dan font input, termasuk pengujian Safari/keyboard.
3. Tabel master/prodi: kartu mobile dan aksi yang selalu terjangkau.
4. Form nilai/batas SKS/profil: tindakan simpan tetap terlihat dan konteks ringkas.
5. Menu atas mobile, dashboard ringkas, toolbar/pagination konsisten.
6. Optimasi pemuatan data dan pengujian koneksi lambat.

## Kriteria penerimaan

- Tidak ada overflow halaman pada 320–768 px.
- Identitas/status/aksi utama daftar dapat dijangkau tanpa geser horizontal.
- Pengisian KRS menampilkan SKS dan tombol simpan selama memilih mata kuliah.
- Kontrol utama memiliki target sentuh 44 px, input teks/angka/select memakai 16 px.
- Footer tindakan tidak menutupi konten atau keyboard dan bekerja dengan safe-area.
- Tampilan desktop tetap menu bar atas.
- Aturan semester KRS, otorisasi, CSRF, transaksi dan nilai akhir dosen tidak berubah.
- Lulus pengujian empat peran dan pemeriksaan perangkat fisik Android/iPhone.

Bukti terukur: tests/artifacts/mobile-audit/audit.json. Screenshot lokal menggunakan data sintetis dan diabaikan Git.

## Status implementasi 4 Oktober 2026

Rekomendasi telah diterapkan: bar KRS/Simpan, kontrol mobile 44px dan input 16px, kartu data mobile, detail identitas/form yang dapat dibuka, footer offcanvas, menu atas dengan batas tinggi, dashboard ringkas, toolbar/pagination, serta pagination server untuk daftar master utama, akun, relasi prodi, perwalian, dan mahasiswa akademik baca-saja. Form pengisian nilai/batas SKS/KRS tetap lengkap. Dialog baris dibatasi pada halaman hasil yang dimuat.

Pengujian perangkat fisik Safari/iPhone, keyboard native, dan jaringan ponsel nyata tetap perlu dilakukan. Mekanisme visualViewport/safe-area dan debounce/cancel/retry sudah tersedia; ini tidak menggantikan pengujian fisik.

Verifikasi sesudah implementasi: 294 pemeriksaan integrasi/browser/HTTP lolos; audit ulang 210 kombinasi halaman/viewport menunjukkan 0 overflow halaman dan 0 tabel dengan geser horizontal.
