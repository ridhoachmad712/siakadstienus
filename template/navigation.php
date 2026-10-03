<?php
require_once __DIR__.'/ui.php';
$sk_route=sk_route_parent(sk_current_route());
$sk_profile=sk_current_route()==='dashboard' && ($_GET['view']??'')==='profil';
?>
<aside class="sk-sidebar" aria-label="Navigasi utama">
<a class="sk-sidebar-brand" href="dashboard"><?php if (!empty($r_pengaturan['logo_aplikasi'])) { ?><img class="sk-sidebar-logo" src="../img/<?= sk_escape(rawurlencode($r_pengaturan['logo_aplikasi'])); ?>" alt=""><?php } else { ?><span class="sk-brand-mark" aria-hidden="true">S</span><?php } ?><span><strong>SIAKAD</strong><small><?= sk_escape(($r_pengaturan['nama_kampus']??'') ?: 'STIE Nusantara'); ?></small></span></a>
<div class="sk-sidebar-caption">Ruang kerja · <?= sk_escape(sk_role_label($_SESSION['level'])); ?></div>
<ul class="sk-navigation">
<?php foreach (sk_navigation($_SESSION['level']) as [$label,$target]) {
$children=is_array($target)?$target:[];
$active=!$sk_profile && ($children?in_array($sk_route,array_column($children,1),true):$sk_route===$target);
?>
<li class="nav-item <?= $active?'active':''; ?>">
<?php if ($children) { ?>
<details class="sk-nav-group" <?= $active?'open':''; ?>><summary><?= sk_nav_icon($label); ?><span><?= sk_escape($label); ?></span><span class="sk-nav-chevron" aria-hidden="true">›</span></summary><div class="sk-nav-children">
<?php foreach ($children as [$childLabel,$href]) { $external=strpos($href,'https://')===0; ?>
<a class="<?= $sk_route===$href?'active':''; ?>" href="<?= sk_escape($href); ?>" <?= $external?'target="_blank" rel="noopener noreferrer"':''; ?> <?= $sk_route===$href?'aria-current="page"':''; ?>><?= sk_escape($childLabel); ?><?= $external?' ↗':''; ?></a>
<?php } ?></div></details>
<?php } else { $external=strpos($target,'https://')===0; ?>
<a class="nav-link <?= $active?'active':''; ?>" href="<?= sk_escape($target); ?>" <?= $external?'target="_blank" rel="noopener noreferrer"':''; ?> <?= $active?'aria-current="page"':''; ?>><?= sk_nav_icon($label); ?><span><?= sk_escape($label); ?><?= $external?' ↗':''; ?></span></a>
<?php } ?></li>
<?php } ?></ul>
<div class="sk-sidebar-footer"><small>Sistem Informasi Akademik</small><a href="logout">Keluar dari akun <span aria-hidden="true">↗</span></a></div>
</aside>
<button class="sk-sidebar-backdrop" type="button" aria-label="Tutup menu navigasi" tabindex="-1"></button>
