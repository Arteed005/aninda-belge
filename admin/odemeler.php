<?php
require __DIR__ . '/_guard.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$total = getShopierPaymentCount();
$totalPages = max(1, (int) ceil($total / ADMIN_PAYMENTS_PER_PAGE));
$page = min($page, $totalPages);
$payments = getShopierPaymentsPaginated($page);
$kpis = getShopierPaymentKpis();
$trend = getMonthlyPaymentTrend(6);

$activeNav = 'odemeler';
$pageTitle = 'Ödemeler';
$pageSubtitle = number_format($total, 0, ',', '.') . ' onaylanmış Shopier ödemesi';
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

<div class="admin-card">
  <div class="admin-card-title" style="margin-bottom:18px;">Aylık Ödeme Adedi</div>
  <div class="admin-chart">
    <?php foreach ($trend as $m): ?>
      <div class="admin-chart-col">
        <div class="admin-chart-bar revenue" style="opacity:<?= $m['opacity'] ?>; height:<?= max(4, $m['height']) ?>px;" title="<?= $m['value'] ?> ödeme"></div>
        <div class="admin-chart-label"><?= htmlspecialchars($m['label']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-title" style="margin-bottom:16px;">Shopier Premium Ödemeleri</div>
  <?php if (empty($payments)): ?>
    <div class="admin-empty">Henüz ödeme yok.</div>
  <?php else: ?>
    <div class="admin-table-head admin-cols-payments">
      <div>Sipariş No</div><div>Müşteri</div><div>E-posta</div><div>Paket</div><div>Tarih</div>
    </div>
    <?php foreach ($payments as $p): ?>
      <div class="admin-table-row admin-cols-payments">
        <div class="admin-cell-strong"><?= htmlspecialchars($p['orderId']) ?></div>
        <div><?= htmlspecialchars($p['name']) ?></div>
        <div class="admin-cell-truncate admin-cell-muted"><?= htmlspecialchars($p['email']) ?></div>
        <div><?= htmlspecialchars($p['package']) ?></div>
        <div class="admin-cell-date"><?= htmlspecialchars($p['date']) ?></div>
      </div>
    <?php endforeach; ?>

    <div class="admin-pagination">
      <div><?= (($page - 1) * ADMIN_PAYMENTS_PER_PAGE) + 1 ?>–<?= min($page * ADMIN_PAYMENTS_PER_PAGE, $total) ?> / <?= number_format($total, 0, ',', '.') ?> ödeme</div>
      <div class="admin-pagination-links">
        <?php if ($page > 1): ?>
          <a href="odemeler.php?page=<?= $page - 1 ?>">Önceki</a>
        <?php else: ?>
          <span class="disabled">Önceki</span>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
          <a href="odemeler.php?page=<?= $page + 1 ?>">Sonraki</a>
        <?php else: ?>
          <span class="disabled">Sonraki</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<p style="font-size:13px; color:oklch(50% 0.02 260); margin-top:16px;">
  Gelir rakamları, Premium için ₺<?= PREMIUM_PRICE_TRY ?>, Emlak paketi için ₺<?= EMLAK_PRICE_TRY ?> sabit fiyat üzerinden tahmini olarak hesaplanıyor (gerçek tutar/ödeme yöntemi kaydı tutulmuyor). Kesin tutarlar için Shopier panelindeki "Siparişler" ve "Tahsilatlar" sekmelerine bakabilirsin.
</p>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
