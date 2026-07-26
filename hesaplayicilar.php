<?php
require_once __DIR__ . '/bootstrap.php';

$icon = '<circle cx="12" cy="12" r="9.5"></circle><line x1="8" y1="9" x2="16" y2="9"></line><line x1="8" y1="13" x2="10.2" y2="13"></line><line x1="13.8" y1="13" x2="16" y2="13"></line><line x1="8" y1="16.5" x2="10.2" y2="16.5"></line><line x1="13.8" y1="16.5" x2="16" y2="16.5"></line>';

$items = [];
foreach (getAllCalculators() as $cfgSlug => $cfg) {
    $items[] = [
        'slug' => $cfgSlug,
        'title' => $cfg['title'] ?? $cfgSlug,
        'description' => $cfg['description'] ?? '',
        'meta' => $cfg['meta'] ?? '',
        'href' => 'hesapla.php?slug=' . $cfgSlug,
    ];
}
usort($items, fn($a, $b) => strcmp($a['title'], $b['title']));

$pageTitle = 'Hesaplayıcılar | ' . SITE_TITLE;
$pageDescription = 'Kıdem tazminatı, ihbar süresi, yıllık izin gibi hesaplamaları ücretsiz ve anında yap.';
require __DIR__ . '/partials/_header.php';
?>

<div class="breadcrumb">
  <a href="index.php">Ana Sayfa</a><span>&rsaquo;</span>
  <span class="current">Hesaplayıcılar</span>
</div>

<main class="category-main">
  <div class="category-heading">
    <h1>Hesaplayıcılar</h1>
    <p>Kıdem tazminatı, ihbar süresi, yıllık izin gibi hesaplamaları ücretsiz ve anında yap.</p>
    <p class="category-intro">İş hayatına dair hakların rakamla ifade edilmesi gereken durumlar için — belge oluşturmadan önce ya da bağımsız olarak, sadece birkaç bilgiyle sonucu hemen gör.</p>
  </div>

  <div class="category-search">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5b6b82" stroke-width="2" stroke-linecap="round">
      <circle cx="11" cy="11" r="7"></circle>
      <line x1="21" y1="21" x2="16.2" y2="16.2"></line>
    </svg>
    <input type="text" id="category-search-input" placeholder="Hesaplayıcı ara..." autocomplete="off">
  </div>

  <?php if (empty($items)): ?>
    <div class="category-empty">
      <p class="category-empty-title">Henüz hesaplayıcı yok</p>
      <p class="category-empty-sub">Yakında eklenecek, takipte kal.</p>
    </div>
  <?php else: ?>
    <div id="category-grid" class="category-grid">
      <?php foreach ($items as $item): ?>
        <div class="category-card"
          data-name="<?= htmlspecialchars(mb_strtolower($item['title'], 'UTF-8')) ?>"
          data-desc="<?= htmlspecialchars(mb_strtolower($item['description'], 'UTF-8')) ?>">
          <div class="category-card-icon">
            <svg viewBox="0 0 24 24" width="25" height="25" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><?= $icon ?></svg>
          </div>
          <h3><?= htmlspecialchars($item['title']) ?></h3>
          <p><?= htmlspecialchars($item['description']) ?></p>
          <div class="category-card-footer">
            <span class="category-card-meta"><?= htmlspecialchars($item['meta']) ?></span>
            <a href="<?= htmlspecialchars($item['href']) ?>" class="category-card-btn">Hesapla</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div id="category-empty" class="category-empty" style="display:none">
      <p class="category-empty-title">Sonuç bulunamadı</p>
      <p class="category-empty-sub" id="category-empty-query"></p>
    </div>
  <?php endif; ?>
</main>

<script src="/assets/js/category.js"></script>

<?php require __DIR__ . '/partials/_footer.php'; ?>
