# projects-lex (Proomist LEX)

**Proomist LEX**, hukuk büroları ve avukatlar için özel olarak geliştirilmiş, operasyonel süreçleri dijitalleştiren ve merkezi bir noktadan yönetilmesini sağlayan kapsamlı bir **Avukat Ofis Yönetim Sistemi**dir.

## 🌟 Temel Özellikler

- **👥 Müvekkil Yönetimi:** Gerçek ve tüzel kişi müvekkillerin iletişim bilgileri, vergi/TC kimlik numaraları ve ilişkili dosyalarının tek ekrandan takibi.
- **📂 Dosya ve Dava Takibi:** Açık ve kapalı dava dosyaları, icra dosyaları ve danışmanlık dosyalarının oluşturulması; esas no, mahkeme ve taraf bilgilerinin yönetimi.
- **⚖️ Duruşma Takvimi:** Dosyalara bağlı duruşmaların tarihi, saati, mahkemesi ve duruşma notlarının takibi. Yaklaşan duruşmalar için hatırlatmalar.
- **✅ İş Listesi ve Görev Yönetimi:** Avukatlar ve personel arasında görev atamaları, bitiş tarihleri, öncelik durumları ve checklist destekli görev takibi.
- **📄 Evrak ve Belge Yönetimi:** Dosyalara ve müvekkillere ait dilekçe, sözleşme, karar gibi dokümanların güvenli şekilde yüklenmesi ve saklanması.
- **💰 Mali Takip ve Finans:** Alacak, borç, masraf ve tahsilatların yönetimi. Müvekkil bazlı cari hesap ekstreleri oluşturma, dağıtım (şelale) modeli ile vekalet ücreti ve emanet takibi.
- **📊 Raporlama ve Analizler:** Dava durumları, finansal özetler ve iş yükü analizlerini içeren detaylı rapor ekranları.
- **🔐 Rol Tabanlı Yetkilendirme:** Kurucu Ortak, Ortak Avukat, Muhasebeci, Avukat ve Sistem Yöneticisi gibi farklı rollerle modül bazlı erişim kontrolü (RBAC).
- **🛡️ Güvenlik ve Loglama:** Detaylı işlem logları (Activity Logs), hata izleme (Error Logs), JWT tabanlı güvenli oturum yönetimi ve Strict CSP (Content Security Policy) politikaları.

## 💻 Teknoloji Yığını

### Backend
- **Dil:** PHP 8.x
- **Framework:** Slim Framework v4
- **Mimari:** MVC / RESTful API + SSR (Server-Side Rendering)
- **Şablon Motoru:** Twig
- **Kimlik Doğrulama:** Firebase JWT (JSON Web Tokens) - HttpOnly Cookie
- **Validasyon:** Valitron
- **Ek Kütüphaneler:** Dompdf (PDF Çıktıları), Symfony Mailer (E-posta Gönderimi)

### Frontend
- **Tasarım:** Tailwind CSS (Özelleştirilmiş Tema, Hızlı UI)
- **İkonlar:** Lucide Icons
- **Script:** Vanilla JavaScript (API entegrasyonu, modaller, asenkron işlemler)

### Veritabanı
- **Sürücü:** PDO (PHP Data Objects) destekli yapısıyla MySQL, PostgreSQL veya SQLite desteği.

## 🚀 Kurulum

Projeyi yerel ortamınızda veya sunucunuzda çalıştırmak için aşağıdaki adımları izleyin.

### Gereksinimler
- PHP 8.1 veya üzeri
- Composer
- Web Sunucusu (Apache/Nginx)
- Veritabanı Sunucusu (MySQL/MariaDB veya SQLite)

### Adımlar

1. **Repoyu Klonlayın:**
   ```bash
   git clone https://github.com/proomist/projects-lex.git
   cd projects-lex
   ```

2. **Bağımlılıkları Yükleyin:**
   ```bash
   composer install
   ```

3. **Çevre Değişkenlerini Ayarlayın:**
   Projenin güvenlik gereksinimleri gereği `.env` dosyası repoda bulunmaz.
   
   **`.env` İçerisindeki Gizli Anahtarların (Secrets) Üretilmesi:**
   Oluşturduğunuz `.env` dosyasını bir metin editörüyle açın ve aşağıdaki alanları güvenli bir şekilde doldurun:
   - `DB_PASS`: MariaDB/MySQL veritabanı parolanız.
   - `JWT_SECRET_KEY`: Kullanıcı oturumlarını (token) şifrelemek için kullanılır. **En az 64 karakter** uzunluğunda, harf ve rakamlardan oluşan rastgele bir dize olmalıdır. (Terminalden `openssl rand -base64 48` veya `openssl rand -hex 32` ile üretebilirsiniz).
   - `AES_ENCRYPTION_KEY`: Hassas verileri veritabanında şifrelemek için kullanılır. AES-256 standardı için **tam olarak 32 karakter** olmalıdır. (Terminalden `openssl rand -base64 24` ile üretebilirsiniz).
   
   > [!WARNING]
   > Bu anahtarları (`JWT_SECRET_KEY` ve `AES_ENCRYPTION_KEY`) belirleyip projeyi ayağa kaldırdıktan sonra **kaybetmemelisiniz**. Özellikle `AES_ENCRYPTION_KEY` değerini değiştirirseniz, veritabanındaki mevcut şifrelenmiş verileri (TC No vb.) bir daha okuyamazsınız! Şifrelerinizi 1Password veya Bitwarden gibi güvenli bir yerde yedeklemeyi unutmayın.

4. **Veritabanını Kurun:**
   Eğer projenizde bir SQL export dosyası varsa (örn: `legal_db_export.sql`), bu dosyayı oluşturduğunuz veritabanına içe aktarın.

5. **Geliştirme Sunucusunu Başlatın:**
   Dahili PHP sunucusu ile projeyi hemen test edebilirsiniz:
   ```bash
   php -S localhost:8000 -t public
   ```
   Tarayıcınızdan `http://localhost:8000` adresine giderek uygulamaya erişebilirsiniz.

## 🧪 Testler
Projeye ait birim ve entegrasyon testlerini çalıştırmak için **Pest PHP** kullanılmaktadır.
```bash
composer test
```

## 🔒 Güvenlik Notları
- Proje, session tabanlı açıklar yerine JWT tabanlı `HttpOnly` çerezleri kullanarak XSS saldırılarına karşı korunmaktadır.
- Özel bir `SecurityHeadersMiddleware` ile güçlü `Content-Security-Policy`, `X-Frame-Options` ve `X-XSS-Protection` kuralları uygulanmaktadır.
- Kritik veri ekleme/güncelleme rotaları `RateLimitMiddleware` ile brute-force saldırılarına karşı korunmaktadır.

## 📜 Lisans
Bu projenin tüm hakları saklıdır. Kullanım ve dağıtım koşulları için proje yetkilisi ile iletişime geçiniz.
# projects-lex
