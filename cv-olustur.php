<?php
require_once __DIR__ . '/bootstrap.php';

/**
 * SSR render of the CV preview. Single shared DOM structure for all 3 themes
 * — [data-theme] on the wrapper drives color/typography/photo styling in CSS
 * (assets/css/site.css). The two-column "modern" layout is only built for
 * real in the PDF (templates/resume-shell-modern.php); the live preview
 * approximates it with a colored header/accent rather than a literal
 * sidebar, keeping this in sync with one preview structure instead of three.
 */
function renderCvPreviewHtml(array $resumeData): void
{
    $contact = array_values(array_filter([
        $resumeData['email'], $resumeData['phone'], $resumeData['location'], $resumeData['linkedin'],
    ], fn($v) => $v !== ''));
    ?>
    <div class="resume-preview-header">
      <?php if (!empty($resumeData['photo'])): ?>
        <img class="resume-preview-photo" src="<?= htmlspecialchars($resumeData['photo']) ?>" alt="">
      <?php endif; ?>
      <p class="resume-preview-name"><?= htmlspecialchars($resumeData['full_name'] !== '' ? $resumeData['full_name'] : 'Ad Soyad') ?></p>
      <?php if ($resumeData['title'] !== ''): ?><p class="resume-preview-title"><?= htmlspecialchars($resumeData['title']) ?></p><?php endif; ?>
      <?php if ($contact): ?><p class="resume-preview-contact"><?= htmlspecialchars(implode(' · ', $contact)) ?></p><?php endif; ?>
    </div>

    <?php if ($resumeData['summary'] !== ''): ?>
      <div class="resume-preview-section">
        <p class="resume-preview-section-title">Hakkımda</p>
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
        <p class="resume-preview-section-title"><?= htmlspecialchars($section['title']) ?></p>
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
              <?php if ($dateRange !== ''): ?><span class="resume-preview-entry-dates"><?= htmlspecialchars($dateRange) ?></span><?php endif; ?>
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
        <p class="resume-preview-section-title">Yetenekler</p>
        <p class="resume-preview-tags"><?php foreach ($resumeData['skills'] as $skill): ?><span class="tag-pill"><?= htmlspecialchars($skill) ?></span><?php endforeach; ?></p>
      </div>
    <?php endif; ?>

    <?php if ($resumeData['languages']): ?>
      <div class="resume-preview-section">
        <p class="resume-preview-section-title">Diller</p>
        <p class="resume-preview-tags"><?php foreach ($resumeData['languages'] as $lang): ?><span class="tag-pill"><?= htmlspecialchars($lang) ?></span><?php endforeach; ?></p>
      </div>
    <?php endif; ?>

    <?php if (!empty($resumeData['hobbies'])): ?>
      <div class="resume-preview-section">
        <p class="resume-preview-section-title">Hobiler</p>
        <p class="resume-preview-tags"><?php foreach ($resumeData['hobbies'] as $hobby): ?><span class="tag-pill"><?= htmlspecialchars($hobby) ?></span><?php endforeach; ?></p>
      </div>
    <?php endif; ?>
    <?php
}

$slug = 'ozgecmis-cv';
$config = getTemplateConfig($slug);
if ($config === null) {
    http_response_code(404);
    $pageTitle = 'CV oluşturucu bulunamadı | ' . SITE_TITLE;
    require __DIR__ . '/partials/_header.php';
    echo '<main class="template-main"><h1>Sayfa bulunamadı</h1><p><a href="index.php">Ana sayfaya dön</a></p></main>';
    require __DIR__ . '/partials/_footer.php';
    exit;
}

$themes = $config['themes'] ?? [];
$themeKeys = array_column($themes, 'key');
$theme = $_GET['tema'] ?? '';
$themeSelected = in_array($theme, $themeKeys, true);

$formErrors = $_SESSION['form_errors'][$slug] ?? [];
$formValues = $_SESSION['form_values'][$slug] ?? [];
$formGroups = $_SESSION['form_groups'][$slug] ?? [];
unset($_SESSION['form_errors'][$slug], $_SESSION['form_values'][$slug], $_SESSION['form_groups'][$slug]);

$fieldsByName = [];
foreach ($config['fields'] as $field) {
    $fieldsByName[$field['name']] = $field;
}
$groupsByKey = [];
foreach ($config['groups'] ?? [] as $group) {
    $groupsByKey[$group['key']] = $group;
}
$steps = $config['steps'] ?? [];
$resumeData = renderResumeData($config, $formValues, $formGroups);

$pageTitle = 'CV Oluşturucu — 3 Şablon, Fotoğraflı Özgeçmiş | ' . SITE_TITLE;
$pageDescription = 'Klasik, modern veya minimalist şablonla profesyonel özgeçmişini ücretsiz oluştur. Fotoğraf, deneyim, eğitim, sertifika, proje ve referans ekle, PDF olarak indir.';
require __DIR__ . '/partials/_header.php';
?>

<div class="breadcrumb">
  <a href="index.php">Ana Sayfa</a><span>&rsaquo;</span>
  <a href="kategori.php?slug=<?= urlencode($config['category']) ?>">İş Belgeleri</a><span>&rsaquo;</span>
  <span class="current">CV Oluşturucu</span>
</div>

<?php if (!$themeSelected): ?>
  <main class="cv-picker-main">
    <div class="cv-picker-heading">
      <h1>CV Oluşturucu</h1>
      <p>Başlamak için bir şablon seç — istediğin zaman değiştirebilirsin.</p>
    </div>

    <div class="cv-theme-grid">
      <?php foreach ($themes as $t): ?>
        <div class="cv-theme-card">
          <div class="cv-theme-preview" data-theme-preview="<?= htmlspecialchars($t['key']) ?>">
            <div class="cv-theme-preview-photo"></div>
            <div class="cv-theme-preview-line cv-theme-preview-name"></div>
            <div class="cv-theme-preview-line cv-theme-preview-title"></div>
            <div class="cv-theme-preview-rule"></div>
            <div class="cv-theme-preview-line"></div>
            <div class="cv-theme-preview-line short"></div>
            <div class="cv-theme-preview-line"></div>
          </div>
          <h3><?= htmlspecialchars($t['label']) ?></h3>
          <p><?= htmlspecialchars($t['desc']) ?></p>
          <a href="cv-olustur.php?tema=<?= urlencode($t['key']) ?>" class="cv-theme-btn">Bu Şablonla Başla</a>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
<?php else: ?>
  <main class="template-main">
    <div class="template-heading">
      <h1>CV Oluşturucu <span class="cv-theme-badge"><?= htmlspecialchars($themes[array_search($theme, $themeKeys, true)]['label']) ?></span></h1>
      <p>Bilgilerini gir, CV'n sağda canlı olarak önizlensin. <a href="cv-olustur.php" class="accent-link">Şablonu değiştir</a></p>
    </div>

    <?php if (!empty($formErrors)): ?>
      <div class="form-errors">
        <strong>Formda hatalar var:</strong>
        <ul>
          <?php foreach ($formErrors as $msg): ?><li><?= htmlspecialchars($msg) ?></li><?php endforeach; ?>
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

          <form id="doc-form" action="cv-indir.php?tema=<?= urlencode($theme) ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

            <?php foreach ($steps as $i => $step): ?>
              <div class="wizard-panel<?= $i === 0 ? ' active' : '' ?>" data-step-panel="<?= $i ?>">
                <?php if (!empty($step['group'])):
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
            <div id="resume-preview" data-theme="<?= htmlspecialchars($theme) ?>">
              <?php renderCvPreviewHtml($resumeData); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script type="application/json" id="tpl-config"><?= json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
  <script src="assets/js/cv-builder.js"></script>
<?php endif; ?>

<?php require __DIR__ . '/partials/_footer.php'; ?>
