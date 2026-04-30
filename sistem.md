# Hukuk Bürosu Yönetim Sistemi — Genel Kullanım Kılavuzu

Bu kılavuz, sistemin tüm modüllerini adım adım anlatır. Mali Takip detayları için ayrı kılavuza bakabilirsin.

---

## 1. Dashboard (Ana Sayfa)

Sisteme giriş yaptığında ilk gördüğün sayfa burası. Tüm büronun anlık durumunu bir bakışta gösterir.

### Üst Kartlar

| Kart | Ne Gösterir? |
|------|-------------|
| **Aktif Dosyalar** | Şu an devam eden dava/dosya sayısı |
| **Duruşma (Bu Ay)** | Bu ay planlanan duruşma sayısı |
| **Geciken Görev** | Süresi geçmiş ama tamamlanmamış görevler |
| **Bekleyen Alacak** | Tüm müvekkillerden tahsil edilmemiş toplam alacak |
| **Bu Ay Kâr** | Bu ayki ücret tahsilatı − büro gideri |
| **Emanet Kasası** | Müvekkillerden alınan emanetlerin masraflar düşüldükten sonraki bakiyesi |

### Alt Bölümler

- **Yaklaşan Duruşmalar**: Bugünden itibaren en yakın 5 duruşma. "Bugün" olanlar kırmızı etiketle vurgulanır.
- **Bana Atanan Görevler**: Sana atanmış aktif görevler. Checkbox'a tıklayarak doğrudan tamamlandı olarak işaretleyebilirsin.

---

## 2. Müvekkiller

Menüden **Müvekkiller** sayfasına git.

### Yeni Müvekkil Ekleme

1. **"+ Yeni Müvekkil"** butonuna tıkla
2. Önce müvekkil tipini seç:
   - **Bireysel** → Ad, Soyad, TC Kimlik No, Doğum Tarihi, Meslek
   - **Kurumsal** → Şirket Adı, Vergi No, Vergi Dairesi, Ticaret Sicil No, Yetkili Kişi
3. İletişim bilgilerini gir: Telefon, E-posta, Şehir, İlçe, Açık Adres
4. Varsayılan avukatı ata (opsiyonel)
5. **Kaydet**

> **TC Kimlik / Vergi No Doğrulama**: Sistem girdiğin numarayı anında doğrular. Yeşil tik görürsen geçerli, kırmızı çarpı görürsen hatalı.

### Müvekkil Listesinde Ne Görürsün?

| Sütun | Açıklama |
|-------|----------|
| **Müvekkil** | Ad soyad veya şirket adı + müvekkil kodu |
| **Tip / Kimlik** | Bireysel/Kurumsal + TC/VKN |
| **İletişim** | Telefon + e-posta |
| **Vekalet Borcu** | Alacak − Ücret Tahsilatı (kırmızı = borçlu, yeşil = tamam) |
| **Emanet Bakiye** | Emanet − Masraf (yeşil = bakiye var, kırmızı = aşım) |
| **Durum** | Aktif / Pasif |

### Arama

Üst kısımdaki arama kutusuna isim, TC kimlik no, e-posta veya şirket adı yazarak filtreleyebilirsin.

---

## 3. Dosyalar ve Davalar

Menüden **Dosyalar & Davalar** sayfasına git.

### Yeni Dosya Açma

1. **"+ Yeni Dosya"** butonuna tıkla
2. Formda:
   - **Müvekkil** → Hangi müvekkilin dosyası? (zorunlu)
   - **Müvekkil Pozisyonu** → Davacı / Davalı / Müdahil vb.
   - **Dosya Türü** → Hukuk / Ceza / İcra / İdare vb. (zorunlu)
   - **Mahkeme** → Dosyanın görüldüğü mahkeme
   - **Esas / Takip No** → Mahkemenin verdiği numara
   - **Konu** → Davanın özet konusu (zorunlu)
   - **Açılış Tarihi** → Davanın açıldığı tarih
   - **Karşı Taraf** → Karşı taraf adı
   - **Karşı Taraf Avukatı** → Karşı vekil
3. **Kaydet**

### Dosya Durumları

| Durum | Renk | Anlam |
|-------|------|-------|
| Aktif | Yeşil | Dava devam ediyor |
| Karar Aşaması | Amber | Karar bekleniyor |
| İstinaf/Yargıtay | İndigo | Üst mahkemede |
| Sonuçlandı | Mavi | Dava sona erdi |
| Arşiv | Gri | Arşivlenmiş dosya |

### Filtreleme

- **Durum filtresi**: Aktif, Karar Aşaması, İstinaf/Yargıtay vb.
- **Tür filtresi**: Hukuk, Ceza, İcra vb.
- **Arama**: Dosya no, esas no, konu, karşı taraf, mahkeme veya müvekkil adıyla ara

---

## 4. Duruşma Takvimi

Menüden **Duruşma Takvimi** sayfasına git.

### Yeni Duruşma Ekleme

1. **"+ Yeni Duruşma"** butonuna tıkla
2. Formda:
   - **Dosya** → Hangi dosyanın duruşması? (sadece aktif dosyalar listelenir)
   - **Tarih** → Duruşma tarihi (zorunlu)
   - **Saat** → Duruşma saati (zorunlu)
   - **Açıklama / Not** → Duruşmayla ilgili notlar
   - **Durum** → Planlandı / Tamamlandı / Ertelendi / İptal
3. **Kaydet**

### Duruşma Listesi

- Geçmiş tarihli duruşmalar **kırmızı** ile vurgulanır
- Durum renkleri: Mavi = Planlandı, Yeşil = Tamamlandı, Amber = Ertelendi, Kırmızı = İptal
- Tarih ve durum filtreleriyle daraltabilirsin

> **İpucu**: Dashboard'daki "Yaklaşan Duruşmalar" bölümü sadece planlanmış gelecek duruşmaları gösterir.

---

## 5. İş Listesi (Görevler)

Menüden **İş Listesi** sayfasına git.

### Yeni Görev Oluşturma

1. **"+ Yeni Görev"** butonuna tıkla
2. Formda:
   - **Görev Başlığı** → Ne yapılacak? (zorunlu)
   - **İlgili Dosya** → Varsa hangi dosyayla ilgili?
   - **Görev Tipi** → Dilekçe / Toplantı / Araştırma / İcra İşlemi / Mali İşlem / Diğer
   - **Sorumlu** → Kim yapacak? (zorunlu)
   - **Bitiş Tarihi** → Son teslim tarihi
   - **Öncelik** → Acil / Yüksek / Normal / Düşük
   - **Durum** → Bekliyor / Devam Ediyor / Tamamlandı / Beklemede
   - **Açıklama** → Detaylı bilgi
3. **Kaydet**

### Hızlı Tamamlama

Görev listesinde her satırın solundaki **checkbox**'a tıklayarak görevi anında "Tamamlandı" yapabilirsin. Tekrar tıklarsan "Devam Ediyor"a geri döner.

### Renkler ve Uyarılar

- **Kırmızı tarih**: Bitiş tarihi geçmiş, görev gecikmiş
- **Kırmızı öncelik etiketi**: Acil veya Yüksek
- **Üstü çizili metin + yeşil tik**: Tamamlanmış görev

### Filtreleme

- **Durum**: Bekliyor / Devam Ediyor / Tamamlandı / Beklemede
- **Öncelik**: Acil / Yüksek / Normal / Düşük

---

## 6. Evrak Yönetimi

Menüden **Evraklar** sayfasına git.

### Belge Yükleme

1. **"+ Evrak Yükle"** butonuna tıkla
2. Dosyayı yükle:
   - Kutucuğa **sürükle-bırak** yap veya tıklayıp bilgisayarından seç
   - Desteklenen formatlar: PDF, Word, Excel, JPG, PNG, TIFF
   - Maksimum boyut: **10 MB**
3. Formu doldur:
   - **Evrak Başlığı** → Belgenin adı (zorunlu)
   - **Evrak Türü** → Dilekçe / Sözleşme / Karar / Bilirkişi Raporu vb. (zorunlu)
   - **İlgili Müvekkil** → Kime ait? (opsiyonel)
   - **İlgili Dosya** → Hangi dosyaya bağlı? (müvekkil seçildikten sonra aktif olur)
   - **Notlar** → Ek bilgi
4. **Kaydet**

### Evrak Listesinde Ne Görürsün?

- Dosya ikonu (PDF kırmızı, Word mavi, Excel yeşil, Resim mor)
- Evrak başlığı + orijinal dosya adı
- Evrak türü etiketi
- Bağlı müvekkil + dosya bilgisi
- Dosya boyutu
- Yükleyen kişi ve tarih
- **İndir** butonu ile doğrudan indirebilirsin

### Filtreleme

Evrak Türü dropdown'undan belge tipine göre filtrele.

---

## 7. Raporlar

Menüden **Raporlar** sayfasına git.

### Mali Rapor

1. Üst kısımdaki tarih aralığını ayarla (başlangıç − bitiş)
2. **"Uygula"** butonuna tıkla
3. Şu bilgileri göreceksin:
   - **Ücret Tahsilatı** — Vekalet ücreti ödemeleri
   - **Emanet Girişi** — Müvekkillerden alınan emanetler
   - **Müvekkil Masrafı** — Müvekkiller adına yapılan harcamalar
   - **Büro Gideri** — Genel büro masrafları
   - **Bekleyen Alacak** — Henüz tahsil edilmemiş
   - **Kâr** — Ücret Tahsilatı − Büro Gideri
   - **Emanet Kasası** — Emanet − Masraf bakiyesi

### Dava İstatistikleri

- Dosya durumlarına göre dağılım grafiği (Aktif, Karar Aşaması, Sonuçlandı vb.)

### Görev İstatistikleri

- Görev durumlarına göre dağılım (Bekliyor, Devam Ediyor, Tamamlandı vb.)

### Kategori Analizi

- Her mali hareket kategorisi bazında detaylı dağılım (5 grup: Ücret Tahsilatları, Emanet Girişleri, Müvekkil Masrafları, Büro Giderleri, Alacaklar)

---

## 8. Personel Yönetimi

> Bu bölüm sadece **Kurucu Ortak** ve **Sistem Yöneticisi** rolleri için geçerlidir.

Profil menüsünden **Personel** sayfasına git.

### Yeni Personel Ekleme

1. **"+ Yeni Kullanıcı"** butonuna tıkla
2. Formda:
   - **Ad** ve **Soyad** (zorunlu)
   - **Kullanıcı Adı** → Giriş için kullanılacak (zorunlu)
   - **E-posta** (zorunlu)
   - **Şifre** → En az 6 karakter (zorunlu, düzenlemede opsiyonel)
   - **Ünvan / Rol** → Kurucu Ortak / Ortak Avukat / Avukat / Stajyer vb.
   - **Durum** → Aktif / Askıda / Pasif
3. **Kaydet**

### Şifre Güvenliği

Form'da şifre girerken 4 çubuklu güç göstergesi göreceksin:
- 1 çubuk = Çok Zayıf
- 2 çubuk = Zayıf
- 3 çubuk = Orta
- 4 çubuk = Güçlü

### Roller ve Yetkiler

| Rol | Erişim |
|-----|--------|
| **Kurucu Ortak** | Tüm sistem (personel, ayarlar, loglar dahil) |
| **Ortak Avukat** | Mali takip + raporlar dahil tüm operasyonel alanlar |
| **Avukat / Stajyer** | Dosyalar, müvekkiller, görevler, evraklar |
| **Muhasebeci / Sekreter** | Mali takip + temel operasyonel alanlar |

---

## 9. Sistem Tanımları

> Bu bölüm sadece **Kurucu Ortak** ve **Sistem Yöneticisi** rolleri için geçerlidir.

Profil menüsünden **Tanımlamalar** sayfasına git.

### Nasıl Çalışır?

Sol panelden bir **grup** seç, sağ panelde o grubun değerlerini gör ve düzenle.

### Mevcut Gruplar

| Grup | Kullanıldığı Yer |
|------|-----------------|
| Personel Ünvanları | Personel → Rol/Ünvan |
| Dosya Türleri | Dosyalar → Dosya Tipi |
| Müvekkil Pozisyonları | Dosyalar → Müvekkil Rolü |
| Kapanış Türleri | Dosyalar → Kapanış Sebebi |
| Duruşma Türleri | Duruşmalar → Tip |
| Ödeme Yöntemleri | Mali Takip → Ödeme Şekli |
| İletişim Türleri | Müvekkiller → İletişim |
| Görev Öncelikleri | İş Listesi → Öncelik |
| Evrak Türleri | Evraklar → Belge Tipi |
| Mali Kategoriler | Mali Takip → Kategori |

### Yeni Değer Ekleme

1. Sol panelden grubu seç
2. **"+ Yeni Değer"** butonuna tıkla
3. Formda:
   - **Değer** → Veritabanında saklanan teknik değer
   - **Etiket** → Kullanıcının gördüğü isim
   - **Sıralama** → Listeleme sırası
   - **Durum** → Aktif / Pasif
4. **Kaydet**

> **Dikkat**: "Sistem" etiketli değerler korumalıdır — değer alanı değiştirilemez ve silinemez. Sadece etiket ve sıralama düzenlenebilir. Kendi eklediğin "Özel" değerleri serbestçe düzenleyebilirsin.

---

## 10. Genel İpuçları

### Sayfalama

Listelerde 20 kayıt/sayfa gösterilir. Alt kısımdaki **Önceki / Sonraki** butonlarıyla sayfalar arasında gezinebilirsin. Toplam kayıt sayısı üst kısımda badge olarak görünür.

### Arama

Arama kutusu olan sayfalarda yazmaya başladığında otomatik filtreleme çalışır. Birden fazla alana aynı anda bakar (örn. müvekkil adı, TC no, e-posta).

### Silme İşlemi

Sistemde hiçbir veri kalıcı olarak silinmez. "Sil" butonuna tıkladığında kayıt **arşivlenir** (soft delete). Bu sayede yanlışlıkla silinen veriler geri alınabilir.

### Durum Renkleri

Tüm sayfalarda tutarlı renk kodlaması kullanılır:
- **Yeşil** → Aktif, Tamamlandı, Ödendi, Bakiye var
- **Kırmızı** → Gecikmiş, İptal, Borç var, Masraf aşımı
- **Mavi** → Planlandı, Bekliyor, Bilgi
- **Amber** → Uyarı, Ertelendi, Kısmi

### Profil Menüsü

Sağ üst köşedeki ismine tıklayarak:
- **Kullanım Rehberi** → Bu döküman
- **Tanımlamalar** → Sistem tanım değerlerini yönet (yetkili roller)
- **Sistem Ayarları** → Genel ayarlar (yetkili roller)
- **İşlem Logları** → Sistem aktivitelerini izle (Sistem Yöneticisi)
- **Hata İzleme** → Hata kayıtlarını gör (Sistem Yöneticisi)
- **Güvenli Çıkış** → Oturumu kapat

### Mobil Kullanım

Sistem mobil cihazlarla tam uyumludur. Sol üstteki hamburger menüsüne tıklayarak tüm sayfalara erişebilirsin.





senaryo
müvekkil senet getirdi, icraya verilecek. örneğin 100 bin tl senet tutarı.
bu senedin icraya verilmesi esnasında bir takım masraflar yapılıyor, bu masrafları müvekkil üstleniyor, daha sonra icra dosyası tamamlandığında toplam dosya tutarı ve üzerine yapılan masraflar ekleniyor ve karşı taraf vekalet ücreti ve müvekkil vekalet ücreti ile birlikte nihai tutar ortaya çıkıyor.
nihai tutar avukatın hesabına yatırılıyor.
avukat bu tutardan, karşı taraf vekalet ücretini, kendi vekalet ücretini hesaplıyor, kesinti yapılıyor ve kalan tutarı müvekkilin hesabına ödüyor.
100 bin tl örneğinden gidersek; 10 bin karşı vekalet ücreti, 3 bin dosya açılış masrafı, 2 bin baro pul masrafı, 5 bin duruşma masrafı, 10 bin müvekkil vekalet ücreti çıktı diyelim.
müvekkil ile anlaşma esnasında, müvekkilden 3 bin tl dosya ücreti, 7 bin tl diğer masraflar diye belirtiliyor, müvekkilde bu ücreti avukata teslim ediyor, avukat bu ücretleri müvekkil adına icra dairesine yatırıyor. karşı taraf vekalet ücreti ve kendi vekalet ücretini ilk etapta hesaba katmamış oluyor.
icra dairesi senedin tahsilatını yaptıktan sonra, avukat, senet sahibi müvekkilinin hesabına,senet tutarı (100 bin), dosya ücreti 3 bin, 7 bin diğer ücretler toplam 110 bin aktarıyor. icra dairesinden gelen 130 binin 110 bini müvekkile aktarılıyor.
birde vergilendirme uzmanları aşağıdaki gibi yazmışlar, bizim konumuzu tam anlatmıyor daha çok mali müşavirlerin konusu, sen benim senaryomu anladın mı? belki yanlış anlatmış olabilirim, düzeltebiliriz.

Avukatların icra işlemlerindeki muhasebe süreçleri, tahsil edilen vekalet ücreti ve masrafların yasal belgelendirilmesine (Serbest Meslek Makbuzu) dayanır. İcra dairesinden alınan vekalet ücreti KDV dahil kabul edilip stopajlı/stopajsız ayrımıyla faturalandırılmalı, müvekkil adına yapılan tahsilatlar ise "Müvekkil Cari Hesapları"nda kayıt altına alınarak defter beyan sistemine işlenmelidir. 

İcra İşlemlerinde Temel Muhasebe Adımları:
Serbest Meslek Makbuzu (SMM) Düzenlenmesi: İcra dosyasından tahsil edilen avukatlık ücreti için makbuz kesilmesi zorunludur.
Vekalet Ücreti ve KDV: İcra emrindeki vekalet ücreti KDV dahil kabul edilir; tevkifat (kesinti) varsa netleştirilerek makbuz düzenlenir.
Stopaj Oranı: Karşı taraf vergi mükellefi ise vekalet ücreti üzerinden %20 (serbest meslek kazancı) gelir vergisi tevkifatı yapılır.
Müvekkil Parası ve Masraf İadeleri: Tahsil edilen asıl alacak ve geri alınan masraflar, avukatın şahsi geliri olmayıp müvekkilin parasıdır. Bu tutarlar "336 Müvekkil Cari Hesapları" veya benzeri alt hesaplarda izlenir, avukatın gelirine dahil edilmez.




3000 tl masrafı müşteri karışladı, ek masrafları müşteri adına ben icra dairesine yatıyorum.
sisteme gireceksin avans olarak.
diyelimki 145 bin yattı icra dairesi tarafından.
145 bin icra dosyasından geldi diye sisteme girilecek. 
bireysel hesap yaptın avukatlık vekalet ücretini de hesaplayıp önceden yapılan masrafları da düştün 25 bini kendine gelir olarak yazacaksın, kalan bakiyede firmanın alacağı yani 100 bin. 20 bin kalan rakam da müvekkilin masraf olarak verdiği rakamlar, bu 100 bin tl de firmaya hesaptan gönderilecek. sonuç olarak bu dosyadan 0 tl kalması gerekir.

