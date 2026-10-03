<?php
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login_ajax($koneksi);
require_once '../config/table_pagination.php';
if(isset($_POST["query"]))
{
	$search = mysqli_real_escape_string($koneksi, $_POST["query"]);
	$mata_kuliah = "SELECT * FROM mata_kuliah INNER JOIN tbl_jenis_mk ON mata_kuliah.id_jenis_mk=tbl_jenis_mk.id_jenis_mk
	WHERE kode_matkul LIKE '%".$search."%' OR nama_matkul LIKE '%".$search."%' OR sks LIKE '%".$search."%' ";
}else{
	$mata_kuliah = "SELECT * FROM mata_kuliah INNER JOIN tbl_jenis_mk ON mata_kuliah.id_jenis_mk=tbl_jenis_mk.id_jenis_mk ORDER BY semester ASC";
}
?>
<?php
	[$mata_kuliah,$sk_offset]=siakad_tabel_halaman($koneksi,$mata_kuliah,['Semester'=>'mata_kuliah.semester']);
$result = mysqli_query($koneksi, $mata_kuliah);
	if(mysqli_num_rows($result) > 0)
	{
		?>
		<table class="table table-vcenter card-table">
			<thead>
				<tr>
					<th style="text-align: center;">NO.</th>
					<th style="text-align: center;">KODE MATA KULIAH</th>
					<th style="text-align: center;">MATA KULIAH</th>
					<th style="text-align: center;">SKS</th>
					<th style="text-align: center;">SEMESTER</th>
					<th style="text-align: center;">JENIS</th>
					<th style="text-align: center;">PILIHAN</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$no=$sk_offset+1;
				while($t_matkul = mysqli_fetch_array($result))
				{
					$kode_matkul=$t_matkul['kode_matkul'];
					?>
					<tr>
						<td style="text-align: center;"><?= $no++; ?>.</td>
						<td style="text-align: center;"><?= $t_matkul['kode_matkul']; ?></td>
						<td style="text-transform: capitalize;"><?= $t_matkul['nama_matkul']; ?></td>
						<td style="text-align: center;">
							<?= $t_matkul['sks']; ?>
						</td>
						<td style="text-align: center;">
							<?= $t_matkul['semester']; ?>
						</td>
						<td style="text-align: center;">
							<?= $t_matkul['jenis_mk']; ?>
						</td>
						<td class="text-center">  
							<!-- edit data -->
							<a class="btn btn-warning center-icon" data-bs-toggle="offcanvas" href="#offcanvasEnd<?= $t_matkul['kode_matkul']; ?>" role="button" aria-controls="offcanvasEnd">
								<!-- Download SVG icon from http://tabler-icons.io/i/edit -->
								<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
							</a>
							<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd<?= $t_matkul['kode_matkul']; ?>" aria-labelledby="offcanvasEndLabel">
								<form action="" method="post">
<?php siakad_csrf_field(); ?>
									<div class="offcanvas-header">
										<h2 class="offcanvas-title" id="offcanvasEndLabel">Edit data Mata Kuliah</h2>
										<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
									</div>
									<div class="offcanvas-body">
										<div>
											<div class="mb-3">
												<label>Kode Matkul</label> 
												<input type="hidden" name="kode_matkul" value="<?= $t_matkul['kode_matkul']; ?>">
												<input type="text" name="kode_matkul" readonly="readonly" value="<?= $t_matkul['kode_matkul']; ?>" class="form-control" required="require">
											</div>
											<div class="mb-3">
												<label>Mata Kuliah</label> 
												<textarea class="form-control" name="nama_matkul" required="required"><?= $t_matkul['nama_matkul']; ?></textarea>
											</div>
											<div class="mb-3">
												<label>SKS</label>
												<input type="number" class="form-control" name="sks" value="<?= $t_matkul['sks']; ?>" required="required">
											</div>
											<div class="mb-3">
												<label>Semester</label>
												<input type="number" class="form-control" name="semester" value="<?= $t_matkul['semester']; ?>" required="required">
											</div>
											<div class="mb-3">
												<label>Jenis MK</label>
												<select name="id_jenis_mk" class="form-control">
													<?php
													$query_jm="SELECT * FROM tbl_jenis_mk";
													$sql_jenis_mk=mysqli_query($koneksi, $query_jm);
													while ($data_jk=mysqli_fetch_array($sql_jenis_mk)) {
														?>
														<option value="<?= $data_jk['id_jenis_mk'] ?>" <?= ($data_jk['jenis_mk'] == $t_matkul['jenis_mk'])? "selected": "" ?>> 
															<?= $data_jk['jenis_mk']?>
														</option>
														<?php											
													}
													?>      
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
							<a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-danger<?= $t_matkul['kode_matkul']; ?>">
								<!-- Download SVG icon from http://tabler-icons.io/i/trash -->
								<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
							</a>
							<!-- hapus data -->
							<div class="modal modal-blur fade" id="modal-danger<?= $t_matkul['kode_matkul']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
								<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
									<div class="modal-content">
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										<div class="modal-status bg-danger"></div>
										<div class="modal-body text-center py-4">
											<!-- Download SVG icon from http://tabler-icons.io/i/alert-triangle -->
											<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
											<h3>Apakah Anda Benar Ingin Menghapus Mata kuliah <?= $t_matkul['nama_matkul']; ?> ?</h3>
										</div>
										<div class="modal-footer">
											<div class="w-100">
												<div class="row">
													<div class="col"><a href="#" class="btn btn-white w-100" data-bs-dismiss="modal">
														Tidak!
													</a></div>
													<div class="col"><a href="mata_kuliah?aksi=hapus&kode_matkul=<?= $t_matkul['kode_matkul']; ?>" class="btn btn-danger w-100">
														Hapus!
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

</div>
