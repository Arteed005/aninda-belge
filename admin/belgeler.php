<?php
require __DIR__ . '/_guard.php';

$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, ['all', 'active', 'expired', 'watermarked'], true)) {
    $filter = 'all';
}
$search = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));

$total = getDocumentCount($filter, $search);
$totalPages = max(1, (int) ceil($total / ADMIN_DOCS_PER_PAGE));
$page = min($page, $totalPages);
$documents = getDocumentsPaginated($filter, $search, $page);

$filters = [
    'all' => 'Tümü',
    'active' => 'Aktif',
    'expired' => 'Süresi Dolmuş',
    'watermarked' => 'Filigranlı',
];

function admFilterUrl(string $filter, string $search): string
{
    $params = ['filter' => $filter];
    if ($search !== '') {
        $params['q'] = $search;
    }
    return 'belgeler.php?' . http_build_query($params);
}

function admPageUrl(string $filter, string $search, int $page): string
{
    $params = ['filter' => $filter, 'page' => $page];
    if ($search !== '') {
        $params['q'] = $search;
    }
    return 'belgeler.php?' . http_build_query($params);
}

$activeNav = 'belgeler';
$pageTitle = 'Belgeler';
$pageSubtitle = number_format($total, 0, ',', '.') . ' belge' . ($search !== '' ? ' · "' . $search . '" için sonuçlar' : '');
require __DIR__ . '/_layout_top.php';
?>

<div class="admin-toolbar">
  <div class="admin-filters">
    <?php foreach ($filters as $key => $label): ?>
      <a href="<?= htmlspecialchars(admFilterUrl($key, $search)) ?>" class="admin-filter-pill<?= $filter === $key ? ' active' : '' ?>"><?= htmlspecialchars($label) ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="admin-card">
  <?php if (empty($documents)): ?>
    <div class="admin-empty">Kriterlere uyan belge bulunamadı.</div>
  <?php else: ?>
    <div class="admin-table-head admin-cols-docs">
      <div>Belge No</div><div>Müşteri</div><div>Tür</div><div>Durum</div><div>Oluşturulma</div><div></div>
    </div>
    <?php foreach ($documents as $doc): ?>
      <div class="admin-table-row admin-cols-docs">
        <div class="admin-cell-strong"><?= htmlspecialchars($doc['no']) ?></div>
        <div class="admin-cell-truncate">
          <?= htmlspecialchars($doc['customer']) ?>
          <?php if ($doc['isWatermarked']): ?><span class="admin-badge admin-badge-neutral" style="margin-left:6px;">Filigranlı</span><?php endif; ?>
        </div>
        <div class="admin-cell-muted"><?= htmlspecialchars($doc['type']) ?></div>
        <div>
          <span class="admin-badge <?= $doc['isExpired'] ? 'admin-badge-neutral' : 'admin-badge-success' ?>"><?= htmlspecialchars($doc['status']) ?></span>
        </div>
        <div class="admin-cell-date"><?= htmlspecialchars($doc['date']) ?></div>
        <div class="admin-row-action">
          <button type="button" class="admin-row-action-trigger">⋯</button>
          <div class="admin-row-action-menu">
            <a href="belge-goster.php?id=<?= $doc['id'] ?>" target="_blank" rel="noopener">Görüntüle</a>
            <form method="post" action="belge-sil.php" data-confirm="<?= htmlspecialchars($doc['no']) ?> numaralı belge kalıcı olarak silinecek. Emin misiniz?">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= $doc['id'] ?>">
              <input type="hidden" name="return" value="<?= htmlspecialchars(admPageUrl($filter, $search, $page)) ?>">
              <button type="submit" class="danger">Sil</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="admin-pagination">
      <div><?= (($page - 1) * ADMIN_DOCS_PER_PAGE) + 1 ?>–<?= min($page * ADMIN_DOCS_PER_PAGE, $total) ?> / <?= number_format($total, 0, ',', '.') ?> belge</div>
      <div class="admin-pagination-links">
        <?php if ($page > 1): ?>
          <a href="<?= htmlspecialchars(admPageUrl($filter, $search, $page - 1)) ?>">Önceki</a>
        <?php else: ?>
          <span class="disabled">Önceki</span>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
          <a href="<?= htmlspecialchars(admPageUrl($filter, $search, $page + 1)) ?>">Sonraki</a>
        <?php else: ?>
          <span class="disabled">Sonraki</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
