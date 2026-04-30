# [LOG] Loglama, İzleme ve İhlal Tespiti Kuralları

## [LOG-01] Güvenlik Loglama Standartları — YÜKSEK
Loglanması gereken olaylar: başarılı/başarısız login, logout, parola değişikliği, yetkilendirme hataları, input validation hataları, admin işlemleri.

```
✗ YANLIŞ:
  logger.info('Login: user=' + user + ' pass=' + pass)   // şifre log'da!
  // veya: Hiç güvenlik log'u yok

✓ DOĞRU:
  logger.info('LOGIN_SUCCESS', { userId, ip, userAgent, timestamp })
  logger.warn('LOGIN_FAILED', { attemptedUser, ip, userAgent, timestamp })
  logger.warn('AUTH_DENIED', { userId, resource, action, ip })
  // ASLA: şifre, token, PII, kredi kartı log'da olmamalı
```

Kontrol:
- [ ] Authentication olayları loglanıyor
- [ ] Yetkilendirme hataları loglanıyor
- [ ] Admin işlemleri audit log'da
- [ ] Log'larda şifre/token/PII yok
- [ ] Log'lar merkezi sisteme gönderiliyor (tamper-proof)
- [ ] Log saklama süresi KVKK ile uyumlu

## [LOG-02] Anomali Tespiti — ORTA
Alarm kuralları:
- 5 dakikada 10+ başarısız login → Alarm
- Admin endpoint'e non-admin erişim denemesi → Alarm
- Olağandışı saatte yüksek API trafiği → Alarm
- Bildirim kanalı: Telegram bot / Slack / Email
