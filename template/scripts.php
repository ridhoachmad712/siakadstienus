<?php
/**
 * Script bersama untuk seluruh halaman SIAKAD.
 * Dipanggil menjelang </body>:
 *     <?php include "../template/scripts.php"; ?>
 * Script khusus milik halaman tetap ditulis setelah include ini.
 */
$sk_base = isset($sk_base) ? $sk_base : '../';
?>
<script src="<?= $sk_base; ?>dist/js/jquery.js"></script>
<script src="<?= $sk_base; ?>dist/js/tabler.min.js"></script>
<script src="<?= $sk_base; ?>assets/form-layout.js?v=20261003-layout"></script>
<script>
	// Tautan penghapusan lama dikirim sebagai POST dengan token sesi.
	// Delegasi juga bekerja untuk baris yang dimuat melalui AJAX.
	document.addEventListener('click', function (event) {
		var link = event.target.closest('a[href]');
		if (!link) return;
		var url = new URL(link.href, window.location.href);
		if (url.origin !== window.location.origin) return;
		var aksi = url.searchParams.get('aksi');
		if (aksi !== 'hapus' && aksi !== 'del') return;
		event.preventDefault();
		event.stopImmediatePropagation();
		if (!window.confirm('Hapus data ini? Data yang sudah digunakan dalam riwayat akademik tidak dapat dihapus.')) return;
		var form = document.createElement('form');
		form.method = 'post';
		function field(name, value) {
			var input = document.createElement('input');
			input.type = 'hidden'; input.name = name; input.value = value;
			form.appendChild(input);
		}
		url.searchParams.forEach(function (value, name) { field(name, value); });
		url.searchParams.delete('aksi');
		// Pertahankan konteks mahasiswa/periode/profil dosen pada halaman aktif.
		var current = new URL(window.location.href);
		['qwe', 'qaz', 'nip'].forEach(function (name) {
			if (!url.searchParams.has(name) && current.searchParams.has(name)) url.searchParams.set(name, current.searchParams.get(name));
		});
		if (!url.searchParams.has('qwe') && url.searchParams.has('id_thn_akademik')) url.searchParams.set('qwe', url.searchParams.get('id_thn_akademik'));
		form.action = url.pathname + url.search;
		field('csrf_token', <?= json_encode(siakad_csrf_token()); ?>);
		document.body.appendChild(form); form.submit();
	}, true);
</script>
