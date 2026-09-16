# AGENTS.md — Anında Belge Proje Rehberi ve Mimari Dokümantasyonu

Bu belge, **Anında Belge** projesinde çalışacak yapay zeka ajanları (ve insan geliştiriciler) için projenin mevcut durumunu, mimarisini, teknik kararlarını, kurallarını ve yapılacaklar listesini özetleyen **tek doğruluk kaynağıdır (single source of truth)**.

---

## 1. Proje Genel Bakışı

- **Proje Adı:** Anında Belge
- **Canlı URL:** [https://anindabelge.com](https://anindabelge.com)
- **Repo:** `Arteed005/aninda-belge` (GitHub)
- **Hedef:** Türkiye pazarı için kullanıcıların 3 dakika içinde resmi ve hukuki standartlara uygun sözleşmeler, dilekçeler, mektuplar, profesyonel CV'ler ve iş hukuku hesaplamaları oluşturmasını sağlayan modern bir SaaS platformu.
- **Canlı Ortam & Dağıtım:** cPanel paylaşımlı hosting (`mt-north.guzelhosting.com`), Git™ Version Control entegrasyonu ("Pull or Deploy" tek tıkla güncelleme). Yerel geliştirme ortamı XAMPP (Apache + MySQL/MariaDB + PHP 8.x).

---

## 2. Teknoloji Yığını (Tech Stack)

| Katman | Teknoloji / Kütüphane | Açıklama / Kararlar |
| :--- | :--- | :--- |
| **Backend** | Saf PHP (Vanilla PHP 8.x) | Framework (Laravel, Symfony vb.) YOKTUR. Hızlı yükleme, düşük kaynak kullanımı ve cPanel uyumluluğu için düz ve modüler PHP tercih edilmiştir. |
| **Veritabanı** | MySQL / MariaDB (InnoDB, utf8mb4) | PDO tekil bağlantısı (`getPdo()`), %100 Prepared Statements. |
| **Frontend** | Vanilla JavaScript (ES6+) | Framework (React, Vue vb.) YOKTUR. Küçük boyut, bağımlılıksız DOM yönetimi (`live-preview.js`, `cv-builder.js`, `calculators.js`). |
| **Stil (CSS)** | Vanilla CSS (`assets/css/site.css`) | Tailwind veya Bootstrap KULLANILMAZ. CSS Custom Properties (değişkenler), flexbox/grid (sadece web arayüzünde; PDF'te DEĞİL). |
| **PDF Üretimi** | Dompdf (`dompdf/dompdf`) | Sunucuda HTML/CSS'i doğrudan A4 PDF'e çevirir. `vendor/` klasörü doğrudan sunucuda tutulur. |
| **E-posta İletimi** | PHPMailer (`phpmailer/phpmailer`) | cPanel SMTP SSL 465 üzerinden gerçek gönderim (`destek@anindabelge.com`). |
| **Görsel İşleme** | PHP GD Extension | CV fotoğraf yüklemelerinde kare kırpma ve 400x400 boyutlandırma (`ResumePhoto.php`). |
| **Ödeme Altyapısı**| Shopier REST API & Webhook | Bireysel Erişim Anahtarı (PAT) + `order.created` webhook + Idempotency tablosu. |
| **Analitik & SEO** | GA4 (`G-EPQ8MRZEN8`), Schema.org | Gerekli sayfalarda BreadcrumbList, FAQPage ve Organization JSON-LD mikro verileri. |

---

## 3. Mimari Prensipler ve Tasarım Kararları

### 3.1. Config-Driven (Yapılandırma Odaklı) Motor
- **Yeni Belge Eklemek:** Kod yazmayı gerektirmez. `templates/configs/{slug}.json` dosyası eklenmesi yeterlidir. Sistem bu dosyayı otomatik keşfeder.
- **Hesaplayıcılar:** `templates/calculators/{slug}.json` ve `lib/CalculatorConfig.php` üzerinden yönetilir.
- **Rehberler:** `templates/guides/{slug}.json` ve `lib/GuideConfig.php` üzerinden yönetilir.

### 3.2. Tek Doğruluk Kaynağı (Single Source of Truth)
- `lib/ClauseRenderer.php`: Hem web üzerindeki canlı önizleme (SSR/JS) hem de Dompdf çıktısı için metinlerin üretildiği tek kaynaktır.
- `lib/ResumeRenderer.php`: CV belgeleri için aynı tekil render mantığını sağlar.
- `lib/FormFields.php`: Tüm form tiplerinin (`text`, `textarea`, `date`, `select`, `number`, `money`, `file`) ortak render motorudur.

### 3.3. Belge Türleri (Document Kinds)
1. **Sözleşmeler ve Dilekçeler (`kind: "contract"` veya tanımsız):**
   - **Maddeli format** (Kira, Araç Satış, İş Sözleşmesi vb.) veya **Akıcı mektup formatı** (İstifa, İhtarname, Dilekçeler).
   - "Ek Madde Ekleme" ve standart maddeleri serbestçe düzenleyebilme ("Clause Overrides") desteği.
   - İki sütunlu tablo tabanlı imza bloğu.
2. **Özgeçmiş (`kind: "resume"`):**
   - `cv-olustur.php` ve `cv-indir.php` üzerinden bağımsız akış.
   - 3 görsel tema: **Klasik** (tek sütun), **Modern** (fotoğraflı, iki sütunlu lacivert yan panel), **Minimalist** (zarif tipografi).
   - Tekrarlanabilir gruplar (`groups`: İş Deneyimi, Eğitim, Sertifika, Proje, Referans).
   - HTML5 Native sürükle-bırak (`drag & drop`) ile sıralama.
   - İmza bloğu yer almaz.

### 3.4. Dinamik PDF Ölçekleme (`buildFittedPdf`)
- Kullanıcı çok fazla ek madde girdiğinde veya uzun metin yazdığında PDF'in ikinci sayfaya taşmaması için `buildFittedPdf(callable $renderHtml)` fonksiyonu çalışır.
- Sayfa sayısı 1'i aştığı sürece font ve boşluk ölçeğini kademeli olarak `1.0`'dan `0.66`'ya kadar küçültür ve tek sayfaya sığdırır.

### 3.5. Dompdf Sınırlamaları ve Kritik Kurallar
> [!WARNING]
> Dompdf **Flexbox ve CSS Grid desteklemez**.
- PDF şablonlarında (`templates/pdf-shell.php`, `templates/resume-shell-*.php`) düzenler sadece `<table>` veya `float` ile kurulmalıdır.
- Float kullanıldığında ardışık elemanların üst üste binmemesi için her kartta `clear: both; overflow: hidden;` kuralı zorunludur.
- Çok sayfaya taşabilecek CV çıktılarında sayfa bölünmelerini korumak için `break-inside: avoid;` kullanılır.

### 3.6. KVKK ve Gizlilik Mimarisi (Privacy-First)
- **Misafir Kullanıcılar (Giriş Yapmamış):** Doldurulan veriler veritabanına (`documents`) **asla kaydedilmez**. Bellekte anlık PDF üretilir, stream edilir ve bellekten silinir.
- **Kayıtlı Kullanıcılar:** Belgelerim geçmişi için `documents` tablosuna kaydedilir. 30 gün (`RETENTION_DAYS_DEFAULT`) sonra `cron/cleanup-documents.php` CLI görevi tarafından otomatik olarak temizlenir.
- **Kayıtlı Kişiler (Persons):** Üçüncü şahıslara ait T.C. Kimlik No, telefon, adres gibi hassas verilerin düz metin saklanması KVKK riski doğurduğu için `config/app.php` içinde `PERSONS_FEATURE_ENABLED = false` ile geçici olarak dondurulmuştur (kodlar korunmuş, arayüz ve sızıntı noktaları kapatılmıştır).

### 3.7. Güvenlik Prensipleri
- CSRF Koruması: Form gönderimlerinde `csrf_token()` ve `csrf_check()`.
- Path Traversal Koruması: Slug parametrelerinde katı regex doğrulaması (`^[a-z0-9-]+$`).
- XSS Önleme: PHP tarafında `htmlspecialchars()`, JS tarafında `textContent`.
- Token Güvenliği: E-posta doğrulama ve şifre sıfırlama token'ları veritabanında SHA-256 hash olarak saklanır (plaintext asla tutulmaz), sürelidir (24 saat / 1 saat).

---

## 4. Dizin ve Dosya Yapısı

```
c:\xampp\htdocs\Anında Belge/
├── admin/                     # Yönetici Paneli
│   ├── _guard.php             # Admin yetki kontrolü (is_admin = 1)
│   ├── _layout_top.php        # Admin ortak başlık ve navigasyon
│   ├── _layout_bottom.php     # Admin ortak kapanış
│   ├── index.php              # Admin gösterge paneli (KPI'lar, son kayıtlar)
│   ├── musteriler.php         # Müşteri listesi ve paket atama dropdown'u
│   ├── musteri-islem.php      # Paket güncelleme endpoint'i (action=set_package)
│   ├── odemeler.php           # Shopier siparişleri ve paket bazlı ciro grafiği
│   ├── belgeler.php           # Sistemde üretilmiş belgeler listesi
│   ├── belge-goster.php       # Belge detay görüntüleme
│   ├── belge-sil.php          # Manuel belge silme
│   └── ayarlar.php            # Sistem ve ortam bilgileri
│
├── assets/
│   ├── css/site.css           # Tüm sitenin tekil CSS dosyası (özelleştirilmiş tasarım sistemi)
│   ├── js/
│   │   ├── live-preview.js    # Sözleşme/Dilekçe sihirbazı ve anlık önizleme
│   │   ├── cv-builder.js      # CV sihirbazı, grup ekleme/silme, drag-drop
│   │   ├── calculators.js     # Kıdem, ihbar, izin hesaplama motoru
│   │   ├── category.js        # Kategori içi arama ve filtreleme
│   │   ├── homepage.js        # Ana sayfa arama kataloğu ve modal yönetimi
│   │   ├── person-picker.js   # Formlarda kayıtlı kişi otomatik doldurucu
│   │   └── property-picker.js # Kira sözleşmesinde taşınmaz otomatik doldurucu
│   └── (logolar, faviconlar, og-image vb.)
│
├── config/
│   ├── app.php                # Genel ayarlar, sabitler, paket fiyatları, KVKK bayrağı
│   ├── db.php                 # Yerel PDO veritabanı bağlantısı (.gitignore)
│   ├── db.example.php         # Veritabanı şablonu
│   ├── mail.php               # SMTP kimlik bilgileri (.gitignore)
│   ├── mail.example.php       # Mail şablonu
│   ├── shopier.php            # Shopier PAT ve ürün ID'leri (.gitignore)
│   └── shopier.example.php    # Shopier şablonu
│
├── cron/
│   └── cleanup-documents.php  # Süresi dolan (expires_at) belgeleri silen CLI scripti
│
├── lib/                       # Çekirdek Kütüphaneler & İş Mantığı
│   ├── Admin.php              # Admin KPI ve veri çekme fonksiyonları
│   ├── Auth.php               # Kayıt, giriş, oturum, e-posta doğrulama, şifre sıfırlama
│   ├── CalculatorConfig.php   # Hesaplayıcı JSON yapılandırma yöneticisi
│   ├── ClauseRenderer.php     # Sözleşme/Dilekçe HTML & PDF render motoru
│   ├── Documents.php          # Belgeler CRUD ve kullanıcı geçmişi
│   ├── FormFields.php         # Form input elemanları üreticisi
│   ├── GuideConfig.php        # Hukuki rehber makaleleri yöneticisi
│   ├── Mailer.php             # PHPMailer ile HTML e-posta gönderimi
│   ├── Packages.php           # Kullanıcı paketleri yönetimi (Premium, Emlak)
│   ├── Pdf.php                # Dompdf derleyici, fitted scaling ve watermark
│   ├── Persons.php            # Kayıtlı Kişiler (Persons) CRUD katmanı
│   ├── Properties.php         # Taşınmazlar (Properties) CRUD katmanı
│   ├── ResumePhoto.php        # CV fotoğraf kırpma ve base64 işleme
│   ├── ResumeRenderer.php     # CV veri yapısı ve render motoru
│   ├── Security.php           # CSRF koruma yardımcıları
│   ├── Shopier.php            # Shopier API istemcisi, sipariş doğrulama ve paket tanımlama
│   ├── TemplateConfig.php     # Belge şablon JSON yöneticisi
│   └── Validator.php          # Sunucu taraflı form doğrulama kuralları
│
├── partials/
│   ├── _header.php            # Ortak site üst bilgisi, navigasyon, flash banner'lar
│   ├── _footer.php            # Ortak site alt bilgisi, linkler, yasal sayfalar
│   └── _legal-nav.php         # Hukuki sayfalar arası sekme navigasyonu
│
├── sql/
│   ├── schema.sql             # Sıfırdan kurulum için tam veritabanı şeması
│   └── migration-*.sql        # Aşamalı veritabanı güncelleme migration dosyaları
│
├── templates/
│   ├── calculators/*.json     # 3 hesaplayıcı config dosyası
│   ├── configs/*.json         # 26 hazır belge şablon config dosyası
│   ├── guides/*.json          # 3 SEO hukuki rehber makale config dosyası
│   ├── pdf-shell.php          # Sözleşme ve dilekçeler için Dompdf HTML iskeleti
│   ├── resume-shell-klasik.php # CV Klasik tema Dompdf iskeleti
│   ├── resume-shell-modern.php # CV Modern tema Dompdf iskeleti
│   └── resume-shell-minimalist.php # CV Minimalist tema Dompdf iskeleti
│
├── index.php                  # Ana sayfa (kanonik "/" adresine yönlendirir)
├── sablon.php                 # Dinamik sözleşme/dilekçe oluşturma sihirbazı
├── indir.php                  # Sözleşme/Dilekçe PDF üretim ve indirme endpoint'i
├── cv-olustur.php             # Özgeçmiş (CV) oluşturma sihirbazı (3 temalı)
├── cv-indir.php               # Özgeçmiş PDF üretim ve indirme endpoint'i
├── belgelerim.php             # Kullanıcının kayıtlı belgeleri sayfası
├── belge-indir.php            # Belgelerim'den sahiplik kontrollü indirme
├── belge-kopyala.php          # Eski belgeden yeni form doldurma
├── emlak.php                  # Emlak Çalışma Alanı Dashboard
├── emlak-tasinmazlar.php      # Taşınmaz yönetimi listesi ve modalı
├── emlak-tasinmaz-islem.php   # Taşınmaz POST CRUD endpoint'i
├── emlak-tasinmazlar-api.php  # Taşınmaz JSON API (otomatik doldurma için)
├── kisilerim.php              # Kayıtlı kişiler sayfası (geçici olarak donduruldu)
├── kisi-islem.php             # Kişi POST CRUD endpoint'i
├── kisiler-api.php            # Kişi JSON API
├── hesaplayicilar.php         # Hesaplama araçları listesi
├── hesapla.php                # Tekil hesaplama aracı sayfası
├── rehberler.php              # Hukuki rehberler listesi
├── rehber.php                 # Tekil rehber makale sayfası
├── premium.php                # Paketler ve fiyatlandırma sayfası (Premium / Emlak)
├── giris.php                  # Giriş ve Kayıt tek sayfa sekmesi
├── cikis.php                  # Oturum kapatma
├── sifre-sifirla.php          # Şifre sıfırlama talep ve yeni şifre formu
├── dogrula.php                # E-posta doğrulama linki işleme
├── dogrula-gonder.php         # Tekrar doğrulama e-postası gönderme
├── shopier-webhook.php        # Shopier ödeme webhook endpoint'i
├── sitemap.php                # Dinamik XML site haritası
└── 404.php                    # Özel 404 Hata sayfası
```

---

## 5. Üyelik, Paket ve Ödeme Modeli

Veritabanında `users` ve `user_packages` tabloları ile yönetilen esnek bir paket yapısı bulunmaktadır:

```
                  ┌────────────────────────┐
                  │    ÜCRETSİZ KULLANICI  │
                  │  - 26 Şablon Erişimi   │
                  │  - Filigranlı İndirme  │
                  │  - Misafirde No-Storage│
                  └───────────┬────────────┘
                              │
            ┌─────────────────┴─────────────────┐
            ▼                                   ▼
┌───────────────────────┐           ┌────────────────────────┐
│     PREMİUM PAKET     │           │      EMLAK PAKETİ      │
│        (₺99/ay)       │           │        (₺249/ay)       │
├───────────────────────┤           ├────────────────────────┤
│ - Filigransız PDF     │           │ - Premium'un TÜM hakları│
│ - Belgelerim Geçmişi  │──────────►│ - Emlak Dashboard      │
│ - Belge Kopyalama     │ (Kapsar)  │ - Taşınmaz Yönetimi    │
│ - Madde Düzenleme     │           │ - Kira Otomatik Doldurma│
│ - Kayıtlı Kişiler (*) │           │                        │
└───────────────────────┘           └────────────────────────┘
```
*\* KVKK risk analizi nedeniyle Kişilerim geçici olarak `PERSONS_FEATURE_ENABLED = false` durumundadır.*

### Shopier Entegrasyon Akışı
1. Kullanıcı `premium.php` üzerinden Shopier barındırılan ürün sayfasına yönlendirilir.
2. Ödeme tamamlandığında Shopier `POST shopier-webhook.php` adresini tetikler.
3. Webhook gelen gövdeye doğrudan güvenmez; Bireysel Erişim Anahtarı (PAT) ile Shopier REST API'den (`GET /v1/orders/{id}`) siparişin güncel ve gerçek durumunu çeker.
4. Idempotency tablosu (`shopier_processed_orders`) üzerinden siparişin daha önce işlenip işlenmediği kontrol edilir.
5. Siparişteki alıcı e-posta adresiyle eşleşen kullanıcıya:
   - Premium ürün ise: `grantPremiumDays($userId, 30)`
   - Emlak ürün ise: `grantPremiumDays($userId, 30)` + `grantPackageDays($userId, 'emlak', 30)` işletilir.

---

## 6. Mevcut Durum Özeti (Current Status)

- **Şablon Kataloğu:** 26 adet sözleşme, dilekçe ve CV şablonu eksiksiz çalışmaktadır.
- **CV Motoru:** 3 farklı görsel temada (Klasik, Modern, Minimalist) fotoğraf yükleme ve dinamik sürükle-bırak desteğiyle aktiftir.
- **Hesaplayıcılar & SEO:** Kıdem tazminatı, ihbar süresi ve yıllık izin hesaplayıcıları ile 3 adet hukuki rehber makalesi devrededir. Sayfa başlıkları arama niyetine ("Nasıl Yazılır?") göre optimize edilmiştir. PageSpeed skorları masaüstünde 100, mobilde 90+ seviyesindedir.
- **Ödemeler:** Shopier entegrasyonu canlı ortamda doğrulanmış ve çalışmaktadır.
- **Gizlilik:** Misafir kullanıcı form verilerinin veritabanına kaydedilmesi engellenmiş, "privacy-first" modeline geçilmiştir.
- **Şifre Sıfırlama & E-posta Doğrulama:** E-posta ile güvenli sıfırlama akışı, arayüzde 60 saniyelik geri sayım (cooldown) sayacı ve veritabanı seviyesinde e-posta/kullanıcı bazlı rate-limiting (`password_reset_requested_at`, `email_verify_requested_at`) entegre edilmiştir.
- **Aktif Çalışma Alanı:** `sifre-sifirla.php`, `dogrula-gonder.php`, `lib/Auth.php`, `lib/Validator.php`, `sql/migration-password-reset-cooldown.sql`, `sql/migration-email-verify-cooldown.sql` ve `assets/css/site.css` dosyalarında geliştirmeler tamamlanmıştır.

---

## 7. Yapılacaklar Listesi (Roadmap / Backlog)

### 7.1. Kısa Vadeli Görevler & Teknik Borçlar
- [ ] **E-posta & Şifre DB Cooldown Commit & Deploy:** `sifre-sifirla.php`, `dogrula-gonder.php`, `lib/Auth.php`, `lib/Validator.php`, `sql/migration-password-reset-cooldown.sql` ve `sql/migration-email-verify-cooldown.sql` dosyalarının commit edilip cPanel'e deploy edilmesi, canlı veritabanında migration'ların çalıştırılması.
- [ ] **Modern CV Teması Kozmetik İyileştirme:** Az içerikli CV'lerde sol lacivert panelin sayfa sonuna kadar uzamaması durumunun (Dompdf tablo yükseklik kısıtı) giderilmesi veya çok sayfalı CV desteğinin Modern temaya da uyarlanması.
- [ ] **cPanel Cron Görevi Teyidi:** `cron/cleanup-documents.php` CLI görevinin cPanel crontab üzerinde her gece (ör. `0 3 * * *`) çalışacak şekilde ayarlandığının canlı ortamda teyit edilmesi.
- [ ] **Arama Motoru Takibi:** Search Console Performans sekmesinden "nasıl yazılır" ve belge sorgularındaki indeksleme ve sıralama durumunun izlenmesi.

### 7.2. Orta Vadeli Görevler (Emlak Faz 2 ve Gelişmiş Özellikler)
- [ ] **Emlak Çalışma Alanı — Faz 2 (5 Yeni Şablon):**
  - Depozito Teslim Tutanağı
  - Ev Teslim Tutanağı
  - Tahliye Taahhütnamesi
  - Demirbaş Listesi
  - Kiralanan Yer Teslim Tutanağı
- [ ] **Belge Zinciri / Öneri Motoru:** Kira sözleşmesi üretildikten sonra kullanıcıya tek tıkla *"Bu sözleşme için Tahliye Taahhütnamesi ve Depozito Tutanağı da oluşturmak ister misiniz?"* diyerek aynı taraflarla yeni belge önerme akışı.
- [ ] **Kişilerim (Persons) KVKK Çözümü:**
  - T.C. Kimlik No, telefon ve adres verilerinin veritabanında simetrik şifrelemeyle (`openssl_encrypt` AES-256-GCM) saklanması,
  - Kullanıcıya açık rıza / aydınlatma checkbox'ı sunularak özelliğin (`PERSONS_FEATURE_ENABLED = true`) güvenle yeniden açılması.
- [ ] **Shopier E-posta Eşleşme İyileştirmesi:** Kullanıcının Shopier ödeme ekranında sitedeki kayıtlı e-postasından farklı bir adres girmesi durumunda manuel müdahaleye gerek kalmaması için ödeme yönlendirmesinde kullanıcı ID'sinin veya özel parametrenin güvenli taşınması.

### 7.3. Uzun Vadeli Görevler & Büyüme
- [ ] **Hesaplayıcı Tavan Güncellemeleri:** Kıdem tazminatı tavanı her Ocak ve Temmuz ayında değiştiği için `templates/calculators/kidem-tazminati-hesaplama.json` içindeki `constants.tavan` değerinin dönemsel güncellenmesi.
- [ ] **Yeni Dikey Paketler:** Emlak modülünün başarısına göre;
  - İK / Şirket Paketi (İş sözleşmesi, bordro, izin talep, fesih, ibraname yönetimi),
  - Hukuk / Danışmanlık Paketi.
- [ ] **Mobil Uygulama:** Google Play ve Apple Developer hesapları açıldıktan sonra PWA veya webview tabanlı mobil uygulama yayını.

---

## 8. Geliştirici ve Ajanlar İçin Katı Kurallar (Agent Guidelines)

1. **Vanilla Prensibini Bozma:** Projeye Tailwind, Bootstrap, React, Vue, Webpack vb. ağır derleme araçları EKLEME. Mevcut `assets/css/site.css` ve `assets/js/*.js` yapısını sürdür.
2. **Dompdf Kısıtlarına Saygı Duy:**
   - PDF şablonlarına asla Flexbox veya Grid yazma.
   - İki sütunlu düzenler için `<table>` veya `float` kullan.
   - Her `float` bloğunun sonuna `clear: both; overflow: hidden;` ekle.
3. **Veritabanı Değişiklikleri:**
   - Veritabanı şemasında bir değişiklik yaptığında hem `sql/schema.sql` dosyasını güncelle hem de `sql/migration-{konu}.sql` adında yeni bir migration dosyası oluştur.
   - Canlı ortamda migration'ların elle phpMyAdmin'den çalıştırıldığını unutma.
4. **Hassas Bilgileri Repoya Taşıma:**
   - `config/db.php`, `config/mail.php` ve `config/shopier.php` dosyaları `.gitignore` kapsamındadır. Gerçek şifreleri ve API anahtarlarını commit etme; sadece `.example.php` dosyalarını güncelle.
5. **Güvenlikten Ödün Verme:**
   - Yeni bir POST endpoint'i eklerken daima `csrf_check()` kullan.
   - Kullanıcıdan gelen ID parametrelerinde daima `WHERE user_id = :user_id` sahiplik kontrolü yap.
   - Çıktılarda XSS'e karşı `htmlspecialchars()` kullan.
6. **Türkçe Karakter Desteği:**
   - `strtoupper()` yerine projeye özel `trUpper()` (`ClauseRenderer.php`) fonksiyonunu kullan (İ/I dönüşüm hatasını engellemek için).
   - Form verilerinde para birimlerini `15.000 TL`, tarihleri `GG.AA.YYYY` formatında göster.
