<?php
include"../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login_ajax($koneksi);
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
		<table class="table table-vcenter card-table">
			<thead>
				<tr>
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
					<th>OPSI</th>
				</tr>
			</thead>
			<?php
			$no=1;
			while($t_mhs = mysqli_fetch_array($result))
			{
				$nim_npm=$t_mhs['nim_npm'];
				$foto_mhs=$t_mhs['foto_mhs'];
				?>
				<tr>
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
						<td>  

							<table>
								<tr>
									<td>
										<a class="btn btn-info" href="detail_mhs?nim_npm=<?= $t_mhs['nim_npm']; ?>"><!-- Download SVG icon from http://tabler-icons.io/i/eye -->
											<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="2" /><path d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7" /></svg></a>
										</td>
										<td>
											<a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-danger<?= $t_mhs['nim_npm']; ?>">
												<!-- Download SVG icon from http://tabler-icons.io/i/trash -->
												<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
											</a>
											<!-- hapus data -->
											<div class="modal modal-blur fade" id="modal-danger<?= $t_mhs['nim_npm']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
												<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
													<div class="modal-content">
														<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
														<div class="modal-status bg-danger"></div>
														<div class="modal-body text-center py-4">
															<!-- Download SVG icon from http://tabler-icons.io/i/alert-triangle -->
															<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
															<h3>Ingin hapus mahasiswa <?= $t_mhs['nama_mhs']; ?> ?</h3>
														</div>
														<div class="modal-footer">
															<div class="w-100">
																<div class="row">
																	<div class="col"><a href="#" class="btn btn-white w-100" data-bs-dismiss="modal">
																		Tidak
																	</a></div>
																	<div class="col"><a href="mhs?aksi=hapus&nim_npm=<?= $t_mhs['nim_npm']; ?>" class="btn btn-danger w-100">
																		Hapus
																	</a></div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>

										</td>
										<td>
											<!-- edit data -->
											<a class="btn btn-warning" data-bs-toggle="offcanvas" href="#offcanvasEnd<?= $t_mhs['nim_npm']; ?>" role="button" aria-controls="offcanvasEnd">
												<!-- Download SVG icon from http://tabler-icons.io/i/edit -->
												<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
											</a>
											<div style="overflow: auto;" class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd<?= $t_mhs['nim_npm']; ?>" aria-labelledby="offcanvasEndLabel">
												<form action="" method="post">
<?php siakad_csrf_field(); ?>
													<div class="offcanvas-header">
														<h2 class="offcanvas-title" id="offcanvasEndLabel">Edit data Mahasiswa</h2>
														<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
													</div>
													<div class="offcanvas-body">
														<div>
															<div class="mb-3">
																<label>NIM/NPM</label> 
																<input type="hidden" name="nim_npm" value="<?= $t_mhs['nim_npm']; ?>">
																<input type="text" name="nim_npm" readonly="readonly" value="<?= $t_mhs['nim_npm']; ?>" class="form-control" required="require">
															</div>
															<div class="mb-3">
																<label>Nama Mahasiswa</label>
																<input type="text" name="nama_mhs" class="form-control" value="<?= $t_mhs['nama_mhs']; ?>">
															</div>
															<div class="mb-3">
																<label>Tahun masuk</label>
																<input type="number" maxlength="4" name="thn_masuk" class="form-control" value="<?= $t_mhs['thn_masuk']; ?>">
															</div>
															<div class="mb-3">
																<label>Lulusan Jalur</label>
																<input type="text" name="lulusan_jalur" class="form-control" value="<?= $t_mhs['lulusan_jalur']; ?>">
															</div>
															<div class="mb-3">
																<label>Sekolah Asal</label>
																<input type="text" name="sekolah_asal" class="form-control" value="<?= $t_mhs['sekolah_asal']; ?>">
															</div>
															<div class="mb-3">
																<label>Jenis Kelamin</label>
																<select name="id_jk" class="form-control">
																	<?php
																	$query_jk="SELECT * FROM tbl_jk";
																	$sql_jk=mysqli_query($koneksi, $query_jk);
																	while ($data_jk=mysqli_fetch_array($sql_jk)) {
																		?>
																		<option value="<?= $data_jk['id_jk'] ?>" <?= ($data_jk['jenis_kelamin'] == $t_mhs['jenis_kelamin'])? "selected": "" ?>> 
																			<?= $data_jk['jenis_kelamin']?>
																		</option>
																		<?php											
																	}
																	?>      
																</select>
															</div>
															<div class="mb-3">
																<label>Tempat Lahir</label>
																<input type="text" name="tempat_lhr" class="form-control" required="required" value="<?= $t_mhs['tempat_lhr']; ?>">
															</div>
															<div class="mb-3">
																<label>Tanggal Lahir</label>
																<input type="date" name="tgl_lhr_mhs" class="form-control" required="required" value="<?= $t_mhs['tgl_lhr_mhs']; ?>">
															</div>
															<div class="mb-3">
																<label>Agama</label>
																<select name="id_agama" class="form-control">
																	<?php
																	$query_agama="SELECT * FROM tbl_agama";
																	$sql_agama=mysqli_query($koneksi, $query_agama);
																	while ($data_agama=mysqli_fetch_array($sql_agama)) {
																		?>
																		<option value="<?= $data_agama['id_agama'] ?>" <?= ($data_agama['agama'] == $t_mhs['agama'])? "selected": "" ?>> 
																			<?= $data_agama['agama']?>
																		</option>
																		<?php											
																	}
																	?>      
																</select>
															</div>
															<div class="mb-3">
																<label>Email</label>
																<input type="email" name="email" class="form-control" required="required" value="<?= $t_mhs['email']; ?>">
															</div>
															<div class="mb-3">
																<label>Alamat</label>
																<textarea class="form-control" name="alamat_mhs"><?= $t_mhs['alamat_mhs']; ?></textarea>
															</div>
															<div class="mb-3">
																<label>No Telp</label>
																<input type="number" name="no_telp_mhs" class="form-control" value="<?= $t_mhs['no_telp_mhs']; ?>">
															</div>
															<div class="mb-3">
																<label>Status Mahasiswa</label>
																<select name="status_mhs" class="form-control">
																	<option value="Aktif" <?php echo ($t_mhs['status_mhs']=='Aktif')?"selected":""; ?>>Aktif	
																		<option value="Tidak Aktif" <?php echo ($t_mhs['status_mhs']=='Tidak Aktif')?"selected":""; ?>>Tidak Aktif
																			<option value="Lulus" <?php echo ($t_mhs['status_mhs']=='Lulus')?"selected":""; ?>>Lulus
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
											</table>






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
						</table>
					