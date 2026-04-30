### Parça 3: Dosya/Dava Yönetimi

> **Not:** Dosya durumları, yetki matrisi ve kayıt yaşam döngüsü için `docs/00-terminoloji-ve-kurallar.md` ile `docs/02-proje-plani.md` dosyalarındaki ilgili bölümler referans alınmalıdır.

#### US-3.1: Yeni Dosya Açma

**User Story**

Bir avukat olarak, yeni dava veya icra dosyası açmak istiyorum ki müvekkilimin hukuki işlemlerini sisteme kaydedip takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Dosya türü seçilmelidir (dava, icra, danışmanlık, arabuluculuk) |
| AC2 | Müvekkil seçimi zorunludur |
| AC3 | Müvekkil pozisyonu belirtilmelidir (davacı, davalı, alacaklı, borçlu) |
| AC4 | Büro dosya numarası otomatik oluşturulur (D-2025-0001) |
| AC5 | Sorumlu avukat atanmalıdır |
| AC6 | Dosya varsayılan olarak "Taslak" durumunda açılır |
| AC7 | En az bir karşı taraf bilgisi girilmelidir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
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
|---|---|---|
| EC1 | Aynı müvekkil ve karşı taraf ile ikinci dosya açılır | Uyarı: "Bu taraflar arasında X dosya mevcut" |
| EC2 | Müvekkil hem davacı hem davalı olarak seçilir | Engellenir: "Müvekkil aynı dosyada hem davacı hem davalı olamaz" |
| EC3 | Karşı taraf olarak mevcut müvekkil seçilir | Uyarı: "Karşı taraf müvekkil olarak kayıtlı. Çıkar çatışması olabilir" |
| EC4 | Dosya numarası yıl değişiminde | D-2026-0001 olarak yeni seri başlar |
| EC5 | Aynı anda iki kullanıcı dosya açar | Her biri farklı numara alır, çakışma olmaz |
| EC6 | Mahkeme esas numarası formatı farklı (2025/123, E.2025/123) | Her iki format da kabul edilir |
| EC7 | Çok uzun dava konusu özeti girilir (5000+ karakter) | Kabul edilir, özet alanı genişler |
| EC8 | Talep tutarı 0 veya negatif girilir | "Talep tutarı pozitif olmalıdır" veya 0 kabul edilir (manevi tazminat) |
| EC9 | Birden fazla karşı taraf eklenir (10+) | Kabul edilir, liste şeklinde gösterilir |
| EC10 | Dosya açılırken form yarıda bırakılır | Taslak olarak otomatik kaydedilir veya kaybolur |

#### US-3.2: Dosya Bilgi Güncelleme

**User Story**

Bir avukat olarak, dosya bilgilerini güncellemek istiyorum ki mahkeme bilgileri ve dava durumu her zaman güncel kalsın.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Mahkeme adı ve esas numarası eklenebilir/güncellenebilir |
| AC2 | Dosya durumu değiştirilebilir |
| AC3 | Karşı taraf bilgileri güncellenebilir |
| AC4 | Değişiklik tarihçesi tutulur |
| AC5 | Kritik alan değişikliklerinde (durum, mahkeme) onay istenir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Zorunlu alan silinerek kaydedilir | İlgili alanın zorunluluk hatası |
| NC2 | Kapanmış dosyanın durumu "Aktif"e çekilir | Uyarı: "Kapanmış dosyayı yeniden açmak istiyor musunuz?" |
| NC3 | Yetkisiz kullanıcı başkasının dosyasını günceller | "Bu dosyayı düzenleme yetkiniz yok" hatası |
| NC4 | Aynı dosya iki kullanıcı tarafından eş zamanlı düzenlenir | Çakışma uyarısı, son değişiklik gösterilir |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Esas numarası olmayan dosyaya esas numarası eklenir | Kayıt güncellenir, dosya "Aktif" durumuna geçirilebilir |
| EC2 | Mahkeme değişikliği yapılır (görevsizlik kararı) | Eski mahkeme bilgisi tarihçede saklanır |
| EC3 | Müvekkil pozisyonu değiştirilir (davacıdan davalıya) | Uyarı gösterilir, onay ile güncellenir |
| EC4 | Dosya türü değiştirilir (dava → icra) | Gerekli alanlar güncellenir, eksik bilgi istenir |
| EC5 | Tüm karşı taraflar silinir | "En az bir karşı taraf olmalıdır" hatası |
| EC6 | Dosya düzenlenirken silinir (başka kullanıcı tarafından) | Kaydet tıklanınca "Dosya bulunamadı" hatası |

#### US-3.3: Dosya Durum Değişikliği

**User Story**

Bir avukat olarak, dosyanın durumunu güncellemek istiyorum ki dosyanın hangi aşamada olduğu takip edilebilsin.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Durum değişikliği için neden/not zorunludur |
| AC2 | Belirli durum geçişleri izin verilir (Taslak → Aktif, Aktif → Beklemede) |
| AC3 | Kapanış durumunda kapanış şekli seçilmelidir |
| AC4 | Durum değişikliği tarihçede kaydedilir |
| AC5 | Durum değişikliğinde ilgili kişilere bildirim gönderilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Geçersiz durum geçişi denenir (Taslak → Kapandı) | "Bu durum geçişi yapılamaz. Önce Aktif durumuna alın" hatası |
| NC2 | Kapanış şekli seçilmeden dosya kapatılır | "Kapanış şekli seçiniz" hatası |
| NC3 | Durum değişiklik notu girilmez | "Değişiklik nedeni zorunludur" hatası |
| NC4 | Açık görevi olan dosya kapatılır | Uyarı: "Bu dosyada X açık görev var. Devam edilsin mi?" |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Beklemede durumundan direkt Kapandı'ya geçiş | İzin verilir (örn: feragat durumu) |
| EC2 | Kapanmış dosya yeniden Aktif yapılır | İzin verilir, yeniden açılma tarihi kaydedilir |
| EC3 | Aynı durum tekrar seçilir | "Dosya zaten bu durumda" uyarısı, işlem yapılmaz |
| EC4 | Durum değişikliği sırasında yetki kaldırılır | İşlem reddedilir |
| EC5 | Ödenmemiş alacağı olan dosya kapatılır | Uyarı: "Bu dosyada X TL alacak bakiyesi var" |
| EC6 | Dosya durumu çok hızlı değiştirilir (spam) | Rate limiting veya onay mekanizması |

#### US-3.4: Duruşma Kaydı Oluşturma

**User Story**

Bir avukat olarak, duruşma tarihlerini kaydetmek istiyorum ki duruşmalarımı takvimlendirip takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Duruşma tarihi ve saati zorunludur |
| AC2 | Katılacak avukat seçilmelidir |
| AC3 | Duruşma türü seçilmelidir (ön inceleme, tahkikat, karar, keşif) |
| AC4 | Aynı tarih ve saatte çakışan duruşma varsa uyarı verilir |
| AC5 | Duruşma hatırlatıcısı otomatik oluşturulur |
| AC6 | Geçmiş tarihli duruşma eklenebilir (sonradan kayıt) |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Tarih girilmeden kaydedilir | "Duruşma tarihi zorunludur" hatası |
| NC2 | Saat girilmeden kaydedilir | "Duruşma saati zorunludur" hatası |
| NC3 | Katılacak avukat seçilmez | "Katılacak avukat seçiniz" hatası |
| NC4 | Duruşma türü seçilmez | "Duruşma türü seçiniz" hatası |
| NC5 | Kapanmış dosyaya duruşma eklenir | "Kapanmış dosyaya duruşma eklenemez" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Aynı gün aynı saatte farklı dosyalara duruşma eklenir | Çakışma uyarısı: "Bu saatte başka duruşmanız var: [Dosya No]" |
| EC2 | Resmi tatil gününe duruşma eklenir | Uyarı: "Bu tarih resmi tatil" (ama engellemez) |
| EC3 | Hafta sonuna duruşma eklenir | Uyarı: "Bu tarih hafta sonu" |
| EC4 | 5 yıl sonrası tarihte duruşma eklenir | Kabul edilir |
| EC5 | Geçmiş tarihli duruşma eklenir (2 ay önce) | Kabul edilir, "Geçmiş tarih" uyarısı |
| EC6 | Aynı dosyaya aynı gün birden fazla duruşma eklenir | Kabul edilir (aynı gün farklı mahkemelerde olabilir) |
| EC7 | Duruşma saati mesai saatleri dışında (22:00) | Uyarı gösterilir ama kabul edilir |
| EC8 | Mahkeme salonu bilgisi çok uzun (100+ karakter) | Maksimum karakter sınırı uygulanır |
| EC9 | Pasif avukat katılacak avukat olarak seçilir | "Pasif kullanıcı seçilemez" hatası |

#### US-3.5: Duruşma Sonucu Kaydetme

**User Story**

Bir avukat olarak, duruşma sonucunu kaydetmek istiyorum ki dosya ilerleyişi takip edilebilsin ve yapılacaklar belirlensin.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Sadece geçmiş veya bugünkü tarihli duruşmaya sonuç girilebilir |
| AC2 | Sonuç özeti zorunludur |
| AC3 | Sonraki duruşma tarihi girilebilir (yeni duruşma kaydı oluşturur) |
| AC4 | Ara karar ve verilen kararlar kaydedilebilir |
| AC5 | Duruşma durumu "Tamamlandı" olarak güncellenir |
| AC6 | Yapılacak işlemler görev olarak oluşturulabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Gelecek tarihli duruşmaya sonuç girilir | "Henüz gerçekleşmemiş duruşmaya sonuç girilemez" hatası |
| NC2 | Sonuç özeti boş bırakılır | "Sonuç özeti zorunludur" hatası |
| NC3 | Zaten sonuç girilmiş duruşmaya tekrar sonuç girilir | "Bu duruşmaya zaten sonuç girilmiş. Düzenlemek ister misiniz?" |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Sonraki duruşma tarihi bugün girilir | Kabul edilir (aynı gün birden fazla duruşma) |
| EC2 | Sonraki duruşma tarihi geçmiş tarih girilir | "Sonraki duruşma tarihi geçmiş olamaz" hatası |
| EC3 | Duruşma ertelendi olarak işaretlenir | Yeni tarih zorunlu olur |
| EC4 | Çok uzun duruşma notu girilir (10.000+ karakter) | Kabul edilir |
| EC5 | Aynı anda iki avukat sonuç girer | Çakışma yönetimi, ilk kaydeden kazanır |
| EC6 | Duruşma sonucu olarak "Karar verildi" seçilir | Dosya durumu güncelleme önerisi gösterilir |

#### US-3.6: Belge Yükleme

**User Story**

Bir avukat olarak, dosyaya belge yüklemek istiyorum ki tüm evraklar dijital ortamda saklanabilsin ve kolayca erişilebilsin.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | PDF, DOC, DOCX, JPG, PNG, TIFF formatları desteklenir |
| AC2 | Maksimum dosya boyutu 25 MB'dır |
| AC3 | Belge kategorisi seçilmelidir |
| AC4 | Belge adı otomatik oluşturulur, değiştirilebilir |
| AC5 | Çoklu dosya yükleme desteklenir |
| AC6 | Yükleme ilerlemesi gösterilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Desteklenmeyen format yüklenir (.exe, .zip) | "Bu dosya formatı desteklenmiyor" hatası |
| NC2 | 25 MB üzeri dosya yüklenir | "Dosya boyutu 25 MB'ı geçemez" hatası |
| NC3 | Belge kategorisi seçilmez | "Belge kategorisi seçiniz" hatası |
| NC4 | Yetkisiz kullanıcı belge yükler | "Bu dosyaya belge yükleme yetkiniz yok" hatası |
| NC5 | Virüslü dosya yüklenir | Dosya reddedilir, güvenlik uyarısı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
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

#### US-3.7: Belge Görüntüleme ve İndirme

**User Story**

Bir avukat olarak, dosyadaki belgeleri görüntülemek ve indirmek istiyorum ki evraklara hızlıca erişebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | PDF dosyaları tarayıcıda önizleme yapılabilir |
| AC2 | Tüm dosyalar indirilebilir |
| AC3 | Belge arama özelliği çalışır |
| AC4 | Belge listesi kategoriye göre filtrelenebilir |
| AC5 | Her indirme loglanır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Yetkisiz kullanıcı belge görüntülemek ister | "Bu belgeye erişim yetkiniz yok" hatası |
| NC2 | Silinmiş belge görüntülenmek istenir | "Belge bulunamadı" hatası |
| NC3 | Sunucuda dosya fiziksel olarak yok | "Dosya sunucuda bulunamadı. Sistem yöneticisiyle iletişime geçin" |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Çok büyük PDF önizleme yapılır (100+ sayfa) | Sayfa sayfa yüklenir, performans optimize edilir |
| EC2 | Bozuk PDF önizleme yapılmak istenir | "Dosya önizlenemiyor. İndirmeyi deneyin" mesajı |
| EC3 | Aynı anda 10 dosya indirilir | Zip olarak indirilir veya sırayla başlar |
| EC4 | İndirme sırasında oturum süresi dolar | İndirme tamamlanır veya yetki hatası |
| EC5 | Mobil cihazda büyük dosya indirilir | Dosya boyutu uyarısı gösterilir |
| EC6 | Tarayıcı PDF önizlemeyi desteklemiyor | Otomatik indirme başlar |

#### US-3.8: Dosya Atama

**User Story**

Bir kurucu ortak olarak, dosyaları avukatlara atamak istiyorum ki iş dağılımı yapılabilsin ve her avukat kendi dosyalarını takip edebilsin.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Bir dosyaya birden fazla avukat atanabilir |
| AC2 | Sorumlu avukat (tek) ve yardımcı avukatlar (çoklu) ayrımı yapılır |
| AC3 | Atama yapıldığında ilgili avukata bildirim gönderilir |
| AC4 | Atama kaldırıldığında ilgili avukata bildirim gönderilir |
| AC5 | Atama geçmişi tutulur |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Sorumlu avukat olmadan dosya kaydedilir | "Sorumlu avukat zorunludur" hatası |
| NC2 | Pasif kullanıcı atanmak istenir | "Pasif kullanıcı atanamaz" hatası |
| NC3 | Yetkisiz kullanıcı atama yapmak ister | "Atama yapma yetkiniz yok" hatası |
| NC4 | Aynı kullanıcı hem sorumlu hem yardımcı yapılır | "Aynı kullanıcı iki rolde atanamaz" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Sorumlu avukat değiştirilir | Eski sorumluya bildirim, yeni sorumluya bildirim |
| EC2 | Tek sorumlu avukat kaldırılır | "Sorumlu avukat kaldırılamaz. Önce yeni sorumlu atayın" |
| EC3 | 10+ avukat aynı dosyaya atanır | Kabul edilir, liste şeklinde gösterilir |
| EC4 | Avukat kendi kendini dosyadan çıkarır | İzin verilir (sorumlu değilse) veya engellenir |
| EC5 | Atanan avukatın yetkisi sonradan kaldırılır | Atama kalır, erişim engellenir |
| EC6 | Tüm atamalar kaldırılır | Sorumlu avukat zorunlu olduğundan engellenir |

#### US-3.9: Dosya Arama ve Filtreleme

**User Story**

Bir avukat olarak, dosyaları çeşitli kriterlere göre aramak ve filtrelemek istiyorum ki aradığım dosyaya hızlıca ulaşabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Büro dosya no, esas no, müvekkil adı ile arama yapılabilir |
| AC2 | Dosya türü, durum, sorumlu avukat ile filtreleme yapılabilir |
| AC3 | Tarih aralığı ile filtreleme yapılabilir |
| AC4 | Arama sonuçları kaydedilebilir (favori arama) |
| AC5 | Sonuçlar Excel'e aktarılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Hiç sonuç döndürmeyen arama yapılır | "Sonuç bulunamadı. Filtreleri değiştirin" mesajı |
| NC2 | Çok kısa arama terimi girilir (1-2 karakter) | "En az 3 karakter girin" uyarısı |
| NC3 | Stajyer, atanmadığı dosyaları arar | Sadece atandığı dosyalar sonuçlarda görünür |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Esas numarası farklı formatlarla aranır (2025/123, E.2025/123) | Her iki format da eşleşir |
| EC2 | Türkçe karakter içeren terimle arama yapılır | Doğru sonuçlar döner |
| EC3 | Büyük/küçük harf duyarsız arama | Tüm varyasyonlar bulunur |
| EC4 | Çok fazla filtre uygulanır (5+ kriter) | Tüm kriterler AND mantığıyla çalışır |
| EC5 | 10.000+ dosya içinde arama | Performans 3 saniyenin altında |
| EC6 | Silinmiş dosyalar aranır | Varsayılanda görünmez, "Arşivi dahil et" ile görünür |
| EC7 | Arama sonuçları sayfalanır (100+ sonuç) | 20'şer kayıt gösterilir, sayfalama çalışır |
| EC8 | Özel karakterlerle arama yapılır | Karakterler escape edilir |

#### US-3.10: Dosya Kapatma

**User Story**

Bir avukat olarak, sonuçlanan dosyayı kapatmak istiyorum ki aktif dosya listesi güncel kalsın ve raporlar doğru olsun.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Kapanış şekli seçilmelidir (kazanıldı, kaybedildi, sulh, vb.) |
| AC2 | Kapanış tarihi girilmelidir |
| AC3 | Kapanış notu opsiyoneldir |
| AC4 | Açık görevler için uyarı verilir |
| AC5 | Ödenmemiş alacak için uyarı verilir |
| AC6 | Kapatma sonrası dosya salt okunur olmaz, belge eklenebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Kapanış şekli seçilmeden kapatılır | "Kapanış şekli seçiniz" hatası |
| NC2 | Kapanış tarihi girilmeden kapatılır | "Kapanış tarihi zorunludur" hatası |
| NC3 | Kapanış tarihi dosya açılış tarihinden önce | "Kapanış tarihi açılış tarihinden önce olamaz" hatası |
| NC4 | Taslak durumundaki dosya kapatılır | "Taslak dosya kapatılamaz. Önce aktife alın" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Kapanış tarihi gelecekte girilir | Kabul edilir (planlanan kapanış) veya engellenir |
| EC2 | Açık duruşması olan dosya kapatılır | Uyarı: "Bu dosyada planlanmış duruşma var" |
| EC3 | Açık görevi olan dosya kapatılır | Uyarı: "X açık görev var. Kapatılsın mı?" |
| EC4 | Bakiyesi olan dosya kapatılır | Uyarı gösterilir, onay ile devam edilir |
| EC5 | Aynı dosya birden fazla kez kapatılır | "Dosya zaten kapalı" mesajı |
| EC6 | Kapatılmış dosya yeniden açılır | İzin verilir, yeniden açılma tarihi kaydedilir |
| EC7 | Kapanış şekli sonradan değiştirilir | İzin verilir, tarihçede kaydedilir |

