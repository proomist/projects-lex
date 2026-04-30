# [FILE] Dosya Yükleme ve Path Traversal Kuralları

## [FILE-01] Dosya Yükleme Güvenliği — KRİTİK
Yüklenen dosyalar: MIME tipi (magic bytes), uzantı beyaz listesi, boyut limiti ile doğrulanmalı. Dosya adı UUID ile yeniden adlandırılmalı. Web root dışında saklanmalı.

```
✗ YANLIŞ:
  path = '/uploads/' + req.file.originalname   // orijinal ad + web root içi

✓ DOĞRU:
  newName = uuid4() + '.' + allowedExtension
  path = '/var/data/uploads/' + newName        // web root dışı
  // Magic bytes ile gerçek MIME doğrula
  // Nginx: location /uploads/ { deny all; }
  // Resim dosyaları re-encode edilmeli (EXIF/metadata temizliği)
```

Kontrol:
- [ ] MIME tipi magic bytes ile doğrulanıyor
- [ ] Uzantı beyaz listesi uygulanmış
- [ ] Dosya adı UUID ile yeniden adlandırılıyor
- [ ] Web root dışında saklanıyor
- [ ] Upload dizininde script çalıştırma engellenmiş
- [ ] Maksimum boyut limiti var

## [FILE-02] Path Traversal Önleme — KRİTİK
Dosya okuma/yazma işlemlerinde canonical path doğrulaması zorunludur.

```
✗ YANLIŞ:
  file = open('/reports/' + req.query.filename)
  // Saldırı: ?filename=../../../etc/passwd

✓ DOĞRU:
  basePath = realpath('/reports/')
  fullPath = realpath('/reports/' + sanitizedFilename)
  if (!fullPath.startsWith(basePath)) deny()
  // Dosya adında sadece alfanumerik ve nokta izin ver
```

Kontrol:
- [ ] Canonical path kontrolü uygulanmış
- [ ] `../` dizin geçişi engelleniyor
- [ ] Symlink takibi kısıtlanmış
