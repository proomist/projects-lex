/**
 * Proomist Hukuk - Ana Uygulama Script'i
 * Kullanıcı bilgileri, saat/tarih, profil dropdown, hamburger menü, IP bilgisi.
 * Arama, bildirimler ve navbar badge yönetimi.
 *
 * Kimlik doğrulama: HttpOnly cookie (legal_session) – token JS'den erişilemez.
 * Kullanıcı bilgisi: /api/v1/me endpoint'inden ve localStorage (sadece UI verileri).
 */

'use strict';

/* =========================================================================
   LUCIDE İKONLARI BAŞLAT
   ========================================================================= */

document.addEventListener('DOMContentLoaded', function () {
    createLucideIcons();
});

/* =========================================================================
   KULLANICI BİLGİLERİNİ YÜKLE
   ========================================================================= */

async function loadUserInfo() {
    // 1) Hızlı render: localStorage'dan cached user verisi (UI amaçlı)
    var userData = JSON.parse(localStorage.getItem('legal_user') || '{}');
    if (userData && userData.first_name) {
        updateUserUI(userData.first_name, userData.last_name, userData.title || 'Yetkili');
    }

    // 2) Backend'den güncel veri al (rol + IP)
    try {
        var res = await apiCall('/me');
        if (res && res.status === 'success' && res.data) {
            var data = res.data;

            var footerRole = document.getElementById('footer-user-role');
            if (footerRole) footerRole.textContent = '(' + (data.role || 'Yetkili') + ')';

            var ipEl = document.getElementById('user-ip');
            if (ipEl) ipEl.textContent = data.client_ip || 'Bilinmiyor';
        }
    } catch (e) {
        console.log('Kullanıcı bilgisi yüklenemedi.');
    }
}

function updateUserUI(firstName, lastName, role) {
    var footerName = document.getElementById('footer-user-name');
    if (footerName) footerName.textContent = firstName + ' ' + lastName;

    var headerName = document.getElementById('header-name');
    var headerRole = document.getElementById('header-role');
    var headerInitials = document.getElementById('header-initials');

    if (headerName) headerName.textContent = firstName + ' ' + lastName;
    if (headerRole) headerRole.textContent = role;
    if (headerInitials) headerInitials.textContent = firstName.charAt(0) + lastName.charAt(0);
}

loadUserInfo();

/* =========================================================================
   HIZLI İŞLEM (YENİ KAYIT) DROPDOWN
   ========================================================================= */

function toggleQuickActionMenu() {
    var menu = document.getElementById('quickActionDropdown');
    if (menu) menu.classList.toggle('hidden');
}

window.addEventListener('click', function (e) {
    var container = document.getElementById('quickActionContainer');
    var menu = document.getElementById('quickActionDropdown');
    if (container && menu && !container.contains(e.target) && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
    }
});

window.toggleQuickActionMenu = toggleQuickActionMenu;

/* =========================================================================
   PROFİL DROPDOWN
   ========================================================================= */

function toggleProfileMenu() {
    var menu = document.getElementById('profileMenu');
    if (menu) menu.classList.toggle('hidden');
}

window.addEventListener('click', function (e) {
    var container = document.getElementById('profileDropdownContainer');
    var menu = document.getElementById('profileMenu');
    if (container && menu && !container.contains(e.target) && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
    }
});

window.toggleProfileMenu = toggleProfileMenu;

/* =========================================================================
   SAAT VE TARİH GÜNCELLEYİCİ
   ========================================================================= */

function updateTime() {
    var now = new Date();
    var timeEl = document.getElementById('current-time');
    var dateEl = document.getElementById('current-date');

    if (timeEl) timeEl.textContent = now.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
    if (dateEl) dateEl.textContent = now.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' });
}
setInterval(updateTime, 1000);
updateTime();

/* =========================================================================
   ÇIKIŞ (Backend cookie temizleme)
   ========================================================================= */

async function doLogout() {
    try {
        await fetch('/api/v1/logout', {
            method: 'POST',
            credentials: 'include'
        });
    } catch (e) {
        // Logout API çağrısı başarısız olsa bile local verileri temizle
    }
    localStorage.removeItem('legal_user');
    window.location.href = '/login';
}

window.doLogout = doLogout;

/* =========================================================================
   HAMBURGER MENÜ (MOBİL DRAWER)
   ========================================================================= */

function toggleMobileMenu() {
    var overlay = document.getElementById('mobileMenuOverlay');
    var drawer = document.getElementById('mobileMenuDrawer');
    if (!overlay || !drawer) return;

    var isOpen = overlay.classList.contains('active');

    if (isOpen) {
        closeMobileMenu();
    } else {
        overlay.classList.add('active');
        drawer.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeMobileMenu() {
    var overlay = document.getElementById('mobileMenuOverlay');
    var drawer = document.getElementById('mobileMenuDrawer');
    if (!overlay || !drawer) return;

    overlay.classList.remove('active');
    drawer.classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeMobileMenu();
        closeSearchDropdown();
        closeNotificationDropdown();
    }
});

window.toggleMobileMenu = toggleMobileMenu;
window.closeMobileMenu = closeMobileMenu;

/* =========================================================================
   NAVBAR AKTİF SAYFA SCROLL (Desktop yatay navbar için)
   ========================================================================= */

document.addEventListener('DOMContentLoaded', function () {
    var activeLink = document.querySelector('nav .flex a.bg-gold-custom\\/10');
    if (activeLink) {
        activeLink.scrollIntoView({ inline: 'center', behavior: 'smooth', block: 'nearest' });
    }
});

/* =========================================================================
   NAVBAR BADGE'LERİ YÜKLE (Görev kırmızı nokta + bildirim sayısı)
   ========================================================================= */

async function loadBadges() {
    try {
        var res = await apiCall('/dashboard/badges');
        if (res && res.status === 'success' && res.data) {
            // İş Listesi kırmızı nokta
            var taskDot = document.getElementById('taskBadgeDot');
            if (taskDot) {
                if (res.data.pending_tasks > 0) {
                    taskDot.classList.remove('hidden');
                } else {
                    taskDot.classList.add('hidden');
                }
            }

            // Bildirim badge
            var notifBadge = document.getElementById('notificationBadge');
            if (notifBadge) {
                if (res.data.unread_notifications > 0) {
                    notifBadge.classList.remove('hidden');
                } else {
                    notifBadge.classList.add('hidden');
                }
            }
        }
    } catch (e) {
        // Badge yüklenemezse sessizce devam et
    }
}

loadBadges();
// Her 60 saniyede badge'leri güncelle
setInterval(loadBadges, 60000);

/* =========================================================================
   HIZLI ARAMA (Ctrl+K ile açılır, debounce ile arama)
   ========================================================================= */

var searchTimeout = null;

function initSearch() {
    var input = document.getElementById('globalSearchInput');
    var dropdown = document.getElementById('searchResultsDropdown');
    var resultsList = document.getElementById('searchResultsList');
    var emptyState = document.getElementById('searchEmptyState');
    var loadingState = document.getElementById('searchLoadingState');

    if (!input) return;

    // Ctrl+K kısayolu
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            input.focus();
        }
    });

    // Input değişikliklerini dinle (debounce)
    input.addEventListener('input', function() {
        var query = input.value.trim();
        clearTimeout(searchTimeout);

        if (query.length < 2) {
            closeSearchDropdown();
            return;
        }

        // Loading state göster
        dropdown.classList.remove('hidden');
        resultsList.innerHTML = '';
        emptyState.classList.add('hidden');
        loadingState.classList.remove('hidden');

        searchTimeout = setTimeout(function() {
            performSearch(query);
        }, 300);
    });

    // Focus out ile kapat
    document.addEventListener('click', function(e) {
        var container = document.getElementById('globalSearchContainer');
        if (container && !container.contains(e.target)) {
            closeSearchDropdown();
        }
    });
}

async function performSearch(query) {
    var dropdown = document.getElementById('searchResultsDropdown');
    var resultsList = document.getElementById('searchResultsList');
    var emptyState = document.getElementById('searchEmptyState');
    var loadingState = document.getElementById('searchLoadingState');

    var res = await apiCall('/dashboard/search?q=' + encodeURIComponent(query));
    loadingState.classList.add('hidden');

    if (res && res.status === 'success' && res.data && res.data.length > 0) {
        emptyState.classList.add('hidden');
        var typeIcons = {
            'client': '<i data-lucide="users" class="w-4 h-4 text-blue-500 shrink-0"></i>',
            'case': '<i data-lucide="folder-open" class="w-4 h-4 text-amber-600 shrink-0"></i>',
            'task': '<i data-lucide="check-square" class="w-4 h-4 text-emerald-500 shrink-0"></i>'
        };
        var typeLabels = {
            'client': 'Müvekkil',
            'case': 'Dosya',
            'task': 'Görev'
        };

        resultsList.innerHTML = res.data.map(function(item) {
            return '<a href="' + item.url + '" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0">' +
                (typeIcons[item.type] || '') +
                '<div class="flex-1 min-w-0">' +
                    '<div class="text-sm font-medium text-slate-800 truncate">' + item.title + '</div>' +
                    '<div class="text-xs text-slate-400">' + (item.subtitle || '') + '</div>' +
                '</div>' +
                '<span class="text-[10px] font-medium text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded shrink-0">' + (typeLabels[item.type] || '') + '</span>' +
            '</a>';
        }).join('');

        createLucideIcons(resultsList);
    } else {
        resultsList.innerHTML = '';
        emptyState.classList.remove('hidden');
    }
}

function closeSearchDropdown() {
    var dropdown = document.getElementById('searchResultsDropdown');
    if (dropdown) dropdown.classList.add('hidden');
}

window.closeSearchDropdown = closeSearchDropdown;

document.addEventListener('DOMContentLoaded', initSearch);

/* =========================================================================
   BİLDİRİMLER (Reminders tabanlı)
   ========================================================================= */

function initNotifications() {
    var btn = document.getElementById('notificationBtn');
    var dropdown = document.getElementById('notificationDropdown');
    var container = document.getElementById('notificationContainer');

    if (!btn || !dropdown) return;

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var isHidden = dropdown.classList.contains('hidden');
        if (isHidden) {
            dropdown.classList.remove('hidden');
            loadNotifications();
        } else {
            dropdown.classList.add('hidden');
        }
    });

    document.addEventListener('click', function(e) {
        if (container && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

async function loadNotifications() {
    var list = document.getElementById('notificationList');
    var countEl = document.getElementById('notificationCount');
    var emptyEl = document.getElementById('notificationEmpty');

    var res = await apiCall('/dashboard/notifications');
    if (res && res.status === 'success' && res.data) {
        var items = res.data.items || [];
        var unread = res.data.unread_count || 0;

        if (countEl) countEl.textContent = unread > 0 ? (unread + ' okunmamış') : '';

        if (!items.length) {
            list.innerHTML = '<div class="p-4 text-center text-sm text-slate-400">Bildirim bulunmuyor</div>';
            return;
        }

        list.innerHTML = items.map(function(n) {
            var isUnread = !n.is_read;
            var bgClass = isUnread ? 'bg-gold-custom/5' : '';
            var dotHtml = isUnread ? '<span class="w-2 h-2 rounded-full bg-gold-custom shrink-0 mt-1.5"></span>' : '<span class="w-2 shrink-0"></span>';

            var date = new Date(n.remind_at);
            var timeStr = date.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' }) + ' ' +
                          date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });

            var context = n.case_no ? ('Dosya: ' + n.case_no) : (n.task_title || '');

            return '<div class="flex items-start gap-2 px-4 py-3 hover:bg-slate-50 transition-colors border-b border-slate-50 cursor-pointer ' + bgClass + '" onclick="markNotificationRead(' + n.id + ', this)">' +
                dotHtml +
                '<div class="flex-1 min-w-0">' +
                    '<div class="text-sm text-slate-700 leading-snug">' + n.message + '</div>' +
                    (context ? '<div class="text-xs text-slate-400 mt-0.5">' + context + '</div>' : '') +
                    '<div class="text-[10px] text-slate-400 mt-1">' + timeStr + '</div>' +
                '</div>' +
            '</div>';
        }).join('');
    }
}

async function markNotificationRead(id, el) {
    await apiCall('/dashboard/notifications/' + id + '/read', 'PUT');
    if (el) {
        el.classList.remove('bg-gold-custom/5');
        var dot = el.querySelector('span.bg-gold-custom');
        if (dot) dot.remove();
    }
    loadBadges();
}

function closeNotificationDropdown() {
    var dropdown = document.getElementById('notificationDropdown');
    if (dropdown) dropdown.classList.add('hidden');
}

window.markNotificationRead = markNotificationRead;
window.closeNotificationDropdown = closeNotificationDropdown;

document.addEventListener('DOMContentLoaded', initNotifications);
