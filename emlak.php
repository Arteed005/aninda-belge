<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
if (!$user) {
    header('Location: giris.php');
    exit;
}

$isEmlak = !empty($user['is_emlak']);

$pageTitle = 'Emlak Çalışma Alanı | ' . SITE_TITLE;
require __DIR__ . '/partials/_header.php';
?>

<main class="template-main">
  <div class="package-hero package-hero-emlak">
    <div class="package-hero-icon">🏠</div>
    <div class="package-hero-body">
      <div class="package-hero-label">EMLAK PAKETİ</div>
      <h1>Emlak Çalışma Alanı</h1>
      <p>Kişilerini ve taşınmazlarını kaydet, kira sözleşmesini saniyeler içinde doldur.</p>
    </div>
  </div>

  <?php if (!$isEmlak): ?>
    <div class="category-empty">
      <p class="category-empty-title">Bu özellik Emlak paketine özel</p>
      <p class="category-empty-sub">Emlak paketiyle taşınmaz bilgilerini bir kere kaydedip her kira sözleşmesinde tek tıkla kullanabilirsin. Premium'un tüm avantajları da dahildir.</p>
      <p class="category-empty-sub"><a href="premium.php#emlak" class="accent-link">Emlak Paketine Geç →</a></p>
    </div>
  <?php else: ?>
    <?php
    $todayDocs = getTodayDocumentCountForUser($user['id']);
    $propertyCount = getPropertyCountForUser($user['id']);
    $personCount = getPersonCountForUser($user['id']);
    $recentDocs = getDocumentsForUser($user['id'], 1, 5);
    $recentProperties = getRecentPropertiesForUser($user['id'], 5);
    $recentPersons = getRecentPersonsForUser($user['id'], 5);
    ?>

    <div class="emlak-kpi-grid">
      <div class="emlak-kpi-card">
        <div class="emlak-kpi-label">Bugün Oluşturulan Belge</div>
        <div class="emlak-kpi-value"><?= (int) $todayDocs ?></div>
      </div>
      <div class="emlak-kpi-card">
        <div class="emlak-kpi-label">Toplam Kayıtlı Taşınmaz</div>
        <div class="emlak-kpi-value"><?= (int) $propertyCount ?></div>
      </div>
      <div class="emlak-kpi-card">
        <div class="emlak-kpi-label">Toplam Kayıtlı Kişi</div>
        <div class="emlak-kpi-value"><?= (int) $personCount ?></div>
      </div>
    </div>

    <h2 class="emlak-section-title">Hızlı İşlemler</h2>
    <div class="emlak-quick-actions">
      <a href="sablon.php?slug=kira-sozlesmesi" class="emlak-quick-action-btn">+ Yeni Kira Sözleşmesi</a>
      <span class="emlak-quick-action-btn disabled">+ Yeni Ev Teslim Tutanağı <span class="emlak-soon-badge">Yakında</span></span>
      <span class="emlak-quick-action-btn disabled">+ Yeni Tahliye Taahhütnamesi <span class="emlak-soon-badge">Yakında</span></span>
      <span class="emlak-quick-action-btn disabled">+ Yeni Depozito Tutanağı <span class="emlak-soon-badge">Yakında</span></span>
    </div>

    <div class="emlak-content-grid">
      <div class="emlak-panel">
        <div class="emlak-panel-head">
          <h2>Son Kullanılan Belgeler</h2>
          <a href="belgelerim.php" class="accent-link">Tümü →</a>
        </div>
        <?php if (empty($recentDocs)): ?>
          <p class="category-empty-sub">Henüz belge oluşturmadın.</p>
        <?php else: ?>
          <ul class="emlak-mini-list">
            <?php foreach ($recentDocs as $doc): ?>
              <li><span><?= htmlspecialchars($doc['type']) ?></span><span class="emlak-mini-list-meta"><?= htmlspecialchars($doc['date']) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div class="emlak-panel">
        <div class="emlak-panel-head">
          <h2>Son Eklenen Taşınmazlar</h2>
          <a href="emlak-tasinmazlar.php" class="accent-link">Tümü →</a>
        </div>
        <?php if (empty($recentProperties)): ?>
          <p class="category-empty-sub">Henüz taşınmaz eklemedin.</p>
        <?php else: ?>
          <ul class="emlak-mini-list">
            <?php foreach ($recentProperties as $prop): ?>
              <li><span><?= htmlspecialchars($prop['title']) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div class="emlak-panel">
        <div class="emlak-panel-head">
          <h2>Son Eklenen Kişiler</h2>
          <a href="kisilerim.php" class="accent-link">Tümü →</a>
        </div>
        <?php if (empty($recentPersons)): ?>
          <p class="category-empty-sub">Henüz kişi eklemedin.</p>
        <?php else: ?>
          <ul class="emlak-mini-list">
            <?php foreach ($recentPersons as $p): ?>
              <li><span><?= htmlspecialchars($p['full_name']) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="emlak-panel-links">
      <a href="emlak-tasinmazlar.php" class="btn-ghost">Taşınmazlarım</a>
      <a href="kisilerim.php" class="btn-ghost">Kişilerim</a>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
