<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
$pageTitle = 'Premium | ' . SITE_TITLE;
$pageDescription = 'Filigransız PDF indirme ve daha fazlası için Anında Belge Premium.';
require __DIR__ . '/partials/_header.php';
?>

<div class="breadcrumb">
  <a href="/">Ana Sayfa</a><span>&rsaquo;</span>
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
      <p class="premium-active-text">
        Tüm belgelerin artık filigransız indiriliyor. Teşekkürler!
        <?php if (!empty($user['premium_expires_at'])): ?>
          <br><?= htmlspecialchars((new DateTime($user['premium_expires_at']))->format('d.m.Y')) ?> tarihine kadar geçerli.
        <?php endif; ?>
      </p>
    <?php else: ?>
      <ul class="premium-benefits">
        <li>Tüm belgelerde <strong>filigransız</strong> PDF indirme</li>
        <li>Sınırsız belge oluşturma</li>
        <li>Öncelikli destek</li>
      </ul>
      <div class="premium-price">
        <span class="premium-price-amount">₺<?= PREMIUM_PRICE_TRY ?><span class="premium-price-period">/ay</span></span>
      </div>
      <?php if (!$user): ?>
        <a href="giris.php" class="premium-cta">Satın Almak İçin Giriş Yap</a>
        <p class="premium-note">Satın alabilmek için önce ücretsiz bir hesap oluşturman gerekiyor.</p>
      <?php elseif (SHOPIER_PAYMENT_URL !== ''): ?>
        <a href="<?= htmlspecialchars(SHOPIER_PAYMENT_URL) ?>" class="premium-cta">Satın Al</a>
        <p class="premium-note">Ödeme sayfasında, hesabındaki <strong><?= htmlspecialchars($user['email']) ?></strong> adresini kullanmayı unutma — üyeliğin bu e-postayla otomatik açılıyor.</p>
      <?php else: ?>
        <button type="button" class="premium-cta" disabled>Çok Yakında</button>
        <p class="premium-note">Ödeme altyapımızı hazırlıyoruz. Hazır olduğunda buradan satın alabileceksin — merak ediyorsan <a href="mailto:destek@anindabelge.com">destek@anindabelge.com</a> adresinden bize yazabilirsin.</p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
