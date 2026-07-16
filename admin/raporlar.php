<?php
require __DIR__ . '/_guard.php';

$kpis = getReportKpis();
$trend = getMonthlyDocumentTrend(12);
$categoryBreakdown = getTemplateCategoryBreakdown();
$topCustomers = getTopCustomersByDocCount(5);

$activeNav = 'raporlar';
$pageTitle = 'Raporlar';
$pageSubtitle = date('Y') . ' yılı performans özeti';
require __DIR__ . '/_layout_top.php';
?>

<div class="admin-kpi-grid">
  <?php foreach ($kpis as $k): ?>
    <div class="admin-kpi-card">
      <div class="admin-kpi-label"><?= htmlspecialchars($k['label']) ?></div>
      <div class="admin-kpi-value"><?= htmlspecialchars($k['value']) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="admin-content-grid" style="grid-template-columns:1.5fr 1fr;">
  <div class="admin-card">
    <div class="admin-card-title" style="margin-bottom:18px;">Aylık Belge Hacmi</div>
    <div class="admin-chart">
      <?php foreach ($trend as $m): ?>
        <div class="admin-chart-col">
          <div class="admin-chart-bar revenue" style="opacity:<?= $m['opacity'] ?>; height:<?= $m['height'] ?>px;"></div>
          <div class="admin-chart-label"><?= htmlspecialchars($m['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card-title" style="margin-bottom:16px;">Şablon Kategorisi Dağılımı</div>
    <?php if (empty($categoryBreakdown)): ?>
      <div class="admin-empty">Henüz veri yok.</div>
    <?php else: ?>
      <div class="admin-progress-list">
        <?php foreach ($categoryBreakdown as $c): ?>
          <div>
            <div class="admin-progress-head">
              <span><?= htmlspecialchars($c['name']) ?></span>
              <span class="admin-progress-pct">%<?= $c['pct'] ?></span>
            </div>
            <div class="admin-progress-track">
              <div class="admin-progress-fill" style="width:<?= $c['pct'] ?>%;"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-title" style="margin-bottom:16px;">En Çok Belge Oluşturan Müşteriler</div>
  <?php if (empty($topCustomers)): ?>
    <div class="admin-empty">Henüz veri yok.</div>
  <?php else: ?>
    <div class="admin-table-head admin-cols-topcustomers" style="grid-template-columns:2fr 1fr;">
      <div>Müşteri</div><div>Belge Sayısı</div>
    </div>
    <?php foreach ($topCustomers as $c): ?>
      <div class="admin-table-row admin-cols-topcustomers" style="grid-template-columns:2fr 1fr;">
        <div class="admin-cell-strong" style="font-variant-numeric:normal;"><?= htmlspecialchars($c['name']) ?></div>
        <div><?= $c['docCount'] ?></div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
