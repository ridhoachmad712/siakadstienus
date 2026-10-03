<?php
session_start();

include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login_ajax($koneksi);
$kode_prodi=$_SESSION['kode_prodi'];
if(isset($_POST["query"]))
{
	$search = mysqli_real_escape_string($koneksi, $_POST["query"]);
	$data_mahasiswa = "SELECT * FROM mahasiswa LEFT JOIN tbl_jk ON mahasiswa.id_jk=tbl_jk.id_jk
	LEFT JOIN tbl_agama ON mahasiswa.id_agama=tbl_agama.id_agama
	WHERE nim_npm LIKE '%".$search."%' OR nama_mhs LIKE '%".$search."%' OR thn_masuk LIKE '%".$search."%' OR status_mhs LIKE '%".$search."%'  ";
}else{
	$data_mahasiswa = "SELECT * FROM mahasiswa LEFT JOIN tbl_jk ON mahasiswa.id_jk=tbl_jk.id_jk
	LEFT JOIN tbl_agama ON mahasiswa.id_agama=tbl_agama.id_agama ORDER BY nama_mhs ASC";
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
<?php
	$result = mysqli_query($koneksi, $data_mahasiswa);
	if(mysqli_num_rows($result) > 0)
	{
		?>
		<form action="" method="post">
<?php siakad_csrf_field(); ?>
			<div class="card">
				<div class="card-body">
					<a href="jurusan_has_mhs" class="btn">Kembali</a>
					<input type="submit" name="tambah" value="Tambah" class="btn">
				</div>
			</div>
			<table class="table table-vcenter card-table">
				<thead>
					<tr>
						<th style="text-align: center;">OPSI</th>
						<th>NO</th>
						<th>NIM/NPM</th>
						<th>NAMA MAHASISWA</th>
						<th>Tahun Masuk</th>
						<th>Status Mhs</th>
						<th>JK</th>
						<TH>AGAMA</TH>
						<TH>ALAMAT</TH>
						<th>TTL</th>
						<th>Email</th>
						<TH>FOTO</TH>
					</tr>
				</thead>
					<?php
					$no=1;
					while($t_mhs = mysqli_fetch_array($result))
					{
						$nim_npm=$t_mhs['nim_npm'];
						$foto_mhs=$t_mhs['foto_mhs'];
						?>

						<?php 
						$cek_data=mysqli_num_rows(mysqli_query($koneksi,"SELECT * FROM prodi_has_mhs WHERE nim_npm='$nim_npm'"));
						if ($cek_data > 0) {

						}else{
							?>
							<tr>
								<td style="text-align: center;"><input type="checkbox" name="pilih[]" value="<?= $nim_npm; ?>"></td>
								<td><?= $no++; ?>.</td>
								<td><?= $t_mhs['nim_npm']; ?></td>
								<td style="text-transform: capitalize;"><?= $t_mhs['nama_mhs']; ?></td>
								<td>
									<?= $t_mhs['thn_masuk']; ?>
								</td>
								<td><?= $t_mhs['status_mhs']; ?></td>
								<td><?= $t_mhs['jenis_kelamin']; ?></td>
								<td><?= $t_mhs['agama']; ?></td>
								<td><?= $t_mhs['alamat_mhs']; ?></td>
								<td><?= $t_mhs['tempat_lhr']; ?>, <?= tgl_indo($t_mhs['tgl_lhr_mhs']); ?></td>
								<td><?= $t_mhs['email']; ?></td>
								<td>
									<?php 
									if ($foto_mhs=='') {
										?>
										<img style="width: 70pt;" src="foto_mhs/avatar-blank.png"></td>
									<?php }else{ ?>
										<img style="width: 70pt;" src="foto_mhs/<?= $t_mhs['foto_mhs']; ?>"></td>
									<?php } ?>

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
	