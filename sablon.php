<?php
require_once __DIR__ . '/bootstrap.php';

$slug = $_GET['slug'] ?? '';
$config = isValidSlug($slug) ? getTemplateConfig($slug) : null;

if ($config === null) {
    http_response_code(404);
    $pageTitle = 'Şablon bulunamadı | ' . SITE_TITLE;
    require __DIR__ . '/partials/_header.php';
    echo '<main class="template-main"><h1>Şablon bulunamadı</h1><p><a href="/">Ana sayfaya dön</a></p></main>';
    require __DIR__ . '/partials/_footer.php';
    exit;
}

if (($config['kind'] ?? 'contract') === 'resume') {
    header('Location: cv-olustur.php', true, 301);
    exit;
}

$formErrors = $_SESSION['form_errors'][$slug] ?? [];
$formValues = $_SESSION['form_values'][$slug] ?? [];
$formExtraClauses = $_SESSION['form_extra_clauses'][$slug] ?? [];
$formClauseOverrides = $_SESSION['form_clause_overrides'][$slug] ?? [];
unset(
    $_SESSION['form_errors'][$slug],
    $_SESSION['form_values'][$slug],
    $_SESSION['form_extra_clauses'][$slug],
    $_SESSION['form_clause_overrides'][$slug]
);

$categoryNames = ['sozlesmeler' => 'Sözleşmeler', 'dilekceler' => 'Dilekçeler', 'is-belgeleri' => 'İş Belgeleri', 'kisisel-belgeler' => 'Kişisel Belgeler'];
$categoryLabel = $categoryNames[$config['category']] ?? ucfirst($config['category'] ?? '');

$fieldsByName = [];
foreach ($config['fields'] as $field) {
    $fieldsByName[$field['name']] = $field;
}

$user = currentUser();
$isPremium = !empty($user['is_premium']);
$personGroupsByAnchor = [];
foreach ($config['personGroups'] ?? [] as $group) {
    $anchorField = $group['map']['full_name'] ?? null;
    if ($anchorField !== null) {
        $personGroupsByAnchor[$anchorField] = $group;
    }
}

$steps = $config['steps'] ?? [['title' => 'Bilgiler', 'fields' => array_column($config['fields'], 'name')]];
if (!empty($config['clauses'])) {
    $steps[] = ['title' => 'Maddeleri Düzenle', 'fields' => [], 'clauseEditor' => true];
}
if ($config['allowCustomClauses'] ?? true) {
    $steps[] = ['title' => 'Ek Maddeler', 'fields' => [], 'customClauses' => true];
}

$renderedClauses = array_merge(renderClauses($config, $formValues, $formClauseOverrides), renderCustomClauses($formExtraClauses));
$signatures = $config['signatures'] ?? ['Taraf 1', 'Taraf 2'];

$pageTitle = ($config['seoTitle'] ?? $config['title']) . ' | ' . SITE_TITLE;
$pageDescription = $config['description'] ?? SITE_DESCRIPTION;
require __DIR__ . '/partials/_header.php';
$__breadcrumbJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => SITE_URL . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $categoryLabel, 'item' => SITE_URL . '/kategori.php?slug=' . urlencode($config['category'])],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $config['title'], 'item' => $__canonicalUrl],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($__breadcrumbJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<div class="breadcrumb">
  <a href="/">Ana Sayfa</a><span>&rsaquo;</span>
  <a href="kategori.php?slug=<?= urlencode($config['category']) ?>"><?= htmlspecialchars($categoryLabel) ?></a><span>&rsaquo;</span>
  <span class="current"><?= htmlspecialchars($config['title']) ?></span>
</div>

<main class="template-main">
  <div class="template-heading">
    <h1><?= htmlspecialchars($config['title']) ?></h1>
    <p>Bilgilerini gir, belgen sağda canlı olarak önizlensin.</p>
  </div>

  <?php if (!empty($formErrors)): ?>
    <div class="form-errors">
      <strong>Formda hatalar var:</strong>
      <ul>
        <?php foreach ($formErrors as $msg): ?>
          <li><?= htmlspecialchars($msg) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="template-grid">
    <div>
      <div class="form-card">
        <div class="info-box">
          <div class="dot">i</div>
          <p>Bu belge bilgilendirme amaçlıdır, hukuki tavsiye niteliği taşımaz.</p>
        </div>

        <div class="wizard-stepper">
          <?php foreach ($steps as $i => $step): ?>
            <div class="wizard-step-dot<?= $i === 0 ? ' active' : '' ?>" data-step-dot="<?= $i ?>">
              <span class="wizard-step-num"><?= $i + 1 ?></span>
              <span class="wizard-step-label"><?= htmlspecialchars($step['title']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <form id="doc-form" action="indir.php?slug=<?= urlencode($slug) ?>" method="post">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

          <?php foreach ($steps as $i => $step): ?>
            <div class="wizard-panel<?= $i === 0 ? ' active' : '' ?>" data-step-panel="<?= $i ?>">
              <?php if (!empty($step['clauseEditor'])): ?>
                <p class="clause-editor-intro">Standart madde metinlerini istersen kendi ihtiyacına göre değiştirebilirsin. Düzenlemediğin maddeler girdiğin bilgilerle otomatik doldurulur.</p>
                <div id="clause-editor-list" class="clause-editor-list">
                  <?php foreach ($config['clauses'] as $ci => $clause): ?>
                    <?php $hasOverride = isset($formClauseOverrides[$ci]) && trim((string) $formClauseOverrides[$ci]) !== ''; ?>
                    <div class="clause-editor-item" data-clause-index="<?= $ci ?>">
                      <div class="clause-editor-head">
                        <span class="clause-editor-title"><?= htmlspecialchars($clause['title'] !== '' ? $clause['title'] : ('Madde ' . ($ci + 1))) ?></span>
                        <button type="button" class="clause-editor-toggle" data-clause-toggle="<?= $ci ?>"><?= $hasOverride ? 'Şablona Dön' : 'Düzenle' ?></button>
                      </div>
                      <textarea name="clause_overrides[<?= $ci ?>]" class="clause-editor-textarea" data-clause-textarea="<?= $ci ?>" rows="4" placeholder="Bu maddeyi kendi metninle değiştir..."<?= $hasOverride ? '' : ' hidden' ?>><?= htmlspecialchars($formClauseOverrides[$ci] ?? '') ?></textarea>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php elseif (!empty($step['customClauses'])): ?>
                <div id="custom-clauses-list" class="custom-clauses-list">
                  <?php foreach ($formExtraClauses as $idx => $text): ?>
                    <div class="custom-clause-item">
                      <label>Ek Madde <?= $idx + 1 ?></label>
                      <textarea name="extra_clauses[]" class="custom-clause-textarea" rows="3" placeholder="Ek madde metnini yaz..."><?= htmlspecialchars($text) ?></textarea>
                      <button type="button" class="custom-clause-remove">Kaldır</button>
                    </div>
                  <?php endforeach; ?>
                </div>
                <button type="button" id="add-custom-clause-btn" class="add-clause-btn">+ Ek Madde Ekle</button>
              <?php else: ?>
                <div class="field-list">
                  <?php foreach ($step['fields'] as $fieldName):
                    $field = $fieldsByName[$fieldName] ?? null;
                    if ($field === null) {
                        continue;
                    }
                    $personGroup = $personGroupsByAnchor[$fieldName] ?? null;
                    if ($personGroup !== null):
                        if ($isPremium): ?>
                          <div class="person-picker-row">
                            <select class="person-picker-select" data-map='<?= htmlspecialchars(json_encode($personGroup['map'], JSON_UNESCAPED_UNICODE)) ?>'>
                              <option value="">— <?= htmlspecialchars($personGroup['label']) ?>: Kişilerim'den seç —</option>
                            </select>
                          </div>
                        <?php else: ?>
                          <div class="person-picker-locked">🔒 Premium ile <?= htmlspecialchars(mb_strtolower($personGroup['label'], 'UTF-8')) ?> bilgilerini Kişilerim'den tek tıkla doldurabilirsin. <a href="premium.php">Premium'a Geç →</a></div>
                        <?php endif;
                    endif;
                    $val = $formValues[$fieldName] ?? '';
                    renderFieldInput($field, $val);
                  endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>

          <div class="wizard-nav">
            <button type="button" id="wizard-back-btn" class="wizard-btn-back">Geri</button>
            <button type="button" id="wizard-next-btn" class="wizard-btn-next">İleri</button>
            <button type="submit" id="download-btn" class="download-btn" disabled>PDF Olarak İndir</button>
          </div>

          <div class="download-area">
            <p id="required-hint" class="required-hint">PDF indirmek için zorunlu alanları doldur.</p>
            <p class="premium-hint">Ücretsiz PDF'de küçük filigran bulunur. Filigransız indirme için <a href="premium.php" class="accent-link">Premium'a geç &rarr;</a></p>
          </div>
        </form>
      </div>
    </div>

    <div class="preview-wrap">
      <div class="preview-outer">
        <div class="preview-sheet">
          <div class="preview-title">
            <h3><?= htmlspecialchars(trUpper($config['title'])) ?></h3>
            <div class="rule"></div>
          </div>

          <div id="preview-clauses">
            <?php foreach ($renderedClauses as $clause): ?>
              <div class="madde-block">
                <p class="madde-title"><?= htmlspecialchars($clause['title']) ?></p>
                <?php foreach ($clause['lines'] as $line): ?>
                  <p class="madde-line"><?= renderLineHtml($line) ?></p>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="sig-row">
            <?php foreach ($signatures as $label): ?>
              <div><?= htmlspecialchars($label) ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($config['longDescription'])): ?>
    <section class="template-info">
      <h2>Bu Belge Ne İşe Yarar?</h2>
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

<script type="application/json" id="tpl-config"><?= json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="/assets/js/live-preview.js"></script>
<?php if ($isPremium && !empty($config['personGroups'])): ?>
<script src="/assets/js/person-picker.js"></script>
<?php endif; ?>

<?php require __DIR__ . '/partials/_footer.php'; ?>
