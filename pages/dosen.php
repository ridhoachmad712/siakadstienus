<?php
session_start();
include "../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login($koneksi);
$username = $_SESSION['username'];

$level = $_SESSION['level'];
// pemeriksaan login dilakukan oleh config/auth.php di atas
// --------------------------------------------------
// pengaturan aplikasi 
$pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan = mysqli_fetch_array($pengaturan);
// tambah data fakultas
if (isset($_POST['tambah'])) {
  $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
  $nama_dosen = mysqli_real_escape_string($koneksi, $_POST['nama_dosen']);
  $id_jk = mysqli_real_escape_string($koneksi, $_POST['id_jk']);
  $id_agama = mysqli_real_escape_string($koneksi, $_POST['id_agama']);
  $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
  $email = mysqli_real_escape_string($koneksi, $_POST['email']);
  $tmp_lhr_dosen = mysqli_real_escape_string($koneksi, $_POST['tmp_lhr_dosen']);
  $tgl_lhr_dosen = mysqli_real_escape_string($koneksi, $_POST['tgl_lhr_dosen']);
  $email = mysqli_real_escape_string($koneksi, $_POST['email']);
  $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
  $input = mysqli_query($koneksi, "INSERT INTO dosen VALUES('$nip','$nama_dosen','$id_jk','$id_agama','$alamat','','$tmp_lhr_dosen','$tgl_lhr_dosen','$email','$no_telp')");
  if ($input == 1) {
    echo "<script>window.alert('Dosen Berhasil ditambah !!!')
    window.location='dosen'</script>";
  }
}
// Edit data fakultas
if (isset($_POST['update'])) {
  $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
  $nama_dosen = mysqli_real_escape_string($koneksi, $_POST['nama_dosen']);
  $id_jk = mysqli_real_escape_string($koneksi, $_POST['id_jk']);
  $id_agama = mysqli_real_escape_string($koneksi, $_POST['id_agama']);
  $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
  $tmp_lhr_dosen = mysqli_real_escape_string($koneksi, $_POST['tmp_lhr_dosen']);
  $tgl_lhr_dosen = mysqli_real_escape_string($koneksi, $_POST['tgl_lhr_dosen']);
  $email = mysqli_real_escape_string($koneksi, $_POST['email']);
  $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
  $update = mysqli_query($koneksi, "UPDATE dosen SET nama_dosen='$nama_dosen', id_jk='$id_jk', id_agama='$id_agama', alamat='$alamat', tmp_lhr_dosen='$tmp_lhr_dosen', tgl_lhr_dosen='$tgl_lhr_dosen', email='$email', no_telp='$no_telp' WHERE nip='$nip'");
  if ($update == 1) {
    echo "<script>window.alert('Data Berhasil diupdate !!!')
    window.location='dosen'</script>";
  }
}
// Hapus data

// ==================================================
// IMPORT DATA (.xls / .xlsx / .csv) - memakai config/import_master.php
// ==================================================
$hasil_import = null;
if (isset($_POST['import'])) {
  require_once "../config/import_master.php";

  $hasil_import = imp_hasil_baru();
  $upload = null;
  try {
    $upload = imp_terima_upload('dosen');
    $rows   = imp_baca_spreadsheet($upload['path'], $upload['ext']);
    $hasil_import = imp_proses_dosen($koneksi, $rows, isset($_POST['update_jika_ada']));
    if ($hasil_import['total'] === 0) {
      $hasil_import['fatal'] = 'File terbaca, tetapi tidak ada baris data di bawah baris header. Pastikan data dimulai pada baris ke-2 di sheet pertama.';
    }
  } catch (Throwable $e) {
    $hasil_import['fatal'] = $e->getMessage();
  }
  if ($upload) {
    imp_bersihkan($upload['path']);
  }
}
?>
<?php $judul_halaman = "Master Data Dosen"; include "../template/head.php"; ?>
  <div class="wrapper">
    <?php
    require_once "../template/header.php";
    ?>
    <div class="navbar-expand-md">
      <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar navbar-light">
          <div class="container-xl">
            <?php
            require_once "../template/menu.php";
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="page-wrapper">
      <div class="container-xl">
        <!-- Page title -->
        <div class="page-header d-print-none">
          <div class="row align-items-center">
            <div class="col">
              <h2 class="page-title">
                Master Data Dosen
              </h2>
            </div>
          </div>
        </div>
      </div>
      <div class="page-body">
        <div class="container-xl">
          <div class="row row-cards">
            <?php
            // ringkasan hasil import
            if ($hasil_import) {
              imp_render_hasil($hasil_import);
            }
            ?>




            <div class="col-12">
              <div class="card">

                <div class="card">
                  <div class="card-body sk-master-toolbar">
                    <a class="btn btn-primary" data-bs-toggle="offcanvas" href="#offcanvasStart" role="button" aria-controls="offcanvasStart">
                      Tambah Data
                    </a>
                    <a class="btn" data-bs-toggle="modal" data-bs-target="#modal-simple">
                      <!-- Download SVG icon from http://tabler-icons.io/i/file-import -->
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                        <path d="M5 13v-8a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5.5m-9.5 -2h7m-3 -3l3 3l-3 3" />
                      </svg>
                      Import Data
                    </a>
                    <a href="template_file/dosen.csv" class="btn btn-secondary">
                      <!-- Download SVG icon from http://tabler-icons.io/i/file-download -->
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                        <line x1="12" y1="11" x2="12" y2="17" />
                        <polyline points="9 14 12 17 15 14" />
                      </svg>
                      Template File
                    </a>
                    <div class="modal modal-blur fade" id="modal-simple" tabindex="-1" role="dialog" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                          <form action="" method="post" enctype="multipart/form-data">
<?php siakad_csrf_field(); ?>
                            <div class="modal-header">
                              <h5 class="modal-title">Import File Data Dosen</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <div class="mb-3">
                <label class="form-label">File data (.xls, .xlsx, atau .csv)</label>
                <input type="file" accept=".xls,.xlsx,.csv" name="dosen" class="form-control" required="required">
                <small class="form-hint">Urutan kolom harus sama dengan Template File. Data dimulai pada baris ke-2.</small>
              </div>
              <label class="form-check">
                <input type="checkbox" class="form-check-input" name="update_jika_ada" value="1">
                <span class="form-check-label">Perbarui data bila kode/NIP sudah ada di database</span>
              </label>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                              <button type="submit" name="import" class="btn btn-success">
                                <!-- Download SVG icon from http://tabler-icons.io/i/file-import -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                  <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                  <path d="M5 13v-8a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5.5m-9.5 -2h7m-3 -3l3 3l-3 3" />
                                </svg>
                                Import
                              </button>
                            </div>
                        </div>
                      </div>
                      </form>
                    </div>
                  </div>
                </div>


                <div class="table-responsive">
                  <div class="my-2 my-md-0 flex-grow-1 flex-md-grow-0 order-first order-md-last">
                    <form action="." method="get">
                      <div class="input-icon">
                        <span class="input-icon-addon">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <circle cx="10" cy="10" r="7" />
                            <line x1="21" y1="21" x2="15" y2="15" />
                          </svg>
                        </span>
                        <input type="text" name="search_text" id="search_text" class="form-control" placeholder="Pencarian…" aria-label="Cari data dosen">
                      </div>
                    </form>
                  </div>
                  <!-- tampil data -->
                  <div id="data-dosen"></div>
                  <!-- ------------ -->
                </div>
              </div>
            </div>


          </div>
        </div>
      </div>
      <?php
      require_once "../template/footer.php";
      ?>
    </div>
  </div>


  <div class="page-body">
    <div class="container-xl">


      <div style="overflow: auto;" class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasStart" aria-labelledby="offcanvasStartLabel">
        <form action="" method="post" enctype="multipart/form-data">
<?php siakad_csrf_field(); ?>
          <div class="offcanvas-header">
            <h2 class="offcanvas-title" id="offcanvasStartLabel">Tambah data Dosen</h2>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <div>
              <div class="mb-3">
                <label>NIDN</label>
                <input type="text" name="nip" class="form-control" placeholder="NIDN" required="require">
              </div>
              <div class="mb-3">
                <label>Nama Dosen</label>
                <input type="text" name="nama_dosen" class="form-control" required="required">
              </div>
              <div class="mb-3">
                <label>Jenis Kelamin</label>
                <select class="form-control" name="id_jk" required="required">
                  <option value="">--Pilih--</option>
                  <option value="1">Laki-Laki</option>
                  <option value="2">Perempuan</option>
                </select>
              </div>
              <div class="mb-3">
                <label>Agama</label>
                <select class="form-control" name="id_agama" required="required">
                  <option value="">--Pilih--</option>
                  <option value="1">Islam</option>
                  <option value="2">Kristen Protestan</option>
                  <option value="3">Kristen Katolik</option>
                  <option value="4">Hindu</option>
                  <option value="5">Budha</option>
                  <option value="6">Konghucu</option>
                </select>
              </div>
              <div class="mb-3">
                <label>Alamat</label>
                <textarea class="form-control" name="alamat"></textarea>
              </div>
              <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control">
              </div>
              <div class="mb-3">
                <label>Tempat Lahir</label>
                <input type="text" name="tmp_lhr_dosen" class="form-control" required="required">
              </div>
              <div class="mb-3">
                <label>Tanggal Lahir</label>
                <input type="date" name="tgl_lhr_dosen" class="form-control" required="required">
              </div>
              <div class="mb-3">
                <label>No Telp</label>
                <input type="text" name="no_telp" class="form-control">
              </div>
            </div>
            <div class="mt-3">
              <button class="btn" type="submit" name="tambah">
                Simpan
              </button>
              <button class="btn" type="button" data-bs-dismiss="offcanvas">
                Tutup
              </button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>
  <!-- Libs JS -->
  <!-- Tabler Core -->
  <?php include "../template/scripts.php"; ?>
  <!-- javascript search data fakultas -->

</body>

</html>
