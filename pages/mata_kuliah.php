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
  $kode_matkul=mysqli_real_escape_string($koneksi, $_POST['kode_matkul']);
  $nama_matkul=mysqli_real_escape_string($koneksi, $_POST['nama_matkul']);
  $sks=mysqli_real_escape_string($koneksi, $_POST['sks']);
  $semester=mysqli_real_escape_string($koneksi, $_POST['semester']);
  $id_jenis_mk=mysqli_real_escape_string($koneksi, $_POST['id_jenis_mk']);
  $input=mysqli_query($koneksi,"INSERT INTO mata_kuliah VALUES('$kode_matkul','$nama_matkul','$sks','$semester','$id_jenis_mk')");
  if ($input == 1) {
    echo "<script>window.alert('Mata Kuliah $nama_matkul Berhasil di tambahkan !!!')
    window.location='mata_kuliah'</script>";
  }else{
    echo "<script>window.alert('Tambah data gagal !!!')
    window.location='mata_kuliah'</script>";
  }
}
// Edit data fakultas
if (isset($_POST['update'])) {
  $kode_matkul=mysqli_real_escape_string($koneksi, $_POST['kode_matkul']);
  $nama_matkul=mysqli_real_escape_string($koneksi, $_POST['nama_matkul']);
  $sks=mysqli_real_escape_string($koneksi, $_POST['sks']);
  $semester=mysqli_real_escape_string($koneksi, $_POST['semester']);
  $id_jenis_mk=mysqli_real_escape_string($koneksi, $_POST['id_jenis_mk']);
  $update=mysqli_query($koneksi,"UPDATE mata_kuliah SET nama_matkul='$nama_matkul', sks='$sks', semester='$semester', id_jenis_mk='$id_jenis_mk' WHERE kode_matkul='$kode_matkul'");
  if ($update == 1) {
    echo "<script>window.alert('Berhasil diupdate menjadi $nama_matkul !!!')
    window.location='mata_kuliah'</script>";
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
<?php $judul_halaman = "Master Data Mata Kuliah"; include "../template/head.php"; ?>
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
               Master Data Mata Kuliah
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
                  <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                  Tambah Data
                </a>
                <a class="btn" data-bs-toggle="modal" data-bs-target="#modal-simple">
                  <!-- Download SVG icon from http://tabler-icons.io/i/file-import -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 13v-8a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5.5m-9.5 -2h7m-3 -3l3 3l-3 3" /></svg>
                  Import Data
                </a>
                <a href="template_file/mata_kuliah.xls" class="btn btn-secondary">
                  <!-- Download SVG icon from http://tabler-icons.io/i/file-download -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><line x1="12" y1="11" x2="12" y2="17" /><polyline points="9 14 12 17 15 14" /></svg>
                  Template File Mata kuliah
                </a>
                <a href="cetak/matakuliah" target="_blank" class="btn">
                  <!-- Download SVG icon from http://tabler-icons.io/i/printer -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><rect x="7" y="13" width="10" height="8" rx="2" /></svg>
                  Cetak Daftar Mata Kuliah
                </a>
              </div>
            </div>
            </br>
            <div class="table-responsive">
              <div class="my-2 my-md-0 flex-grow-1 flex-md-grow-0 order-first order-md-last">
                <form action="." method="get">
                  <div class="input-icon">
                    <span class="input-icon-addon">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7" /><line x1="21" y1="21" x2="15" y2="15" /></svg>
                    </span>
                    <input type="text" name="search_text" id="search_text" class="form-control" placeholder="Cari Mata Kuliah" aria-label="Cari data mata kuliah">
                  </div>
                </form>
              </div>
              </br>
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
          <h5 class="modal-title">Import File Mata Kuliah</h5>
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
        <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
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


<div class="page-body">
  <div class="container-xl">
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasStart" aria-labelledby="offcanvasStartLabel">
      <form action="" method="post">
<?php siakad_csrf_field(); ?>
        <div class="offcanvas-header">
          <h2 class="offcanvas-title" id="offcanvasStartLabel">Tambah Data Mata Kuliah</h2>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
         <div>
          <div class="mb-3">
            <label>Kode Mata Kuliah</label>
            <input type="text" name="kode_matkul" class="form-control" required="require">
          </div>
          <div class="mb-3">
            <label>Nama Mata Kuliah</label>
            <textarea class="form-control" name="nama_matkul" required="required"></textarea>
          </div>
          <div class="mb-3">
            <label>SKS</label>
            <input type="number" name="sks" class="form-control" required="required">
          </div>
          <div class="mb-3">
            <label>Semester</label>
            <input type="text" name="semester" class="form-control" required="required">
          </div>
          <div class="mb-3">
            <label>Jenis Mata Kuliah</label>
            <select class="form-control" name="id_jenis_mk" required>
              <option value="">Pilih Jenis Mata Kuliah</option>
              <?php
              $jenis_mk=mysqli_query($koneksi,"SELECT * FROM tbl_jenis_mk");
              while ($tampil_jenis_mk=mysqli_fetch_array($jenis_mk)) {
               ?>
               <option value="<?= $tampil_jenis_mk['id_jenis_mk']; ?>"><?= $tampil_jenis_mk['jenis_mk']; ?></option>
             <?php } ?>
           </select>
         </div>
       </div>
       <div class="mt-3">
        <button class="btn btn-green" type="submit" name="tambah">
          Simpan
        </button>
        <button class="btn btn-red" type="button" data-bs-dismiss="offcanvas">
          Batal
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
<script>
  $(document).ready(function(){
    load_data();
    function load_data(query)
    {
      $.ajax({
        url:"search_matkul.php",
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
