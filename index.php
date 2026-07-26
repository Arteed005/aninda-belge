<?php
require_once __DIR__ . '/bootstrap.php';

$searchCatalog = [
    ['name' => 'Kira Sözleşmesi', 'cat' => 'Sözleşme', 'slug' => 'kira-sozlesmesi'],
    ['name' => 'İstifa Dilekçesi', 'cat' => 'Dilekçe', 'slug' => 'istifa-dilekcesi'],
    ['name' => 'Araç Satış Sözleşmesi', 'cat' => 'Sözleşme', 'slug' => 'arac-satis-sozlesmesi'],
    ['name' => 'Vekaletname', 'cat' => 'Kişisel Belge', 'slug' => 'vekaletname'],
    ['name' => 'İş Sözleşmesi', 'cat' => 'Sözleşme', 'slug' => 'is-sozlesmesi'],
    ['name' => 'Fesih Dilekçesi', 'cat' => 'Dilekçe', 'slug' => 'fesih-dilekcesi'],
    ['name' => 'Muvafakatname', 'cat' => 'Kişisel Belge', 'slug' => 'muvafakatname'],
    ['name' => 'İbraname', 'cat' => 'İş Belgesi', 'slug' => 'ibraname'],
    ['name' => 'Özgeçmiş (CV)', 'cat' => 'İş Belgesi', 'slug' => 'ozgecmis-cv'],
    ['name' => 'Nüfus Kaydı Talep Dilekçesi', 'cat' => 'Dilekçe', 'slug' => 'nufus-kaydi-talep-dilekcesi'],
    ['name' => 'İhtarname', 'cat' => 'Dilekçe', 'slug' => 'ihtarname'],
    ['name' => 'Borç Senedi', 'cat' => 'Sözleşme', 'slug' => 'borc-senedi'],
    ['name' => 'Eşya Satış Sözleşmesi', 'cat' => 'Sözleşme', 'slug' => 'esya-satis-sozlesmesi'],
    ['name' => 'Kapora Sözleşmesi', 'cat' => 'Sözleşme', 'slug' => 'kapora-sozlesmesi'],
    ['name' => 'Freelance Hizmet Sözleşmesi', 'cat' => 'Sözleşme', 'slug' => 'freelance-hizmet-sozlesmesi'],
    ['name' => 'Ortaklık Sözleşmesi', 'cat' => 'Sözleşme', 'slug' => 'ortaklik-sozlesmesi'],
    ['name' => 'İzin Talep Dilekçesi', 'cat' => 'Dilekçe', 'slug' => 'izin-talep-dilekcesi'],
    ['name' => 'Zam Talep Dilekçesi', 'cat' => 'Dilekçe', 'slug' => 'zam-talep-dilekcesi'],
    ['name' => 'Trafik Cezası İtiraz Dilekçesi', 'cat' => 'Dilekçe', 'slug' => 'trafik-cezasi-itiraz-dilekcesi'],
    ['name' => 'Askerlik Erteleme Dilekçesi', 'cat' => 'Dilekçe', 'slug' => 'askerlik-erteleme-dilekcesi'],
    ['name' => 'Çalışma Belgesi', 'cat' => 'İş Belgesi', 'slug' => 'calisma-belgesi'],
    ['name' => 'Referans Mektubu', 'cat' => 'İş Belgesi', 'slug' => 'referans-mektubu'],
    ['name' => 'Staj Sözleşmesi', 'cat' => 'Sözleşme', 'slug' => 'staj-sozlesmesi'],
    ['name' => 'Ön Yazı', 'cat' => 'İş Belgesi', 'slug' => 'on-yazi'],
    ['name' => 'Kefaletname', 'cat' => 'Kişisel Belge', 'slug' => 'kefaletname'],
    ['name' => 'Taahhütname', 'cat' => 'Kişisel Belge', 'slug' => 'taahhutname'],
];
foreach ($searchCatalog as &$item) {
    $itemCfg = getTemplateConfig($item['slug']);
    $item['available'] = $itemCfg !== null;
    $item['href'] = (($itemCfg['kind'] ?? 'contract') === 'resume') ? 'cv-olustur.php' : 'sablon.php?slug=' . $item['slug'];
}
unset($item);

$chipNames = ['Kira Sözleşmesi', 'İstifa Dilekçesi', 'Vekaletname', 'Araç Satış Sözleşmesi'];

$popularCards = [
    ['name' => 'Kira Sözleşmesi', 'tag' => 'Sözleşme', 'slug' => 'kira-sozlesmesi', 'meta' => 'Ort. 3 dk',
        'desc' => 'Ev veya iş yeri kiralamaları için yasal olarak geçerli sözleşme oluştur.'],
    ['name' => 'İstifa Dilekçesi', 'tag' => 'Dilekçe', 'slug' => 'istifa-dilekcesi', 'meta' => 'Ort. 2 dk',
        'desc' => 'İşyerinden ayrılırken kullanabileceğin resmi istifa dilekçesi hazırla.'],
    ['name' => 'Araç Satış Sözleşmesi', 'tag' => 'Sözleşme', 'slug' => 'arac-satis-sozlesmesi', 'meta' => 'Ort. 4 dk',
        'desc' => 'Araç alım satım işlemlerini güvence altına alan sözleşme taslağı.'],
    ['name' => 'Vekaletname', 'tag' => 'Kişisel Belge', 'slug' => 'vekaletname', 'meta' => 'Ort. 3 dk',
        'desc' => 'Bir işlemi senin adına yapması için başka birine yetki ver.'],
];
foreach ($popularCards as &$card) {
    $card['available'] = getTemplateConfig($card['slug']) !== null;
}
unset($card);

$__homeUser = currentUser();
$__homeIsPremium = $__homeUser && !empty($__homeUser['is_premium']);

$pageTitle = SITE_TITLE . ' | Belgeni 3 Dakikada Hazırla';
$pageDescription = SITE_DESCRIPTION;
require __DIR__ . '/partials/_header.php';
?>

<main>
<section class="hero">
  <div class="hero-blob-1"></div>
  <div class="hero-blob-2"></div>

  <div class="hero-inner">
    <h1>Belgeni <span class="accent">3 Dakikada</span> Hazırla</h1>
    <p class="hero-sub">Sözleşme, dilekçe ve resmi belgelerini hazır şablonlardan saniyeler içinde oluştur, PDF olarak indir.</p>

    <form id="search-form" class="search-form">
      <div class="search-box">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5b6b82" stroke-width="2" stroke-linecap="round">
          <circle cx="11" cy="11" r="7"></circle>
          <line x1="21" y1="21" x2="16.2" y2="16.2"></line>
        </svg>
        <input type="text" id="search-input" class="search-input" placeholder="Hangi belgeye ihtiyacın var? örn: kira sözleşmesi" autocomplete="off">
        <button type="submit" class="search-submit">Ara</button>
      </div>
      <div id="search-dropdown" class="search-dropdown"></div>
    </form>

    <div id="chips-area" class="chips-area">
      <div class="chip-row">
        <?php foreach ($chipNames as $chip): ?>
          <button type="button" class="chip-btn" data-chip="<?= htmlspecialchars($chip) ?>"><?= htmlspecialchars($chip) ?></button>
        <?php endforeach; ?>
      </div>
      <p class="trust-line">SSL korumalı&nbsp; &middot; &nbsp;KVKK uyumlu&nbsp; &middot; &nbsp;10.000+ kullanıcı tarafından tercih ediliyor</p>
    </div>
  </div>
</section>

<section class="section-block alt">
  <div class="section-inner">
    <div class="section-heading">
      <h2>Kategoriler</h2>
      <p>İhtiyacına uygun belge türünü seç</p>
    </div>
    <div class="cat-grid">

      <a href="kategori.php?slug=sozlesmeler" class="cat-card">
        <div class="cat-icon">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2.5h8l4 4V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1Z"></path><path d="M14 2.5V6.5a1 1 0 0 0 1 1H18.5"></path><line x1="7.5" y1="10" x2="15.5" y2="10"></line><line x1="7.5" y1="13" x2="15.5" y2="13"></line><path d="M7.5 16.7l1.6 1.6 3-3.6" stroke-width="1.8"></path></svg>
        </div>
        <h3>Sözleşmeler</h3>
        <p>Kira, iş, satış ve daha fazlası için hazır sözleşme şablonları</p>
      </a>

      <a href="kategori.php?slug=dilekceler" class="cat-card">
        <div class="cat-icon">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2.5h8l4 4V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1Z"></path><path d="M14 2.5V6.5a1 1 0 0 0 1 1H18.5"></path><line x1="7.5" y1="10" x2="13.5" y2="10"></line><path d="M9 18.7l.4-2 6-6 1.6 1.6-6 6-2 .4Z"></path></svg>
        </div>
        <h3>Dilekçeler</h3>
        <p>Resmi kurumlara ve işverene sunacağın dilekçeleri hızlıca yaz</p>
      </a>

      <a href="kategori.php?slug=is-belgeleri" class="cat-card">
        <div class="cat-icon">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="12" rx="2"></rect><path d="M8.5 8V6a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v2"></path><line x1="3" y1="13" x2="21" y2="13"></line><line x1="10.3" y1="13" x2="13.7" y2="13" stroke-width="2.2"></line></svg>
        </div>
        <h3>İş Belgeleri</h3>
        <p>Özgeçmiş, ibraname ve kurumsal evraklar tek yerde</p>
      </a>

      <a href="kategori.php?slug=kisisel-belgeler" class="cat-card">
        <div class="cat-icon">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2.2"></rect><circle cx="8" cy="12" r="2.2"></circle><line x1="13" y1="10" x2="18.5" y2="10"></line><line x1="13" y1="13" x2="18.5" y2="13"></line><line x1="5.5" y1="16.3" x2="10.5" y2="16.3"></line></svg>
        </div>
        <h3>Kişisel Belgeler</h3>
        <p>Vekaletname, muvafakatname ve diğer kişisel evraklar</p>
      </a>

    </div>
  </div>
</section>

<section class="section-block">
  <div class="section-inner">
    <div class="section-heading">
      <h2>Popüler Şablonlar</h2>
      <p>En çok kullanılan hazır belge şablonları</p>
    </div>
    <div class="popular-grid">
      <?php foreach ($popularCards as $card): ?>
        <div class="popular-card">
          <span class="popular-tag"><?= htmlspecialchars($card['tag']) ?></span>
          <h3><?= htmlspecialchars($card['name']) ?></h3>
          <p><?= htmlspecialchars($card['desc']) ?></p>
          <div class="popular-footer">
            <span class="popular-meta"><?= htmlspecialchars($card['meta']) ?></span>
            <?php if ($card['available']): ?>
              <a href="sablon.php?slug=<?= urlencode($card['slug']) ?>" class="popular-btn">Kullan</a>
            <?php else: ?>
              <button type="button" class="popular-btn" data-modal-template="<?= htmlspecialchars($card['name']) ?>">Kullan</button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if (!$__homeIsPremium): ?>
<section class="section-block">
  <div class="section-inner">
    <div class="premium-banner">
      <div class="premium-banner-glow"></div>
      <span class="premium-banner-badge">✨ Premium</span>
      <h2>Filigransız, Sınırsız, Öncelikli</h2>
      <p>Tüm belgelerini filigransız indir, sınırsız belge oluştur, öncelikli destek al — ayda sadece <strong>₺<?= PREMIUM_PRICE_TRY ?></strong>.</p>
      <a href="premium.php" class="premium-banner-cta">Premium'a Geç <span aria-hidden="true">→</span></a>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section-block alt">
  <div class="section-inner" style="max-width:980px;text-align:center">
    <div class="section-heading">
      <h2>Nasıl Çalışır</h2>
      <p>Üç basit adımda belgen hazır</p>
    </div>
    <div class="how-grid">

      <div class="how-step">
        <div class="how-icon-wrap">
          <div class="how-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2.5h8l4 4V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1Z"></path><path d="M14 2.5V6.5a1 1 0 0 0 1 1H18.5"></path><circle cx="15.5" cy="16.3" r="4.2" fill="#f5f7fa"></circle><path d="M13.6 16.3l1.2 1.2 2.1-2.5" stroke-width="1.8"></path></svg>
          </div>
          <div class="how-badge">1</div>
        </div>
        <h3>Şablon Seç</h3>
        <p>İhtiyacına uygun şablonu binlerce belge arasından seç</p>
      </div>

      <div class="how-step">
        <div class="how-icon-wrap">
          <div class="how-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="14" height="18" rx="1.5"></rect><line x1="6" y1="7.5" x2="14" y2="7.5"></line><line x1="6" y1="11" x2="14" y2="11"></line><line x1="6" y1="14.5" x2="11" y2="14.5"></line><path d="M15 17.6l4.3-4.3 1.6 1.6-4.3 4.3H15z" fill="currentColor" stroke="none"></path></svg>
          </div>
          <div class="how-badge">2</div>
        </div>
        <h3>Bilgilerini Doldur</h3>
        <p>Formu doldur, bilgilerin otomatik olarak belgeye işlensin</p>
      </div>

      <div class="how-step">
        <div class="how-icon-wrap">
          <div class="how-icon">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2.5h8l4 4V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1Z"></path><path d="M14 2.5V6.5a1 1 0 0 0 1 1H18.5"></path><line x1="12" y1="10.5" x2="12" y2="16.5"></line><polyline points="9,14 12,17 15,14"></polyline></svg>
          </div>
          <div class="how-badge">3</div>
        </div>
        <h3>PDF İndir</h3>
        <p>Belgeni önizle, onayla ve PDF olarak hemen indir</p>
      </div>

    </div>
  </div>
</section>
</main>

<div id="template-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <button type="button" id="modal-close" class="modal-close">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="5" y1="5" x2="19" y2="19"></line><line x1="19" y1="5" x2="5" y2="19"></line></svg>
    </button>
    <div class="modal-icon">
      <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2.5h8l4 4V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1Z"></path><path d="M14 2.5V6.5a1 1 0 0 0 1 1H18.5"></path><line x1="12" y1="10.5" x2="12" y2="16.5"></line><polyline points="9,14 12,17 15,14"></polyline></svg>
    </div>
    <h3 id="modal-title"></h3>
    <p>Bu şablonu doldurmaya hazırız! Bu belge türü çok yakında eklenecek.</p>
    <button type="button" id="modal-ok" class="modal-ok-btn">Tamam, Anladım</button>
  </div>
</div>

<script type="application/json" id="catalog-data"><?= json_encode($searchCatalog, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="/assets/js/homepage.js"></script>

<?php require __DIR__ . '/partials/_footer.php'; ?>
