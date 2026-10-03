<?php 
session_start();
include"config/koneksi.php";
if ( isset($_SESSION["username"])) {
	header("location: pages/dashboard");
	exit;
}
// pengaturan aplikasi 
$pengaturan=mysqli_query($koneksi,"SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan=mysqli_fetch_array($pengaturan);
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="refresh" content="0;url=pages/login">
	<title><?= nilai($r_pengaturan, 'nama_aplikasi'); ?></title>
	<link rel="shortcut icon" href="img/<?= nilai($r_pengaturan, 'logo_aplikasi'); ?>" />
	<script language="javascript">
		window.location.href = "pages/login"
	</script>
</head>
<body>
	<a href="pages/login">Go to Demo</a>
</body>
</html>
