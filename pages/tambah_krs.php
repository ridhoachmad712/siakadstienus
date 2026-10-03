<?php 
session_start();
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login($koneksi);
$username=$_SESSION['username'];

$level=$_SESSION['level'];
// pemeriksaan login dilakukan oleh config/auth.php di atas
// --------------------------------------------------
// pengaturan aplikasi 
$pengaturan=mysqli_query($koneksi,"SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan=mysqli_fetch_array($pengaturan);
// MAHASISWA
if ($level=='mhs') {
  $mhs=mysqli_query($koneksi,"SELECT * FROM mahasiswa
    LEFT JOIN tbl_jk ON mahasiswa.id_jk=tbl_jk.id_jk
    LEFT JOIN tbl_agama ON mahasiswa.id_agama=tbl_agama.id_agama WHERE nim_npm='$username'");
  $tampil_mhs=mysqli_fetch_array($mhs);
}

?>
<?php $judul_halaman = "Penawaran Mata Kuliah"; include "../template/head.php"; ?>
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
                Penawaran Mata Kuliah
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
                <div class="table-responsive">
                  <div class="my-2 my-md-0 flex-grow-1 flex-md-grow-0 order-first order-md-last">

                    <div class="col-lg-6">
                      <div class="card">
                        <div class="card-body">

                          <table
                          class="table table-vcenter card-table">
                          <thead>
                            <tr>
                              <th>Tahun/Angkatan</th>
                              <td>: <?= nilai($tampil_mhs, 'thn_masuk'); ?></td>
                            </tr>
                            <tr>
                              <th>NIM</th>
                              <td>: <?= nilai($tampil_mhs, 'nim_npm'); ?></td>
                            </tr>
                            <tr>
                              <th>Nama</th>
                              <td>: <?= nilai($tampil_mhs, 'nama_mhs'); ?></td>
                            </tr>
                            <tr>
                              <th>Jurusan/Program Studi</th>
                              <td>:</td>
                            </tr>
                            <tr>
                              <th>Penasehat Akademik</th>
                              <td>:</td>
                            </tr>
                          </thead>
                        </table>

                      </div>
                    </div>
                  </div>

                </div>

               <!--  <div class="card-body">
                  <table>
                    <tr>
                      <td><a class="btn btn-yellow" href="" style="text-decoration: none;">Cetak KRS</a></td>
                      <td><a class="btn btn-info" href="tambah_krs" style="text-decoration: none;">Tambah KRS</a></td>
                    </tr>
                  </table>
                </div> -->
                <!-- tampil data -->
                <form action="" method="post">
<?php siakad_csrf_field(); ?>
                  <table class="table table-vcenter card-table">
                    <thead>
                      <tr>
                        <th style="text-align: center;">Ambil</th>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th>sks</th>
                        <th>Dosen</th>
                        <th>Semester</th>
                        <th>Ruang</th>
                        <th>Jam Kuliah</th>
                        <th>Status</th>

                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td style="text-align: center;"><input type="checkbox" name="id_jadwal[]"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                      </tr> 
                    </tbody>
                  </table>

                  <div class="card-body">
                    <table>
                      <tr>
                        <td><input type="submit" name="ambil" value="Ambil" class="btn btn-info"></td>
                        <td><button type="reset" class="btn btn-yellow">Reset</button></td>
                        <td><a href="krs" class="btn btn-danger">Batal</a></td>
                      </tr>
                    </table>
                  </div>

                </form>
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


<!-- Libs JS -->
<!-- Tabler Core -->
  <?php include "../template/scripts.php"; ?>
<!-- javascript search data fakultas -->

</body>
</html>