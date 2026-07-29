<?php
require __DIR__ . '/_guard.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$total = getCustomerCount();
$totalPages = max(1, (int) ceil($total / ADMIN_CUSTOMERS_PER_PAGE));
$page = min($page, $totalPages);
$customers = getCustomersPaginated($page);
$kpis = getCustomerKpis();

$activeNav = 'musteriler';
$pageTitle = 'Müşteriler';
$pageSubtitle = number_format($total, 0, ',', '.') . ' kayıtlı müşteri';
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
  <div class="admin-card-title" style="margin-bottom:16px;">Tüm Müşteriler</div>
  <?php if (empty($customers)): ?>
    <div class="admin-empty">Henüz müşteri yok.</div>
  <?php else: ?>
    <div class="admin-table-head admin-cols-customers">
      <div>Ad Soyad</div><div>E-posta</div><div>Kayıt</div><div>Belge</div><div>Premium</div><div>Durum</div><div></div>
    </div>
    <?php foreach ($customers as $c): ?>
      <div class="admin-table-row admin-cols-customers">
        <div class="admin-person">
          <div class="admin-avatar-sm admin-avatar-xs" style="background:<?= htmlspecialchars($c['avatarBg']) ?>;"><?= htmlspecialchars($c['initials']) ?></div>
          <span class="admin-cell-strong" style="font-variant-numeric:normal;"><?= htmlspecialchars($c['name']) ?></span>
        </div>
        <div class="admin-cell-truncate admin-cell-muted"><?= htmlspecialchars($c['email']) ?></div>
        <div class="admin-cell-date"><?= htmlspecialchars($c['since']) ?></div>
        <div><?= $c['docCount'] ?></div>
        <div>
          <?php if ($c['isPremium']): ?>
            <span class="admin-badge admin-badge-success">Premium<?= $c['premiumExpiresAt'] ? ' — ' . htmlspecialchars($c['premiumExpiresAt']) . "'e kadar" : '' ?></span>
          <?php else: ?>
            <span class="admin-badge admin-badge-neutral">Ücretsiz</span>
          <?php endif; ?>
          <?php if ($c['isEmlak']): ?>
            <span class="admin-badge admin-badge-success">Emlak</span>
          <?php endif; ?>
        </div>
        <div>
          <?php if ($c['isVerified']): ?>
            <span class="admin-badge admin-badge-success">Doğrulandı</span>
          <?php else: ?>
            <span class="admin-badge admin-badge-warning">Bekliyor</span>
          <?php endif; ?>
        </div>
        <div class="admin-row-action">
          <button type="button" class="admin-row-action-trigger">⋯</button>
          <div class="admin-row-action-menu">
            <form method="post" action="musteri-islem.php">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= $c['id'] ?>">
              <input type="hidden" name="action" value="toggle_premium">
              <input type="hidden" name="return" value="musteriler.php?page=<?= $page ?>">
              <button type="submit"><?= $c['isPremium'] ? 'Premium Kaldır' : 'Premium Yap' ?></button>
            </form>
            <form method="post" action="musteri-islem.php">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= $c['id'] ?>">
              <input type="hidden" name="action" value="toggle_emlak">
              <input type="hidden" name="return" value="musteriler.php?page=<?= $page ?>">
              <button type="submit"><?= $c['isEmlak'] ? 'Emlak Kaldır' : 'Emlak Yap' ?></button>
            </form>
            <form method="post" action="musteri-islem.php" data-confirm="<?= htmlspecialchars($c['name']) ?> kalıcı olarak silinecek. Emin misiniz?">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= $c['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="return" value="musteriler.php?page=<?= $page ?>">
              <button type="submit" class="danger">Sil</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="admin-pagination">
      <div><?= (($page - 1) * ADMIN_CUSTOMERS_PER_PAGE) + 1 ?>–<?= min($page * ADMIN_CUSTOMERS_PER_PAGE, $total) ?> / <?= number_format($total, 0, ',', '.') ?> müşteri</div>
      <div class="admin-pagination-links">
        <?php if ($page > 1): ?>
          <a href="musteriler.php?page=<?= $page - 1 ?>">Önceki</a>
        <?php else: ?>
          <span class="disabled">Önceki</span>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
          <a href="musteriler.php?page=<?= $page + 1 ?>">Sonraki</a>
        <?php else: ?>
          <span class="disabled">Sonraki</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
