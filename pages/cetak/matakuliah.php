<?php 
session_start();
include"../../config/koneksi.php";
require_once "../../config/auth.php";
siakad_wajib_login($koneksi, null, "../login");
$pengaturan=mysqli_query($koneksi,"SELECT * FROM pengaturan WHERE id_pengaturan='1'");
$r_pengaturan=mysqli_fetch_array($pengaturan);
?>
<!DOCTYPE html>
<html>
<head>
	<?php 
	error_reporting(0);
	include "printer.css"; ?>
	<title>Mata Kuliah</title>
	<style type="text/css">
		.style4 {font-size: 12; }
		.style7 { font-size: 12;
			color: #265180;
			font-family: Georgia, "Times New Roman", Times, serif;
		}
	</style>
<?php require __DIR__.'/../../template/print-style.php'; ?>
</head>
<body>
	<table class="basic"  border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td width="65" rowspan="6"><img style="width: 70px;" src="../../img/<?= nilai($r_pengaturan, 'logo_aplikasi'); ?>"></td>
			<td width="550" align="center">&nbsp;</td>
		</tr>
		<tr>
			<td align="center" class=fs><strong style="text-transform: uppercase;"><?= nilai($r_pengaturan, 'nama_kampus'); ?></strong></td>
		</tr>
		<tr>
			<td align="center"><p><?= nilai($r_pengaturan, 'alamat'); ?> <br />Email: <?= nilai($r_pengaturan, 'email'); ?>, Tlp./Wa: <?= nilai($r_pengaturan, 'no_telp'); ?></p></td>
		</tr>
	</table>
	<hr>
	<table class="basic" width="750" border="0" align="center" cellpadding="0" cellspacing="0">
		<tr>
			<td align="center">
				<h2>Data Mata Kuliah</h2></td>
			</tr>
		</table>

		<table class="table_2">
			<tr>
				<td>
					<b>No.</b>
				</td>
				<td>
					<b>Kode Mata Kuliah</b>
				</td>
				<td>
					<b>Nama Mata Kuliah</b>
				</td>
				<td style="text-align: center;">
					<b>Sks</b>
				</td>
			</tr>
			<?php 
			$no=1;
			$matkul=mysqli_query($koneksi,"SELECT * FROM mata_kuliah ORDER BY nama_matkul ASC");
			while ($r_matkul=mysqli_fetch_array($matkul)) {
				?>
				<tr>
					<td><?= $no++; ?>.</td>
					<td><?= $r_matkul['kode_matkul']; ?></td>
					<td><?= $r_matkul['nama_matkul']; ?></td>
					<td style="text-align: center;"><?= $r_matkul['sks']; ?></td>
				</tr>
			<?php } ?>
		</table>

		<script type="text/javascript">
			window.print();
		</script>
	</body>
	</html>