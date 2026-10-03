<?php
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login_ajax($koneksi);
require_once '../config/table_pagination.php';
if(isset($_POST["query"]))
{
	$search = mysqli_real_escape_string($koneksi, $_POST["query"]);
	$jurusan = "SELECT * FROM prodi INNER JOIN dosen ON prodi.ketua_prodi=dosen.nip
	WHERE kode_prodi LIKE '%".$search."%' OR nama_prodi LIKE '%".$search."%' OR jenjang LIKE '%".$search."%' ";
}else{
	$jurusan = "SELECT * FROM prodi INNER JOIN dosen ON prodi.ketua_prodi=dosen.nip ORDER BY nama_prodi ASC";
}
?>
<?php
	[$jurusan,$sk_offset]=siakad_tabel_halaman($koneksi,$jurusan,[]);
$result = mysqli_query($koneksi, $jurusan);
	if(mysqli_num_rows($result) > 0)
	{
		?>
		<table class="table table-vcenter card-table">
			<thead>
				<tr>
					<th style="text-align: center;">NO.</th>
					<th style="text-align: center;">KODE PROGRAM STUDI</th>
					<th style="text-align: center;">NAMA PROGRAM STUDI</th>
					<th style="text-align: center;">KETUA PROGRAM STUDI</th>
					<th style="text-align: center;">JENJANG</th>
					<th style="text-align: center;">AKREDITASI</th>
					<th style="text-align: center;">OPSI</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$no=$sk_offset+1;
				while($t_jurusan = mysqli_fetch_array($result))
				{
					$kode_jurusan=$t_jurusan['kode_prodi'];
					?>
					<tr>
						<td style="text-align: center;"><?= $no++; ?>.</td>
						<td style="text-align: center;"><?= $t_jurusan['kode_prodi']; ?></td>
						<td style="text-transform: capitalize;"><?= $t_jurusan['nama_prodi']; ?></td>
						<td style="text-align: center;">
							<?= $t_jurusan['nama_dosen']; ?>
						</td>
						<td style="text-align: center;">
							<?= $t_jurusan['jenjang']; ?>
						</td>
						<td style="text-align: center;">
							<?= $t_jurusan['akreditasi']; ?>
						</td>
						<td style="text-align: center;">  
							<a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-danger<?= $t_jurusan['kode_prodi']; ?>">Hapus</a>
							<!-- hapus data -->
							<div class="modal modal-blur fade" id="modal-danger<?= $t_jurusan['kode_prodi']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
								<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
									<div class="modal-content">
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										<div class="modal-status bg-danger"></div>
										<div class="modal-body text-center py-4">
											<!-- Download SVG icon from http://tabler-icons.io/i/alert-triangle -->
											<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
											<h3>Ingin hapus data <?= $t_jurusan['jenis']; ?> ?</h3>
											<div class="text-muted"><?= $t_jurusan['nama_prodi']; ?></div>
										</div>
										<div class="modal-footer">
											<div class="w-100">
												<div class="row">
													<div class="col"><a href="#" class="btn btn-white w-100" data-bs-dismiss="modal">
														Tidak
													</a></div>
													<div class="col"><a href="jurusan?aksi=hapus&kode_prodi=<?= $t_jurusan['kode_prodi']; ?>" class="btn btn-danger w-100">
														Hapus
													</a></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- edit data -->
							<a class="btn btn-warning" data-bs-toggle="offcanvas" href="#offcanvasEnd<?= $t_jurusan['kode_prodi']; ?>" role="button" aria-controls="offcanvasEnd">
								Edit
							</a>
							<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd<?= $t_jurusan['kode_prodi']; ?>" aria-labelledby="offcanvasEndLabel">
								<form action="" method="post">
<?php siakad_csrf_field(); ?>
									<div class="offcanvas-header">
										<h2 class="offcanvas-title" id="offcanvasEndLabel">Edit Data Program Studi</h2>
										<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
									</div>
									<div class="offcanvas-body">
										<div>
											<div class="mb-3">
												<label>Kode Program Studi</label> 
												<input type="hidden" name="kode_prodi" value="<?= $t_jurusan['kode_prodi']; ?>">
												<input type="text" name="kode_prodi" readonly="readonly" value="<?= $t_jurusan['kode_prodi']; ?>" class="form-control" required="require">
											</div>
											<div class="mb-3">
												<label>Nama Program Studi</label> 
												<textarea class="form-control" name="nama_prodi" required="required"><?= $t_jurusan['nama_prodi']; ?></textarea>
											</div>
											<div class="mb-3">
												<label>Ketua Program Studi</label>
												<select name="ketua_prodi" class="form-control">
													<?php
													$query_dosen="SELECT * FROM dosen";
													$sql_dosen=mysqli_query($koneksi, $query_dosen);
													while ($data_dosen=mysqli_fetch_array($sql_dosen)) {
														?>
														<option value="<?= $data_dosen['nip'] ?>" <?= ($data_dosen['nama_dosen'] == $t_jurusan['nama_dosen'])? "selected": "" ?>> 
															<?= $data_dosen['nama_dosen']?>
														</option>
														<?php											
													}
													?>      
												</select>
											</div>
											<div class="mb-3">
												<label>Jenjang</label>
												<select name="jenjang" class="form-control">
													<option value="D3" <?= ('D3' == $t_jurusan['jenjang'])? "selected": "" ?>> 
														<?= 'D3' ?>
													</option>   
													<option value="S1" <?= ('S1' == $t_jurusan['jenjang'])? "selected": "" ?>> 
														<?= 'S1' ?>
													</option>  
													<option value="S2" <?= ('S2' == $t_jurusan['jenjang'])? "selected": "" ?>> 
														<?= 'S2' ?>
													</option> 
													<option value="S3" <?= ('S3' == $t_jurusan['jenjang'])? "selected": "" ?>> 
														<?= 'S3' ?>
													</option>  
												</select>
											</div>
											<div class="mb-3">
												<label>Akreditasi</label>
												<select name="akreditasi" class="form-control">
													<option value="Unggul" <?= ('Unggul' == $t_jurusan['akreditasi'])? "selected": "" ?>> 
														<?= 'Unggul' ?>
													</option>   
													<option value="Baik Sekali" <?= ('Baik Sekali' == $t_jurusan['akreditasi'])? "selected": "" ?>> 
														<?= 'Baik Sekali' ?>
													</option>  
													<option value="Baik" <?= ('Baik' == $t_jurusan['akreditasi'])? "selected": "" ?>> 
														<?= 'Baik' ?>
													</option>
												</select>
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
						<center><br><br><p>Data belum ada !!!</p></center>
					</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>

</div>
