<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');

$urls = [
    ['loc' => SITE_URL . '/index.php', 'priority' => '1.0'],
    ['loc' => SITE_URL . '/giris.php', 'priority' => '0.5'],
];

foreach (['sozlesmeler', 'dilekceler', 'is-belgeleri', 'kisisel-belgeler'] as $catSlug) {
    $urls[] = ['loc' => SITE_URL . '/kategori.php?slug=' . urlencode($catSlug), 'priority' => '0.8'];
}

foreach (getAllTemplateConfigs() as $slug => $cfg) {
    $loc = (($cfg['kind'] ?? 'contract') === 'resume')
        ? SITE_URL . '/cv-olustur.php'
        : SITE_URL . '/sablon.php?slug=' . urlencode($slug);
    $urls[] = ['loc' => $loc, 'priority' => '0.7'];
}

$urls[] = ['loc' => SITE_URL . '/hesaplayicilar.php', 'priority' => '0.8'];
foreach (getAllCalculators() as $slug => $cfg) {
    $urls[] = ['loc' => SITE_URL . '/hesapla.php?slug=' . urlencode($slug), 'priority' => '0.7'];
}

$urls[] = ['loc' => SITE_URL . '/rehberler.php', 'priority' => '0.7'];
foreach (getAllGuides() as $slug => $cfg) {
    $urls[] = ['loc' => SITE_URL . '/rehber.php?slug=' . urlencode($slug), 'priority' => '0.6'];
}

foreach (['kullanim-sartlari.php', 'gizlilik-politikasi.php', 'kvkk-aydinlatma-metni.php'] as $legalPage) {
    $urls[] = ['loc' => SITE_URL . '/' . $legalPage, 'priority' => '0.3'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
  <url>
    <loc><?= htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') ?></loc>
    <priority><?= $url['priority'] ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
