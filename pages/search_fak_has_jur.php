<?php
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login_ajax($koneksi);
$kode_fakultas = mysqli_real_escape_string($koneksi, $_GET["kode_fakultas"]);
$fakultas=mysqli_query($koneksi,"SELECT * FROM tbl_fakultas WHERE kode_fakultas='$kode_fakultas'");
$t_fakultas=mysqli_fetch_array($fakultas);
if(isset($_POST["query"]))
{
	$search = mysqli_real_escape_string($koneksi, $_POST["query"]);
	$jur = "SELECT * FROM fakultas_has_jurusan INNER JOIN prodi ON fakultas_has_jurusan.kode_prodi=prodi.kode_prodi
	WHERE prodi.kode_prodi LIKE '%".$search."%' OR prodi.nama_prodi LIKE '%".$search."%'";
}else{
	$jur = "SELECT * FROM fakultas_has_jurusan INNER JOIN prodi ON fakultas_has_jurusan.kode_prodi=prodi.kode_prodi WHERE fakultas_has_jurusan.kode_fakultas='$kode_fakultas'";
}
?>
<?php
	$result = mysqli_query($koneksi, $jur);
	if(mysqli_num_rows($result) > 0)
	{
		?>
		<table class="table table-vcenter card-table">
			<thead>
				<tr>
					<th>NO</th>
					<th>KODE JURUSAN/PRODI</th>
					<th>NAMA JURUSAN/PRODI</th>
					<th>KOORDINATOR JURUSAN/PRODI</th>
					<th>OPSI</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$no=1;
				while($t_jur = mysqli_fetch_array($result))
				{
					$kode_prodi=$t_jur['kode_prodi'];
					?>
					<tr>
						<td><?= $no++; ?>.</td>
						<td><?= $t_jur['kode_prodi']; ?></td>
						<td style="text-transform: capitalize;"><?= $t_jur['nama_prodi']; ?></td>
						<td>
							<?php 
							$kaprodi=mysqli_query($koneksi,"SELECT * FROM prodi INNER JOIN dosen ON prodi.ketua_prodi=dosen.nip WHERE kode_prodi='$kode_prodi'");
							$t_kaprodi=mysqli_fetch_array($kaprodi);
							echo nilai($t_kaprodi, 'nama_dosen');
							?>
						</td>
						<td>  
							<a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-danger<?= $t_jur['id']; ?>">Hapus</a>
							<!-- hapus data -->
							<div class="modal modal-blur fade" id="modal-danger<?= $t_jur['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
								<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
									<div class="modal-content">
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										<div class="modal-status bg-danger"></div>
										<div class="modal-body text-center py-4">
											<!-- Download SVG icon from http://tabler-icons.io/i/alert-triangle -->
											<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
											<h3>Ingin hapus <?= $t_jur['jenis']; ?> <?= $t_jur['nama_prodi']; ?> dari Fakultas <?= nilai($t_fakultas, 'nama_fakultas'); ?> ?</h3>
										</div>
										<div class="modal-footer">
											<div class="w-100">
												<div class="row">
													<div class="col"><a href="#" class="btn btn-white w-100" data-bs-dismiss="modal">
														Tidak
													</a></div>
													<div class="col"><a href="fak-has-jur?aksi=hapus&id=<?= $t_jur['id']; ?>&kode_fakultas=<?= $kode_fakultas; ?>" class="btn btn-danger w-100">
														Hapus
													</a></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</td>
					</tr>
					<?php
				}
			}else{
				?>
				<tr>
					<td style="padding: 10pt;" colspan="4">
						<center><br><br><p>Data Tidak ada !!!</p></center>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
