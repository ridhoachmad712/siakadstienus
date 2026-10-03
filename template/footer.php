<footer class="footer footer-transparent d-print-none">
  <div class="container-xl sk-footer-inner">
    <span><?= htmlspecialchars(($r_pengaturan['nama_aplikasi']??'') ?: 'SIAKAD',ENT_QUOTES,'UTF-8'); ?> · Sistem Informasi Akademik</span>
    <small>Dikembangkan oleh <?= htmlspecialchars($r_pengaturan['copyright']??'',ENT_QUOTES,'UTF-8'); ?></small>
  </div>
</footer>
