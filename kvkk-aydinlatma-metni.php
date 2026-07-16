<?php
require_once __DIR__ . '/bootstrap.php';

$legalActive = 'kvkk-aydinlatma-metni';
$pageTitle = 'KVKK Aydınlatma Metni | ' . SITE_TITLE;
$pageDescription = 'Anında belge kişisel verilerin işlenmesine ilişkin KVKK aydınlatma metni.';

$dataCategories = [
    ['cat' => 'Kimlik Bilgisi', 'examples' => 'Ad, soyad, TC kimlik numarası'],
    ['cat' => 'İletişim Bilgisi', 'examples' => 'E-posta, adres, telefon'],
    ['cat' => 'Müşteri İşlem Bilgisi', 'examples' => 'Oluşturulan belge türü, işlem tarihi'],
    ['cat' => 'Finansal Bilgi', 'examples' => 'Sınırlı ödeme bilgileri'],
    ['cat' => 'İşlem Güvenliği Bilgisi', 'examples' => 'IP adresi, çerez verileri'],
];

$rights = [
    'Kişisel verinizin işlenip işlenmediğini öğrenme',
    'İşlenmişse buna ilişkin bilgi talep etme',
    'İşlenme amacını öğrenme',
    'Yurt içi/dışı aktarıldığı kişileri bilme',
    'Eksik veya yanlış işlenmişse düzeltilmesini isteme',
    'Kanuni şartlar çerçevesinde silinmesini isteme',
    'Otomatik sistemlerle analiz edilmesi sonucu aleyhinize sonuç çıkmasına itiraz etme',
];

require __DIR__ . '/partials/_header.php';
?>

<div class="breadcrumb">
  <a href="index.php">Ana Sayfa</a><span>&rsaquo;</span>
  <span>Yasal</span><span>&rsaquo;</span>
  <span class="current">KVKK Aydınlatma Metni</span>
</div>

<main class="legal-main">
  <?php require __DIR__ . '/partials/_legal-nav.php'; ?>

  <div class="legal-content">
    <p class="legal-updated">Son Güncelleme Tarihi: 13.07.2026</p>
    <h1>KVKK Aydınlatma Metni</h1>
    <p class="legal-intro">6698 sayılı Kişisel Verilerin Korunması Kanunu uyarınca, veri sorumlusu sıfatıyla Anında Belge tarafından elde edilen kişisel verileriniz aşağıdaki kapsamda işlenmektedir.</p>

    <section class="legal-section">
      <h2>1. Veri Sorumlusu</h2>
      <p>Unvan: Anında Belge<br>E-posta: destek@anindabelge.com</p>
    </section>

    <section class="legal-section">
      <h2>2. İşlenen Kişisel Veri Kategorileri</h2>
      <div class="legal-table-wrap">
        <table class="legal-table">
          <thead>
            <tr><th>Kategori</th><th>Örnekler</th></tr>
          </thead>
          <tbody>
            <?php foreach ($dataCategories as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['cat']) ?></td>
                <td><?= htmlspecialchars($row['examples']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="legal-section">
      <h2>3. İşlenme Amaçları</h2>
      <p>Kişisel verileriniz; talep ettiğiniz belgenin oluşturulması, üyelik işlemlerinin yürütülmesi, ödeme süreçlerinin tamamlanması ve hukuki yükümlülüklerin yerine getirilmesi amaçlarıyla işlenmektedir.</p>
    </section>

    <section class="legal-section">
      <h2>4. Toplanma Yöntemi ve Hukuki Sebebi</h2>
      <p>Verileriniz, Site üzerindeki formları doldurmanız yoluyla elektronik ortamda toplanır. İşleme faaliyeti; sözleşmenin ifası, hukuki yükümlülüğün yerine getirilmesi ve meşru menfaat hukuki sebeplerine dayanmaktadır.</p>
    </section>

    <section class="legal-section">
      <h2>5. Veri Sahibinin Hakları</h2>
      <ul class="legal-rights">
        <?php foreach ($rights as $r): ?>
          <li><?= htmlspecialchars($r) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section class="legal-section">
      <h2>6. Başvuru Yöntemi</h2>
      <p>Haklarınızı kullanmak için taleplerinizi destek@anindabelge.com adresine iletebilirsiniz. Başvurularınız en geç 30 gün içinde ücretsiz olarak sonuçlandırılır.</p>
    </section>
  </div>
</main>

<?php require __DIR__ . '/partials/_footer.php'; ?>
