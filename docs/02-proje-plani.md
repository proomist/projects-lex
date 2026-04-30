## BÖLÜM 2: PROJE PLANI

> **Ortak Tanımlar:** Görev/mali terimler ve standart politikalar için `docs/00-terminoloji-ve-kurallar.md` dosyasındaki sözlüğe başvurulmalıdır.

### Genel Mimari Çerçeve

Sistem altı ana modülden oluşur: Kullanıcı ve Yetki Yönetimi, Müvekkil Yönetimi, Dosya/Dava Yönetimi, İş Listesi ve Görev Yönetimi, Mali Takip, Raporlama ve Dashboard. Her modül kendi içinde bağımsız çalışabilir ancak diğer modüllerle veri ilişkisi kurar.

### Modül Haritası ve İlişkiler

```
┌─────────────────────────────────────────────────────────────────┐
│                    KULLANICI VE YETKİ YÖNETİMİ                  │
│         (Tüm modüllerin erişim kontrolünü yönetir)              │
└─────────────────────────────────────────────────────────────────┘
                                │
        ┌───────────────────────┼───────────────────────┐
        ▼                       ▼                       ▼
┌───────────────┐       ┌───────────────┐       ┌───────────────┐
│   MÜVEKKİL    │◄─────►│  DOSYA/DAVA   │◄─────►│   İŞ LİSTESİ  │
│   YÖNETİMİ    │       │   YÖNETİMİ    │       │    GÖREVLER   │
└───────────────┘       └───────────────┘       └───────────────┘
        │                       │                       │
        └───────────┬───────────┴───────────┬───────────┘
                    ▼                       ▼
            ┌───────────────┐       ┌───────────────┐
            │  MALİ TAKİP   │       │  RAPORLAMA    │
            │  ALACAK-BORÇ  │──────►│  DASHBOARD    │
            └───────────────┘       └───────────────┘
```

### Mobil Uyum ve Native Yol Haritası

- **Aşama 0 - Responsive/PWA:** `docs/01-ozellik-analizi.md` içindeki responsive strateji tüm ekranlarda uygulanır; 360/768/1024/1440 breakpoint'leri design token üzerinden yönetilir.
- **Aşama 1 - PWA Paketleme:** Web uygulaması manifest, icon, splash ve offline cache (takvim, görev listesi) destekleriyle yayınlanır; mobil kullanıcılar için kısa yol deneyimi sağlanır.
- **Aşama 2 - API Sözleşmesi:** REST/GraphQL endpoint'leri OpenAPI/GraphQL şemalarında dondurularak mobil ekip ile paylaşılır; versiyonlama zorunludur (v1, v2...).
- **Aşama 3 - Native Köprü:** React Native uygulaması, web ile aynı component kütüphanesinin (design token + headless component) paylaşımı sayesinde kademeli geliştirilir; auth, görev, takvim modülleri önceliklidir.
- **Aşama 4 - Feature Parity:** Native uygulama web ile eş seviyeye geldiğinde dashboard ve rapor ekranları için özel mobil layout'lar devreye alınır.

### MODÜL 1: KULLANICI VE YETKİ YÖNETİMİ

#### 1.1 Kullanıcı Yönetimi

**Kullanıcı Kartı Bilgileri**

- Kullanıcı adı (benzersiz, login için)
- Ad soyad
- E-posta (şifre sıfırlama ve bildirimler için)
- Telefon
- Unvan: Kurucu Ortak, Ortak Avukat, Avukat, Stajyer, Sekreter, Muhasebeci
- Durum: Aktif / Pasif / Askıda
- Profil fotoğrafı
- Son giriş tarihi
- Oluşturulma tarihi

**Kimlik Doğrulama**

- Kullanıcı adı + şifre ile giriş
- Şifre politikası: minimum 8 karakter, büyük/küçük harf, rakam
- Başarısız giriş denemesi kilidi (5 deneme sonrası 15 dk)
- Şifre sıfırlama e-posta ile
- Oturum süresi ayarlanabilir (varsayılan 8 saat)
- Çoklu oturum kontrolü (aynı anda tek cihaz veya çoklu)

**Kullanıcı İşlemleri**

- Kullanıcı oluşturma (sadece yönetici)
- Kullanıcı düzenleme
- Kullanıcı pasife alma (silmek yerine)
- Şifre zorunlu değiştirme
- Kullanıcı aktivite logu görüntüleme

#### 1.2 Rol Yönetimi

**Varsayılan Roller**

| Rol | Açıklama |
|---|---|
| Sistem Yöneticisi | Tüm yetkiler, kullanıcı ve rol yönetimi |
| Kurucu Ortak | Tüm dosya ve mali verilere erişim, raporlar |
| Avukat | Atandığı dosyalar + kendi oluşturduğu dosyalar |
| Stajyer | Atandığı dosyalara sınırlı erişim, düzenleme yok |
| Sekreter | Dosya görüntüleme, görev oluşturma, belge yükleme |
| Muhasebeci | Sadece mali modül erişimi |

**Rol Tanımlama**

- Rol adı ve açıklaması
- Varsayılan rol işareti (yeni kullanıcıya otomatik atanacak)
- Rol aktif/pasif durumu

#### 1.3 Yetki Tanımlama

**Yetki Kategorileri ve Detayları**

Müvekkil Modülü Yetkileri:

- `muvekkil.listele` - Müvekkil listesini görme
- `muvekkil.goruntule` - Müvekkil detayını görme
- `muvekkil.ekle` - Yeni müvekkil oluşturma
- `muvekkil.duzenle` - Müvekkil bilgisi güncelleme
- `muvekkil.sil` - Müvekkil silme/arşivleme
- `muvekkil.bakiye_gor` - Müvekkil bakiyesini görme

Dosya Modülü Yetkileri:

- `dosya.listele` - Dosya listesini görme
- `dosya.goruntule` - Dosya detayını görme
- `dosya.ekle` - Yeni dosya açma
- `dosya.duzenle` - Dosya bilgisi güncelleme
- `dosya.sil` - Dosya silme/arşivleme
- `dosya.durusma_ekle` - Duruşma kaydı ekleme
- `dosya.belge_yukle` - Belge yükleme
- `dosya.belge_sil` - Belge silme
- `dosya.atama_yap` - Dosyaya kullanıcı atama

İş Listesi Yetkileri:

- `gorev.listele` - Görev listesini görme
- `gorev.goruntule` - Görev detayını görme
- `gorev.ekle` - Yeni görev oluşturma
- `gorev.duzenle` - Görev güncelleme
- `gorev.sil` - Görev silme
- `gorev.atama_yap` - Görevi başkasına atama
- `gorev.tamamla` - Görevi tamamlandı işaretleme

Mali Modül Yetkileri:

- `mali.listele` - Mali hareketleri listeleme
- `mali.goruntule` - Hareket detayını görme
- `mali.alacak_ekle` - Alacak kaydı oluşturma
- `mali.tahsilat_ekle` - Tahsilat kaydı oluşturma
- `mali.gider_ekle` - Gider/masraf kaydı oluşturma
- `mali.duzenle` - Mali kayıt düzenleme
- `mali.sil` - Mali kayıt silme
- `mali.rapor_gor` - Mali raporları görme

Raporlama Yetkileri:

- `rapor.dosya` - Dosya raporlarını görme
- `rapor.mali` - Mali raporları görme
- `rapor.performans` - Performans raporlarını görme
- `rapor.export` - Rapor dışa aktarma

Sistem Yetkileri:

- `sistem.kullanici_yonet` - Kullanıcı ekleme/düzenleme
- `sistem.rol_yonet` - Rol ve yetki tanımlama
- `sistem.ayarlar` - Sistem ayarlarını değiştirme
- `sistem.log_gor` - Sistem loglarını görme

#### 1.4 Veri Erişim Kapsamı

**Kapsam Türleri**

- `tumu` - Tüm verilere erişim
- `atanan` - Sadece kendisine atanan kayıtlar
- `departman` - Aynı departmandaki kullanıcıların kayıtları
- `olusturan` - Sadece kendi oluşturduğu kayıtlar

**Kapsam Uygulama Matrisi**

| Rol | Müvekkil | Dosya | Görev | Mali |
|---|---|---|---|---|
| Sistem Yöneticisi | Tümü | Tümü | Tümü | Tümü |
| Kurucu Ortak | Tümü | Tümü | Tümü | Tümü |
| Avukat | Tümü | Atanan+Oluşturan | Atanan+Oluşturan | Dosya bazlı |
| Stajyer | Görüntüle | Atanan | Atanan | Yok |
| Sekreter | Tümü | Tümü (salt okunur) | Tümü | Yok |
| Muhasebeci | Bakiye | Yok | Yok | Tümü |

#### 1.5 Aktivite Logu

**Loglanan İşlemler**

- Giriş/çıkış işlemleri
- Kayıt oluşturma/güncelleme/silme
- Yetki değişiklikleri
- Belge indirme
- Rapor görüntüleme
- Başarısız erişim denemeleri

**Log Kayıt Bilgileri**

- Tarih saat
- Kullanıcı
- İşlem türü
- Modül ve kayıt ID
- Eski değer / yeni değer (değişiklik için)
- IP adresi
- Tarayıcı/cihaz bilgisi

### MODÜL 2: MÜVEKKİL YÖNETİMİ

#### 2.1 Müvekkil Kartı

**Genel Bilgiler**

- Müvekkil kodu (otomatik: M-2025-0001)
- Müvekkil türü: Gerçek Kişi / Tüzel Kişi
- Kayıt tarihi
- Durum: Aktif / Pasif / Potansiyel / Kara Liste
- Sorumlu avukat (varsayılan atama)
- Referans kaynağı (nereden geldi)
- Notlar

**Gerçek Kişi Bilgileri**

- TC Kimlik No
- Ad
- Soyad
- Doğum tarihi
- Doğum yeri
- Anne adı / Baba adı
- Meslek
- Medeni durum

**Tüzel Kişi Bilgileri**

- Vergi numarası
- Vergi dairesi
- Ticaret unvanı
- Ticaret sicil no
- Mersis no
- Kuruluş tarihi
- Faaliyet alanı
- Yetkili kişi adı soyadı
- Yetkili kişi görevi

#### 2.2 İletişim Bilgileri

**Telefon Kayıtları (çoklu)**

- Telefon numarası
- Tür: Cep / İş / Ev / Faks
- Birincil işareti
- WhatsApp aktif mi

**E-posta Kayıtları (çoklu)**

- E-posta adresi
- Tür: Kişisel / İş
- Birincil işareti
- Bildirim gönderilsin mi

**Adres Kayıtları (çoklu)**

- Adres başlığı (Ev, İş, vb.)
- İl / İlçe / Mahalle
- Açık adres
- Posta kodu
- Birincil işareti

#### 2.3 Bağlantılı Kişiler

**Kişi Türleri**

- Aile üyesi (eş, çocuk, anne, baba)
- İş ortağı
- Şirket yetkilisi
- Vekil
- Acil durumda aranacak

**Kişi Bilgileri**

- Ad soyad
- İlişki türü
- Telefon
- E-posta
- Not

#### 2.4 Müvekkil Geçmişi

**Görüşme Kayıtları**

- Tarih saat
- Görüşme türü: Telefon / Yüz yüze / E-posta / Online
- Görüşen avukat
- Konu özeti
- Detaylı not
- Sonraki adım

**Dosya Özeti**

- Toplam dosya sayısı
- Aktif dosya sayısı
- Kapanan dosya sayısı
- Kazanılan / Kaybedilen

**Mali Özet**

- Toplam alacak
- Toplam tahsilat
- Güncel bakiye
- Vadesi geçen alacak

#### 2.5 Müvekkil İşlemleri

**Temel İşlemler**

- Yeni müvekkil kaydı
- Müvekkil bilgisi güncelleme
- Müvekkil arşivleme (pasife alma)
- Müvekkil birleştirme (duplicate kayıtları tek kayıtta toplama)

**Toplu İşlemler**

- Excel'den müvekkil aktarımı
- Müvekkil listesi dışa aktarma
- Toplu SMS/e-posta gönderimi (opsiyonel)

### MODÜL 3: DOSYA/DAVA YÖNETİMİ

#### 3.1 Dosya Türleri ve Kategorileri

**Ana Dosya Türleri**

Dava Dosyası:

- Hukuk davası (alacak, tazminat, boşanma, miras, tapu, tüketici)
- Ceza davası (sanık müdafii, katılan vekili)
- İdare davası (iptal, tam yargı)
- İş davası (işe iade, kıdem/ihbar, iş kazası)
- Ticaret davası (iflas, konkordato, şirket)

İcra Dosyası:

- İlamlı icra
- İlamsız icra
- Kambiyo senetlerine özgü
- Kira alacağı
- Tahliye

Danışmanlık Dosyası:

- Sözleşme inceleme/hazırlama
- Hukuki görüş
- Şirket danışmanlığı
- Sürekli danışmanlık

Arabuluculuk Dosyası:

- Zorunlu arabuluculuk
- İhtiyari arabuluculuk

#### 3.2 Dosya Kartı

**Temel Bilgiler**

- Büro dosya no (otomatik: D-2025-0001)
- Dosya türü ve kategorisi
- Dosya açılış tarihi
- Müvekkil (bağlantı)
- Müvekkil pozisyonu: Davacı / Davalı / Alacaklı / Borçlu / Şüpheli / Sanık / Mağdur / Katılan
- Sorumlu avukat
- Dosya durumu

**Mahkeme/İcra Bilgileri**

- Mahkeme/icra dairesi adı
- İl / İlçe
- Esas numarası
- Karar numarası (sonuçlandıysa)
- Dosya türü (mahkemede)

**Karşı Taraf Bilgileri (çoklu)**

- Ad soyad / Unvan
- TC / Vergi no
- Pozisyon (davalı, davacı, vs.)
- Avukatı
- İletişim bilgileri

**Dava Detayları**

- Dava konusu özeti
- Talep edilen tutar
- Harca esas değer
- Dava tarihi
- Beklenen sonuç

#### 3.3 Dosya Durumları

**Durum Geçiş Şeması**

```
[Taslak] ──► [Aktif] ──► [Beklemede] ──► [Karar Aşaması] ──► [Kapandı]
                │              │                                  │
                │              ▼                                  ▼
                └────► [İptal/Düşme]                         [Arşiv]
```

**Durum Açıklamaları**

- **Taslak:** Dosya açıldı, henüz işlem başlamadı
- **Aktif:** Dosya görülüyor, işlemler devam ediyor
- **Beklemede:** Bilirkişi, keşif, başka dosya bekleniyor
- **Karar Aşaması:** Duruşmalar bitti, karar bekleniyor
- **Kapandı:** Dosya sonuçlandı
- **İptal/Düşme:** Dosya düşürüldü veya iptal edildi
- **Arşiv:** Kapanan dosya arşive kaldırıldı

**Kapanış Şekilleri**

- Kazanıldı (tam)
- Kazanıldı (kısmi)
- Kaybedildi
- Sulh ile sonuçlandı
- Feragat
- Kabul
- Düşme
- Görevsizlik/Yetkisizlik
- Birleştirme

#### 3.4 Duruşma Yönetimi

**Duruşma Kaydı**

- Duruşma tarihi
- Duruşma saati
- Mahkeme salonu
- Duruşma türü: Ön inceleme / Tahkikat / Karar / Keşif / Bilirkişi
- Katılacak avukat
- Durum: Planlandı / Tamamlandı / Ertelendi / İptal

**Duruşma Sonucu**

- Sonuç özeti
- Verilen kararlar
- Ara karar
- Sonraki duruşma tarihi
- Yapılacak işlemler
- Duruşma notları (detaylı)

**Hatırlatıcılar**

- Duruşmadan 1 hafta önce
- Duruşmadan 1 gün önce
- Duruşma günü sabahı

#### 3.5 Belge Yönetimi

**Belge Kategorileri**

- Vekaletname
- Dilekçe (dava, cevap, cevaba cevap, delil, beyan)
- Mahkeme kararı (ara karar, nihai karar)
- Bilirkişi raporu
- Keşif tutanağı
- Sözleşme
- Delil (fatura, senet, yazışma, fotoğraf)
- Kimlik belgeleri
- Diğer

**Belge Bilgileri**

- Belge adı
- Kategori
- Belge tarihi (belgenin kendi tarihi)
- Yükleme tarihi
- Yükleyen kullanıcı
- Dosya boyutu
- Açıklama/not

**Belge İşlemleri**

- Belge yükleme (tek/çoklu)
- Belge indirme
- Belge önizleme
- Belge silme (yetki gerekir)
- Belge versiyonlama
- Belge arama (isim ve içerik)

#### 3.6 Dosya Atama

**Atama Türleri**

- Sorumlu avukat (tek, zorunlu)
- Yardımcı avukat (çoklu)
- Stajyer
- Sekreter desteği

**Atama Bilgileri**

- Atanan kullanıcı
- Atama rolü
- Atama tarihi
- Atayan kullanıcı
- Not


### MODÜL 4: İŞ LİSTESİ VE GÖREV YÖNETİMİ

> Ayrıntılı acceptance criteria ve edge case'ler için `docs/05-user-stories/parca-4-gorev.md` dosyasını referans alın. Bu bölüm yüksek seviye mimariyi özetler.

#### 4.1 Görev Yapısı

**Görev Temel Bilgileri**

- Görev ID (otomatik)
- Görev başlığı
- Görev açıklaması (detaylı)
- Görev türü
- Öncelik: Düşük / Normal / Yüksek / Acil
- Durum: Bekliyor / Devam Ediyor / Tamamlandı / İptal

**Tarih Bilgileri**

- Oluşturma tarihi
- Başlangıç tarihi (plananan)
- Bitiş tarihi (plananan/deadline)
- Gerçek başlangıç tarihi
- Gerçek bitiş tarihi
- Tahmini süre (saat)
- Gerçekleşen süre (saat)

**İlişkiler**

- Bağlı dosya (opsiyonel)
- Bağlı müvekkil (opsiyonel, dosya yoksa)
- Üst görev (alt görev için)
- Bağımlı görevler (bu bitmeden başlayamaz)

#### 4.2 Görev Türleri

**Dosya İlişkili Görevler**

- Dilekçe hazırlama
- Duruşmaya hazırlık
- Belge toplama
- Bilirkişi raporu inceleme
- Müvekkil ile görüşme
- Karşı taraf ile iletişim
- Temyiz/itiraz hazırlığı
- Harç yatırma
- Tebligat takibi

**Genel Görevler**

- Araştırma yapma
- Mevzuat inceleme
- İçtihat tarama
- Toplantı
- Eğitim/seminer
- İdari iş

**Periyodik Görevler**

- Dosya durum kontrolü
- Müvekkil bilgilendirme
- Süre takibi
- Ödeme hatırlatma

#### 4.3 Görev Atama

**Atama Bilgileri**

- Görevli kullanıcı (asıl sorumlu)
- Yardımcı görevliler (çoklu)
- Atayan kullanıcı
- Atama tarihi
- Atama notu

**Atama Kuralları**

- Bir görevin en az bir sorumlusu olmalı
- Görev atama yetkisi olan kullanıcılar atayabilir
- Kendine görev atama her kullanıcı yapabilir
- Görevli değiştiğinde bildirim gider

**Görev Devretme**

- Mevcut görevli başka birine devredebilir
- Devir geçmişi tutulur
- Devir nedeni kaydedilir

#### 4.4 Görev Durumları ve Geçişleri

**Durum Akışı**

```
[Taslak] ──► [Bekliyor] ──► [Devam Ediyor] ──► [Tamamlandı]
                │                  │
                ▼                  ▼
            [İptal]           [Beklemede]
                                   │
                                   ▼
                            [Devam Ediyor]
```

**Durum Açıklamaları**

- **Taslak:** Oluşturuldu, henüz atanmadı
- **Bekliyor:** Atandı, başlangıç tarihi gelmedi
- **Devam Ediyor:** Üzerinde çalışılıyor
- **Beklemede:** Dış etken bekleniyor (bilgi, onay, vs.)
- **Tamamlandı:** İş bitirildi
- **İptal:** Görev iptal edildi

**Durum Değişikliği Kaydı**

- Her durum değişikliği loglanır
- Kim, ne zaman, hangi durumdan hangi duruma
- Değişiklik notu

#### 4.5 Alt Görevler ve Kontrol Listesi

**Alt Görev (Subtask)**

- Büyük görevleri parçalama
- Her alt görev bağımsız atanabilir
- Alt görevlerin tamamlanması üst görevi etkiler
- Alt görev kendi tarihlerine sahip

**Kontrol Listesi (Checklist)**

- Basit yapılacaklar listesi
- Sadece başlık ve tamamlandı işareti
- Görev içinde hızlı takip için
- Sıralama desteği

#### 4.6 Hatırlatıcı ve Bildirimler

**Otomatik Hatırlatıcılar**

- Başlangıç tarihi geldiğinde
- Bitiş tarihinden X gün/saat önce
- Bitiş tarihi geçtiğinde (gecikme)
- Beklemede görev için periyodik hatırlatma

**Hatırlatıcı Ayarları**

- Hatırlatıcı aktif/pasif
- Hatırlatma zamanı (gün/saat önce)
- Hatırlatma tekrarı
- Bildirim kanalı: Sistem içi / E-posta

**Bildirim Türleri**

- Görev atandığında
- Görev güncellendi
- Görev tamamlandı
- Görev süresi yaklaşıyor
- Görev gecikti
- Yorum eklendi

#### 4.7 Görev Takibi ve Raporlama

**Görev Listesi Görünümleri**

- Liste görünümü (tablo)
- Kanban görünümü (durumlara göre sütunlar)
- Takvim görünümü
- Gantt görünümü (zaman çizelgesi)

**Filtreleme Seçenekleri**

- Duruma göre
- Önceliğe göre
- Görevliye göre
- Tarihe göre (bu hafta, bu ay, geciken)
- Dosyaya göre
- Müvekkile göre
- Türe göre

**Görev Metrikleri**

- Toplam görev sayısı
- Tamamlanan görev sayısı
- Geciken görev sayısı
- Ortalama tamamlanma süresi
- Görevli bazında dağılım

### MODÜL 5: MALİ TAKİP (ALACAK-BORÇ)

> Ayrıntılı senaryolar için `docs/05-user-stories/parca-5-mali-takip.md` dosyasına bakınız. Buradaki maddeler modül kapsamını üst düzeyde tanımlar.

#### 5.1 Mali Hesap Yapısı

**Hesap Türleri**

Alacak Hesapları (Müvekkilden beklenen):

- Vekalet ücreti alacağı
- Masraf avansı alacağı
- Duruşma ücreti alacağı
- Danışmanlık ücreti alacağı

Gider Hesapları (Dosya için yapılan harcama):

- Harç giderleri
- Bilirkişi ücreti
- Keşif masrafı
- Tebligat/posta gideri
- Yol/konaklama gideri
- Diğer masraflar

Tahsilat Hesapları (Müvekkilden alınan):

- Nakit tahsilat
- Banka tahsilatı
- Kredi kartı tahsilatı

#### 5.2 Mali Hareket Kaydı

**Hareket Temel Bilgileri**

- Hareket ID (otomatik)
- Hareket tarihi
- Hareket türü: Alacak / Gider / Tahsilat
- Hareket kategorisi (ücreti türü, gider türü, vs.)
- Tutar
- Para birimi (TL varsayılan)
- KDV dahil/hariç
- KDV oranı ve tutarı

**İlişkiler**

- Müvekkil (zorunlu)
- Dosya (opsiyonel, genel hareket için boş)
- İlgili alacak kaydı (tahsilat için hangi alacağa sayıldı)

**Detay Bilgileri**

- Açıklama
- Belge no (makbuz, fatura no)
- Vade tarihi (alacak için)
- Ödeme şekli (tahsilat için): Nakit / Havale / EFT / Kredi Kartı / Çek
- Banka/kasa bilgisi

#### 5.3 Alacak Yönetimi

**Alacak Kaydı Oluşturma**

- Sözleşme bazlı: Vekalet sözleşmesinden otomatik
- Manuel: Tek seferlik ücret girişi
- Periyodik: Aylık danışmanlık ücreti

**Alacak Durumları**

- **Bekliyor:** Vade gelmedi
- **Vadesi Geldi:** Ödeme bekleniyor
- **Kısmi Ödendi:** Bir kısmı tahsil edildi
- **Ödendi:** Tamamı tahsil edildi
- **Gecikti:** Vade geçti, ödenmedi
- **İptal:** Alacak iptal edildi

**Alacak Takibi**

- Vadesi yaklaşan alacaklar listesi
- Vadesi geçen alacaklar listesi
- Yaşlandırma raporu (aging): 0-30, 31-60, 61-90, 90+ gün

#### 5.4 Tahsilat Yönetimi

**Tahsilat Kaydı**

- Tahsilat tarihi
- Tahsilat tutarı
- Ödeme yöntemi
- Belge bilgisi (dekont, makbuz no)
- Açıklama

**Tahsilat Eşleştirme**

- Manuel eşleştirme: Kullanıcı hangi alacağa sayılacağını seçer
- Otomatik FIFO: En eski vadeli alacağa otomatik sayar
- Kısmi eşleştirme: Bir tahsilatı birden fazla alacağa bölme
- Avans tahsilat: Henüz alacak oluşmadan alınan ödeme

**Tahsilat Makbuzu**

- Otomatik makbuz numarası
- Makbuz yazdırma
- E-posta ile gönderme

#### 5.5 Gider/Masraf Yönetimi

**Gider Kaydı**

- Gider tarihi
- Gider kategorisi
- Tutar
- Ödeme şekli
- Belge bilgisi (fiş, fatura)
- Dosya bağlantısı

**Gider Kategorileri**

- Harç (dava açma, temyiz, icra)
- Bilirkişi ücreti
- Keşif masrafı
- Tebligat/posta/kargo
- Noter masrafı
- Yol masrafı
- Konaklama
- Tercüme ücreti
- Diğer

**Masraf Avansı**

- Müvekkilden masraf avansı talep etme
- Avansın giderlere mahsubu
- Kalan avans bakiyesi

#### 5.6 Bakiye Hesaplama

**Dosya Bakiyesi**

- Dosya Toplam Alacak = Σ(Dosyaya ait alacak kayıtları)
- Dosya Toplam Tahsilat = Σ(Dosyaya ait tahsilat kayıtları)
- Dosya Toplam Gider = Σ(Dosyaya ait gider kayıtları)
- Dosya Net Bakiye = Toplam Alacak - Toplam Tahsilat
- Dosya Gider Bakiyesi = Toplam Gider - Masraf Avansı Kullanımı

**Müvekkil Bakiyesi**

- Müvekkil Toplam Alacak = Σ(Tüm dosyaların alacakları) + Genel alacaklar
- Müvekkil Toplam Tahsilat = Σ(Tüm tahsilatlar)
- Müvekkil Net Bakiye = Toplam Alacak - Toplam Tahsilat

#### 5.7 Ödeme Planı

**Plan Oluşturma**

- Toplam alacak tutarı
- Taksit sayısı
- İlk taksit tarihi
- Taksit aralığı (haftalık, aylık)
- Taksit tutarları (eşit veya farklı)

**Plan Takibi**

- Taksit takvimi
- Ödenen taksitler
- Geciken taksitler
- Kalan taksit sayısı ve tutarı

#### 5.8 Mali Raporlar

**Cari Hesap Ekstresi**

- Müvekkil bazlı tüm hareketler
- Tarih aralığı filtreleme
- Açılış bakiyesi, hareketler, kapanış bakiyesi

**Alacak Yaşlandırma Raporu**

- 0-30 gün
- 31-60 gün
- 61-90 gün
- 90+ gün
- Müvekkil ve dosya bazlı kırılım

**Gelir Raporu**

- Dönemsel tahsilat toplamı
- Ücret türüne göre dağılım
- Avukat bazında dağılım

**Gider Raporu**

- Dönemsel gider toplamı
- Kategori bazında dağılım
- Dosya bazında dağılım

### MODÜL 6: RAPORLAMA VE DASHBOARD

> Detaylı rapor user story'leri `docs/05-user-stories/parca-6-raporlama.md` dosyasındadır.

#### 6.1 Ana Dashboard

**Özet Kartları**

- Aktif dosya sayısı
- Bu aydaki duruşma sayısı
- Güncel toplam alacak
- Bu ayki tahsilat
- Geciken görev sayısı
- Bugünkü görevler

**Hızlı Grafikler**

- Son 6 ay tahsilat trendi (çizgi grafik)
- Dosya türü dağılımı (pasta grafik)
- Dosya durumu dağılımı (bar grafik)

**Uyarı ve Bildirimler**

- Yarınki duruşmalar
- Bugün vadesi gelen alacaklar
- Geciken görevler
- Yaklaşan süreler

#### 6.2 Takvim Görünümü

**Takvim İçeriği**

- Duruşmalar
- Görevler (başlangıç ve bitiş tarihleri)
- Kritik tarihler (süre bitimi, vs.)
- Ödeme vadeleri

**Görünüm Seçenekleri**

- Günlük
- Haftalık
- Aylık
- Ajanda (liste)

**Filtreleme**

- Etkinlik türüne göre
- Avukata göre
- Dosyaya göre

#### 6.3 Dosya Raporları

**Dosya Listesi Raporu**

- Filtreleme: Tür, durum, tarih aralığı, avukat
- Sıralama: Tarih, numara, müvekkil
- Dışa aktarma: Excel, PDF

**Dosya Özet Raporu**

- Toplam dosya sayısı
- Türe göre dağılım
- Duruma göre dağılım
- Ortalama dosya yaşı
- Kapanma oranları

**Duruşma Raporu**

- Tarih aralığındaki duruşmalar
- Avukat bazında duruşma sayısı
- Mahkeme bazında dağılım
- Sonuçlanan/ertelenen oranı

#### 6.4 Mali Raporlar

**Alacak Raporu**

- Toplam alacak
- Vadesi geçen alacak
- Yaşlandırma analizi
- Müvekkil bazında kırılım

**Tahsilat Raporu**

- Dönemsel tahsilat
- Ödeme yöntemine göre dağılım
- Avukat bazında tahsilat
- Aylık tahsilat trendi

**Karlılık Raporu**

- Dosya bazında gelir-gider
- Müvekkil bazında karlılık
- Avukat bazında performans

#### 6.5 Performans Raporları

**Avukat Performansı**

- Dosya sayısı (aktif/kapanan)
- Duruşma sayısı
- Görev tamamlama oranı
- Tahsilat tutarı
- Dosya kazanma oranı

**Genel Performans**

- Dönemsel dosya açılış/kapanış
- Ortalama dosya çözüm süresi
- Tahsilat oranı
- Müvekkil memnuniyeti (opsiyonel)

#### 6.6 Rapor Özellikleri

**Filtreleme ve Parametreler**

- Tarih aralığı
- Avukat seçimi
- Dosya türü
- Müvekkil
- Durum

**Dışa Aktarma**

- Excel (.xlsx)
- PDF
- CSV

**Rapor Kaydetme**

- Sık kullanılan raporları kaydetme
- Kaydedilmiş rapor parametreleri
- Hızlı erişim

