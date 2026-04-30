/**
 * Proomist Hukuk - Yardımcı Fonksiyonlar
 * Toast bildirimleri, modal yardımcıları, Lucide ikon optimizasyonu.
 */

'use strict';

/* =========================================================================
   GLOBAL DIALOG (MODAL & CONFIRM) SİSTEMİ
   Tarayıcının varsayılan alert() ve confirm() fonksiyonları ile
   eski toast sisteminin yerine kullanılacak şık, tek tip modal sistemi.
   ========================================================================= */

/**
 * Global onay modalını (Confirm) açar.
 * @param {string} title - Başlık
 * @param {string} message - Mesaj
 * @param {string} confirmText - Onay butonu metni
 * @param {string} confirmColor - Onay butonu stili
 * @returns {Promise<boolean>}
 */
function showConfirm(title, message, confirmText = 'Evet, Onaylıyorum', confirmColor = 'bg-rose-600 hover:bg-rose-700') {
    return _openGlobalDialog({
        title,
        message,
        type: 'warning',
        isConfirm: true,
        confirmText,
        confirmColor
    });
}

/**
 * Global bildirim modalını (Alert/Toast alternatifi) açar.
 * @param {string} message - Mesaj
 * @param {string} type - 'success', 'error', 'warning', 'info'
 * @param {string} title - Opsiyonel başlık (Boş bırakılırsa type'a göre otomatik atanır)
 * @returns {Promise<void>}
 */
function showToast(message, type = 'info', title = null) {
    if (!title) {
        const titles = {
            success: 'Başarılı İşlem',
            error: 'Bir Hata Oluştu',
            warning: 'Uyarı',
            info: 'Bilgilendirme'
        };
        title = titles[type] || 'Bilgi';
    }

    let confirmColor = 'bg-slate-800 hover:bg-slate-900';
    if (type === 'success') confirmColor = 'bg-emerald-600 hover:bg-emerald-700';
    if (type === 'error') confirmColor = 'bg-rose-600 hover:bg-rose-700';
    if (type === 'warning') confirmColor = 'bg-amber-600 hover:bg-amber-700';

    return _openGlobalDialog({
        title,
        message,
        type,
        isConfirm: false,
        confirmText: 'Tamam',
        confirmColor
    });
}

/**
 * İç kullanım için ana Dialog fonksiyonu
 */
function _openGlobalDialog({ title, message, type, isConfirm, confirmText, confirmColor }) {
    return new Promise((resolve) => {
        const modal = document.getElementById('globalDialogModal');
        const content = document.getElementById('globalDialogContent');
        const titleEl = document.getElementById('globalDialogTitle');
        const messageEl = document.getElementById('globalDialogMessage');
        const cancelBtn = document.getElementById('globalDialogCancelBtn');
        const okBtn = document.getElementById('globalDialogOkBtn');
        const iconContainer = document.getElementById('globalDialogIconContainer');

        if (!modal || !content) {
            console.error('Global Dialog Modal elementleri bulunamadı.');
            resolve(false);
            return;
        }

        // İkonları ve Renkleri Ayarla
        const typeConfig = {
            success: { bg: 'bg-emerald-100', icon: 'check-circle', color: 'text-emerald-600' },
            error: { bg: 'bg-rose-100', icon: 'x-circle', color: 'text-rose-600' },
            warning: { bg: 'bg-amber-100', icon: 'alert-triangle', color: 'text-amber-600' },
            info: { bg: 'bg-blue-100', icon: 'info', color: 'text-blue-600' }
        };

        const config = typeConfig[type] || typeConfig.info;

        iconContainer.className = `flex items-center justify-center w-12 h-12 rounded-full mb-4 mx-auto ${config.bg}`;
        iconContainer.innerHTML = `<i data-lucide="${config.icon}" class="w-6 h-6 ${config.color}"></i>`;

        // Metinleri Ayarla
        titleEl.textContent = title;
        messageEl.textContent = message;
        okBtn.querySelector('span').textContent = confirmText;

        // Buton stillerini ayarla
        okBtn.className = `px-4 py-2 text-white rounded-md font-medium text-sm transition-colors w-full flex justify-center items-center gap-2 ${confirmColor}`;

        // İptal Butonu Görünürlüğü (Sadece Confirm ise göster)
        if (isConfirm) {
            cancelBtn.classList.remove('hidden');
        } else {
            cancelBtn.classList.add('hidden');
        }

        // İkonları oluştur
        if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [iconContainer] });

        // Event Listener'ları temizle ve yeniden bekle
        const handleCancel = () => {
            closeDialogModal();
            cleanup();
            resolve(false);
        };

        const handleOk = () => {
            closeDialogModal();
            cleanup();
            resolve(true);
        };

        const cleanup = () => {
            cancelBtn.removeEventListener('click', handleCancel);
            okBtn.removeEventListener('click', handleOk);
        };

        cancelBtn.addEventListener('click', handleCancel);
        okBtn.addEventListener('click', handleOk);

        // Modalı Aç
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        });

        // Yardımcı kapatma fonksiyonu
        function closeDialogModal() {
            modal.classList.add('opacity-0');
            content.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200);
        }
    });
}

// Geriye dönük uyumluluk ve global erişim
window.showToast = showToast;
window.showConfirm = showConfirm;

/* =========================================================================
   MODAL YARDIMCILARI
   Tüm sayfalarda aynı açılış/kapanış animasyon kalıbı kullanılıyor.
   ========================================================================= */

/**
 * Modal açar (animasyonlu).
 * @param {string} modalId - Modal container ID
 * @param {string} contentId - Modal içerik div ID
 */
function openModal(modalId, contentId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(contentId);
    if (!modal || !content) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    });
}

/**
 * Modal kapatır (animasyonlu).
 * @param {string} modalId - Modal container ID
 * @param {string} contentId - Modal içerik div ID
 */
function closeModal(modalId, contentId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(contentId);
    if (!modal || !content) return;

    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

// Global erişim
window.openModal = openModal;
window.closeModal = closeModal;

/* =========================================================================
   LUCİDE İKON OPTİMİZASYONU
   ========================================================================= */

/**
 * Lucide ikonlarını belirli bir container içinde oluşturur.
 * Tüm dokümandaki ikonları tekrar parse etmek yerine sadece yeni eklenen
 * DOM alanındaki ikonları hedefler.
 * @param {HTMLElement|Document} [container=document] - İkon aranacak container
 */
function createLucideIcons(container) {
    if (typeof lucide === 'undefined') return;

    if (container && container !== document && container instanceof HTMLElement) {
        // Hedefli ikon oluşturma
        lucide.createIcons({ nodes: [container] });
    } else {
        // Tüm dokümanda (fallback/ilk yükleme)
        lucide.createIcons();
    }
}

// Global erişim
window.createLucideIcons = createLucideIcons;

/* =========================================================================
   EMPTY STATE BİLEŞENİ
   ========================================================================= */

/**
 * Boş durum HTML'i oluşturur.
 * @param {string} icon - Lucide ikon adı
 * @param {string} title - Başlık
 * @param {string} description - Açıklama
 * @param {string} [actionText] - Aksiyon buton metni
 * @param {string} [actionOnClick] - Aksiyon butonu onclick
 * @param {number} [colspan=5] - Tablo colspan değeri
 * @returns {string} HTML string
 */
function emptyStateRow(icon, title, description, actionText, actionOnClick, colspan = 5) {
    let actionBtn = '';
    if (actionText && actionOnClick) {
        actionBtn = `
            <button onclick="${actionOnClick}" class="mt-4 inline-flex items-center gap-2 bg-gray-custom hover:bg-[#333] text-white px-4 py-2 rounded-md text-sm font-medium transition-colors shadow-sm">
                <i data-lucide="plus" class="w-4 h-4 text-gold-custom"></i>
                ${actionText}
            </button>
        `;
    }

    return `
        <tr>
            <td colspan="${colspan}" class="text-center py-12">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gold-custom/10 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="${icon}" class="w-8 h-8 text-gold-custom"></i>
                    </div>
                    <h4 class="text-base font-semibold text-slate-700 mb-1">${title}</h4>
                    <p class="text-sm text-slate-500 max-w-sm">${description}</p>
                    ${actionBtn}
                </div>
            </td>
        </tr>
    `;
}

// Global erişim
window.emptyStateRow = emptyStateRow;

/* =========================================================================
   MODAL İÇİ ALERT (showAlert - eski uyumluluk)
   Modal içindeki #modalAlert elemanı için inline uyarı gösterimi.
   ========================================================================= */

/**
 * Modal içi uyarı gösterir (eski showAlert uyumluluğu).
 * @param {string} msg - Mesaj
 * @param {boolean} [isError=true] - Hata mı başarı mı
 */
function showModalAlert(msg, isError = true) {
    const alertEl = document.getElementById('modalAlert');
    if (!alertEl) return;

    alertEl.textContent = msg;
    alertEl.className = `mb-4 p-3 rounded-md text-sm font-medium ${isError ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}`;
    alertEl.classList.remove('hidden');
}

// showAlert → showModalAlert uyumluluğu (mevcut sayfa kodlarında showAlert kullanılıyor)
window.showAlert = showModalAlert;
window.showModalAlert = showModalAlert;

/* =========================================================================
   HTML ESCAPE (XSS Koruması)
   innerHTML ile DOM'a eklenen kullanıcı verileri için zorunlu.
   ========================================================================= */

/**
 * HTML özel karakterlerini escape eder (XSS koruması).
 * @param {string|null|undefined} str
 * @returns {string}
 */
function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

window.escapeHtml = escapeHtml;

/* =========================================================================
   LOOKUP (DİNAMİK ENUM) YARDIMCILARI
   Sistem tanımları tablosundan dropdown değerlerini yükler ve cache'ler.
   ========================================================================= */

const _lookupCache = {};

/**
 * Lookup tablosundan bir grubun değerlerini yükleyip select elementine doldurur.
 * @param {string} selectId - Select elemanının ID'si
 * @param {string} groupKey - Lookup grup anahtarı (örn: 'case_types')
 * @param {string|null} defaultValue - Varsayılan seçili değer
 * @param {string|null} placeholder - İlk boş seçenek metni (örn: 'Seçiniz...')
 * @returns {Promise<Array>} Yüklenen değerler
 */
async function populateSelect(selectId, groupKey, defaultValue = null, placeholder = null) {
    const select = document.getElementById(selectId);
    if (!select) return [];

    let values = _lookupCache[groupKey];
    if (!values) {
        try {
            const response = await apiCall(`/lookups/${groupKey}`, 'GET');
            if (response && response.status === 'success') {
                values = response.data;
                _lookupCache[groupKey] = values;
            } else {
                return [];
            }
        } catch (e) {
            console.error(`Lookup yüklenemedi: ${groupKey}`, e);
            return [];
        }
    }

    select.innerHTML = '';

    if (placeholder) {
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder;
        opt.disabled = true;
        if (!defaultValue) opt.selected = true;
        select.appendChild(opt);
    }

    values.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.value;
        opt.textContent = item.label;
        if (defaultValue && item.value === defaultValue) {
            opt.selected = true;
        }
        select.appendChild(opt);
    });

    return values;
}

/**
 * Lookup cache'ini temizler.
 * @param {string|null} groupKey - Belirli bir grup veya null ise tümü
 */
function clearLookupCache(groupKey = null) {
    if (groupKey) {
        delete _lookupCache[groupKey];
    } else {
        Object.keys(_lookupCache).forEach(k => delete _lookupCache[k]);
    }
}

/**
 * Filtre dropdown'ını lookup tablosundan doldurur (toolbar filtreleri için).
 * İlk seçenek "Tümü" olarak eklenir (seçilebilir, disabled değil).
 * @param {string} selectId - Select elemanının ID'si
 * @param {string} groupKey - Lookup grup anahtarı
 * @param {string} allLabel - "Tümü" seçeneğinin metni (örn: 'Tümü (Tür)')
 * @returns {Promise<Array>} Yüklenen değerler
 */
async function populateFilterSelect(selectId, groupKey, allLabel = 'Tümü') {
    const select = document.getElementById(selectId);
    if (!select) return [];

    let values = _lookupCache[groupKey];
    if (!values) {
        try {
            const response = await apiCall(`/lookups/${groupKey}`, 'GET');
            if (response && response.status === 'success') {
                values = response.data;
                _lookupCache[groupKey] = values;
            } else {
                return [];
            }
        } catch (e) {
            console.error(`Lookup yüklenemedi: ${groupKey}`, e);
            return [];
        }
    }

    // Mevcut seçili değeri koru
    const currentValue = select.value;
    select.innerHTML = '';

    const allOpt = document.createElement('option');
    allOpt.value = '';
    allOpt.textContent = allLabel;
    select.appendChild(allOpt);

    values.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.value;
        opt.textContent = item.label;
        select.appendChild(opt);
    });

    // Önceki seçimi geri yükle
    if (currentValue) select.value = currentValue;

    return values;
}

window.populateSelect = populateSelect;
window.populateFilterSelect = populateFilterSelect;
window.clearLookupCache = clearLookupCache;
