<?php include __DIR__.'/head.php'; ?>
<div class="wrapper">
  <?php require __DIR__.'/header.php'; ?>
  <div class="navbar-expand-md"><div class="collapse navbar-collapse" id="navbar-menu"><nav class="navbar navbar-light" aria-label="Menu aplikasi"><div class="container-xl"><?php require __DIR__.'/menu.php'; ?></div></nav></div></div>
  <main class="page-wrapper sk-academic">
    <div class="container-xl">
      <div class="page-header"><div class="sk-eyebrow">Akademik / <?= sk_escape(sk_role_label($level)); ?></div><h1 class="page-title mt-2"><?= sk_escape($judul_halaman); ?></h1><p class="page-subtitle mt-2"><?= sk_escape($sk_description); ?></p></div>
      <div class="page-body"><?php require $sk_view; ?></div>
    </div>
    <?php require __DIR__.'/footer.php'; ?>
  </main>
</div>
<?php require __DIR__.'/scripts.php'; ?>
<script src="../assets/academic.js?v=20261003-layout"></script>
</body></html>
