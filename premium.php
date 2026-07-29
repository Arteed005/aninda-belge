<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
$pageTitle = 'Premium | ' . SITE_TITLE;
$pageDescription = 'Filigransız PDF indirme, Kayıtlı Kişiler ve Emlak Çalışma Alanı için Anında Belge paketleri.';
require __DIR__ . '/partials/_header.php';

$plans = [
    [
        'key' => 'premium',
        'title' => 'Premium',
        'price' => PREMIUM_PRICE_TRY,
        'benefits' => [
            'Tüm belgelerde filigransız PDF indirme',
            'Sınırsız belge oluşturma',
            'Kayıtlı Kişiler ile tek tıkla belge doldurma',
            'Öncelikli destek',
        ],
        'owned' => $user && !empty($user['is_premium']),
        'expiresAt' => $user ? ($user['premium_expires_at'] ?? null) : null,
        'paymentUrl' => SHOPIER_PAYMENT_URL,
    ],
    [
        'key' => 'emlak',
        'title' => 'Emlak Paketi',
        'price' => EMLAK_PRICE_TRY,
        'benefits' => [
            "Premium'un tüm avantajları",
            'Taşınmazlarım modülü (sınırsız taşınmaz, çoklu sahiplik)',
            'Kira sözleşmesinde taşınmaz seçici ile otomatik doldurma',
            'Emlak Çalışma Alanı dashboard\'u',
        ],
        'owned' => $user && !empty($user['is_emlak']),
        'expiresAt' => $user && !empty($user['is_emlak']) ? getPackageExpiryForUser((int) $user['id'], 'emlak') : null,
        'paymentUrl' => SHOPIER_EMLAK_PAYMENT_URL,
    ],
];
?>

<div class="breadcrumb">
  <a href="/">Ana Sayfa</a><span>&rsaquo;</span>
  <span class="current">Premium</span>
</div>

<main class="template-main">
  <div class="template-heading" style="text-align:center;">
    <h1>Anında Belge Paketleri</h1>
    <p>İhtiyacına göre filigransız, hızlı belge oluşturma paketlerinden birini seç.</p>
  </div>

  <div class="plan-grid">
    <?php foreach ($plans as $plan): ?>
      <div class="plan-card" id="<?= htmlspecialchars($plan['key']) ?>">
        <h2 class="plan-card-title"><?= htmlspecialchars($plan['title']) ?></h2>

        <?php if ($plan['owned']): ?>
          <div class="premium-active-badge">✓ Bu Paket Sende Var</div>
          <p class="premium-active-text">
            <?php if ($plan['expiresAt']): ?>
              <?= htmlspecialchars((new DateTime($plan['expiresAt']))->format('d.m.Y')) ?> tarihine kadar geçerli.
            <?php else: ?>
              Süresiz olarak tanımlı.
            <?php endif; ?>
          </p>
        <?php else: ?>
          <ul class="premium-benefits">
            <?php foreach ($plan['benefits'] as $benefit): ?>
              <li><?= htmlspecialchars($benefit) ?></li>
            <?php endforeach; ?>
          </ul>
          <div class="premium-price">
            <span class="premium-price-amount">₺<?= (int) $plan['price'] ?><span class="premium-price-period">/ay</span></span>
          </div>
          <?php if (!$user): ?>
            <a href="giris.php" class="plan-cta">Satın Almak İçin Giriş Yap</a>
            <p class="premium-note">Satın alabilmek için önce ücretsiz bir hesap oluşturman gerekiyor.</p>
          <?php elseif ($plan['paymentUrl'] !== ''): ?>
            <a href="<?= htmlspecialchars($plan['paymentUrl']) ?>" class="plan-cta">Satın Al</a>
            <p class="premium-note">Ödeme sayfasında, hesabındaki <strong><?= htmlspecialchars($user['email']) ?></strong> adresini kullanmayı unutma — üyeliğin bu e-postayla otomatik açılıyor.</p>
          <?php else: ?>
            <button type="button" class="plan-cta" disabled>Çok Yakında</button>
            <p class="premium-note">Bu paketi hazırlıyoruz. Hazır olduğunda buradan satın alabileceksin — merak ediyorsan <a href="mailto:destek@anindabelge.com">destek@anindabelge.com</a> adresinden bize yazabilirsin.</p>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
