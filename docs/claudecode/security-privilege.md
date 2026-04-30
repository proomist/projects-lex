# [PRIV] Privilege Escalation Önleme Kuralları

## [PRIV-01] Her İstekte Yetkilendirme Kontrolü — KRİTİK
Her endpoint'te authentication + authorization kontrol edilmelidir. IDOR (Insecure Direct Object Reference) dahildir.

```
✗ YANLIŞ:
  app.get('/api/users/:id', authMiddleware, (req, res) => {
    return db.getUser(req.params.id)     // herhangi birinin verisini okuyabilir
  })

✓ DOĞRU:
  app.get('/api/users/:id', authMiddleware, (req, res) => {
    if (req.user.id !== req.params.id && req.user.role !== 'admin')
      return res.status(403)
    return db.getUser(req.params.id)
  })
```

Kontrol:
- [ ] Her endpoint'te authorization kontrolü var
- [ ] IDOR testi yapılmış (başka kullanıcının verisine erişim denenmiş)
- [ ] ID parametreleri tahmin edilemez (UUID v4 tercih)
- [ ] Admin endpoint'leri ayrı route grubunda

## [PRIV-02] Role Manipulation Önleme — KRİTİK
Rol bilgisi istemciden gelen veriye (cookie, hidden field, request body) göre belirlenmemelidir.

```
✗ YANLIŞ:
  user.role = req.body.role     // kullanıcı kendini admin yapabilir

✓ DOĞRU:
  user = db.getUser(authenticatedUserId)
  if (user.role !== requiredRole) deny()
  // Rol değişikliği sadece admin endpoint'inden + audit log
```

## [PRIV-03] Fonksiyon Seviyesi Erişim Kontrolü — YÜKSEK
Gizli/belgelenmemiş endpoint'ler de yetkilendirmeden geçmelidir. Security through obscurity kabul edilmez.

```
✗ YANLIŞ:
  app.post('/api/internal/reset-all-passwords', handler)   // URL'yi bilen çağırabilir

✓ DOĞRU:
  app.post('/api/admin/reset-passwords',
    authMiddleware, requireRole('superadmin'), auditLog, handler
  )
```

Kontrol:
- [ ] Tüm endpoint'ler (gizli dahil) yetki kontrolünden geçiyor
- [ ] Belgelenmemiş endpoint'ler envantere alınmış
- [ ] Endpoint discovery testi yapılmış
