# [CSRF/CORS] İstek Güvenliği Kuralları

## [CSRF-01] CSRF Token Zorunluluğu — KRİTİK
State-changing işlemler (POST, PUT, DELETE) için CSRF koruması zorunludur.

```
✗ YANLIŞ:
  <form action='/transfer' method='POST'>
    <input name='amount' value='1000'>
  </form>
  // Saldırgan kendi sitesinden bu formu gönderebilir

✓ DOĞRU:
  // Server-rendered: Synchronizer Token Pattern
  <input type='hidden' name='_csrf' value='cryptoRandomToken'>

  // SPA: SameSite=Strict cookie + X-CSRF-Token custom header
  // Double Submit Cookie pattern alternatif
```

## [CORS-01] CORS Yapılandırması — YÜKSEK
Origin beyaz listesi ile tam eşleşme kontrolü yapılmalıdır.

```
✗ YANLIŞ:
  Access-Control-Allow-Origin: *
  // veya: Origin header'ını reflect etme
  response.setHeader('ACAO', request.getHeader('Origin'))

✓ DOĞRU:
  allowedOrigins = ['https://app.proomist.com', 'https://admin.proomist.com']
  if (request.origin in allowedOrigins)
    response.setHeader('ACAO', request.origin)
    response.setHeader('ACAC', 'true')
```

Kontrol:
- [ ] CSRF token veya SameSite cookie uygulanmış
- [ ] CORS origin beyaz listesi tanımlı
- [ ] Wildcard (*) kullanılmıyor
- [ ] Origin reflection yapılmıyor
