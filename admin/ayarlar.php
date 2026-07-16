<?php
require __DIR__ . '/_guard.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'change_password') {
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Geçersiz istek, lütfen sayfayı yenileyip tekrar deneyin.';
    } else {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['new_password_confirm'] ?? '');

        if (!verifyUserPassword($adminUser['id'], $current)) {
            $errors[] = 'Mevcut şifre hatalı.';
        }
        if (strlen($new) < 6) {
            $errors[] = 'Yeni şifre en az 6 karakter olmalı.';
        }
        if ($new !== $confirm) {
            $errors[] = 'Yeni şifreler eşleşmiyor.';
        }

        if (empty($errors)) {
            updateUserPassword($adminUser['id'], $new);
            $success = 'Şifreniz güncellendi.';
        }
    }
}

$activeNav = 'ayarlar';
$pageTitle = 'Ayarlar';
$pageSubtitle = 'Hesap ve şifre yönetimi';
require __DIR__ . '/_layout_top.php';
?>

<div class="admin-settings-grid">
  <div class="admin-profile-card">
    <div class="admin-avatar-lg"><?= htmlspecialchars(adminInitials($adminUser['name'])) ?></div>
    <div>
      <div class="admin-profile-name"><?= htmlspecialchars($adminUser['name']) ?></div>
      <div class="admin-profile-email"><?= htmlspecialchars($adminUser['email']) ?></div>
      <div class="admin-profile-role">Yönetici</div>
    </div>
  </div>

  <div style="display:flex; flex-direction:column; gap:20px;">
    <div class="admin-card">
      <div class="admin-card-title" style="margin-bottom:16px;">Şifremi Değiştir</div>

      <?php foreach ($errors as $error): ?>
        <div class="admin-form-error"><?= htmlspecialchars($error) ?></div>
      <?php endforeach; ?>
      <?php if ($success): ?>
        <div class="admin-form-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="post" action="ayarlar.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="form" value="change_password">
        <div class="admin-form-group">
          <label for="current_password">Mevcut Şifre</label>
          <input type="password" id="current_password" name="current_password" autocomplete="current-password">
        </div>
        <div class="admin-form-group">
          <label for="new_password">Yeni Şifre</label>
          <input type="password" id="new_password" name="new_password" autocomplete="new-password">
        </div>
        <div class="admin-form-group">
          <label for="new_password_confirm">Yeni Şifre (Tekrar)</label>
          <input type="password" id="new_password_confirm" name="new_password_confirm" autocomplete="new-password">
        </div>
        <button type="submit" class="admin-btn-primary">Şifreyi Güncelle</button>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
