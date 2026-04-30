### Parça 5: Mali Takip (Alacak-Borç)

> **Not:** Tüm mali terimler, tahsilat eşleştirme politikaları ve rapor performans eşikleri için `docs/00-terminoloji-ve-kurallar.md` dosyasındaki ortak kurallar geçerlidir. Bu dosyada belirtilen user story'ler, o kuralların uygulama detaylarını açıklar.

#### US-5.1: Alacak Kaydı Oluşturma

**User Story**

Bir avukat olarak, müvekkilden alacak kaydı oluşturmak istiyorum ki vekalet ücreti ve masrafları takip edebilir, tahsilat yapabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Alacak türü seçilmelidir (vekalet ücreti, danışmanlık, duruşma ücreti, masraf avansı) |
| AC2 | Tutar zorunludur ve pozitif olmalıdır |
| AC3 | Müvekkil seçimi zorunludur |
| AC4 | Dosya bağlantısı opsiyoneldir (genel alacak için) |
| AC5 | Vade tarihi belirlenebilir |
| AC6 | KDV dahil/hariç seçimi yapılabilir |
| AC7 | Alacak varsayılan olarak "Bekliyor" durumunda oluşturulur |
| AC8 | Alacak kaydı oluşturulduğunda müvekkil bakiyesi güncellenir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Tutar girilmeden kaydedilir | "Tutar zorunludur" hatası |
| NC2 | Tutar 0 veya negatif girilir | "Tutar pozitif bir değer olmalıdır" hatası |
| NC3 | Müvekkil seçilmeden kaydedilir | "Müvekkil seçimi zorunludur" hatası |
| NC4 | Alacak türü seçilmeden kaydedilir | "Alacak türü seçiniz" hatası |
| NC5 | Arşivlenmiş müvekkile alacak eklenir | "Arşivlenmiş müvekkile alacak eklenemez" hatası |
| NC6 | Kapanmış dosyaya alacak eklenir | Uyarı gösterilir, onay ile kabul edilir |
| NC7 | Yetkisiz kullanıcı alacak oluşturur | "Mali kayıt oluşturma yetkiniz yok" hatası |
**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Çok yüksek tutar girilir (1.000.000.000 TL) | Kabul edilir, format kontrolü yapılır |
| EC2 | Çok düşük tutar girilir (0.01 TL) | Kabul edilir |
| EC3 | Vade tarihi geçmişte girilir | Uyarı gösterilir, kabul edilir (geriye dönük kayıt) |
| EC4 | Vade tarihi çok uzak gelecekte (10 yıl) | Kabul edilir |
| EC5 | KDV oranı %0 girilir (istisna) | Kabul edilir |
| EC6 | KDV oranı standart dışı girilir (%25) | Kabul edilir veya önceden tanımlı oranlardan seçim |
| EC7 | Aynı müvekkile aynı tutarda ikinci alacak | Mükerrer uyarısı, onay ile kabul |
| EC8 | Alacak açıklaması çok uzun (5000+ karakter) | Maksimum karakter sınırı veya kabul |
| EC9 | Farklı para birimi seçilir (USD, EUR) | Kabul edilir, kur bilgisi istenir veya sabit kur |
| EC10 | Dosya seçilip sonra müvekkil değiştirilir | Dosyanın müvekkili otomatik seçilir |

_Kullanılabilirlik Notu:_ Alacak formundaki para birimi, KDV oranı ve belge numarası alanları ofis varsayılanlarıyla otomatik doldurulmalı; kullanıcı yalnızca gerektiğinde bu değerleri değiştirmelidir.

#### US-5.2: Tahsilat Kaydı Oluşturma

**User Story**

Bir avukat olarak, müvekkilden tahsilat kaydı oluşturmak istiyorum ki ödemeleri kayıt altına alabilir ve alacak bakiyesini güncelleyebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Tahsilat tutarı zorunludur ve pozitif olmalıdır |
| AC2 | Müvekkil seçimi zorunludur |
| AC3 | Ödeme yöntemi seçilmelidir (nakit, havale, EFT, kredi kartı, çek) |
| AC4 | Tahsilat tarihi zorunludur |
| AC5 | Tahsilat bir veya birden fazla alacağa eşleştirilebilir |
| AC6 | Belge numarası (makbuz, dekont) girilebilir |
| AC7 | Tahsilat kaydedildiğinde müvekkil bakiyesi güncellenir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Tutar girilmeden kaydedilir | "Tutar zorunludur" hatası |
| NC2 | Tutar negatif girilir | "Tutar pozitif olmalıdır" hatası |
| NC3 | Müvekkil seçilmeden kaydedilir | "Müvekkil seçimi zorunludur" hatası |
| NC4 | Ödeme yöntemi seçilmeden kaydedilir | "Ödeme yöntemi seçiniz" hatası |
| NC5 | Tahsilat tarihi boş bırakılır | "Tahsilat tarihi zorunludur" hatası |
| NC6 | Yetkisiz kullanıcı tahsilat kaydeder | "Mali kayıt oluşturma yetkiniz yok" hatası |
| NC7 | Hiç alacağı olmayan müvekkile tahsilat | Kabul edilir (avans olarak) veya uyarı |
**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Tahsilat tutarı toplam alacaktan fazla | Uyarı: "Tahsilat alacaktan fazla. Avans olarak kaydedilsin mi?" |
| EC2 | Tahsilat birden fazla alacağa bölünür | Bölme ekranı gösterilir, toplamlar kontrol edilir |
| EC3 | Tahsilat tarihi gelecekte girilir | Uyarı gösterilir, kabul edilir (ileri tarihli çek) |
| EC4 | Tahsilat tarihi çok geçmişte (5 yıl önce) | Uyarı gösterilir, kabul edilir |
| EC5 | Aynı dekont numarası ile ikinci tahsilat | Mükerrer uyarısı gösterilir |
| EC6 | Çek ile tahsilat, çek karşılıksız çıkar | Tahsilat iptal mekanizması gerekir |
| EC7 | Kısmi tahsilat yapılır (alacağın bir kısmı) | Kabul edilir, alacak "Kısmi Ödendi" durumuna geçer |
| EC8 | Tahsilat eşleştirilmeden kaydedilir | Kabul edilir, sonradan eşleştirme yapılabilir |
| EC9 | Tahsilat sırasında alacak silinir | "Alacak bulunamadı" hatası, işlem iptal |
| EC10 | Dövizle tahsilat yapılır | Kur girişi istenir, TL karşılığı hesaplanır |

_Kullanılabilirlik Notu:_ Tahsilat formunda dokunmatik cihazlar için "hızlı ödeme yöntemi" kısayolları bulunmalı; belge numarası alanı opsiyonel tutulmalıdır.

#### US-5.3: Gider/Masraf Kaydı Oluşturma

**User Story**

Bir avukat olarak, dosya için yapılan masrafları kaydetmek istiyorum ki giderleri takip edebilir ve müvekkilden talep edebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Gider kategorisi seçilmelidir (harç, bilirkişi, keşif, tebligat, yol, konaklama) |
| AC2 | Tutar zorunludur ve pozitif olmalıdır |
| AC3 | Gider tarihi zorunludur |
| AC4 | Dosya bağlantısı zorunludur (genel gider için opsiyonel) |
| AC5 | Belge/fiş bilgisi eklenebilir |
| AC6 | Belge yüklenebilir (fiş, fatura fotoğrafı) |
| AC7 | Gider kaydedildiğinde dosya masraf toplamı güncellenir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Gider kategorisi seçilmeden kaydedilir | "Gider kategorisi seçiniz" hatası |
| NC2 | Tutar girilmeden kaydedilir | "Tutar zorunludur" hatası |
| NC3 | Tutar negatif girilir | "Tutar pozitif olmalıdır" hatası |
| NC4 | Gider tarihi boş bırakılır | "Gider tarihi zorunludur" hatası |
| NC5 | Yetkisiz kullanıcı gider kaydeder | "Gider kaydetme yetkiniz yok" hatası |
| NC6 | Arşivlenmiş dosyaya gider eklenir | "Arşivlenmiş dosyaya gider eklenemez" hatası |
**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Gider tarihi gelecekte girilir | Uyarı gösterilir, kabul edilir (planlanan gider) |
| EC2 | Çok küçük gider tutarı (0.50 TL) | Kabul edilir |
| EC3 | Aynı gün aynı kategoride birden fazla gider | Kabul edilir |
| EC4 | Gider belgesi yüklenirken hata oluşur | Gider kaydedilir, belge sonra eklenebilir |
| EC5 | Fatura numarası çok uzun (50+ karakter) | Kabul edilir veya sınırlandırılır |
| EC6 | Gider dosyasız kaydedilir (genel ofis gideri) | Kabul edilir, müvekkil/dosya yerine "Genel" işaretlenir |
| EC7 | Gider masraf avansından düşülür | Otomatik mahsup seçeneği sunulur |
| EC8 | Aynı fatura numarası ile ikinci gider | Mükerrer uyarısı |
| EC9 | Gider kategorisi sonradan değiştirilir | Kabul edilir, tarihçe tutulur |
| EC10 | Döviz cinsinden gider kaydedilir | Kur girişi istenir |

#### US-5.4: Tahsilat Eşleştirme

**User Story**

Bir avukat olarak, tahsilatı hangi alacağa saymak istediğimi belirlemek istiyorum ki bakiyeler doğru hesaplansın ve takip kolaylaşsın.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Tahsilat oluşturulurken veya sonradan eşleştirme yapılabilir |
| AC2 | Bir tahsilat birden fazla alacağa bölünebilir |
| AC3 | Otomatik FIFO eşleştirme seçeneği sunulur |
| AC4 | Eşleştirme tutarları toplamı tahsilat tutarını geçemez |
| AC5 | Eşleştirme sonrası alacak durumu otomatik güncellenir |
| AC6 | Eşleştirme geri alınabilir ve yeniden yapılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Eşleştirme toplamı tahsilattan fazla girilir | "Eşleştirme toplamı tahsilat tutarını geçemez" hatası |
| NC2 | Farklı müvekkilin alacağına eşleştirme yapılır | "Farklı müvekkilin alacağına eşleştirme yapılamaz" hatası |
| NC3 | Zaten tamamen eşleştirilmiş tahsilat tekrar eşleştirilir | "Eşleştirilecek tutar kalmadı" mesajı |
| NC4 | Yetkisiz kullanıcı eşleştirme yapar | "Eşleştirme yapma yetkiniz yok" hatası |
**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Tahsilat 10+ alacağa bölünür | Kabul edilir |
| EC2 | Eşleştirme tutarı 0.01 TL | Kabul edilir |
| EC3 | FIFO seçilir ama en eski alacak kısmi ödenmiş | Kalan tutara eşleştirilir, devamı sonrakine |
| EC4 | Eşleştirme yapılırken alacak silinir | "Alacak bulunamadı" hatası, işlem iptal |
| EC5 | Eşleştirme yapılırken alacak tutarı değiştirilir | Mevcut tutar üzerinden işlem devam eder veya uyarı |
| EC6 | Avans tahsilatı sonradan alacağa eşleştirilir | Kabul edilir, avans bakiyesi düşer |
| EC7 | Eşleştirme geri alınır, alacak tekrar açık duruma geçer | Alacak durumu "Vadesi Geldi"ye döner |
| EC8 | Aynı anda iki kullanıcı aynı tahsilatı eşleştirir | İlk kaydeden geçerli, diğerine uyarı |
| EC9 | Eşleştirme sırasında tahsilat tutarı değiştirilir | Eşleştirme sıfırlanır veya uyarı |

_Politika Notu:_ Tahsilat oluşturulurken varsayılan davranış otomatik FIFO'dur; kullanıcı manuel eşleştirme modunu seçtiğinde FIFO devre dışı kalır ve kullanıcı alacakları tek tek belirler. Avans tahsilatlar FIFO hesaplamasına dahil edilmez (bekleyen avans hesabı).

#### US-5.5: Ödeme Planı Oluşturma

**User Story**

Bir avukat olarak, müvekkile taksitli ödeme planı tanımlamak istiyorum ki büyük tutarları parçalara bölebilir ve tahsilatı kolaylaştırabilirim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Toplam tutar belirlenmelidir |
| AC2 | Taksit sayısı girilmelidir |
| AC3 | İlk taksit tarihi belirlenmelidir |
| AC4 | Taksit aralığı seçilmelidir (haftalık, aylık, özel) |
| AC5 | Taksitler otomatik hesaplanır veya manuel girilebilir |
| AC6 | Her taksit için ayrı alacak kaydı oluşturulabilir |
| AC7 | Plan müvekkil ve opsiyonel olarak dosyaya bağlanır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Toplam tutar girilmeden plan oluşturulur | "Toplam tutar zorunludur" hatası |
| NC2 | Taksit sayısı 0 veya negatif girilir | "Taksit sayısı pozitif olmalıdır" hatası |
| NC3 | İlk taksit tarihi geçmişte girilir | Uyarı gösterilir, kabul edilir |
| NC4 | Müvekkil seçilmeden plan oluşturulur | "Müvekkil seçimi zorunludur" hatası |
| NC5 | Taksit toplamı plan toplamını geçer | "Taksit toplamı plan tutarını geçemez" hatası |
**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Taksit sayısı çok fazla (100+) | Kabul edilir veya makul sınır (örn: 60) |
| EC2 | Tutar taksitlere tam bölünmüyor (1000 TL / 3) | Son taksitte kuruş farkı ayarlanır |
| EC3 | Taksit tutarları manuel farklı girilir (ilk büyük, sonrakiler küçük) | Kabul edilir |
| EC4 | Ödeme planı oluşturulurken bir taksit atlanır | Ardışık olmayan tarihler kabul edilir |
| EC5 | Mevcut alacaklardan ödeme planı oluşturulur | Mevcut alacaklar plana dönüştürülür |
| EC6 | Ödeme planı iptal edilir | Tüm ilişkili alacaklar iptal veya bağımsız kalır |
| EC7 | Taksit tarihi resmi tatile denk gelir | Uyarı gösterilir ama kabul edilir |
| EC8 | Plan oluşturulduktan sonra toplam tutar değiştirilir | Taksitler yeniden hesaplanır veya engellenir |
| EC9 | Plan kapsamında kısmi ödeme yapılır | İlgili taksit "Kısmi Ödendi" olur |

#### US-5.6: Cari Hesap Ekstresi

**User Story**

Bir avukat olarak, müvekkilin cari hesap ekstresini görmek istiyorum ki tüm alacak, tahsilat ve gider hareketlerini tek ekranda takip edebilir ve müvekkile sunabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Müvekkil bazlı tüm mali hareketler kronolojik listelenir |
| AC2 | Açılış bakiyesi, hareketler ve kapanış bakiyesi gösterilir |
| AC3 | Tarih aralığı ile filtreleme yapılabilir |
| AC4 | Dosya bazlı filtreleme yapılabilir |
| AC5 | Hareket türüne göre filtreleme yapılabilir |
| AC6 | Ekstre PDF ve Excel olarak dışa aktarılabilir |
| AC7 | Ekstre müvekkile e-posta ile gönderilebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Müvekkil seçilmeden ekstre görüntülenir | "Müvekkil seçimi zorunludur" hatası |
| NC2 | Hiç mali hareketi olmayan müvekkil seçilir | "Bu müvekkilin mali hareketi bulunmuyor" mesajı |
| NC3 | Yetkisiz kullanıcı ekstre görüntüler | "Mali verilere erişim yetkiniz yok" hatası |
**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | 1000+ hareket olan müvekkil ekstresi | Sayfalama uygulanır, performans optimize edilir |
| EC2 | Çok geniş tarih aralığı seçilir (10 yıl) | Kabul edilir, yükleme süresi uyarısı |
| EC3 | Negatif bakiye (müvekkil alacaklı) | Farklı renkte gösterilir, parantez içinde |
| EC4 | Farklı para birimlerinde hareket var | Her para birimi ayrı sütunda veya dönüştürülmüş |
| EC5 | PDF export çok uzun (100+ sayfa) | Sayfa sınırı uyarısı veya bölünmüş dosya |
| EC6 | E-posta gönderiminde hata oluşur | Hata mesajı, PDF manuel indirilebilir |
| EC7 | Ekstre görüntülenirken yeni hareket eklenir | Yenileme ile güncellenir |
| EC8 | Açılış bakiyesi hesaplanırken seçilen tarih öncesi hareket yok | Açılış bakiyesi 0 |
| EC9 | İptal edilmiş hareketler ekstrede | Varsayılanda gizli, filtre ile gösterilebilir |

#### US-5.7: Alacak Yaşlandırma Raporu

**User Story**

Bir kurucu ortak olarak, alacakların yaşlandırma raporunu görmek istiyorum ki vadesi geçen alacakları takip edebilir ve tahsilat önceliklerini belirleyebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Alacaklar yaş gruplarına ayrılır (0-30, 31-60, 61-90, 90+ gün) |
| AC2 | Her yaş grubu için toplam tutar gösterilir |
| AC3 | Müvekkil bazlı kırılım görüntülenebilir |
| AC4 | Dosya bazlı kırılım görüntülenebilir |
| AC5 | Sadece vadesi geçen alacaklar filtrelenebilir |
| AC6 | Rapor Excel olarak dışa aktarılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Hiç alacak kaydı yokken rapor görüntülenir | "Alacak kaydı bulunmuyor" mesajı |
| NC2 | Yetkisiz kullanıcı raporu görüntüler | "Mali raporlara erişim yetkiniz yok" hatası |
**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Tüm alacaklar aynı yaş grubunda | Diğer gruplar 0 olarak gösterilir |
| EC2 | 1000+ günlük alacak var | 90+ grubunda gösterilir |
| EC3 | Vadesi bugün olan alacak | 0-30 gün grubunda (0 günlük) |
| EC4 | Vade tarihi olmayan alacaklar | Ayrı "Vadesiz" grubunda gösterilir |
| EC5 | Kısmi ödenmiş alacaklar | Kalan tutar üzerinden yaşlandırma |
| EC6 | Rapor tarihi değiştirilerek geçmiş tarih baz alınır | O tarihteki duruma göre hesaplanır |
| EC7 | Çok fazla müvekkil (5000+) | Performans optimizasyonu, lazy loading |
| EC8 | Yaş grubu aralıkları özelleştirilmek istenir | Ayarlardan değiştirilebilir veya sabit |

#### US-5.8: Tahsilat Makbuzu Oluşturma

**User Story**

Bir avukat olarak, tahsilat için makbuz oluşturmak istiyorum ki müvekkile resmi belge verebilir ve kayıt tutabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Makbuz numarası otomatik oluşturulur |
| AC2 | Makbuz tarihi tahsilat tarihi olarak atanır |
| AC3 | Müvekkil bilgileri otomatik doldurulur |
| AC4 | Tutar yazı ve rakamla gösterilir |
| AC5 | Makbuz PDF olarak indirilebilir |
| AC6 | Makbuz yazdırılabilir |
| AC7 | Makbuz müvekkile e-posta ile gönderilebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Tahsilat kaydedilmeden makbuz oluşturulmak istenir | "Önce tahsilat kaydedin" hatası |
| NC2 | İptal edilmiş tahsilat için makbuz istenir | "İptal edilmiş tahsilat için makbuz oluşturulamaz" hatası |
| NC3 | Yetkisiz kullanıcı makbuz oluşturur | "Makbuz oluşturma yetkiniz yok" hatası |
**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Tutar çok yüksek, yazıyla uzun oluyor | Makbuz formatı genişler veya küçük font |
| EC2 | Müvekkil adı çok uzun (tüzel kişi) | Satır kaydırma veya kısaltma |
| EC3 | Aynı tahsilat için ikinci makbuz istenir | "Bu tahsilat için makbuz mevcut. Tekrar oluşturulsun mu?" |
| EC4 | Makbuz numarası yıl değişiminde | Yeni seri başlar (2026-0001) |
| EC5 | E-posta gönderiminde hata | Hata mesajı, manuel indirme önerisi |
| EC6 | PDF oluşturulamıyor (sunucu hatası) | Hata mesajı, yeniden deneme butonu |
| EC7 | Makbuz şablonu değiştirilmek isteniyor | Ayarlardan şablon seçimi |
| EC8 | Dövizli tahsilat için makbuz | Döviz tutarı ve TL karşılığı birlikte gösterilir |

#### US-5.9: Mali Hareket Düzeltme/İptal

**User Story**

Bir avukat olarak, hatalı mali kaydı düzeltmek veya iptal etmek istiyorum ki muhasebe kayıtları doğru olsun.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Mali hareket düzenlenebilir (tutar, tarih, açıklama) |
| AC2 | Düzenleme nedeni zorunlu olarak girilmelidir |
| AC3 | Düzenleme tarihçesi tutulur (eski/yeni değer) |
| AC4 | Mali hareket iptal edilebilir (silinmez, iptal işaretlenir) |
| AC5 | İptal nedeni zorunlu olarak girilmelidir |
| AC6 | İptal edilen hareketin eşleştirmeleri otomatik geri alınır |
| AC7 | Sadece yetkili kullanıcılar düzeltme/iptal yapabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Düzeltme nedeni girilmeden kaydedilir | "Düzeltme nedeni zorunludur" hatası |
| NC2 | İptal nedeni girilmeden iptal edilir | "İptal nedeni zorunludur" hatası |
| NC3 | Zaten iptal edilmiş hareket tekrar iptal edilir | "Bu hareket zaten iptal edilmiş" hatası |
| NC4 | Yetkisiz kullanıcı düzeltme yapar | "Mali kayıt düzeltme yetkiniz yok" hatası |
| NC5 | Eşleştirilmiş tahsilat tutarı düşürülür | "Eşleştirme tutarından az olamaz. Önce eşleştirmeyi güncelleyin" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Çok eski hareket düzeltilir (2 yıl önce) | Uyarı gösterilir, yönetici onayı gerekir |
| EC2 | Tahsilat iptal edilir, bağlı alacak ne olur | Alacak tekrar "açık" duruma geçer |
| EC3 | Alacak iptal edilir, bağlı tahsilat ne olur | Tahsilat eşleştirmesi kaldırılır, avans olur |
| EC4 | Ödeme planındaki taksit iptal edilir | Plan güncellenir, toplam değişir |
| EC5 | Düzeltme sırasında başka kullanıcı aynı kaydı düzenler | Çakışma uyarısı, son değişiklik gösterilir |
| EC6 | İptal edilen hareket raporlarda | Varsayılanda hariç tutulur, filtre ile dahil edilebilir |
| EC7 | Toplu iptal yapılır (10+ hareket) | Batch işlem, her biri için neden gerekir veya tek neden |
| EC8 | Düzeltme yapıldıktan sonra geri alınmak istenir | Düzeltme tarihçesinden eski değere dönülebilir |

#### US-5.10: Mali Raporlar

**User Story**

Bir kurucu ortak olarak, mali raporları görmek istiyorum ki ofisin gelir-gider durumunu analiz edebilir ve kararlar verebilir.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Dönemsel gelir raporu görüntülenebilir |
| AC2 | Dönemsel gider raporu görüntülenebilir |
| AC3 | Alacak-tahsilat özet raporu görüntülenebilir |
| AC4 | Dosya bazlı karlılık raporu görüntülenebilir |
| AC5 | Avukat bazlı tahsilat raporu görüntülenebilir |
| AC6 | Tarih aralığı ve diğer filtreler uygulanabilir |
| AC7 | Tüm raporlar Excel ve PDF olarak dışa aktarılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Yetkisiz kullanıcı mali rapor görüntüler | "Mali raporlara erişim yetkiniz yok" hatası |
| NC2 | Hiç veri olmayan dönem seçilir | "Seçilen dönemde veri bulunmuyor" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Çok geniş tarih aralığı (10 yıl) | Performans uyarısı, aylık/yıllık özet önerilir |
| EC2 | Dosya karlılık hesaplanırken gider > gelir | Negatif karlılık gösterilir (zarar) |
| EC3 | Avukat ayrıldıktan sonra rapor | Eski kayıtlarda görünür, aktif/pasif filtresi |
| EC4 | Farklı para birimleri karışık raporda | TL'ye dönüştürülür veya ayrı gösterilir |
| EC5 | Rapor oluşturulurken yeni hareket eklenir | Rapor anı verileri gösterir, dinamik güncellenmez |
| EC6 | PDF çok büyük (100+ sayfa) | Özet mod önerilir veya bölünür |
| EC7 | Grafikli rapor istenir | Çizgi/bar grafik eklenir |
| EC8 | Karşılaştırmalı rapor (bu yıl vs geçen yıl) | İki dönem yan yana veya fark gösterilir |

#### US-5.11: Masraf Avansı Yönetimi

**User Story**

Bir avukat olarak, müvekkilden alınan masraf avansını takip etmek istiyorum ki yapılan masrafları avanstan düşebilir ve kalan avansı görebilir.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Masraf avansı ayrı bir alacak türü olarak kaydedilir |
| AC2 | Gider kaydedilirken avanstan düşme seçeneği sunulur |
| AC3 | Kalan avans bakiyesi görüntülenebilir |
| AC4 | Avans yetersizse uyarı verilir |
| AC5 | Avans iadesi kaydedilebilir |
| AC6 | Dosya bazlı avans takibi yapılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Avanstan fazla masraf düşülmek istenir | "Avans bakiyesi yetersiz. Kalan: X TL" uyarısı |
| NC2 | Avans iadesi avans bakiyesinden fazla girilir | "İade tutarı avans bakiyesini geçemez" hatası |
| NC3 | Hiç avansı olmayan dosyadan avans düşülmek istenir | "Bu dosyada kullanılabilir avans yok" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Birden fazla dosyaya yayılmış avans | Her dosya için ayrı avans bakiyesi |
| EC2 | Genel avans (dosyasız) | Herhangi bir dosyanın masrafına kullanılabilir |
| EC3 | Avans iadesi yapılırken kalan masraf var | Uyarı: "Karşılanmamış X TL masraf var" |
| EC4 | Dosya kapanır, kalan avans ne olur | Avans iadesi veya başka dosyaya aktarım seçeneği |
| EC5 | Avans tahsilatı iptal edilir | Kullanılmış masraflar açık alacağa dönüşür |
| EC6 | Çok küçük avans kalıntısı (0.50 TL) | İade veya silme seçeneği |
| EC7 | Avans kullanım geçmişi görüntülenmek istenir | Detaylı log gösterilir |

