/**
 * Proomist Hukuk - Global API İstek Aracı
 * Cookie tabanlı (HttpOnly) kimlik doğrulama.
 * Token artık localStorage'da tutulmaz – tarayıcı cookie'yi otomatik gönderir.
 */

'use strict';

/**
 * API'ye istek gönderir. Cookie yoksa/geçersizse backend 401 döner.
 * @param {string} endpoint - API endpoint (örn: '/clients')
 * @param {string} [method='GET'] - HTTP method
 * @param {Object|null} [body=null] - Request body (JSON olarak gönderilir)
 * @returns {Promise<Object|null>} API yanıtı veya null (401 durumunda)
 */
async function apiCall(endpoint, method = 'GET', body = null) {
    const options = {
        method: method.toUpperCase(),
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
    };

    if (body) {
        options.body = JSON.stringify(body);
    }

    try {
        const response = await fetch('/api/v1' + endpoint, options);

        if (response.status === 401) {
            console.warn('API 401 – Oturum süresi dolmuş veya geçersiz.');
            if (typeof showToast === 'function') {
                showToast('Oturum süresi doldu. Giriş sayfasına yönlendiriliyorsunuz...', 'warning');
            }
            setTimeout(() => { window.location.href = '/login'; }, 1500);
            return null;
        }

        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('JSON parse error:', text.substring(0, 500));
            return { status: 'error', message: 'Sunucu hatası (HTTP ' + response.status + ')' };
        }
    } catch (error) {
        console.error('API Error:', error);
        return { status: 'error', message: 'Sunucuya bağlanılamadı.' };
    }
}

// Global erişim
window.apiCall = apiCall;
