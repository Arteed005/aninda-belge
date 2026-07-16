<?php
require_once __DIR__ . '/bootstrap.php';

http_response_code(404);
$pageTitle = 'Sayfa Bulunamadı | ' . SITE_TITLE;
$pageDescription = 'Aradığın sayfa bulunamadı. Anında belge ana sayfasına dönüp belge şablonlarını keşfedebilirsin.';
require __DIR__ . '/partials/_header.php';
?>

<main>
<section class="verify-result">
  <div class="verify-result-card">
    <h1>Sayfa Bulunamadı</h1>
    <p>Aradığın sayfa kaldırılmış veya hiç var olmamış olabilir. Ana sayfadan istediğin belge şablonuna ulaşabilirsin.</p>
    <a href="index.php" class="verify-result-btn">Ana Sayfaya Dön</a>
  </div>
</section>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
