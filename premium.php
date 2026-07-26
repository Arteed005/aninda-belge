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
  <div class="premium-hero">
    <div class="premium-hero-glow"></div>
    <span class="premium-hero-badge">✨ Premium Üyelik</span>
    <h1>Belgelerini Filigransız ve Profesyonel İndir</h1>
    <p>Anında Belge Premium ile hazırladığın her PDF, filigransız ve teslim edilmeye hazır olur.</p>
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
        <li class="premium-benefit-row"><span class="premium-benefit-icon">💧</span>Tüm belgelerde <strong>filigransız</strong> PDF indirme</li>
        <li class="premium-benefit-row"><span class="premium-benefit-icon">⚡</span>Sınırsız belge oluşturma</li>
        <li class="premium-benefit-row"><span class="premium-benefit-icon">🎧</span>Öncelikli destek</li>
      </ul>
      <div class="premium-price">
        <span class="premium-price-amount">₺<?= PREMIUM_PRICE_TRY ?><span class="premium-price-period">/ay</span></span>
      </div>
      <?php if (!$user): ?>
        <a href="giris.php" class="premium-cta">Satın Almak İçin Giriş Yap</a>
        <p class="premium-note">Satın alabilmek için önce ücretsiz bir hesap oluşturman gerekiyor.</p>
      <?php elseif (SHOPIER_PAYMENT_URL !== ''): ?>
        <a href="<?= htmlspecialchars(SHOPIER_PAYMENT_URL) ?>" class="premium-cta">Satın Al <span aria-hidden="true">→</span></a>
        <p class="premium-note">Ödeme sayfasında, hesabındaki <strong><?= htmlspecialchars($user['email']) ?></strong> adresini kullanmayı unutma — üyeliğin bu e-postayla otomatik açılıyor.</p>
        <div class="premium-trust">🔒 Shopier ile güvenli ödeme · Tek seferlik satın alma, otomatik yenileme yok</div>
      <?php else: ?>
        <button type="button" class="premium-cta" disabled>Çok Yakında</button>
        <p class="premium-note">Ödeme altyapımızı hazırlıyoruz. Hazır olduğunda buradan satın alabileceksin — merak ediyorsan <a href="mailto:destek@anindabelge.com">destek@anindabelge.com</a> adresinden bize yazabilirsin.</p>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <?php if (!($user && !empty($user['is_premium']))): ?>
  <div class="premium-compare">
    <div class="premium-compare-col">
      <h3>Ücretsiz</h3>
      <ul>
        <li>26 belge şablonuna erişim</li>
        <li>PDF üzerinde küçük filigran</li>
        <li>Standart destek</li>
      </ul>
    </div>
    <div class="premium-compare-col is-premium">
      <h3>Premium</h3>
      <ul>
        <li>26 belge şablonuna erişim</li>
        <li>Filigransız, teslime hazır PDF</li>
        <li>Öncelikli destek</li>
      </ul>
    </div>
  </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
