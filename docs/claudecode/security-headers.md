# [HDR] HTTP Header Güvenliği Kuralları

## [HDR-01] Güvenlik Header'ları — YÜKSEK
Tüm HTTP yanıtlarında aşağıdaki header'lar bulunmalıdır:

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{random}'; frame-ancestors 'none'
Strict-Transport-Security: max-age=63072000; includeSubDomains; preload
```

Kaldırılması gereken header'lar:
- `Server` → kaldır veya maskele
- `X-Powered-By` → kaldır
- `X-AspNet-Version` → kaldır

Kontrol:
- [ ] Tüm güvenlik header'ları tanımlı
- [ ] Server/X-Powered-By kaldırılmış
- [ ] HSTS aktif, uzun max-age

## [HDR-02] Cache-Control (Hassas Veri) — ORTA
Login, profil, finansal endpoint'lerde:

```
Cache-Control: no-store, no-cache, must-revalidate, private
Pragma: no-cache
```

CDN'de hassas veri cache'lenmemeli.
