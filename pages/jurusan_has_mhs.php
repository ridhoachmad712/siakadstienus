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
<?php $judul_halaman = "Mahasiswa Program Studi"; include "../template/head.php"; ?>
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
                Data Mahasiswa Program Studi <?= nilai($prodi, 'nama_prodi'); ?>
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
                      <a class="btn btn-green" href="add_mhs_jurusan">
                        Tambah Mahasiswa
                      </a>
                    </div>
                  </div>
                  <!-- tampil data -->
                  <table class="table table-vcenter card-table">
                    <thead>
                      <tr>
                        <th style="text-align: center;">NO.</th>
                        <th style="text-align: center;">NIM</th>
                        <th style="text-align: center;">NAMA MAHASISWA</th>
                        <th style="text-align: center;">ANGKATAN</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">JENIS KELAMIN</th>
                        <th style="text-align: center;">AGAMA</TH>
                        <th style="text-align: center;">ALAMAT</TH>
                        <th style="text-align: center;">TEMPAT & TANGGAL LAHIR</th>
                        <th style="text-align: center;">NO. HP</th>
                        <TH style="text-align: center;">FOTO</TH>
                        <th style="text-align: center;">HAPUS</th>
                      </tr>
                    </thead>
                        <?php
                        $no=1; 
                        require_once '../config/table_pagination.php';
[$sk_list_sql,$sk_offset]=siakad_tabel_statis($koneksi,"SELECT * FROM prodi_has_mhs
                          INNER JOIN mahasiswa ON prodi_has_mhs.nim_npm=mahasiswa.nim_npm
                          LEFT JOIN tbl_jk ON mahasiswa.id_jk=tbl_jk.id_jk
                          LEFT JOIN tbl_agama ON mahasiswa.id_agama=tbl_agama.id_agama
                          WHERE prodi_has_mhs.kode_prodi='$kode_prodi'
                          ORDER BY mahasiswa.nim_npm DESC",["mahasiswa.nim_npm", "mahasiswa.nama_mhs"]);
$no=$sk_offset+1;
$mhs=mysqli_query($koneksi,$sk_list_sql); // Menambahkan ORDER BY untuk mengurutkan berdasarkan NIM
                        while ($t_mhs=mysqli_fetch_array($mhs)) {
                          $foto_mhs=$t_mhs['foto_mhs'];
                          ?>
                      <tr>
                        <td style="text-align: center;"><?= $no++; ?>.</td>
                        <td style="text-align: center;"><?= $t_mhs['nim_npm']; ?></td>
                        <td><?= $t_mhs['nama_mhs']; ?></td>
                        <td style="text-align: center;"><?= $t_mhs['thn_masuk']; ?></td>
                        <td style="text-align: center;"><?= $t_mhs['status_mhs']; ?></td>
                        <td style="text-align: center;"><?= $t_mhs['jenis_kelamin']; ?></td>
                        <td style="text-align: center;"><?= $t_mhs['agama']; ?></td>
                        <td><?= $t_mhs['alamat_mhs']; ?></td>
                        <td><?= $t_mhs['tempat_lhr']; ?>, <?= tgl_indo($t_mhs['tgl_lhr_mhs']); ?></td>
                        <td><?= $t_mhs['no_telp_mhs']; ?></td>
                        <td>
                          <?php 
                          if ($foto_mhs=='') {
                            ?>
                            <img style="width: 70pt;" src="foto_mhs/avatar-blank.png">
                          <?php }else{ ?>
                            <img style="width: 70pt;" src="foto_mhs/<?= $t_mhs['foto_mhs']; ?>">
                          <?php } ?>
                        </td>
                        <td style="text-align: center;">
                          <a href="jurusan_has_mhs?id=<?= $t_mhs['id']; ?>&aksi=hapus" onclick="return confirm('Hapus data ini ?')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" style="color: red;"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
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
