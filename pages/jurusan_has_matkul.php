<?php 
session_start();
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login($koneksi);
$username=$_SESSION['username'];

$level=$_SESSION['level'];
$kode_prodi=$_SESSION['kode_prodi'];
// 
$prodi=mysqli_fetch_array(mysqli_query($koneksi,"SELECT * FROM prodi WHERE kode_prodi='$kode_prodi'"));
// 
// pemeriksaan login dilakukan oleh config/auth.php di atas
// --------------------------------------------------
// pengaturan aplikasi 
$pengaturan=mysqli_query($koneksi,"SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan=mysqli_fetch_array($pengaturan);

function tgl_indo($tanggal){
  $bulan = array (
    1 => 'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
  );
  $pecahkan = explode('-', $tanggal);
  return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}
// Hapus data

?>
<?php $judul_halaman = "Mata Kuliah Program Studi"; include "../template/head.php"; ?>
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
                Data Mata Kuliah Program Studi <?= nilai($prodi, 'nama_prodi'); ?>
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
                  <div class="card">
                    <div class="card-body">
                      <a class="btn" href="add_matkul_jurusan">
                        Tambah Mata Kuliah
                      </a>
                    </div>
                  </div>
                  <!-- tampil data -->
                  <table class="table table-vcenter card-table">
                    <thead>
                     <tr>
                      <th style="text-align: center;">NO</th>
                      <th style="text-align: center;">KODE  </th>
                      <th style="text-align: center;">MATA KULIAH</th>
                      <th style="text-align: center;">SKS</th>
                      <th style="text-align: center;">SEMESTER</th>
                      <th style="text-align: center;">JENIS MATA KULIAH</th>
                      <th style="text-align: center;">HAPUS</th>
                    </tr>
                  </thead>
                  <?php 
                  $no=1;
                  $matkul=mysqli_query($koneksi,"SELECT * FROM prodi_has_matkul
                    INNER JOIN mata_kuliah ON prodi_has_matkul.kode_matkul=mata_kuliah.kode_matkul
                    LEFT JOIN tbl_jenis_mk ON mata_kuliah.id_jenis_mk=tbl_jenis_mk.id_jenis_mk
                    WHERE kode_prodi='$kode_prodi'
                    ORDER BY mata_kuliah.semester ASC");
                  while ($t_matkul=mysqli_fetch_array($matkul)) {
                    ?>
                    <tr>
                      <td style="text-align: center;"><?= $no++; ?>.</td>
                      <td style="text-align: center;"><?= $t_matkul['kode_matkul']; ?></td>
                      <td><?= $t_matkul['nama_matkul']; ?></td>
                      <td style="text-align: center;"><?= $t_matkul['sks']; ?></td>
                      <td style="text-align: center;"><?= $t_matkul['semester']; ?></td>
                      <td style="text-align: center;"><?= $t_matkul['jenis_mk']; ?></td>
                        <td style="text-align: center;">
                          <a href="jurusan_has_matkul?id=<?= $t_matkul['id']; ?>&aksi=hapus" onclick="return confirm('Hapus data ini ?')" style="display: flex; justify-content: center; align-items: center; height: 100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
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