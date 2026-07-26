<?php
/**
 * Expects (set by the including page before requiring this file):
 * $activeNav    string  one of: dashboard, belgeler, musteriler, odemeler, raporlar, ayarlar
 * $pageTitle    string  page heading shown top-right of the header
 * $pageSubtitle string  small subheading under the title
 * $adminUser    array   set by admin/_guard.php
 */

$__navItems = [
    'dashboard' => [
        'label' => 'Genel Bakış',
        'href' => 'index.php',
        'icon' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
    ],
    'belgeler' => [
        'label' => 'Belgeler',
        'href' => 'belgeler.php',
        'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/>',
    ],
    'musteriler' => [
        'label' => 'Müşteriler',
        'href' => 'musteriler.php',
        'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    ],
    'odemeler' => [
        'label' => 'Ödemeler',
        'href' => 'odemeler.php',
        'icon' => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
    ],
    'raporlar' => [
        'label' => 'Raporlar',
        'href' => 'raporlar.php',
        'icon' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
    ],
];
$__systemNavItems = [
    'ayarlar' => [
        'label' => 'Ayarlar',
        'href' => 'ayarlar.php',
        'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    ],
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?> | <?= htmlspecialchars(SITE_TITLE) ?> Yönetim</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="/assets/favicon.ico" sizes="any">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-sidebar">
  <a href="index.php" class="admin-brand">
    <span class="admin-brand-dot"></span>
    <span class="admin-brand-name">anındabelge</span>
  </a>

  <div class="admin-nav-group">
    <div class="admin-nav-label">Panel</div>
    <?php foreach ($__navItems as $key => $item): ?>
      <a href="<?= htmlspecialchars($item['href']) ?>" class="admin-nav-item<?= $activeNav === $key ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $item['icon'] ?></svg>
        <?= htmlspecialchars($item['label']) ?>
      </a>
    <?php endforeach; ?>

    <div class="admin-nav-label spaced">Sistem</div>
    <?php foreach ($__systemNavItems as $key => $item): ?>
      <a href="<?= htmlspecialchars($item['href']) ?>" class="admin-nav-item<?= $activeNav === $key ? ' active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $item['icon'] ?></svg>
        <?= htmlspecialchars($item['label']) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="admin-sidebar-user">
    <div class="admin-avatar-dark"><?= htmlspecialchars(adminInitials($adminUser['name'])) ?></div>
    <div class="admin-sidebar-user-info">
      <div class="admin-sidebar-user-name"><?= htmlspecialchars($adminUser['name']) ?></div>
      <div class="admin-sidebar-user-role">Yönetici</div>
    </div>
    <a href="../cikis.php" class="admin-sidebar-logout" title="Çıkış Yap">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="oklch(75% 0.008 240)" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
  </div>
</div>

<div class="admin-main">
  <div class="admin-header">
    <div class="admin-header-title">
      <h1><?= htmlspecialchars($pageTitle) ?></h1>
      <p><?= htmlspecialchars($pageSubtitle) ?></p>
    </div>
    <div class="admin-header-actions">
      <?php if ($activeNav === 'belgeler'): ?>
        <form class="admin-search-box" method="get" action="belgeler.php">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="oklch(50% 0.02 260)" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="q" placeholder="Belge no, müşteri ara…" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        </form>
      <?php else: ?>
        <div class="admin-search-box" aria-hidden="true">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="oklch(50% 0.02 260)" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <span style="font-size:13.5px; color:oklch(50% 0.02 260);">Belge no, müşteri ara…</span>
        </div>
      <?php endif; ?>
      <div class="admin-icon-btn">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="oklch(30% 0.03 260)" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      </div>
      <div class="admin-divider"></div>
      <div class="admin-user-chip">
        <div class="admin-avatar-navy"><?= htmlspecialchars(adminInitials($adminUser['name'])) ?></div>
        <div class="admin-user-chip-name"><?= htmlspecialchars($adminUser['name']) ?></div>
      </div>
    </div>
  </div>

  <?php if (!empty($_SESSION['flash_notice'])): ?>
    <div class="admin-form-success" style="margin:-8px 0 0;"><?= htmlspecialchars($_SESSION['flash_notice']) ?></div>
    <?php unset($_SESSION['flash_notice']); ?>
  <?php endif; ?>
