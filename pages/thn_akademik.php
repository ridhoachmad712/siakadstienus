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
 $ket=mysqli_real_escape_string($koneksi, $_POST['ket']);
 $thn_akademik=mysqli_real_escape_string($koneksi, $_POST['thn_akademik']);
 $cekdata=mysqli_num_rows(mysqli_query($koneksi,"SELECT * FROM thn_akademik WHERE thn_akademik='$thn_akademik' AND ket='$ket'"));
 if ($cekdata==1) {
  echo "<script>window.alert('Maaf Tahun Akademik sudah ada !!!')
  window.location='thn_akademik'</script>";
}else{
  $input=mysqli_query($koneksi,"INSERT INTO thn_akademik VALUES(NULL,'$ket','$thn_akademik')");
  $data=mysqli_query($koneksi,"SELECT * FROM thn_akademik WHERE thn_akademik='$thn_akademik' AND ket='$ket'");
  $r_data=mysqli_fetch_array($data);
  $id_thn_akademik=nilai($r_data, 'id_thn_akademik');
  $input2=mysqli_query($koneksi,"INSERT INTO jadwal_penawaran VALUES(NULL,'$id_thn_akademik','0000-00-00','0000-00-00')");
  $input2=mysqli_query($koneksi,"INSERT INTO jadwal_input_nilai VALUES(NULL,'$id_thn_akademik','0000-00-00','0000-00-00')");
  if ($input == 1) {
    echo "<script>window.alert('Tahun Akademik $thn_akademik Berhasil di tambahkan !!!')
    window.location='thn_akademik'</script>";
  }else{
    echo "<script>window.alert('Tambah data gagal !!!')
    window.location='thn_akademik'</script>";
  }
}
}
// Edit data fakultas

// Hapus data

if (isset($_POST['set_jadwal'])) {
  $id_thn_akademik=mysqli_real_escape_string($koneksi, $_POST['id_thn_akademik']);
  $dari_tgl=mysqli_real_escape_string($koneksi, $_POST['dari_tgl']);
  $sampai_tgl=mysqli_real_escape_string($koneksi, $_POST['sampai_tgl']);
  $update=mysqli_query($koneksi,"UPDATE jadwal_penawaran SET dari_tgl='$dari_tgl', sampai_tgl='$sampai_tgl' WHERE id_thn_akademik='$id_thn_akademik'");
  echo "<script>window.alert('Jadwal Penawaran Berhasil di atur !!!')
  window.location='thn_akademik'</script>";
}
if (isset($_POST['set_jadwal_nilai'])) {
  $id_thn_akademik=mysqli_real_escape_string($koneksi, $_POST['id_thn_akademik']);
  $dari_tgl=mysqli_real_escape_string($koneksi, $_POST['dari_tgl']);
  $sampai_tgl=mysqli_real_escape_string($koneksi, $_POST['sampai_tgl']);
  $update=mysqli_query($koneksi,"UPDATE jadwal_input_nilai SET dari_tgl='$dari_tgl', sampai_tgl='$sampai_tgl' WHERE id_thn_akademik='$id_thn_akademik'");
  echo "<script>window.alert('Jadwal Input Nilai Berhasil di atur !!!')
  window.location='thn_akademik'</script>";
}
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
<?php $judul_halaman = "Pengaturan Semester & Tahun Akademik"; include "../template/head.php"; ?>
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
                Pengaturan Semester & Tahun Akademik
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
                    Tambah Semester
                  </a>
                </div>
              </div>

              <div class="table-responsive">
                <!-- tampil data -->
                <table class="table table-vcenter card-table">
                  <thead>
                    <th>NO.</th>
                    <th>TAHUN AKADEMIK</th> 
                    <th>Semester</th>
                    <th>Jadwal Pengisian KRS</th>
                    <th>Jadwal Input Nilai Dosen</th>
                    <th>Hapus</th>
                  </thead>
                  <?php 
                  $no=1;
                  $thn_akademik=mysqli_query($koneksi,"SELECT * FROM thn_akademik ORDER BY thn_akademik DESC, ket DESC");
                  while ($t_akademik=mysqli_fetch_array($thn_akademik)) {
                    $id_thn_akademik=$t_akademik['id_thn_akademik'];
                    ?>
                    <tr>
                      <td><?= $no++ ?>.</td>
                      <td><?= $t_akademik['thn_akademik']; ?></td>
                      <td><?= $t_akademik['ket']; ?></td>
                      <td>
                        <?php 
                        $j=mysqli_query($koneksi,"SELECT * FROM jadwal_penawaran WHERE id_thn_akademik='$id_thn_akademik'");
                        $row_j=mysqli_fetch_array($j);
                        $dari_tgl=nilai($row_j, 'dari_tgl');
                        ?>
                        <?php 
                        if ($dari_tgl=="0000-00-00") {
                          echo "Jadwal Belum diatur";
                        }else{
                         ?>
                         <?= tgl_indo(nilai($row_j, 'dari_tgl')); ?> Sampai <?= tgl_indo(nilai($row_j, 'sampai_tgl')); ?>
                       <?php } ?>
                       <a href="" data-bs-toggle="modal" data-bs-target="#Penawaran<?= $t_akademik['id_thn_akademik']; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><circle cx="12" cy="12" r="3" /></svg>
                      </a>

                      <div class="modal modal-blur fade" id="Penawaran<?= $t_akademik['id_thn_akademik']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                          <div class="modal-content">
                            <form action="" method="post">
<?php siakad_csrf_field(); ?>
                              <div class="modal-header">
                                <h5 class="modal-title">Atur Jadwal Penawaran Tahun Akademik <?= $t_akademik['thn_akademik']; ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                               <div class="form-group">
                                <label>Mulai Tanggal</label>
                                <input type="hidden" name="id_thn_akademik" value="<?= $id_thn_akademik; ?>">
                                <input type="date" name="dari_tgl" class="form-control" value="<?= nilai($row_j, 'dari_tgl'); ?>" required="required">
                              </div><br>
                              <div class="form-group">
                                <label>Sampai Tanggal</label>
                                <input type="date" name="sampai_tgl" value="<?= nilai($row_j, 'sampai_tgl'); ?>" class="form-control" required="required">
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                              <button type="submit" name="set_jadwal" class="btn btn-info">
                                Simpan
                              </button>
                            </div>
                          </div>
                        </div>
                      </form>
                    </div>

                  </td>
                  <!-- input nilai -->
                  <td>
                    <?php 
                    $i=mysqli_query($koneksi,"SELECT * FROM jadwal_input_nilai WHERE id_thn_akademik='$id_thn_akademik'");
                    $row_i=mysqli_fetch_array($i);
                    $mulai_tgl=nilai($row_i, 'dari_tgl');
                    ?>
                    <?php 
                    if ($mulai_tgl=="0000-00-00") {
                      echo "Jadwal Belum diatur";
                    }else{
                     ?>
                     <?= tgl_indo(nilai($row_i, 'dari_tgl')); ?> Sampai <?= tgl_indo(nilai($row_i, 'sampai_tgl')); ?>
                   <?php } ?>
                   <a href="" data-bs-toggle="modal" data-bs-target="#modal-simple2<?= $t_akademik['id_thn_akademik']; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><circle cx="12" cy="12" r="3" /></svg>
                  </a>

                  <div class="modal modal-blur fade" id="modal-simple2<?= $t_akademik['id_thn_akademik']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                      <div class="modal-content">
                        <form action="" method="post">
<?php siakad_csrf_field(); ?>
                          <div class="modal-header">
                            <h5 class="modal-title">Atur Jadwal Input Nilai Tahun Akademik <?= $t_akademik['thn_akademik']; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                           <div class="form-group">
                            <label>Mulai Tanggal</label>
                            <input type="hidden" name="id_thn_akademik" value="<?= $id_thn_akademik; ?>">
                            <input type="date" name="dari_tgl" class="form-control" value="<?= nilai($row_i, 'dari_tgl'); ?>" required="required">
                          </div><br>
                          <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="sampai_tgl" value="<?= nilai($row_i, 'sampai_tgl'); ?>" class="form-control" required="required">
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                          <button type="submit" name="set_jadwal_nilai" class="btn btn-info">
                            Simpan
                          </button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>

              </td>

              <!-- batas -->
              <td>
                <a onclick="return confirm('Hapus data ini ?')" href="thn_akademik?aksi=hapus&id=<?= $t_akademik['id_thn_akademik']; ?>">
                  <!-- Download SVG icon from http://tabler-icons.io/i/trash -->
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


<div class="page-body">
  <div class="container-xl">



    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasStart" aria-labelledby="offcanvasStartLabel">
      <form action="" method="post">
<?php siakad_csrf_field(); ?>
        <div class="offcanvas-header">
          <h2 class="offcanvas-title" id="offcanvasStartLabel">Tambah Tahun Akademik</h2>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
         <div>
          <div class="mb-3">
            <label>Tahun Akademik</label> 
            <input type="text" name="thn_akademik" placeholder="Tahun Akademik" class="form-control" required="require">
          </div>
          <div class="mb-3">
            <label>Semester</label> 
            <select class="form-select" name="ket" required="required">
              <option value="">--Pilih--</option>
              <option value="Ganjil">Ganjil</option>
              <option value="Genap">Genap</option>
            </select>
          </div>
        </div>
        <div class="mt-3">
          <button class="btn" type="submit" name="tambah">
            Simpan
          </button>
          <button class="btn" type="button" data-bs-dismiss="offcanvas">
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

</body>
</html>