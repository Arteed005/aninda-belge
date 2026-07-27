<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
if (!$user) {
    header('Location: giris.php');
    exit;
}

$isPremium = !empty($user['is_premium']);
$persons = $isPremium ? getPersonsForUser($user['id']) : [];

$pageTitle = 'Kişilerim | ' . SITE_TITLE;
require __DIR__ . '/partials/_header.php';
?>

<main class="template-main">
  <div class="template-heading">
    <h1>Kişilerim</h1>
    <p>Sık kullandığın kişileri kaydet, belge oluştururken tek tıkla doldur.</p>
  </div>

  <?php if (!$isPremium): ?>
    <div class="category-empty">
      <p class="category-empty-title">Bu özellik Premium üyelere özel</p>
      <p class="category-empty-sub">Premium ile kayıtlı kişilerinizi tek tıkla belgelere doldurabilirsiniz.</p>
      <p class="category-empty-sub"><a href="premium.php" class="accent-link">Premium'a Geç →</a></p>
    </div>
  <?php else: ?>
    <div class="persons-toolbar">
      <button type="button" class="download-btn person-add-btn" data-open-person-modal>+ Yeni Kişi Ekle</button>
    </div>

    <?php if (empty($persons)): ?>
      <div class="category-empty">
        <p class="category-empty-title">Henüz kayıtlı kişin yok</p>
        <p class="category-empty-sub">"Yeni Kişi Ekle" ile ilk kişini kaydet.</p>
      </div>
    <?php else: ?>
      <div class="persons-grid">
        <?php foreach ($persons as $p): ?>
          <div class="person-card">
            <h3><?= htmlspecialchars($p['full_name']) ?></h3>
            <?php if ($p['tc_no']): ?><p class="person-card-line">TC: <?= htmlspecialchars($p['tc_no']) ?></p><?php endif; ?>
            <?php if ($p['phone']): ?><p class="person-card-line"><?= htmlspecialchars($p['phone']) ?></p><?php endif; ?>
            <?php if ($p['email']): ?><p class="person-card-line"><?= htmlspecialchars($p['email']) ?></p><?php endif; ?>
            <?php if ($p['address']): ?><p class="person-card-line person-card-address"><?= htmlspecialchars($p['address']) ?></p><?php endif; ?>
            <div class="person-card-actions">
              <button type="button" class="person-edit-btn"
                data-open-person-modal
                data-person-id="<?= $p['id'] ?>"
                data-person-name="<?= htmlspecialchars($p['full_name']) ?>"
                data-person-tc="<?= htmlspecialchars($p['tc_no'] ?? '') ?>"
                data-person-phone="<?= htmlspecialchars($p['phone'] ?? '') ?>"
                data-person-email="<?= htmlspecialchars($p['email'] ?? '') ?>"
                data-person-address="<?= htmlspecialchars($p['address'] ?? '') ?>">Düzenle</button>
              <form method="post" action="kisi-islem.php" onsubmit="return confirm('Bu kişiyi silmek istediğine emin misin?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="person-delete-btn">Sil</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="modal-overlay hidden" id="person-modal">
      <div class="modal-box person-modal-box">
        <button type="button" class="modal-close" id="person-modal-close">✕</button>
        <h3 id="person-modal-title">Yeni Kişi Ekle</h3>
        <form method="post" action="kisi-islem.php" class="field-list">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="action" id="person-form-action" value="create">
          <input type="hidden" name="id" id="person-form-id" value="">
          <div class="field">
            <label for="person-full-name">Ad Soyad</label>
            <input type="text" id="person-full-name" name="full_name" required placeholder="Örn: Ahmet Yılmaz">
          </div>
          <div class="field">
            <label for="person-tc-no">T.C. Kimlik No</label>
            <input type="text" id="person-tc-no" name="tc_no" placeholder="12345678901" maxlength="11">
          </div>
          <div class="field">
            <label for="person-phone">Telefon</label>
            <input type="text" id="person-phone" name="phone" placeholder="0555 123 45 67">
          </div>
          <div class="field">
            <label for="person-email">E-posta</label>
            <input type="text" id="person-email" name="email" placeholder="ornek@eposta.com">
          </div>
          <div class="field">
            <label for="person-address">Adres</label>
            <textarea id="person-address" name="address" rows="2" placeholder="Mahalle, sokak, no, ilçe/il"></textarea>
          </div>
          <button type="submit" class="download-btn" style="width:100%;">Kaydet</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</main>

<script src="/assets/js/persons.js"></script>

<?php require __DIR__ . '/partials/_footer.php'; ?>
