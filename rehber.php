<?php
require_once __DIR__ . '/bootstrap.php';

$slug = $_GET['slug'] ?? '';
$config = isValidSlug($slug) ? getGuideConfig($slug) : null;

if ($config === null) {
    http_response_code(404);
    $pageTitle = 'Rehber bulunamadı | ' . SITE_TITLE;
    require __DIR__ . '/partials/_header.php';
    echo '<main class="template-main"><h1>Rehber bulunamadı</h1><p><a href="rehberler.php">Rehberlere dön</a></p></main>';
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
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => SITE_URL . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Rehberler', 'item' => SITE_URL . '/rehberler.php'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $config['title'], 'item' => $__canonicalUrl],
    ],
];
$__articleJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $config['title'],
    'description' => $config['description'] ?? '',
    'datePublished' => $config['publishedDate'] ?? null,
    'dateModified' => $config['updatedDate'] ?? ($config['publishedDate'] ?? null),
    'author' => ['@type' => 'Organization', 'name' => SITE_TITLE],
    'publisher' => ['@type' => 'Organization', 'name' => SITE_TITLE],
];
?>
<script type="application/ld+json"><?= json_encode($__breadcrumbJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<script type="application/ld+json"><?= json_encode($__articleJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<div class="breadcrumb">
  <a href="/">Ana Sayfa</a><span>&rsaquo;</span>
  <a href="rehberler.php">Rehberler</a><span>&rsaquo;</span>
  <span class="current"><?= htmlspecialchars($config['title']) ?></span>
</div>

<main class="template-main">
  <article class="guide-article">
    <div class="template-heading">
      <h1><?= htmlspecialchars($config['title']) ?></h1>
      <p><?= htmlspecialchars($config['description'] ?? '') ?></p>
      <?php if (!empty($config['updatedDate'])): ?>
        <p class="guide-updated">Güncellenme: <?= htmlspecialchars((new DateTime($config['updatedDate']))->format('d.m.Y')) ?></p>
      <?php endif; ?>
    </div>

    <div class="guide-body">
      <?php foreach ($config['body'] ?? [] as $block): ?>
        <?php if (($block['type'] ?? '') === 'h2'): ?>
          <h2><?= htmlspecialchars($block['text']) ?></h2>
        <?php elseif (($block['type'] ?? '') === 'list'): ?>
          <ul>
            <?php foreach ($block['items'] ?? [] as $li): ?>
              <li><?= htmlspecialchars($li) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p><?= htmlspecialchars($block['text'] ?? '') ?></p>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($config['relatedCalculators']) || !empty($config['relatedTemplates'])): ?>
      <div class="guide-related">
        <h3>İlgili Araçlar</h3>
        <div class="guide-related-links">
          <?php foreach ($config['relatedCalculators'] ?? [] as $calcSlug): $calc = getCalculatorConfig($calcSlug); if (!$calc) continue; ?>
            <a href="hesapla.php?slug=<?= urlencode($calcSlug) ?>" class="guide-related-link">🧮 <?= htmlspecialchars($calc['title']) ?></a>
          <?php endforeach; ?>
          <?php foreach ($config['relatedTemplates'] ?? [] as $tplSlug): $tpl = getTemplateConfig($tplSlug); if (!$tpl) continue; ?>
            <a href="sablon.php?slug=<?= urlencode($tplSlug) ?>" class="guide-related-link">📄 <?= htmlspecialchars($tpl['title']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </article>

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

<?php require __DIR__ . '/partials/_footer.php'; ?>
