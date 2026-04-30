# AVUKAT OFİS YÖNETİM SİSTEMİ

## User Stories, Acceptance Criteria, Negative Cases ve Edge Cases

### Teknik Dokümantasyon

**Versiyon:** 1.0  
**Tarih:** Şubat 2025

---

# İÇİNDEKİLER

1. [KULLANICI VE YETKİ YÖNETİMİ](#modül-1-kullanici-ve-yetki-yönetimi)
2. [MÜVEKKİL YÖNETİMİ](#modül-2-müvekkil-yönetimi)
3. [DOSYA/DAVA YÖNETİMİ](#modül-3-dosyadava-yönetimi)
4. [İŞ LİSTESİ VE GÖREV YÖNETİMİ](#modül-4-iş-listesi-ve-görev-yönetimi)
5. [MALİ TAKİP (ALACAK-BORÇ)](#modül-5-mali-takip-alacak-borç)
6. [RAPORLAMA VE DASHBOARD](#modül-6-raporlama-ve-dashboard)

---

# MODÜL 1: KULLANICI VE YETKİ YÖNETİMİ

## US-1.1: Kullanıcı Girişi

**User Story**

Bir kullanıcı olarak, kullanıcı adı ve şifremle sisteme giriş yapmak istiyorum ki yetkilendirilmiş işlemlerimi gerçekleştirebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Kullanıcı adı ve şifre alanları zorunludur, boş bırakılamaz |
| AC2 | Doğru bilgilerle giriş yapıldığında ana sayfaya yönlendirilir |
| AC3 | Hatalı bilgilerle girişte "Kullanıcı adı veya şifre hatalı" mesajı gösterilir |
| AC4 | Giriş sonrası son giriş tarihi güncellenir |
| AC5 | Oturum süresi dolduğunda otomatik çıkış yapılır ve login sayfasına yönlendirilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Kullanıcı adı boş, şifre dolu gönderilir | "Kullanıcı adı zorunludur" hatası |
| NC2 | Kullanıcı adı dolu, şifre boş gönderilir | "Şifre zorunludur" hatası |
| NC3 | Sistemde olmayan kullanıcı adı girilir | "Kullanıcı adı veya şifre hatalı" (güvenlik için spesifik değil) |
| NC4 | Doğru kullanıcı adı, yanlış şifre girilir | "Kullanıcı adı veya şifre hatalı" |
| NC5 | Pasif durumdaki kullanıcı giriş yapmaya çalışır | "Hesabınız aktif değil, yönetici ile iletişime geçin" |
| NC6 | SQL injection denemesi yapılır | Giriş reddedilir, log kaydı oluşturulur |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Kullanıcı adında Türkçe karakter var (şükrü) | Normal şekilde giriş yapılabilir |
| EC2 | Şifrede özel karakterler var (!@#$%^&*) | Normal şekilde doğrulanır |
| EC3 | Aynı anda iki farklı tarayıcıdan giriş | Sistem ayarına göre: izin ver veya ilk oturumu sonlandır |
| EC4 | Giriş sırasında internet kesilir | İşlem timeout olur, hata mesajı gösterilir |
| EC5 | Çok uzun kullanıcı adı girilir (1000+ karakter) | Maksimum karakter sınırı uygulanır |
| EC6 | Caps Lock açıkken şifre girilir | Kullanıcıya Caps Lock uyarısı gösterilir |

---

## US-1.2: Başarısız Giriş Kilidi

**User Story**

Sistem yöneticisi olarak, brute force saldırılarını önlemek için belirli sayıda başarısız girişten sonra hesabın geçici olarak kilitlenmesini istiyorum.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | 5 başarısız giriş denemesinden sonra hesap 15 dakika kilitlenir |
| AC2 | Kilitli hesaba giriş denendiğinde kalan süre gösterilir |
| AC3 | Kilit süresi dolduktan sonra başarısız deneme sayacı sıfırlanır |
| AC4 | Başarılı giriş yapıldığında deneme sayacı sıfırlanır |
| AC5 | Her başarısız deneme loglanır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Kilitli hesaba doğru şifreyle giriş denenir | Kilit mesajı gösterilir, giriş yapılamaz |
| NC2 | Farklı IP adreslerinden aynı hesaba saldırı | Tüm IP'lerden denemeler sayılır, hesap kilitlenir |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | 4 başarısız denemeden sonra 15 dakika beklenir | Sayaç sıfırlanmaz, 5. denemede kilitlenir (sayaç timeout süresi ayrı olmalı) |
| EC2 | Kilit süresi tam dolmak üzereyken giriş denenir | Hala kilitli mesajı gösterilir |
| EC3 | Sunucu saati değiştirilir/saat farkı oluşur | Kilit süresi sunucu saatine göre hesaplanır |
| EC4 | Hesap kilitlendikten sonra şifre sıfırlama istenir | Şifre sıfırlama kilidi etkilemez, mail gönderilir |

---

## US-1.3: Kullanıcı Oluşturma

**User Story**

Sistem yöneticisi olarak, yeni kullanıcı oluşturmak istiyorum ki ofise katılan personel sistemi kullanabilsin.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Kullanıcı adı benzersiz olmalıdır |
| AC2 | E-posta adresi geçerli formatta olmalıdır |
| AC3 | Şifre minimum 8 karakter, en az 1 büyük harf, 1 küçük harf, 1 rakam içermelidir |
| AC4 | En az bir rol atanmalıdır |
| AC5 | Oluşturma sonrası kullanıcıya bilgilendirme e-postası gönderilir |
| AC6 | Yeni kullanıcı varsayılan olarak "Aktif" durumundadır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Mevcut kullanıcı adıyla kayıt denenir | "Bu kullanıcı adı zaten kullanılıyor" hatası |
| NC2 | Geçersiz e-posta formatı girilir (test@) | "Geçerli bir e-posta adresi girin" hatası |
| NC3 | Şifre politikasına uymayan şifre girilir (123456) | "Şifre en az 8 karakter, büyük/küçük harf ve rakam içermelidir" |
| NC4 | Rol atamadan kayıt denenir | "En az bir rol seçmelisiniz" hatası |
| NC5 | Yetkisiz kullanıcı (avukat) kullanıcı oluşturmaya çalışır | "Bu işlem için yetkiniz bulunmuyor" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Kullanıcı adı sadece rakamlardan oluşur (12345) | Kabul edilir (iş kuralına göre değişebilir) |
| EC2 | Kullanıcı adı maksimum uzunlukta (50 karakter) | Kabul edilir |
| EC3 | Kullanıcı adı minimum uzunlukta (3 karakter) | Kabul edilir |
| EC4 | E-posta subdomainli (user@mail.law.firm.com) | Kabul edilir |
| EC5 | Şifre tam minimum gereksinimleri karşılar (Abcdefg1) | Kabul edilir |
| EC6 | Kullanıcı adında boşluk var (ali veli) | Reddedilir, boşluk kullanılamaz |
| EC7 | Aynı e-posta ile ikinci kullanıcı oluşturulur | İş kuralına göre: izin ver veya reddet |
| EC8 | Çoklu rol atanır (Avukat + Muhasebeci) | Kabul edilir, yetkiler birleştirilir |

---

## US-1.4: Rol Tanımlama

**User Story**

Sistem yöneticisi olarak, özel roller tanımlayıp bu rollere yetkiler atamak istiyorum ki farklı personel gruplarının erişimlerini yönetebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Rol adı benzersiz olmalıdır |
| AC2 | Rol açıklaması opsiyoneldir |
| AC3 | Role en az bir yetki atanabilir |
| AC4 | Varsayılan roller silinemez, sadece yetkileri değiştirilebilir |
| AC5 | Rol silindiğinde o role sahip kullanıcılar varsayılan role geçer |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Mevcut rol adıyla yeni rol oluşturulur | "Bu rol adı zaten mevcut" hatası |
| NC2 | Varsayılan rol (Sistem Yöneticisi) silinmeye çalışılır | "Varsayılan roller silinemez" hatası |
| NC3 | Kullanıcıya atanmış rol silinir | Uyarı gösterilir, onay sonrası kullanıcılar varsayılan role aktarılır |
| NC4 | Boş isimle rol oluşturulur | "Rol adı zorunludur" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Role hiç yetki atanmadan kaydedilir | Kabul edilir, kullanıcı hiçbir işlem yapamaz |
| EC2 | Rol adı çok uzun (200+ karakter) | Maksimum 100 karakter sınırı uygulanır |
| EC3 | Rol adı özel karakterler içerir (Avukat@Kıdemli) | Kabul edilir veya filtrelenir (iş kuralı) |
| EC4 | Aynı anda iki admin aynı rolü düzenler | Son kaydeden kazanır veya çakışma uyarısı |
| EC5 | Tüm yetkiler tek role atanır | Kabul edilir |
| EC6 | Rol 100+ kullanıcıya atanmışken silinir | Performans sorunu oluşabilir, batch işlem yapılmalı |

---

## US-1.5: Şifre Sıfırlama

**User Story**

Bir kullanıcı olarak, şifremi unuttuğumda e-posta ile sıfırlama bağlantısı almak istiyorum ki hesabıma tekrar erişebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Kayıtlı e-posta adresine sıfırlama linki gönderilir |
| AC2 | Sıfırlama linki 24 saat geçerlidir |
| AC3 | Link kullanıldıktan sonra geçersiz olur |
| AC4 | Yeni şifre mevcut şifreyle aynı olamaz |
| AC5 | Şifre değiştirildikten sonra tüm aktif oturumlar sonlandırılır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Sistemde olmayan e-posta ile sıfırlama istenir | Güvenlik için aynı mesaj: "E-posta adresinize talimatlar gönderildi" |
| NC2 | Süresi dolmuş link kullanılır | "Bu bağlantının süresi dolmuş" hatası |
| NC3 | Aynı link ikinci kez kullanılır | "Bu bağlantı daha önce kullanılmış" hatası |
| NC4 | Eski şifre ile aynı yeni şifre girilir | "Yeni şifre eski şifrenizden farklı olmalıdır" |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Kullanıcı 5 dakika içinde 10 kez sıfırlama ister | Rate limiting uygulanır, beklemesi istenir |
| EC2 | E-posta sunucusu çalışmıyor | Kullanıcıya hata mesajı, admin'e bildirim |
| EC3 | Link'e tıklanmadan 23 saat 59 dakika geçer | Hala geçerli |
| EC4 | Sıfırlama sırasında hesap pasife alınır | İşlem reddedilir |
| EC5 | Mobil cihazda e-postadaki link açılır | Mobil uyumlu sıfırlama sayfası gösterilir |

---

## US-1.6: Yetki Kontrolü

**User Story**

Sistem olarak, her işlemde kullanıcının yetkisini kontrol etmek istiyorum ki yetkisiz erişim engellensin.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Yetkisiz işlem denemesi loglanır |
| AC2 | Yetkisiz erişimde kullanıcı dostu hata mesajı gösterilir |
| AC3 | Menüde sadece yetkili olduğu modüller görünür |
| AC4 | URL ile doğrudan yetkisiz sayfaya erişim engellenir |
| AC5 | API çağrılarında yetki kontrolü yapılır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Stajyer, kullanıcı yönetimi sayfasına URL ile erişmeye çalışır | 403 Forbidden, ana sayfaya yönlendirme |
| NC2 | Sekreter, mali rapor API'sini çağırır | 403 yanıtı, log kaydı |
| NC3 | Avukat, atanmadığı dosyayı görüntülemeye çalışır | "Bu dosyaya erişim yetkiniz yok" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Kullanıcının rolü işlem sırasında değiştirilir | Mevcut oturum eski yetkilerle devam eder, sonraki girişte güncellenir |
| EC2 | Kullanıcının tüm rolleri kaldırılır | Hiçbir işlem yapamaz, sadece dashboard görür |
| EC3 | Rol yetkisi kaldırılırken kullanıcı aktif işlem yapıyor | İşlem tamamlanır, sonraki işlem engellenir |
| EC4 | Admin kendi admin yetkisini kaldırır | Engellenir: "Kendi yetkinizi kaldıramazsınız" |
| EC5 | Sistemdeki son admin silinmeye çalışılır | Engellenir: "Sistemde en az bir yönetici olmalıdır" |

---

# MODÜL 2: MÜVEKKİL YÖNETİMİ

## US-2.1: Yeni Müvekkil Kaydı (Gerçek Kişi)

**User Story**

Bir avukat olarak, yeni gerçek kişi müvekkil kaydı oluşturmak istiyorum ki müvekkilimin bilgilerini sistemde saklayabileyim ve dosyalarına bağlayabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | TC Kimlik No 11 haneli olmalı ve doğrulama algoritmasından geçmeli |
| AC2 | Ad ve soyad alanları zorunludur |
| AC3 | En az bir iletişim bilgisi (telefon veya e-posta) girilmelidir |
| AC4 | Müvekkil kodu otomatik oluşturulur (M-2025-0001 formatı) |
| AC5 | Aynı TC Kimlik No ile mükerrer kayıt engellenmelidir |
| AC6 | Kayıt sonrası müvekkil detay sayfasına yönlendirilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | TC Kimlik No 10 haneli girilir | "TC Kimlik No 11 haneli olmalıdır" hatası |
| NC2 | TC Kimlik No harf içerir (1234567890A) | "TC Kimlik No sadece rakam içermelidir" hatası |
| NC3 | Algoritma geçersiz TC girilir (11111111111) | "Geçersiz TC Kimlik No" hatası |
| NC4 | Ad alanı boş bırakılır | "Ad alanı zorunludur" hatası |
| NC5 | Soyad alanı boş bırakılır | "Soyad alanı zorunludur" hatası |
| NC6 | Telefon ve e-posta boş bırakılır | "En az bir iletişim bilgisi girilmelidir" hatası |
| NC7 | Sistemde kayıtlı TC ile yeni kayıt denenir | "Bu TC Kimlik No ile kayıtlı müvekkil mevcut: [Ad Soyad]" uyarısı |
| NC8 | Yetkisiz kullanıcı (stajyer) müvekkil eklemeye çalışır | "Bu işlem için yetkiniz bulunmuyor" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | TC Kimlik No 0 ile başlıyor (01234567890) | Kabul edilir, 0 ile başlayan TC'ler geçerlidir |
| EC2 | Ad alanına tek karakter girilir (A) | Kabul edilir veya minimum 2 karakter kuralı |
| EC3 | Soyad birden fazla kelime içerir (Öztürk Yılmaz) | Kabul edilir |
| EC4 | Ad alanında rakam var (Ali2) | Reddedilir, "Ad sadece harf içermelidir" |
| EC5 | İsimde özel Türkçe karakterler (İ, Ş, Ğ, Ü, Ö, Ç) | Doğru şekilde kaydedilir ve görüntülenir |
| EC6 | Doğum tarihi gelecekte girilir | "Doğum tarihi gelecek bir tarih olamaz" hatası |
| EC7 | Doğum tarihi 150 yıl önce girilir | Uyarı gösterilir ama kabul edilir |
| EC8 | Aynı anda iki kullanıcı aynı TC ile kayıt yapar | İlk kaydeden başarılı, ikinciye mükerrer hatası |
| EC9 | Müvekkil kodu yıl değişiminde sıfırlanır | M-2026-0001 olarak devam eder |
| EC10 | Form doldurulurken oturum süresi dolar | Kaydedilmemiş veri kaybolur, uyarı gösterilmeli |

---

## US-2.2: Yeni Müvekkil Kaydı (Tüzel Kişi)

**User Story**

Bir avukat olarak, şirket müvekkil kaydı oluşturmak istiyorum ki kurumsal müvekkillerimi yönetebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Vergi numarası 10 veya 11 haneli olmalıdır |
| AC2 | Ticaret unvanı zorunludur |
| AC3 | Yetkili kişi adı soyadı zorunludur |
| AC4 | Aynı vergi numarası ile mükerrer kayıt engellenmelidir |
| AC5 | Vergi dairesi seçimi yapılabilmelidir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Vergi numarası 9 haneli girilir | "Vergi numarası 10 veya 11 haneli olmalıdır" hatası |
| NC2 | Ticaret unvanı boş bırakılır | "Ticaret unvanı zorunludur" hatası |
| NC3 | Yetkili kişi bilgisi girilmez | "Yetkili kişi bilgisi zorunludur" hatası |
| NC4 | Mevcut vergi numarası ile kayıt denenir | "Bu vergi numarası ile kayıtlı müvekkil mevcut" uyarısı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Ticaret unvanı çok uzun (500+ karakter) | Maksimum 300 karakter sınırı |
| EC2 | Şahıs şirketi kaydedilir (11 haneli VKN = TC) | Her iki alan da aynı değerle doldurulabilir |
| EC3 | Yabancı şirket kaydedilir (VKN yok) | "Yabancı Şirket" seçeneği ile VKN zorunluluğu kalkar |
| EC4 | Yetkili kişi aynı zamanda gerçek kişi müvekkil | Bağlantı kurulabilir, mükerrer uyarısı |
| EC5 | Şirket birleşmesi sonrası unvan değişikliği | Güncelleme yapılabilir, eski unvan tarihçede saklanır |
| EC6 | Mersis numarası formatı yanlış girilir | Format kontrolü ve düzeltme önerisi |

---

## US-2.3: Müvekkil Arama ve Listeleme

**User Story**

Bir avukat olarak, müvekkilleri hızlıca aramak ve listelemek istiyorum ki ihtiyacım olan müvekkil kaydına kolayca ulaşabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Ad, soyad, TC, vergi no, telefon ile arama yapılabilir |
| AC2 | Arama sonuçları anlık güncellenir (en az 3 karakter sonrası) |
| AC3 | Sonuçlar sayfalanır (varsayılan 20 kayıt) |
| AC4 | Durum, tür ve sorumlu avukata göre filtreleme yapılabilir |
| AC5 | Sonuçlar ad, kayıt tarihi, bakiyeye göre sıralanabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Eşleşen kayıt olmayan terim aranır | "Sonuç bulunamadı" mesajı, arama önerileri |
| NC2 | Sadece 1-2 karakter ile arama yapılır | "En az 3 karakter girin" uyarısı |
| NC3 | Stajyer tüm müvekkilleri görmeye çalışır | Sadece atandığı dosyaların müvekkillerini görür |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Arama terimi Türkçe karakter içerir (Şükrü) | Doğru sonuçlar döner |
| EC2 | Arama büyük/küçük harf duyarsız (ALİ = ali = Ali) | Tüm varyasyonlar bulunur |
| EC3 | TC numarasının son 4 hanesi ile arama | Sonuçlar döner (iş kuralına göre) |
| EC4 | Telefon numarası farklı formatlarla arama (5551234567, 555-123-45-67) | Normalize edilip eşleştirilir |
| EC5 | 10.000+ müvekkil içinde arama | Performans 2 saniyenin altında kalmalı |
| EC6 | Arama sırasında internet kesilir | Son sonuçlar gösterilir, bağlantı uyarısı |
| EC7 | Özel karakterle arama (*ali*, %test%) | Özel karakterler escape edilir, güvenlik sağlanır |
| EC8 | Silinmiş (arşivlenmiş) müvekkil aranır | Varsayılanda görünmez, "Arşivi dahil et" filtresi ile görünür |

---

## US-2.4: Müvekkil Bilgi Güncelleme

**User Story**

Bir avukat olarak, müvekkil bilgilerini güncellemek istiyorum ki kayıtlar her zaman güncel kalsın.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Tüm alanlar düzenlenebilir (TC/VKN hariç) |
| AC2 | TC Kimlik No değişikliği için yönetici onayı gerekir |
| AC3 | Değişiklikler tarihçede saklanır |
| AC4 | Kaydetmeden çıkışta onay istenir |
| AC5 | Güncelleme sonrası başarı mesajı gösterilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Zorunlu alan silinerek kaydedilir | "Ad alanı zorunludur" hatası |
| NC2 | Geçersiz e-posta formatına güncellenir | "Geçerli bir e-posta adresi girin" hatası |
| NC3 | Yetkisiz kullanıcı güncelleme yapar | "Bu işlem için yetkiniz bulunmuyor" hatası |
| NC4 | Başka kullanıcının düzenlediği kayıt kaydedilir | "Bu kayıt başka bir kullanıcı tarafından değiştirildi" uyarısı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Tüm telefon numaraları silinir, en az biri olmalı kuralı | "En az bir iletişim bilgisi olmalıdır" hatası |
| EC2 | Birincil telefon silinir, başka telefon var | Kalan telefonlardan biri otomatik birincil olur |
| EC3 | Müvekkil türü değiştirilir (gerçek → tüzel) | Uyarı gösterilir, uygun alanlar güncellenir |
| EC4 | Çok uzun not girilir (10.000+ karakter) | Maksimum karakter sınırı veya kabul |
| EC5 | Değişiklik yapılmadan kaydet tıklanır | "Değişiklik yapılmadı" bilgisi, gereksiz kayıt önlenir |
| EC6 | Form açıkken müvekkil başka kullanıcı tarafından silinir | Kaydetmeye çalışınca "Kayıt bulunamadı" hatası |

---

## US-2.5: Müvekkil Arşivleme

**User Story**

Bir avukat olarak, artık çalışmadığım müvekkilleri arşivlemek istiyorum ki aktif listem temiz kalsın ama veriler silinmesin.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Arşivlenen müvekkil varsayılan listede görünmez |
| AC2 | Arşivlenen müvekkilin aktif dosyası varsa uyarı verilir |
| AC3 | Arşivlenmiş müvekkil geri getirilebilir |
| AC4 | Arşivleme nedeni kaydedilir |
| AC5 | Arşiv tarihçesi görüntülenebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Aktif dosyası olan müvekkil arşivlenir | "Bu müvekkilin X aktif dosyası var. Devam etmek istiyor musunuz?" onay kutusu |
| NC2 | Bakiyesi olan müvekkil arşivlenir | "Bu müvekkilin X TL bakiyesi var" uyarısı |
| NC3 | Yetkisiz kullanıcı arşivleme yapar | "Bu işlem için yetkiniz bulunmuyor" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Arşivlenmiş müvekkile yeni dosya açılmaya çalışılır | "Müvekkil arşivlenmiş. Önce aktife alın" uyarısı |
| EC2 | Arşivlenmiş müvekkil aranır | Sadece "Arşivi dahil et" filtresi ile bulunur |
| EC3 | Aynı müvekkil birden fazla kez arşivlenip aktife alınır | Her işlem tarihçede ayrı kayıt olur |
| EC4 | Toplu arşivleme yapılır (50+ müvekkil) | Progress bar gösterilir, batch işlem yapılır |
| EC5 | Arşivleme sırasında bağlantı kesilir | İşlem geri alınır, tutarlılık sağlanır |

---

## US-2.6: Müvekkil Birleştirme

**User Story**

Bir avukat olarak, yanlışlıkla mükerrer oluşturulmuş müvekkil kayıtlarını birleştirmek istiyorum ki veriler tek kayıtta toplanır.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | İki müvekkil kaydı seçilerek birleştirme başlatılır |
| AC2 | Ana kayıt ve birleştirilecek kayıt belirlenir |
| AC3 | Tüm dosyalar, mali hareketler ana kayda aktarılır |
| AC4 | İletişim bilgileri birleştirilir (mükerrer değilse) |
| AC5 | Birleştirme sonrası eski kayıt silinir |
| AC6 | İşlem geri alınamaz, onay istenir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Farklı türde müvekkiller birleştirilir (gerçek + tüzel) | "Farklı türdeki müvekkiller birleştirilemez" hatası |
| NC2 | Aynı müvekkil kendisiyle birleştirilmeye çalışılır | "Aynı müvekkil seçilemez" hatası |
| NC3 | Yetkisiz kullanıcı birleştirme yapar | "Bu işlem için yönetici yetkisi gerekli" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Her iki müvekkilin de aynı dosyaya bağlantısı var | Uyarı verilir, birleştirme sonrası tek bağlantı kalır |
| EC2 | İki müvekkilin toplam 100+ dosyası var | İşlem uzun sürebilir uyarısı, arka planda çalışır |
| EC3 | Birleştirme sırasında dosyalardan birine işlem yapılır | Dosya kilidi veya çakışma yönetimi |
| EC4 | Mali hareketler birleştirilirken bakiye tutarsızlığı | Detaylı rapor gösterilir, manuel onay istenir |
| EC5 | Birleştirme yarıda kesilir | Transaction rollback, her iki kayıt da korunur |

---

## US-2.7: Müvekkil İletişim Geçmişi

**User Story**

Bir avukat olarak, müvekkilimle yaptığım tüm görüşmeleri kaydetmek istiyorum ki iletişim geçmişini takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Görüşme türü seçilmelidir (telefon, yüz yüze, e-posta, online) |
| AC2 | Görüşme tarihi ve saati kaydedilir |
| AC3 | Görüşme özeti zorunludur |
| AC4 | Görüşmelerin kim tarafından kaydedildiği gösterilir |
| AC5 | Görüşme geçmişi kronolojik sırada listelenir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Görüşme türü seçilmeden kaydedilir | "Görüşme türü seçiniz" hatası |
| NC2 | Özet alanı boş bırakılır | "Görüşme özeti zorunludur" hatası |
| NC3 | Gelecek tarihli görüşme kaydedilir | Kabul edilir (planlanmış görüşme olarak) veya uyarı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Aynı dakikada iki görüşme kaydedilir | Kabul edilir (farklı türlerde olabilir) |
| EC2 | Çok uzun görüşme notu (5000+ karakter) | Kabul edilir, metin alanı genişler |
| EC3 | Geçmiş tarihli görüşme kaydedilir (1 yıl önce) | Kabul edilir, tarihçeye eklenir |
| EC4 | Görüşme kaydı düzenlenir | Düzenleme tarihi ve düzenleyen kaydedilir |
| EC5 | Görüşme kaydı silinir | Soft delete, yönetici görebilir |

---

## US-2.8: Müvekkil Bakiye Görüntüleme

**User Story**

Bir avukat olarak, müvekkilin güncel bakiyesini ve mali özetini görmek istiyorum ki alacak durumunu takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Toplam alacak, tahsilat ve bakiye özeti gösterilir |
| AC2 | Dosya bazlı bakiye dağılımı görüntülenir |
| AC3 | Vadesi geçen alacaklar vurgulanır |
| AC4 | Son 5 mali hareket özeti gösterilir |
| AC5 | Detaylı cari hesap ekstresi erişilebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Mali yetkisi olmayan kullanıcı bakiye görüntüler | Bakiye alanı gizlenir veya "Yetki gerekli" mesajı |
| NC2 | Hiç mali hareketi olmayan müvekkil | "Henüz mali hareket bulunmuyor" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Negatif bakiye (müvekkil alacaklı) | Farklı renkte gösterilir, avans durumu belirtilir |
| EC2 | Çok yüksek bakiye (1.000.000+ TL) | Formatlanarak gösterilir (1.000.000,00 TL) |
| EC3 | Farklı para birimlerinde hareket var | Her para birimi ayrı gösterilir veya dönüştürülür |
| EC4 | Bakiye hesaplanırken hata oluşur | Hata mesajı, manuel yenileme butonu |
| EC5 | Eş zamanlı tahsilat kaydedilirken bakiye görüntülenir | Güncel bakiye gösterilir (real-time veya refresh) |

---

# MODÜL 3: DOSYA/DAVA YÖNETİMİ

## US-3.1: Yeni Dosya Açma

**User Story**

Bir avukat olarak, yeni dava veya icra dosyası açmak istiyorum ki müvekkilimin hukuki işlemlerini sisteme kaydedip takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Dosya türü seçilmelidir (dava, icra, danışmanlık, arabuluculuk) |
| AC2 | Müvekkil seçimi zorunludur |
| AC3 | Müvekkil pozisyonu belirtilmelidir (davacı, davalı, alacaklı, borçlu) |
| AC4 | Büro dosya numarası otomatik oluşturulur (D-2025-0001) |
| AC5 | Sorumlu avukat atanmalıdır |
| AC6 | Dosya varsayılan olarak "Taslak" durumunda açılır |
| AC7 | En az bir karşı taraf bilgisi girilmelidir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Dosya türü seçilmeden kaydedilir | "Dosya türü seçiniz" hatası |
| NC2 | Müvekkil seçilmeden kaydedilir | "Müvekkil seçimi zorunludur" hatası |
| NC3 | Müvekkil pozisyonu seçilmez | "Müvekkil pozisyonu seçiniz" hatası |
| NC4 | Sorumlu avukat atanmadan kaydedilir | "Sorumlu avukat ataması zorunludur" hatası |
| NC5 | Karşı taraf bilgisi girilmez | "En az bir karşı taraf giriniz" hatası |
| NC6 | Arşivlenmiş müvekkile dosya açılmaya çalışılır | "Müvekkil arşivlenmiş. Önce aktife alın" uyarısı |
| NC7 | Yetkisiz kullanıcı dosya açmaya çalışır | "Bu işlem için yetkiniz bulunmuyor" hatası |
| NC8 | Pasif kullanıcı sorumlu avukat olarak atanır | "Pasif kullanıcı atanamaz" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Aynı müvekkil ve karşı taraf ile ikinci dosya açılır | Uyarı gösterilir: "Bu taraflar arasında X dosya mevcut" |
| EC2 | Müvekkil hem davacı hem davalı olarak seçilir | Engellenir: "Müvekkil aynı dosyada hem davacı hem davalı olamaz" |
| EC3 | Karşı taraf olarak mevcut müvekkil seçilir | Uyarı: "Karşı taraf sisteminizde müvekkil olarak kayıtlı. Çıkar çatışması olabilir" |
| EC4 | Dosya numarası yıl değişiminde | D-2026-0001 olarak yeni seri başlar |
| EC5 | Aynı anda iki kullanıcı dosya açar | Her biri farklı numara alır, çakışma olmaz |
| EC6 | Mahkeme esas numarası formatı farklı (2025/123, E.2025/123) | Her iki format da kabul edilir |
| EC7 | Çok uzun dava konusu özeti girilir (5000+ karakter) | Kabul edilir, özet alanı genişler |
| EC8 | Talep tutarı 0 veya negatif girilir | "Talep tutarı pozitif olmalıdır" veya 0 kabul edilir (manevi tazminat) |
| EC9 | Birden fazla karşı taraf eklenir (10+) | Kabul edilir, liste şeklinde gösterilir |
| EC10 | Dosya açılırken form yarıda bırakılır | Taslak olarak otomatik kaydedilir veya kaybolur |

---

## US-3.2: Dosya Bilgi Güncelleme

**User Story**

Bir avukat olarak, dosya bilgilerini güncellemek istiyorum ki mahkeme bilgileri ve dava durumu her zaman güncel kalsın.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Mahkeme adı ve esas numarası eklenebilir/güncellenebilir |
| AC2 | Dosya durumu değiştirilebilir |
| AC3 | Karşı taraf bilgileri güncellenebilir |
| AC4 | Değişiklik tarihçesi tutulur |
| AC5 | Kritik alan değişikliklerinde (durum, mahkeme) onay istenir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Zorunlu alan silinerek kaydedilir | İlgili alanın zorunluluk hatası |
| NC2 | Kapanmış dosyanın durumu "Aktif"e çekilir | Uyarı: "Kapanmış dosyayı yeniden açmak istiyor musunuz?" |
| NC3 | Yetkisiz kullanıcı başkasının dosyasını günceller | "Bu dosyayı düzenleme yetkiniz yok" hatası |
| NC4 | Aynı dosya iki kullanıcı tarafından eş zamanlı düzenlenir | Çakışma uyarısı, son değişiklik gösterilir |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Esas numarası olmayan dosyaya esas numarası eklenir | Kayıt güncellenir, dosya "Aktif" durumuna geçirilebilir |
| EC2 | Mahkeme değişikliği yapılır (görevsizlik kararı) | Eski mahkeme bilgisi tarihçede saklanır |
| EC3 | Müvekkil pozisyonu değiştirilir (davacıdan davalıya) | Uyarı gösterilir, onay ile güncellenir |
| EC4 | Dosya türü değiştirilir (dava → icra) | Gerekli alanlar güncellenir, eksik bilgi istenir |
| EC5 | Tüm karşı taraflar silinir | "En az bir karşı taraf olmalıdır" hatası |
| EC6 | Dosya düzenlenirken silinir (başka kullanıcı tarafından) | Kaydet tıklanınca "Dosya bulunamadı" hatası |

---

## US-3.3: Dosya Durum Değişikliği

**User Story**

Bir avukat olarak, dosyanın durumunu güncellemek istiyorum ki dosyanın hangi aşamada olduğu takip edilebilsin.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Durum değişikliği için neden/not zorunludur |
| AC2 | Belirli durum geçişleri izin verilir (Taslak → Aktif, Aktif → Beklemede) |
| AC3 | Kapanış durumunda kapanış şekli seçilmelidir |
| AC4 | Durum değişikliği tarihçede kaydedilir |
| AC5 | Durum değişikliğinde ilgili kişilere bildirim gönderilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Geçersiz durum geçişi denenir (Taslak → Kapandı) | "Bu durum geçişi yapılamaz. Önce Aktif durumuna alın" hatası |
| NC2 | Kapanış şekli seçilmeden dosya kapatılır | "Kapanış şekli seçiniz" hatası |
| NC3 | Durum değişiklik notu girilmez | "Değişiklik nedeni zorunludur" hatası |
| NC4 | Açık görevi olan dosya kapatılır | Uyarı: "Bu dosyada X açık görev var. Devam edilsin mi?" |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Beklemede durumundan direkt Kapandı'ya geçiş | İzin verilir (örn: feragat durumu) |
| EC2 | Kapanmış dosya yeniden Aktif yapılır | İzin verilir, yeniden açılma tarihi kaydedilir |
| EC3 | Aynı durum tekrar seçilir | "Dosya zaten bu durumda" uyarısı, işlem yapılmaz |
| EC4 | Durum değişikliği sırasında yetki kaldırılır | İşlem reddedilir |
| EC5 | Ödenmemiş alacağı olan dosya kapatılır | Uyarı: "Bu dosyada X TL alacak bakiyesi var" |
| EC6 | Dosya durumu çok hızlı değiştirilir (spam) | Rate limiting veya onay mekanizması |

---

## US-3.4: Duruşma Kaydı Oluşturma

**User Story**

Bir avukat olarak, duruşma tarihlerini kaydetmek istiyorum ki duruşmalarımı takvimlendirip takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Duruşma tarihi ve saati zorunludur |
| AC2 | Katılacak avukat seçilmelidir |
| AC3 | Duruşma türü seçilmelidir (ön inceleme, tahkikat, karar, keşif) |
| AC4 | Aynı tarih ve saatte çakışan duruşma varsa uyarı verilir |
| AC5 | Duruşma hatırlatıcısı otomatik oluşturulur |
| AC6 | Geçmiş tarihli duruşma eklenebilir (sonradan kayıt) |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Tarih girilmeden kaydedilir | "Duruşma tarihi zorunludur" hatası |
| NC2 | Saat girilmeden kaydedilir | "Duruşma saati zorunludur" hatası |
| NC3 | Katılacak avukat seçilmez | "Katılacak avukat seçiniz" hatası |
| NC4 | Duruşma türü seçilmez | "Duruşma türü seçiniz" hatası |
| NC5 | Kapanmış dosyaya duruşma eklenir | "Kapanmış dosyaya duruşma eklenemez" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Aynı gün aynı saatte farklı dosyalara duruşma eklenir | Çakışma uyarısı: "Bu saatte başka duruşmanız var: [Dosya No]" |
| EC2 | Resmi tatil gününe duruşma eklenir | Uyarı: "Bu tarih resmi tatil" (ama engellemez) |
| EC3 | Hafta sonuna duruşma eklenir | Uyarı: "Bu tarih hafta sonu" |
| EC4 | 5 yıl sonrası tarihte duruşma eklenir | Kabul edilir |
| EC5 | Geçmiş tarihli duruşma eklenir (2 ay önce) | Kabul edilir, "Geçmiş tarih" uyarısı |
| EC6 | Aynı dosyaya aynı gün birden fazla duruşma eklenir | Kabul edilir (aynı gün farklı mahkemelerde olabilir) |
| EC7 | Duruşma saati mesai saatleri dışında (22:00) | Uyarı gösterilir ama kabul edilir |
| EC8 | Mahkeme salonu bilgisi çok uzun (100+ karakter) | Maksimum karakter sınırı uygulanır |
| EC9 | Pasif avukat katılacak avukat olarak seçilir | "Pasif kullanıcı seçilemez" hatası |

---

## US-3.5: Duruşma Sonucu Kaydetme

**User Story**

Bir avukat olarak, duruşma sonucunu kaydetmek istiyorum ki dosya ilerleyişi takip edilebilsin ve yapılacaklar belirlensin.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Sadece geçmiş veya bugünkü tarihli duruşmaya sonuç girilebilir |
| AC2 | Sonuç özeti zorunludur |
| AC3 | Sonraki duruşma tarihi girilebilir (yeni duruşma kaydı oluşturur) |
| AC4 | Ara karar ve verilen kararlar kaydedilebilir |
| AC5 | Duruşma durumu "Tamamlandı" olarak güncellenir |
| AC6 | Yapılacak işlemler görev olarak oluşturulabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Gelecek tarihli duruşmaya sonuç girilir | "Henüz gerçekleşmemiş duruşmaya sonuç girilemez" hatası |
| NC2 | Sonuç özeti boş bırakılır | "Sonuç özeti zorunludur" hatası |
| NC3 | Zaten sonuç girilmiş duruşmaya tekrar sonuç girilir | "Bu duruşmaya zaten sonuç girilmiş. Düzenlemek ister misiniz?" |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Sonraki duruşma tarihi bugün girilir | Kabul edilir (aynı gün birden fazla duruşma) |
| EC2 | Sonraki duruşma tarihi geçmiş tarih girilir | "Sonraki duruşma tarihi geçmiş olamaz" hatası |
| EC3 | Duruşma ertelendi olarak işaretlenir | Yeni tarih zorunlu olur |
| EC4 | Çok uzun duruşma notu girilir (10.000+ karakter) | Kabul edilir |
| EC5 | Aynı anda iki avukat sonuç girer | Çakışma yönetimi, ilk kaydeden kazanır |
| EC6 | Duruşma sonucu olarak "Karar verildi" seçilir | Dosya durumu güncelleme önerisi gösterilir |

---

## US-3.6: Belge Yükleme

**User Story**

Bir avukat olarak, dosyaya belge yüklemek istiyorum ki tüm evraklar dijital ortamda saklanabilsin ve kolayca erişilebilsin.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | PDF, DOC, DOCX, JPG, PNG, TIFF formatları desteklenir |
| AC2 | Maksimum dosya boyutu 25 MB'dır |
| AC3 | Belge kategorisi seçilmelidir |
| AC4 | Belge adı otomatik oluşturulur, değiştirilebilir |
| AC5 | Çoklu dosya yükleme desteklenir |
| AC6 | Yükleme ilerlemesi gösterilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Desteklenmeyen format yüklenir (.exe, .zip) | "Bu dosya formatı desteklenmiyor" hatası |
| NC2 | 25 MB üzeri dosya yüklenir | "Dosya boyutu 25 MB'ı geçemez" hatası |
| NC3 | Belge kategorisi seçilmez | "Belge kategorisi seçiniz" hatası |
| NC4 | Yetkisiz kullanıcı belge yükler | "Bu dosyaya belge yükleme yetkiniz yok" hatası |
| NC5 | Virüslü dosya yüklenir | Dosya reddedilir, güvenlik uyarısı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Aynı isimde ikinci belge yüklenir | "Belge_Adi(1).pdf" şeklinde yeniden adlandırılır |
| EC2 | Dosya adı Türkçe karakter içerir (Dilekçe_Şikayet.pdf) | Doğru şekilde kaydedilir ve görüntülenir |
| EC3 | Dosya adı çok uzun (200+ karakter) | Kısaltılır veya hata verilir |
| EC4 | Dosya adında özel karakterler var (Belge<>:"/\|?*.pdf) | Özel karakterler temizlenir |
| EC5 | Yükleme sırasında internet kesilir | Yükleme iptal edilir, yeniden deneme önerilir |
| EC6 | 50 dosya aynı anda yüklenir | Sırayla yüklenir, ilerleme gösterilir |
| EC7 | 0 KB boyutunda dosya yüklenir | "Dosya boş olamaz" hatası |
| EC8 | Bozuk/açılamayan dosya yüklenir | Kabul edilir ama uyarı verilir |
| EC9 | Aynı dosyanın farklı versiyonları yüklenir | Versiyon numarası otomatik atanır |
| EC10 | Kapanmış dosyaya belge yüklenir | İzin verilir (karar, kesinleşme vb. için) |

---

## US-3.7: Belge Görüntüleme ve İndirme

**User Story**

Bir avukat olarak, dosyadaki belgeleri görüntülemek ve indirmek istiyorum ki evraklara hızlıca erişebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | PDF dosyaları tarayıcıda önizleme yapılabilir |
| AC2 | Tüm dosyalar indirilebilir |
| AC3 | Belge arama özelliği çalışır |
| AC4 | Belge listesi kategoriye göre filtrelenebilir |
| AC5 | Her indirme loglanır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Yetkisiz kullanıcı belge görüntülemek ister | "Bu belgeye erişim yetkiniz yok" hatası |
| NC2 | Silinmiş belge görüntülenmek istenir | "Belge bulunamadı" hatası |
| NC3 | Sunucuda dosya fiziksel olarak yok | "Dosya sunucuda bulunamadı. Sistem yöneticisiyle iletişime geçin" |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Çok büyük PDF önizleme yapılır (100+ sayfa) | Sayfa sayfa yüklenir, performans optimize edilir |
| EC2 | Bozuk PDF önizleme yapılmak istenir | "Dosya önizlenemiyor. İndirmeyi deneyin" mesajı |
| EC3 | Aynı anda 10 dosya indirilir | Zip olarak indirilir veya sırayla başlar |
| EC4 | İndirme sırasında oturum süresi dolar | İndirme tamamlanır veya yetki hatası |
| EC5 | Mobil cihazda büyük dosya indirilir | Dosya boyutu uyarısı gösterilir |
| EC6 | Tarayıcı PDF önizlemeyi desteklemiyor | Otomatik indirme başlar |

---

## US-3.8: Dosya Atama

**User Story**

Bir kurucu ortak olarak, dosyaları avukatlara atamak istiyorum ki iş dağılımı yapılabilsin ve her avukat kendi dosyalarını takip edebilsin.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Bir dosyaya birden fazla avukat atanabilir |
| AC2 | Sorumlu avukat (tek) ve yardımcı avukatlar (çoklu) ayrımı yapılır |
| AC3 | Atama yapıldığında ilgili avukata bildirim gönderilir |
| AC4 | Atama kaldırıldığında ilgili avukata bildirim gönderilir |
| AC5 | Atama geçmişi tutulur |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Sorumlu avukat olmadan dosya kaydedilir | "Sorumlu avukat zorunludur" hatası |
| NC2 | Pasif kullanıcı atanmak istenir | "Pasif kullanıcı atanamaz" hatası |
| NC3 | Yetkisiz kullanıcı atama yapmak ister | "Atama yapma yetkiniz yok" hatası |
| NC4 | Aynı kullanıcı hem sorumlu hem yardımcı yapılır | "Aynı kullanıcı iki rolde atanamaz" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Sorumlu avukat değiştirilir | Eski sorumluya bildirim, yeni sorumluya bildirim |
| EC2 | Tek sorumlu avukat kaldırılır | "Sorumlu avukat kaldırılamaz. Önce yeni sorumlu atayın" |
| EC3 | 10+ avukat aynı dosyaya atanır | Kabul edilir, liste şeklinde gösterilir |
| EC4 | Avukat kendi kendini dosyadan çıkarır | İzin verilir (sorumlu değilse) veya engellenir |
| EC5 | Atanan avukatın yetkisi sonradan kaldırılır | Atama kalır, erişim engellenir |
| EC6 | Tüm atamalar kaldırılır | Sorumlu avukat zorunlu olduğundan engellenir |

---

## US-3.9: Dosya Arama ve Filtreleme

**User Story**

Bir avukat olarak, dosyaları çeşitli kriterlere göre aramak ve filtrelemek istiyorum ki aradığım dosyaya hızlıca ulaşabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Büro dosya no, esas no, müvekkil adı ile arama yapılabilir |
| AC2 | Dosya türü, durum, sorumlu avukat ile filtreleme yapılabilir |
| AC3 | Tarih aralığı ile filtreleme yapılabilir |
| AC4 | Arama sonuçları kaydedilebilir (favori arama) |
| AC5 | Sonuçlar Excel'e aktarılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Hiç sonuç döndürmeyen arama yapılır | "Sonuç bulunamadı. Filtreleri değiştirin" mesajı |
| NC2 | Çok kısa arama terimi girilir (1-2 karakter) | "En az 3 karakter girin" uyarısı |
| NC3 | Stajyer, atanmadığı dosyaları arar | Sadece atandığı dosyalar sonuçlarda görünür |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Esas numarası farklı formatlarla aranır (2025/123, E.2025/123) | Her iki format da eşleşir |
| EC2 | Türkçe karakter içeren terimle arama yapılır | Doğru sonuçlar döner |
| EC3 | Büyük/küçük harf duyarsız arama | Tüm varyasyonlar bulunur |
| EC4 | Çok fazla filtre uygulanır (5+ kriter) | Tüm kriterler AND mantığıyla çalışır |
| EC5 | 10.000+ dosya içinde arama | Performans 3 saniyenin altında |
| EC6 | Silinmiş dosyalar aranır | Varsayılanda görünmez, "Arşivi dahil et" ile görünür |
| EC7 | Arama sonuçları sayfalanır (100+ sonuç) | 20'şer kayıt gösterilir, sayfalama çalışır |
| EC8 | Özel karakterlerle arama yapılır | Karakterler escape edilir |

---

## US-3.10: Dosya Kapatma

**User Story**

Bir avukat olarak, sonuçlanan dosyayı kapatmak istiyorum ki aktif dosya listesi güncel kalsın ve raporlar doğru olsun.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Kapanış şekli seçilmelidir (kazanıldı, kaybedildi, sulh, vb.) |
| AC2 | Kapanış tarihi girilmelidir |
| AC3 | Kapanış notu opsiyoneldir |
| AC4 | Açık görevler için uyarı verilir |
| AC5 | Ödenmemiş alacak için uyarı verilir |
| AC6 | Kapatma sonrası dosya salt okunur olmaz, belge eklenebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Kapanış şekli seçilmeden kapatılır | "Kapanış şekli seçiniz" hatası |
| NC2 | Kapanış tarihi girilmeden kapatılır | "Kapanış tarihi zorunludur" hatası |
| NC3 | Kapanış tarihi dosya açılış tarihinden önce | "Kapanış tarihi açılış tarihinden önce olamaz" hatası |
| NC4 | Taslak durumundaki dosya kapatılır | "Taslak dosya kapatılamaz. Önce aktife alın" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Kapanış tarihi gelecekte girilir | Kabul edilir (planlanan kapanış) veya engellenir |
| EC2 | Açık duruşması olan dosya kapatılır | Uyarı: "Bu dosyada planlanmış duruşma var" |
| EC3 | Açık görevi olan dosya kapatılır | Uyarı: "X açık görev var. Kapatılsın mı?" |
| EC4 | Bakiyesi olan dosya kapatılır | Uyarı gösterilir, onay ile devam edilir |
| EC5 | Aynı dosya birden fazla kez kapatılır | "Dosya zaten kapalı" mesajı |
| EC6 | Kapatılmış dosya yeniden açılır | İzin verilir, yeniden açılma tarihi kaydedilir |
| EC7 | Kapanış şekli sonradan değiştirilir | İzin verilir, tarihçede kaydedilir |

---

# MODÜL 4: İŞ LİSTESİ VE GÖREV YÖNETİMİ

## US-4.1: Yeni Görev Oluşturma

**User Story**

Bir avukat olarak, yapılacak işleri görev olarak kaydetmek istiyorum ki işlerimi takip edebilir ve unutmamış olayım.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Görev başlığı zorunludur |
| AC2 | Başlangıç ve bitiş tarihi belirlenebilir |
| AC3 | Görevli (sorumlu kişi) atanmalıdır |
| AC4 | Öncelik seviyesi seçilebilir (Düşük, Normal, Yüksek, Acil) |
| AC5 | Görev bir dosyaya bağlanabilir (opsiyonel) |
| AC6 | Görev bir müvekkile bağlanabilir (dosya yoksa) |
| AC7 | Görev türü seçilebilir |
| AC8 | Görev varsayılan olarak "Bekliyor" durumunda oluşturulur |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Görev başlığı boş bırakılır | "Görev başlığı zorunludur" hatası |
| NC2 | Görevli atanmadan kaydedilir | "Görevli ataması zorunludur" hatası |
| NC3 | Bitiş tarihi başlangıç tarihinden önce girilir | "Bitiş tarihi başlangıç tarihinden önce olamaz" hatası |
| NC4 | Pasif kullanıcı görevli olarak atanır | "Pasif kullanıcı atanamaz" hatası |
| NC5 | Yetkisiz kullanıcı başkasına görev atar | "Başkasına görev atama yetkiniz yok" hatası |
| NC6 | Arşivlenmiş dosyaya görev bağlanır | "Arşivlenmiş dosyaya görev oluşturulamaz" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Görev başlığı çok uzun (500+ karakter) | Maksimum 200 karakter sınırı uygulanır |
| EC2 | Görev başlığı minimum uzunlukta (1 karakter) | Kabul edilir veya minimum 3 karakter kuralı |
| EC3 | Başlangıç tarihi geçmişte girilir | Kabul edilir (gecikmiş görev kaydı) |
| EC4 | Bitiş tarihi çok uzak gelecekte (5+ yıl) | Kabul edilir |
| EC5 | Aynı başlıkla ikinci görev oluşturulur | Kabul edilir (farklı ID alır) |
| EC6 | Hem dosya hem müvekkil seçilir ama uyumsuz | Dosyanın müvekkili otomatik seçilir |
| EC7 | Kullanıcı kendine görev atar | Kabul edilir |
| EC8 | Tüm kullanıcılara aynı görev atanır | Her kullanıcı için ayrı görev oluşturulur veya çoklu atama |
| EC9 | Görev açıklaması HTML/script içerir | Temizlenir, XSS önlenir |
| EC10 | Tahmini süre 0 veya negatif girilir | "Tahmini süre pozitif olmalıdır" hatası |

---

## US-4.2: Görev Atama ve Devretme

**User Story**

Bir avukat olarak, görevleri ekip üyelerine atamak veya devretmek istiyorum ki iş dağılımını yönetebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Görev oluşturulurken veya sonradan atama yapılabilir |
| AC2 | Birden fazla yardımcı görevli atanabilir |
| AC3 | Görevli değiştirildiğinde eski ve yeni görevliye bildirim gider |
| AC4 | Devir nedeni kaydedilebilir |
| AC5 | Atama geçmişi tutulur |
| AC6 | Sadece yetkili kullanıcılar başkasına atama yapabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Tamamlanmış göreve yeni görevli atanır | Uyarı: "Görev tamamlanmış. Yine de atamak istiyor musunuz?" |
| NC2 | İptal edilmiş göreve atama yapılır | "İptal edilmiş göreve atama yapılamaz" hatası |
| NC3 | Stajyer başka avukata görev atar | "Başkasına görev atama yetkiniz yok" hatası |
| NC4 | Görev kendisinden alınıp başkasına verilir (yetkisizce) | "Bu görevi devretme yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Aynı kullanıcıya tekrar atama yapılır | "Görev zaten bu kullanıcıya atanmış" mesajı |
| EC2 | Görev 10+ kişiye atanır | Kabul edilir, herkes görevi listesinde görür |
| EC3 | Asıl görevli pasife alınır | Uyarı gönderilir, yeniden atama istenir |
| EC4 | Devir sırasında görev durumu değişir | İşlem tamamlanır, son durum geçerli olur |
| EC5 | Tüm görevliler görevden çıkarılır | "En az bir görevli olmalıdır" hatası |
| EC6 | Görev devredilirken açıklama çok uzun | Maksimum 500 karakter sınırı |
| EC7 | Toplu görev devri yapılır (10+ görev) | Batch işlem, ilerleme gösterilir |
| EC8 | Atama bildirimi gönderilemez (e-posta hatası) | Görev atanır, bildirim hatası loglanır |

---

## US-4.3: Görev Durumu Güncelleme

**User Story**

Bir avukat olarak, görev durumunu güncellemek istiyorum ki işin hangi aşamada olduğu görülebilsin.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Görev durumları: Bekliyor, Devam Ediyor, Beklemede, Tamamlandı, İptal |
| AC2 | Durum değişikliğinde not eklenebilir |
| AC3 | Durum değişikliği tarihçede kaydedilir |
| AC4 | Belirli durum geçişleri mantıksal olmalı |
| AC5 | Tamamlandı durumuna geçildiğinde tamamlanma tarihi otomatik kaydedilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Bekliyor durumundan direkt Tamamlandı'ya geçilir | İzin verilir (hızlı tamamlama) veya uyarı |
| NC2 | İptal edilmiş görev Devam Ediyor yapılır | "İptal edilmiş görev yeniden açılamaz" veya izin (iş kuralı) |
| NC3 | Yetkisiz kullanıcı başkasının görevini günceller | "Bu görevi güncelleme yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Aynı durum tekrar seçilir | İşlem yapılmaz, "Görev zaten bu durumda" |
| EC2 | Durum çok hızlı değiştirilir (1 saniyede 5 kez) | Son durum geçerli, tümü tarihçeye yazılır |
| EC3 | Tamamlandı yapılıp tekrar Devam Ediyor yapılır | İzin verilir, tamamlanma tarihi silinir |
| EC4 | Alt görevi olan görev tamamlanır | Uyarı: "X alt görev henüz tamamlanmadı" |
| EC5 | Bağımlı görevi olan görev tamamlanır | Bağımlı görevler "Bekliyor"dan "Başlayabilir"e geçer |
| EC6 | Görev Beklemede yapılır, bekleme nedeni girilmez | Uyarı gösterilir ama zorunlu değil |
| EC7 | Geçmiş tarihli bitiş tarihine sahip görev hala Bekliyor | Otomatik "Gecikti" işareti veya durum |

---

## US-4.4: Alt Görev Oluşturma

**User Story**

Bir avukat olarak, büyük görevleri alt görevlere bölmek istiyorum ki işi parçalara ayırıp daha iyi takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Her görevin altına alt görev eklenebilir |
| AC2 | Alt görevler ana görevden bağımsız atanabilir |
| AC3 | Alt görevlerin kendi başlangıç/bitiş tarihleri olabilir |
| AC4 | Tüm alt görevler tamamlandığında ana görev için uyarı verilir |
| AC5 | Alt görevin alt görevi oluşturulamaz (tek seviye) |
| AC6 | Alt görev sayısı sınırsızdır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Alt göreve alt görev eklenmek istenir | "Alt görevin alt görevi oluşturulamaz" hatası |
| NC2 | Tamamlanmış ana göreve alt görev eklenir | Uyarı: "Ana görev tamamlanmış. Yine de eklemek istiyor musunuz?" |
| NC3 | Alt görev başlığı boş bırakılır | "Alt görev başlığı zorunludur" hatası |
| NC4 | Alt görev bitiş tarihi ana görev bitişinden sonra | Uyarı gösterilir ama kabul edilir |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | 50+ alt görev oluşturulur | Kabul edilir, sayfalama veya scroll |
| EC2 | Alt görev ana görevden önce bitiyor | Kabul edilir |
| EC3 | Ana görev silinir, alt görevler ne olur | Alt görevler de silinir (cascade) veya bağımsız kalır |
| EC4 | Tüm alt görevler tamamlanır | Ana görev otomatik tamamlanmaz, uyarı verilir |
| EC5 | Alt görev farklı kullanıcıya atanır | Kabul edilir, bağımsız bildirim |
| EC6 | Alt görev ana görevden farklı dosyaya bağlanır | Engellenir, ana görevin dosyası geçerli |
| EC7 | Alt görevlerin sırası değiştirilir (drag-drop) | Sıralama kaydedilir |

---

## US-4.5: Görev Kontrol Listesi (Checklist)

**User Story**

Bir avukat olarak, görev içinde basit yapılacaklar listesi tutmak istiyorum ki küçük adımları hızlıca işaretleyebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Her göreve kontrol listesi maddeleri eklenebilir |
| AC2 | Maddeler tamamlandı/tamamlanmadı olarak işaretlenebilir |
| AC3 | Maddeler sürükle-bırak ile sıralanabilir |
| AC4 | Tamamlanan madde sayısı / toplam madde gösterilir |
| AC5 | Maddeler düzenlenebilir ve silinebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Boş madde eklenir | "Madde metni zorunludur" hatası |
| NC2 | Tamamlanmış göreve madde eklenir | Kabul edilir (tamamlandıktan sonra ek iş çıkabilir) |
| NC3 | Yetkisiz kullanıcı madde ekler | "Bu göreve madde ekleme yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | 100+ madde eklenir | Kabul edilir, scroll gösterilir |
| EC2 | Madde metni çok uzun (500+ karakter) | Maksimum 200 karakter sınırı |
| EC3 | Tüm maddeler tamamlanır | "Tüm maddeler tamamlandı" bildirimi, görev durumu değişmez |
| EC4 | Tamamlanmış madde geri alınır | Kabul edilir, tamamlanma işareti kalkar |
| EC5 | Madde silinirken görev de siliniyor | Madde silme, görev silmeden bağımsız |
| EC6 | Aynı anda iki kullanıcı farklı maddeleri işaretler | Her iki değişiklik de kaydedilir |
| EC7 | Sıralama değiştirilirken bağlantı kesilir | Son kaydedilen sıra geçerli |

---

## US-4.6: Görev Hatırlatıcıları

**User Story**

Bir avukat olarak, görevler için hatırlatıcı ayarlamak istiyorum ki önemli işleri zamanında yapmayı unutmayayım.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Görev bitiş tarihinden X gün/saat önce hatırlatıcı ayarlanabilir |
| AC2 | Birden fazla hatırlatıcı eklenebilir |
| AC3 | Hatırlatıcı sistem içi bildirim ve/veya e-posta olarak gönderilebilir |
| AC4 | Görev geciktiğinde otomatik hatırlatıcı gönderilir |
| AC5 | Hatırlatıcılar kapatılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Bitiş tarihi olmayan göreve hatırlatıcı eklenir | "Hatırlatıcı için bitiş tarihi gerekli" hatası |
| NC2 | Hatırlatıcı süresi bitiş tarihinden sonra ayarlanır | "Hatırlatıcı bitiş tarihinden önce olmalı" hatası |
| NC3 | Tamamlanmış görev için hatırlatıcı ayarlanır | "Tamamlanmış görev için hatırlatıcı ayarlanamaz" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Hatırlatıcı zamanı geçmişte kalır (görev güncellenince) | Hatırlatıcı hemen gönderilir veya atlanır |
| EC2 | Aynı zaman için birden fazla hatırlatıcı ayarlanır | Tek hatırlatıcı gönderilir, mükerrer önlenir |
| EC3 | Görevli değişir, eski hatırlatıcılar ne olur | Yeni görevliye aktarılır |
| EC4 | E-posta sunucusu çalışmıyor | Sistem içi bildirim gönderilir, log tutulur |
| EC5 | Kullanıcı bildirimleri kapatmış | Sadece sistem içi bildirim, e-posta gönderilmez |
| EC6 | 100+ görevin hatırlatıcısı aynı anda tetiklenir | Kuyruğa alınır, sırayla gönderilir |
| EC7 | Hatırlatıcı gönderildiğinde kullanıcı çevrimdışı | Sonraki girişte bildirim gösterilir |

---

## US-4.7: Görev Arama ve Filtreleme

**User Story**

Bir avukat olarak, görevleri çeşitli kriterlere göre aramak ve filtrelemek istiyorum ki aradığım görevi hızlıca bulabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Görev başlığı ve açıklamasında arama yapılabilir |
| AC2 | Durum, öncelik, görevli, tarih aralığı ile filtreleme yapılabilir |
| AC3 | Dosya veya müvekkile göre filtreleme yapılabilir |
| AC4 | Gecikmiş görevler ayrı filtrelenebilir |
| AC5 | Sonuçlar öncelik, tarih veya duruma göre sıralanabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Hiç sonuç döndürmeyen arama | "Sonuç bulunamadı" mesajı |
| NC2 | Çok kısa arama terimi (1-2 karakter) | "En az 3 karakter girin" uyarısı |
| NC3 | Stajyer başkasının görevlerini arar | Sadece kendine atanan görevler görünür |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Türkçe karakter ile arama | Doğru sonuçlar döner |
| EC2 | Büyük/küçük harf duyarsız arama | Tüm varyasyonlar bulunur |
| EC3 | Çoklu filtre uygulanır (5+ kriter) | AND mantığı ile çalışır |
| EC4 | "Bugün" filtresi gece yarısında uygulanır | Doğru gün sınırı |
| EC5 | "Bu hafta" filtresi hafta ortasında | Pazartesi-Pazar veya Pazar-Cumartesi (ayar) |
| EC6 | 10.000+ görev içinde arama | Performans 2 saniye altında |
| EC7 | Silinmiş görevler aranır | Varsayılanda görünmez, filtre ile görünür |
| EC8 | Tarih filtresi saat dilimi farklı kullanıcıda | Kullanıcı saat dilimine göre hesaplanır |

---

## US-4.8: Görev Görünümleri (Liste, Kanban, Takvim)

**User Story**

Bir avukat olarak, görevlerimi farklı görünümlerde incelemek istiyorum ki çalışma şeklime uygun şekilde takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Liste görünümünde tablo formatında gösterilir |
| AC2 | Kanban görünümünde durumlara göre sütunlar oluşur |
| AC3 | Takvim görünümünde tarihlerine göre gösterilir |
| AC4 | Görünüm tercihi kaydedilir |
| AC5 | Her görünümde filtreleme çalışır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Tarihsiz görevler takvim görünümünde | Ayrı "Tarihsiz" bölümünde gösterilir |
| NC2 | Çok fazla görev tek günde (50+) | Günün altında "+45 daha" linki |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Kanban'da sürükle-bırak ile durum değişir | Durum güncellenir, tarihçeye yazılır |
| EC2 | Kanban sütununda 100+ görev var | Scroll eklenir, lazy loading |
| EC3 | Takvim görünümünde çok uzun başlık | Kısaltılır, hover ile tam gösterilir |
| EC4 | Mobil cihazda Kanban görünümü | Yatay scroll veya tek sütun modu |
| EC5 | Görünüm değiştirilirken filtreleme korunur | Aktif filtreler tüm görünümlerde geçerli |
| EC6 | Takvimde ay değiştirilir, yükleme uzun sürer | Loading göstergesi, cache kullanımı |
| EC7 | Liste görünümünde sütunlar gizlenebilir | Kullanıcı tercihi kaydedilir |
| EC8 | Kanban'da özel sütun eklenir | İzin verilir veya sabit sütunlar |

---

## US-4.9: Görev Raporları

**User Story**

Bir kurucu ortak olarak, görev raporları görmek istiyorum ki ekibin iş yükünü ve performansını değerlendirebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Toplam, tamamlanan, geciken görev sayıları gösterilir |
| AC2 | Görevli bazında görev dağılımı görüntülenir |
| AC3 | Ortalama tamamlanma süresi hesaplanır |
| AC4 | Öncelik ve tür bazında dağılım gösterilir |
| AC5 | Tarih aralığı ile filtreleme yapılabilir |
| AC6 | Rapor Excel'e aktarılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Yetkisiz kullanıcı tüm raporları görmeye çalışır | Sadece kendi görevlerinin raporunu görür |
| NC2 | Hiç görev olmayan tarih aralığı seçilir | "Bu dönemde görev bulunmuyor" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Çok geniş tarih aralığı seçilir (5 yıl) | Performans uyarısı, parçalı yükleme |
| EC2 | Sadece 1 görev varken ortalama hesaplanır | O görevin süresi ortalama olur |
| EC3 | Hiç tamamlanmış görev yokken oran hesaplanır | %0 veya "Henüz tamamlanan görev yok" |
| EC4 | Tahmini süre girilmemiş görevlerin raporu | "Veri yok" olarak gösterilir |
| EC5 | Rapor çok fazla veri içeriyor (10.000+ satır) | Sayfalama veya özet mod |
| EC6 | Rapor oluşturulurken yeni görev eklenir | Anlık rapor, yeni görev dahil olmayabilir |
| EC7 | Excel export çok büyük (50MB+) | Dosya sıkıştırılır veya bölünür |

---

## US-4.10: Toplu Görev İşlemleri

**User Story**

Bir avukat olarak, birden fazla görevi aynı anda güncellemek istiyorum ki zamandan tasarruf edebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Çoklu görev seçimi yapılabilir |
| AC2 | Seçili görevlerin durumu toplu değiştirilebilir |
| AC3 | Seçili görevler toplu atanabilir |
| AC4 | Seçili görevler toplu silinebilir (yetki gerekir) |
| AC5 | Toplu işlem sonucu özet gösterilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Hiç görev seçmeden toplu işlem yapılır | "En az bir görev seçin" hatası |
| NC2 | Farklı durumlardaki görevler aynı duruma çekilir | İzin verilir, geçersiz geçişler raporlanır |
| NC3 | Yetkisiz kullanıcı toplu silme yapar | "Toplu silme yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | 500+ görev seçilir | Performans uyarısı, batch işlem |
| EC2 | Seçili görevlerden bazıları başka kullanıcıya ait | Sadece yetkili olunanlar işlenir, diğerleri raporlanır |
| EC3 | Toplu işlem sırasında bağlantı kesilir | İşlem geri alınır veya yarım kalır, durum raporlanır |
| EC4 | Toplu atama yapılırken hedef kullanıcı pasif | İşlem reddedilir, hata mesajı |
| EC5 | Tüm görevler seçilip silinir | Onay kutusu: "Tüm görevleri silmek istediğinize emin misiniz?" |
| EC6 | Toplu işlem yarıda iptal edilir | Yapılan işlemler kalır, kalanlar yapılmaz |
| EC7 | Aynı görevler iki farklı kullanıcı tarafından toplu işleme alınır | İlk tamamlayan kazanır, diğerine uyarı |

---

## US-4.11: Görev Bağımlılıkları

**User Story**

Bir avukat olarak, görevler arası bağımlılık tanımlamak istiyorum ki bir iş bitmeden diğerinin başlamaması gereken durumları yönetebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Görev, başka bir görevin tamamlanmasına bağlanabilir |
| AC2 | Bağımlı görev, önceki tamamlanmadan "Devam Ediyor" yapılamaz |
| AC3 | Döngüsel bağımlılık engellenmelidir |
| AC4 | Bağımlılık grafiği görüntülenebilir |
| AC5 | Önceki görev tamamlandığında bağımlı göreve bildirim gönderilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Görev kendisine bağımlı yapılır | "Görev kendisine bağımlı olamaz" hatası |
| NC2 | Döngüsel bağımlılık oluşturulur (A→B→C→A) | "Döngüsel bağımlılık oluşturulamaz" hatası |
| NC3 | Bağımlı görev zorla başlatılır | Uyarı gösterilir, yönetici onayı ile izin verilir |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Bir görev 10+ göreve bağımlı | Kabul edilir, tümü tamamlanmalı |
| EC2 | Bağımlı olunan görev silinir | Bağımlılık otomatik kalkar, uyarı verilir |
| EC3 | Bağımlı olunan görev iptal edilir | Bağımlı görev başlayabilir hale gelir |
| EC4 | Çok derin bağımlılık zinciri (A→B→C→D→E→F) | Kabul edilir, grafik gösterilir |
| EC5 | Bağımlılık farklı dosyaların görevleri arasında | İzin verilir |
| EC6 | Tamamlanmış göreve bağımlılık eklenir | Bağımlı görev hemen başlayabilir durumda |
| EC7 | Bağımlılık eklendikten sonra önceki görev geri açılır | Bağımlı görev tekrar bekler durumuna geçer |

---
 İlk taksit tarihi geçmişte girilir | Uyarı gösterilir, kabul edilir |
| NC4 | Müvekkil seçilmeden plan oluşturulur | "Müvekkil seçimi zorunludur" hatası |
| NC5 | Taksit toplamı plan toplamını geçer | "Taksit toplamı plan tutarını geçemez" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Taksit sayısı çok fazla (100+) | Kabul edilir veya makul sınır (örn: 60) |
| EC2 | Tutar taksitlere tam bölünmüyor (1000 TL / 3) | Son taksitte kuruş farkı ayarlanır |
| EC3 | Taksit tutarları manuel farklı girilir (ilk büyük, sonrakiler küçük) | Kabul edilir |
| EC4 | Ödeme planı oluşturulurken bir taksit atlanır | Ardışık olmayan tarihler kabul edilir |
| EC5 | Mevcut alacaklardan ödeme planı oluşturulur | Mevcut alacaklar plana dönüştürülür |
| EC6 | Ödeme planı iptal edilir | Tüm ilişkili alacaklar iptal veya bağımsız kalır |
| EC7 | Taksit tarihi resmi tatile denk gelir | Uyarı gösterilir ama kabul edilir |
| EC8 | Plan oluşturulduktan sonra toplam tutar değiştirilir | Taksitler yeniden hesaplanır veya engellenir |
| EC9 | Plan kapsamında kısmi ödeme yapılır | İlgili taksit "Kısmi Ödendi" olur |

---

## US-5.6: Cari Hesap Ekstresi

**User Story**

Bir avukat olarak, müvekkilin cari hesap ekstresini görmek istiyorum ki tüm alacak, tahsilat ve gider hareketlerini tek ekranda takip edebilir ve müvekkile sunabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Müvekkil bazlı tüm mali hareketler kronolojik listelenir |
| AC2 | Açılış bakiyesi, hareketler ve kapanış bakiyesi gösterilir |
| AC3 | Tarih aralığı ile filtreleme yapılabilir |
| AC4 | Dosya bazlı filtreleme yapılabilir |
| AC5 | Hareket türüne göre filtreleme yapılabilir |
| AC6 | Ekstre PDF ve Excel olarak dışa aktarılabilir |
| AC7 | Ekstre müvekkile e-posta ile gönderilebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Müvekkil seçilmeden ekstre görüntülenir | "Müvekkil seçimi zorunludur" hatası |
| NC2 | Hiç mali hareketi olmayan müvekkil seçilir | "Bu müvekkilin mali hareketi bulunmuyor" mesajı |
| NC3 | Yetkisiz kullanıcı ekstre görüntüler | "Mali verilere erişim yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | 1000+ hareket olan müvekkil ekstresi | Sayfalama uygulanır, performans optimize edilir |
| EC2 | Çok geniş tarih aralığı seçilir (10 yıl) | Kabul edilir, yükleme süresi uyarısı |
| EC3 | Negatif bakiye (müvekkil alacaklı) | Farklı renkte gösterilir, parantez içinde |
| EC4 | Farklı para birimlerinde hareket var | Her para birimi ayrı sütunda veya dönüştürülmüş |
| EC5 | PDF export çok uzun (100+ sayfa) | Sayfa sınırı uyarısı veya bölünmüş dosya |
| EC6 | E-posta gönderiminde hata oluşur | Hata mesajı, PDF manuel indirilebilir |
| EC7 | Ekstre görüntülenirken yeni hareket eklenir | Yenileme ile güncellenir |
| EC8 | Açılış bakiyesi hesaplanırken seçilen tarih öncesi hareket yok | Açılış bakiyesi 0 |
| EC9 | İptal edilmiş hareketler ekstrede | Varsayılanda gizli, filtre ile gösterilebilir |

---

## US-5.7: Alacak Yaşlandırma Raporu

**User Story**

Bir kurucu ortak olarak, alacakların yaşlandırma raporunu görmek istiyorum ki vadesi geçen alacakları takip edebilir ve tahsilat önceliklerini belirleyebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Alacaklar yaş gruplarına ayrılır (0-30, 31-60, 61-90, 90+ gün) |
| AC2 | Her yaş grubu için toplam tutar gösterilir |
| AC3 | Müvekkil bazlı kırılım görüntülenebilir |
| AC4 | Dosya bazlı kırılım görüntülenebilir |
| AC5 | Sadece vadesi geçen alacaklar filtrelenebilir |
| AC6 | Rapor Excel olarak dışa aktarılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Hiç alacak kaydı yokken rapor görüntülenir | "Alacak kaydı bulunmuyor" mesajı |
| NC2 | Yetkisiz kullanıcı raporu görüntüler | "Mali raporlara erişim yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Tüm alacaklar aynı yaş grubunda | Diğer gruplar 0 olarak gösterilir |
| EC2 | 1000+ günlük alacak var | 90+ grubunda gösterilir |
| EC3 | Vadesi bugün olan alacak | 0-30 gün grubunda (0 günlük) |
| EC4 | Vade tarihi olmayan alacaklar | Ayrı "Vadesiz" grubunda gösterilir |
| EC5 | Kısmi ödenmiş alacaklar | Kalan tutar üzerinden yaşlandırma |
| EC6 | Rapor tarihi değiştirilerek geçmiş tarih baz alınır | O tarihteki duruma göre hesaplanır |
| EC7 | Çok fazla müvekkil (5000+) | Performans optimizasyonu, lazy loading |
| EC8 | Yaş grubu aralıkları özelleştirilmek istenir | Ayarlardan değiştirilebilir veya sabit |

---

## US-5.8: Tahsilat Makbuzu Oluşturma

**User Story**

Bir avukat olarak, tahsilat için makbuz oluşturmak istiyorum ki müvekkile resmi belge verebilir ve kayıt tutabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Makbuz numarası otomatik oluşturulur |
| AC2 | Makbuz tarihi tahsilat tarihi olarak atanır |
| AC3 | Müvekkil bilgileri otomatik doldurulur |
| AC4 | Tutar yazı ve rakamla gösterilir |
| AC5 | Makbuz PDF olarak indirilebilir |
| AC6 | Makbuz yazdırılabilir |
| AC7 | Makbuz müvekkile e-posta ile gönderilebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Tahsilat kaydedilmeden makbuz oluşturulmak istenir | "Önce tahsilat kaydedin" hatası |
| NC2 | İptal edilmiş tahsilat için makbuz istenir | "İptal edilmiş tahsilat için makbuz oluşturulamaz" hatası |
| NC3 | Yetkisiz kullanıcı makbuz oluşturur | "Makbuz oluşturma yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Tutar çok yüksek, yazıyla uzun oluyor | Makbuz formatı genişler veya küçük font |
| EC2 | Müvekkil adı çok uzun (tüzel kişi) | Satır kaydırma veya kısaltma |
| EC3 | Aynı tahsilat için ikinci makbuz istenir | "Bu tahsilat için makbuz mevcut. Tekrar oluşturulsun mu?" |
| EC4 | Makbuz numarası yıl değişiminde | Yeni seri başlar (2026-0001) |
| EC5 | E-posta gönderiminde hata | Hata mesajı, manuel indirme önerisi |
| EC6 | PDF oluşturulamıyor (sunucu hatası) | Hata mesajı, yeniden deneme butonu |
| EC7 | Makbuz şablonu değiştirilmek isteniyor | Ayarlardan şablon seçimi |
| EC8 | Dövizli tahsilat için makbuz | Döviz tutarı ve TL karşılığı birlikte gösterilir |

---

## US-5.9: Mali Hareket Düzeltme/İptal

**User Story**

Bir avukat olarak, hatalı mali kaydı düzeltmek veya iptal etmek istiyorum ki muhasebe kayıtları doğru olsun.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Mali hareket düzenlenebilir (tutar, tarih, açıklama) |
| AC2 | Düzenleme nedeni zorunlu olarak girilmelidir |
| AC3 | Düzenleme tarihçesi tutulur (eski/yeni değer) |
| AC4 | Mali hareket iptal edilebilir (silinmez, iptal işaretlenir) |
| AC5 | İptal nedeni zorunlu olarak girilmelidir |
| AC6 | İptal edilen hareketin eşleştirmeleri otomatik geri alınır |
| AC7 | Sadece yetkili kullanıcılar düzeltme/iptal yapabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Düzeltme nedeni girilmeden kaydedilir | "Düzeltme nedeni zorunludur" hatası |
| NC2 | İptal nedeni girilmeden iptal edilir | "İptal nedeni zorunludur" hatası |
| NC3 | Zaten iptal edilmiş hareket tekrar iptal edilir | "Bu hareket zaten iptal edilmiş" hatası |
| NC4 | Yetkisiz kullanıcı düzeltme yapar | "Mali kayıt düzeltme yetkiniz yok" hatası |
| NC5 | Eşleştirilmiş tahsilat tutarı düşürülür | "Eşleştirme tutarından az olamaz. Önce eşleştirmeyi güncelleyin" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Çok eski hareket düzeltilir (2 yıl önce) | Uyarı gösterilir, yönetici onayı gerekir |
| EC2 | Tahsilat iptal edilir, bağlı alacak ne olur | Alacak tekrar "açık" duruma geçer |
| EC3 | Alacak iptal edilir, bağlı tahsilat ne olur | Tahsilat eşleştirmesi kaldırılır, avans olur |
| EC4 | Ödeme planındaki taksit iptal edilir | Plan güncellenir, toplam değişir |
| EC5 | Düzeltme sırasında başka kullanıcı aynı kaydı düzenler | Çakışma uyarısı, son değişiklik gösterilir |
| EC6 | İptal edilen hareket raporlarda | Varsayılanda hariç tutulur, filtre ile dahil edilebilir |
| EC7 | Toplu iptal yapılır (10+ hareket) | Batch işlem, her biri için neden gerekir veya tek neden |
| EC8 | Düzeltme yapıldıktan sonra geri alınmak istenir | Düzeltme tarihçesinden eski değere dönülebilir |

---

## US-5.10: Mali Raporlar

**User Story**

Bir kurucu ortak olarak, mali raporları görmek istiyorum ki ofisin gelir-gider durumunu analiz edebilir ve kararlar verebilir.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Dönemsel gelir raporu görüntülenebilir |
| AC2 | Dönemsel gider raporu görüntülenebilir |
| AC3 | Alacak-tahsilat özet raporu görüntülenebilir |
| AC4 | Dosya bazlı karlılık raporu görüntülenebilir |
| AC5 | Avukat bazlı tahsilat raporu görüntülenebilir |
| AC6 | Tarih aralığı ve diğer filtreler uygulanabilir |
| AC7 | Tüm raporlar Excel ve PDF olarak dışa aktarılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Yetkisiz kullanıcı mali rapor görüntüler | "Mali raporlara erişim yetkiniz yok" hatası |
| NC2 | Hiç veri olmayan dönem seçilir | "Seçilen dönemde veri bulunmuyor" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Çok geniş tarih aralığı (10 yıl) | Performans uyarısı, aylık/yıllık özet önerilir |
| EC2 | Dosya karlılık hesaplanırken gider > gelir | Negatif karlılık gösterilir (zarar) |
| EC3 | Avukat ayrıldıktan sonra rapor | Eski kayıtlarda görünür, aktif/pasif filtresi |
| EC4 | Farklı para birimleri karışık raporda | TL'ye dönüştürülür veya ayrı gösterilir |
| EC5 | Rapor oluşturulurken yeni hareket eklenir | Rapor anı verileri gösterir, dinamik güncellenmez |
| EC6 | PDF çok büyük (100+ sayfa) | Özet mod önerilir veya bölünür |
| EC7 | Grafikli rapor istenir | Çizgi/bar grafik eklenir |
| EC8 | Karşılaştırmalı rapor (bu yıl vs geçen yıl) | İki dönem yan yana veya fark gösterilir |

---

## US-5.11: Masraf Avansı Yönetimi

**User Story**

Bir avukat olarak, müvekkilden alınan masraf avansını takip etmek istiyorum ki yapılan masrafları avanstan düşebilir ve kalan avansı görebilir.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Masraf avansı ayrı bir alacak türü olarak kaydedilir |
| AC2 | Gider kaydedilirken avanstan düşme seçeneği sunulur |
| AC3 | Kalan avans bakiyesi görüntülenebilir |
| AC4 | Avans yetersizse uyarı verilir |
| AC5 | Avans iadesi kaydedilebilir |
| AC6 | Dosya bazlı avans takibi yapılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Avanstan fazla masraf düşülmek istenir | "Avans bakiyesi yetersiz. Kalan: X TL" uyarısı |
| NC2 | Avans iadesi avans bakiyesinden fazla girilir | "İade tutarı avans bakiyesini geçemez" hatası |
| NC3 | Hiç avansı olmayan dosyadan avans düşülmek istenir | "Bu dosyada kullanılabilir avans yok" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Birden fazla dosyaya yayılmış avans | Her dosya için ayrı avans bakiyesi |
| EC2 | Genel avans (dosyasız) | Herhangi bir dosyanın masrafına kullanılabilir |
| EC3 | Avans iadesi yapılırken kalan masraf var | Uyarı: "Karşılanmamış X TL masraf var" |
| EC4 | Dosya kapanır, kalan avans ne olur | Avans iadesi veya başka dosyaya aktarım seçeneği |
| EC5 | Avans tahsilatı iptal edilir | Kullanılmış masraflar açık alacağa dönüşür |
| EC6 | Çok küçük avans kalıntısı (0.50 TL) | İade veya silme seçeneği |
| EC7 | Avans kullanım geçmişi görüntülenmek istenir | Detaylı log gösterilir |

---

# MODÜL 6: RAPORLAMA VE DASHBOARD

## US-6.1: Ana Dashboard Görüntüleme

**User Story**

Bir avukat olarak, sisteme girdiğimde özet bilgileri dashboard'da görmek istiyorum ki güncel durumu hızlıca kavrayabilir ve önceliklerimi belirleyebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Aktif dosya sayısı kartı gösterilir |
| AC2 | Bu aydaki/haftaki duruşma sayısı gösterilir |
| AC3 | Toplam alacak bakiyesi gösterilir |
| AC4 | Bu ayki tahsilat tutarı gösterilir |
| AC5 | Geciken görev sayısı gösterilir |
| AC6 | Bugünkü görevler listesi gösterilir |
| AC7 | Yaklaşan duruşmalar listesi gösterilir (7 gün) |
| AC8 | Vadesi geçen alacaklar uyarısı gösterilir |
| AC9 | Dashboard kullanıcı rolüne göre kişiselleştirilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Mali yetkisi olmayan kullanıcı dashboard görür | Alacak/tahsilat kartları gizlenir veya "Yetki gerekli" |
| NC2 | Hiç veri olmayan yeni sistemde dashboard açılır | "Henüz veri yok" mesajları, başlangıç rehberi |
| NC3 | Stajyer dashboard'a erişir | Sadece atandığı dosya/görev bilgileri görünür |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Bugün 50+ görev var | İlk 10 gösterilir, "Tümünü gör" linki |
| EC2 | Bu hafta 20+ duruşma var | İlk 10 gösterilir, takvime link |
| EC3 | Çok yüksek alacak bakiyesi (milyonlar) | Formatlanarak gösterilir (1.5M TL) |
| EC4 | Dashboard yüklenirken bağlantı yavaş | Loading skeleton gösterilir |
| EC5 | Gece yarısında dashboard açılır (gün değişimi) | Doğru günün verileri gösterilir |
| EC6 | Farklı saat dilimindeki kullanıcı | Kullanıcının saat dilimine göre hesaplanır |
| EC7 | Dashboard açıkken yeni görev eklenir | Manuel yenileme veya otomatik güncelleme |
| EC8 | Tüm kartlarda 0 değeri var | Kartlar gösterilir, "0" değeri ile |
| EC9 | Mobil cihazda dashboard görüntülenir | Responsive tasarım, kartlar alt alta |
| EC10 | Kullanıcının hiç atanmış dosyası yok | "Size atanmış dosya bulunmuyor" mesajı |

---

## US-6.2: Dashboard Özelleştirme

**User Story**

Bir avukat olarak, dashboard'umu özelleştirmek istiyorum ki en çok kullandığım bilgileri ön planda görebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Dashboard widget'ları sürükle-bırak ile sıralanabilir |
| AC2 | Widget'lar gizlenebilir/gösterilebilir |
| AC3 | Widget boyutları ayarlanabilir (küçük, orta, büyük) |
| AC4 | Özelleştirmeler kullanıcı bazlı kaydedilir |
| AC5 | Varsayılan düzene dönüş seçeneği vardır |
| AC6 | Yeni widget eklenebilir (kullanılabilir listeden) |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Yetkisiz widget eklenmeye çalışılır (mali widget, yetkisiz kullanıcı) | Widget listesinde görünmez veya eklenemez |
| NC2 | Tüm widget'lar gizlenir | "En az bir widget aktif olmalı" uyarısı veya boş dashboard |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Çok fazla widget eklenir (20+) | Scroll eklenir, performans uyarısı |
| EC2 | Sürükle-bırak sırasında bağlantı kesilir | Son kaydedilen düzen korunur |
| EC3 | Mobil cihazda sürükle-bırak | Touch destekli veya devre dışı |
| EC4 | Varsayılana dönülür, sonra geri alınmak istenir | Geri alma seçeneği (son 1 düzen) |
| EC5 | Widget silinip tekrar eklendiğinde | Varsayılan boyut ve konumda eklenir |
| EC6 | Kullanıcı silindikten sonra özelleştirmeler | Kullanıcı ile birlikte silinir |
| EC7 | Aynı widget iki kez eklenmeye çalışılır | Engellenir, "Bu widget zaten ekli" mesajı |

---

## US-6.3: Takvim Görünümü

**User Story**

Bir avukat olarak, takvim görünümünde duruşma ve görevlerimi görmek istiyorum ki zamanımı planlayabilir ve çakışmaları fark edebilir.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Günlük, haftalık, aylık görünüm seçenekleri vardır |
| AC2 | Duruşmalar takvimde farklı renkte gösterilir |
| AC3 | Görevler bitiş tarihine göre takvimde gösterilir |
| AC4 | Kritik tarihler (süre bitimi) takvimde gösterilir |
| AC5 | Etkinliğe tıklanınca detay açılır |
| AC6 | Takvim üzerinden yeni duruşma/görev eklenebilir |
| AC7 | Çakışan etkinlikler vurgulanır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Hiç etkinlik olmayan ay görüntülenir | Boş takvim gösterilir |
| NC2 | Yetkisiz kullanıcı başkasının takvimini görmeye çalışır | "Bu takvimi görüntüleme yetkiniz yok" veya kendi takvimi |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Aynı gün 10+ etkinlik var | Günün altında özet, tıklanınca liste |
| EC2 | Çok uzun etkinlik başlığı | Kısaltılır, hover ile tam gösterilir |
| EC3 | Gün içinde aynı saatte 3+ çakışma | Çakışma uyarısı belirgin şekilde gösterilir |
| EC4 | Takvim çok geçmiş/gelecek tarihe kaydırılır (5 yıl) | Veri yüklenmeye devam eder |
| EC5 | Takvimden sürükle-bırak ile tarih değiştirme | Etkinlik tarihi güncellenir, onay istenir |
| EC6 | Mobil cihazda takvim görünümü | Günlük görünüm varsayılan, swipe ile gün değişimi |
| EC7 | Resmi tatiller takvimde işaretli | Farklı arka plan rengi, tatil adı |
| EC8 | Hafta başlangıç günü ayarlanabilir (Pazartesi/Pazar) | Kullanıcı tercihine göre |
| EC9 | Takvim yazdırılmak istenir | Yazdırma dostu format oluşturulur |
| EC10 | Takvim harici uygulamaya aktarılmak istenir (Google Calendar) | iCal/ICS formatında export |

---

## US-6.4: Dosya Raporları

**User Story**

Bir kurucu ortak olarak, dosya raporlarını görmek istiyorum ki dosya durumlarını analiz edebilir ve ofis performansını değerlendirebilir.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Aktif/kapanan dosya sayısı özeti gösterilir |
| AC2 | Dosya türüne göre dağılım grafiği gösterilir |
| AC3 | Dosya durumuna göre dağılım gösterilir |
| AC4 | Avukat bazlı dosya dağılımı gösterilir |
| AC5 | Dönemsel dosya açılış/kapanış trendi gösterilir |
| AC6 | Ortalama dosya çözüm süresi hesaplanır |
| AC7 | Dosya kazanma/kaybetme oranı gösterilir |
| AC8 | Filtreler uygulanabilir (tarih, tür, durum, avukat) |
| AC9 | Rapor Excel ve PDF olarak dışa aktarılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Yetkisiz kullanıcı tüm dosya raporunu görmeye çalışır | Sadece kendi dosyalarının raporu gösterilir |
| NC2 | Hiç dosya olmayan dönem seçilir | "Seçilen dönemde dosya bulunmuyor" mesajı |
| NC3 | Stajyer dosya raporlarına erişir | Erişim reddedilir veya sınırlı veri |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | 10.000+ dosya raporlanacak | Performans uyarısı, sayfalama veya özet mod |
| EC2 | Hiç kapanan dosya yok, kazanma oranı hesaplanamıyor | "Henüz kapanan dosya yok" mesajı |
| EC3 | Tüm dosyalar aynı türde | Grafik tek dilim gösterir |
| EC4 | Avukat ayrıldıktan sonra raporda görünümü | "Eski Çalışan" etiketi ile gösterilir |
| EC5 | Çok geniş tarih aralığı seçilir | Yıllık özet önerilir |
| EC6 | Filtre kombinasyonu hiç sonuç döndürmüyor | "Kriterlere uygun dosya yok" mesajı |
| EC7 | Grafik çok fazla kategori içeriyor (50+ dosya türü) | "Diğer" kategorisi oluşturulur |
| EC8 | PDF export grafikleri içeriyor | Grafikler resim olarak eklenir |
| EC9 | Karşılaştırmalı rapor istenir (bu yıl vs geçen yıl) | Yan yana veya üst üste grafik |

---

## US-6.5: Duruşma Raporları

**User Story**

Bir kurucu ortak olarak, duruşma raporlarını görmek istiyorum ki duruşma yükünü takip edebilir ve avukat performansını değerlendirebilir.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Dönemsel duruşma sayısı gösterilir |
| AC2 | Avukat bazlı duruşma dağılımı gösterilir |
| AC3 | Mahkeme bazlı duruşma dağılımı gösterilir |
| AC4 | Duruşma sonuçları dağılımı gösterilir (tamamlanan/ertelenen) |
| AC5 | Günlük/haftalık/aylık duruşma yoğunluğu grafiği gösterilir |
| AC6 | Yaklaşan duruşmalar listesi gösterilir |
| AC7 | Filtreler uygulanabilir (tarih, avukat, mahkeme, dosya türü) |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Yetkisiz kullanıcı tüm duruşma raporunu görmeye çalışır | Sadece kendi duruşmalarının raporu |
| NC2 | Hiç duruşma kaydı yokken rapor görüntülenir | "Duruşma kaydı bulunmuyor" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Bir avukatın ayda 100+ duruşması var | Yoğunluk uyarısı gösterilir |
| EC2 | Tüm duruşmalar ertelenmiş | Erteleme oranı %100 gösterilir |
| EC3 | Mahkeme adı değişmiş (birleşme, isim değişikliği) | Eski ve yeni isim ayrı veya birleşik gösterilir |
| EC4 | Aynı gün aynı avukat 5+ duruşma | Çakışma analizi gösterilir |
| EC5 | Geçmiş 5 yıllık duruşma trendi istenir | Performans uyarısı, yıllık özet |
| EC6 | Duruşma saati bilgisi eksik kayıtlar | "Saat bilgisi yok" olarak işaretlenir |
| EC7 | Resmi tatildeki duruşmalar | Ayrı işaretlenir (muhtemelen hatalı kayıt) |
| EC8 | Duruşma sonucu girilmemiş geçmiş duruşmalar | "Sonuç bekleniyor" uyarısı listesi |

---

## US-6.6: Performans Raporları

**User Story**

Bir kurucu ortak olarak, avukat performans raporlarını görmek istiyorum ki ekibin verimliliğini ölçebilir ve iş dağılımını optimize edebilir.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Avukat bazlı aktif dosya sayısı gösterilir |
| AC2 | Avukat bazlı kapanan dosya sayısı ve kazanma oranı gösterilir |
| AC3 | Avukat bazlı duruşma sayısı gösterilir |
| AC4 | Avukat bazlı görev tamamlama oranı gösterilir |
| AC5 | Avukat bazlı tahsilat tutarı gösterilir |
| AC6 | Avukatlar arası karşılaştırma grafiği gösterilir |
| AC7 | Dönemsel performans trendi gösterilir |
| AC8 | Hedef belirleme ve hedefe göre kıyaslama yapılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Yetkisiz kullanıcı performans raporunu görmeye çalışır | "Performans raporlarına erişim yetkiniz yok" hatası |
| NC2 | Avukat kendi performans raporunu görmek ister | Sadece kendi verilerini görür veya engellenir (politikaya göre) |
| NC3 | Tek avukatlı ofiste karşılaştırma raporu | "Karşılaştırma için birden fazla avukat gerekli" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Yeni başlayan avukatın performansı | Dönem ortalamasına göre değil, kendi süresine göre değerlendirme |
| EC2 | Avukat izne çıkmış, performans düşük görünüyor | İzin dönemleri hariç tutma seçeneği |
| EC3 | Part-time çalışan avukat | Çalışma saatine göre normalize etme |
| EC4 | Çok farklı uzmanlık alanları (ceza vs ticaret) | Dosya türüne göre ayrı değerlendirme |
| EC5 | Hedef belirlenmemiş | "Hedef belirlenmedi" gösterilir, kıyaslama yapılmaz |
| EC6 | Tüm görevler gecikmiş | %0 zamanında tamamlama, uyarı |
| EC7 | Performans skoru negatif çıkabilir mi | Minimum 0 veya negatif gösterilir |
| EC8 | Pasif avukatın geçmiş performansı | Görüntülenebilir, "Pasif" etiketi ile |
| EC9 | Performans raporu çalışanlara açık/kapalı ayarı | Sistem ayarlarından belirlenir |

---

## US-6.7: Hatırlatma ve Bildirim Merkezi

**User Story**

Bir avukat olarak, tüm hatırlatma ve bildirimlerimi tek merkezden görmek istiyorum ki hiçbir önemli bildirimi kaçırmayayım.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Okunmamış bildirim sayısı header'da gösterilir |
| AC2 | Bildirimler kronolojik sırada listelenir |
| AC3 | Bildirim türüne göre filtreleme yapılabilir |
| AC4 | Bildirim okundu/okunmadı olarak işaretlenebilir |
| AC5 | Tüm bildirimleri okundu işaretle seçeneği vardır |
| AC6 | Bildirime tıklanınca ilgili kayda gidilir |
| AC7 | Eski bildirimler otomatik arşivlenir (30 gün) |
| AC8 | Bildirim tercihleri ayarlanabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Hiç bildirim yokken merkez açılır | "Bildiriminiz bulunmuyor" mesajı |
| NC2 | Bildirime tıklanır ama ilgili kayıt silinmiş | "İlgili kayıt bulunamadı" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | 1000+ okunmamış bildirim var | Sayfalama, performans optimizasyonu |
| EC2 | Aynı anda çok fazla bildirim oluşuyor (toplu işlem) | Gruplanarak gösterilir |
| EC3 | Bildirim tercihleri kapatılmış, önemli bildirim | Kritik bildirimler her zaman gönderilir |
| EC4 | Bildirim e-posta olarak da gönderilecek ama e-posta adresi yok | Sadece sistem içi bildirim |
| EC5 | Kullanıcı çevrimdışıyken bildirimler birikir | Sonraki girişte toplu gösterilir |
| EC6 | Bildirim içeriği çok uzun | Kısaltılır, tıklanınca tam gösterilir |
| EC7 | Mobil cihazda push notification | Tarayıcı izni ile çalışır |
| EC8 | Bildirim tercihlerinde tüm bildirimler kapatılmak istenir | Uyarı: "Önemli bildirimleri kaçırabilirsiniz" |
| EC9 | Okunmamış bildirimleri işaretlerken sayfa yenileniyor | İşlem tamamlanana kadar beklenir veya batch |

---

## US-6.8: Rapor Kaydetme ve Zamanlama

**User Story**

Bir kurucu ortak olarak, sık kullandığım raporları kaydetmek ve otomatik gönderim zamanlamak istiyorum ki rutin raporlama işlemlerinden tasarruf edeyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Rapor parametreleri kaydedilebilir (isim verilir) |
| AC2 | Kaydedilen raporlar listesi görüntülenebilir |
| AC3 | Kaydedilmiş rapor tek tıkla çalıştırılabilir |
| AC4 | Rapor düzenli çalışacak şekilde zamanlanabilir (günlük, haftalık, aylık) |
| AC5 | Zamanlanan rapor e-posta ile gönderilebilir |
| AC6 | Zamanlanan rapor birden fazla alıcıya gönderilebilir |
| AC7 | Zamanlama düzenlenebilir veya iptal edilebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Rapor ismi verilmeden kaydedilir | "Rapor ismi zorunludur" hatası |
| NC2 | Aynı isimle ikinci rapor kaydedilir | "Bu isimde rapor mevcut. Üzerine yazılsın mı?" |
| NC3 | Geçersiz e-posta adresine zamanlama yapılır | "Geçerli e-posta adresi girin" hatası |
| NC4 | Yetkisiz kullanıcı başkasının kaydedilmiş raporunu siler | "Bu raporu silme yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | 100+ kaydedilmiş rapor var | Kategorileme veya arama eklenir |
| EC2 | Zamanlanan rapor gönderim saatinde sunucu kapalı | Sonraki açılışta gönderilir veya atlanır |
| EC3 | Rapor alıcısı e-postayı almıyor (spam) | Gönderim logu tutulur, durum kontrol edilir |
| EC4 | Zamanlanmış rapor çok büyük (50MB+) | E-posta limiti uyarısı, link olarak gönderim |
| EC5 | Rapor parametrelerinde kullanılan filtre artık geçersiz (silinmiş avukat) | Uyarı mesajı, rapor hatasız çalışır |
| EC6 | Kullanıcı pasife alındığında zamanlanmış raporları | İptal edilir veya yöneticiye aktarılır |
| EC7 | Aynı rapor birden fazla zaman için zamanlanır | Kabul edilir, her biri ayrı çalışır |
| EC8 | Zamanlanan rapor ayda 1000+ kez çalışacak şekilde ayarlanır | Makul sınır (günde 1) veya uyarı |

---

## US-6.9: Dışa Aktarma (Export)

**User Story**

Bir avukat olarak, raporları ve listeleri farklı formatlarda dışa aktarmak istiyorum ki verileri başka sistemlerde kullanabilir veya müvekkillere sunabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Excel (.xlsx) formatında export desteklenir |
| AC2 | PDF formatında export desteklenir |
| AC3 | CSV formatında export desteklenir |
| AC4 | Export dosya adı otomatik oluşturulur (Rapor_Tarih) |
| AC5 | Büyük veri setleri için arka planda export yapılır |
| AC6 | Export tamamlandığında bildirim gönderilir |
| AC7 | Export geçmişi görüntülenebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Boş veri seti export edilmeye çalışılır | "Export edilecek veri yok" mesajı |
| NC2 | Yetkisiz kullanıcı başkasının verilerini export eder | Sadece yetkili olduğu veriler export edilir |
| NC3 | Export sırasında oturum süresi dolar | Arka plan işlemi devam eder, sonra indirilebilir |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | 100.000+ satır export edilecek | Arka plan işlemi, tamamlanınca bildirim |
| EC2 | Excel satır limiti aşılıyor (1M+) | Dosya bölünür veya uyarı verilir |
| EC3 | PDF çok uzun (500+ sayfa) | Boyut uyarısı, bölme önerisi |
| EC4 | Türkçe karakterler Excel'de bozuk görünüyor | UTF-8 encoding ile doğru gösterim |
| EC5 | Export dosyası çok büyük (100MB+) | Sıkıştırma (zip) önerisi |
| EC6 | Eş zamanlı 10+ kullanıcı export yapar | Kuyruk sistemi, sırayla işlenir |
| EC7 | Export sırasında sunucu yeniden başlar | İşlem kaybolur, yeniden başlatma gerekir |
| EC8 | CSV'de virgül içeren veriler | Tırnak içine alınır veya escape edilir |
| EC9 | PDF'te grafikler var | Grafikler resim olarak gömülür |
| EC10 | Export'a şifre koymak isteniyor | PDF şifreleme seçeneği |

---

## US-6.10: Sistem Logları ve Denetim

**User Story**

Bir sistem yöneticisi olarak, sistem loglarını ve denetim kayıtlarını görmek istiyorum ki güvenlik olaylarını takip edebilir ve sorunları tespit edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Giriş/çıkış logları görüntülenebilir |
| AC2 | Kayıt oluşturma/güncelleme/silme logları görüntülenebilir |
| AC3 | Yetki değişikliği logları görüntülenebilir |
| AC4 | Başarısız giriş denemeleri görüntülenebilir |
| AC5 | Loglar tarih, kullanıcı, işlem türüne göre filtrelenebilir |
| AC6 | Loglar export edilebilir |
| AC7 | Kritik güvenlik olayları için anlık uyarı gönderilebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Yetkisiz kullanıcı sistem loglarına erişmeye çalışır | "Sistem loglarına erişim yetkiniz yok" hatası |
| NC2 | Çok geniş tarih aralığı seçilir (5 yıl) | Performans uyarısı, parçalı yükleme |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Milyonlarca log kaydı var | Sayfalama, performans optimizasyonu, arşivleme |
| EC2 | Log kaydı silinmek istenir | Loglar silinemez, sadece arşivlenebilir |
| EC3 | Log değiştirilmek istenir | Loglar değiştirilemez (immutable) |
| EC4 | Hassas veri logda görünüyor (şifre) | Şifreler asla loglanmaz, maskelenir |
| EC5 | Aynı IP'den çok fazla başarısız giriş | Otomatik uyarı, geçici IP engeli |
| EC6 | Log veritabanı doldu | Eski loglar arşivlenir, disk alanı uyarısı |
| EC7 | Saat dilimi farklılığı logda | UTC ve yerel saat birlikte gösterilir |
| EC8 | Kullanıcı adı değişmiş, eski logda eski isim | Eski isim korunur, referans ID ile bağlantı |
| EC9 | Toplu işlem 1000+ log oluşturur | Özet log kaydı + detay linki |
| EC10 | Log detayında JSON/XML formatında veri | Formatlı görüntüleme, expand/collapse |

---

## US-6.11: Sistem Ayarları

**User Story**

Bir sistem yöneticisi olarak, sistem ayarlarını yönetmek istiyorum ki ofis ihtiyaçlarına göre sistemi yapılandırabilirim.

**Acceptance Criteria**

| # | Kriter |
|---|--------|
| AC1 | Ofis bilgileri (isim, adres, logo) ayarlanabilir |
| AC2 | Varsayılan değerler ayarlanabilir (KDV oranı, para birimi) |
| AC3 | Numara formatları ayarlanabilir (dosya no, makbuz no) |
| AC4 | E-posta ayarları yapılandırılabilir (SMTP) |
| AC5 | Oturum süresi ayarlanabilir |
| AC6 | Şifre politikası ayarlanabilir |
| AC7 | Bildirim varsayılanları ayarlanabilir |
| AC8 | Yedekleme ayarları yapılandırılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| NC1 | Yetkisiz kullanıcı sistem ayarlarına erişir | "Sistem ayarlarına erişim yetkiniz yok" hatası |
| NC2 | Geçersiz SMTP ayarları kaydedilir | "E-posta sunucusuna bağlanılamadı" hatası |
| NC3 | Oturum süresi 0 veya negatif girilir | "Geçerli bir süre girin" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---------|----------------|
| EC1 | Logo dosyası çok büyük (10MB) | Boyut sınırı uyarısı, sıkıştırma önerisi |
| EC2 | Dosya no formatı değiştirilir, mevcut kayıtlar | Mevcut kayıtlar etkilenmez, yeniler yeni formatta |
| EC3 | KDV oranı değiştirilir | Mevcut kayıtlar etkilenmez, yeniler yeni oranla |
| EC4 | E-posta testi yapılır ama alınamıyor | Test sonucu gösterilir, spam kontrolü önerisi |
| EC5 | Şifre politikası sıkılaştırılır | Mevcut şifreler geçerli, sonraki değişiklikte uygulanır |
| EC6 | Yedekleme sırasında sunucu yoğun | Gece saatlerine zamanlama önerisi |
| EC7 | Birden fazla para birimi etkinleştirilir | Kur yönetimi ekranı aktif olur |
| EC8 | Ayarlar değiştirilirken başka admin aynı ayarı değiştirir | Son kaydeden kazanır, çakışma uyarısı |
| EC9 | Kritik ayar değişikliği yapılır (güvenlik) | Onay kutusu, admin şifresi tekrar istenir |
| EC10 | Yedekleme dosyası indirilmek istenir | Şifreli, güvenli indirme bağlantısı |

---

# ÖZET TABLO

| Parça | Modül | User Story Sayısı |
|-------|-------|-------------------|
| 1 | Kullanıcı ve Yetki Yönetimi | 6 |
| 2 | Müvekkil Yönetimi | 8 |
| 3 | Dosya/Dava Yönetimi | 10 |
| 4 | İş Listesi ve Görev Yönetimi | 11 |
| 5 | Mali Takip (Alacak-Borç) | 11 |
| 6 | Raporlama ve Dashboard | 11 |
| **Toplam** | **6 Modül** | **57 User Story** |

---

# TAMAMLANAN DOKÜMAN İÇERİĞİ

Her User Story için hazırlanan içerikler:

- ✅ User Story açıklaması
- ✅ Acceptance Criteria (5-9 kriter/US)
- ✅ Negative Cases (3-7 senaryo/US)
- ✅ Edge Cases (6-10 senaryo/US)

---

**Doküman Sonu**
