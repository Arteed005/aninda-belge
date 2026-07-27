<?php
require_once __DIR__ . '/bootstrap.php';

$token = $_GET['token'] ?? '';
$success = $token !== '' && verifyEmailToken($token);

$pageTitle = 'E-posta Doğrulama | ' . SITE_TITLE;
$pageDescription = 'Anında belge hesabı e-posta doğrulama sayfası.';
require __DIR__ . '/partials/_header.php';
?>

<main>
<section class="verify-result">
  <div class="verify-result-card">
    <?php if ($success): ?>
      <h1>E-posta Adresin Doğrulandı</h1>
      <p>Artık hesabının tüm özelliklerinden faydalanabilirsin.</p>
    <?php else: ?>
      <h1>Bağlantı Geçersiz veya Süresi Dolmuş</h1>
      <p>Doğrulama bağlantısının süresi dolmuş olabilir. Giriş yaptıktan sonra doğrulama e-postasını tekrar gönderebilirsin.</p>
    <?php endif; ?>
    <a href="/" class="verify-result-btn">Ana Sayfaya Dön</a>
  </div>
</section>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
