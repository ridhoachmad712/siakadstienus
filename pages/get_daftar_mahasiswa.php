<?php 
session_start();
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login($koneksi);
$username=$_SESSION['username'];

$level=$_SESSION['level'];
$kode_prodi=$_SESSION['kode_prodi'];
$id_jadwal=sql_aman($koneksi, $_GET['qwe'] ?? null);

$d=mysqli_fetch_array(mysqli_query($koneksi,"SELECT * FROM jadwal_mengajar WHERE id_jadwal='$id_jadwal'"));
$kode_prodi=nilai($d, 'kode_prodi');
$id_thn_akademik=nilai($d, 'id_thn_akademik');
$prodi=mysqli_fetch_array(mysqli_query($koneksi,"SELECT * FROM prodi WHERE kode_prodi='$kode_prodi'"));
$fakultas=mysqli_fetch_array(mysqli_query($koneksi,"SELECT * FROM fakultas_has_jurusan WHERE kode_prodi='$kode_prodi'"));
$kode_fakultas=nilai($fakultas, 'kode_fakultas');

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
<?php $judul_halaman = "Daftar Mahasiswa"; include "../template/head.php"; ?>
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
                Daftar Mahasiswa
              </h2>
              <p style="margin-top: 1em;">Halo Bapak/Ibu <strong><?= sk_escape($sk_name); ?></strong>
              <br>Berikut Daftar Mahasiswa pada Mata Kuliah <strong>
                <?php 
                $mk=mysqli_fetch_array(mysqli_query($koneksi,"SELECT * FROM jadwal_mengajar INNER JOIN mata_kuliah ON jadwal_mengajar.kode_mk=mata_kuliah.kode_matkul WHERE id_jadwal='$id_jadwal' AND kode_prodi='$kode_prodi'"));
                ?>
                <?= nilai($mk, 'nama_matkul'); ?></strong></p>
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
                 </div>

               </div>


               <form action="" method="post">
<?php siakad_csrf_field(); ?>

              <table class="table table-vcenter card-table">
                  <thead>
                      <tr>
                          <th style="text-align: center;">No.</th>
                          <th style="text-align: center;">Nim</th>
                          <th style="text-align: center;">Nama mahasiswa</th>
                          <th style="text-align: center;">Jenis Kelamin</th>
                          <th style="text-align: center;">No. Handphone</th>
                          <th style="text-align: center;">Angkatan</th>
                          <th style="text-align: center;">Keterangan</th>
                      </tr>
                  </thead>
                  <?php
                  $no = 1;
                  $nilai = mysqli_query($koneksi, "SELECT * FROM khs_mhs
                  INNER JOIN mahasiswa ON khs_mhs.nim_npm=mahasiswa.nim_npm
                  LEFT JOIN tbl_jk ON mahasiswa.id_jk=tbl_jk.id_jk
                  WHERE id_thn_akademik='$id_thn_akademik' AND kode_prodi='$kode_prodi'
                  AND id_jadwal='$id_jadwal'
                  ORDER BY mahasiswa.nim_npm ASC");
                  while ($t_nilai = mysqli_fetch_array($nilai)) {
                      ?>
                      <tr>
                          <td style="text-align: center;"><?= $no++; ?>.</td>
                          <td style="text-align: center;"><?= $t_nilai['nim_npm']; ?><input type="hidden" name="nim_npm[]" value="<?= $t_nilai['nim_npm']; ?>"></td>
                          <td style="text-transform: capitalize;"><?= $t_nilai['nama_mhs']; ?></td>
                          <td style="text-align: center;"><?= $t_nilai['jenis_kelamin']; ?></td>
                          <td style="text-align: center;"><?= $t_nilai['no_telp_mhs']; ?></td>
                          <td style="text-align: center;"><?= $t_nilai['thn_masuk']; ?></td>
                          <td style="text-align: center;"> - </td>
                      </tr>
                  <?php } ?>
              </table>
              </form>
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



<!-- tambah jadwal -->
<form action="" method="post">
<?php siakad_csrf_field(); ?>
  <div class="modal modal-blur fade" id="modal-scrollable" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Penjadwalan Kuliah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="id_thn_akademik" value="<?= $id_thn_akademik; ?>">

          <div class="mb-3">
            <label>Mata Kuliah</label>
            <select class="form-select" name="kode_mk" required>
              <option value="">--Pilih--</option>
              <?php 
              $mata_kuliah=mysqli_query($koneksi,"SELECT * FROM prodi_has_matkul
                INNER JOIN mata_kuliah ON prodi_has_matkul.kode_matkul=mata_kuliah.kode_matkul
                LEFT JOIN tbl_jenis_mk ON mata_kuliah.id_jenis_mk=tbl_jenis_mk.id_jenis_mk WHERE kode_prodi='$kode_prodi'");
              while ($t_matkul=mysqli_fetch_array($mata_kuliah)) {
                ?>
                <option value="<?= $t_matkul['kode_matkul']; ?>"><?= $t_matkul['kode_matkul']; ?> | <?= $t_matkul['nama_matkul']; ?> | Sks <?= $t_matkul['sks']; ?> | Semester <?= $t_matkul['semester']; ?> | <?= $t_matkul['jenis_mk']; ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="mb-3">
            <label>Dosen Pengajar</label>
            <select class="form-select" name="nip" required>
              <option value="">--Pilih--</option>
              <?php 
              $dosen=mysqli_query($koneksi,"SELECT * FROM prodi_has_dosen
                INNER JOIN dosen ON prodi_has_dosen.nip=dosen.nip WHERE kode_prodi='$kode_prodi'");
              while ($t_dosen=mysqli_fetch_array($dosen)) {
                ?>
                <option value="<?= $t_dosen['nip']; ?>"><?= $t_dosen['nama_dosen']; ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="mb-3">
            <label>Ruangan</label>
            <select class="form-select" name="kode_ruangan" required>
              <option value="">--Pilih--</option>
              <?php 
              $ruangan=mysqli_query($koneksi,"SELECT * FROM tbl_ruangan WHERE kode_fakultas='$kode_fakultas' ORDER BY kode_ruangan ASC");
              while ($t_ruangan=mysqli_fetch_array($ruangan)) {
                ?>
                <option value="<?= $t_ruangan['kode_ruangan']; ?>"><?= $t_ruangan['nama_ruangan']; ?> - Lantai <?= $t_ruangan['lantai']; ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="mb-3">
            <label>Hari</label>
            <select class="form-select" name="id_hari" required>
              <option value="">--Pilih--</option>
              <?php 
              $hari=mysqli_query($koneksi,"SELECT * FROM tbl_hari");
              while ($t_hari=mysqli_fetch_array($hari)) {
                ?>
                <option value="<?= $t_hari['id_hari']; ?>"><?= $t_hari['nama_hari']; ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="mb-3">
            <div class="row">
              <div class="col-lg-6">
                <label>Mulai jam</label>
                <input type="time" name="mulai_jam" class="form-control" required>
              </div>
              <div class="col-lg-6">
                <label>Sampai jam</label>
                <input type="time" name="sampai_jam" class="form-control" required>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn me-auto" data-bs-dismiss="modal">Tutup</button>
          <input type="submit" name="simpan" class="btn btn-info" value="Tambah">
        </div>
      </div>
    </div>
  </div>
</form>
<!--  -->


<!-- Libs JS -->
<!-- Tabler Core -->
  <?php include "../template/scripts.php"; ?>
<!-- javascript search data fakultas -->

</body>
</html>