# [API] API Güvenliği ve Rate Limiting Kuralları

## [API-01] Rate Limiting — YÜKSEK
Tüm endpoint'lere rate limiting uygulanmalıdır. Hassas endpoint'lere ayrı, daha sıkı limitler.

```
✗ YANLIŞ:
  app.post('/api/login', loginHandler)   // sınırsız deneme

✓ DOĞRU:
  app.use('/api/', globalRateLimit(1000/dakika))
  app.use('/api/login', strictRateLimit(5/dakika))
  app.use('/api/register', strictRateLimit(3/dakika))
  // 429 yanıtında Retry-After header
  // IP + kullanıcı bazlı bileşik limit
```

Kontrol:
- [ ] Global API rate limit tanımlı
- [ ] Login/register/reset için ayrı sıkı limit
- [ ] 429 yanıtında Retry-After header
- [ ] X-Forwarded-For spoofing engellenmiş (trusted proxy)

## [API-02] GraphQL Güvenliği — YÜKSEK (GraphQL kullanılıyorsa)
Query derinliği ve karmaşıklığı sınırlanmalı, production'da introspection kapalı olmalıdır.

```
✗ YANLIŞ:
  // Sınırsız nested query + introspection açık

✓ DOĞRU:
  validationRules: [depthLimit(5), costAnalysis({max: 1000})]
  // Production: introspection kapalı
  // Field-level authorization her resolver'da
```

## [API-03] API Yanıt Güvenliği — ORTA
- Hata yanıtlarında stack trace, internal path, DB bilgisi yok
- Pagination ile veri miktarı sınırlı (max 100 kayıt/sayfa)
- API versiyonlama stratejisi uygulanmış
- Belgelenmemiş endpoint'ler 404 döndürüyor
