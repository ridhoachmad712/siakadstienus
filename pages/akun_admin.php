<?php 
session_start();
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login($koneksi);
require_once "../config/login_service.php";
require_once "../config/academic.php";
if (isset($_POST['tambah'])) {
    siakad_form_proses(function() use ($koneksi) { siakad_buat_akun($koneksi,$_POST['username']??null,$_POST['password']??null,'admin',$_POST['kode_prodi']??''); }, 'akun_admin');
}

$username=$_SESSION['username'];

$level=$_SESSION['level'];
// pemeriksaan login dilakukan oleh config/auth.php di atas
// --------------------------------------------------
// pengaturan aplikasi 
$pengaturan=mysqli_query($koneksi,"SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan=mysqli_fetch_array($pengaturan);
// tambah data fakultas

// Edit data fakultas

// Hapus data

?>
<?php $judul_halaman = "Data Akun Admin"; include "../template/head.php"; ?>
  <div class="wrapper">
    <?php 
    require_once"../template/header.php";
    ?>
    <div class="navbar-expand-md">
      <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar navbar-light">
          <div class="container-xl">
            <?php 
            require_once"../template/menu.php";
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
                Data Akun Admin
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
                  <a class="btn" data-bs-toggle="offcanvas" href="#offcanvasStart" role="button" aria-controls="offcanvasStart">
                    Tambah Data
                  </a>
                </div>
              </div>
              <div class="table-responsive">
                <!-- tampil data -->
                <table class="table table-vcenter card-table">
                  <thead>
                    <th>NO</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Opsi</th>
                  </thead>
                  <tbody>
                    <?php 
                    $no=1;
                    require_once '../config/table_pagination.php';
[$sk_list_sql,$sk_offset]=siakad_tabel_statis($koneksi,"SELECT * FROM user WHERE level='admin'",["username"]);
$no=$sk_offset+1;
$user=mysqli_query($koneksi,$sk_list_sql);
                    while ($t_user=mysqli_fetch_array($user)) {
                      ?>
                      <tr>
                        <td><?= $no++ ?>.</td>
                        <td><?= $t_user['username']; ?></td>
                        <td>Tersimpan aman</td>
                        <td>
                          <a onclick="return confirm('Hapus data user ini ?')" href="akun_admin?aksi=hapus&id_user=<?= $t_user['id_user']; ?>">
                            <!-- Download SVG icon from http://tabler-icons.io/i/trash -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                          </a>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
                <!-- ------------ -->
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>
    <?php 
    require_once"../template/footer.php";
    ?>
  </div>
</div>


<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasStart" aria-labelledby="offcanvasStartLabel">
  <form action="" method="post">
<?php siakad_csrf_field(); ?>
    <div class="offcanvas-header">
      <h2 class="offcanvas-title" id="offcanvasStartLabel">Tambah Akun Admin</h2>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
     <div>
      <div class="mb-3">
        <label>Username</label> 
        <input type="text" name="username" placeholder="Username" class="form-control" required="require">
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" minlength="8" maxlength="72" name="password" placeholder="Password" class="form-control" required="require">
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
<!-- Libs JS -->
<!-- Tabler Core -->
  <?php include "../template/scripts.php"; ?>
<!-- javascript search data fakultas -->

</body>
</html>
