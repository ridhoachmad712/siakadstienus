<?php
session_start();

include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login_ajax($koneksi);
$kode_prodi=$_SESSION['kode_prodi'];
if(isset($_POST["query"]))
{
	$search = mysqli_real_escape_string($koneksi, $_POST["query"]);
	$mata_kuliah = "SELECT * FROM mata_kuliah INNER JOIN tbl_jenis_mk ON mata_kuliah.id_jenis_mk=tbl_jenis_mk.id_jenis_mk
	WHERE kode_matkul LIKE '%".$search."%' OR nama_matkul LIKE '%".$search."%' OR sks LIKE '%".$search."%' ";
}else{
	$mata_kuliah = "SELECT * FROM mata_kuliah INNER JOIN tbl_jenis_mk ON mata_kuliah.id_jenis_mk=tbl_jenis_mk.id_jenis_mk ORDER BY nama_matkul ASC";
}
?>
<?php
	$result = mysqli_query($koneksi, $mata_kuliah);
	if(mysqli_num_rows($result) > 0)
	{
		?>
		<form action="" method="post">
<?php siakad_csrf_field(); ?>
			<div class="card">
				<div class="card-body">
					<a class="btn" href="jurusan_has_matkul">
						Kembali
					</a>
					<input type="submit" name="tambah" class="btn" value="Tambah">
				</div>
			</div>
			<table class="table table-vcenter card-table">
				<thead>
					<tr>
						<th style="text-align:  center;">OPSI</th>
						<th>NO</th>
						<th>KODE MATA KULIAH</th>
						<th>MATA KULIAH</th>
						<th>SKS</th>
						<th>SEMESTER</th>
						<th>JENIS MK</th>

					</tr>
				</thead>
				<?php
				$no=1;
				while($t_matkul = mysqli_fetch_array($result))
				{
					$kode_matkul=$t_matkul['kode_matkul'];
					?>

					<?php 
					$cek_data=mysqli_num_rows(mysqli_query($koneksi,"SELECT * FROM prodi_has_matkul
						INNER JOIN mata_kuliah ON prodi_has_matkul.kode_matkul=mata_kuliah.kode_matkul WHERE prodi_has_matkul.kode_matkul='$kode_matkul' AND mata_kuliah.id_jenis_mk!='2'"));
					if ($cek_data > 0) {

					}else{
						?>
						<tr>
							<td style="text-align: center;"><input type="checkbox" name="pilih[]" value="<?= $kode_matkul; ?>"></td>
							<td><?= $no++; ?>.</td>
							<td><?= $t_matkul['kode_matkul']; ?></td>
							<td style="text-transform: capitalize;"><?= $t_matkul['nama_matkul']; ?></td>
							<td>
								<?= $t_matkul['sks']; ?>
							</td>
							<td>
								<?= $t_matkul['semester']; ?>
							</td>
							<td>
								<?= $t_matkul['jenis_mk']; ?>
							</td>
						</tr>
						<?php
					}
				}
			}else{
				?>
				<tr>
					<td style="padding: 10pt;" colspan="4">
						<center><br><br><p>Data Tidak ada !!!</p></center>
					</td>
				</tr>
			<?php } ?>
		</table>
	</form>
