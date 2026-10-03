<?php 
session_start();
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login($koneksi);
$nim_npm=sql_aman($koneksi, $_GET['nim_npm'] ?? null);
// data mhs
$mhs=mysqli_query($koneksi,"SELECT * FROM mahasiswa
  LEFT JOIN tbl_jk ON mahasiswa.id_jk=tbl_jk.id_jk
  LEFT JOIN tbl_agama ON mahasiswa.id_agama=tbl_agama.id_agama WHERE nim_npm='$nim_npm'");
$row_mhs=mysqli_fetch_array($mhs);
$foto_mhs=nilai($row_mhs, 'foto_mhs');
// 
// data orgtua
$orgtua=mysqli_fetch_array(mysqli_query($koneksi,"SELECT * FROM tbl_org_tua WHERE nim_npm='$nim_npm'"));
// 
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
?>
<?php $judul_halaman = "Detail data mahasiswa"; include "../template/head.php"; ?>
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
                Detail data mahasiswa
              </h2>
            </div>
          </div>
        </div>
      </div>


      <div class="page-body">
        <div class="container-xl">
          <div class="card">
            <div class="card-body">
              <a href="mhs" class="btn btn-secondary">Kembali</a>
            </div>
          </div>
          <div class="row row-cards">
            <div class="col-lg-12">
              <div class="card">
                <ul class="nav nav-tabs" data-bs-toggle="tabs">
                  <li class="nav-item">
                    <a href="#tabs-home-7" class="nav-link active" data-bs-toggle="tab">Data Diri</a>
                  </li>
                  <li class="nav-item">
                    <a href="#tabs-profile-7" class="nav-link" data-bs-toggle="tab">Data Orang Tua</a>
                  </li>
                 <!--  <li class="nav-item ms-auto">
                    <a href="#tabs-settings-7" class="nav-link" title="Settings" data-bs-toggle="tab">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><circle cx="12" cy="12" r="3" /></svg>
                    </a>
                  </li> -->
                </ul>
                <div class="card-body">
                  <div class="tab-content">
                    <div class="tab-pane active show" id="tabs-home-7">
                      <div>
                        <table class="table table-vcenter card-table">
                          <tr>
                            <td>Nim / Npm</td>
                            <td>: <?= nilai($row_mhs, 'nim_npm'); ?></td>
                            <td rowspan="4">
                              <?php 
                              if ($foto_mhs=='') {
                                ?>
                                <img style="width: 85pt;" src="foto_mhs/avatar-blank.png"></td>
                              <?php }else{ ?>
                                <img style="width: 85pt;" src="foto_mhs/<?= nilai($row_mhs, 'foto_mhs'); ?>"></td>
                              <?php } ?>
                            </td>
                          </tr>
                          <tr>
                            <td>Nama </td>
                            <td>: <?= nilai($row_mhs, 'nama_mhs'); ?></td>
                          </tr>
                          <tr>
                            <td>Tahun Masuk </td>
                            <td>: <?= nilai($row_mhs, 'thn_masuk'); ?></td>
                          </tr>
                          <tr>
                            <td>Tempat Tanggal lahir</td>
                            <td>: <?= nilai($row_mhs, 'tempat_lhr'); ?>, <?= tgl_indo(nilai($row_mhs, 'tgl_lhr_mhs')); ?></td>
                          </tr>
                          <tr>
                            <td>Jenis Kelamin</td>
                            <td>: <?= nilai($row_mhs, 'jenis_kelamin'); ?></td>
                          </tr>
                          <tr>
                            <td>Agama</td>
                            <td>: <?= nilai($row_mhs, 'agama'); ?></td>
                          </tr>
                          <tr>
                            <td>Email</td>
                            <td>: <?= nilai($row_mhs, 'email'); ?></td>
                          </tr>
                          <tr>
                            <td>Alamat mahasiswa</td>
                            <td>: <?= nilai($row_mhs, 'alamat_mhs'); ?></td>
                          </tr>
                          <tr>
                            <td>No Telp mahasiswa</td>
                            <td>: <?= nilai($row_mhs, 'no_telp_mhs'); ?></td>
                          </tr>
                          <tr>
                            <td>Status Mahasiswa</td>
                            <td>: <?= nilai($row_mhs, 'status_mhs'); ?></td>
                          </tr>
                        </table>
                      </div>
                    </div>
                    <div class="tab-pane" id="tabs-profile-7">
                      <div>
                       <table class="table table-vcenter card-table">
                        <tr>
                          <td>No KK</td>
                          <td>: <?= nilai($orgtua, 'no_kk'); ?></td>
                        </tr>
                        <tr>
                          <td>Nama Ayah </td>
                          <td>: <?= nilai($orgtua, 'nama_ayah'); ?></td>
                        </tr>
                        <tr>
                          <td>Tempat Tanggal lahir ayah </td>
                          <td>: <?= nilai($orgtua, 'tmp_lhr_ayah'); ?>, <?= nilai($orgtua, 'tgl_lhr_ayah'); ?></td>
                        </tr>
                        <tr>
                          <td>Pekerjaan Ayah</td>
                          <td>: <?= nilai($orgtua, 'pekerjaan_ayah'); ?></td>
                        </tr>
                        <tr>
                          <td>Penghasilan ayah</td>
                          <td>: <?= nilai($orgtua, 'penghasilan_ayah'); ?></td>
                        </tr>
                        <tr>
                          <td>Pendidikan Ayah</td>
                          <td>: <?= nilai($orgtua, 'pend_ayah'); ?></td>
                        </tr>
                        <tr>
                          <td>Nama Ibu</td>
                          <td>: <?= nilai($orgtua, 'nama_ibu'); ?></td>
                        </tr>
                        <tr>
                          <td>Tenpat tanggal lahir ibu</td>
                          <td>: <?= nilai($orgtua, 'tmp_lhr_ibu'); ?>, <?= nilai($orgtua, 'tgl_lhr_ibu'); ?></td>
                        </tr>
                        <tr>
                          <td>Pekerjaan Ibu</td>
                          <td>: <?= nilai($orgtua, 'pekerjaan_ibu'); ?></td>
                        </tr>
                        <tr>
                          <td>Pendidikan Ibu</td>
                          <td>: <?= nilai($orgtua, 'pend_ibu'); ?></td>
                        </tr>
                        <tr>
                          <td>Alamat Org tua</td>
                          <td>: <?= nilai($orgtua, 'alamat_org_tua'); ?></td>
                        </tr>
                        <tr>
                          <td>No Telp Orgtua</td>
                          <td>: <?= nilai($orgtua, 'no_telp_orgtua'); ?></td>
                        </tr>
                      </table>
                    </div>
                  </div>
                  <div class="tab-pane" id="tabs-settings-7">
                    <div>Donec ac vitae diam amet vel leo egestas consequat rhoncus in luctus amet, facilisi sit mauris accumsan nibh habitant senectus</div>
                  </div>
                </div>
              </div>
            </div>
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