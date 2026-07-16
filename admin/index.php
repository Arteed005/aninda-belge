<?php
require __DIR__ . '/_guard.php';

$stats = getDashboardStats();
$trend = getMonthlyDocumentTrend(12);
$recentDocs = getRecentDocumentsAdmin(8);
$docTypes = getDocTypeBreakdown(5);
$recentCustomers = getRecentCustomers(5);

$activeNav = 'dashboard';
$pageTitle = 'Genel Bakış';
$pageSubtitle = turkishDateLong(new DateTime());
require __DIR__ . '/_layout_top.php';
?>

<div class="admin-kpi-grid">
  <div class="admin-kpi-card">
    <div class="admin-kpi-label">Toplam Belge</div>
    <div class="admin-kpi-value"><?= number_format($stats['totalDocuments'], 0, ',', '.') ?></div>
  </div>
  <div class="admin-kpi-card">
    <div class="admin-kpi-label">Aktif Müşteri</div>
    <div class="admin-kpi-value"><?= number_format($stats['activeCustomers'], 0, ',', '.') ?></div>
  </div>
  <div class="admin-kpi-card">
    <div class="admin-kpi-label">Premium Kullanıcı</div>
    <div class="admin-kpi-value"><?= number_format($stats['premiumUsers'], 0, ',', '.') ?></div>
  </div>
  <div class="admin-kpi-card">
    <div class="admin-kpi-label">Doğrulanmamış Hesap</div>
    <div class="admin-kpi-value"><?= number_format($stats['unverifiedUsers'], 0, ',', '.') ?></div>
  </div>
</div>

<div class="admin-content-grid">
  <div class="admin-col">

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">Aylık Belge Hacmi</div>
        <div class="admin-card-sub">Son 12 ay</div>
      </div>
      <div class="admin-chart">
        <?php foreach ($trend as $m): ?>
          <div class="admin-chart-col">
            <div class="admin-chart-bar" style="opacity:<?= $m['opacity'] ?>; height:<?= $m['height'] ?>px;"></div>
            <div class="admin-chart-label"><?= htmlspecialchars($m['label']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">Son Belgeler</div>
        <a href="belgeler.php" class="admin-card-link">Tümünü gör</a>
      </div>
      <?php if (empty($recentDocs)): ?>
        <div class="admin-empty">Henüz belge oluşturulmamış.</div>
      <?php else: ?>
        <div class="admin-table-head admin-cols-docs-compact">
          <div>Belge No</div><div>Müşteri</div><div>Tür</div><div>Tarih</div>
        </div>
        <?php foreach ($recentDocs as $doc): ?>
          <div class="admin-table-row admin-cols-docs-compact">
            <div class="admin-cell-strong"><?= htmlspecialchars($doc['no']) ?></div>
            <div class="admin-cell-truncate"><?= htmlspecialchars($doc['customer']) ?></div>
            <div class="admin-cell-muted"><?= htmlspecialchars($doc['type']) ?></div>
            <div class="admin-cell-date"><?= htmlspecialchars($doc['date']) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>

  <div class="admin-col">

    <div class="admin-card compact">
      <div class="admin-card-title" style="margin-bottom:16px;">Belge Türü Dağılımı</div>
      <?php if (empty($docTypes)): ?>
        <div class="admin-empty">Henüz veri yok.</div>
      <?php else: ?>
        <div class="admin-progress-list">
          <?php foreach ($docTypes as $t): ?>
            <div>
              <div class="admin-progress-head">
                <span><?= htmlspecialchars($t['name']) ?></span>
                <span class="admin-progress-pct">%<?= $t['pct'] ?></span>
              </div>
              <div class="admin-progress-track">
                <div class="admin-progress-fill" style="width:<?= $t['pct'] ?>%;"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="admin-card compact">
      <div class="admin-card-title" style="margin-bottom:14px;">Son Kayıt Olan Müşteriler</div>
      <?php if (empty($recentCustomers)): ?>
        <div class="admin-empty">Henüz müşteri yok.</div>
      <?php else: ?>
        <div class="admin-person-list">
          <?php foreach ($recentCustomers as $c): ?>
            <div class="admin-person-row">
              <div class="admin-avatar-sm" style="background:<?= htmlspecialchars($c['avatarBg']) ?>;"><?= htmlspecialchars($c['initials']) ?></div>
              <div style="flex:1; min-width:0;">
                <div class="admin-person-name"><?= htmlspecialchars($c['name']) ?></div>
                <div class="admin-person-email"><?= htmlspecialchars($c['email']) ?></div>
              </div>
              <div class="admin-person-meta"><?= $c['docCount'] ?> belge</div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="admin-card compact admin-quick-actions">
      <div class="admin-card-title" style="margin-bottom:2px;">Hızlı İşlemler</div>
      <a href="belgeler.php" class="admin-btn-primary">Tüm Belgeleri Gör</a>
      <a href="musteriler.php" class="admin-btn-secondary">Tüm Müşterileri Gör</a>
    </div>

  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
