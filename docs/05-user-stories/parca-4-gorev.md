### Parça 4: İş Listesi ve Görev Yönetimi

> **Not:** Görev durumları, alt görev, checklist ve bağımlılık tanımları için `docs/00-terminoloji-ve-kurallar.md` dosyasındaki sözlük ve politikalar geçerlidir. Aşağıdaki user story'ler bu standartları genişletmektedir.

#### US-4.1: Yeni Görev Oluşturma

**User Story**

Bir avukat olarak, yapılacak işleri görev olarak kaydetmek istiyorum ki işlerimi takip edebilir ve unutmamış olayım.

**Acceptance Criteria**

| # | Kriter |
|---|---|
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
|---|---|---|
| NC1 | Görev başlığı boş bırakılır | "Görev başlığı zorunludur" hatası |
| NC2 | Görevli atanmadan kaydedilir | "Görevli ataması zorunludur" hatası |
| NC3 | Bitiş tarihi başlangıç tarihinden önce girilir | "Bitiş tarihi başlangıç tarihinden önce olamaz" hatası |
| NC4 | Pasif kullanıcı görevli olarak atanır | "Pasif kullanıcı atanamaz" hatası |
| NC5 | Yetkisiz kullanıcı başkasına görev atar | "Başkasına görev atama yetkiniz yok" hatası |
| NC6 | Arşivlenmiş dosyaya görev bağlanır | "Arşivlenmiş dosyaya görev oluşturulamaz" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
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

#### US-4.2: Görev Atama ve Devretme

**User Story**

Bir avukat olarak, görevleri ekip üyelerine atamak veya devretmek istiyorum ki iş dağılımını yönetebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Görev oluşturulurken veya sonradan atama yapılabilir |
| AC2 | Birden fazla yardımcı görevli atanabilir |
| AC3 | Görevli değiştirildiğinde eski ve yeni görevliye bildirim gider |
| AC4 | Devir nedeni kaydedilebilir |
| AC5 | Atama geçmişi tutulur |
| AC6 | Sadece yetkili kullanıcılar başkasına atama yapabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Tamamlanmış göreve yeni görevli atanır | Uyarı: "Görev tamamlanmış. Yine de atamak istiyor musunuz?" |
| NC2 | İptal edilmiş göreve atama yapılır | "İptal edilmiş göreve atama yapılamaz" hatası |
| NC3 | Stajyer başka avukata görev atar | "Başkasına görev atama yetkiniz yok" hatası |
| NC4 | Görev kendisinden alınıp başkasına verilir (yetkisizce) | "Bu görevi devretme yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Aynı kullanıcıya tekrar atama yapılır | "Görev zaten bu kullanıcıya atanmış" mesajı |
| EC2 | Görev 10+ kişiye atanır | Kabul edilir, herkes görevi listesinde görür |
| EC3 | Asıl görevli pasife alınır | Uyarı gönderilir, yeniden atama istenir |
| EC4 | Devir sırasında görev durumu değişir | İşlem tamamlanır, son durum geçerli olur |
| EC5 | Tüm görevliler görevden çıkarılır | "En az bir görevli olmalıdır" hatası |
| EC6 | Görev devredilirken açıklama çok uzun | Maksimum 500 karakter sınırı |
| EC7 | Toplu görev devri yapılır (10+ görev) | Batch işlem, ilerleme gösterilir |
| EC8 | Atama bildirimi gönderilemez (e-posta hatası) | Görev atanır, bildirim hatası loglanır |

#### US-4.3: Görev Durumu Güncelleme

**User Story**

Bir avukat olarak, görev durumunu güncellemek istiyorum ki işin hangi aşamada olduğu görülebilsin.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Görev durumları: Bekliyor, Devam Ediyor, Beklemede, Tamamlandı, İptal |
| AC2 | Durum değişikliğinde not eklenebilir |
| AC3 | Durum değişikliği tarihçede kaydedilir |
| AC4 | Belirli durum geçişleri mantıksal olmalı |
| AC5 | Tamamlandı durumuna geçildiğinde tamamlanma tarihi otomatik kaydedilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Bekliyor durumundan direkt Tamamlandı'ya geçilir | İzin verilir (hızlı tamamlama) veya uyarı |
| NC2 | İptal edilmiş görev Devam Ediyor yapılır | "İptal edilmiş görev yeniden açılamaz" veya izin (iş kuralı) |
| NC3 | Yetkisiz kullanıcı başkasının görevini günceller | "Bu görevi güncelleme yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Aynı durum tekrar seçilir | İşlem yapılmaz, "Görev zaten bu durumda" |
| EC2 | Durum çok hızlı değiştirilir (1 saniyede 5 kez) | Son durum geçerli, tümü tarihçeye yazılır |
| EC3 | Tamamlandı yapılıp tekrar Devam Ediyor yapılır | İzin verilir, tamamlanma tarihi silinir |
| EC4 | Alt görevi olan görev tamamlanır | Uyarı: "X alt görev henüz tamamlanmadı" |
| EC5 | Bağımlı görevi olan görev tamamlanır | Bağımlı görevler "Bekliyor"dan "Başlayabilir"e geçer |
| EC6 | Görev Beklemede yapılır, bekleme nedeni girilmez | Uyarı gösterilir ama zorunlu değil |
| EC7 | Geçmiş tarihli bitiş tarihine sahip görev hala Bekliyor | Otomatik "Gecikti" işareti veya durum |

_Not:_ Standart durum geçişleri ve yönetici override koşulları için Terminoloji dokümanındaki "Görev Yaşam Döngüsü" tablosu referans alınır. User story yalnızca uygulamadaki zorunlu mesajları tanımlar.

#### US-4.4: Alt Görev Oluşturma

**User Story**

Bir avukat olarak, büyük görevleri alt görevlere bölmek istiyorum ki işi parçalara ayırıp daha iyi takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Her görevin altına alt görev eklenebilir |
| AC2 | Alt görevler ana görevden bağımsız atanabilir |
| AC3 | Alt görevlerin kendi başlangıç/bitiş tarihleri olabilir |
| AC4 | Tüm alt görevler tamamlandığında ana görev için uyarı verilir |
| AC5 | Alt görevin alt görevi oluşturulamaz (tek seviye) |
| AC6 | Alt görev sayısı sınırsızdır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Alt göreve alt görev eklenmek istenir | "Alt görevin alt görevi oluşturulamaz" hatası |
| NC2 | Tamamlanmış ana göreve alt görev eklenir | Uyarı: "Ana görev tamamlanmış. Yine de eklemek istiyor musunuz?" |
| NC3 | Alt görev başlığı boş bırakılır | "Alt görev başlığı zorunludur" hatası |
| NC4 | Alt görev bitiş tarihi ana görev bitişinden sonra | Uyarı gösterilir ama kabul edilir |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | 50+ alt görev oluşturulur | Kabul edilir, sayfalama veya scroll |
| EC2 | Alt görev ana görevden önce bitiyor | Kabul edilir |
| EC3 | Ana görev silinir, alt görevler ne olur | Alt görevler de silinir (cascade) veya bağımsız kalır |
| EC4 | Tüm alt görevler tamamlanır | Ana görev otomatik tamamlanmaz, uyarı verilir |
| EC5 | Alt görev farklı kullanıcıya atanır | Kabul edilir, bağımsız bildirim |
| EC6 | Alt görev ana görevden farklı dosyaya bağlanır | Engellenir, ana görevin dosyası geçerli |
| EC7 | Alt görevlerin sırası değiştirilir (drag-drop) | Sıralama kaydedilir |

_Politika Notu:_ Alt görevler tek seviye ile sınırlıdır ve ana görevin bağlamını (dosya/müvekkil) devralır. Bu kural ihlalini yakalayan validasyonlar Terminoloji dokümanındaki madde 2 ile uyumludur.

#### US-4.5: Görev Kontrol Listesi (Checklist)

**User Story**

Bir avukat olarak, görev içinde basit yapılacaklar listesi tutmak istiyorum ki küçük adımları hızlıca işaretleyebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Her göreve kontrol listesi maddeleri eklenebilir |
| AC2 | Maddeler tamamlandı/tamamlanmadı olarak işaretlenebilir |
| AC3 | Maddeler sürükle-bırak ile sıralanabilir |
| AC4 | Tamamlanan madde sayısı / toplam madde gösterilir |
| AC5 | Maddeler düzenlenebilir ve silinebilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Boş madde eklenir | "Madde metni zorunludur" hatası |
| NC2 | Tamamlanmış göreve madde eklenir | Kabul edilir (tamamlandıktan sonra ek iş çıkabilir) |
| NC3 | Yetkisiz kullanıcı madde ekler | "Bu göreve madde ekleme yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | 100+ madde eklenir | Kabul edilir, scroll gösterilir |
| EC2 | Madde metni çok uzun (500+ karakter) | Maksimum 200 karakter sınırı |
| EC3 | Tüm maddeler tamamlanır | "Tüm maddeler tamamlandı" bildirimi, görev durumu değişmez |
| EC4 | Tamamlanmış madde geri alınır | Kabul edilir, tamamlanma işareti kalkar |
| EC5 | Madde silinirken görev de siliniyor | Madde silme, görev silmeden bağımsız |
| EC6 | Aynı anda iki kullanıcı farklı maddeleri işaretler | Her iki değişiklik de kaydedilir |
| EC7 | Sıralama değiştirilirken bağlantı kesilir | Son kaydedilen sıra geçerli |

#### US-4.6: Görev Hatırlatıcıları

**User Story**

Bir avukat olarak, görevler için hatırlatıcı ayarlamak istiyorum ki önemli işleri zamanında yapmayı unutmayayım.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Görev bitiş tarihinden X gün/saat önce hatırlatıcı ayarlanabilir |
| AC2 | Birden fazla hatırlatıcı eklenebilir |
| AC3 | Hatırlatıcı sistem içi bildirim ve/veya e-posta olarak gönderilebilir |
| AC4 | Görev geciktiğinde otomatik hatırlatıcı gönderilir |
| AC5 | Hatırlatıcılar kapatılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Bitiş tarihi olmayan göreve hatırlatıcı eklenir | "Hatırlatıcı için bitiş tarihi gerekli" hatası |
| NC2 | Hatırlatıcı süresi bitiş tarihinden sonra ayarlanır | "Hatırlatıcı bitiş tarihinden önce olmalı" hatası |
| NC3 | Tamamlanmış görev için hatırlatıcı ayarlanır | "Tamamlanmış görev için hatırlatıcı ayarlanamaz" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Hatırlatıcı zamanı geçmişte kalır (görev güncellenince) | Hatırlatıcı hemen gönderilir veya atlanır |
| EC2 | Aynı zaman için birden fazla hatırlatıcı ayarlanır | Tek hatırlatıcı gönderilir, mükerrer önlenir |
| EC3 | Görevli değişir, eski hatırlatıcılar ne olur | Yeni görevliye aktarılır |
| EC4 | E-posta sunucusu çalışmıyor | Sistem içi bildirim gönderilir, log tutulur |
| EC5 | Kullanıcı bildirimleri kapatmış | Sadece sistem içi bildirim, e-posta gönderilmez |
| EC6 | 100+ görevin hatırlatıcısı aynı anda tetiklenir | Kuyruğa alınır, sırayla gönderilir |
| EC7 | Hatırlatıcı gönderildiğinde kullanıcı çevrimdışı | Sonraki girişte bildirim gösterilir |

_Politika Notu:_ Hatırlatıcıların tetiklenme sıralaması ve kuyruk davranışı Terminoloji dokümanındaki "Hatırlatıcılar ve Uyarılar" maddesine göre uygulanır.

#### US-4.7: Görev Arama ve Filtreleme

**User Story**

Bir avukat olarak, görevleri çeşitli kriterlere göre aramak ve filtrelemek istiyorum ki aradığım görevi hızlıca bulabileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Görev başlığı ve açıklamasında arama yapılabilir |
| AC2 | Durum, öncelik, görevli, tarih aralığı ile filtreleme yapılabilir |
| AC3 | Dosya veya müvekkile göre filtreleme yapılabilir |
| AC4 | Gecikmiş görevler ayrı filtrelenebilir |
| AC5 | Sonuçlar öncelik, tarih veya duruma göre sıralanabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Hiç sonuç döndürmeyen arama | "Sonuç bulunamadı" mesajı |
| NC2 | Çok kısa arama terimi (1-2 karakter) | "En az 3 karakter girin" uyarısı |
| NC3 | Stajyer başkasının görevlerini arar | Sadece kendine atanan görevler görünür |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Türkçe karakter ile arama | Doğru sonuçlar döner |
| EC2 | Büyük/küçük harf duyarsız arama | Tüm varyasyonlar bulunur |
| EC3 | Çoklu filtre uygulanır (5+ kriter) | AND mantığı ile çalışır |
| EC4 | "Bugün" filtresi gece yarısında uygulanır | Doğru gün sınırı |
| EC5 | "Bu hafta" filtresi hafta ortasında | Pazartesi-Pazar veya Pazar-Cumartesi (ayar) |
| EC6 | 10.000+ görev içinde arama | Performans 2 saniye altında |
| EC7 | Silinmiş görevler aranır | Varsayılanda görünmez, filtre ile görünür |
| EC8 | Tarih filtresi saat dilimi farklı kullanıcıda | Kullanıcı saat dilimine göre hesaplanır |

#### US-4.8: Görev Görünümleri (Liste, Kanban, Takvim)

**User Story**

Bir avukat olarak, görevlerimi farklı görünümlerde incelemek istiyorum ki çalışma şeklime uygun şekilde takip edebileyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Liste görünümünde tablo formatında gösterilir |
| AC2 | Kanban görünümünde durumlara göre sütunlar oluşur |
| AC3 | Takvim görünümünde tarihlerine göre gösterilir |
| AC4 | Görünüm tercihi kaydedilir |
| AC5 | Her görünümde filtreleme çalışır |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Tarihsiz görevler takvim görünümünde | Ayrı "Tarihsiz" bölümünde gösterilir |
| NC2 | Çok fazla görev tek günde (50+) | Günün altında "+45 daha" linki |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Kanban'da sürükle-bırak ile durum değişir | Durum güncellenir, tarihçeye yazılır |
| EC2 | Kanban sütununda 100+ görev var | Scroll eklenir, lazy loading |
| EC3 | Takvim görünümünde çok uzun başlık | Kısaltılır, hover ile tam gösterilir |
| EC4 | Mobil cihazda Kanban görünümü | Yatay scroll veya tek sütun modu |
| EC5 | Görünüm değiştirilirken filtreleme korunur | Aktif filtreler tüm görünümlerde geçerli |
| EC6 | Takvimde ay değiştirilir, yükleme uzun sürer | Loading göstergesi, cache kullanımı |
| EC7 | Liste görünümünde sütunlar gizlenebilir | Kullanıcı tercihi kaydedilir |
| EC8 | Kanban'da özel sütun eklenir | İzin verilir veya sabit sütunlar |

#### US-4.9: Görev Raporları

**User Story**

Bir kurucu ortak olarak, görev raporları görmek istiyorum ki ekibin iş yükünü ve performansını değerlendirebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Toplam, tamamlanan, geciken görev sayıları gösterilir |
| AC2 | Görevli bazında görev dağılımı görüntülenir |
| AC3 | Ortalama tamamlanma süresi hesaplanır |
| AC4 | Öncelik ve tür bazında dağılım gösterilir |
| AC5 | Tarih aralığı ile filtreleme yapılabilir |
| AC6 | Rapor Excel'e aktarılabilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Yetkisiz kullanıcı tüm raporları görmeye çalışır | Sadece kendi görevlerinin raporunu görür |
| NC2 | Hiç görev olmayan tarih aralığı seçilir | "Bu dönemde görev bulunmuyor" mesajı |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Çok geniş tarih aralığı seçilir (5 yıl) | Performans uyarısı, parçalı yükleme |
| EC2 | Sadece 1 görev varken ortalama hesaplanır | O görevin süresi ortalama olur |
| EC3 | Hiç tamamlanmış görev yokken oran hesaplanır | %0 veya "Henüz tamamlanan görev yok" |
| EC4 | Tahmini süre girilmemiş görevlerin raporu | "Veri yok" olarak gösterilir |
| EC5 | Rapor çok fazla veri içeriyor (10.000+ satır) | Sayfalama veya özet mod |
| EC6 | Rapor oluşturulurken yeni görev eklenir | Anlık rapor, yeni görev dahil olmayabilir |
| EC7 | Excel export çok büyük (50MB+) | Dosya sıkıştırılır veya bölünür |

#### US-4.10: Toplu Görev İşlemleri

**User Story**

Bir avukat olarak, birden fazla görevi aynı anda güncellemek istiyorum ki zamandan tasarruf edebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Çoklu görev seçimi yapılabilir |
| AC2 | Seçili görevlerin durumu toplu değiştirilebilir |
| AC3 | Seçili görevler toplu atanabilir |
| AC4 | Seçili görevler toplu silinebilir (yetki gerekir) |
| AC5 | Toplu işlem sonucu özet gösterilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Hiç görev seçmeden toplu işlem yapılır | "En az bir görev seçin" hatası |
| NC2 | Farklı durumlardaki görevler aynı duruma çekilir | İzin verilir, geçersiz geçişler raporlanır |
| NC3 | Yetkisiz kullanıcı toplu silme yapar | "Toplu silme yetkiniz yok" hatası |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | 500+ görev seçilir | Performans uyarısı, batch işlem |
| EC2 | Seçili görevlerden bazıları başka kullanıcıya ait | Sadece yetkili olunanlar işlenir, diğerleri raporlanır |
| EC3 | Toplu işlem sırasında bağlantı kesilir | İşlem geri alınır veya yarım kalır, durum raporlanır |
| EC4 | Toplu atama yapılırken hedef kullanıcı pasif | İşlem reddedilir, hata mesajı |
| EC5 | Tüm görevler seçilip silinir | Onay kutusu: "Tüm görevleri silmek istediğinize emin misiniz?" |
| EC6 | Toplu işlem yarıda iptal edilir | Yapılan işlemler kalır, kalanlar yapılmaz |
| EC7 | Aynı görevler iki farklı kullanıcı tarafından toplu işleme alınır | İlk tamamlayan kazanır, diğerine uyarı |

#### US-4.11: Görev Bağımlılıkları

**User Story**

Bir avukat olarak, görevler arası bağımlılık tanımlamak istiyorum ki bir iş bitmeden diğerinin başlamaması gereken durumları yönetebil eyim.

**Acceptance Criteria**

| # | Kriter |
|---|---|
| AC1 | Görev, başka bir görevin tamamlanmasına bağlanabilir |
| AC2 | Bağımlı görev, önceki tamamlanmadan "Devam Ediyor" yapılamaz |
| AC3 | Döngüsel bağımlılık engellenmelidir |
| AC4 | Bağımlılık grafiği görüntülenebilir |
| AC5 | Önceki görev tamamlandığında bağımlı göreve bildirim gönderilir |

**Negatif Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| NC1 | Görev kendisine bağımlı yapılır | "Görev kendisine bağımlı olamaz" hatası |
| NC2 | Döngüsel bağımlılık oluşturulur (A→B→C→A) | "Döngüsel bağımlılık oluşturulamaz" hatası |
| NC3 | Bağımlı görev zorla başlatılır | Uyarı gösterilir, yönetici onayı ile izin verilir |

**Edge Cases**

| # | Senaryo | Beklenen Sonuç |
|---|---|---|
| EC1 | Bir görev 10+ göreve bağımlı | Kabul edilir, tümü tamamlanmalı |
| EC2 | Bağımlı olunan görev silinir | Bağımlılık otomatik kalkar, uyarı verilir |
| EC3 | Bağımlı olunan görev iptal edilir | Bağımlı görev başlayabilir hale gelir |
| EC4 | Çok derin bağımlılık zinciri (A→B→C→D→E→F) | Kabul edilir, grafik gösterilir |
| EC5 | Bağımlılık farklı dosyaların görevleri arasında | İzin verilir |
| EC6 | Tamamlanmış göreve bağımlılık eklenir | Bağımlı görev hemen başlayabilir durumda |
| EC7 | Bağımlılık eklendikten sonra önceki görev geri açılır | Bağımlı görev tekrar bekler durumuna geçer |
| EC8 | Bağımlılık grafiği çok karmaşık (20+ düğüm) | Görselleştirme için zoom/filtreleme |

_Not:_ Bağımlılık grafiği yalnızca okuma amaçlıdır; sürükle-bırak düzenleme Terminoloji dokümanında belirtildiği gibi desteklenmez. Yönetici override gerektiren durumlarda sistem onay ekranı açmalıdır.
