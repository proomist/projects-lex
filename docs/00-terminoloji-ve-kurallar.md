# Terminoloji ve Kurallar

Bu doküman, Avukat Ofis Yönetim Sistemi içerisindeki modüllerde kullanılan ortak terimleri ve iş kurallarını merkezî olarak tanımlar. Diğer tüm dokümanlar bu sözlüğe referans vererek tutarlılık sağlar.

## 1. Görev Yaşam Döngüsü

| Durum | Açıklama | Varsayılan Geçişler |
|---|---|---|
| Taslak | Oluşturulmuş ancak atanmadı | Taslak → Bekliyor |
| Bekliyor | Atanmış, başlama tarihi gelmedi | Bekliyor → Devam Ediyor / Beklemede |
| Devam Ediyor | Üzerinde çalışılıyor | Devam Ediyor → Beklemede / Tamamlandı / İptal |
| Beklemede | Dış koşul bekleniyor | Beklemede → Devam Ediyor / İptal |
| Tamamlandı | İş bitti, tamamlanma tarihi kaydedildi | Tamamlandı → Devam Ediyor (geri alınır) |
| İptal | Görev iptal edildi | İptal → (yalnızca yönetici onayıyla) Devam Ediyor |

> **Not:** Ayrıntılı durum açıklamaları için user story dosyalarındaki Negative/Edge Case tabloları geçerlidir; bu tablo yalnızca standart geçişleri özetler.

## 2. Alt Görev ve Kontrol Listesi Politikası

- Alt görevler tek seviye ile sınırlıdır; alt görevin altı oluşturulamaz.
- Alt görev farklı dosya veya müvekkile bağlanamaz; ana görevin bağlamı devralınır.
- Tüm alt görevler tamamlandığında ana görev otomatik kapanmaz; sistem uyarısı gösterir.
- Kontrol listeleri (checklist) hızlı adımlar içindir; veri doğrulaması ana görev alanları üzerinden yapılır.

## 3. Görev Bağımlılıkları

- Bir görev, başka bir görevin **Tamamlandı** olmasına bağlanabilir.
- Döngüsel bağımlılık (A→B→C→A) oluşturulması bloklanır.
- Bağımlı görev "Devam Ediyor" durumuna alınmak istendiğinde önceki görev tamamlanana kadar engellenir; yönetici override hakkı için onay penceresi gösterilir.
- Bağımlılık grafikleri yalnızca okuma amaçlıdır; sürükle-bırak düzenleme yapılmaz.

## 4. Hatırlatıcılar ve Uyarılar

- Hatırlatıcılar görev bitiş tarihine göre planlanır; bitiş tarihi olmayan göreve hatırlatıcı eklenemez.
- Aynı zaman damgasına sahip birden fazla hatırlatıcı tek bildirimde birleştirilir.
- Görevli değiştiğinde aktif hatırlatıcılar yeni görevliye aktarılır.
- 100+ eşzamanlı hatırlatıcı tetiklenirse kuyruk sistemi devreye girer; gönderim kronolojik yapılır.

## 5. Yetki ve Erişim Kuralları

- Rol/Yetki matrisi `docs/02-proje-plani.md` dosyasındaki tabloya göre yönetilir.
- User story dosyalarındaki "Yetki" kriterleri, bu matristeki izinlere dayalıdır. Yeni rol eklenirse önce matriste tanımlanmalı, ardından ilgili user story güncellenmelidir.

## 6. Mali Eşleştirme Politikası

- Tahsilat girişinde varsayılan davranış **otomatik FIFO**'dur (en eski vadeli alacak).
- Kullanıcı "manuel eşleştirme" seçerse FIFO devre dışı kalır ve kullanıcı hangi alacağa/kaç TL düşeceğini belirler.
- Avans tahsilatlar, bağlanacak alacak oluşana kadar "bekleyen avans" hesabında tutulur ve FIFO algoritmasına dâhil edilmez.

## 7. Raporlama ve Export Performans Eşiği

- 5.000 satırı aşan raporlar asenkron kuyruğa alınır; kullanıcıya "rapor hazır" bildirimi gelir.
- 50 MB üzerindeki export'lar otomatik sıkıştırılır veya parçalı dosya olarak sunulur.
- Rapor üretim süresi 2 saniyeyi geçerse sistem "arka planda hazırlanıyor" mesajı gösterir ve kullanıcı başka işlemlere devam edebilir.

## 8. Hata ve Loglama Politikası

- Her kritik iş akışı (görev atama, mali kayıt, rapor export) için başarısızlık durumunda kullanıcıya net hata mesajı, loglara teknik detay yazılır.
- Logların tutulması `docs/02-proje-plani.md` içindeki Aktivite Logu bölümüne uygun şekilde yapılır.

## 9. PHP ve Sunucu Geliştirme Politikası

- **Sürüm Standartları:** Backend **PHP 8.2+** üzerinde çalışır. Bağımlılıklar `Composer` ile yönetilir. Sadece HTTP routing ve middleware yönetimi için **Slim Framework 4** kullanılır. Devasa framework'lerden (Laravel/Symfony) kaçınılır.
- **Tip Güvenliği:** Projedeki tüm PHP dosyaları `declare(strict_types=1);` ile başlamak zorundadır. Gevşek tip kullanımına (loose typing) izin verilmez.
- **Katmanlı Mimari (ADR / Service Pattern):** Kodlar; `Controller/Action` (HTTP isteklerini alır), `Service` (İş kurallarını işler) ve `Repository` (Veritabanı ile konuşur) katmanlarına ayrılır. Controller katmanında SQL sorgusu yazılamaz veya iş kuralı (business logic) işletilemez.
- **Kod Kalitesi ve Standartlar:** Kodlama PSR-12 standardına uygun olmalıdır. Statik kod analizi için `PHPStan` (tercihen Level 8) kullanılarak tip uyumsuzlukları ve olası hatalar kod çalışmadan önce tespit edilmelidir.
- **Güvenlik Odaklı Geliştirme (OWASP):** Geliştirilen her modül, kod yazımı esnasında **OWASP Top 10** standartlarına göre tasarlanmalı ve kontrol edilmelidir. XSS koruması için çıktıların encode edilmesi, CSRF koruması için her state-changing işlemde token kontrolü zorunludur.
- **Test Politikası:** Projede otomatik birim (unit) veya entegrasyon **testleri yazılmayacaktır**. Bunun yerine geliştirici (veya AI), kodu üretirken edge-case'leri (istisnai durumları) ve OWASP güvenlik standartlarını koda dahil etmekle, doğrulama (validation) mekanizmalarını manuel olarak en üst düzeyde kurmakla yükümlüdür.

## 10. Veritabanı ve Şifreleme Kuralları

- **Veritabanı:** MariaDB (tercihen 10.11+ LTS veya 11.x) kullanılacaktır.
- **Veri Erişimi (Saf PDO):** Herhangi bir ORM (Eloquent, Doctrine vb.) veya Query Builder paketi **kullanılmayacaktır**. Veritabanı işlemleri sadece **Saf PDO** kullanılarak yapılacaktır.
- **SQL Injection Koruması:** PDO kullanılarak yazılan her sorgu istisnasız `prepare()` ve `execute()` yöntemleriyle (Prepared Statements) çalıştırılmalıdır. Değişkenler SQL string'i içine asla doğrudan (concatenation) eklenemez.
- **Parola Hashing:** Kullanıcı parolaları PHP'nin yerleşik `password_hash()` fonksiyonu ve **Argon2id** algoritması kullanılarak şifrelenecektir. Parolalar veritabanında düz metin veya MD5/SHA1 gibi güvensiz algoritmalarla asla tutulamaz.
- **Hassas Veri Şifreleme:** Kişisel veriler veya sistem dışı saklanması gereken hassas stringler **AES-256-GCM** algoritması ile OpenSSL eklentisi (`openssl_encrypt` / `openssl_decrypt`) kullanılarak şifrelenecek ve doğrulanacaktır. Şifreleme anahtarları (encryption keys) repo içinde değil, sunucu ortam değişkenlerinde saklanmalıdır.
- **Kimlik Doğrulama:** Stateless (Durumsuz) JWT (JSON Web Token) yapısı kullanılacaktır.

## 11. VDS Üzerinde Dağıtım ve Barındırma Politikası

- **Sunucu ve Web Servisi:** Proje yalnızca kullanıcıya ait Linux tabanlı VDS sunucularda barındırılır. Web sunucusu olarak sadece **Apache** kullanılacaktır (Nginx kurulmayacaktır). Gerekli URL yönlendirmeleri (URL Rewriting) için `.htaccess` dosyaları doğru yapılandırılmalıdır.
- **Bağımlılıklar:** Sunucuda PHP 8.2+ (ilgili PDO, OpenSSL, mbstring, json uzantıları ile birlikte), MariaDB ve Composer kurulu olmalıdır.
- **Dağıtım Akışı:** Geliştirici kodu yerelde tamamladıktan sonra sunucuya manuel veya SFTP ile aktarır. Sunucuda `composer install --no-dev --optimize-autoloader` çalıştırılarak bağımlılıklar canlı ortama uygun ve hızlı şekilde yüklenir.
- **Ortam Değişkenleri:** `.env` dosyası repoya dahil edilmez (gitignore'da bulunur). Tüm hassas bilgiler (DB şifreleri, JWT secret, AES anahtarları) sadece VDS üzerindeki `.env` dosyasında güvenli dosya izinleriyle (chmod 600) saklanır.

---

Bu dosya tüm modüller için referans niteliğindedir. Yeni bir terim veya politika eklendiğinde önce burada tanımlanmalı, ardından ilgili `docs` dosyaları güncellenmelidir.