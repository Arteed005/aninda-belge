<?php
require_once __DIR__ . '/bootstrap.php';

$slug = $_GET['slug'] ?? '';
$config = isValidSlug($slug) ? getCalculatorConfig($slug) : null;

if ($config === null) {
    http_response_code(404);
    $pageTitle = 'Hesaplayıcı bulunamadı | ' . SITE_TITLE;
    require __DIR__ . '/partials/_header.php';
    echo '<main class="template-main"><h1>Hesaplayıcı bulunamadı</h1><p><a href="hesaplayicilar.php">Hesaplayıcılara dön</a></p></main>';
    require __DIR__ . '/partials/_footer.php';
    exit;
}

$pageTitle = ($config['seoTitle'] ?? $config['title']) . ' | ' . SITE_TITLE;
$pageDescription = $config['description'] ?? SITE_DESCRIPTION;
require __DIR__ . '/partials/_header.php';

$__breadcrumbJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => SITE_URL . '/index.php'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Hesaplayıcılar', 'item' => SITE_URL . '/hesaplayicilar.php'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $config['title'], 'item' => $__canonicalUrl],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($__breadcrumbJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<div class="breadcrumb">
  <a href="index.php">Ana Sayfa</a><span>&rsaquo;</span>
  <a href="hesaplayicilar.php">Hesaplayıcılar</a><span>&rsaquo;</span>
  <span class="current"><?= htmlspecialchars($config['title']) ?></span>
</div>

<main class="template-main">
  <div class="template-heading">
    <h1><?= htmlspecialchars($config['title']) ?></h1>
    <p><?= htmlspecialchars($config['description'] ?? '') ?></p>
  </div>

  <div class="template-grid">
    <div>
      <div class="form-card">
        <div class="info-box">
          <div class="dot">i</div>
          <p>Bu hesaplama bilgilendirme amaçlıdır, hukuki tavsiye niteliği taşımaz.</p>
        </div>

        <div class="field-list">
          <?php foreach ($config['inputs'] as $field): ?>
            <?php renderFieldInput($field, ''); ?>
          <?php endforeach; ?>
        </div>

        <button type="button" id="calc-btn" class="download-btn calc-btn">Hesapla</button>
      </div>
    </div>

    <div>
      <div id="calc-result" class="calc-result" aria-live="polite">
        <div class="calc-result-panel">
          <div class="calc-result-placeholder">
            <div class="calc-result-placeholder-icon">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"></rect><line x1="8" y1="7.5" x2="16" y2="7.5"></line><line x1="8" y1="12" x2="10.2" y2="12"></line><line x1="13.8" y1="12" x2="16" y2="12"></line><line x1="8" y1="15.7" x2="10.2" y2="15.7"></line><line x1="13.8" y1="15.7" x2="16" y2="15.7"></line></svg>
            </div>
            <p>Bilgilerini gir ve "Hesapla"ya bas, sonucu burada göreceksin.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($config['longDescription'])): ?>
    <section class="template-info">
      <h2>Bu Hesaplayıcı Ne İşe Yarar?</h2>
      <?php foreach ($config['longDescription'] as $para): ?>
        <p><?= htmlspecialchars($para) ?></p>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <?php if (!empty($config['faq'])): ?>
    <section class="template-faq">
      <h2>Sıkça Sorulan Sorular</h2>
      <div class="faq-list">
        <?php foreach ($config['faq'] as $item): ?>
          <details class="faq-item">
            <summary class="faq-question"><?= htmlspecialchars($item['q']) ?></summary>
            <p class="faq-answer"><?= htmlspecialchars($item['a']) ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
      $__faqJsonLd = [
          '@context' => 'https://schema.org',
          '@type' => 'FAQPage',
          'mainEntity' => array_map(static fn($item) => [
              '@type' => 'Question',
              'name' => $item['q'],
              'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
          ], $config['faq']),
      ];
    ?>
    <script type="application/ld+json"><?= json_encode($__faqJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
  <?php endif; ?>
</main>

<script type="application/json" id="calc-config"><?= json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="/assets/js/calculators.js"></script>

<?php require __DIR__ . '/partials/_footer.php'; ?>
