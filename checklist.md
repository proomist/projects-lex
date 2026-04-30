# Proje Geliştirme Checklist (PHP + MariaDB)

Bu doküman, `docs/07-yol-haritasi.md` ve sistem analiz dokümanlarına (`docs/01-ozellik-analizi.md`, `docs/02-proje-plani.md`) dayanarak oluşturulmuş eksiksiz geliştirme kontrol listesidir.

---

## Aşama 0: Ortam Kurulumu ve Altyapı
- [ ] VDS sunucuya (Apache) domain yönlendirmelerinin yapılması.
- [ ] VDS üzerinde PHP 8.2+ ve MariaDB 11.x kurulumlarının tamamlanması.
- [ ] `.env.example` dosyasının oluşturulması (Geliştirme, domain, path, DB, AES key değişkenleri dahil).
- [ ] Composer init ve `slim/slim`, `php-di/php-di`, `firebase/php-jwt`, `vlucas/valitron` paketlerinin yüklenmesi.
- [ ] Klasör yapısının oluşturulması (src/Controller, src/Service, src/Repository, public/ vs.).
- [ ] `public/index.php` ve `.htaccess` yapılandırmalarının tamamlanması.
- [ ] MariaDB veritabanı şemasının (Migration scriptleri ile veya manuel) oluşturulması (Müvekkil, Dosya, Görev, Mali, User, Log vb. tablolar).
- [ ] `Database` connection wrapper'ının (Saf PDO) singleton olarak ayağa kaldırılması.

## Aşama 1: Kullanıcı ve Yetki Yönetimi (Auth Modülü)
- [ ] **Tablo/Şema:** `users`, `roles`, `permissions`, `user_logs`
- [ ] `password_hash()` (Argon2id) ile kullanıcı kayıt servisinin yazılması (Kurucu / İlk Admin).
- [ ] JWT tabanlı Login (Kimlik Doğrulama) servisinin yazılması.
- [ ] Token yenileme (Refresh Token) veya geçerlilik süresi (Expiration) mantığının kurulması.
- [ ] `AuthMiddleware`'in yazılması (Gelen her requestte JWT doğrulama).
- [ ] Rol bazlı `RoleMiddleware` veya guard'ların yazılması (`docs/02-proje-plani.md` Modül 1.3 ve 1.4'e göre).
- [ ] Başarısız login denemeleri için rate limiting (Throttle) ve aktivite loglarının (Aktivite Logu) DB'ye yazılma mekanizmasının kurulması.
- [ ] Kullanıcı (Avukat, Stajyer, Sekreter vb.) CRUD işlemlerinin (Sistem Yöneticisi yetkisine bağlı) tamamlanması.

## Aşama 2: Müvekkil Yönetimi
- [ ] **Tablo/Şema:** `clients`, `client_contacts` (telefon/mail/adres), `client_relations` (bağlantılı kişiler).
- [ ] Hassas verilerin (TCKN, İletişim vs.) `AES-256-GCM` ile şifrelenip DB'ye yazılması/okunması mekanizmasının entegrasyonu.
- [ ] Müvekkil Kartı oluşturma (Gerçek Kişi / Tüzel Kişi ayrımlı form ve servis mantığı).
- [ ] Çoklu iletişim bilgilerinin (Telefon, Adres, E-Posta) yönetimi servisleri.
- [ ] Müvekkil durumu (Aktif/Pasif/Kara Liste) güncelleme servisleri.
- [ ] Müvekkil arama, listeleme ve filtreleme (Pagination ile) endpoint'leri.

## Aşama 3: Dosya ve Dava Yönetimi
- [ ] **Tablo/Şema:** `cases`, `case_parties` (karşı taraf), `hearings` (duruşmalar), `case_documents` (belgeler).
- [ ] Dosya açma (Dava, İcra, Danışmanlık, Arabuluculuk türlerine göre) servisinin oluşturulması.
- [ ] "Dosya Numaralama" (Örn: 2025-0001) otomatik jeneratör servisinin yazılması.
- [ ] Dosya durumu (Aktif, Beklemede, Karar Aşaması, Kapandı, Arşiv) geçiş servisleri ve validasyonları.
- [ ] Duruşma kaydı ekleme, düzenleme ve listeleme (Mahkeme, salon, saat detaylı).
- [ ] Duruşma sonucu/notu ekleme servisleri.
- [ ] Dosyaya avukat/stajyer/sekreter atama (ve yetki kapsamı) ayarları.
- [ ] Belge/Evrak modülü: Dosya upload (güvenli dizine), boyut/mime-type kontrolü ve DB'ye metadata kaydı.

## Aşama 4: Mali Takip (Alacak-Borç)
- [ ] **Tablo/Şema:** `financial_transactions`, `installments` (ödeme planları).
- [ ] Alacak Kaydı (Vekalet ücreti, masraf avansı vb.) servisinin oluşturulması (Vade tarihi, tutar).
- [ ] Gider/Masraf (Harç, bilirkişi, keşif) giriş servisleri.
- [ ] Tahsilat (Gelen para) giriş servisleri.
- [ ] **Kritik Algoritma:** Tahsilat eşleştirme (Manuel veya Otomatik FIFO) mantığının yazılması.
- [ ] Bakiye Hesaplama Servisi: Dosya bazlı ve Müvekkil bazlı dinamik bakiye hesaplama metodunun yazılması (DB view veya aggregate query).
- [ ] Taksitli ödeme planı oluşturma jeneratörü.

## Aşama 5: İş Listesi, Görev Yönetimi ve Hatırlatıcılar
- [ ] **Tablo/Şema:** `tasks`, `subtasks`, `reminders`.
- [ ] Görev (Dilekçe hazırlığı, müvekkil görüşmesi vb.) oluşturma, atama ve öncelik (Acil/Normal) ayarlama servisleri.
- [ ] Görev durumu (Bekliyor, Devam Ediyor, Tamamlandı) update mekanizması.
- [ ] Alt görev (Subtask) ve Checklist (Kontrol listesi) yapıları.
- [ ] Hatırlatıcılar (Reminders) altyapısı: Cron tabanlı (VDS sunucu cron) hatırlatıcı tetikleyici betiğinin yazılması.

## Aşama 6: Raporlama ve Dashboard
- [ ] Performans ve maliyet optimizasyonlu rapor SQL query'lerinin yazılması (Çoklu JOIN işlemleri).
- [ ] Ana Dashboard API: Aktif dosya, aylık duruşma, güncel tahsilat/alacak özetini çeken tek endpoint.
- [ ] Vadesi geçen alacaklar (Aging report) ve yaşlandırma algoritması API'si.
- [ ] Takvim API'si: İstenen tarih aralığındaki duruşma, görev ve hatırlatıcıları birleştiren veri seti endpoint'i.
- [ ] 5000+ satırlı raporlar için asenkron (veya parçalı) CSV/Excel export işlemleri.
- [ ] Sistem loglarının ve hata metriklerinin yetkiliye raporlanması ekranı.

## Aşama 7: Son Güvenlik Kontrolleri ve Deploy
- [ ] OWASP Top 10 Audit (Manuel kod incelemesi, SQLi, XSS, CSRF, IDOR).
- [ ] Dosya izinlerinin (chmod/chown) ve ortam değişkenlerinin canlıya uygun konfigürasyonu.
- [ ] PWA, Cache ve Web performansı kontrolü.
- [ ] Uygulamanın dev moddan prod moda geçişi ve yayına alınması.
