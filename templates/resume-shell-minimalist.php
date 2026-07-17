<?php
/**
 * dompdf-safe HTML/CSS wrapper for the "minimalist" CV theme — single
 * column, generous whitespace, thin typography, no color blocks. Structural
 * sibling of resume-shell-klasik.php with different spacing/type choices.
 */
$scale = $scale ?? 1.0;
$f = static fn(float $px): string => round($px * $scale, 2) . 'px';
$lineHeight = max(1.3, round(1.6 * $scale, 2));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 0; size: A4; }
  body { font-family: DejaVu Sans, sans-serif; color: #2b2f36; font-size: <?= $f(11.5) ?>; margin: 0; }
  .sheet { padding: <?= $f(44) ?> <?= $f(48) ?>; }
  .watermark { position: fixed; top: 40%; left: 15%; font-size: <?= $f(60) ?>; color: rgba(26,43,74,0.06); transform: rotate(-25deg); }

  .resume-header { margin-bottom: <?= $f(26) ?>; }
  .resume-photo { width: <?= $f(64) ?>; height: <?= $f(64) ?>; border-radius: 50%; margin: 0 0 <?= $f(14) ?>; }
  .resume-name { font-size: <?= $f(21) ?>; font-weight: normal; letter-spacing: 1.5px; margin: 0 0 <?= $f(4) ?>; }
  .resume-title { font-size: <?= $f(11.5) ?>; color: #8a8f99; letter-spacing: .5px; margin: 0 0 <?= $f(10) ?>; }
  .resume-contact { font-size: <?= $f(9.5) ?>; color: #8a8f99; margin: 0; letter-spacing: .3px; }
  .resume-header-rule { width: <?= $f(30) ?>; height: <?= $f(2) ?>; background: #3d424b; margin-top: <?= $f(14) ?>; }

  .resume-section { margin-bottom: <?= $f(20) ?>; }
  .resume-section-title {
    font-size: <?= $f(9.5) ?>; font-weight: normal; color: #8a8f99; letter-spacing: 2px;
    text-transform: uppercase; margin: 0 0 <?= $f(10) ?>; padding-bottom: <?= $f(6) ?>;
    border-bottom: 1px solid #ececec;
  }
  .resume-summary { font-size: <?= $f(11.5) ?>; line-height: <?= $lineHeight ?>; color: #3d424b; margin: 0; font-weight: normal; }
  .resume-tags { font-size: <?= $f(11) ?>; color: #3d424b; margin: 0; }
  .tag-pill {
    display: inline-block; border: 1px solid #d8dade; color: #3d424b; font-size: <?= $f(10) ?>;
    padding: <?= $f(3) ?> <?= $f(10) ?>; border-radius: <?= $f(9) ?>; margin: 0 <?= $f(6) ?> <?= $f(6) ?> 0;
  }

  .resume-entry-head { margin: 0 0 <?= $f(4) ?>; clear: both; overflow: hidden; }
  .resume-entry-main { font-size: <?= $f(12) ?>; }
  .resume-entry-primary { font-weight: bold; color: #2b2f36; }
  .resume-entry-secondary { color: #6b7079; }
  .resume-entry-dates {
    float: right; font-size: <?= $f(9.5) ?>; color: #8a8f99; white-space: nowrap;
    background: #f5f5f6; padding: <?= $f(2) ?> <?= $f(8) ?>; border-radius: <?= $f(8) ?>;
  }
  .resume-entry-desc { font-size: <?= $f(11) ?>; color: #3d424b; line-height: <?= $lineHeight ?>; margin: <?= $f(4) ?> 0 0; }
  .resume-entry { margin-bottom: <?= $f(14) ?>; }
  .resume-entry:last-child { margin-bottom: 0; }

  .disclaimer { margin-top: <?= $f(30) ?>; font-size: <?= $f(9) ?>; color: #b6b9c0; }
</style>
</head>
<body>
<div class="sheet">
  <?php if ($watermark): ?>
  <div class="watermark">ANINDA BELGE</div>
  <?php endif; ?>

  <div class="resume-header">
    <?php if (!empty($resumeData['photo'])): ?>
      <img class="resume-photo" src="<?= htmlspecialchars($resumeData['photo']) ?>" alt="">
    <?php endif; ?>
    <p class="resume-name"><?= htmlspecialchars(trUpper($resumeData['full_name'])) ?></p>
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
      <p class="resume-section-title">Hakkımda</p>
      <p class="resume-summary"><?= htmlspecialchars($resumeData['summary']) ?></p>
    </div>
  <?php endif; ?>

  <?php foreach ($resumeData['sections'] as $section): ?>
    <?php if (empty($section['entries'])) continue; ?>
    <div class="resume-section">
      <p class="resume-section-title"><?= htmlspecialchars($section['title']) ?></p>
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
            <?php if ($dateRange !== ''): ?><span class="resume-entry-dates"><?= htmlspecialchars($dateRange) ?></span><?php endif; ?><span class="resume-entry-main">
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
      <p class="resume-section-title">Yetenekler</p>
      <p class="resume-tags"><?php foreach ($resumeData['skills'] as $skill): ?><span class="tag-pill"><?= htmlspecialchars($skill) ?></span><?php endforeach; ?></p>
    </div>
  <?php endif; ?>

  <?php if ($resumeData['languages']): ?>
    <div class="resume-section">
      <p class="resume-section-title">Diller</p>
      <p class="resume-tags"><?php foreach ($resumeData['languages'] as $lang): ?><span class="tag-pill"><?= htmlspecialchars($lang) ?></span><?php endforeach; ?></p>
    </div>
  <?php endif; ?>

  <?php if (!empty($resumeData['hobbies'])): ?>
    <div class="resume-section">
      <p class="resume-section-title">Hobiler</p>
      <p class="resume-tags"><?php foreach ($resumeData['hobbies'] as $hobby): ?><span class="tag-pill"><?= htmlspecialchars($hobby) ?></span><?php endforeach; ?></p>
    </div>
  <?php endif; ?>

  <p class="disclaimer">Bu belge bilgilendirme amaçlıdır. anındabelge.com üzerinden oluşturulmuştur.</p>
</div>
</body>
</html>
