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
<?php $judul_halaman = "Dosen Program Studi"; include "../template/head.php"; ?>
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
                Data Dosen Program Studi <?= nilai($prodi, 'nama_prodi'); ?>
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
                      <a class="btn" href="add_dosen_jurusan">
                        Tambah Dosen
                      </a>
                    </div>
                  </div>
                  <!-- tampil data -->
                  <table class="table table-vcenter card-table">
                    <thead>
                      <tr>
                        <th class="text-center">NO</th>
                        <th class="text-center">NIDN</th>
                        <th class="text-center">NAMA DOSEN</th>
                        <th class="text-center">JENIS KELAMIN</th>
                        <th class="text-center">AGAMA</TH>
                        <th class="text-center">ALAMAT</TH>
                        <th class="text-center">TEMPAT &  TANGGAL LAHIR</th>
                        <th class="text-center">Mhs Perwalian</th>
                        <th class="text-center">FOTO</TH>
                        <th class="text-center">HAPUS</th>
                      </tr>
                    </thead>
                    <?php
                    $no=1; 
                    $dosen=mysqli_query($koneksi,"SELECT * FROM prodi_has_dosen
                      INNER JOIN dosen ON prodi_has_dosen.nip=dosen.nip
                      LEFT JOIN tbl_jk ON dosen.id_jk=tbl_jk.id_jk
                      LEFT JOIN tbl_agama ON dosen.id_agama=tbl_agama.id_agama WHERE prodi_has_dosen.kode_prodi='$kode_prodi'");
                    while ($t_dosen=mysqli_fetch_array($dosen)) {
                      $foto_dosen=$t_dosen['foto_dosen'];
                      $nip=$t_dosen['nip'];
                      ?>
                      <tr>
                        <td class="text-center"><?= $no++; ?>.</td>
                        <td class="text-center"><?= $t_dosen['nip']; ?></td>
                        <td><?= $t_dosen['nama_dosen']; ?></td>
                        <td class="text-center"><?= $t_dosen['jenis_kelamin']; ?></td>
                        <td class="text-center"><?= $t_dosen['agama']; ?></td>
                        <td><?= $t_dosen['alamat']; ?></td>
                        <td><?= $t_dosen['tmp_lhr_dosen']; ?>, <?= tgl_indo($t_dosen['tgl_lhr_dosen']); ?></td>
                        <td>
                          <a href="dosen_has_mhs?nip=<?= $nip; ?>" class="btn">
                            <?php 
                            $mhs_pa=mysqli_num_rows(mysqli_query($koneksi,"SELECT * FROM mhs_has_pa WHERE nip='$nip'"));
                            echo "$mhs_pa Mahasiswa";
                            ?>
                          </a>
                        </td>
                        <td>
                          <?php if ($foto_dosen == '') { ?>
                            <img style="width: 60pt;" src="foto_dosen/avatar-blank.png" class="rounded">
                          <?php } else { ?>
                            <img style="width: 60pt;" src="foto_dosen/<?= $t_dosen['foto_dosen']; ?>" class="rounded">
                          <?php } ?>
                        </td>
                        <td class="text-center">
                        <a href="jurusan_has_dosen?id=<?= $t_dosen['id']; ?>&aksi=hapus" onclick="return confirm('Hapus data ini ?')">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="red" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
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