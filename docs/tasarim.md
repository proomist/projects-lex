# Proomist LEX - UI Tasarim Rehberi

> Bu dosya, Proomist LEX projesinin tasarim sistemini birebir yeniden olusturmak icin gereken tum detaylari icerir.
> Yeni bir projede bu dosyayi AI'a verdiginizde, ayni gorsel tasarimi elde edersiniz.

---

## 1. Bagimlilaklar (CDN)

```html
<!-- Google Fonts: IBM Plex Sans -->
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@0.344.0/dist/umd/lucide.min.js"></script>
```

---

## 2. Tailwind Konfigurasyonu

```html
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                'gold-custom': '#cfb481',
                'gray-custom': '#282828',
            },
            fontFamily: {
                sans: ['IBM Plex Sans', 'sans-serif'],
                mono: ['JetBrains Mono', 'monospace'],
            }
        }
    }
}
</script>
```

### Renk Paleti

| Kullanim | Deger | Tailwind Sinifi |
|----------|-------|-----------------|
| Ana vurgu (altin) | `#cfb481` | `text-gold-custom`, `bg-gold-custom` |
| Altin hover | `#b89e6e` | `hover:bg-[#b89e6e]` |
| Koyu arka plan | `#282828` | `bg-gray-custom`, `text-gray-custom` |
| Koyu kenarlık | `#3c3c3c` | `border-[#3c3c3c]` |
| Sayfa arka plan | `#f4f4f5` | `bg-[#f4f4f5]` |
| Basarili | emerald-500/600 | `text-emerald-600`, `bg-emerald-50` |
| Hata | rose-500/600 | `text-rose-600`, `bg-rose-50` |
| Uyari | amber-500/600 | `text-amber-600`, `bg-amber-50` |
| Bilgi | blue-500/600 | `text-blue-600`, `bg-blue-50` |
| Notr | slate-* | `text-slate-600`, `bg-slate-100` |

---

## 3. Genel Yapi (Layout)

```
+----------------------------------------------------------+
| HEADER (h-12, bg-gray-custom, z-30)                      |
+----------------------------------------------------------+
| NAVBAR (hidden md:block, bg-gray-custom, z-20)           |
+----------------------------------------------------------+
| BREADCRUMB (h-10, bg-white, border-b, z-10)              |
+----------------------------------------------------------+
|                                                          |
| MAIN CONTENT (flex-1, p-4 md:p-6, bg-[#f4f4f5])         |
|                                                          |
+----------------------------------------------------------+
| FOOTER (h-7, bg-gray-custom, z-20)                       |
+----------------------------------------------------------+
```

### Body

```html
<body class="bg-[#f4f4f5] font-sans text-sm text-slate-800 flex flex-col min-h-screen">
```

---

## 4. Header (Ust Bar)

```html
<header class="bg-gray-custom text-white flex items-center justify-between px-4 shadow-md z-30" style="height:48px">

    <!-- Sol: Logo -->
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded bg-gold-custom/20 flex items-center justify-center border border-gold-custom/30">
            <i data-lucide="scale" class="w-5 h-5 text-gold-custom"></i>
        </div>
        <div>
            <span class="text-sm font-bold tracking-wide text-white">PROOMIST</span>
            <span class="text-gold-custom font-bold ml-1">LEX</span>
        </div>
    </div>

    <!-- Orta: Arama (lg+) -->
    <div class="hidden lg:flex items-center flex-1 max-w-md mx-8">
        <div class="relative w-full">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" placeholder="Ara... (Ctrl+K)"
                class="w-full bg-[#333] text-white placeholder-gray-400 pl-9 pr-4 py-1.5 rounded-md text-sm border border-[#444] focus:border-gold-custom focus:ring-1 focus:ring-gold-custom outline-none transition-all">
        </div>
    </div>

    <!-- Sag: Bildirim + Profil -->
    <div class="flex items-center gap-2">
        <!-- Hizli Islem Butonu -->
        <button class="hidden sm:flex items-center gap-1.5 bg-gold-custom hover:bg-[#b89e6e] text-gray-900 px-3 py-1.5 rounded-md text-xs font-semibold transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Hizli Islem
        </button>

        <!-- Bildirim -->
        <button class="relative p-2 text-gray-300 hover:text-white hover:bg-[#333] rounded-md transition-colors">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span id="notificationBadge" class="hidden absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold animate-pulse"></span>
        </button>

        <!-- Profil Avatar -->
        <button class="flex items-center gap-2 p-1.5 rounded-md hover:bg-[#333] transition-colors">
            <div class="w-7 h-7 rounded-full bg-[#1a1a1a] border border-[#444] flex items-center justify-center text-xs font-bold text-gold-custom">
                <span id="header-initials">AA</span>
            </div>
            <div class="hidden md:block text-left">
                <div id="header-name" class="text-xs font-medium text-white leading-none">Ad Soyad</div>
                <div id="header-role" class="text-[10px] text-gray-400">Rol</div>
            </div>
            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 hidden md:block"></i>
        </button>
    </div>
</header>
```

---

## 5. Yatay Navbar (Desktop)

```html
<nav class="bg-gray-custom border-t border-[#3c3c3c] hidden md:block z-20">
    <div class="flex items-center px-4 gap-1 overflow-x-auto custom-scroll" style="height:42px">

        <!-- Normal Link -->
        <a href="/sayfa" class="flex items-center gap-2 px-3 py-2 rounded text-gray-300 hover:bg-[#333] hover:text-white transition-colors whitespace-nowrap">
            <i data-lucide="icon-name" class="w-4 h-4"></i>
            <span class="text-sm font-medium">Sayfa Adi</span>
        </a>

        <!-- Aktif Link -->
        <a href="/aktif-sayfa" class="flex items-center gap-2 px-3 py-2 rounded bg-gold-custom/10 text-gold-custom border border-gold-custom/20 whitespace-nowrap">
            <i data-lucide="icon-name" class="w-4 h-4"></i>
            <span class="text-sm font-medium">Aktif Sayfa</span>
        </a>

        <!-- Ayirici -->
        <div class="w-px h-5 bg-[#3c3c3c] mx-1"></div>
    </div>
</nav>
```

---

## 6. Mobil Drawer Menu

```html
<!-- Overlay -->
<div id="mobileMenuOverlay" class="mobile-menu-overlay"></div>

<!-- Drawer -->
<div id="mobileMenuDrawer" class="mobile-menu-drawer">
    <!-- Ust Kisim: Logo + Kapat -->
    <div style="padding:16px;border-bottom:1px solid #3c3c3c;display:flex;align-items:center;justify-content:between">
        ...
    </div>

    <!-- Menu Linkleri -->
    <nav style="padding:12px;flex:1;overflow-y:auto">
        <a href="/sayfa" style="display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:6px;color:#9ca3af;text-decoration:none;font-size:14px;transition:all 0.2s">
            <i data-lucide="icon" style="width:20px;height:20px"></i>
            Sayfa Adi
        </a>

        <!-- Aktif Link -->
        <a href="/aktif" style="background:rgba(207,180,129,0.1);color:#cfb481;border:1px solid rgba(207,180,129,0.2);...">
            ...
        </a>
    </nav>
</div>
```

### Mobil Drawer CSS

```css
.mobile-menu-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    z-index: 40;
    opacity: 0; visibility: hidden;
    transition: all 0.3s ease;
}
.mobile-menu-overlay.active { opacity: 1; visibility: visible; }

.mobile-menu-drawer {
    position: fixed; left: 0; top: 0;
    width: 280px; height: 100%;
    background: #282828;
    z-index: 41;
    transform: translateX(-100%);
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    display: flex; flex-direction: column;
    box-shadow: 4px 0 24px rgba(0,0,0,0.4);
}
.mobile-menu-drawer.active { transform: translateX(0); }
```

---

## 7. Breadcrumb

```html
<div class="bg-white border-b border-slate-200 flex items-center px-4 z-10" style="height:40px">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="/" class="hover:text-gold-custom transition-colors">
            <i data-lucide="home" class="w-4 h-4"></i>
        </a>
        <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300"></i>
        <span class="text-slate-800 font-medium">Sayfa Adi</span>
    </div>
</div>
```

---

## 8. Sayfa Icerik Alani

### Ana Container (Kart)

```html
<div class="bg-white border border-slate-200 rounded-lg shadow-sm flex flex-col h-full min-h-[500px]">

    <!-- Toolbar -->
    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 shrink-0">
        <div class="flex items-center gap-2">
            <h3 class="font-semibold text-gray-custom flex items-center gap-2">
                <i data-lucide="icon" class="w-5 h-5 text-gold-custom"></i>
                Baslik
            </h3>
            <span class="bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full border border-slate-200 font-medium">
                Kayit Sayisi
            </span>
        </div>
        <div class="flex items-center gap-3">
            <!-- Filtre / Butonlar -->
        </div>
    </div>

    <!-- Icerik (Tablo veya Liste) -->
    <div class="flex-1 overflow-auto custom-scroll">
        ...
    </div>

    <!-- Pagination (Alt Bar) -->
    <div class="px-5 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between shrink-0 rounded-b-lg">
        <span class="text-sm text-slate-500">Sayfa 1 / 5</span>
        <div class="flex gap-1">
            <button class="px-3 py-1 border border-slate-300 rounded bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 text-sm">Onceki</button>
            <button class="px-3 py-1 border border-slate-300 rounded bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 text-sm">Sonraki</button>
        </div>
    </div>
</div>
```

---

## 9. Butonlar

```html
<!-- Birincil Buton (Koyu) -->
<button class="bg-gray-custom hover:bg-[#333] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
    <i data-lucide="plus" class="w-4 h-4 text-gold-custom"></i>
    Yeni Ekle
</button>

<!-- Vurgu Buton (Altin) -->
<button class="bg-gold-custom hover:bg-[#b89e6e] text-gray-900 px-6 py-2 rounded-md text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
    <i data-lucide="save" class="w-4 h-4"></i>
    Kaydet
</button>

<!-- Ikincil Buton (Kenarlıklı) -->
<button class="px-4 py-2 border border-slate-300 rounded bg-white text-slate-600 hover:bg-slate-50 text-sm font-medium transition-colors">
    Iptal
</button>

<!-- Tehlike Butonu -->
<button class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
    Sil
</button>

<!-- Ikon Buton (Tablo Icindeki Aksiyonlar) -->
<button class="p-1.5 rounded-md hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors">
    <i data-lucide="edit" class="w-4 h-4"></i>
</button>

<button class="p-1.5 rounded-md hover:bg-rose-50 text-slate-400 hover:text-rose-500 transition-colors">
    <i data-lucide="trash-2" class="w-4 h-4"></i>
</button>
```

---

## 10. Form Elemanlari

```html
<!-- Label -->
<label class="block text-sm font-medium text-slate-700 mb-1">
    Alan Adi <span class="text-rose-500">*</span>
</label>

<!-- Text Input -->
<input type="text" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-gold-custom focus:ring-1 focus:ring-gold-custom transition-all" placeholder="Placeholder...">

<!-- Select -->
<select class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-gold-custom focus:ring-1 focus:ring-gold-custom transition-all">
    <option value="">Seciniz...</option>
</select>

<!-- Textarea -->
<textarea rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm outline-none focus:border-gold-custom focus:ring-1 focus:ring-gold-custom transition-all resize-none" placeholder="Aciklama..."></textarea>

<!-- Form Grid -->
<div class="grid grid-cols-2 gap-5">
    <div><!-- Alan 1 --></div>
    <div><!-- Alan 2 --></div>
</div>

<!-- Ikonlu Arama Inputu -->
<div class="relative">
    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
    <input type="text" placeholder="Ara..." class="pl-9 pr-4 py-2 border border-slate-300 rounded-md text-sm focus:border-gold-custom focus:ring-1 focus:ring-gold-custom outline-none">
</div>
```

---

## 11. Tablo

```html
<table class="w-full">
    <thead>
        <tr class="bg-slate-50 text-slate-500 font-medium sticky top-0 z-10 shadow-sm border-b border-slate-200">
            <th class="px-5 py-3 text-left text-xs uppercase tracking-wider">Sutun</th>
            <th class="px-5 py-3 text-left text-xs uppercase tracking-wider">Sutun</th>
            <th class="px-5 py-3 text-center text-xs uppercase tracking-wider">Islemler</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        <tr class="hover:bg-slate-50/50 transition-colors group">
            <td class="px-5 py-3 text-sm text-slate-800">Deger</td>
            <td class="px-5 py-3 text-sm text-slate-500">Deger</td>
            <td class="px-5 py-3 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button class="p-1.5 rounded-md hover:bg-blue-50 text-slate-400 hover:text-blue-600 transition-colors">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </button>
                    <button class="p-1.5 rounded-md hover:bg-rose-50 text-slate-400 hover:text-rose-500 transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>
        </tr>
    </tbody>
</table>
```

---

## 12. Durum Badge'leri

```html
<!-- Basarili / Aktif -->
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border bg-emerald-50 text-emerald-700 border-emerald-200">Aktif</span>

<!-- Hata / Tehlike -->
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border bg-rose-50 text-rose-600 border-rose-200">Pasif</span>

<!-- Uyari -->
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border bg-amber-50 text-amber-700 border-amber-200">Beklemede</span>

<!-- Bilgi -->
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border bg-blue-50 text-blue-700 border-blue-200">Bilgi</span>

<!-- Notr -->
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border bg-slate-100 text-slate-600 border-slate-200">Normal</span>
```

---

## 13. Istatistik Kartlari (Dashboard)

```html
<!-- Koyu Kart -->
<div class="bg-gray-custom text-white rounded-lg border border-[#3c3c3c] p-4 shadow-md">
    <div class="flex items-center justify-between mb-3">
        <div class="w-10 h-10 rounded-md bg-gold-custom/10 flex items-center justify-center">
            <i data-lucide="icon" class="w-5 h-5 text-gold-custom"></i>
        </div>
        <span class="text-2xl font-bold">42</span>
    </div>
    <div class="text-sm text-gray-300">Istatistik Adi</div>
</div>

<!-- Beyaz Kart -->
<div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm">
    <div class="flex items-center justify-between mb-3">
        <div class="w-10 h-10 rounded-md bg-gray-custom/5 flex items-center justify-center text-gray-custom border border-gray-custom/10">
            <i data-lucide="icon" class="w-5 h-5"></i>
        </div>
        <span class="text-2xl font-bold text-gray-custom">18</span>
    </div>
    <div class="text-sm text-slate-500">Istatistik Adi</div>
</div>
```

---

## 14. Modal

```html
<!-- Modal Overlay -->
<div id="myModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">

    <!-- Modal Icerik -->
    <div id="myModalContent" class="bg-white rounded-lg shadow-xl w-full max-w-2xl flex flex-col max-h-[90vh] transform scale-95 transition-transform duration-300">

        <!-- Baslik -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between shrink-0">
            <h3 class="text-lg font-semibold text-gray-custom flex items-center gap-2">
                <i data-lucide="icon" class="w-5 h-5 text-gold-custom"></i>
                Modal Basligi
            </h3>
            <button onclick="closeModal('myModal','myModalContent')" class="text-slate-400 hover:text-rose-500 transition-colors p-1 rounded-md hover:bg-rose-50">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Icerik -->
        <div class="p-6 overflow-y-auto custom-scroll">
            <div id="modalAlert" class="hidden mb-4 p-3 rounded-md text-sm font-medium"></div>
            <!-- Form alanlari buraya -->
        </div>

        <!-- Alt Bar -->
        <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end gap-3 shrink-0 bg-slate-50 rounded-b-lg">
            <button onclick="closeModal('myModal','myModalContent')" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">
                Iptal
            </button>
            <button onclick="save()" class="bg-gold-custom hover:bg-[#b89e6e] text-gray-900 px-6 py-2 rounded-md text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Kaydet</span>
            </button>
        </div>
    </div>
</div>
```

### Modal Acma/Kapama (JS)

```javascript
function openModal(modalId, contentId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(contentId);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    });
}

function closeModal(modalId, contentId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(contentId);
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}
```

---

## 15. Global Dialog (Toast/Confirm Alternatifi)

```html
<div id="globalDialogModal" class="fixed inset-0 bg-black/60 z-[100] hidden items-center justify-center opacity-0 transition-opacity duration-200 backdrop-blur-sm">
    <div id="globalDialogContent" class="bg-white rounded-lg shadow-xl w-full max-w-sm overflow-hidden transform scale-95 transition-transform duration-200 m-4">
        <div class="p-6">
            <div id="globalDialogIconContainer" class="flex items-center justify-center w-12 h-12 rounded-full mb-4 mx-auto"></div>
            <h3 id="globalDialogTitle" class="text-lg font-bold text-center text-gray-900 mb-2"></h3>
            <p id="globalDialogMessage" class="text-sm text-center text-slate-500 mb-6"></p>
            <div class="flex gap-3 justify-center">
                <button id="globalDialogCancelBtn" class="hidden px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-md font-medium text-sm transition-colors w-full">Iptal</button>
                <button id="globalDialogOkBtn" class="px-4 py-2 text-white rounded-md font-medium text-sm transition-colors w-full flex justify-center items-center gap-2">
                    <span>Tamam</span>
                </button>
            </div>
        </div>
    </div>
</div>
```

### Dialog Ikon Tipleri

```javascript
const typeConfig = {
    success: { bg: 'bg-emerald-100', icon: 'check-circle', color: 'text-emerald-600' },
    error:   { bg: 'bg-rose-100',    icon: 'x-circle',     color: 'text-rose-600' },
    warning: { bg: 'bg-amber-100',   icon: 'alert-triangle', color: 'text-amber-600' },
    info:    { bg: 'bg-blue-100',    icon: 'info',          color: 'text-blue-600' }
};
```

---

## 16. Bos Durum (Empty State)

```html
<tr>
    <td colspan="7" class="text-center py-12">
        <div class="flex flex-col items-center">
            <div class="w-16 h-16 bg-gold-custom/10 rounded-full flex items-center justify-center mb-4">
                <i data-lucide="icon" class="w-8 h-8 text-gold-custom"></i>
            </div>
            <h4 class="text-base font-semibold text-slate-700 mb-1">Kayit Bulunamadi</h4>
            <p class="text-sm text-slate-500 max-w-sm">Henuz bir kayit eklenmemis.</p>
            <button onclick="yeniEkle()" class="mt-4 inline-flex items-center gap-2 bg-gray-custom hover:bg-[#333] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm">
                <i data-lucide="plus" class="w-4 h-4 text-gold-custom"></i>
                Yeni Ekle
            </button>
        </div>
    </td>
</tr>
```

---

## 17. Bilgi Banneri

```html
<div class="bg-blue-50/50 border-b border-blue-100 px-5 py-4 shrink-0">
    <div class="flex items-start gap-3">
        <i data-lucide="info" class="w-5 h-5 text-blue-500 mt-0.5 shrink-0"></i>
        <div class="text-sm text-blue-900">
            Bilgilendirme mesaji buraya yazilir.
        </div>
    </div>
</div>
```

---

## 18. Footer (Durum Cubugu)

```html
<footer class="bg-gray-custom border-t border-[#3c3c3c] flex items-center justify-between px-3 z-20 select-none" style="height:28px">
    <div class="flex items-center gap-3">
        <span class="flex items-center gap-1.5 text-[11px] text-gray-400">
            <i data-lucide="shield-check" class="w-3 h-3 text-emerald-400"></i>
            Guvenli Baglanti
        </span>
        <span class="flex items-center gap-1.5 text-[11px] text-gray-400">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Cevrimici
        </span>
    </div>
    <div class="flex items-center gap-3 text-[11px] text-gray-400">
        <span class="bg-[#1a1a1a] px-2 py-0.5 rounded border border-[#333]">
            <i data-lucide="globe" class="w-3 h-3 inline"></i> <span id="user-ip">0.0.0.0</span>
        </span>
        <span id="current-date">15 Mart 2026</span>
        <span id="current-time" class="font-mono">14:30</span>
    </div>
</footer>
```

---

## 19. Sifre Guc Gostergesi

```html
<div class="mt-2">
    <div class="flex gap-1 mb-1">
        <div class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors" id="str1"></div>
        <div class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors" id="str2"></div>
        <div class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors" id="str3"></div>
        <div class="h-1.5 flex-1 rounded-full bg-slate-200 transition-colors" id="str4"></div>
    </div>
    <p class="text-xs text-slate-500">Sifre gucu: Zayif</p>
</div>
```

### Guc Renkleri

| Seviye | Renk | Tailwind |
|--------|------|----------|
| Zayif (1/4) | Kirmizi | `bg-rose-500` |
| Orta (2/4) | Turuncu | `bg-amber-500` |
| Iyi (3/4) | Sari | `bg-yellow-400` |
| Guclu (4/4) | Yesil | `bg-emerald-500` |

---

## 20. Oncelik Badge'leri

```html
<!-- Acil / Yuksek -->
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-50 text-rose-600 border border-rose-100">Acil</span>

<!-- Normal -->
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">Normal</span>

<!-- Dusuk -->
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-50 text-slate-500 border border-slate-100">Dusuk</span>
```

---

## 21. Yuklenme Animasyonlari

```html
<!-- Spinner -->
<i data-lucide="loader-2" class="w-6 h-6 animate-spin text-gold-custom"></i>

<!-- Skeleton Yukleme -->
<div class="skeleton h-4 w-3/4 mb-2"></div>
<div class="skeleton h-4 w-1/2"></div>
```

### Skeleton CSS

```css
.skeleton {
    background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.5s ease-in-out infinite;
    border-radius: 4px;
}

@keyframes skeleton-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
```

---

## 22. Scrollbar Stilleri

```css
/* Genel scrollbar */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* Koyu arka plan icin (navbar vb.) */
.custom-scroll::-webkit-scrollbar { height: 4px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background: #4a4a4a; border-radius: 2px; }
.custom-scroll::-webkit-scrollbar-thumb:hover { background: #cfb481; }
```

---

## 23. Responsive Tasarim Kurallari

| Breakpoint | Davranis |
|------------|----------|
| < 768px (mobil) | Hamburger menu, tek sutun grid, tablo yatay scroll |
| >= 768px (md) | Yatay navbar gorunur, 2 sutunlu grid |
| >= 1024px (lg) | Header arama gorunur, 3-4 sutunlu grid |

### Yaygin Kaliplar

```html
<!-- Responsive Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">

<!-- Responsive Padding -->
<main class="flex-1 p-4 md:p-6">

<!-- Mobilde gizle, masaustunde goster -->
<div class="hidden md:block">...</div>
<div class="hidden lg:flex">...</div>

<!-- Tablo responsive wrap -->
<div class="overflow-x-auto">
    <table class="w-full min-w-[800px]">...</table>
</div>
```

---

## 24. Login Sayfasi

```html
<body class="bg-[#f4f4f5] min-h-screen flex items-center justify-center p-4 font-sans">
    <div class="w-full max-w-md">
        <!-- Logo Alani -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto bg-gray-custom rounded-lg flex items-center justify-center shadow-md mb-4 border border-[#3c3c3c]">
                <i data-lucide="scale" class="w-8 h-8 text-gold-custom"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-custom">PROOMIST <span class="text-gold-custom">LEX</span></h1>
            <p class="text-sm text-slate-500 mt-1">Hukuk Burosu Yonetim Sistemi</p>
        </div>

        <!-- Login Karti -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <h2 class="text-lg font-semibold text-gray-custom mb-6 text-center">Giris Yap</h2>

            <div id="loginAlert" class="hidden mb-4 p-3 rounded-md text-sm font-medium"></div>

            <form>
                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kullanici Adi veya E-posta</label>
                    <div class="relative">
                        <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-md text-sm focus:border-gold-custom focus:ring-1 focus:ring-gold-custom outline-none transition-all" placeholder="ornek@proomist.com">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Sifre</label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="password" class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-md text-sm focus:border-gold-custom focus:ring-1 focus:ring-gold-custom outline-none transition-all" placeholder="********">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gray-custom hover:bg-[#333] text-white py-2.5 rounded-md text-sm font-semibold transition-colors shadow-sm flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4 text-gold-custom"></i>
                    Giris Yap
                </button>
            </form>
        </div>

        <!-- Alt bilgi -->
        <p class="text-center text-xs text-slate-400 mt-6">&copy; 2026 Proomist LEX</p>
    </div>
</body>
```

---

## 25. Ikon Sistemi (Lucide)

### Boyutlar

| Kullanim | Sinif |
|----------|-------|
| Kucuk (tablo, badge) | `w-3 h-3` veya `w-4 h-4` |
| Orta (buton, navbar) | `w-5 h-5` |
| Buyuk (empty state, dialog) | `w-6 h-6` veya `w-8 h-8` |

### Yaygin Ikonlar

| Alan | Ikonlar |
|------|---------|
| Navigasyon | layout-dashboard, users, folder-open, gavel, check-square, wallet, file-archive, bar-chart-3, shield, settings, book-open |
| Aksiyonlar | plus, edit, trash-2, save, x, search, bell, log-out, download, upload, eye |
| Durum | check-circle, alert-triangle, info, x-circle, loader-2 |
| Veri | trending-up, calendar, clock, shield-check, globe, hash |

### Kullanim

```html
<!-- Statik ikon -->
<i data-lucide="icon-adi" class="w-5 h-5 text-gold-custom"></i>

<!-- JS ile olusturma -->
<script>
    lucide.createIcons();                    // Tum sayfa
    lucide.createIcons({ nodes: [element] }); // Belirli container
</script>
```

---

## 26. XSS Korumasi

`innerHTML` ile DOM'a kullanici verisi eklerken **mutlaka** `escapeHtml()` kullanin:

```javascript
function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
```

---

## Ozet: Tasarim Prensipleri

1. **Renk**: Koyu grilar (#282828, #3c3c3c) + altin vurgu (#cfb481) + slate notr tonlar
2. **Font**: IBM Plex Sans (UI), JetBrains Mono (veri)
3. **Koseleri**: `rounded-md` (butonlar, inputlar), `rounded-lg` (kartlar, modallar)
4. **Golge**: `shadow-sm` (kartlar), `shadow-md` (header), `shadow-xl` (modallar)
5. **Gecisler**: Her hover/focus efektinde `transition-colors` veya `transition-all`
6. **Spacing**: 4px grid sistemi (p-1=4px, p-2=8px, p-3=12px, p-4=16px, p-5=20px, p-6=24px)
7. **Ikonlar**: Lucide 0.344, her zaman `<i data-lucide="...">` ile
8. **Responsive**: Mobile-first, md (768px) ve lg (1024px) breakpoint'leri
9. **Modallar**: Backdrop blur + scale animasyonu
10. **Tablolar**: Sticky header, hover satir, divide-y ayirici
