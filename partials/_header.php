<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? SITE_TITLE) ?></title>
<?php
$__metaDescription = $pageDescription ?? SITE_DESCRIPTION;
$__canonicalUrl = SITE_URL . $_SERVER['REQUEST_URI'];
$__ogImage = SITE_URL . '/assets/og-image.png';
?>
<meta name="description" content="<?= htmlspecialchars($__metaDescription) ?>">
<link rel="canonical" href="<?= htmlspecialchars($__canonicalUrl) ?>">
<?php if (defined('GOOGLE_SITE_VERIFICATION') && GOOGLE_SITE_VERIFICATION): ?>
<meta name="google-site-verification" content="<?= htmlspecialchars(GOOGLE_SITE_VERIFICATION) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= htmlspecialchars(SITE_TITLE) ?>">
<meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? SITE_TITLE) ?>">
<meta property="og:description" content="<?= htmlspecialchars($__metaDescription) ?>">
<meta property="og:url" content="<?= htmlspecialchars($__canonicalUrl) ?>">
<meta property="og:image" content="<?= htmlspecialchars($__ogImage) ?>">
<meta property="og:locale" content="tr_TR">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($pageTitle ?? SITE_TITLE) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($__metaDescription) ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($__ogImage) ?>">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => SITE_TITLE,
    'url' => SITE_URL,
    'logo' => SITE_URL . '/assets/logo-aninda-belge.png',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<link rel="icon" href="/assets/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
<link rel="apple-touch-icon" href="/assets/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"></noscript>
<link rel="stylesheet" href="/assets/css/site.css">
<?php if (defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars(GA_MEASUREMENT_ID) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= htmlspecialchars(GA_MEASUREMENT_ID) ?>');
</script>
<?php endif; ?>
</head>
<body>
<header class="site-header">
  <div class="wrap header-inner">
    <a href="index.php" class="logo-link">
      <img src="/assets/logo-header.png" alt="anında belge" class="logo-img" width="320" height="175">
    </a>
    <div class="header-actions">
      <?php $__user = currentUser(); ?>
      <?php if ($__user): ?>
        <span class="header-greeting">Merhaba, <?= htmlspecialchars(explode(' ', $__user['name'])[0]) ?></span>
        <a href="belgelerim.php" class="btn-ghost">Belgelerim</a>
        <a href="cikis.php" class="btn-ghost">Çıkış Yap</a>
      <?php else: ?>
        <a href="giris.php#giris" class="btn-ghost">Giriş Yap</a>
        <a href="giris.php#kayit" class="btn-accent">Kayıt Ol</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<?php if (!empty($_SESSION['flash_notice'])): ?>
  <div class="flash-notice">
    <div class="wrap"><?= htmlspecialchars($_SESSION['flash_notice']) ?></div>
  </div>
  <?php unset($_SESSION['flash_notice']); ?>
<?php endif; ?>

<?php if ($__user && empty($__user['email_verified_at']) && !in_array(basename($_SERVER['SCRIPT_NAME']), ['dogrula.php', 'dogrula-gonder.php'], true)): ?>
  <div class="verify-banner">
    <div class="wrap verify-banner-inner">
      <span>E-posta adresini henüz doğrulamadın. <a href="dogrula-gonder.php">Doğrulama e-postasını tekrar gönder</a></span>
      <span class="verify-banner-hint">Maili göremiyorsan spam/gereksiz klasörünü kontrol etmeyi unutma.</span>
    </div>
  </div>
<?php endif; ?>
