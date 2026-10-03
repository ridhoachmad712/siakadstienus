<?php 
session_start();
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login($koneksi);
$username=$_SESSION['username'];

$level=$_SESSION['level'];
$kode_prodi=$_SESSION['kode_prodi'];
// pemeriksaan login dilakukan oleh config/auth.php di atas
// --------------------------------------------------
// pengaturan aplikasi 
$pengaturan=mysqli_query($koneksi,"SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan=mysqli_fetch_array($pengaturan);
// tambah data fakultas
if (isset($_POST['tambah'])) {
  $nip=sql_aman($koneksi, $_POST['pilih'] ?? null);
  $jumlah_dipilih = count($nip);
  for($x=0;$x<$jumlah_dipilih;$x++){
    $ok=mysqli_query($koneksi,"INSERT INTO prodi_has_dosen VALUES(NULL,'$kode_prodi','$nip[$x]')");
    $update=mysqli_query($koneksi,"UPDATE user SET kode_prodi='$kode_prodi' WHERE username='$nip[$x]' AND level='dosen'");
    echo "<script>window.alert('$jumlah_dipilih Data dosen yang dipilih berhasil di tambah')
    window.location='jurusan_has_dosen'</script>";
  }
}
// Edit data fakultas

// Hapus data

?>
<?php $judul_halaman = "Master Data Dosen"; include "../template/head.php"; ?>
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
                Master Data Dosen
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
                  <div id="data-dosen"></div>
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
  <script>
    $(document).ready(function(){
      load_data();
      function load_data(query)
      {
        $.ajax({
          url:"get_add_dosen.php",
          method:"post",
          data:{query:query},
          success:function(data)
          {
            $('#data-dosen').html(data);
          }
        });
      }
      $('#search_text').keyup(function(){
        var search = $(this).val();
        if(search != '')
        {
          load_data(search);
        }
        else
        {
          load_data();      
        }
      });
    });
  </script>
</body>
</html>