<?php 
session_start();
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login($koneksi);
$username=$_SESSION['username'];

$level=$_SESSION['level'];
// data fakultas
$kode_fakultas=mysqli_real_escape_string($koneksi, $_GET['kode_fakultas']);
$fakultas=mysqli_query($koneksi,"SELECT * FROM tbl_fakultas WHERE kode_fakultas='$kode_fakultas'");
$t_fakultas=mysqli_fetch_array($fakultas);
// -------------------------
// pemeriksaan login dilakukan oleh config/auth.php di atas
// --------------------------------------------------
// pengaturan aplikasi 
$pengaturan=mysqli_query($koneksi,"SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan=mysqli_fetch_array($pengaturan);
// tambah data fakultas
if (isset($_POST['tambah'])) {
  $nama_ruangan = sql_aman($koneksi, $_POST['nama_ruangan'] ?? null);
  $lantai = sql_aman($koneksi, $_POST['lantai'] ?? null);
  mysqli_query($koneksi,"INSERT INTO tbl_ruangan VALUES(NULL,'$kode_fakultas','$nama_ruangan','$lantai')");
  echo "<script>window.alert('Tambah Ruangan Berhasil !!!')
  window.location='ruangan?kode_fakultas=$kode_fakultas'</script>";
}
// Hapus data

?>
<?php $judul_halaman = "Data Ruangan"; include "../template/head.php"; ?>
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
              <h4>Data Ruangan</h4>
              <h5>
                FAKULTAS <?= nilai($t_fakultas, 'nama_fakultas'); ?>
              </h5>
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
                  <a href="fakultas" class="btn btn-secondary">
                    Kembali
                  </a>
                  <a href="#" class="btn" data-bs-toggle="modal" data-bs-target="#modal-report">
                    <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                    Tambah Ruangan
                  </a>
                </div>
              </div>
              <div class="table-responsive">
                <div class="my-2 my-md-0 flex-grow-1 flex-md-grow-0 order-first order-md-last">

                </div>
                <!-- tampil data -->
                <div>
                  <table class="table table-vcenter card-table">
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>Nama Ruangan</th>
                        <th>Lantai</th>
                        <th>Hapus</th>
                      </tr>
                    </thead>
                    <?php 
                    $no=1;
                    $ruang=mysqli_query($koneksi,"SELECT * FROM tbl_ruangan WHERE kode_fakultas='$kode_fakultas' ORDER BY nama_ruangan ASC");
                    while ($t_ruangan=mysqli_fetch_array($ruang)) {
                      ?>
                      <tr>
                        <td><?= $no++; ?>.</td>
                        <td><?= $t_ruangan['nama_ruangan']; ?></td>
                        <td><?= $t_ruangan['lantai'] ?></td>
                        <td><a href="ruangan?aksi=hapus&kode_ruangan=<?= $t_ruangan['kode_ruangan']; ?>&kode_fakultas=<?= $kode_fakultas; ?>" onclick="return confirm('Hapus data ruang ini ?')">
                          <!-- Download SVG icon from http://tabler-icons.io/i/trash -->
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                        </a></td>
                      </tr>
                    <?php } ?>
                  </table>
                </div>
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

<!-- input data  -->
<div class="modal modal-blur fade" id="modal-report" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form action="" method="post">
<?php siakad_csrf_field(); ?>
        <div class="modal-header">
          <h5 class="modal-title">Tambah Ruangan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Nama Ruangan</label>
            <input type="text" placeholder="Nama Ruangan" autocomplete="off" class="form-control" name="nama_ruangan" required>
          </div>
          <div class="mb-3">
            <label>Lantai</label>
            <input type="text" placeholder="Lantai" class="form-control" name="lantai">
          </div>
        </div>
        <div class="modal-footer">
          <a href="#" class="btn" data-bs-dismiss="modal">
            Tutup
          </a>
          <button type="submit" name="tambah" class="btn btn-info">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!--  -->

<!-- Libs JS -->
<!-- Tabler Core -->
  <?php include "../template/scripts.php"; ?>
<!-- javascript search data fakultas -->

</body>
</html>