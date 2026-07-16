<?php
/**
 * dompdf-safe HTML/CSS wrapper. dompdf only supports CSS 2.1 + partial CSS3 —
 * no flexbox/grid. Expects $config, $renderedClauses (from renderClauses()),
 * $watermark to be in scope (set by renderPdfHtml() in lib/Pdf.php).
 *
 * $scale (0-1) shrinks font sizes/spacing so buildFittedPdf() (lib/Pdf.php) can
 * keep the document on a single A4 page regardless of clause count.
 */
$scale = $scale ?? 1.0;
$f = static fn(float $px): string => round($px * $scale, 2) . 'px';
$lineHeight = max(1.2, round(1.6 * $scale, 2));
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 0; size: A4; }
  body { font-family: DejaVu Sans, sans-serif; color: #1a2b4a; font-size: <?= $f(12) ?>; margin: 0; }
  .sheet { padding: <?= $f(34) ?> <?= $f(40) ?>; }
  .doc-title { text-align: center; font-size: <?= $f(17) ?>; font-weight: bold; letter-spacing: 1px; margin: 0 0 <?= $f(4) ?>; }
  .doc-sub { text-align: center; font-size: <?= $f(10) ?>; color: #9aa5b4; margin: 0 0 <?= $f(16) ?>; }
  .rule { border-top: 1px solid #e4e8ee; margin-bottom: <?= $f(18) ?>; }
  .madde-title { font-size: <?= $f(11) ?>; font-weight: bold; color: #1e9e5c; letter-spacing: 0.5px; margin: 0 0 <?= $f(6) ?>; }
  .madde-line { font-size: <?= $f(12) ?>; color: #3a4658; line-height: <?= $lineHeight ?>; margin: 0 0 <?= $f(4) ?>; }
  .madde-block { margin-bottom: <?= $f(16) ?>; }
  .fv-empty { color: #c3cad4; }
  .fv-filled { font-weight: bold; }
  table.sig { width: 100%; margin-top: <?= $f(30) ?>; border-collapse: collapse; table-layout: fixed; }
  table.sig td { text-align: center; padding-top: <?= $f(8) ?>; border-top: 1px solid #c9cfd9; font-size: <?= $f(10.5) ?>; color: #9aa5b4; }
  .watermark { position: fixed; top: 40%; left: 15%; font-size: <?= $f(60) ?>; color: rgba(26,43,74,0.08); transform: rotate(-25deg); }
  .disclaimer { margin-top: <?= $f(26) ?>; font-size: <?= $f(9.5) ?>; color: #9aa5b4; border-top: 1px solid #e4e8ee; padding-top: <?= $f(10) ?>; }
</style>
</head>
<body>
<div class="sheet">
  <?php if ($watermark): ?>
  <div class="watermark">ANINDA BELGE</div>
  <?php endif; ?>

  <p class="doc-title"><?= htmlspecialchars(trUpper($config['title'] ?? '')) ?></p>
  <div class="rule"></div>

  <?php foreach ($renderedClauses as $clause): ?>
    <div class="madde-block">
      <p class="madde-title"><?= htmlspecialchars($clause['title']) ?></p>
      <?php foreach ($clause['lines'] as $line): ?>
        <p class="madde-line"><?= renderLineHtml($line) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <?php $signatures = $config['signatures'] ?? ['Taraf 1', 'Taraf 2']; ?>
  <table class="sig">
    <tr>
      <?php foreach ($signatures as $label): ?>
        <td><?= htmlspecialchars($label) ?></td>
      <?php endforeach; ?>
    </tr>
  </table>

  <p class="disclaimer">Bu belge bilgilendirme amaçlıdır, hukuki tavsiye niteliği taşımaz. anındabelge.com üzerinden oluşturulmuştur.</p>
</div>
</body>
</html>
