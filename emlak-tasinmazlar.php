<?php
require_once __DIR__ . '/bootstrap.php';

$user = currentUser();
if (!$user) {
    header('Location: giris.php');
    exit;
}

$isEmlak = !empty($user['is_emlak']);
$properties = [];
$allPersons = [];
$ownersByProperty = [];
if ($isEmlak) {
    $properties = getPropertiesForUser($user['id']);
    $allPersons = PERSONS_FEATURE_ENABLED ? getPersonsForUser($user['id']) : [];
    foreach ($properties as $prop) {
        $ownersByProperty[$prop['id']] = PERSONS_FEATURE_ENABLED ? getPropertyOwners((int) $prop['id']) : [];
    }
}

$pageTitle = 'Taşınmazlarım | ' . SITE_TITLE;
require __DIR__ . '/partials/_header.php';
?>

<main class="template-main">
  <div class="template-heading">
    <h1>Taşınmazlarım</h1>
    <p>Kiraya verdiğin/sattığın taşınmazları kaydet, belge oluştururken tek tıkla doldur.</p>
  </div>

  <?php if (!$isEmlak): ?>
    <div class="category-empty">
      <p class="category-empty-title">Bu özellik Emlak paketine özel</p>
      <p class="category-empty-sub">Emlak paketiyle kayıtlı taşınmazlarınızı tek tıkla belgelere doldurabilirsiniz.</p>
      <p class="category-empty-sub"><a href="premium.php#emlak" class="accent-link">Emlak Paketine Geç →</a></p>
    </div>
  <?php else: ?>
    <div class="persons-toolbar">
      <a href="emlak.php" class="btn-ghost">← Emlak Çalışma Alanı</a>
      <button type="button" class="download-btn person-add-btn" data-open-property-modal>+ Yeni Taşınmaz Ekle</button>
    </div>

    <?php if (PERSONS_FEATURE_ENABLED && empty($allPersons)): ?>
      <div class="category-empty-sub" style="margin-bottom:16px;">İpucu: önce <a href="kisilerim.php" class="accent-link">Kişilerim</a>'e sahip bilgilerini eklersen, taşınmaza sahip atayabilirsin.</div>
    <?php endif; ?>

    <?php if (empty($properties)): ?>
      <div class="category-empty">
        <p class="category-empty-title">Henüz kayıtlı taşınmazın yok</p>
        <p class="category-empty-sub">"Yeni Taşınmaz Ekle" ile ilk taşınmazını kaydet.</p>
      </div>
    <?php else: ?>
      <div class="persons-grid">
        <?php foreach ($properties as $prop): ?>
          <?php $owners = $ownersByProperty[$prop['id']]; ?>
          <div class="person-card">
            <h3><?= htmlspecialchars($prop['title']) ?></h3>
            <?php if ($prop['address']): ?><p class="person-card-line person-card-address"><?= htmlspecialchars($prop['address']) ?></p><?php endif; ?>
            <?php if ($prop['room_count'] || $prop['gross_sqm']): ?>
              <p class="person-card-line"><?= htmlspecialchars(trim(($prop['room_count'] ?: '') . ' ' . ($prop['gross_sqm'] ? $prop['gross_sqm'] . ' m²' : ''))) ?></p>
            <?php endif; ?>
            <?php if ($prop['rent_amount']): ?><p class="person-card-line">Kira: <?= htmlspecialchars($prop['rent_amount']) ?></p><?php endif; ?>
            <?php if (!empty($owners)): ?>
              <p class="person-card-line">Sahip: <?= htmlspecialchars(implode(', ', array_column($owners, 'full_name'))) ?></p>
            <?php endif; ?>
            <div class="person-card-actions">
              <button type="button" class="person-edit-btn"
                data-open-property-modal
                data-property='<?= htmlspecialchars(json_encode($prop, JSON_UNESCAPED_UNICODE)) ?>'
                data-property-owners='<?= htmlspecialchars(json_encode(array_column($owners, 'id'), JSON_UNESCAPED_UNICODE)) ?>'>Düzenle</button>
              <form method="post" action="emlak-tasinmaz-islem.php" onsubmit="return confirm('Bu taşınmazı silmek istediğine emin misin?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $prop['id'] ?>">
                <button type="submit" class="person-delete-btn">Sil</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="modal-overlay hidden" id="property-modal">
      <div class="modal-box person-modal-box">
        <button type="button" class="modal-close" id="property-modal-close">✕</button>
        <h3 id="property-modal-title">Yeni Taşınmaz Ekle</h3>
        <form method="post" action="emlak-tasinmaz-islem.php" class="field-list">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="action" id="property-form-action" value="create">
          <input type="hidden" name="id" id="property-form-id" value="">

          <div class="field"><label for="property-title">Başlık</label>
            <input type="text" id="property-title" name="title" required placeholder="Örn: Kadıköy 2+1 Daire"></div>

          <?php if (PERSONS_FEATURE_ENABLED): ?>
          <div class="field"><label>Sahip(ler)</label>
            <div class="person-multiselect" id="property-owners-list">
              <?php foreach ($allPersons as $p): ?>
                <label class="person-multiselect-item">
                  <input type="checkbox" name="owner_ids[]" value="<?= $p['id'] ?>" class="property-owner-checkbox">
                  <?= htmlspecialchars($p['full_name']) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php else: ?>
          <div class="field"><label>Sahip(ler)</label>
            <p class="category-empty-sub" style="margin:0;">🔒 Kişilerim özelliği geçici olarak kullanım dışı olduğu için sahip atanamıyor.</p>
          </div>
          <?php endif; ?>

          <div class="field-row">
            <div class="field"><label for="property-province">İl</label><input type="text" id="property-province" name="province"></div>
            <div class="field"><label for="property-district">İlçe</label><input type="text" id="property-district" name="district"></div>
          </div>
          <div class="field-row">
            <div class="field"><label for="property-neighborhood">Mahalle</label><input type="text" id="property-neighborhood" name="neighborhood"></div>
            <div class="field"><label for="property-unit-no">Daire No</label><input type="text" id="property-unit-no" name="unit_no"></div>
          </div>
          <div class="field"><label for="property-address">Açık Adres</label><textarea id="property-address" name="address" rows="2"></textarea></div>
          <div class="field-row">
            <div class="field"><label for="property-floor">Kat</label><input type="text" id="property-floor" name="floor"></div>
            <div class="field"><label for="property-room-count">Oda Sayısı</label><input type="text" id="property-room-count" name="room_count" placeholder="Örn: 2+1"></div>
            <div class="field"><label for="property-gross-sqm">Metrekare</label><input type="text" id="property-gross-sqm" name="gross_sqm"></div>
          </div>
          <div class="field-row">
            <div class="field"><label for="property-block-no">Ada</label><input type="text" id="property-block-no" name="block_no"></div>
            <div class="field"><label for="property-parcel-no">Parsel</label><input type="text" id="property-parcel-no" name="parcel_no"></div>
            <div class="field"><label for="property-section-no">Bağımsız Bölüm No</label><input type="text" id="property-section-no" name="independent_section_no"></div>
          </div>
          <div class="field"><label for="property-deed">Tapu Bilgileri</label><input type="text" id="property-deed" name="title_deed_info"></div>
          <div class="field-row">
            <div class="field"><label for="property-rent">Kira Bedeli (opsiyonel)</label><input type="text" id="property-rent" name="rent_amount"></div>
            <div class="field"><label for="property-deposit">Depozito (opsiyonel)</label><input type="text" id="property-deposit" name="deposit_amount"></div>
          </div>
          <div class="field"><label for="property-description">Açıklama</label><textarea id="property-description" name="description" rows="2"></textarea></div>

          <button type="submit" class="download-btn" style="width:100%;">Kaydet</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</main>

<script src="/assets/js/properties.js"></script>

<?php require __DIR__ . '/partials/_footer.php'; ?>
