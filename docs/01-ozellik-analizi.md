# Avukat Ofis Yönetim Sistemi

---

## BÖLÜM 1: ÖZELLİK ANALİZİ

> **Referans:** Kullanılan tüm görev/mali terimleri ve standart iş kuralları için `docs/00-terminoloji-ve-kurallar.md` dosyasındaki sözlük esas alınmalıdır.

### Genel Çerçeve

Sistem üç ana eksende şekillenir: dava/dosya yönetimi, mali takip (alacak-borç) ve müvekkil ilişkileri. Bunlar birbirine sıkı bağlı; bir müvekkilin birden fazla dosyası, her dosyanın kendi mali hareketi, her hareketin de müvekkil bakiyesine etkisi var.

### Ana Modüller ve Kısa Açıklamaları

1. **Müvekkil Yönetimi**
   Gerçek/tüzel kişi kartları, iletişim bilgileri, dosya geçmişi, genel bakiye durumu
2. **Dava/Dosya Yönetimi**
   Dosya açma, duruşma/tarih takibi, belge arşivi, dosya durumu (aktif/kapandı/beklemede)
3. **Mali Takip (Alacak-Borç)**
   Avukatlık ücreti, masraflar, tahsilatlar, dosya bazlı ve müvekkil bazlı bakiye
4. **Görev ve Hatırlatıcılar**
   Duruşma tarihleri, ihtarname süreleri, ödeme vadeleri, kritik tarih uyarıları
5. **Raporlama**
   Dosya durumu özeti, mali durum, vadesi geçen alacaklar, performans metrikleri

### Modüller Arası İlişkiler

```
Müvekkil (1) ──────┬──────> (N) Dosya/Dava
                   │
                   └──────> (N) Mali Hareket (genel)

Dosya (1) ─────────┬──────> (N) Duruşma/Tarih
                   │
                   ├──────> (N) Belge/Evrak
                   │
                   └──────> (N) Mali Hareket (dosya bazlı)

Mali Hareket ──────┬──────> Müvekkil Bakiyesi (toplam)
                   │
                   └──────> Dosya Bakiyesi (dosya bazlı)
```

### Detaylı Özellik Açılımları

#### 1. Müvekkil Yönetimi

**Müvekkil Kartı**

- Gerçek kişi: TC, ad-soyad, doğum tarihi, meslek
- Tüzel kişi: vergi no, unvan, yetkili kişi, ticaret sicil
- Çoklu iletişim: telefon, e-posta, adres (ev/iş/diğer)
- Müvekkil notu: özel durumlar, hassasiyetler

**Müvekkil Durumu**

- Aktif / Pasif / Potansiyel müvekkil ayrımı
- Kara liste işaretleme (ödeme problemi yaşanan)

**Bağlantılı Kişiler**

- Bir müvekkilin şirket ortakları, aile üyeleri
- Karşı taraf bilgisi (ileride çıkar çatışması kontrolü için)

#### 2. Dava/Dosya Yönetimi

**Dosya Türleri**

- Dava dosyası (hukuk, ceza, idare, iş)
- İcra dosyası
- Danışmanlık dosyası (dava olmayan işler)
- Arabuluculuk dosyası

**Dosya Kartı Bilgileri**

- Büro dosya numarası (kendi sistemin)
- Mahkeme/icra dairesi ve esas no
- Dosya açılış tarihi
- Müvekkil pozisyonu: davacı/davalı/alacaklı/borçlu
- Karşı taraf bilgileri
- Dava konusu ve özeti
- Talep edilen/beklenen tutar

**Dosya Durumu**

- Aktif / Beklemede / Karar aşaması / Kapandı / Arşiv
- Kapanış şekli: kazanıldı, kaybedildi, sulh, feragat, düşme

**Duruşma ve Tarih Takibi**

- Duruşma tarihi, saati, salonu
- Duruşma sonucu notu (her duruşma için)
- Gelecek duruşma tarihi
- Kritik tarihler: temyiz süresi, itiraz süresi, zamanaşımı

**Belge Yönetimi**

- Dosyaya belge ekleme (dilekçe, karar, sözleşme, delil)
- Belge kategorilendirme
- Versiyon takibi (aynı dilekçenin taslakları)
- OCR ile aranabilirlik (opsiyonel)

#### 3. Mali Takip (Alacak-Borç)

**Hareket Türleri**

Alacak (müvekkilden beklenen):

- Başlangıç ücreti / vekalet ücreti
- Duruşma ücreti
- Masraf avansı talebi
- Yol/konaklama/harç/posta masrafları

Tahsilat (müvekkilden gelen):

- Nakit / havale / eft / kredi kartı
- Kısmi ödeme desteği

Gider (dosya için yapılan):

- Harç ödemeleri
- Bilirkişi ücreti
- Keşif masrafı
- Posta/tebligat

**Bakiye Hesaplama**

Dosya bazlı bakiye:

- Dosya Alacağı = Kesilen ücretler + Masraflar
- Dosya Tahsilatı = O dosyaya atanan ödemeler
- Dosya Bakiyesi = Alacak - Tahsilat

Müvekkil genel bakiyesi:

- Toplam Bakiye = Σ(Tüm dosya bakiyeleri) + Genel hareketler

**Ödeme Planı**

- Taksitli ödeme tanımlama
- Taksit takibi ve gecikme hesabı

**Tahsilat Eşleştirme**

- Gelen ödemeyi hangi dosyaya/alacağa saymak istediğini seçme
- Otomatik FIFO (en eski alacağa say) seçeneği

#### 4. Görev ve Hatırlatıcılar

**Otomatik Hatırlatıcılar**

- Duruşmadan X gün önce
- Süre bitiminden X gün önce
- Ödeme vadesinden X gün önce/sonra

**Manuel Görevler**

- Dosyaya veya müvekkile bağlı görev oluşturma
- Atama (birden fazla avukat varsa)
- Öncelik ve durum takibi

**Takvim Görünümü**

- Günlük/haftalık/aylık duruşma ve görev takvimi
- Çakışma uyarısı (aynı saatte iki duruşma)

#### 5. Raporlama

**Dosya Raporları**

- Aktif dosya listesi (mahkeme, tür, durum bazlı filtreleme)
- Kapanan dosya özeti (kazanma/kaybetme oranı)
- Dosya yaşı analizi (ne kadar süredir açık)

**Mali Raporlar**

- Toplam alacak / tahsilat / bakiye özeti
- Vadesi geçen alacaklar (aging report: 30/60/90 gün)
- Dönemsel gelir raporu
- Masraf analizi (dosya başı ortalama masraf)

**Müvekkil Raporları**

- Müvekkil bazlı dosya ve bakiye özeti
- Tahsilat geçmişi
- Risk skoru (ödeme alışkanlığına göre)

### Ek Özellikler (İsteğe Bağlı)

**Çoklu Avukat Desteği**

- Kullanıcı rolleri: yönetici avukat, avukat, sekreter, stajyer
- Dosya atama ve yetki sınırlama

**İletişim Kaydı**

- Müvekille yapılan görüşme notları
- Telefon/e-posta/yüz yüze görüşme logu

**Sözleşme Şablonları**

- Vekalet sözleşmesi şablonu
- Otomatik alan doldurma (müvekkil adı, tarih vb.)

#### Responsive Tasarım Stratejisi (Mobil Uygulama Öncesi Köprü)

- Tüm ekranlar **mobile-first** tasarlanır; 360px, 768px, 1024px ve 1440px kırılımlarında component davranışı tanımlanır.
- Dashboard, takvim, görev ve mali tablolar için aynı design system kullanılır; komponentler React Native'e taşınırken stil token'ları yeniden kullanılır.
- Form yoğun ekranlarda bölmeli düzen (sol: özet kart, sağ: form) yalnızca ≥1024px genişlikte gösterilir; altındaki genişliklerde tek kolon + accordion yaklaşımı zorunludur.
- Kritik aksiyon butonları (kaydet, hatırlatıcı ekle, rapor dışa aktar) 48px dokunmatik hedef standardına göre yerleştirilir; mobil kullanımda sağ altta floating action button varyantı desteklenir.
- Grafik ve tablolar aynı veri kaynağını kullanır; tablolar <768px'te kart liste görünümüne otomatik geçer.
- Native uygulama geliştirilene kadar PWA özellikleri (ikon, splash, offline cache'li takvim ve görev listesi) aktif edilir; böylece kullanıcılar mobilde web sürümünü kısa yol olarak kullanabilir.

**Mobil Erişim**

- Duruşma takvimi ve dosya özeti mobilde görüntüleme
- Hızlı not ekleme

### Kritik Tasarım Kararları

| Karar Noktası | Önerilen Yaklaşım |
|---|---|
| Dosya numaralama | Yıl-Sıra formatı (2025-0001) |
| Bakiye görünümü | Hem dosya bazlı hem müvekkil toplamı aynı ekranda |
| Tarih uyarıları | Dashboard'da "bugün/bu hafta" widget'ı |
| Belge depolama | Dosya sistemi + DB'de metadata |
| Silme politikası | Soft delete (arşivle, silme) |

### Ek Gereksinimler

- İş listesi oluşturmada işin başlangıç bitiş tarihi ve görevlisi eklenebilsin.
- Kullanıcı rol bazlı yetki tanımlanabilsin.

### Kullanılabilirlik Notları

- **Görev Oluşturma Akışı:** Çok sayıda alan gerektiren görev formu iki bölümde tasarlanmalıdır: "Temel Bilgiler" (başlık, tarih, görevli) ve "Gelişmiş Ayarlar" (alt görev, checklist, bağımlılık). Böylece yoğun alanlar yalnızca ihtiyaç halinde açılır.
- **Mali Kayıt Formları:** Tüm mali girişlerde ofis varsayılanları (para birimi, KDV oranı, varsayılan belge numara formatı) otomatik doldurulmalı; kullanıcı isterse değiştirmelidir. Bu yaklaşım sahadaki hızlı veri girişini destekler.
- **Raporlama Ekranları:** 5.000+ satır üretmesi muhtemel raporlarda kullanıcıya "arka planda hazırlanacak" mesajı gösterilmeli ve sonuç hazır olduğunda bildirim gelmelidir. Böylece ekran kilitlenmez.
