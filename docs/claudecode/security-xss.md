# [XSS] Cross-Site Scripting Kuralları

## [XSS-01] Output Encoding Zorunluluğu — KRİTİK
Kullanıcı girdisi HTML'e yazılmadan önce bağlama uygun encode edilmelidir.

- HTML body → HTML entity encoding
- HTML attribute → attribute encoding
- JavaScript string → JS encoding
- URL parametresi → URL encoding (encodeURIComponent)

```
✗ YANLIŞ:
  <div>{{ userData }}</div>          // raw, escape yok
  element.innerHTML = userInput      // DOM-based XSS

✓ DOĞRU:
  <div>{{ userData | escape }}</div>  // template auto-escape
  element.textContent = userInput    // safe DOM API
```

Kontrol:
- [ ] Template engine auto-escape aktif (Blade, Razor, Twig, React JSX)
- [ ] innerHTML yerine textContent veya framework binding kullanılıyor
- [ ] URL parametreleri encodeURIComponent ile encode ediliyor
- [ ] CSS value içinde kullanıcı girdisi yok veya sanitize

## [XSS-02] Content Security Policy (CSP) — YÜKSEK
CSP header ile inline script engellenmelidir.

```
✗ YANLIŞ:
  Content-Security-Policy: script-src 'unsafe-inline' 'unsafe-eval' *

✓ DOĞRU:
  Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{random}'; style-src 'self'; img-src 'self' data:; frame-ancestors 'none'
```

Kontrol:
- [ ] CSP header tüm sayfalarda mevcut
- [ ] `unsafe-inline` ve `unsafe-eval` yok
- [ ] Nonce veya hash-based CSP uygulanmış

## [XSS-03] Rich Text Sanitizasyonu — KRİTİK
WYSIWYG editör çıktısı sunucu tarafında beyaz liste tabanlı sanitizer ile temizlenmelidir. Kara liste (blacklist) yaklaşımı bypass edilebilir.

```
✗ YANLIŞ:
  html = input.replace(/<script>/gi, '')
  // Bypass: <scr<script>ipt>alert(1)</script>

✓ DOĞRU:
  clean = sanitizer.clean(input, allowedTags: ['p','b','i','a','ul','li'])
  // .NET: HtmlSanitizer | PHP: HTMLPurifier | JS: DOMPurify
```

Kontrol:
- [ ] Sunucu tarafında beyaz liste tabanlı sanitizer mevcut
- [ ] Event handler attribute'ları (onclick, onerror) engelleniyor
- [ ] İstemci tarafı sanitizasyon tek başına güvenilmiyor
