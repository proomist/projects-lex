# Proomist LEX — Sistem Kullanım Kılavuzu

> Bu döküman Mali Takip **hariç** tüm modülleri kapsar.
> Mali Takip kılavuzu için uygulama içi Rehber sayfasındaki "Mali Takip" sekmesine bak.

---

## 1. Dashboard (Ana Sayfa)

Sisteme giriş yaptığında ilk gördüğün sayfa burası. Tüm büronun anlık durumunu bir bakışta gösterir.

### Üst Kartlar

| Kart | Ne Gösterir? |
|------|-------------|
| Aktif Dosyalar | Devam eden dava/dosya sayısı |
| Duruşma (Bu Ay) | Bu ay planlanan duruşma sayısı |
| Geciken Görev | Süresi geçmiş tamamlanmamış görevler |
| Bekleyen Alacak | Tüm müvekkillerden tahsil edilmemiş toplam |
| Bu Ay Kâr | Ücret Tahsilatı − Büro Gideri |
| Emanet Kasası | Müvekkillerden alınan emanetlerin, masraflar düşüldükten sonraki bakiyesi |

### Alt Bölümler

- **Yaklaşan Duruşmalar:** En yakın 5 duruşma. "Bugün" olanlar kırmızı etiketle vurgulanır.
- **Bana Atanan Görevler:** Sana atanmış aktif görevler. Checkbox'a tıklayarak doğrudan tamamlayabilirsin.

---

## 2. Müvekkiller

### Yeni Müvekkil Ekleme

1. **"+ Yeni Müvekkil"** butonuna tıkla
2. Müvekkil tipini seç:
   - **Bireysel** → Ad, Soyad, TC Kimlik No, Doğum Tarihi, Meslek
   - **Kurumsal** → Şirket Adı, Vergi No, Vergi Dairesi, Ticaret Sicil No, Yetkili Kişi
3. İletişim bilgilerini gir: Telefon, E-posta, Şehir, İlçe, Açık Adres
4. Varsayılan avukatı ata (opsiyonel)
5. **Kaydet**

> **TC Kimlik / Vergi No Doğrulama:** Sistem girdiğin numarayı anında doğrular. Yeşil tik = geçerli, kırmızı çarpı = hatalı.

### Listede Ne Görürsün?

| Sütun | Açıklama |
|-------|----------|
| Vekalet Borcu | Alacak − Ücret Tahsilatı. Kırmızı = borçlu, yeşil = tamamlandı |
| Emanet Bakiye | Emanet − Masraf. Yeşil = bakiye var, kırmızı = masraf aşıldı |

### Arama

Arama kutusuna isim, TC kimlik no, e-posta veya şirket adı yazarak filtreleyebilirsin.

---

## 3. Dosyalar ve Davalar

### Yeni Dosya Açma

1. **"+ Yeni Dosya"** butonuna tıkla
2. Formda:
   - **Müvekkil** → Hangi müvekkilin dosyası? (zorunlu)
   - **Müvekkil Pozisyonu** → Davacı / Davalı / Müdahil vb.
   - **Dosya Türü** → Hukuk / Ceza / İcra / İdare vb. (zorunlu)
   - **Mahkeme** → Dosyanın görüldüğü mahkeme
   - **Esas / Takip No** → Mahkemenin verdiği numara
   - **Konu** → Davanın özet konusu (zorunlu)
   - **Karşı Taraf** → Karşı taraf adı ve avukatı
3. **Kaydet**

### Dosya Durumları

- **Aktif** — Dava devam ediyor
- **Karar Aşaması** — Duruşmalar bitti, karar bekleniyor
- **İstinaf / Yargıtay** — Üst mahkemeye taşındı
- **Sonuçlandı** — Karar kesinleşti
- **Arşiv** — Dosya kapandı

### Filtreleme

- **Durum filtresi:** Aktif, Karar Aşaması, İstinaf/Yargıtay, Sonuçlandı, Arşiv
- **Tür filtresi:** Hukuk, Ceza, İcra, İdare vb.
- **Arama:** Dosya no, esas no, konu, karşı taraf, mahkeme veya müvekkil adı

---

## 4. Duruşma Takvimi

### Yeni Duruşma Ekleme

1. **"+ Yeni Duruşma"** butonuna tıkla
2. Formda:
   - **Dosya** → Hangi davaya ait? (zorunlu)
   - **Tarih & Saat** → Duruşma tarihi
   - **Salon** → Mahkeme salonu
   - **Duruşma Türü** → İlk Duruşma / Ara Duruşma / Keşif / Karar vb.
   - **Katılacak Avukat** → Kim gidecek?
   - **Durum** → Planlandı / Tamamlandı / Ertelendi / İptal
   - **Özet Notlar** → Duruşmada ne konuşuldu?
   - **Sonraki Duruşma Tarihi** → Varsa bir sonraki tarih
3. **Kaydet**

### Duruşma Durumları

- **Planlandı** — Henüz gerçekleşmedi
- **Tamamlandı** — Duruşma yapıldı
- **Ertelendi** — İleri tarihe alındı
- **İptal** — Duruşma iptal edildi

### Filtreleme

- Durum ve tarih bazında filtreleyebilirsin
- Dashboard'da en yakın 5 duruşma otomatik görünür

---

## 5. İş Listesi (Görevler)

### Yeni Görev Oluşturma

1. **"+ Yeni Görev"** butonuna tıkla
2. Formda:
   - **Başlık** → Görev adı (zorunlu)
   - **Açıklama** → Detaylı bilgi
   - **Görev Türü** → Dava İşlemi / İdari İş / Araştırma / Evrak vb.
   - **Öncelik** → Düşük / Normal / Yüksek / Acil
   - **Atanan Kişi** → Kim yapacak?
   - **Başlangıç / Bitiş Tarihi** → Zaman aralığı
   - **Müvekkil / Dosya** → İlgili müvekkil veya dava (opsiyonel)
3. **Kaydet**

### Görev Durumları

- **Bekliyor** — Henüz başlanmadı
- **Devam Ediyor** — Çalışılıyor
- **Tamamlandı** — Bitti
- **İptal** — İptal edildi

### Hızlı Tamamlama

Dashboard'daki "Bana Atanan Görevler" bölümünde checkbox'a tıklayarak görevi doğrudan tamamlayabilirsin.

### Filtreleme

- Durum, öncelik ve tarih bazında filtrele
- Arama kutusuyla başlık veya açıklama içinde ara

---

## 6. Evrak Yönetimi

### Belge Yükleme

1. **"+ Yeni Evrak"** butonuna tıkla
2. Formda:
   - **Dosya** → Belgeyi yükle (sürükle-bırak veya dosya seç)
   - **Dosya/Dava** → Hangi davaya bağlı? (opsiyonel)
   - **Müvekkil** → Hangi müvekkile ait? (opsiyonel)
   - **Kategori** → Dilekçe / Makbuz / Sözleşme / Mahkeme Kararı vb.
   - **Açıklama** → Belge hakkında not
3. **Yükle**

### Desteklenen Dosya Türleri

PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF ve diğer yaygın formatlar.

### Belge İndirme

Listedeki herhangi bir belgeye tıklayarak indirebilirsin.

### Filtreleme

- Dosya/dava ve müvekkil bazında filtrele
- Arama kutusuyla dosya adı veya açıklama içinde ara

---

## 7. Raporlar

### Mali Rapor

1. Menüden **Raporlar** sayfasına git
2. Üst kısımdaki tarih aralığını ayarla (başlangıç − bitiş)
3. **"Uygula"** butonuna tıkla

### Göreceğin Bilgiler

| Metrik | Açıklama |
|--------|----------|
| Ücret Tahsilatı | Vekalet ücreti ödemeleri |
| Emanet Girişi | Alınan emanetler |
| Müvekkil Masrafı | Müvekkil adına yapılan harcamalar |
| Büro Gideri | Genel büro masrafları |
| Bekleyen Alacak | Henüz tahsil edilmemiş |
| Kâr | Ücret Tahsilatı − Büro Gideri |

### Diğer Raporlar

- **Dava İstatistikleri** — Dosya durumlarına göre dağılım
- **Görev İstatistikleri** — Görev durumlarına göre dağılım
- **Kategori Analizi** — Her mali kategori bazında detaylı dağılım

---

## 8. Personel Yönetimi

> Bu bölüm sadece **Kurucu Ortak** ve **Sistem Yöneticisi** rolleri için geçerlidir.

### Yeni Kullanıcı Ekleme

1. Menüden **Personel** sayfasına git
2. **"+ Yeni Kullanıcı"** butonuna tıkla
3. Ad, Soyad, Kullanıcı Adı, E-posta, Şifre, Ünvan/Rol, Durum gir
4. **Kaydet**

Şifre girerken 4 çubuklu güç göstergesi göreceksin. 4 çubuk = güçlü şifre.

### Roller ve Yetkiler

| Rol | Erişim |
|-----|--------|
| Kurucu Ortak | Tüm sistem (personel, ayarlar, loglar dahil) |
| Ortak Avukat | Mali takip + raporlar dahil tüm operasyonel alanlar |
| Avukat / Stajyer | Dosyalar, müvekkiller, görevler, evraklar |
| Muhasebeci / Sekreter | Mali takip + temel operasyonel alanlar |

---

## 9. Sistem Tanımları

> Bu bölüm sadece **Kurucu Ortak** ve **Sistem Yöneticisi** rolleri için geçerlidir.

1. Profil menüsünden **Tanımlamalar** sayfasına git
2. Sol panelden bir **grup** seç (Dosya Türleri, Ödeme Yöntemleri vb.)
3. Sağ panelde o grubun değerlerini gör
4. **"+ Yeni Değer"** ile yeni tanım ekle

> **Dikkat:** "Sistem" etiketli değerler korumalıdır — değer alanı değiştirilemez ve silinemez. Sadece etiket ve sıralama düzenlenebilir.

---

## 10. Genel İpuçları

### Sayfalama
Listelerde 20 kayıt/sayfa gösterilir. Alt kısımdaki **Önceki / Sonraki** butonlarıyla gezinebilirsin. Toplam kayıt sayısı toolbar'da badge olarak görünür.

### Arama
Arama kutusu olan sayfalarda yazmaya başladığında otomatik filtreleme çalışır. Birden fazla alana aynı anda bakar (isim, TC no, e-posta vb.).

### Hızlı Arama (Ctrl+K)
Üst menüdeki arama kutusuna tıkla veya **Ctrl+K** kısayolunu kullan. Müvekkil, dosya ve görev arasında anlık arama yapar.

### Silme İşlemi
Sistemde hiçbir veri kalıcı olarak silinmez. "Sil" butonuna tıkladığında kayıt **arşivlenir**. Yanlışlıkla silinen veriler geri alınabilir.

### Durum Renkleri

| Renk | Anlam |
|------|-------|
| Yeşil | Aktif / Tamamlandı / Bakiye var |
| Kırmızı | Gecikmiş / İptal / Borç var |
| Mavi | Planlandı / Bekliyor |
| Turuncu | Uyarı / Ertelendi / Kısmi |

### Profil Menüsü
Sağ üst köşedeki ismine tıklayarak erişebilirsin: Kullanım Rehberi, Tanımlamalar, Sistem Ayarları, İşlem Logları, Güvenli Çıkış.

### Mobil Kullanım
Sistem mobil cihazlarla tam uyumludur. Sol üstteki hamburger menüsüne tıklayarak tüm sayfalara erişebilirsin.

### Bildirimler
Sağ üstteki çan ikonuna tıklayarak bildirimlerini görebilirsin. Yeni duruşma, geciken görev gibi önemli olaylar otomatik bildirim oluşturur.
