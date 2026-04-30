# Avukat Ofis Yönetim Sistemi - Geliştirme Kuralları (rules.md)

Bu dosya, Avukat Ofis Yönetim Sistemi geliştirilirken istisnasız uyulması gereken **KATI** kuralları ve standartları belirler. Hiçbir modül veya özellik geliştirilirken bu kurallardan taviz verilemez.

---

## 1. Geliştirme ve Ortam Değişkenleri (Environment) Kuralları

Tüm ortam değişkenleri (environment variables) proje çalıştırılmadan önce `.env` dosyasından okunmalıdır.

- **APP_ENV:** Projenin çalışma ortamını belirler. `development` veya `production` değerlerini alır. Hata yakalama (error reporting) ve debug modları bu değişkene göre otomatik açılır/kapanır.
- **APP_DOMAIN:** Uygulamanın çalıştığı ana domain. (Örn: `https://hukuk.ofisim.com` veya `http://localhost:8080`). Tüm URL'ler ve yönlendirmeler bu temel alınarak oluşturulmalıdır.
- **APP_BASE_PATH:** Uygulamanın sunucuda bulunduğu alt dizin (eğer kök dizinde değilse). (Örn: `/app` veya `/`). Slim Framework'ün base path yapılandırmasında zorunludur.
- **Gizlilik:** `.env` dosyası repoya ASLA eklenmez. Yalnızca şablon olarak `.env.example` dosyası bulunur. Veritabanı şifreleri, JWT secret key, AES encryption key gibi hassas bilgiler yalnızca `.env` dosyasında tutulur.

## 2. Mimari ve Teknoloji Standartları

- **PHP 8.2+ ve Katı Tip Bildirimi:** Her PHP dosyasının ilk satırı istisnasız `declare(strict_types=1);` olmak zorundadır. Fonksiyon parametreleri ve dönüş tipleri (return types) kesin olarak belirtilmelidir.
- **Framework Kısıtlaması:** Sadece **Slim Framework 4** (routing, middleware) ve **PHP-DI** (Dependency Injection) kullanılacaktır. Laravel, Symfony, CodeIgniter gibi ağır framework'ler veya bileşenleri kullanılamaz.
- **Katmanlı Mimari (ADR):** İş kuralları asla Controller'lara (Action) yazılamaz. Her iş kuralı (business logic) kendi `Service` sınıfında, veritabanı işlemleri kendi `Repository` sınıfında olmalıdır.
- **Bağımlılık Enjeksiyonu:** Sınıflar, ihtiyaç duydukları diğer sınıfları kendi içlerinde `new` anahtar kelimesi ile oluşturamaz. Tüm bağımlılıklar kurucu (constructor) metodlar üzerinden enjekte edilmelidir (Dependency Injection).

## 3. Veritabanı ve PDO Kuralları

- **Sadece Saf PDO:** Veritabanı erişimi için ORM (Eloquent vb.) veya Query Builder kullanılamaz. PDO tabanlı bağlantı ve Repository yapısı kullanılacaktır.
- **SQL Injection Koruması:** Tüm SQL sorguları PDO'nun `prepare()` ve `execute()` metodları kullanılarak yazılmalıdır. Değişkenler asla SQL stringi içine doğrudan eklenemez.
- **Soft Delete:** Hiçbir veri fiziksel olarak silinmez (`DELETE FROM...`). Tüm tablolarda silme işlemi `is_deleted = 1` (veya `deleted_at` timestamp) flag'i güncellenerek yapılır.
- **İlişkisel Bütünlük:** Bir dosya silindiğinde/arşivlendiğinde, ona bağlı görevler, mali kayıtlar ve duruşmalar korunur, ancak statüleri pasife/arşive alınır.

## 4. Güvenlik ve Şifreleme Standartları

- **OWASP Top 10 Uyumluluğu:** Uygulama, XSS (Cross Site Scripting), CSRF (Cross-Site Request Forgery) ve IDOR (Insecure Direct Object Reference) gibi saldırılara karşı proaktif olarak korunmalıdır. Çıktılar (output) her zaman HTML encode edilerek ekrana basılır.
- **AES-256-GCM:** Veritabanında gizli kalması gereken kritik/hassas veriler (örn: TCKN, IBAN, özel notlar vb.) AES-256-GCM standardı kullanılarak şifrelenip veritabanına yazılmalı, okunurken çözülmelidir. Şifreleme anahtarı (APP_ENCRYPTION_KEY) `.env`'den alınır.
- **Parola Güvenliği:** Kullanıcı parolaları sadece PHP'nin `password_hash()` fonksiyonu ile **Argon2id** algoritması kullanılarak hashlenebilir.
- **JWT (JSON Web Token):** Oturum yönetimi durumsuz (stateless) olmalı ve JWT üzerinden yapılmalıdır. Token payload'unda asla hassas veri (parola vb.) taşınamaz.
- **Yetki Kontrolü:** Her endpoint'te mutlaka JWT doğrulama middleware'i olmalı; işlemi yapan kullanıcının, işlem yapmaya çalıştığı kaynağa yetkisi olup olmadığı (IDOR kontrolü) her serviste ayrıca kontrol edilmelidir.

## 5. Hata Yönetimi ve Loglama

- **Kullanıcıya Dost Mesajlar:** Hata durumlarında son kullanıcıya asla SQL hata mesajı veya PHP stack trace gösterilmez. Geriye `{"status": "error", "message": "Anlaşılır bir hata mesajı"}` formatında standart JSON dönülmelidir.
- **Teknik Loglama:** Kritik iş akışları (login denemesi, veri silme/güncelleme) ve teknik hatalar, `docs/02-proje-plani.md` içerisindeki aktivite logu politikasına göre veritabanına veya log dosyasına kaydedilir.
- **İstisnalar (Exceptions):** Özel iş kuralı hataları için özel Exception sınıfları (Örn: `InsufficientBalanceException`, `UnauthorizedActionException`) oluşturulmalı ve try-catch bloklarında bu tipler üzerinden hata yakalanmalıdır.

## 6. Geliştirme Süreci Onay Kuralı

- Geliştirici (AI dahil), herhangi bir yeni modül, dosya veya özellik kodlamasına başlamadan önce **mutlaka kullanıcıya bir plan sunmalı ve açık onay beklemelidir**. Varsayımda bulunularak kod yazılamaz.
- Kod önerilerinde her zaman tam, çalışır ve bu belgedeki güvenlik (AES, PDO, OWASP) kurallarına uygun bloklar üretilecektir. "Mutlu senaryo" (happy path) ile bırakılamaz; validation ve hata yönetimi koda entegre edilmek zorundadır.
