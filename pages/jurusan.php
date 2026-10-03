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
// tambah data fakultas
if (isset($_POST['tambah'])) {
  $kode_prodi=mysqli_real_escape_string($koneksi, $_POST['kode_prodi']);
  $nama_prodi=mysqli_real_escape_string($koneksi, $_POST['nama_prodi']);
  $ketua_prodi=mysqli_real_escape_string($koneksi, $_POST['ketua_prodi']);
  $jenis=mysqli_real_escape_string($koneksi, $_POST['jenis']);
  $jenjang=mysqli_real_escape_string($koneksi, $_POST['jenjang']);
  $akreditasi=mysqli_real_escape_string($koneksi, $_POST['akreditasi']);
  $input=mysqli_query($koneksi,"INSERT INTO prodi VALUES('$kode_prodi','$nama_prodi','$ketua_prodi','$jenis','$jenjang','$akreditasi')");
  if ($input == 1) {
    echo "<script>window.alert('$jenis $nama_prodi Berhasil di tambahkan !!!')
    window.location='jurusan'</script>";
  }else{
    echo "<script>window.alert('Tambah data gagal !!!')
    window.location='jurusan'</script>";
  }
}
// Edit data fakultas
if (isset($_POST['update'])) {
  $kode_prodi=mysqli_real_escape_string($koneksi, $_POST['kode_prodi']);
  $nama_prodi=mysqli_real_escape_string($koneksi, $_POST['nama_prodi']);
  $ketua_prodi=mysqli_real_escape_string($koneksi, $_POST['ketua_prodi']);
  $jenjang=mysqli_real_escape_string($koneksi, $_POST['jenjang']);
  $akreditasi=mysqli_real_escape_string($koneksi, $_POST['akreditasi']);
  $update=mysqli_query($koneksi,"UPDATE prodi SET nama_prodi='$nama_prodi', ketua_prodi='$ketua_prodi', jenjang='$jenjang', akreditasi='$akreditasi' WHERE kode_prodi='$kode_prodi'");
  if ($update == 1) {
    echo "<script>window.alert('Berhasil diupdate menjadi $nama_prodi !!!')
    window.location='jurusan'</script>";
  }
}
// Hapus data

?>
<?php $judul_halaman = "Data Program Studi"; include "../template/head.php"; ?>
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
                Data Program Studi
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
                    <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                    Tambah Data
                  </a>
                </div>
              </div>

              <div class="table-responsive">
                <div class="my-2 my-md-0 flex-grow-1 flex-md-grow-0 order-first order-md-last">
                  <form action="." method="get">
                    <div class="input-icon">
                      <span class="input-icon-addon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7" /><line x1="21" y1="21" x2="15" y2="15" /></svg>
                      </span>
                      <input type="text" name="search_text" id="search_text" autofocus="on" class="form-control" placeholder="Pencarian…" aria-label="Search in website">
                    </div>
                  </form>
                </div>
                <!-- tampil data -->
                <div id="data-jurusan"></div>
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


<div class="page-body">
  <div class="container-xl">

   

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasStart" aria-labelledby="offcanvasStartLabel">
      <form action="" method="post">
<?php siakad_csrf_field(); ?>
        <div class="offcanvas-header">
          <h2 class="offcanvas-title" id="offcanvasStartLabel">Tambah Data Program Studi</h2>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
         <div>
          <div class="mb-3">
            <label>Kode Program Studi</label> 
            <input type="text" name="kode_prodi" class="form-control" required="require">
          </div>
          <div class="mb-3">
            <label>Nama Program Studi</label> 
            <textarea class="form-control" name="nama_prodi" required="required"></textarea>
          </div>
          <div class="mb-3">
            <label>Ketua Program Studi</label> 
            <select class="form-control" name="ketua_prodi" required="required">
              <option value="">--Pilih--</option>
              <?php 
              $dosen=mysqli_query($koneksi,"SELECT * FROM dosen ORDER BY nama_dosen ASC");
              while ($t_dosen=mysqli_fetch_array($dosen)) {
                $nip=$t_dosen['nip'];
                ?>
                <?php 
                $cek_data=mysqli_num_rows(mysqli_query($koneksi,"SELECT * FROM prodi WHERE ketua_prodi='$nip'"));
                if ($cek_data > 0) {
                  ?>

                <?php }else{ ?>
                  <option value="<?= $t_dosen['nip']; ?>"><?= $t_dosen['nama_dosen']; ?></option>
                <?php }} ?>
              </select>
            </div>
            <div class="mb-3">
              <label>Jenis</label>
              <select class="form-control" name="jenis" required="required">
                <option value="">Pilih Jenis</option>
                <option value="Jurusan">Jurusan</option>
                <option value="Prodi">Program Studi</option>
              </select>
            </div>
            <div class="mb-3">
              <label>Jenjang</label>
              <select class="form-control" name="jenjang" required="required">
                <option value="">Pilih Jenjang Studi</option>
                <option value="D3">D3</option>
                <option value="S1">S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
              </select>
            </div>
            <div class="mb-3">
              <label>Akreditasi</label>
              <select class="form-control" name="akreditasi" required="required">
                <option value="">Pilih Akreditasi</option>
                <option value="Unggul">Unggul</option>
                <option value="Sangat Baik">Sangat Baik</option>
                <option value="Baik">Baik</option>
              </select>
            </div>
          </div>
          <div class="mt-3">
            <button class="btn btn-green" type="submit" name="tambah">
              Simpan
            </button>
            <button class="btn btn-red" type="button" data-bs-dismiss="offcanvas">
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
