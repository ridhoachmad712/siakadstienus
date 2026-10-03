<?php
include "../config/koneksi.php";
require_once "../config/auth.php";
siakad_wajib_login_ajax($koneksi);
if (isset($_POST["query"])) {
	$search = mysqli_real_escape_string($koneksi, $_POST["query"]);
	$data_dosen = "SELECT * FROM dosen LEFT JOIN tbl_jk ON dosen.id_jk=tbl_jk.id_jk
	LEFT JOIN tbl_agama ON dosen.id_agama=tbl_agama.id_agama
	WHERE nip LIKE '%" . $search . "%' OR nama_dosen LIKE '%" . $search . "%' ";
} else {
	$data_dosen = "SELECT * FROM dosen LEFT JOIN tbl_jk ON dosen.id_jk=tbl_jk.id_jk
	LEFT JOIN tbl_agama ON dosen.id_agama=tbl_agama.id_agama ORDER BY nama_dosen ASC";
}
function tgl_indo($tanggal)
{
	$bulan = array(
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
	return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}
?>
<?php
	$result = mysqli_query($koneksi, $data_dosen);
	if (mysqli_num_rows($result) > 0) {
	?>
		<table class="table table-vcenter card-table">
			<thead>
				<tr>
					<th>NO</th>
					<th>NIDN</th>
					<th>NAMA DOSEN</th>
					<th>JK</th>
					<TH>AGAMA</TH>
					<th>Email</th>
					<TH>ALAMAT</TH>
					<th>TTL</th>
					<TH>FOTO</TH>
					<th>OPSI</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$no = 1;
				while ($t_dosen = mysqli_fetch_array($result)) {
					$nip = $t_dosen['nip'];
					$foto_dosen = $t_dosen['foto_dosen'];
				?>
					<tr>
						<td><?= $no++; ?>.</td>
						<td><?= $t_dosen['nip']; ?></td>
						<td style="text-transform: capitalize;"><?= $t_dosen['nama_dosen']; ?></td>
						<td>
							<?= $t_dosen['jenis_kelamin']; ?>
						</td>
						<td><?= $t_dosen['agama']; ?></td>
						<td><?= $t_dosen['email']; ?></td>
						<td><?= $t_dosen['alamat']; ?></td>
						<td><?= $t_dosen['tmp_lhr_dosen']; ?>, <?= tgl_indo($t_dosen['tgl_lhr_dosen']); ?></td>
						<td>
							<?php
							if ($foto_dosen == '') {
							?>
								<img style="width: 60pt;" src="foto_dosen/avatar-blank.png">
						</td>
					<?php } else { ?>
						<img style="width: 60pt;" src="foto_dosen/<?= $t_dosen['foto_dosen']; ?>"></td>
					<?php } ?>
					</td>
					<td>
						<a href="#" data-bs-toggle="modal" data-bs-target="#modal-danger<?= $t_dosen['nip']; ?>">
							<!-- Download SVG icon from http://tabler-icons.io/i/trash -->
							<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
								<path stroke="none" d="M0 0h24v24H0z" fill="none" />
								<line x1="4" y1="7" x2="20" y2="7" />
								<line x1="10" y1="11" x2="10" y2="17" />
								<line x1="14" y1="11" x2="14" y2="17" />
								<path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
								<path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
							</svg>
						</a>
						<!-- hapus data -->
						<div class="modal modal-blur fade" id="modal-danger<?= $t_dosen['nip']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
							<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
								<div class="modal-content">
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									<div class="modal-status bg-danger"></div>
									<div class="modal-body text-center py-4">
										<!-- Download SVG icon from http://tabler-icons.io/i/alert-triangle -->
										<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
											<path stroke="none" d="M0 0h24v24H0z" fill="none" />
											<path d="M12 9v2m0 4v.01" />
											<path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" />
										</svg>
										<h3>Ingin hapus dosen <?= $t_dosen['nama_dosen']; ?> ?</h3>
									</div>
									<div class="modal-footer">
										<div class="w-100">
											<div class="row">
												<div class="col"><a href="#" class="btn btn-white w-100" data-bs-dismiss="modal">
														Tidak
													</a></div>
												<div class="col"><a href="dosen?aksi=hapus&nip=<?= $t_dosen['nip']; ?>" class="btn btn-danger w-100">
														Hapus
													</a></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- edit data -->
						<a data-bs-toggle="offcanvas" href="#offcanvasEnd<?= $t_dosen['nip']; ?>" role="button" aria-controls="offcanvasEnd">
							<!-- Download SVG icon from http://tabler-icons.io/i/edit -->
							<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
								<path stroke="none" d="M0 0h24v24H0z" fill="none" />
								<path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
								<path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
								<line x1="16" y1="5" x2="19" y2="8" />
							</svg>
						</a>
						<div style="overflow: auto;" class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd<?= $t_dosen['nip']; ?>" aria-labelledby="offcanvasEndLabel">
							<form action="" method="post">
<?php siakad_csrf_field(); ?>
								<div class="offcanvas-header">
									<h2 class="offcanvas-title" id="offcanvasEndLabel">Edit data Dosen</h2>
									<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
								</div>
								<div class="offcanvas-body">
									<div>
										<div class="mb-3">
											<label>NIP</label>
											<input type="hidden" name="nip" value="<?= $t_dosen['nip']; ?>">
											<input type="text" name="nip" readonly="readonly" value="<?= $t_dosen['nip']; ?>" class="form-control" required="require">
										</div>
										<div class="mb-3">
											<label>Nama Dosen</label>
											<input type="text" name="nama_dosen" class="form-control" value="<?= $t_dosen['nama_dosen']; ?>">
										</div>
										<div class="mb-3">
											<label>Jenis Kelamin</label>
											<select name="id_jk" class="form-control">
												<?php
												$query_jk = "SELECT * FROM tbl_jk";
												$sql_jk = mysqli_query($koneksi, $query_jk);
												while ($data_jk = mysqli_fetch_array($sql_jk)) {
												?>
													<option value="<?= $data_jk['id_jk'] ?>" <?= ($data_jk['jenis_kelamin'] == $t_dosen['jenis_kelamin']) ? "selected" : "" ?>>
														<?= $data_jk['jenis_kelamin'] ?>
													</option>
												<?php
												}
												?>
											</select>
										</div>
										<div class="mb-3">
											<label>Agama</label>
											<select name="id_agama" class="form-control">
												<?php
												$query_agama = "SELECT * FROM tbl_agama";
												$sql_agama = mysqli_query($koneksi, $query_agama);
												while ($data_agama = mysqli_fetch_array($sql_agama)) {
												?>
													<option value="<?= $data_agama['id_agama'] ?>" <?= ($data_agama['agama'] == $t_dosen['agama']) ? "selected" : "" ?>>
														<?= $data_agama['agama'] ?>
													</option>
												<?php
												}
												?>
											</select>
										</div>
										<div class="mb-3">
											<label>Alamat</label>
											<textarea class="form-control" name="alamat"><?= $t_dosen['alamat']; ?></textarea>
										</div>
										<div class="mb-3">
											<label>Email</label>
											<input type="email" name="email" class="form-control" value="<?= $t_dosen['email']; ?>">
										</div>
										<div class="mb-3">
											<label>Tempat Lahir</label>
											<input type="text" name="tmp_lhr_dosen" class="form-control" value="<?= $t_dosen['tmp_lhr_dosen']; ?>">
										</div>
										<div class="mb-3">
											<label>Tanggal Lahir</label>
											<input type="date" name="tgl_lhr_dosen" class="form-control" value="<?= $t_dosen['tgl_lhr_dosen']; ?>">
										</div>
										<div class="mb-3">
											<label>No Telp</label>
											<input type="text" name="no_telp" class="form-control" value="<?= $t_dosen['no_telp']; ?>">
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
			} else {
				?>
				<tr>
					<td style="padding: 10pt;" colspan="4">
						<center><br><br>
							<p>Data Tidak ada !!!</p>
						</center>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
