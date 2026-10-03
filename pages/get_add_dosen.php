<?php
session_start();

include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login_ajax($koneksi);
$kode_prodi=$_SESSION['kode_prodi'];
if(isset($_POST["query"]))
{
	$search = mysqli_real_escape_string($koneksi, $_POST["query"]);
	$data_dosen = "SELECT * FROM dosen LEFT JOIN tbl_jk ON dosen.id_jk=tbl_jk.id_jk
	LEFT JOIN tbl_agama ON dosen.id_agama=tbl_agama.id_agama
	WHERE nip LIKE '%".$search."%' OR nama_dosen LIKE '%".$search."%' ";
}else{
	$data_dosen = "SELECT * FROM dosen LEFT JOIN tbl_jk ON dosen.id_jk=tbl_jk.id_jk
	LEFT JOIN tbl_agama ON dosen.id_agama=tbl_agama.id_agama ORDER BY nama_dosen ASC";
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
	$result = mysqli_query($koneksi, $data_dosen);
	if(mysqli_num_rows($result) > 0)
	{
		?>
		<form action="" method="post">
<?php siakad_csrf_field(); ?>
			<div class="card">
				<div class="card-body">
					<table>
						<tr>
							<td><a href="jurusan_has_dosen" class="btn">Kembali</a></td>
							<td><input type="submit" class="btn" name="tambah" value="Tambah"></td>
						</tr>
					</table>
				</div>
			</div>
			<table class="table table-vcenter card-table">
				<thead>
					<tr>
						<th style="text-align: center;">OPSI</th>
						<th>NO</th>
						<th>NIP</th>
						<th>NAMA DOSEN</th>
						<th>JK</th>
						<TH>AGAMA</TH>
						<TH>ALAMAT</TH>
						<th>TTL</th>
						<TH>FOTO</TH>
					</tr>
				</thead>
					<?php
					$no=1;
					while($t_dosen = mysqli_fetch_array($result))
					{
						$nip=$t_dosen['nip'];
						$foto_dosen=$t_dosen['foto_dosen'];
						?>

						<?php 
						$cek_data=mysqli_num_rows(mysqli_query($koneksi,"SELECT * FROM prodi_has_dosen WHERE nip='$nip' AND kode_prodi='$kode_prodi'"));
						if ($cek_data > 0) {

						}else{
							?>
							<tr>
								<td style="text-align: center;"><input type="checkbox" name="pilih[]" value="<?= $nip; ?>"></td>
								<td><?= $no++; ?>.</td>
								<td><?= $t_dosen['nip']; ?></td>
								<td style="text-transform: capitalize;"><?= $t_dosen['nama_dosen']; ?></td>
								<td>
									<?= $t_dosen['jenis_kelamin']; ?>
								</td>
								<td><?= $t_dosen['agama']; ?></td>
								<td><?= $t_dosen['alamat']; ?></td>
								<td><?= $t_dosen['tmp_lhr_dosen']; ?>, <?= tgl_indo($t_dosen['tgl_lhr_dosen']); ?></td>
								<td>
									<?php 
									if ($foto_dosen=='') {
										?>
										<img style="width: 60pt;" src="foto_dosen/avatar-blank.png"></td>
									<?php }else{ ?>
										<img style="width: 60pt;" src="foto_dosen/<?= $t_dosen['foto_dosen']; ?>"></td>
									<?php } ?>
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
