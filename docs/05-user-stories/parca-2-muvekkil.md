### Parça 2: Müvekkil Yönetimi

> **Not:** Müvekkil kartı, bağlantılı kişi ve yetki senaryoları için `docs/02-proje-plani.md` ve `docs/00-terminoloji-ve-kurallar.md` dosyalarındaki ilgili tanımlar geçerlidir.

#### US-2.1: Yeni Müvekkil Kaydı (Gerçek Kişi)

**User Story**

Bir avukat olarak, yeni gerçek kişi müvekkil kaydı oluşturmak istiyorum ki müvekkilimin bilgilerini sistemde saklayabileyim ve dosyalarına bağlayabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | TC Kimlik No 11 haneli olmalı ve doğrulama algoritmasından geçmeli |
| AC2 | Ad ve soyad alanları zorunludur |
| AC3 | En az bir iletişim bilgisi (telefon veya e-posta) girilmelidir |
| AC4 | Müvekkil kodu otomatik oluşturulur (M-2025-0001 formatı) |
| AC5 | Aynı TC Kimlik No ile mükerrer kayıt engellenmelidir |
| AC6 | Kayıt sonrası müvekkil detay sayfasına yönlendirilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
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
|---|---|---|
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

#### US-2.2: Yeni Müvekkil Kaydı (Tüzel Kişi)

**User Story**

Bir avukat olarak, şirket müvekkil kaydı oluşturmak istiyorum ki kurumsal müvekkillerimi yönetebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Vergi numarası 10 veya 11 haneli olmalıdır |
| AC2 | Ticaret unvanı zorunludur |
| AC3 | Yetkili kişi adı soyadı zorunludur |
| AC4 | Aynı vergi numarası ile mükerrer kayıt engellenmelidir |
| AC5 | Vergi dairesi seçimi yapılabilmelidir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Vergi numarası 9 haneli girilir | "Vergi numarası 10 veya 11 haneli olmalıdır" hatası |
| NC2 | Ticaret unvanı boş bırakılır | "Ticaret unvanı zorunludur" hatası |
| NC3 | Yetkili kişi bilgisi girilmez | "Yetkili kişi bilgisi zorunludur" hatası |
| NC4 | Mevcut vergi numarası ile kayıt denenir | "Bu vergi numarası ile kayıtlı müvekkil mevcut" uyarısı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Ticaret unvanı çok uzun (500+ karakter) | Maksimum 300 karakter sınırı |
| EC2 | Şahıs şirketi kaydedilir (11 haneli VKN = TC) | Her iki alan da aynı değerle doldurulabilir |
| EC3 | Yabancı şirket kaydedilir (VKN yok) | "Yabancı Şirket" seçeneği ile VKN zorunluluğu kalkar |
| EC4 | Yetkili kişi aynı zamanda gerçek kişi müvekkil | Bağlantı kurulabilir, mükerrer uyarısı |
| EC5 | Şirket birleşmesi sonrası unvan değişikliği | Güncelleme yapılabilir, eski unvan tarihçede saklanır |
| EC6 | Mersis numarası formatı yanlış girilir | Format kontrolü ve düzeltme önerisi |

#### US-2.3: Müvekkil Arama ve Listeleme

**User Story**

Bir avukat olarak, müvekkilleri hızlıca aramak ve listelemek istiyorum ki ihtiyacım olan müvekkil kaydına kolayca ulaşabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Ad, soyad, TC, vergi no, telefon ile arama yapılabilir |
| AC2 | Arama sonuçları anlık güncellenir (en az 3 karakter sonrası) |
| AC3 | Sonuçlar sayfalanır (varsayılan 20 kayıt) |
| AC4 | Durum, tür ve sorumlu avukata göre filtreleme yapılabilir |
| AC5 | Sonuçlar ad, kayıt tarihi, bakiyeye göre sıralanabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Eşleşen kayıt olmayan terim aranır | "Sonuç bulunamadı" mesajı, arama önerileri |
| NC2 | Sadece 1-2 karakter ile arama yapılır | "En az 3 karakter girin" uyarısı |
| NC3 | Stajyer tüm müvekkilleri görmeye çalışır | Sadece atandığı dosyaların müvekkillerini görür |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Arama terimi Türkçe karakter içerir (Şükrü) | Doğru sonuçlar döner |
| EC2 | Arama büyük/küçük harf duyarsız (ALİ = ali = Ali) | Tüm varyasyonlar bulunur |
| EC3 | TC numarasının son 4 hanesi ile arama | Sonuçlar döner (iş kuralına göre) |
| EC4 | Telefon numarası farklı formatlarla arama (5551234567, 555-123-45-67) | Normalize edilip eşleştirilir |
| EC5 | 10.000+ müvekkil içinde arama | Performans 2 saniyenin altında kalmalı |
| EC6 | Arama sırasında internet kesilir | Son sonuçlar gösterilir, bağlantı uyarısı |
| EC7 | Özel karakterle arama (ali, %test%) | Özel karakterler escape edilir, güvenlik sağlanır |
| EC8 | Silinmiş (arşivlenmiş) müvekkil aranır | Varsayılanda görünmez, "Arşivi dahil et" filtresi ile görünür |

#### US-2.4: Müvekkil Bilgi Güncelleme

**User Story**

Bir avukat olarak, müvekkil bilgilerini güncellemek istiyorum ki kayıtlar her zaman güncel kalsın.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Tüm alanlar düzenlenebilir (TC/VKN hariç) |
| AC2 | TC Kimlik No değişikliği için yönetici onayı gerekir |
| AC3 | Değişiklikler tarihçede saklanır |
| AC4 | Kaydetmeden çıkışta onay istenir |
| AC5 | Güncelleme sonrası başarı mesajı gösterilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Zorunlu alan silinerek kaydedilir | "Ad alanı zorunludur" hatası |
| NC2 | Geçersiz e-posta formatına güncellenir | "Geçerli bir e-posta adresi girin" hatası |
| NC3 | Yetkisiz kullanıcı güncelleme yapar | "Bu işlem için yetkiniz bulunmuyor" hatası |
| NC4 | Başka kullanıcının düzenlediği kayıt kaydedilir | "Bu kayıt başka bir kullanıcı tarafından değiştirildi" uyarısı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Tüm telefon numaraları silinir, en az biri olmalı kuralı | "En az bir iletişim bilgisi olmalıdır" hatası |
| EC2 | Birincil telefon silinir, başka telefon var | Kalan telefonlardan biri otomatik birincil olur |
| EC3 | Müvekkil türü değiştirilir (gerçek → tüzel) | Uyarı gösterilir, uygun alanlar güncellenir |
| EC4 | Çok uzun not girilir (10.000+ karakter) | Maksimum karakter sınırı veya kabul |
| EC5 | Değişiklik yapılmadan kaydet tıklanır | "Değişiklik yapılmadı" bilgisi, gereksiz kayıt önlenir |
| EC6 | Form açıkken müvekkil başka kullanıcı tarafından silinir | Kaydetmeye çalışınca "Kayıt bulunamadı" hatası |

#### US-2.5: Müvekkil Arşivleme

**User Story**

Bir avukat olarak, artık çalışmadığım müvekkilleri arşivlemek istiyorum ki aktif listem temiz kalsın ama veriler silinmesin.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Arşivlenen müvekkil varsayılan listede görünmez |
| AC2 | Arşivlenen müvekkilin aktif dosyası varsa uyarı verilir |
| AC3 | Arşivlenmiş müvekkil geri getirilebilir |
| AC4 | Arşivleme nedeni kaydedilir |
| AC5 | Arşiv tarihçesi görüntülenebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Aktif dosyası olan müvekkil arşivlenir | "Bu müvekkilin X aktif dosyası var. Devam etmek istiyor musunuz?" onay kutusu |
| NC2 | Bakiyesi olan müvekkil arşivlenir | "Bu müvekkilin X TL bakiyesi var" uyarısı |
| NC3 | Yetkisiz kullanıcı arşivleme yapar | "Bu işlem için yetkiniz bulunmuyor" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Arşivlenmiş müvekkile yeni dosya açılmaya çalışılır | "Müvekkil arşivlenmiş. Önce aktife alın" uyarısı |
| EC2 | Arşivlenmiş müvekkil aranır | Sadece "Arşivi dahil et" filtresi ile bulunur |
| EC3 | Aynı müvekkil birden fazla kez arşivlenip aktife alınır | Her işlem tarihçede ayrı kayıt olur |
| EC4 | Toplu arşivleme yapılır (50+ müvekkil) | Progress bar gösterilir, batch işlem yapılır |
| EC5 | Arşivleme sırasında bağlantı kesilir | İşlem geri alınır, tutarlılık sağlanır |

#### US-2.6: Müvekkil Birleştirme

**User Story**

Bir avukat olarak, yanlışlıkla mükerrer oluşturulmuş müvekkil kayıtlarını birleştirmek istiyorum ki veriler tek kayıtta toplanır.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | İki müvekkil kaydı seçilerek birleştirme başlatılır |
| AC2 | Ana kayıt ve birleştirilecek kayıt belirlenir |
| AC3 | Tüm dosyalar, mali hareketler ana kayda aktarılır |
| AC4 | İletişim bilgileri birleştirilir (mükerrer değilse) |
| AC5 | Birleştirme sonrası eski kayıt silinir |
| AC6 | İşlem geri alınamaz, onay istenir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Farklı türde müvekkiller birleştirilir (gerçek + tüzel) | "Farklı türdeki müvekkiller birleştirilemez" hatası |
| NC2 | Aynı müvekkil kendisiyle birleştirilmeye çalışılır | "Aynı müvekkil seçilemez" hatası |
| NC3 | Yetkisiz kullanıcı birleştirme yapar | "Bu işlem için yönetici yetkisi gerekli" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Her iki müvekkilin de aynı dosyaya bağlantısı var | Uyarı verilir, birleştirme sonrası tek bağlantı kalır |
| EC2 | İki müvekkilin toplam 100+ dosyası var | İşlem uzun sürebilir uyarısı, arka planda çalışır |
| EC3 | Birleştirme sırasında dosyalardan birine işlem yapılır | Dosya kilidi veya çakışma yönetimi |
| EC4 | Mali hareketler birleştirilirken bakiye tutarsızlığı | Detaylı rapor gösterilir, manuel onay istenir |
| EC5 | Birleştirme yarıda kesilir | Transaction rollback, her iki kayıt da korunur |

#### US-2.7: Müvekkil İletişim Geçmişi

**User Story**

Bir avukat olarak, müvekkilimle yaptığım tüm görüşmeleri kaydetmek istiyorum ki iletişim geçmişini takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Görüşme türü seçilmelidir (telefon, yüz yüze, e-posta, online) |
| AC2 | Görüşme tarihi ve saati kaydedilir |
| AC3 | Görüşme özeti zorunludur |
| AC4 | Görüşmelerin kim tarafından kaydedildiği gösterilir |
| AC5 | Görüşme geçmişi kronolojik sırada listelenir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Görüşme türü seçilmeden kaydedilir | "Görüşme türü seçiniz" hatası |
| NC2 | Özet alanı boş bırakılır | "Görüşme özeti zorunludur" hatası |
| NC3 | Gelecek tarihli görüşme kaydedilir | Kabul edilir (planlanmış görüşme olarak) veya uyarı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Aynı dakikada iki görüşme kaydedilir | Kabul edilir (farklı türlerde olabilir) |
| EC2 | Çok uzun görüşme notu (5000+ karakter) | Kabul edilir, metin alanı genişler |
| EC3 | Geçmiş tarihli görüşme kaydedilir (1 yıl önce) | Kabul edilir, tarihçeye eklenir |
| EC4 | Görüşme kaydı düzenlenir | Düzenleme tarihi ve düzenleyen kaydedilir |
| EC5 | Görüşme kaydı silinir | Soft delete, yönetici görebilir |

#### US-2.8: Müvekkil Bakiye Görüntüleme

**User Story**

Bir avukat olarak, müvekkilin güncel bakiyesini ve mali özetini görmek istiyorum ki alacak durumunu takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Toplam alacak, tahsilat ve bakiye özeti gösterilir |
| AC2 | Dosya bazlı bakiye dağılımı görüntülenir |
| AC3 | Vadesi geçen alacaklar vurgulanır |
| AC4 | Son 5 mali hareket özeti gösterilir |
| AC5 | Detaylı cari hesap ekstresi erişilebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Mali yetkisi olmayan kullanıcı bakiye görüntüler | Bakiye alanı gizlenir veya "Yetki gerekli" mesajı |
| NC2 | Hiç mali hareketi olmayan müvekkil | "Henüz mali hareket bulunmuyor" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Negatif bakiye (müvekkil alacaklı) | Farklı renkte gösterilir, avans durumu belirtilir |
| EC2 | Çok yüksek bakiye (1.000.000+ TL) | Formatlanarak gösterilir (1.000.000,00 TL) |
| EC3 | Farklı para birimlerinde hareket var | Her para birimi ayrı gösterilir veya dönüştürülür |
| EC4 | Bakiye hesaplanırken hata oluşur | Hata mesajı, manuel yenileme butonu |
| EC5 | Eş zamanlı tahsilat kaydedilirken bakiye görüntülenir | Güncel bakiye gösterilir (real-time veya refresh) |

