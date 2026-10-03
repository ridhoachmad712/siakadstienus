<?php 
session_start();
include"../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');
// pengaturan aplikasi 
$pengaturan=mysqli_query($koneksi,"SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan=mysqli_fetch_array($pengaturan);
require_once "../config/login_service.php";
if (isset($_POST['masuk'])) {
    if (!siakad_csrf_valid($_POST['csrf_token'] ?? null)) siakad_tolak('Token login tidak valid. Muat ulang halaman.');
    $ok = siakad_login($koneksi, $_POST['username'] ?? '', $_POST['password'] ?? '', $_POST['level'] ?? '');
    siakad_alihkan($ok ? 'dashboard' : 'login?login=gagal');
}
// codingan masuk

?>
<?php
// pesan hasil login (menggantikan popup sweetalert versi lama)
$pesan_error = isset($_GET['login']) ? 'Username, password, atau hak akses tidak sesuai.' : '';
$pesan_sukses = isset($_GET['status']) ? 'Login berhasil. Mengalihkan ke beranda...' : '';
$sk_base = '../';
$judul_halaman = 'Login';
$sk_body_class = 'page-login';
include "../template/head.php";
?>
<div class="sk-login">
  <div class="sk-login-card">
    <div class="sk-login-brand">
      <?php if (!empty(nilai($r_pengaturan, 'logo_aplikasi'))) { ?>
        <img src="../img/<?= htmlspecialchars(nilai($r_pengaturan, 'logo_aplikasi'), ENT_QUOTES, 'UTF-8'); ?>" alt="Logo">
      <?php } ?>
      <span class="sk-login-label">SIAKAD</span>
      <h1>Masuk ke akun Anda</h1>
      <p><?= htmlspecialchars(nilai($r_pengaturan, 'nama_kampus'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <div class="sk-login-body">

      <?php if ($pesan_error !== '') { ?>
        <div class="alert alert-danger" role="alert"><?= $pesan_error; ?></div>
      <?php } ?>
      <?php if ($pesan_sukses !== '') { ?>
        <div class="alert alert-success" role="alert"><?= $pesan_sukses; ?></div>
        <script>setTimeout(function(){ window.location.replace('dashboard'); }, 1200);</script>
      <?php } ?>

      <form method="post" action="" autocomplete="on">
<?php siakad_csrf_field(); ?>
        <div class="mb-3">
          <label class="form-label" for="username">Username</label>
          <input type="text" id="username" name="username" class="form-control" placeholder="NIM / NIP / username" autocomplete="username" required>
        </div>

        <div class="mb-3">
          <label class="form-label" for="myInput">Password</label>
          <div class="sk-password">
            <input type="password" id="myInput" name="password" class="form-control" placeholder="Masukkan password" autocomplete="current-password" required>
            <button type="button" class="sk-password-toggle" onclick="myFunction(this)" aria-label="Tampilkan password" aria-controls="myInput" aria-pressed="false">Lihat</button>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="level">Masuk sebagai</label>
          <select class="form-select" id="level" name="level" required="required">
            <option value="">Pilih peran Anda</option>
            <option value="admin">Admin Akademik</option>
            <option value="Jurusan/Prodi">Program Studi</option>
            <option value="dosen">Dosen</option>
            <option value="mhs">Mahasiswa</option>
          </select>
        </div>

        <button type="submit" name="masuk" value="Masuk" class="btn btn-primary w-100">Masuk</button>
      </form>
    </div>

    <div class="sk-login-footer">
      &copy; <?= date('Y'); ?> <?= htmlspecialchars(nilai($r_pengaturan, 'copyright'), ENT_QUOTES, 'UTF-8'); ?>
    </div>
  </div>
</div>

<script>
  function myFunction(button) {
    var x = document.getElementById("myInput");
    var show = x.type === "password";
    x.type = show ? "text" : "password";
    button.textContent = show ? "Sembunyikan" : "Lihat";
    button.setAttribute("aria-label", show ? "Sembunyikan password" : "Tampilkan password");
    button.setAttribute("aria-pressed", show ? "true" : "false");
  }
</script>
</body>

</html>
