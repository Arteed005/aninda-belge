<?php
require_once __DIR__ . '/bootstrap.php';

/**
 * Renders one field's <input>/<textarea>/<select> markup. Extracted so the
 * wizard-step loop below can reuse it without duplicating the type switch.
 *
 * $nameOverride/$dataAttr let resume repeatable-group cards reuse this same
 * function with a nested POST name (experience[0][company]) and a distinct
 * data attribute (data-group-field) so they're excluded from the generic
 * required-field wizard-step check, which only looks at [data-field].
 */
function renderFieldInput(array $field, string $val, ?string $nameOverride = null, string $dataAttr = 'data-field'): void
{
    $name = $nameOverride ?? $field['name'];
    $id = 'f-' . preg_replace('/[^a-zA-Z0-9]+/', '-', $name);
    $type = $field['type'] ?? 'text';
    $required = !empty($field['required']);
    $reqAttr = $required ? 'required' : '';
    $dataVal = htmlspecialchars($field['name']);
    ?>
    <div class="field">
      <label for="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($field['label']) ?></label>

      <?php if ($type === 'textarea'): ?>
        <textarea id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>" rows="2"
          <?= $dataAttr ?>="<?= $dataVal ?>"
          placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
          <?= $reqAttr ?>><?= htmlspecialchars($val) ?></textarea>

      <?php elseif ($type === 'select'): ?>
        <select id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
          <?= $dataAttr ?>="<?= $dataVal ?>" <?= $reqAttr ?>>
          <option value="">Seçiniz</option>
          <?php foreach ($field['options'] ?? [] as $opt): ?>
            <option value="<?= htmlspecialchars($opt['value']) ?>" <?= $val === $opt['value'] ? 'selected' : '' ?>><?= htmlspecialchars($opt['label']) ?></option>
          <?php endforeach; ?>
        </select>

      <?php elseif ($type === 'date'): ?>
        <input type="date" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
          <?= $dataAttr ?>="<?= $dataVal ?>" value="<?= htmlspecialchars($val) ?>" <?= $reqAttr ?>>

      <?php else: ?>
        <input type="text" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
          <?= $dataAttr ?>="<?= $dataVal ?>" value="<?= htmlspecialchars($val) ?>"
          placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" <?= $reqAttr ?>>
      <?php endif; ?>
    </div>
    <?php
}

/**
 * SSR render of the CV preview (resume "kind" only) — mirrors the shape
 * renderResumeData() produces. A separate, browser-targeted presentation
 * from templates/resume-shell.php's PDF markup, same relationship the
 * contract path already has between #preview-clauses and pdf-shell.php.
 */
function renderResumePreviewHtml(array $resumeData): void
{
    $contact = array_values(array_filter([
        $resumeData['email'], $resumeData['phone'], $resumeData['location'], $resumeData['linkedin'],
    ], fn($v) => $v !== ''));
    ?>
    <div class="resume-preview-header">
      <p class="resume-preview-name"><?= htmlspecialchars($resumeData['full_name'] !== '' ? $resumeData['full_name'] : 'Ad Soyad') ?></p>
      <?php if ($resumeData['title'] !== ''): ?><p class="resume-preview-title"><?= htmlspecialchars($resumeData['title']) ?></p><?php endif; ?>
      <?php if ($contact): ?><p class="resume-preview-contact"><?= htmlspecialchars(implode(' · ', $contact)) ?></p><?php endif; ?>
    </div>

    <?php if ($resumeData['summary'] !== ''): ?>
      <div class="resume-preview-section">
        <p class="resume-preview-section-title">HAKKIMDA</p>
        <p class="resume-preview-text"><?= htmlspecialchars($resumeData['summary']) ?></p>
      </div>
    <?php endif; ?>

    <?php foreach ($resumeData['sections'] as $section):
        if (empty($section['entries'])) {
            continue;
        }
        $fieldNames = array_column($section['fields'], 'name');
    ?>
      <div class="resume-preview-section">
        <p class="resume-preview-section-title"><?= htmlspecialchars(trUpper($section['title'])) ?></p>
        <?php foreach ($section['entries'] as $entry):
            $primary = $entry[$fieldNames[0] ?? ''] ?? '';
            $secondary = $entry[$fieldNames[1] ?? ''] ?? '';
            $dateRange = trim(($entry['start_date'] ?? '') . ((($entry['end_date'] ?? '') !== '') ? ' — ' . $entry['end_date'] : ''));
            $description = $entry['description'] ?? '';
        ?>
          <div class="resume-preview-entry">
            <div class="resume-preview-entry-row">
              <span class="resume-preview-entry-main">
                <?php if ($primary !== ''): ?><span class="resume-preview-entry-primary"><?= htmlspecialchars($primary) ?></span><?php endif; ?>
                <?php if ($secondary !== ''): ?><span class="resume-preview-entry-secondary"><?= htmlspecialchars(($primary !== '' ? ' — ' : '') . $secondary) ?></span><?php endif; ?>
              </span>
              <span class="resume-preview-entry-dates"><?= htmlspecialchars($dateRange) ?></span>
            </div>
            <?php foreach (explode("\n", $description) as $line): if ($line !== ''): ?>
              <p class="resume-preview-entry-desc"><?= htmlspecialchars($line) ?></p>
            <?php endif; endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

    <?php if ($resumeData['skills']): ?>
      <div class="resume-preview-section">
        <p class="resume-preview-section-title">YETENEKLER</p>
        <p class="resume-preview-text"><?= htmlspecialchars(implode(' · ', $resumeData['skills'])) ?></p>
      </div>
    <?php endif; ?>

    <?php if ($resumeData['languages']): ?>
      <div class="resume-preview-section">
        <p class="resume-preview-section-title">DİLLER</p>
        <p class="resume-preview-text"><?= htmlspecialchars(implode(' · ', $resumeData['languages'])) ?></p>
      </div>
    <?php endif; ?>
    <?php
}

$slug = $_GET['slug'] ?? '';
$config = isValidSlug($slug) ? getTemplateConfig($slug) : null;

if ($config === null) {
    http_response_code(404);
    $pageTitle = 'Şablon bulunamadı | ' . SITE_TITLE;
    require __DIR__ . '/partials/_header.php';
    echo '<main class="template-main"><h1>Şablon bulunamadı</h1><p><a href="index.php">Ana sayfaya dön</a></p></main>';
    require __DIR__ . '/partials/_footer.php';
    exit;
}

$isResume = ($config['kind'] ?? 'contract') === 'resume';

$formErrors = $_SESSION['form_errors'][$slug] ?? [];
$formValues = $_SESSION['form_values'][$slug] ?? [];
$formExtraClauses = $_SESSION['form_extra_clauses'][$slug] ?? [];
$formGroups = $_SESSION['form_groups'][$slug] ?? [];
unset(
    $_SESSION['form_errors'][$slug],
    $_SESSION['form_values'][$slug],
    $_SESSION['form_extra_clauses'][$slug],
    $_SESSION['form_groups'][$slug]
);

$categoryNames = ['sozlesmeler' => 'Sözleşmeler', 'dilekceler' => 'Dilekçeler', 'is-belgeleri' => 'İş Belgeleri', 'kisisel-belgeler' => 'Kişisel Belgeler'];
$categoryLabel = $categoryNames[$config['category']] ?? ucfirst($config['category'] ?? '');

$fieldsByName = [];
foreach ($config['fields'] as $field) {
    $fieldsByName[$field['name']] = $field;
}

$groupsByKey = [];
foreach ($config['groups'] ?? [] as $group) {
    $groupsByKey[$group['key']] = $group;
}

$steps = $config['steps'] ?? [['title' => 'Bilgiler', 'fields' => array_column($config['fields'], 'name')]];
if (!$isResume && ($config['allowCustomClauses'] ?? true)) {
    $steps[] = ['title' => 'Ek Maddeler', 'fields' => [], 'customClauses' => true];
}

if ($isResume) {
    $resumeData = renderResumeData($config, $formValues, $formGroups);
} else {
    $renderedClauses = array_merge(renderClauses($config, $formValues), renderCustomClauses($formExtraClauses));
}
$signatures = $config['signatures'] ?? ['Taraf 1', 'Taraf 2'];

$pageTitle = $config['title'] . ' | ' . SITE_TITLE;
$pageDescription = $config['description'] ?? SITE_DESCRIPTION;
require __DIR__ . '/partials/_header.php';
$__breadcrumbJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => SITE_URL . '/index.php'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $categoryLabel, 'item' => SITE_URL . '/kategori.php?slug=' . urlencode($config['category'])],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $config['title'], 'item' => $__canonicalUrl],
    ],
];
?>
<script type="application/ld+json"><?= json_encode($__breadcrumbJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<div class="breadcrumb">
  <a href="index.php">Ana Sayfa</a><span>&rsaquo;</span>
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
              <?php if (!empty($step['customClauses'])): ?>
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
              <?php elseif (!empty($step['group'])):
                  $groupKey = $step['group'];
                  $group = $groupsByKey[$groupKey] ?? null;
                  $entries = $formGroups[$groupKey] ?? [];
                  if (empty($entries)) {
                      $entries = [[]];
                  }
              ?>
                <?php if ($group !== null): ?>
                  <div class="resume-group-list" data-group="<?= htmlspecialchars($groupKey) ?>">
                    <?php foreach ($entries as $idx => $entry): ?>
                      <div class="resume-group-card">
                        <div class="resume-group-card-head">
                          <span class="resume-group-card-title"><?= htmlspecialchars($group['title']) ?> <?= $idx + 1 ?></span>
                          <button type="button" class="resume-group-remove">Kaldır</button>
                        </div>
                        <div class="field-list">
                          <?php foreach ($group['fields'] as $gField):
                              $gVal = $entry[$gField['name']] ?? '';
                              renderFieldInput($gField, $gVal, $groupKey . '[' . $idx . '][' . $gField['name'] . ']', 'data-group-field');
                          endforeach; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <button type="button" class="add-clause-btn resume-add-btn" data-group="<?= htmlspecialchars($groupKey) ?>"><?= htmlspecialchars($group['addLabel'] ?? ('+ ' . $group['title'] . ' Ekle')) ?></button>
                <?php endif; ?>
              <?php else: ?>
                <div class="field-list">
                  <?php foreach ($step['fields'] as $fieldName):
                    $field = $fieldsByName[$fieldName] ?? null;
                    if ($field === null) {
                        continue;
                    }
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
            <p class="premium-hint">Ücretsiz PDF'de küçük filigran bulunur. Filigransız + Word formatı için <a href="#" class="accent-link">Premium'a geç &rarr;</a></p>
          </div>
        </form>
      </div>
    </div>

    <div class="preview-wrap">
      <div class="preview-outer">
        <div class="preview-sheet">
          <?php if ($isResume): ?>
            <div id="resume-preview">
              <?php renderResumePreviewHtml($resumeData); ?>
            </div>
          <?php else: ?>
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
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<script type="application/json" id="tpl-config"><?= json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="/assets/js/live-preview.js"></script>

<?php require __DIR__ . '/partials/_footer.php'; ?>
