<?php
require_once __DIR__ . '/bootstrap.php';

$legalActive = 'kullanim-sartlari';
$pageTitle = 'Kullanım Şartları | ' . SITE_TITLE;
$pageDescription = 'Anında belge internet sitesini kullanırken geçerli olan kullanım şartları ve koşulları.';

$sections = [
    ['title' => '1. Taraflar ve Kabul', 'body' => "İşbu Kullanım Şartları, Anında Belge tarafından işletilen anindabelge.com internet sitesi ile Site'yi kullanan kişi arasındaki ilişkiyi düzenler. Site'yi kullanarak bu Şartları kabul etmiş sayılırsınız."],
    ['title' => '2. Hizmetin Tanımı', 'body' => 'Anında Belge, kullanıcıların girdiği bilgilerle otomatik olarak sözleşme, dilekçe ve benzeri belge taslakları oluşturmasını sağlayan çevrimiçi bir araçtır. Üretilen belgeler genel amaçlı şablonlardır.'],
    ['title' => '3. Hukuki Tavsiye Niteliği Taşımaması', 'body' => 'Site üzerinden oluşturulan hiçbir belge hukuki danışmanlık hizmeti teşkil etmez. Üretilen belgelerin doğruluğu ve yasal geçerliliği konusunda garanti verilmemektedir. Kullanıcı, gerekli görmesi halinde bir avukata danışmakla yükümlüdür.'],
    ['title' => '4. Kullanıcı Yükümlülükleri', 'body' => "Kullanıcı, Site'ye girdiği bilgilerin doğru olduğunu ve Site'yi yasadışı veya üçüncü kişilerin haklarını ihlal edecek şekilde kullanmayacağını kabul eder."],
    ['title' => '5. Ücretli Hizmetler', 'body' => 'Bazı özellikler (filigransız indirme, Word formatı vb.) ücretli olarak sunulabilir. Fiyatlar ilgili satın alma ekranında belirtilir.'],
    ['title' => '6. Fikri Mülkiyet', 'body' => "Site'nin tasarımı, yazılımı ve şablon altyapısı Anında Belge'ye aittir. Kullanıcı, oluşturduğu nihai belgeyi serbestçe kullanabilir ancak Site'nin yapısını izinsiz kopyalayamaz."],
    ['title' => '7. Sorumluluğun Sınırlandırılması', 'body' => "Şirket, Site'nin kullanımından veya üretilen belgelerin içeriğinden doğabilecek zararlardan sorumlu tutulamaz."],
    ['title' => '8. İletişim', 'body' => 'Sorularınız için destek@anindabelge.com adresinden bize ulaşabilirsiniz.'],
];

require __DIR__ . '/partials/_header.php';
?>

<div class="breadcrumb">
  <a href="/">Ana Sayfa</a><span>&rsaquo;</span>
  <span>Yasal</span><span>&rsaquo;</span>
  <span class="current">Kullanım Şartları</span>
</div>

<main class="legal-main">
  <?php require __DIR__ . '/partials/_legal-nav.php'; ?>

  <div class="legal-content">
    <p class="legal-updated">Son Güncelleme Tarihi: 13.07.2026</p>
    <h1>Kullanım Şartları</h1>

    <?php foreach ($sections as $s): ?>
      <section class="legal-section">
        <h2><?= htmlspecialchars($s['title']) ?></h2>
        <p><?= htmlspecialchars($s['body']) ?></p>
      </section>
    <?php endforeach; ?>
  </div>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
