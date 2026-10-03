<?php
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login_ajax($koneksi);
require_once '../config/table_pagination.php';
if(isset($_POST["query"]))
{
	$search = mysqli_real_escape_string($koneksi, $_POST["query"]);
	$fakultas = "SELECT * FROM tbl_fakultas
	WHERE kode_fakultas LIKE '%".$search."%' OR nama_fakultas LIKE '%".$search."%'";
}else{
	$fakultas = "SELECT * FROM tbl_fakultas ORDER BY nama_fakultas ASC";
}
?>
<?php
	[$fakultas,$sk_offset]=siakad_tabel_halaman($koneksi,$fakultas,[]);
$result = mysqli_query($koneksi, $fakultas);
	if(mysqli_num_rows($result) > 0)
	{
		?>
		<table class="table table-vcenter card-table">
			<thead>
				<tr>
					<th style="text-align: center;">NO.</th>
					<th style="text-align: center;">KODE INSTITUSI</th>
					<th style="text-align: center;">BIDANG STUDI</th>
					<th style="text-align: center;">PROGRAM STUDI</th>
					<th style="text-align: center;">RUANGAN</th>
					<th style="text-align: center;">OPSI</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$no=$sk_offset+1;
				while($t_fakultas = mysqli_fetch_array($result))
				{
					$kode_fakultas=$t_fakultas['kode_fakultas'];
					?>
					<tr>
						<td style="text-align: center;"><?= $no++; ?>.</td>
						<td style="text-align: center;"><?= $t_fakultas['kode_fakultas']; ?></td>
						<td style="text-transform: capitalize;"><?= $t_fakultas['nama_fakultas']; ?></td>
						<td style="text-align: center;">
							<a href="fak-has-jur?kode_fakultas=<?= $kode_fakultas; ?>" class="btn">
								<?php 
								$jumlah_jurusan=mysqli_num_rows(mysqli_query($koneksi,"SELECT * FROM fakultas_has_jurusan WHERE kode_fakultas='$kode_fakultas'"));
								echo "$jumlah_jurusan Jurusan/Prodi";
								?>
							</a>
						</td>
						<td style="text-align: center;">
							<a href="ruangan?kode_fakultas=<?= $kode_fakultas; ?>" class="btn">
								<?php 
								$jumlah_ruang=mysqli_num_rows(mysqli_query($koneksi,"SELECT * FROM tbl_ruangan WHERE kode_fakultas='$kode_fakultas'"));
								echo "$jumlah_ruang Ruangan";
								?>
							</a>
						</td>
						<td style="text-align: center;">  
							<a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-danger<?= $t_fakultas['kode_fakultas']; ?>">Hapus</a>
							<!-- hapus data -->
							<div class="modal modal-blur fade" id="modal-danger<?= $t_fakultas['kode_fakultas']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
								<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
									<div class="modal-content">
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										<div class="modal-status bg-danger"></div>
										<div class="modal-body text-center py-4">
											<!-- Download SVG icon from http://tabler-icons.io/i/alert-triangle -->
											<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
											<h3>Ingin hapus data Fakultas ?</h3>
											<div class="text-muted"><?= $t_fakultas['nama_fakultas']; ?></div>
										</div>
										<div class="modal-footer">
											<div class="w-100">
												<div class="row">
													<div class="col"><a href="#" class="btn btn-white w-100" data-bs-dismiss="modal">
														Tidak
													</a></div>
													<div class="col"><a href="fakultas?aksi=hapus&kode_fakultas=<?= $t_fakultas['kode_fakultas']; ?>" class="btn btn-danger w-100">
														Hapus
													</a></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- edit data -->
							<a class="btn btn-warning" data-bs-toggle="offcanvas" href="#offcanvasEnd<?= $t_fakultas['kode_fakultas']; ?>" role="button" aria-controls="offcanvasEnd">
								Edit
							</a>
							<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd<?= $t_fakultas['kode_fakultas']; ?>" aria-labelledby="offcanvasEndLabel">
								<form action="" method="post">
<?php siakad_csrf_field(); ?>
									<div class="offcanvas-header">
										<h2 class="offcanvas-title" id="offcanvasEndLabel">Edit data fakultas</h2>
										<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
									</div>
									<div class="offcanvas-body">
										<div>
											<div class="form-group">
												<label>Kode Fakultas</label> 
												<input type="hidden" name="kode_fakultas" value="<?= $t_fakultas['kode_fakultas']; ?>">
												<input type="text" name="kode_fakultas" readonly="readonly" value="<?= $t_fakultas['kode_fakultas']; ?>" class="form-control" required="require">
											</div>
											<div class="form-group">
												<label>Nama Fakultas</label> 
												<textarea class="form-control" name="nama_fakultas" required="required"><?= $t_fakultas['nama_fakultas']; ?></textarea>
											</div>
										</div>
										<div class="mt-3">
											<button class="btn" type="submit" name="update">
												Update
											</button>
											<button class="btn" type="button" data-bs-dismiss="offcanvas">
												Tutup
											</button>
										</div>
									</div>
								</form>
							</div>
						</td>
					</tr>
					<?php
				}
			}else{
				?>
				<tr>
					<td style="padding: 10pt;" colspan="4">
						<center><br><br><p>Data Fakultas yang anda cari tidak ditemukan !!!</p></center>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>

</div>
