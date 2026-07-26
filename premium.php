<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
$pageTitle = 'Premium | ' . SITE_TITLE;
$pageDescription = 'Filigransız PDF indirme ve daha fazlası için Anında Belge Premium.';
require __DIR__ . '/partials/_header.php';
?>

<div class="breadcrumb">
  <a href="index.php">Ana Sayfa</a><span>&rsaquo;</span>
  <span class="current">Premium</span>
</div>

<main class="template-main">
  <div class="template-heading" style="text-align:center;">
    <h1>Anında Belge Premium</h1>
    <p>Filigransız, temiz belgeler için premium'a geç.</p>
  </div>

  <div class="premium-card">
    <?php if ($user && !empty($user['is_premium'])): ?>
      <div class="premium-active-badge">✓ Premium Üyesin</div>
      <p class="premium-active-text">Tüm belgelerin artık filigransız indiriliyor. Teşekkürler!</p>
    <?php else: ?>
      <ul class="premium-benefits">
        <li>Tüm belgelerde <strong>filigransız</strong> PDF indirme</li>
        <li>Sınırsız belge oluşturma</li>
        <li>Öncelikli destek</li>
      </ul>
      <div class="premium-price">
        <span class="premium-price-amount">₺49<span class="premium-price-period">/ay</span></span>
      </div>
      <button type="button" class="premium-cta" disabled>Çok Yakında</button>
      <p class="premium-note">Ödeme altyapımızı hazırlıyoruz. Hazır olduğunda buradan satın alabileceksin — merak ediyorsan <a href="mailto:destek@anindabelge.com">destek@anindabelge.com</a> adresinden bize yazabilirsin.</p>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
