### Parça 1: Kullanıcı ve Yetki Yönetimi

> **Not:** Kullanıcı rolleri, yetki matrisi ve erişim kapsamı için `docs/02-proje-plani.md` ve `docs/00-terminoloji-ve-kurallar.md` dosyalarındaki tablolar referans alınmalıdır. Aşağıdaki user story'ler bu kuralların uygulama detaylarını açıklar.

#### US-1.1: Kullanıcı Girişi

**User Story**

Bir kullanıcı olarak, kullanıcı adı ve şifremle sisteme giriş yapmak istiyorum ki yetkilendirilmiş işlemlerimi gerçekleştirebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Kullanıcı adı ve şifre alanları zorunludur, boş bırakılamaz |
| AC2 | Doğru bilgilerle giriş yapıldığında ana sayfaya yönlendirilir |
| AC3 | Hatalı bilgilerle girişte "Kullanıcı adı veya şifre hatalı" mesajı gösterilir |
| AC4 | Giriş sonrası son giriş tarihi güncellenir |
| AC5 | Oturum süresi dolduğunda otomatik çıkış yapılır ve login sayfasına yönlendirilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Kullanıcı adı boş, şifre dolu gönderilir | "Kullanıcı adı zorunludur" hatası |
| NC2 | Kullanıcı adı dolu, şifre boş gönderilir | "Şifre zorunludur" hatası |
| NC3 | Sistemde olmayan kullanıcı adı girilir | "Kullanıcı adı veya şifre hatalı" (güvenlik için spesifik değil) |
| NC4 | Doğru kullanıcı adı, yanlış şifre girilir | "Kullanıcı adı veya şifre hatalı" |
| NC5 | Pasif durumdaki kullanıcı giriş yapmaya çalışır | "Hesabınız aktif değil, yönetici ile iletişime geçin" |
| NC6 | SQL injection denemesi yapılır | Giriş reddedilir, log kaydı oluşturulur |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Kullanıcı adında Türkçe karakter var (şükrü) | Normal şekilde giriş yapılabilir |
| EC2 | Şifrede özel karakterler var (!@#$%^&*) | Normal şekilde doğrulanır |
| EC3 | Aynı anda iki farklı tarayıcıdan giriş | Sistem ayarına göre: izin ver veya ilk oturumu sonlandır |
| EC4 | Giriş sırasında internet kesilir | İşlem timeout olur, hata mesajı gösterilir |
| EC5 | Çok uzun kullanıcı adı girilir (1000+ karakter) | Maksimum karakter sınırı uygulanır |
| EC6 | Caps Lock açıkken şifre girilir | Kullanıcıya Caps Lock uyarısı gösterilir |

#### US-1.2: Başarısız Giriş Kilidi

**User Story**

Sistem yöneticisi olarak, brute force saldırılarını önlemek için belirli sayıda başarısız girişten sonra hesabın geçici olarak kilitlenmesini istiyorum.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | 5 başarısız giriş denemesinden sonra hesap 15 dakika kilitlenir |
| AC2 | Kilitli hesaba giriş denendiğinde kalan süre gösterilir |
| AC3 | Kilit süresi dolduktan sonra başarısız deneme sayacı sıfırlanır |
| AC4 | Başarılı giriş yapıldığında deneme sayacı sıfırlanır |
| AC5 | Her başarısız deneme loglanır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Kilitli hesaba doğru şifreyle giriş denenir | Kilit mesajı gösterilir, giriş yapılamaz |
| NC2 | Farklı IP adreslerinden aynı hesaba saldırı | Tüm IP'lerden denemeler sayılır, hesap kilitlenir |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | 4 başarısız denemeden sonra 15 dakika beklenir | Sayaç sıfırlanmaz, 5. denemede kilitlenir (sayaç timeout süresi ayrı olmalı) |
| EC2 | Kilit süresi tam dolmak üzereyken giriş denenir | Hala kilitli mesajı gösterilir |
| EC3 | Sunucu saati değiştirilir/saat farkı oluşur | Kilit süresi sunucu saatine göre hesaplanır |
| EC4 | Hesap kilitlendikten sonra şifre sıfırlama istenir | Şifre sıfırlama kilidi etkilemez, mail gönderilir |

#### US-1.3: Kullanıcı Oluşturma

**User Story**

Sistem yöneticisi olarak, yeni kullanıcı oluşturmak istiyorum ki ofise katılan personel sistemi kullanabilsin.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Kullanıcı adı benzersiz olmalıdır |
| AC2 | E-posta adresi geçerli formatta olmalıdır |
| AC3 | Şifre minimum 8 karakter, en az 1 büyük harf, 1 küçük harf, 1 rakam içermelidir |
| AC4 | En az bir rol atanmalıdır |
| AC5 | Oluşturma sonrası kullanıcıya bilgilendirme e-postası gönderilir |
| AC6 | Yeni kullanıcı varsayılan olarak "Aktif" durumundadır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Mevcut kullanıcı adıyla kayıt denenir | "Bu kullanıcı adı zaten kullanılıyor" hatası |
| NC2 | Geçersiz e-posta formatı girilir (test@) | "Geçerli bir e-posta adresi girin" hatası |
| NC3 | Şifre politikasına uymayan şifre girilir (123456) | "Şifre en az 8 karakter, büyük/küçük harf ve rakam içermelidir" |
| NC4 | Rol atamadan kayıt denenir | "En az bir rol seçmelisiniz" hatası |
| NC5 | Yetkisiz kullanıcı (avukat) kullanıcı oluşturmaya çalışır | "Bu işlem için yetkiniz bulunmuyor" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Kullanıcı adı sadece rakamlardan oluşur (12345) | Kabul edilir (iş kuralına göre değişebilir) |
| EC2 | Kullanıcı adı maksimum uzunlukta (50 karakter) | Kabul edilir |
| EC3 | Kullanıcı adı minimum uzunlukta (3 karakter) | Kabul edilir |
| EC4 | E-posta subdomainli (user@mail.law.firm.com) | Kabul edilir |
| EC5 | Şifre tam minimum gereksinimleri karşılar (Abcdefg1) | Kabul edilir |
| EC6 | Kullanıcı adında boşluk var (ali veli) | Reddedilir, boşluk kullanılamaz |
| EC7 | Aynı e-posta ile ikinci kullanıcı oluşturulur | İş kuralına göre: izin ver veya reddet |
| EC8 | Çoklu rol atanır (Avukat + Muhasebeci) | Kabul edilir, yetkiler birleştirilir |

#### US-1.4: Rol Tanımlama

**User Story**

Sistem yöneticisi olarak, özel roller tanımlayıp bu rollere yetkiler atamak istiyorum ki farklı personel gruplarının erişimlerini yönetebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Rol adı benzersiz olmalıdır |
| AC2 | Rol açıklaması opsiyoneldir |
| AC3 | Role en az bir yetki atanabilir |
| AC4 | Varsayılan roller silinemez, sadece yetkileri değiştirilebilir |
| AC5 | Rol silindiğinde o role sahip kullanıcılar varsayılan role geçer |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Mevcut rol adıyla yeni rol oluşturulur | "Bu rol adı zaten mevcut" hatası |
| NC2 | Varsayılan rol (Sistem Yöneticisi) silinmeye çalışılır | "Varsayılan roller silinemez" hatası |
| NC3 | Kullanıcıya atanmış rol silinir | Uyarı gösterilir, onay sonrası kullanıcılar varsayılan role aktarılır |
| NC4 | Boş isimle rol oluşturulur | "Rol adı zorunludur" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Role hiç yetki atanmadan kaydedilir | Kabul edilir, kullanıcı hiçbir işlem yapamaz |
| EC2 | Rol adı çok uzun (200+ karakter) | Maksimum 100 karakter sınırı uygulanır |
| EC3 | Rol adı özel karakterler içerir (Avukat@Kıdemli) | Kabul edilir veya filtrelenir (iş kuralı) |
| EC4 | Aynı anda iki admin aynı rolü düzenler | Son kaydeden kazanır veya çakışma uyarısı |
| EC5 | Tüm yetkiler tek role atanır | Kabul edilir |
| EC6 | Rol 100+ kullanıcıya atanmışken silinir | Performans sorunu oluşabilir, batch işlem yapılmalı |

#### US-1.5: Şifre Sıfırlama

**User Story**

Bir kullanıcı olarak, şifremi unuttuğumda e-posta ile sıfırlama bağlantısı almak istiyorum ki hesabıma tekrar erişebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Kayıtlı e-posta adresine sıfırlama linki gönderilir |
| AC2 | Sıfırlama linki 24 saat geçerlidir |
| AC3 | Link kullanıldıktan sonra geçersiz olur |
| AC4 | Yeni şifre mevcut şifreyle aynı olamaz |
| AC5 | Şifre değiştirildikten sonra tüm aktif oturumlar sonlandırılır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Sistemde olmayan e-posta ile sıfırlama istenir | Güvenlik için aynı mesaj: "E-posta adresinize talimatlar gönderildi" |
| NC2 | Süresi dolmuş link kullanılır | "Bu bağlantının süresi dolmuş" hatası |
| NC3 | Aynı link ikinci kez kullanılır | "Bu bağlantı daha önce kullanılmış" hatası |
| NC4 | Eski şifre ile aynı yeni şifre girilir | "Yeni şifre eski şifrenizden farklı olmalıdır" |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Kullanıcı 5 dakika içinde 10 kez sıfırlama ister | Rate limiting uygulanır, beklemesi istenir |
| EC2 | E-posta sunucusu çalışmıyor | Kullanıcıya hata mesajı, admin'e bildirim |
| EC3 | Link'e tıklanmadan 23 saat 59 dakika geçer | Hala geçerli |
| EC4 | Sıfırlama sırasında hesap pasife alınır | İşlem reddedilir |
| EC5 | Mobil cihazda e-postadaki link açılır | Mobil uyumlu sıfırlama sayfası gösterilir |

#### US-1.6: Yetki Kontrolü

**User Story**

Sistem olarak, her işlemde kullanıcının yetkisini kontrol etmek istiyorum ki yetkisiz erişim engellensin.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Yetkisiz işlem denemesi loglanır |
| AC2 | Yetkisiz erişimde kullanıcı dostu hata mesajı gösterilir |
| AC3 | Menüde sadece yetkili olduğu modüller görünür |
| AC4 | URL ile doğrudan yetkisiz sayfaya erişim engellenir |
| AC5 | API çağrılarında yetki kontrolü yapılır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Stajyer, kullanıcı yönetimi sayfasına URL ile erişmeye çalışır | 403 Forbidden, ana sayfaya yönlendirme |
| NC2 | Sekreter, mali rapor API'sini çağırır | 403 yanıtı, log kaydı |
| NC3 | Avukat, atanmadığı dosyayı görüntülemeye çalışır | "Bu dosyaya erişim yetkiniz yok" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Kullanıcının rolü işlem sırasında değiştirilir | Mevcut oturum eski yetkilerle devam eder, sonraki girişte güncellenir |
| EC2 | Kullanıcının tüm rolleri kaldırılır | Hiçbir işlem yapamaz, sadece dashboard görür |
| EC3 | Rol yetkisi kaldırılırken kullanıcı aktif işlem yapıyor | İşlem tamamlanır, sonraki işlem engellenir |
| EC4 | Admin kendi admin yetkisini kaldırır | Engellenir: "Kendi yetkinizi kaldıramazsınız" |
| EC5 | Sistemdeki son admin silinmeye çalışılır | Engellenir: "Sistemde en az bir yönetici olmalıdır" |

