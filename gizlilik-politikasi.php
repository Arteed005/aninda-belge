<?php
require_once __DIR__ . '/bootstrap.php';

$legalActive = 'gizlilik-politikasi';
$pageTitle = 'Gizlilik Politikası | ' . SITE_TITLE;
$pageDescription = 'Anında belge kullanıcı verilerinin toplanması, kullanılması ve korunmasına ilişkin gizlilik politikası.';

$sections = [
    ['title' => '1. Topladığımız Bilgiler', 'body' => 'Belge oluşturma sırasında girdiğiniz ad-soyad, adres, tarih, tutar gibi bilgiler yalnızca talep ettiğiniz belgeyi oluşturmak amacıyla kullanılır. Hesap oluşturmanız halinde e-posta adresiniz ve şifrelenmiş şifreniz saklanır. Ayrıca IP adresi, tarayıcı bilgisi ve site içi gezinme verileri otomatik olarak toplanır.'],
    ['title' => '2. Bilgilerin Kullanım Amacı', 'body' => 'Topladığımız bilgiler; talep edilen belgenin oluşturulması, hesap yönetimi, ödeme işlemlerinin tamamlanması ve hizmet kalitesinin iyileştirilmesi amaçlarıyla kullanılır.'],
    ['title' => '3. Belge İçeriği Verilerinin Saklanması', 'body' => 'Belge formuna girdiğiniz bilgiler, PDF oluşturulduktan sonra sunucularımızda sınırlı bir süre saklanır ve süre sonunda otomatik olarak silinir. Kayıtlı kullanıcılar geçmiş belgelerini istedikleri zaman silebilir.'],
    ['title' => '4. Bilgilerin Paylaşımı', 'body' => 'Kişisel verileriniz, yasal zorunluluk halleri ve ödeme işlemlerinin yürütülmesi dışında üçüncü kişilerle paylaşılmaz, satılmaz veya kiralanmaz.'],
    ['title' => '5. Çerezler', 'body' => 'Site, kullanıcı deneyimini iyileştirmek amacıyla çerezler kullanabilir. Tarayıcı ayarlarınızdan çerezleri devre dışı bırakabilirsiniz.'],
    ['title' => '6. Veri Güvenliği', 'body' => 'Verilerinizin güvenliği için SSL şifreleme ve şifrelerin güvenli biçimde saklanması gibi teknik tedbirler uygulanmaktadır.'],
    ['title' => '7. Haklarınız', 'body' => 'KVKK kapsamındaki haklarınız için KVKK Aydınlatma Metni sayfamızı inceleyebilirsiniz.'],
    ['title' => '8. İletişim', 'body' => 'Gizlilikle ilgili sorularınız için destek@anindabelge.com adresinden bize ulaşabilirsiniz.'],
];

require __DIR__ . '/partials/_header.php';
?>

<div class="breadcrumb">
  <a href="/">Ana Sayfa</a><span>&rsaquo;</span>
  <span>Yasal</span><span>&rsaquo;</span>
  <span class="current">Gizlilik Politikası</span>
</div>

<main class="legal-main">
  <?php require __DIR__ . '/partials/_legal-nav.php'; ?>

  <div class="legal-content">
    <p class="legal-updated">Son Güncelleme Tarihi: 13.07.2026</p>
    <h1>Gizlilik Politikası</h1>

    <?php foreach ($sections as $s): ?>
      <section class="legal-section">
        <h2><?= htmlspecialchars($s['title']) ?></h2>
        <p><?= htmlspecialchars($s['body']) ?></p>
      </section>
    <?php endforeach; ?>
  </div>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
