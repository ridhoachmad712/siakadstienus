<?php
session_start();
include "../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login($koneksi);
$username = $_SESSION['username'];

$level = $_SESSION['level'];
$kode_prodi = $_SESSION['kode_prodi'];
//
$prodi = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM prodi WHERE kode_prodi='$kode_prodi'"));
// pemeriksaan login dilakukan oleh config/auth.php di atas
// --------------------------------------------------
// pengaturan aplikasi
$pengaturan = mysqli_query($koneksi, "SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan = mysqli_fetch_array($pengaturan);


require_once "../config/login_service.php";
require_once "../config/academic.php";
if (isset($_POST['ubahpass'])) {
    siakad_form_proses(function() use ($koneksi) {
        siakad_ganti_password($koneksi, $_POST['password'] ?? null, $_POST['password2'] ?? null);
    }, 'dashboard');
}
// MAHASISWA
if ($level == 'mhs') {
  $mhs = mysqli_query($koneksi, "SELECT * FROM mahasiswa
    LEFT JOIN tbl_jk ON mahasiswa.id_jk=tbl_jk.id_jk
    LEFT JOIN tbl_agama ON mahasiswa.id_agama=tbl_agama.id_agama WHERE nim_npm='$username'");
  $tampil_mhs = mysqli_fetch_array($mhs);
  $foto_mhs = nilai($tampil_mhs, 'foto_mhs');
}
//  org tua
$orgtua = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM tbl_org_tua WHERE nim_npm='$username'"));
//

// MAHASISWA
if ($level == 'dosen') {
  $dosen = mysqli_query($koneksi, "SELECT * FROM dosen
    LEFT JOIN tbl_jk ON dosen.id_jk=tbl_jk.id_jk
    LEFT JOIN tbl_agama ON dosen.id_agama=tbl_agama.id_agama WHERE nip='$username'");
  $tampil_dosen = mysqli_fetch_array($dosen);
  $foto_dosen = nilai($tampil_dosen, 'foto_dosen');
}

if (isset($_POST['simpan_mhs'])) {
  // update mhs
  $nama_mhs = sql_aman($koneksi, $_POST['nama_mhs'] ?? null);
  $thn_masuk = sql_aman($koneksi, $_POST['thn_masuk'] ?? null);
  $id_jk = sql_aman($koneksi, $_POST['id_jk'] ?? null);
  $tempat_lhr = sql_aman($koneksi, $_POST['tempat_lhr'] ?? null);
  $tgl_lhr_mhs = sql_aman($koneksi, $_POST['tgl_lhr_mhs'] ?? null);
  $id_agama = sql_aman($koneksi, $_POST['id_agama'] ?? null);
  $email = sql_aman($koneksi, $_POST['email'] ?? null);
  $lulusan_jalur = sql_aman($koneksi, $_POST['lulusan_jalur'] ?? null);
  $sekolah_asal = sql_aman($koneksi, $_POST['sekolah_asal'] ?? null);
  $alamat_mhs = sql_aman($koneksi, $_POST['alamat_mhs'] ?? null);
  $no_telp_mhs = sql_aman($koneksi, $_POST['no_telp_mhs'] ?? null);

  // DATA ORG TUA

  $no_kk = sql_aman($koneksi, $_POST['no_kk'] ?? null);
  $nama_ayah = sql_aman($koneksi, $_POST['nama_ayah'] ?? null);
  $tmp_lhr_ayah = sql_aman($koneksi, $_POST['tmp_lhr_ayah'] ?? null);
  $tgl_lhr_ayah = sql_aman($koneksi, $_POST['tgl_lhr_ayah'] ?? null);
  $pekerjaan_ayah = sql_aman($koneksi, $_POST['pekerjaan_ayah'] ?? null);
  $penghasilan_ayah = sql_aman($koneksi, $_POST['penghasilan_ayah'] ?? null);
  $pend_ayah = sql_aman($koneksi, $_POST['pend_ayah'] ?? null);

  $nama_ibu = sql_aman($koneksi, $_POST['nama_ibu'] ?? null);
  $tmp_lhr_ibu = sql_aman($koneksi, $_POST['tmp_lhr_ibu'] ?? null);
  $tgl_lhr_ibu = sql_aman($koneksi, $_POST['tgl_lhr_ibu'] ?? null);
  $pekerjaan_ibu = sql_aman($koneksi, $_POST['pekerjaan_ibu'] ?? null);
  $penghasilan_ibu = sql_aman($koneksi, $_POST['penghasilan_ibu'] ?? null);
  $pend_ibu = sql_aman($koneksi, $_POST['pend_ibu'] ?? null);
  $alamat_org_tua = sql_aman($koneksi, $_POST['alamat_org_tua'] ?? null);
  $no_telp_orgtua = sql_aman($koneksi, $_POST['no_telp_orgtua'] ?? null);

  $update = mysqli_query($koneksi, "UPDATE mahasiswa SET nama_mhs='$nama_mhs', thn_masuk='$thn_masuk', id_jk='$id_jk', tempat_lhr='$tempat_lhr', tgl_lhr_mhs='$tgl_lhr_mhs', id_agama='$id_agama', email='$email', lulusan_jalur='$lulusan_jalur', sekolah_asal='$sekolah_asal', alamat_mhs='$alamat_mhs', no_telp_mhs='$no_telp_mhs' WHERE nim_npm='$username'");

  $cek_data = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM tbl_org_tua WHERE nim_npm='$username'"));

  if ($cek_data == 0) {
    $input = mysqli_query($koneksi, "INSERT INTO tbl_org_tua VALUES('$username','$no_kk','$nama_ayah','$tmp_lhr_ayah','$tgl_lhr_ayah','$pekerjaan_ayah','$penghasilan_ayah','$pend_ayah','$nama_ibu','$tmp_lhr_ibu','$tgl_lhr_ibu','$pekerjaan_ibu','$penghasilan_ibu','$pend_ibu','$alamat_org_tua','$no_telp_orgtua')");
  } else {
    $update = mysqli_query($koneksi, "UPDATE tbl_org_tua SET no_kk='$no_kk', nama_ayah='$nama_ayah', tmp_lhr_ayah='$tmp_lhr_ayah', tgl_lhr_ayah='$tgl_lhr_ayah', pekerjaan_ayah='$pekerjaan_ayah', penghasilan_ayah='$penghasilan_ayah', pend_ayah='$pend_ayah', nama_ibu='$nama_ibu', tmp_lhr_ibu='$tmp_lhr_ibu', tgl_lhr_ibu='$tgl_lhr_ibu', pekerjaan_ibu='$pekerjaan_ibu', penghasilan_ibu='$penghasilan_ibu', pend_ibu='$pend_ibu', alamat_org_tua='$alamat_org_tua', no_telp_orgtua='$no_telp_orgtua' WHERE nim_npm='$username'");
  }
  echo "<script>window.alert('Data anda berhasil di simpan')
window.location='dashboard?view=profil'</script>";
}

if (isset($_POST['simpan_dosen'])) {
  $nama_dosen = sql_aman($koneksi, $_POST['nama_dosen'] ?? null);
  $id_jk = sql_aman($koneksi, $_POST['id_jk'] ?? null);
  $id_agama = sql_aman($koneksi, $_POST['id_agama'] ?? null);
  $alamat = sql_aman($koneksi, $_POST['alamat'] ?? null);
  $tmp_lhr_dosen = sql_aman($koneksi, $_POST['tmp_lhr_dosen'] ?? null);
  $tgl_lhr_dosen = sql_aman($koneksi, $_POST['tgl_lhr_dosen'] ?? null);
  $email = sql_aman($koneksi, $_POST['email'] ?? null);
  $no_telp = sql_aman($koneksi, $_POST['no_telp'] ?? null);
  $update = mysqli_query($koneksi, "UPDATE dosen SET nama_dosen='$nama_dosen', id_jk='$id_jk', id_agama='$id_agama', alamat='$alamat', tmp_lhr_dosen='$tmp_lhr_dosen', tgl_lhr_dosen='$tgl_lhr_dosen', email='$email', no_telp='$no_telp' WHERE nip='$username'");
  echo "<script>window.alert('Data anda berhasil di simpan')
  window.location='dashboard?view=profil'</script>";
}

if (isset($_POST['ubahfotomhs'])) {
  $rand = $username;
  $ekstensi_diperbolehkan = array('png', 'jpg', 'JPG', 'PNG', 'jpeg', 'JPEG');
  $file_foto = $_FILES['file_foto']['name'];
  $x = explode('.', $file_foto);
  $ekstensi = strtolower(end($x));
  $ukuran = $_FILES['file_foto']['size'];
  $file_tmp = $_FILES['file_foto']['tmp_name'];
  if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
    if ($ukuran < 50044070) {
      if ($foto_mhs == "") {
        # code...
      } else {
        unlink("foto_mhs/$foto_mhs");
      }
      $xx_foto = $rand . '_' . $file_foto;
      move_uploaded_file($file_tmp, 'foto_mhs/' . $rand . '_' . $file_foto);
      $update_foto = mysqli_query($koneksi, "UPDATE mahasiswa SET foto_mhs='$xx_foto' WHERE nim_npm='$username'");
      echo "<script>window.alert('Foto Profile anda berhasil di ubah')
      window.location='dashboard?view=profil'</script>";
    }
  }
}

if (isset($_POST['ubahfotodosen'])) {
  $rand = $username;
  $ekstensi_diperbolehkan = array('png', 'jpg', 'JPG', 'PNG', 'jpeg', 'JPEG');
  $file_foto = $_FILES['file_foto']['name'];
  $x = explode('.', $file_foto);
  $ekstensi = strtolower(end($x));
  $ukuran = $_FILES['file_foto']['size'];
  $file_tmp = $_FILES['file_foto']['tmp_name'];
  if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
    if ($ukuran < 50044070) {
      if ($foto_dosen == "") {
        # code...
      } else {
        unlink("foto_dosen/$foto_dosen");
      }
      $xx_foto = $rand . '_' . $file_foto;
      move_uploaded_file($file_tmp, 'foto_dosen/' . $rand . '_' . $file_foto);
      $update_foto = mysqli_query($koneksi, "UPDATE dosen SET foto_dosen='$xx_foto' WHERE nip='$username'");
      echo "<script>window.alert('Foto Profile anda berhasil di ubah')
      window.location='dashboard?view=profil'</script>";
    }
  }
}


?>
<?php $sk_profile_view = in_array($level, ['mhs', 'dosen'], true) && ($_GET['view'] ?? '') === 'profil'; $judul_halaman = $sk_profile_view ? "Profil saya" : "Beranda"; include "../template/head.php"; ?>
  <div class="wrapper">
    <?php
    require_once "../template/header.php";
    ?>
    <div class="navbar-expand-md">
      <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar navbar-light">
          <div class="container-xl">
            <?php
            require_once "../template/menu.php";
            ?>

          </div>
        </div>
      </div>
    </div>
    <div class="page-wrapper">
      <br>
      <div class="page-body">
        <div class="container-xl">
          <?php if (!$sk_profile_view) { require "../template/dashboard-home.php"; } else { ?>
          <div class="sk-profile-heading"><a href="dashboard">← Beranda</a><h2>Profil saya</h2><p class="text-muted">Kelola biodata, foto, dan password akun Anda.</p></div>
          <?php require "../template/profile.php"; ?>
        <?php } ?>
      </div>
    </div>
    <?php
    require_once "../template/footer.php";
    ?>
  </div>
  </div>
  <?php include "../template/scripts.php"; ?>
</body>
</html>
