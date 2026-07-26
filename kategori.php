<?php
require_once __DIR__ . '/bootstrap.php';

$categoryMeta = [
    'sozlesmeler' => [
        'label' => 'Sözleşmeler',
        'desc' => 'İhtiyacına uygun sözleşme şablonunu seç, bilgilerini doldur, PDF olarak indir.',
        'intro' => 'İki taraf arasındaki hak ve yükümlülükleri yazılı hale getirmek, ileride çıkabilecek anlaşmazlıkları önlemenin en basit yoludur. Kira, iş, alım-satım gibi günlük hayatta sık karşılaşılan sözleşme türlerini burada hazır şablon üzerinden birkaç dakikada oluşturabilirsin. Her şablon, ilgili konudaki temel maddeleri içerir; sen sadece taraf bilgilerini ve şartları doldurursun.',
        'searchPlaceholder' => 'Sözleşme şablonu ara...',
        'icon' => '<path d="M6 2.5h8l4 4V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1Z"></path><path d="M14 2.5V6.5a1 1 0 0 0 1 1H18.5"></path><line x1="7.5" y1="10" x2="15.5" y2="10"></line><line x1="7.5" y1="13" x2="15.5" y2="13"></line><path d="M7.5 16.7l1.6 1.6 3-3.6" stroke-width="1.8"></path>',
    ],
    'dilekceler' => [
        'label' => 'Dilekçeler',
        'desc' => 'İhtiyacına uygun dilekçe şablonunu seç, bilgilerini doldur, PDF olarak indir.',
        'intro' => 'İşyerine, bir kuruma ya da resmi makama sunacağın dilekçelerde doğru format ve ifade önemlidir. İstifa, izin talebi, itiraz gibi sık ihtiyaç duyulan dilekçe türlerini burada hazır şablonlarla, kişisel bilgilerini girerek dakikalar içinde hazırlayabilir, PDF olarak indirip imzalayabilirsin.',
        'searchPlaceholder' => 'Dilekçe şablonu ara...',
        'icon' => '<path d="M6 2.5h8l4 4V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1Z"></path><path d="M14 2.5V6.5a1 1 0 0 0 1 1H18.5"></path><line x1="7.5" y1="10" x2="13.5" y2="10"></line><path d="M9 18.7l.4-2 6-6 1.6 1.6-6 6-2 .4Z"></path>',
    ],
    'is-belgeleri' => [
        'label' => 'İş Belgeleri',
        'desc' => 'İhtiyacına uygun iş belgesi şablonunu seç, bilgilerini doldur, PDF olarak indir.',
        'intro' => 'Çalışma hayatı boyunca ihtiyaç duyulan özgeçmiş, ibraname, referans mektubu gibi belgeleri burada bulabilirsin. Özellikle işe başlarken ve işten ayrılırken istenen evrakların çoğu bu kategoride — doğru formatta, eksiksiz hazırlamak süreci hızlandırır.',
        'searchPlaceholder' => 'İş belgesi şablonu ara...',
        'icon' => '<rect x="3" y="8" width="18" height="12" rx="2"></rect><path d="M8.5 8V6a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v2"></path><line x1="3" y1="13" x2="21" y2="13"></line><line x1="10.3" y1="13" x2="13.7" y2="13" stroke-width="2.2"></line>',
    ],
    'kisisel-belgeler' => [
        'label' => 'Kişisel Belgeler',
        'desc' => 'İhtiyacına uygun kişisel belge şablonunu seç, bilgilerini doldur, PDF olarak indir.',
        'intro' => 'Vekaletname, muvafakatname, taahhütname gibi belgeler günlük hayatta bir başkasına yetki vermek, izin belirtmek ya da bir konuda söz vermek için kullanılır. Bu belgeler genellikle noter huzurunda düzenlenir; burada hazırladığın metin, notere gitmeden önce içeriği netleştirmen için hazır bir taslak sunar.',
        'searchPlaceholder' => 'Kişisel belge şablonu ara...',
        'icon' => '<rect x="2.5" y="5" width="19" height="14" rx="2.2"></rect><circle cx="8" cy="12" r="2.2"></circle><line x1="13" y1="10" x2="18.5" y2="10"></line><line x1="13" y1="13" x2="18.5" y2="13"></line><line x1="5.5" y1="16.3" x2="10.5" y2="16.3"></line>',
    ],
];

$slug = $_GET['slug'] ?? '';
$meta = $categoryMeta[$slug] ?? null;

if ($meta === null) {
    http_response_code(404);
    $pageTitle = 'Kategori bulunamadı | ' . SITE_TITLE;
    require __DIR__ . '/partials/_header.php';
    echo '<main class="template-main"><h1>Kategori bulunamadı</h1><p><a href="index.php">Ana sayfaya dön</a></p></main>';
    require __DIR__ . '/partials/_footer.php';
    exit;
}

$items = [];
foreach (getAllTemplateConfigs() as $cfgSlug => $cfg) {
    if (($cfg['category'] ?? '') === $slug) {
        $items[] = [
            'slug' => $cfgSlug,
            'title' => $cfg['title'] ?? $cfgSlug,
            'description' => $cfg['description'] ?? '',
            'meta' => $cfg['meta'] ?? '',
            'href' => (($cfg['kind'] ?? 'contract') === 'resume') ? 'cv-olustur.php' : 'sablon.php?slug=' . $cfgSlug,
        ];
    }
}
usort($items, fn($a, $b) => strcmp($a['title'], $b['title']));

$pageTitle = $meta['label'] . ' | ' . SITE_TITLE;
$pageDescription = $meta['desc'];
require __DIR__ . '/partials/_header.php';
?>

<div class="breadcrumb">
  <a href="index.php">Ana Sayfa</a><span>&rsaquo;</span>
  <span class="current"><?= htmlspecialchars($meta['label']) ?></span>
</div>

<main class="category-main">
  <div class="category-heading">
    <h1><?= htmlspecialchars($meta['label']) ?></h1>
    <p><?= htmlspecialchars($meta['desc']) ?></p>
    <?php if (!empty($meta['intro'])): ?>
      <p class="category-intro"><?= htmlspecialchars($meta['intro']) ?></p>
    <?php endif; ?>
  </div>

  <div class="category-search">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5b6b82" stroke-width="2" stroke-linecap="round">
      <circle cx="11" cy="11" r="7"></circle>
      <line x1="21" y1="21" x2="16.2" y2="16.2"></line>
    </svg>
    <input type="text" id="category-search-input" placeholder="<?= htmlspecialchars($meta['searchPlaceholder']) ?>" autocomplete="off">
  </div>

  <?php if (empty($items)): ?>
    <div class="category-empty">
      <p class="category-empty-title">Bu kategoride henüz şablon yok</p>
      <p class="category-empty-sub">Yakında eklenecek, takipte kal.</p>
    </div>
  <?php else: ?>
    <div id="category-grid" class="category-grid">
      <?php foreach ($items as $item): ?>
        <div class="category-card"
          data-name="<?= htmlspecialchars(mb_strtolower($item['title'], 'UTF-8')) ?>"
          data-desc="<?= htmlspecialchars(mb_strtolower($item['description'], 'UTF-8')) ?>">
          <div class="category-card-icon">
            <svg viewBox="0 0 24 24" width="25" height="25" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><?= $meta['icon'] ?></svg>
          </div>
          <h3><?= htmlspecialchars($item['title']) ?></h3>
          <p><?= htmlspecialchars($item['description']) ?></p>
          <div class="category-card-footer">
            <span class="category-card-meta"><?= htmlspecialchars($item['meta']) ?></span>
            <a href="<?= htmlspecialchars($item['href']) ?>" class="category-card-btn">Kullan</a>
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
