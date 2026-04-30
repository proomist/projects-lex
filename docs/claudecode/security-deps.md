# [DEP] Bağımlılık ve Supply Chain Güvenliği Kuralları

## [DEP-01] Bağımlılık Tarama ve Güncelleme — YÜKSEK
Tüm bağımlılıklar düzenli güvenlik taramasından geçirilmelidir.

```
✗ YANLIŞ:
  "lodash": "^3.10.0"   // 2015, bilinen prototype pollution açığı
  // Güvenlik taraması yok, lock dosyası yok

✓ DOĞRU:
  // CI/CD pipeline'da:
  npm audit / composer audit / dotnet list package --vulnerable
  // Renovate veya Dependabot ile otomatik güncelleme PR'ları
  // Lock dosyası (package-lock.json, composer.lock) commit'li
```

Kontrol:
- [ ] Vulnerability scan CI'da çalışıyor
- [ ] Kritik açıklar 48 saat içinde yamanıyor
- [ ] Lock dosyaları versiyon kontrolünde
- [ ] Typosquatting kontrolü (paket adı doğrulama)

## [DEP-02] Container/Image Güvenliği — ORTA (Docker kullanılıyorsa)
- Minimal base image (alpine, distroless)
- Root olmayan kullanıcı ile çalışma
- Trivy/Snyk ile imaj taraması CI'da
- Secrets imaj içinde değil, runtime'da enjekte
