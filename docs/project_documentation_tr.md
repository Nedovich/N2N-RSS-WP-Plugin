# N2N Aggregator Wordpress Eklentisi Dokümantasyonu

## 1. Proje Özeti

**N2N Aggregator**, harici kaynaklardan (örneğin n8n otomasyonları veya Python scriptleri) gelen RSS haber verilerini Wordpress sitenizde yayınlamanızı sağlayan bir eklentidir. Bu eklenti, dışarıdan gelen verileri özel bir içerik türü (`aggregated_news`) olarak kaydeder ve bu içerikleri sitenizde listelemenize olanak tanır.

Temel amacı, bir "Haber Toplayıcı" (News Aggregator) backend'i olarak çalışmaktır. Gelen verilerde mükerrer kayıt oluşmasını engellemek için akıllı bir "Upsert" mekanizması kullanır.

## 2. Temel Özellikler

- **Özel İçerik Türü (CPT):** `aggregated_news` adında, sadece başlık ve özet içeren, editörsüz yalın bir içerik türü oluşturur.
- **REST API Entegrasyonu:** Dış kaynaklar `/wp-json/wp/v2/aggregated_news` adresine POST isteği atarak haber ekleyebilir.
- **Akıllı Güncelleme (Upsert):** Eklenen haberin `external_url` (dış bağlantı) adresi veritabanında zaten varsa, yeni bir kayıt oluşturmak yerine mevcut kaydı günceller.
- **Yönlendirme Modları:**
  - **Direkt Yönlendirme:** Kullanıcı habere tıkladığında direkt olarak kaynak siteye gider.
  - **Ara Sayfa (Interstitial):** Kullanıcı önce bir ara sayfaya (reklam veya bilgilendirme) düşer, belirlenen süre sonunda kaynağa yönlendirilir.
- **Shortcode Desteği:** Haberleri listelemek için `[n2n_news]` vb. shortcodelar sunar.

## 3. Çalışma Akış Şeması (Flowchart)

Aşağıdaki şema, verinin sisteme girişinden son kullanıcıya ulaşmasına kadarki süreci özetler:

```mermaid
graph TD
    A[Harici Kaynak<br/>(n8n / Python / RSS)] -->|POST /wp-json/...| B(Wordpress REST API)
    B --> C{Bu URL var mı?}
    C -- Evet --> D[Mevcut Kaydı Güncelle]
    C -- Hayır --> E[Yeni Kayıt Oluştur]
    D & E --> F[(Wordpress Veritabanı)]

    G[Son Kullanıcı] -->|Siteyi Ziyaret Eder| H[Haber Listesi<br/>Shortcode]
    H -->|Habere Tıklar| I{Yönlendirme Modu}
    I -- Direct --> J[Kaynak Siteye Git]
    I -- Interstitial --> K[Ara Sayfa Göster<br/>Sayaç/Reklam]
    K -->|Süre Dolunca| J
```

## 4. Dosya Yapısı ve İşlevleri

Projenin temel dosyaları ve görevleri şunlardır:

### Ana Dosya

- `n2n-aggregator.php`: Eklentinin giriş noktasıdır. Tüm alt bileşenleri yükler ve eklenti aktifleştiğinde gerekli kurulumları (rewrite kuralları vb.) yapar.

### Includes Klasörü (`/includes`)

- **`post-type.php`**: `aggregated_news` içerik türünü ve "Kategoriler/Etiketler" taksonomilerini sisteme tanıtır.
- **`rest-api.php`**: API mantığını içerir. Veri geldiğinde `external_url` kontrolü yaparak güncelleme mi yoksa ekleme mi yapılacağına karar verir (Upsert logic).
- **`redirect.php`**: Yönlendirme mantığını yönetir. Eğer mod "Direct" ise, kullanıcı habere girdiği anda `template_redirect` hook'u ile dışarı atar.
- **`renderer.php`**: HTML çıktılarını üreten "View" katmanıdır. Haber kartlarını ve Ara Sayfa (Interstitial) tasarımlarını oluşturur.
- **`meta.php` & `admin-metaboxes.php`**: Haberlere ait `external_url`, `external_image_url` gibi özel alanların (meta fields) yönetimini sağlar.
- **`settings.php`**: WP Admin panelindeki ayar sayfalarını oluşturur (Yönlendirme modu, sayaç süresi vb.).

### Admin Klasörü (`/admin`)

- **`shortcode-builder.php`**: Admin panelinde kullanıcıların kolayca shortcode oluşturmasını sağlayan arayüzü barındırır.

## 5. Nasıl Kullanılır?

1.  **Kurulum:** Eklentiyi Wordpress plugins klasörüne atın ve aktifleştirin.
2.  **Veri Gönderimi:** n8n veya başka bir araç ile RSS verilerini çekin ve sitenizin `/wp-json/wp/v2/aggregated_news` adresine JSON formatında gönderin.
    - JSON içinde `external_url` alanı mutlaka olmalıdır.
3.  **Ayarlar:** WP Admin > N2N Aggregator ayarlarından yönlendirme modunu (Direct veya Interstitial) seçin.
4.  **Yayınlama:** Sayfalarınıza shortcode ekleyerek haber akışını gösterin.
