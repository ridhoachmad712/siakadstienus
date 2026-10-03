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
// tambah data
if (isset($_POST['tambah'])) {
  $kode_matkul=sql_aman($koneksi, $_POST['pilih'] ?? null);
  $jumlah_dipilih = count($kode_matkul);
  for($x=0;$x<$jumlah_dipilih;$x++){
    $ok=mysqli_query($koneksi,"INSERT INTO prodi_has_matkul VALUES(NULL,'$kode_prodi','$kode_matkul[$x]')");
    echo "<script>window.alert('$jumlah_dipilih Data mata kuliah yang dipilih berhasil di tambah')
    window.location='jurusan_has_matkul'</script>";
  }
}
// Edit data fakultas

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
    $upload = imp_terima_upload('mata_kuliah');
    $rows   = imp_baca_spreadsheet($upload['path'], $upload['ext']);
    $hasil_import = imp_proses_matkul($koneksi, $rows, isset($_POST['update_jika_ada']));
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
<?php $judul_halaman = "Master Mata Kuliah"; include "../template/head.php"; ?>
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
                Master Mata Kuliah
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
                  <div id="data-matkul"></div>
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

  <div class="modal modal-blur fade" id="modal-simple" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form action="" method="post" enctype="multipart/form-data">
<?php siakad_csrf_field(); ?>
          <div class="modal-header">
            <h5 class="modal-title">Import File Mata kuliah</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
           <div class="mb-3">
                <label class="form-label">File data (.xls, .xlsx, atau .csv)</label>
                <input type="file" accept=".xls,.xlsx,.csv" name="mata_kuliah" class="form-control" required="required">
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
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 13v-8a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5.5m-9.5 -2h7m-3 -3l3 3l-3 3" /></svg>
            Import
          </button>
        </div>
      </div>
    </div>
  </form>
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
        url:"get_add_matkul.php",
        method:"post",
        data:{query:query},
        success:function(data)
        {
          $('#data-matkul').html(data);
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