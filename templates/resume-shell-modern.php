<?php
/**
 * dompdf-safe HTML/CSS wrapper for the "modern" CV theme — two-column with a
 * colored photo/contact sidebar. dompdf has no flexbox/grid support, so the
 * two columns are a real HTML <table> (same trick pdf-shell.php uses for its
 * signature row). Expects $config, $resumeData, $watermark, $scale in scope.
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
  table.layout { width: 100%; border-collapse: collapse; }
  td.side { width: 34%; min-height: <?= $f(1030) ?>; background: #1a2b4a; color: #ffffff; padding: <?= $f(30) ?> <?= $f(20) ?>; vertical-align: top; }
  td.main { width: 66%; padding: <?= $f(30) ?> <?= $f(30) ?>; vertical-align: top; }
  .watermark { position: fixed; top: 40%; left: 15%; font-size: <?= $f(60) ?>; color: rgba(26,43,74,0.08); transform: rotate(-25deg); }

  .resume-photo { width: <?= $f(90) ?>; height: <?= $f(90) ?>; border-radius: 50%; margin: 0 auto <?= $f(14) ?>; border: <?= $f(3) ?> solid rgba(255,255,255,.35); }
  .side-name { font-size: <?= $f(16) ?>; font-weight: bold; text-align: center; margin: 0 0 <?= $f(3) ?>; }
  .side-title { font-size: <?= $f(10.5) ?>; color: #3fc37e; text-align: center; margin: 0 0 <?= $f(18) ?>; }
  .side-section { margin-bottom: <?= $f(16) ?>; }
  .side-section-title { font-size: <?= $f(10) ?>; font-weight: bold; letter-spacing: 1px; color: #3fc37e; border-bottom: 1px solid rgba(255,255,255,.25); padding-bottom: <?= $f(4) ?>; margin: 0 0 <?= $f(8) ?>; }
  .side-line { font-size: <?= $f(10) ?>; color: #dfe4ec; margin: 0 0 <?= $f(6) ?>; word-wrap: break-word; }
  .side-tag {
    display: inline-block; font-size: <?= $f(9.5) ?>; color: #dfe4ec; background: rgba(255,255,255,.12);
    padding: <?= $f(3) ?> <?= $f(9) ?>; border-radius: <?= $f(9) ?>; margin: 0 <?= $f(5) ?> <?= $f(5) ?> 0;
  }

  .resume-section { margin-bottom: <?= $f(16) ?>; }
  .resume-section-title {
    font-size: <?= $f(11) ?>; font-weight: bold; color: #1a2b4a; letter-spacing: 1px;
    border-left: <?= $f(3) ?> solid #1e9e5c; padding-left: <?= $f(8) ?>; margin: 0 0 <?= $f(10) ?>;
  }
  .resume-summary { font-size: <?= $f(12) ?>; line-height: <?= $lineHeight ?>; color: #3a4658; margin: 0; }

  .resume-entry-head { margin: 0 0 <?= $f(4) ?>; clear: both; overflow: hidden; }
  .resume-entry-main { font-size: <?= $f(12.5) ?>; }
  .resume-entry-primary { font-weight: bold; color: #1a2b4a; }
  .resume-entry-secondary { color: #3a4658; }
  .resume-entry-dates {
    float: right; font-size: <?= $f(10) ?>; color: #6b7688; white-space: nowrap;
    background: #f2f4f7; padding: <?= $f(2) ?> <?= $f(8) ?>; border-radius: <?= $f(8) ?>;
  }
  .resume-entry-desc { font-size: <?= $f(11.5) ?>; color: #3a4658; line-height: <?= $lineHeight ?>; margin: <?= $f(3) ?> 0 0; }
  .resume-entry { margin-bottom: <?= $f(12) ?>; }
  .resume-entry:last-child { margin-bottom: 0; }

  .disclaimer { margin-top: <?= $f(20) ?>; font-size: <?= $f(9) ?>; color: #9aa5b4; }
</style>
</head>
<body>
  <?php if ($watermark): ?>
  <div class="watermark">ANINDA BELGE</div>
  <?php endif; ?>

  <table class="layout">
    <tr>
      <td class="side">
        <?php if (!empty($resumeData['photo'])): ?>
          <img class="resume-photo" src="<?= htmlspecialchars($resumeData['photo']) ?>" alt="">
        <?php endif; ?>
        <p class="side-name"><?= htmlspecialchars($resumeData['full_name']) ?></p>
        <?php if ($resumeData['title'] !== ''): ?><p class="side-title"><?= htmlspecialchars($resumeData['title']) ?></p><?php endif; ?>

        <?php
          $contactParts = array_values(array_filter([
              $resumeData['email'], $resumeData['phone'], $resumeData['location'], $resumeData['linkedin'],
          ], fn($v) => $v !== ''));
        ?>
        <?php if ($contactParts): ?>
          <div class="side-section">
            <p class="side-section-title">İLETİŞİM</p>
            <?php foreach ($contactParts as $part): ?>
              <p class="side-line"><?= htmlspecialchars($part) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($resumeData['skills']): ?>
          <div class="side-section">
            <p class="side-section-title">YETENEKLER</p>
            <?php foreach ($resumeData['skills'] as $skill): ?><p class="side-tag"><?= htmlspecialchars($skill) ?></p><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if ($resumeData['languages']): ?>
          <div class="side-section">
            <p class="side-section-title">DİLLER</p>
            <?php foreach ($resumeData['languages'] as $lang): ?><p class="side-tag"><?= htmlspecialchars($lang) ?></p><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($resumeData['hobbies'])): ?>
          <div class="side-section">
            <p class="side-section-title">HOBİLER</p>
            <?php foreach ($resumeData['hobbies'] as $hobby): ?><p class="side-tag"><?= htmlspecialchars($hobby) ?></p><?php endforeach; ?>
          </div>
        <?php endif; ?>
      </td>
      <td class="main">
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

        <p class="disclaimer">Bu belge bilgilendirme amaçlıdır. anındabelge.com üzerinden oluşturulmuştur.</p>
      </td>
    </tr>
  </table>
</body>
</html>
