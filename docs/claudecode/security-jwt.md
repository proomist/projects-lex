# [JWT] JWT ve Token Güvenliği Kuralları

## [JWT-01] Payload İçeriği Sınırlandırma — KRİTİK
JWT payload Base64URL encode'dur, şifreleme DEĞİLDİR. Decode edildiğinde tüm içerik okunabilir. PII kesinlikle payload'da olmamalıdır.

```
✗ YANLIŞ:
  payload = {
    userId: 123, email: 'a@b.com', password: 'hash',
    tcKimlik: '11111111110', cardNumber: '4111...'
  }

✓ DOĞRU:
  payload = {
    sub: 123,           // kullanıcı ID
    role: 'admin',      // rol
    iss: 'proomist.com',// issuer
    exp: 1709251200,    // expiry
    iat: 1709164800,    // issued at
    jti: 'unique-id'    // token ID (revocation için)
  }
```

Kontrol:
- [ ] JWT payload'da şifre, PII veya hassas veri yok
- [ ] Token decode edilip içerik gözden geçirildi

## [JWT-02] Algoritma Sabitleme ve None Attack Önleme — KRİTİK
Kabul edilen algoritma sunucu tarafında sabit tanımlanmalıdır. Token header'ındaki `alg` değerine güvenilmemelidir.

```
✗ YANLIŞ:
  verify(token, secret)              // alg token'dan okunuyor
  // alg: "none" kabul ediliyor      → imzasız token geçerli sayılır

✓ DOĞRU:
  verify(token, secret, algorithms=['RS256'])
  // "none" kesinlikle reddedilir
  // Asimetrik anahtar varsa HS256 düşürme engellenir
```

Kontrol:
- [ ] Kabul edilen algoritma açıkça belirtilmiş
- [ ] `alg: none` reddediliyor
- [ ] RS256 → HS256 downgrade engellenmiş

## [JWT-03] JWE ile Payload Şifreleme — YÜKSEK
Payload'da herhangi bir kullanıcı bilgisi taşınıyorsa, JWS'ye ek olarak JWE (şifreli JWT) kullanılmalıdır. Alternatif: opaque token + sunucu taraflı session store.

```
✗ YANLIŞ:
  // Sadece JWS — Base64 decode ile payload okunabilir
  eyJhbGciOiJSUzI1NiJ9.eyJzdWIiOiIxMjMiLCJyb2xlIjoiYWRtaW4ifQ.sig

✓ DOĞRU:
  // Nested JWT: JWS (imza) → JWE (şifreleme)
  // veya: Opaque token (UUID) + Redis/DB session store
  // JWE: RSA-OAEP + A256GCM
```

## [JWT-04] Token Ömrü ve Rotasyon — YÜKSEK
Access token ≤15 dakika. Refresh token kullanıldığında rotasyon yapılmalı (eski iptal, yeni çift döndür).

```
✗ YANLIŞ:
  token = sign(payload, secret, { expiresIn: '30d' })  // 30 gün access token

✓ DOĞRU:
  accessToken  = sign(payload, secret, { expiresIn: '15m' })
  refreshToken = sign(payload, refreshSecret, { expiresIn: '7d' })
  // Refresh kullanıldığında: eski refresh → blacklist, yeni çift döndür
```

Kontrol:
- [ ] Access token ≤15 dakika
- [ ] Refresh token rotasyonu uygulanmış
- [ ] Token iptal listesi (blacklist) mevcut
- [ ] Logout'ta tüm tokenlar iptal ediliyor

## [JWT-05] Token Saklama ve İletim — YÜKSEK
JWT istemcide localStorage'da saklanmamalıdır (XSS ile çalınabilir).

```
✗ YANLIŞ:
  localStorage.setItem('token', jwt)     // XSS ile erişilebilir

✓ DOĞRU:
  Set-Cookie: token=jwt; HttpOnly; Secure; SameSite=Strict; Path=/
  // Flutter: flutter_secure_storage (Keychain/KeyStore)
  // SPA: Authorization header + memory-only (closure pattern)
```

Kontrol:
- [ ] JWT localStorage'da saklanmıyor
- [ ] Cookie: HttpOnly, Secure, SameSite=Strict
- [ ] Mobil: Keychain (iOS) / KeyStore (Android)
- [ ] Token sadece HTTPS üzerinden iletiliyor
