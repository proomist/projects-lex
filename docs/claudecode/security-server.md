# [SRV] Sunucu ve Altyapı Sertleştirme Kuralları (AlmaLinux)

## [SRV-01] İşletim Sistemi Sertleştirme — YÜKSEK

```
✗ YANLIŞ:
  # node /var/www/app.js              → root olarak çalışma
  # systemctl status bluetooth cups   → gereksiz servisler açık

✓ DOĞRU:
  useradd -r -s /sbin/nologin appuser
  systemctl disable bluetooth cups avahi-daemon
  dnf install dnf-automatic           → otomatik güvenlik güncellemesi
  # SELinux enforcing modda
```

Kontrol:
- [ ] Uygulama root olmayan kullanıcı ile çalışıyor
- [ ] Gereksiz servisler devre dışı
- [ ] dnf-automatic yapılandırılmış
- [ ] SELinux enforcing modda
- [ ] Firewalld aktif, sadece gerekli portlar açık
- [ ] SSH: root login kapalı, key-based auth, port değiştirilmiş

## [SRV-02] Web Sunucusu Sertleştirme — YÜKSEK

```
✗ YANLIŞ:
  server_tokens on;
  autoindex on;

✓ DOĞRU:
  server_tokens off;
  autoindex off;
  modsecurity on;
  # .git, .env, .htaccess, backup dosyaları erişime kapalı
  # HTTP → HTTPS yönlendirme zorunlu
```

Kontrol:
- [ ] Server version gizli
- [ ] Dizin listeleme kapalı
- [ ] ModSecurity aktif ve kuralları güncel
- [ ] .git/.env/.htaccess erişime kapalı
- [ ] Cloudflare WAF yapılandırılmış (varsa)

## [SRV-03] Veritabanı Sertleştirme — YÜKSEK
- Veritabanı sadece localhost veya VPN üzerinden erişilebilir
- Uygulama kullanıcısı minimum yetkili (CRUD only, CREATE/DROP yok)
- Varsayılan parolalar değiştirilmiş
- Bağlantı SSL zorunlu (sslmode=verify-full)
- Yedekleme şifreli ve düzenli test ediliyor
