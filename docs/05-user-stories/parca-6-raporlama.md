### Parça 6: Raporlama ve Dashboard

> **Not:** Rapor performansı, export sınırları ve dashboard politika kuralları için `docs/00-terminoloji-ve-kurallar.md` dosyasındaki "Raporlama ve Export Performans Eşiği" maddesi geçerlidir. Aşağıdaki user story'ler, bu kuralların ekran bazındaki detaylarını içerir.

#### US-6.1: Ana Dashboard Görüntüleme

**User Story**

Bir avukat olarak, sisteme girdiğimde özet bilgileri dashboard'da görmek istiyorum ki güncel durumu hızlıca kavrayabilir ve önceliklerimi belirleyebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
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
|---|---|---|
| NC1 | Mali yetkisi olmayan kullanıcı dashboard görür | Alacak/tahsilat kartları gizlenir veya "Yetki gerekli" |
| NC2 | Hiç veri olmayan yeni sistemde dashboard açılır | "Henüz veri yok" mesajları, başlangıç rehberi |
| NC3 | Stajyer dashboard'a erişir | Sadece atandığı dosya/görev bilgileri görünür |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
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

_Performans Notu:_ Dashboard'daki kartlar gerçek zamanlı güncellenirken 5 saniyeyi aşan sorgular için arka plan yenileme tercih edilmeli; kullanıcıya skeleton ekran gösterilmelidir.

#### US-6.2: Dashboard Özelleştirme

**User Story**

Bir avukat olarak, dashboard'umu özelleştirmek istiyorum ki en çok kullandığım bilgileri ön planda görebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Dashboard widget'ları sürükle-bırak ile sıralanabilir |
| AC2 | Widget'lar gizlenebilir/gösterilebilir |
| AC3 | Widget boyutları ayarlanabilir (küçük, orta, büyük) |
| AC4 | Özelleştirmeler kullanıcı bazlı kaydedilir |
| AC5 | Varsayılan düzene dönüş seçeneği vardır |
| AC6 | Yeni widget eklenebilir (kullanılabilir listeden) |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Yetkisiz widget eklenmeye çalışılır (mali widget, yetkisiz kullanıcı) | Widget listesinde görünmez veya eklenemez |
| NC2 | Tüm widget'lar gizlenir | "En az bir widget aktif olmalı" uyarısı veya boş dashboard |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Çok fazla widget eklenir (20+) | Scroll eklenir, performans uyarısı |
| EC2 | Sürükle-bırak sırasında bağlantı kesilir | Son kaydedilen düzen korunur |
| EC3 | Mobil cihazda sürükle-bırak | Touch destekli veya devre dışı |
| EC4 | Varsayılana dönülür, sonra geri alınmak istenir | Geri alma seçeneği (son 1 düzen) |
| EC5 | Widget silinip tekrar eklendiğinde | Varsayılan boyut ve konumda eklenir |
| EC6 | Kullanıcı silindikten sonra özelleştirmeler | Kullanıcı ile birlikte silinir |
| EC7 | Aynı widget iki kez eklenmeye çalışılır | Engellenir, "Bu widget zaten ekli" mesajı |

#### US-6.3: Takvim Görünümü

**User Story**

Bir avukat olarak, takvim görünümünde duruşma ve görevlerimi görmek istiyorum ki zamanımı planlayabilir ve çakışmaları fark edebilir.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Günlük, haftalık, aylık görünüm seçenekleri vardır |
| AC2 | Duruşmalar takvimde farklı renkte gösterilir |
| AC3 | Görevler bitiş tarihine göre takvimde gösterilir |
| AC4 | Kritik tarihler (süre bitimi) takvimde gösterilir |
| AC5 | Etkinliğe tıklanınca detay açılır |
| AC6 | Takvim üzerinden yeni duruşma/görev eklenebilir |
| AC7 | Çakışan etkinlikler vurgulanır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Hiç etkinlik olmayan ay görüntülenir | Boş takvim gösterilir |
| NC2 | Yetkisiz kullanıcı başkasının takvimini görmeye çalışır | "Bu takvimi görüntüleme yetkiniz yok" veya kendi takvimi |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
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

#### US-6.4: Dosya Raporları

**User Story**

Bir kurucu ortak olarak, dosya raporlarını görmek istiyorum ki dosya durumlarını analiz edebilir ve ofis performansını değerlendirebilir.

**Acceptance Criteria**

| # | Kriter |
|---|---|
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
|---|---|---|
| NC1 | Yetkisiz kullanıcı tüm dosya raporunu görmeye çalışır | Sadece kendi dosyalarının raporu gösterilir |
| NC2 | Hiç dosya olmayan dönem seçilir | "Seçilen dönemde dosya bulunmuyor" mesajı |
| NC3 | Stajyer dosya raporlarına erişir | Erişim reddedilir veya sınırlı veri |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | 10.000+ dosya raporlanacak | Performans uyarısı, sayfalama veya özet mod |
| EC2 | Hiç kapanan dosya yok, kazanma oranı hesaplanamıyor | "Henüz kapanan dosya yok" mesajı |
| EC3 | Tüm dosyalar aynı türde | Grafik tek dilim gösterir |
| EC4 | Avukat ayrıldıktan sonra raporda görünümü | "Eski Çalışan" etiketi ile gösterilir |
| EC5 | Çok geniş tarih aralığı seçilir | Yıllık özet önerilir |
| EC6 | Filtre kombinasyonu hiç sonuç döndürmüyor | "Kriterlere uygun dosya yok" mesajı |
| EC7 | Grafik çok fazla kategori içeriyor (50+ dosya türü) | "Diğer" kategorisi oluşturulur |
| EC8 | PDF export grafikleri içeriyor | Grafikler resim olarak eklenir |
| EC9 | Karşılaştırmalı rapor istenir (bu yıl vs geçen yıl) | Yan yana veya üst üste grafik |
| EC10 | Büyük rapor export'u (50MB+) | Arka planda hazırlanır, kullanıcıya download linki iletilir |

#### US-6.5: Duruşma Raporları

**User Story**

Bir kurucu ortak olarak, duruşma raporlarını görmek istiyorum ki duruşma yükünü takip edebilir ve avukat performansını değerlendirebilir.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Dönemsel duruşma sayısı gösterilir |
| AC2 | Avukat bazlı duruşma dağılımı gösterilir |
| AC3 | Mahkeme bazlı duruşma dağılımı gösterilir |
| AC4 | Duruşma sonuçları dağılımı gösterilir (tamamlanan/ertelenen) |
| AC5 | Günlük/haftalık/aylık duruşma yoğunluğu grafiği gösterilir |
| AC6 | Yaklaşan duruşmalar listesi gösterilir |
| AC7 | Filtreler uygulanabilir (tarih, avukat, mahkeme, dosya türü) |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Yetkisiz kullanıcı tüm duruşma raporunu görmeye çalışır | Sadece kendi duruşmalarının raporu |
| NC2 | Hiç duruşma kaydı yokken rapor görüntülenir | "Duruşma kaydı bulunmuyor" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Bir avukatın ayda 100+ duruşması var | Yoğunluk uyarısı gösterilir |
| EC2 | Tüm duruşmalar ertelenmiş | Erteleme oranı %100 gösterilir |
| EC3 | Mahkeme adı değişmiş (birleşme, isim değişikliği) | Eski ve yeni isim ayrı veya birleşik gösterilir |
| EC4 | Aynı gün aynı avukat 5+ duruşma | Çakışma analizi gösterilir |
| EC5 | Geçmiş 5 yıllık duruşma trendi istenir | Performans uyarısı, yıllık özet |
| EC6 | Duruşma saati bilgisi eksik kayıtlar | "Saat bilgisi yok" olarak işaretlenir |
| EC7 | Resmi tatildeki duruşmalar | Ayrı işaretlenir (muhtemelen hatalı kayıt) |
| EC8 | Duruşma sonucu girilmemiş geçmiş duruşmalar | "Sonuç bekleniyor" uyarısı listesi |

#### US-6.6: Performans Raporları

**User Story**

Bir kurucu ortak olarak, avukat performans raporlarını görmek istiyorum ki ekibin verimliliğini ölçebilir ve iş dağılımını optimize edebilir.

**Acceptance Criteria**

| # | Kriter |
|---|---|
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
|---|---|---|
| NC1 | Yetkisiz kullanıcı performans raporunu görmeye çalışır | "Performans raporlarına erişim yetkiniz yok" hatası |
| NC2 | Avukat kendi performans raporunu görmek ister | Sadece kendi verilerini görür veya engellenir (politikaya göre) |
| NC3 | Tek avukatlı ofiste karşılaştırma raporu | "Karşılaştırma için birden fazla avukat gerekli" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Yeni başlayan avukatın performansı | Dönem ortalamasına göre değil, kendi süresine göre değerlendirme |
| EC2 | Avukat izne çıkmış, performans düşük görünüyor | İzin dönemleri hariç tutma seçeneği |
| EC3 | Part-time çalışan avukat | Çalışma saatine göre normalize etme |
| EC4 | Çok farklı uzmanlık alanları (ceza vs ticaret) | Dosya türüne göre ayrı değerlendirme |
| EC5 | Hedef belirlenmemiş | "Hedef belirlenmedi" gösterilir, kıyaslama yapılmaz |
| EC6 | Tüm görevler gecikmiş | %0 zamanında tamamlama, uyarı |
| EC7 | Performans skoru negatif çıkabilir mi | Minimum 0 veya negatif gösterilir |
| EC8 | Pasif avukatın geçmiş performansı | Görüntülenebilir, "Pasif" etiketi ile |
| EC9 | Performans raporu çalışanlara açık/kapalı ayarı | Sistem ayarlarından belirlenir |

#### US-6.7: Hatırlatma ve Bildirim Merkezi

**User Story**

Bir avukat olarak, tüm hatırlatma ve bildirimlerimi tek merkezden görmek istiyorum ki hiçbir önemli bildirimi kaçırmayayım.

**Acceptance Criteria**

| # | Kriter |
|---|---|
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
|---|---|---|
| NC1 | Hiç bildirim yokken merkez açılır | "Bildiriminiz bulunmuyor" mesajı |
| NC2 | Bildirime tıklanır ama ilgili kayıt silinmiş | "İlgili kayıt bulunamadı" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | 1000+ okunmamış bildirim var | Sayfalama, performans optimizasyonu |
| EC2 | Aynı anda çok fazla bildirim oluşuyor (toplu işlem) | Gruplanarak gösterilir |
| EC3 | Bildirim tercihleri kapatılmış, önemli bildirim | Kritik bildirimler her zaman gönderilir |
| EC4 | Bildirim e-posta olarak da gönderilecek ama e-posta adresi yok | Sadece sistem içi bildirim |
| EC5 | Kullanıcı çevrimdışıyken bildirimler birikir | Sonraki girişte toplu gösterilir |
| EC6 | Bildirim içeriği çok uzun | Kısaltılır, tıklanınca tam gösterilir |
| EC7 | Mobil cihazda push notification | Tarayıcı izni ile çalışır |
| EC8 | Bildirim tercihlerinde tüm bildirimler kapatılmak istenir | Uyarı: "Önemli bildirimleri kaçırabilirsiniz" |
| EC9 | Okunmamış bildirimleri işaretlerken sayfa yenileniyor | İşlem tamamlanana kadar beklenir veya batch |

#### US-6.8: Rapor Kaydetme ve Zamanlama

**User Story**

Bir kurucu ortak olarak, sık kullandığım raporları kaydetmek ve otomatik gönderim zamanlamak istiyorum ki rutin raporlama işlemlerinden tasarruf edeyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Rapor parametreleri kaydedilebilir (isim verilir) |
| AC2 | Kaydedilen raporlar listesi görüntülenebilir |
| AC3 | Kaydedilmiş rapor tek tıkla çalıştırılabilir |
| AC4 | Rapor düzenli çalışacak şekilde zamanlanabilir (günlük, haftalık, aylık) |
| AC5 | Zamanlanan rapor e-posta ile gönderilebilir |
| AC6 | Zamanlanan rapor birden fazla alıcıya gönderilebilir |
| AC7 | Zamanlama düzenlenebilir veya iptal edilebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Rapor ismi verilmeden kaydedilir | "Rapor ismi zorunludur" hatası |
| NC2 | Aynı isimle ikinci rapor kaydedilir | "Bu isimde rapor mevcut. Üzerine yazılsın mı?" |
| NC3 | Geçersiz e-posta adresine zamanlama yapılır | "Geçerli e-posta adresi girin" hatası |
| NC4 | Yetkisiz kullanıcı başkasının kaydedilmiş raporunu siler | "Bu raporu silme yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | 100+ kaydedilmiş rapor var | Kategorileme veya arama eklenir |
| EC2 | Zamanlanan rapor gönderim saatinde sunucu kapalı | Sonraki açılışta gönderilir veya atlanır |
| EC3 | Rapor alıcısı e-postayı almıyor (spam) | Gönderim logu tutulur, durum kontrol edilir |
| EC4 | Zamanlanmış rapor çok büyük (50MB+) | E-posta limiti uyarısı, link olarak gönderim |
| EC5 | Rapor parametrelerinde kullanılan filtre artık geçersiz (silinmiş avukat) | Uyarı mesajı, rapor hatasız çalışır |
| EC6 | Kullanıcı pasife alındığında zamanlanmış raporları | İptal edilir veya yöneticiye aktarılır |
| EC7 | Aynı rapor birden fazla zaman için zamanlanır | Kabul edilir, her biri ayrı çalışır |
| EC8 | Zamanlanan rapor ayda 1000+ kez çalışacak şekilde ayarlanır | Makul sınır (günde 1) veya uyarı |

#### US-6.9: Dışa Aktarma (Export)

**User Story**

Bir avukat olarak, raporları ve listeleri farklı formatlarda dışa aktarmak istiyorum ki verileri başka sistemlerde kullanabilir veya müvekkillere sunabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Excel (.xlsx) formatında export desteklenir |
| AC2 | PDF formatında export desteklenir |
| AC3 | CSV formatında export desteklenir |
| AC4 | Export dosya adı otomatik oluşturulur (Rapor_Tarih) |
| AC5 | Büyük veri setleri için arka planda export yapılır |
| AC6 | Export tamamlandığında bildirim gönderilir |
| AC7 | Export geçmişi görüntülenebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Boş veri seti export edilmeye çalışılır | "Export edilecek veri yok" mesajı |
| NC2 | Yetkisiz kullanıcı başkasının verilerini export eder | Sadece yetkili olduğu veriler export edilir |
| NC3 | Export sırasında oturum süresi dolar | Arka plan işlemi devam eder, sonra indirilebilir |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
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

#### US-6.10: Sistem Logları ve Denetim

**User Story**

Bir sistem yöneticisi olarak, sistem loglarını ve denetim kayıtlarını görmek istiyorum ki güvenlik olaylarını takip edebilir ve sorunları tespit edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Giriş/çıkış logları görüntülenebilir |
| AC2 | Kayıt oluşturma/güncelleme/silme logları görüntülenebilir |
| AC3 | Yetki değişikliği logları görüntülenebilir |
| AC4 | Başarısız giriş denemeleri görüntülenebilir |
| AC5 | Loglar tarih, kullanıcı, işlem türüne göre filtrelenebilir |
| AC6 | Loglar export edilebilir |
| AC7 | Kritik güvenlik olayları için anlık uyarı gönderilebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Yetkisiz kullanıcı sistem loglarına erişmeye çalışır | "Sistem loglarına erişim yetkiniz yok" hatası |
| NC2 | Çok geniş tarih aralığı seçilir (5 yıl) | Performans uyarısı, parçalı yükleme |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
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

#### US-6.11: Sistem Ayarları

**User Story**

Bir sistem yöneticisi olarak, sistem ayarlarını yönetmek istiyorum ki ofis ihtiyaçlarına göre sistemi yapılandırabilirim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
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
|---|---|---|
| NC1 | Yetkisiz kullanıcı sistem ayarlarına erişir | "Sistem ayarlarına erişim yetkiniz yok" hatası |
| NC2 | Geçersiz SMTP ayarları kaydedilir | "E-posta sunucusuna bağlanılamadı" hatası |
| NC3 | Oturum süresi 0 veya negatif girilir | "Geçerli bir süre girin" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
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

