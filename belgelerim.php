<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
if (!$user) {
    header('Location: giris.php');
    exit;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = ADMIN_DOCS_PER_PAGE;
$total = getDocumentCountForUser($user['id']);
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$documents = getDocumentsForUser($user['id'], $page, $perPage);

$pageTitle = 'Belgelerim | ' . SITE_TITLE;
require __DIR__ . '/partials/_header.php';
?>

<main class="template-main">
  <div class="template-heading">
    <h1>Belgelerim</h1>
    <p>Daha önce oluşturduğun belgeleri buradan tekrar indirebilirsin. Belgeler <?= (int) RETENTION_DAYS_DEFAULT ?> gün sonra otomatik silinir.</p>
  </div>

  <?php if (empty($documents)): ?>
    <div class="category-empty">
      <p class="category-empty-title">Henüz hiç belge oluşturmadın</p>
      <p class="category-empty-sub"><a href="index.php" class="accent-link">Bir şablon seçip başla</a></p>
    </div>
  <?php else: ?>
    <div class="doc-list">
      <?php foreach ($documents as $doc): ?>
        <div class="doc-row">
          <div class="doc-row-main">
            <span class="doc-no"><?= htmlspecialchars($doc['no']) ?></span>
            <span class="doc-type"><?= htmlspecialchars($doc['type']) ?></span>
          </div>
          <div class="doc-row-meta">
            <span class="doc-badge <?= $doc['isExpired'] ? 'doc-badge-neutral' : 'doc-badge-success' ?>"><?= htmlspecialchars($doc['status']) ?></span>
            <span class="doc-date"><?= htmlspecialchars($doc['date']) ?></span>
          </div>
          <div class="doc-row-action">
            <?php if ($doc['isExpired']): ?>
              <span class="doc-expired-hint">Süresi doldu</span>
            <?php else: ?>
              <a href="belge-indir.php?id=<?= $doc['id'] ?>" class="doc-download-btn">İndir</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="doc-pagination">
        <div><?= (($page - 1) * $perPage) + 1 ?>–<?= min($page * $perPage, $total) ?> / <?= number_format($total, 0, ',', '.') ?> belge</div>
        <div class="doc-pagination-links">
          <?php if ($page > 1): ?>
            <a href="belgelerim.php?page=<?= $page - 1 ?>">Önceki</a>
          <?php else: ?>
            <span class="disabled">Önceki</span>
          <?php endif; ?>
          <?php if ($page < $totalPages): ?>
            <a href="belgelerim.php?page=<?= $page + 1 ?>">Sonraki</a>
          <?php else: ?>
            <span class="disabled">Sonraki</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
