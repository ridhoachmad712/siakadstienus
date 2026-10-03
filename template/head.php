<?php
/**
 * Bagian <head> bersama untuk seluruh halaman SIAKAD.
 * ------------------------------------------------------------------
 * Cara pakai di halaman dalam folder pages/:
 *     <?php $judul_halaman = "Master Data Mahasiswa"; include "../template/head.php"; ?>
 *
 * Untuk halaman di pages/cetak/ tambahkan $sk_base sebelum include:
 *     <?php $sk_base = "../../"; include "../../template/head.php"; ?>
 *
 * Variabel opsional:
 *   $judul_halaman  judul yang tampil di tab browser
 *   $sk_base        path menuju folder root aplikasi (default "../")
 *   $sk_body_class  kelas tambahan untuk <body>
 */

$sk_base = isset($sk_base) ? $sk_base : '../';
$sk_app = isset($r_pengaturan['nama_aplikasi']) && $r_pengaturan['nama_aplikasi'] !== ''
	? $r_pengaturan['nama_aplikasi'] : 'SIAKAD STIE Nusantara';
$sk_logo = isset($r_pengaturan['logo_aplikasi']) ? $r_pengaturan['logo_aplikasi'] : '';
$sk_judul = isset($judul_halaman) && $judul_halaman !== ''
	? $judul_halaman . ' &middot; ' . $sk_app : $sk_app;
$sk_body_class = isset($sk_body_class) ? $sk_body_class : '';
?><!doctype html>
<html lang="id">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<meta http-equiv="X-UA-Compatible" content="ie=edge" />
	<meta name="theme-color" content="#7b203a" />
	<meta name="csrf-token" content="<?= htmlspecialchars(siakad_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>" />
	<title><?= $sk_judul; ?></title>
	<?php if ($sk_logo !== '') { ?>
		<link rel="shortcut icon" href="<?= $sk_base; ?>img/<?= htmlspecialchars($sk_logo, ENT_QUOTES, 'UTF-8'); ?>" />
	<?php } ?>

	<!-- Font -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

	<!-- CSS dasar (Tabler) -->
	<link href="<?= $sk_base; ?>dist/css/tabler.min.css" rel="stylesheet" />
	<link href="<?= $sk_base; ?>dist/css/tabler-flags.min.css" rel="stylesheet" />
	<link href="<?= $sk_base; ?>dist/css/tabler-payments.min.css" rel="stylesheet" />
	<link href="<?= $sk_base; ?>dist/css/tabler-vendors.min.css" rel="stylesheet" />
	<link href="<?= $sk_base; ?>dist/css/demo.min.css" rel="stylesheet" />

	<!-- Tema SIAKAD (harus paling akhir) -->
	<link href="<?= $sk_base; ?>assets/siakad.css?v=20261004-mobile" rel="stylesheet" />
</head>

<body class="antialiased <?= $sk_body_class; ?> <?= strpos($sk_body_class,'page-login')===false?'sk-app-shell':''; ?>">
