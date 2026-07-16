<?php
/**
 * dompdf-safe HTML/CSS wrapper for "kind": "resume" documents. Sibling of
 * pdf-shell.php but a real resume layout — no MADDE headings, no signature
 * table. Expects $config, $resumeData (from renderResumeData()), $watermark,
 * $scale to be in scope (set by renderResumePdfHtml() in lib/Pdf.php).
 */
$scale = $scale ?? 1.0;
$f = static fn(float $px): string => round($px * $scale, 2) . 'px';
$lineHeight = max(1.2, round(1.5 * $scale, 2));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 0; size: A4; }
  body { font-family: DejaVu Sans, sans-serif; color: #1a2b4a; font-size: <?= $f(12) ?>; margin: 0; }
  .sheet { padding: <?= $f(34) ?> <?= $f(40) ?>; }
  .watermark { position: fixed; top: 40%; left: 15%; font-size: <?= $f(60) ?>; color: rgba(26,43,74,0.08); transform: rotate(-25deg); }

  .resume-header { text-align: center; margin-bottom: <?= $f(18) ?>; }
  .resume-name { font-size: <?= $f(22) ?>; font-weight: bold; letter-spacing: .3px; margin: 0 0 <?= $f(3) ?>; }
  .resume-title { font-size: <?= $f(12.5) ?>; color: #1e9e5c; font-weight: bold; margin: 0 0 <?= $f(8) ?>; }
  .resume-contact { font-size: <?= $f(10) ?>; color: #6b7688; margin: 0; }
  .resume-header-rule { border-top: 1px solid #e4e8ee; margin-top: <?= $f(14) ?>; }

  .resume-section { margin-bottom: <?= $f(16) ?>; }
  .resume-section-title {
    font-size: <?= $f(11) ?>; font-weight: bold; color: #1e9e5c; letter-spacing: 1px;
    border-bottom: 1px solid #e4e8ee; padding-bottom: <?= $f(4) ?>; margin: 0 0 <?= $f(9) ?>;
  }
  .resume-summary { font-size: <?= $f(12) ?>; line-height: <?= $lineHeight ?>; color: #3a4658; margin: 0; }
  .resume-tags { font-size: <?= $f(11.5) ?>; color: #3a4658; margin: 0; }

  .resume-entry-head { margin: 0 0 <?= $f(2) ?>; clear: both; overflow: hidden; }
  .resume-entry-main { font-size: <?= $f(12.5) ?>; }
  .resume-entry-primary { font-weight: bold; color: #1a2b4a; }
  .resume-entry-secondary { color: #3a4658; }
  .resume-entry-dates { float: right; font-size: <?= $f(10.5) ?>; color: #9aa5b4; white-space: nowrap; }
  .resume-entry-desc { font-size: <?= $f(11.5) ?>; color: #3a4658; line-height: <?= $lineHeight ?>; margin: <?= $f(3) ?> 0 0; }
  .resume-entry { margin-bottom: <?= $f(12) ?>; }
  .resume-entry:last-child { margin-bottom: 0; }

  .disclaimer { margin-top: <?= $f(26) ?>; font-size: <?= $f(9.5) ?>; color: #9aa5b4; border-top: 1px solid #e4e8ee; padding-top: <?= $f(10) ?>; }
</style>
</head>
<body>
<div class="sheet">
  <?php if ($watermark): ?>
  <div class="watermark">ANINDA BELGE</div>
  <?php endif; ?>

  <div class="resume-header">
    <p class="resume-name"><?= htmlspecialchars($resumeData['full_name']) ?></p>
    <?php if ($resumeData['title'] !== ''): ?>
      <p class="resume-title"><?= htmlspecialchars($resumeData['title']) ?></p>
    <?php endif; ?>
    <?php
      $contactParts = array_values(array_filter([
          $resumeData['email'], $resumeData['phone'], $resumeData['location'], $resumeData['linkedin'],
      ], fn($v) => $v !== ''));
    ?>
    <?php if ($contactParts): ?>
      <p class="resume-contact"><?= htmlspecialchars(implode('   ·   ', $contactParts)) ?></p>
    <?php endif; ?>
    <div class="resume-header-rule"></div>
  </div>

  <?php if ($resumeData['summary'] !== ''): ?>
    <div class="resume-section">
      <p class="resume-section-title">HAKKIMDA</p>
      <p class="resume-summary"><?= htmlspecialchars($resumeData['summary']) ?></p>
    </div>
  <?php endif; ?>

  <?php foreach ($resumeData['sections'] as $section): ?>
    <?php if (empty($section['entries'])) continue; ?>
    <div class="resume-section">
      <p class="resume-section-title"><?= htmlspecialchars(trUpper($section['title'])) ?></p>
      <?php $fieldNames = array_column($section['fields'], 'name'); ?>
      <?php foreach ($section['entries'] as $entry): ?>
        <?php
          $primary = $entry[$fieldNames[0] ?? ''] ?? '';
          $secondary = $entry[$fieldNames[1] ?? ''] ?? '';
          $dateRange = trim(($entry['start_date'] ?? '') . ((($entry['end_date'] ?? '') !== '') ? ' — ' . $entry['end_date'] : ''));
          $description = $entry['description'] ?? '';
        ?>
        <div class="resume-entry">
          <p class="resume-entry-head">
            <span class="resume-entry-dates"><?= htmlspecialchars($dateRange) ?></span><span class="resume-entry-main">
                <?php if ($primary !== ''): ?><span class="resume-entry-primary"><?= htmlspecialchars($primary) ?></span><?php endif; ?>
                <?php if ($secondary !== ''): ?><span class="resume-entry-secondary"><?= htmlspecialchars(($primary !== '' ? ' — ' : '') . $secondary) ?></span><?php endif; ?>
            </span>
          </p>
          <?php foreach (explode("\n", $description) as $descLine): ?>
            <?php if ($descLine !== ''): ?><p class="resume-entry-desc"><?= htmlspecialchars($descLine) ?></p><?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <?php if ($resumeData['skills']): ?>
    <div class="resume-section">
      <p class="resume-section-title">YETENEKLER</p>
      <p class="resume-tags"><?= htmlspecialchars(implode('   ·   ', $resumeData['skills'])) ?></p>
    </div>
  <?php endif; ?>

  <?php if ($resumeData['languages']): ?>
    <div class="resume-section">
      <p class="resume-section-title">DİLLER</p>
      <p class="resume-tags"><?= htmlspecialchars(implode('   ·   ', $resumeData['languages'])) ?></p>
    </div>
  <?php endif; ?>

  <p class="disclaimer">Bu belge bilgilendirme amaçlıdır. anındabelge.com üzerinden oluşturulmuştur.</p>
</div>
</body>
</html>
