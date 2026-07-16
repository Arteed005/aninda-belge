<?php
$legalLinks = [
    'kullanim-sartlari' => ['label' => 'Kullanım Şartları', 'href' => 'kullanim-sartlari.php'],
    'gizlilik-politikasi' => ['label' => 'Gizlilik Politikası', 'href' => 'gizlilik-politikasi.php'],
    'kvkk-aydinlatma-metni' => ['label' => 'KVKK Aydınlatma Metni', 'href' => 'kvkk-aydinlatma-metni.php'],
];
?>
<nav class="legal-nav">
  <div class="legal-nav-label">YASAL</div>
  <div class="legal-nav-links">
    <?php foreach ($legalLinks as $slug => $link): ?>
      <a href="<?= htmlspecialchars($link['href']) ?>" class="legal-nav-link<?= $slug === ($legalActive ?? '') ? ' active' : '' ?>"><?= htmlspecialchars($link['label']) ?></a>
    <?php endforeach; ?>
  </div>
</nav>
