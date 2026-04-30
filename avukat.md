# Hukuk Bürosu Yönetim Sistemi — Mali Takip Kullanım Kılavuzu

Bu kılavuz, mali takip sistemini nasıl kullanacağını adım adım anlatır. Her senaryo gerçek büro ihtiyaçlarına göre hazırlandı.

---

## İçindekiler

1. [Temel Kavramlar](#temel-kavramlar)
2. [Alacak Kaydı Oluşturma](#1-alacak-kaydı-oluşturma)
3. [Ücret Tahsilatı Kaydetme](#2-ücret-tahsilatı-kaydetme)
4. [Emanet (Avans) Alma](#3-emanet-avans-alma)
5. [Müvekkil Masrafı Girme](#4-müvekkil-masrafı-girme)
6. [Büro Gideri Girme](#5-büro-gideri-girme)
7. [Müvekkil Bakiyelerini Takip Etme](#6-müvekkil-bakiyelerini-takip-etme)
8. [Raporlar ve Kâr Takibi](#7-raporlar-ve-kâr-takibi)
9. [Dashboard Kartları](#8-dashboard-kartları)
10. [Gerçek Hayat Senaryoları](#gerçek-hayat-senaryoları)

---

## Temel Kavramlar

Sistemde 5 farklı mali hareket tipi var:

| Tip | Alt Tip | Ne İçin? | Örnek |
|-----|---------|----------|-------|
| **Alacak** | — | Müvekkilden alacağın vekalet ücreti | 50.000 TL vekalet ücreti faturası |
| **Tahsilat** | Ücret | Vekalet ücretinin tahsil edilen kısmı | Müvekkil 20.000 TL ödedi |
| **Tahsilat** | Emanet | Müvekkilden alınan avans/depozito | Masraflar için 5.000 TL avans alındı |
| **Gider** | Müvekkil Masrafı | Müvekkil adına yapılan harcama | Harç, bilirkişi, tebligat ücreti |
| **Gider** | Büro Gideri | Büronun genel gideri | Kira, personel maaşı, kırtasiye |

### Formüller

- **Vekalet Borcu** = Alacak − Ücret Tahsilatı *(Müvekkil sana ne kadar borçlu?)*
- **Emanet Bakiye** = Emanet − Müvekkil Masrafı *(Müvekkilin emanet parası ne kadar kaldı?)*
- **Büro Kârı** = Ücret Tahsilatı − Büro Gideri *(Bu ay ne kazandın?)*

---

## 1. Alacak Kaydı Oluşturma

> Müvekkilinle vekalet ücreti konusunda anlaştın ve faturalandıracaksın.

1. Menüden **Mali Takip** sayfasına git
2. Sağ üstteki **"+ Yeni Kayıt"** butonuna tıkla
3. Açılan formda:
   - **İşlem Tipi** → "Alacak" seç
   - **Müvekkil** → İlgili müvekkili seç
   - **Dosya** → (varsa) ilgili davayı seç
   - **Tarih** → Fatura/anlaşma tarihi
   - **Tutar** → Vekalet ücreti tutarını gir (örn. 50.000)
   - **KDV** → Gerekiyorsa KDV oranını seç
   - **Kategori** → "Vekalet Ücreti" veya uygun kategoriyi seç
   - **Vade Tarihi** → Ödeme beklenen tarih
4. **Kaydet** butonuna tıkla

> **Not:** Alacak kaydında alt tip seçimi yoktur. Bu kayıt otomatik olarak müvekkilin "Vekalet Borcu" hesabına yansır.

---

## 2. Ücret Tahsilatı Kaydetme

> Müvekkil vekalet ücretinin bir kısmını veya tamamını ödedi.

1. **Mali Takip** sayfasına git → **"+ Yeni Kayıt"**
2. Formda:
   - **İşlem Tipi** → "Tahsilat" seç
   - **Alt Tip** → "Ücret Tahsilatı" seç *(otomatik gelir)*
   - **Müvekkil** → Ödeme yapan müvekkili seç
   - **Tutar** → Tahsil edilen tutar
   - **Ödeme Yöntemi** → Nakit / Havale / EFT vb.
3. **Kaydet**

> **Önemli:** Ücret tahsilatı kaydettiğinde sistem otomatik olarak en eski vadeli alacağından başlayarak eşleştirir (FIFO). Yani 3 farklı alacak kaydın varsa, ödeme ilk önce en eski alacaktan düşer.

---

## 3. Emanet (Avans) Alma

> Müvekkil dava masrafları için sana avans/emanet para verdi.

1. **Mali Takip** sayfasına git → **"+ Yeni Kayıt"**
2. Formda:
   - **İşlem Tipi** → "Tahsilat" seç
   - **Alt Tip** → "Emanet (Avans)" seç
   - **Müvekkil** → İlgili müvekkili seç
   - **Tutar** → Alınan emanet tutarı (örn. 5.000 TL)
3. **Kaydet**

> **Fark:** Emanet tahsilatı, vekalet alacaklarından düşmez. Ayrı bir kasada tutulur. Bu para müvekkilin parasıdır ve sadece masrafları karşılamak için kullanılır.

---

## 4. Müvekkil Masrafı Girme

> Müvekkil adına harç, bilirkişi ücreti, tebligat masrafı vb. ödedin.

1. **Mali Takip** sayfasına git → **"+ Yeni Kayıt"**
2. Formda:
   - **İşlem Tipi** → "Gider" seç
   - **Alt Tip** → "Müvekkil Masrafı" seç *(otomatik gelir)*
   - **Müvekkil** → Masraf yapılan müvekkili seç
   - **Dosya** → İlgili davayı seç
   - **Tutar** → Harcanan tutar
   - **Kategori** → "Harç", "Bilirkişi", "Tebligat" vb.
3. **Kaydet**

> **Sonuç:** Bu masraf, müvekkilin emanet bakiyesinden düşer. Eğer emanet bakiyesi negatife düşerse, müvekkil sana masraf borçlu demektir.

---

## 5. Büro Gideri Girme

> Büronun genel giderlerini kaydet: kira, personel maaşı, kırtasiye, internet vb.

1. **Mali Takip** sayfasına git → **"+ Yeni Kayıt"**
2. Formda:
   - **İşlem Tipi** → "Gider" seç
   - **Alt Tip** → "Büro Gideri" seç
   - ⚠️ **Müvekkil ve Dosya alanları otomatik olarak gizlenir** (çünkü büro gideri hiçbir müvekkile bağlı değildir)
   - **Tutar** → Gider tutarı
   - **Kategori** → "Kira", "Personel", "Kırtasiye" vb.
3. **Kaydet**

> **Not:** Büro giderleri kâr hesabında kullanılır. Ücret tahsilatlarından büro giderlerini çıkarınca aylık kârını görürsün.

---

## 6. Müvekkil Bakiyelerini Takip Etme

Mnüden **Müvekkiller** sayfasına git. Tabloda her müvekkil için iki önemli sütun göreceksin:

| Sütun | Anlamı | Renk Kodu |
|-------|--------|-----------|
| **Vekalet Borcu** | Alacak − Ücret Tahsilatı | 🔴 Kırmızı = borç var / 🟢 Yeşil = tamamlandı |
| **Emanet Bakiye** | Emanet − Masraf | 🟢 Yeşil = bakiye var / 🔴 Kırmızı = masraf aşıldı |

### Nasıl Oku?

- **Vekalet Borcu: 30.000 TL (kırmızı)** → Müvekkil sana hâlâ 30.000 TL vekalet ücreti borçlu
- **Vekalet Borcu: 0 TL (yeşil)** → Vekalet ücreti tamamen ödendi
- **Emanet Bakiye: 2.000 TL (yeşil)** → Müvekkilin emanet kasasında 2.000 TL kalmış
- **Emanet Bakiye: -1.500 TL (kırmızı)** → Masraflar emaneti 1.500 TL aştı, müvekkil borçlu

---

## 7. Raporlar ve Kâr Takibi

Menüden **Raporlar** sayfasına git.

### Finansal Özet Kartları

Tarih aralığı seçerek şu bilgileri görebilirsin:

1. **Ücret Tahsilatı** — Toplam vekalet ücreti tahsilatın
2. **Emanet Girişi** — Müvekkillerden alınan toplam emanet
3. **Müvekkil Masrafı** — Müvekkiller adına yapılan toplam harcama
4. **Büro Gideri** — Büro genel giderleri toplamı
5. **Bekleyen Alacak** — Henüz tahsil edilmemiş alacaklar
6. **Kâr** — Ücret Tahsilatı − Büro Gideri *(asıl kazancın)*
7. **Emanet Kasası** — Emanet − Masraf *(müvekkillere ait para)*

### Kategori Analizi

Raporlar sayfasının alt kısmında, her kategori bazında dağılımı görebilirsin. Hangi kategoride ne kadar harcama/tahsilat yaptığını takip et.

---

## 8. Dashboard Kartları

**Ana Sayfa**'da (Dashboard) mali durumunu bir bakışta görebilirsin:

| Kart | Açıklama |
|------|----------|
| Bekleyen Alacak | Tüm müvekkillerden alınması gereken toplam |
| Bu Ay Kâr | Bu ayki ücret tahsilatı − büro gideri |
| Emanet Kasası | Müvekkillere ait toplam emanet bakiyesi |

- **Kâr kartı yeşil** → Bu ay kârdayım
- **Kâr kartı kırmızı** → Bu ay giderler geliri aştı
- **Emanet kasası yeşil** → Müvekkillerin emanet parası kasada
- **Emanet kasası kırmızı** → Masraflar emanetleri aştı

---

## 9. Mali Takip Sayfasında Filtreleme

Mali Takip sayfasında üst kısımdaki filtre seçenekleriyle kayıtlarını daraltabilirsin:

- **Tümü** — Tüm mali hareketler
- **Alacak** — Sadece alacak kayıtları
- **Tahsilat (Tümü)** — Ücret + emanet tahsilatları birlikte
- **Ücret Tahsilatı** — Sadece vekalet ücreti tahsilatları
- **Emanet** — Sadece emanet girişleri
- **Gider (Tümü)** — Masraf + büro gideri birlikte
- **Müvekkil Masrafı** — Sadece müvekkil adına yapılan masraflar
- **Büro Gideri** — Sadece büro genel giderleri

Ayrıca tarih aralığı filtrelemesi de mevcut.

---

## Gerçek Hayat Senaryoları

### Senaryo 1: Yeni Dava Başlangıcı

> Ahmet Yılmaz boşanma davası için sana geldi. 40.000 TL vekalet ücreti + 5.000 TL masraf avansı üzerinde anlaştınız.

**Adımlar:**
1. **Müvekkiller** → Ahmet Yılmaz'ı kaydet
2. **Dosyalar** → Boşanma davasını aç, Ahmet Yılmaz'a bağla
3. **Mali Takip** → Yeni Kayıt → **Alacak**: 40.000 TL (vekalet ücreti)
4. **Mali Takip** → Yeni Kayıt → **Tahsilat > Emanet**: 5.000 TL (masraf avansı)

**Sonuç:**
- Vekalet Borcu: 40.000 TL (kırmızı — henüz ödenmedi)
- Emanet Bakiye: 5.000 TL (yeşil — kasada avans var)

---

### Senaryo 2: Dava Süreci — Masraflar Çıkıyor

> Dava başladı. 2.000 TL harç ve 3.500 TL bilirkişi ücreti ödedin.

**Adımlar:**
1. **Mali Takip** → Yeni Kayıt → **Gider > Müvekkil Masrafı**: 2.000 TL (Harç)
2. **Mali Takip** → Yeni Kayıt → **Gider > Müvekkil Masrafı**: 3.500 TL (Bilirkişi)

**Sonuç:**
- Emanet Bakiye: 5.000 − 2.000 − 3.500 = **-500 TL** (kırmızı — emanet aşıldı)
- Bu durumda müvekkile haber ver: "Masraflar emaneti 500 TL aştı, ek avans gerekiyor."

---

### Senaryo 3: Müvekkil Ödeme Yapıyor

> Ahmet Yılmaz 25.000 TL vekalet ücreti ödemesi + 2.000 TL ek emanet gönderdi.

**Adımlar:**
1. **Mali Takip** → Yeni Kayıt → **Tahsilat > Ücret Tahsilatı**: 25.000 TL
2. **Mali Takip** → Yeni Kayıt → **Tahsilat > Emanet**: 2.000 TL

**Sonuç:**
- Vekalet Borcu: 40.000 − 25.000 = **15.000 TL** (kırmızı — kalan borç)
- Emanet Bakiye: −500 + 2.000 = **1.500 TL** (yeşil — tekrar bakiye var)

---

### Senaryo 4: Ay Sonu Büro Giderleri

> Bu ay büro kirası 15.000 TL, asistan maaşı 12.000 TL ve kırtasiye 800 TL harcadın.

**Adımlar:**
1. **Mali Takip** → Yeni Kayıt → **Gider > Büro Gideri**: 15.000 TL (Kira)
2. **Mali Takip** → Yeni Kayıt → **Gider > Büro Gideri**: 12.000 TL (Personel)
3. **Mali Takip** → Yeni Kayıt → **Gider > Büro Gideri**: 800 TL (Kırtasiye)

> Müvekkil seçmene gerek yok — büro gideri olarak seçtiğinde müvekkil alanı otomatik gizlenir.

**Dashboard'da:**
- Bu ay ücret tahsilatın: 25.000 TL
- Bu ay büro giderin: 27.800 TL
- **Bu Ay Kâr: -2.800 TL** (kırmızı — bu ay zarar ettin)

---

### Senaryo 5: Birden Fazla Müvekkilin Takibi

> 3 aktif müvekkilin var. Hangisinin borcu var, hangisinin emaneti bitti?

1. **Müvekkiller** sayfasına git
2. Tablodaki **Vekalet Borcu** sütununa bak:
   - Ahmet Yılmaz: 15.000 TL (kırmızı) → Hâlâ borçlu
   - Fatma Demir: 0 TL (yeşil) → Tamamen ödedi
   - ABC Ltd. Şti.: 80.000 TL (kırmızı) → Büyük borç var
3. **Emanet Bakiye** sütununa bak:
   - Ahmet Yılmaz: 1.500 TL (yeşil) → Emanet kasada
   - Fatma Demir: -3.000 TL (kırmızı) → Masraf aşımı, avans iste
   - ABC Ltd. Şti.: 10.000 TL (yeşil) → Emanet yeterli

---

### Senaryo 6: Aylık Kâr Raporu Çıkarma

> Ay sonunda büronun mali durumunu görmek istiyorsun.

1. **Raporlar** sayfasına git
2. Tarih aralığını bu ayın başı ve sonu olarak ayarla
3. **"Uygula"** butonuna tıkla
4. Şu bilgileri göreceksin:
   - Ücret Tahsilatı: 85.000 TL
   - Büro Gideri: 42.000 TL
   - **Kâr: 43.000 TL** (yeşil)
   - Emanet Kasası: 15.000 TL (müvekkillere ait, senin paran değil)
   - Bekleyen Alacak: 120.000 TL (henüz tahsil edilmemiş)

---

### Senaryo 7: KDV'li Fatura Kesme

> Müvekkile 50.000 TL + KDV vekalet ücreti faturası keseceksin.

1. **Mali Takip** → Yeni Kayıt → **Alacak**
2. **Tutar**: 50.000 TL
3. **KDV Hesaplama**: "KDV Hariç" seç, oran %20
4. Sistem otomatik hesaplar:
   - Net tutar: 50.000 TL
   - KDV: 10.000 TL
   - **Toplam: 60.000 TL**
5. Kaydet

> Müvekkil 60.000 TL borçlanır (KDV dahil toplam).

---

## Sık Yapılan Hatalar

| Hata | Doğrusu |
|------|---------|
| Emanet parayı "Ücret Tahsilatı" olarak kaydetmek | Emanet seçmelisin, yoksa alacak borçtan düşer |
| Harç masrafını "Büro Gideri" olarak girmek | "Müvekkil Masrafı" seçmelisin, yoksa müvekkilin emanetinden düşmez |
| Büro kirasını müvekkile bağlamak | "Büro Gideri" seç, müvekkil alanı otomatik gizlenir |
| Kısmi ödemeyi tek kayıt yapmak | Her ödeme ayrı kayıt olmalı, sistem otomatik eşleştirir |

---

## Kısa Özet Tablosu

| Ne Yapacaksın? | İşlem Tipi | Alt Tip |
|----------------|------------|---------|
| Vekalet ücreti faturası kes | Alacak | — |
| Müvekkil vekalet ücretini ödedi | Tahsilat | Ücret Tahsilatı |
| Müvekkilden masraf avansı al | Tahsilat | Emanet |
| Müvekkil adına harç/masraf öde | Gider | Müvekkil Masrafı |
| Büro kirası / personel maaşı öde | Gider | Büro Gideri |
