<?php
session_start();
include "../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login($koneksi);
require_once "../config/login_service.php";
require_once "../config/academic.php";
if (isset($_POST['simpan'])) {
    siakad_form_proses(function() use ($koneksi) { siakad_buat_akun_batch($koneksi,$_POST['pilih']??null,'dosen'); }, 'akun_dosen');
}

$username = $_SESSION['username'];

$level = $_SESSION['level'];
// pemeriksaan login dilakukan oleh config/auth.php di atas
// --------------------------------------------------
// pengaturan aplikasi 
$pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan = mysqli_fetch_array($pengaturan);
// tambah data fakultas

// Edit data fakultas

// Hapus data

?>
<?php $judul_halaman = "Data Akun Dosen"; include "../template/head.php"; ?>
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
                Data Akun Dosen
              </h2>
            </div>
          </div>
        </div>
      </div>
      <div class="page-body">
        <div class="container-xl">
          <div class="row row-cards">


            <div class="col-12">
              <div class="card">
                <div class="card">
                  <div class="card-body">
                    <a class="btn" data-bs-toggle="modal" data-bs-target="#modal-scrollable">
                      Tambah Data
                    </a>
                  </div>
                </div>
                <div class="table-responsive">
                  <!-- tampil data -->
                  <table class="table table-vcenter card-table">
                    <thead>
                      <th>NO</th>
                      <th>Nama Dosen</th>
                      <th>Username</th>
                      <th>Password</th>
                      <th>Opsi</th>
                    </thead>
                    <?php
                    $no = 1;
                    $user = mysqli_query($koneksi, "SELECT * FROM user
                    INNER JOIN dosen ON user.username=dosen.nip WHERE level='dosen'");
                    while ($t_user = mysqli_fetch_array($user)) {
                    ?>
                      <tr>
                        <td><?= $no++ ?>.</td>
                        <td><?= $t_user['nama_dosen']; ?></td>
                        <td><?= $t_user['username']; ?></td>
                        <td>Tersimpan aman</td>
                        <td>
                          <a onclick="return confirm('Hapus data user ini ?')" href="akun_dosen?aksi=hapus&id_user=<?= $t_user['id_user']; ?>">
                            <!-- Download SVG icon from http://tabler-icons.io/i/trash -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                              <line x1="4" y1="7" x2="20" y2="7" />
                              <line x1="10" y1="11" x2="10" y2="17" />
                              <line x1="14" y1="11" x2="14" y2="17" />
                              <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                              <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                            </svg>
                          </a>
                        </td>
                      </tr>
                    <?php } ?>
                  </table>
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



  <form action="" method="post">
<?php siakad_csrf_field(); ?>
    <div class="modal modal-blur fade" id="modal-scrollable" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Akun Dosen</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <table class="table table-vcenter card-table table-striped">
              <thead>
                <tr>
                  <th>Opsi</th>
                  <th>NIDN</th>
                  <th>Nama Dosen</th>
                </tr>
              </thead>
              <?php
              $dosen = mysqli_query($koneksi, "SELECT * FROM dosen ORDER BY nama_dosen ASC");
              while ($t_dosen = mysqli_fetch_array($dosen)) {
                $nip = $t_dosen['nip'];
              ?>
                <?php
                $cek_data = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM user WHERE username='$nip'"));
                if ($cek_data > 0) {
                ?>

                <?php } else { ?>
                  <tr>
                    <td><input type="checkbox" name="pilih[]" value="<?= $t_dosen['nip']; ?>"></td>
                    <td><?= $t_dosen['nip']; ?></td>
                    <td><?= $t_dosen['nama_dosen']; ?></td>
                  </tr>
              <?php }
              } ?>
            </table>
          </div>
          <div class="modal-footer">
            <button type="submit" name="simpan" class="btn btn-info">Tambah</button>
          </div>
        </div>
      </div>
    </div>
  </form>
  <!-- Libs JS -->
  <!-- Tabler Core -->
  <?php include "../template/scripts.php"; ?>
  <!-- javascript search data fakultas -->

</body>

</html>