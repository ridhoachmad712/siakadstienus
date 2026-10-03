<?php
require_once __DIR__.'/ui.php';
$sk_route = sk_route_parent(sk_current_route());
$sk_profile = sk_current_route()==='dashboard' && ($_GET['view'] ?? '')==='profil';
?>
<ul class="navbar-nav sk-navigation" aria-label="Navigasi utama">
<?php foreach (sk_navigation($_SESSION['level']) as [$label,$target]) {
    $children = is_array($target) ? $target : [];
    $active = !$sk_profile && ($children ? in_array($sk_route,array_column($children,1),true) : $sk_route===$target);
?>
  <li class="nav-item <?= $children ? 'dropdown' : ''; ?> <?= $active ? 'active' : ''; ?>">
    <?php if ($children) { ?>
      <button type="button" class="nav-link dropdown-toggle <?= $active?'active':''; ?>" data-bs-toggle="dropdown" aria-expanded="false"><?= sk_nav_icon($label); ?><span><?= sk_escape($label); ?></span></button>
      <div class="dropdown-menu">
      <?php foreach ($children as [$childLabel,$href]) { $external = strpos($href,'https://')===0; ?>
        <a class="dropdown-item <?= $sk_route===$href?'active':''; ?>" href="<?= sk_escape($href); ?>" <?= $external?'target="_blank" rel="noopener noreferrer"':''; ?> <?= $sk_route===$href?'aria-current="page"':''; ?>><?= sk_escape($childLabel); ?><?= $external?' ↗':''; ?></a>
      <?php } ?>
      </div>
    <?php } else { $external = strpos($target,'https://')===0; ?>
      <a class="nav-link <?= $active?'active':''; ?>" href="<?= sk_escape($target); ?>" <?= $external?'target="_blank" rel="noopener noreferrer"':''; ?> <?= $active?'aria-current="page"':''; ?>><?= sk_nav_icon($label); ?><span><?= sk_escape($label); ?></span><?= $external?' ↗':''; ?></a>
    <?php } ?>
  </li>
<?php } ?>
</ul>
