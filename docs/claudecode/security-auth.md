# [AUTH] Authentication ve Session Yönetimi Kuralları

## [AUTH-01] Parola Hash Algoritması — KRİTİK
Parolalar bcrypt (cost≥12) veya Argon2id ile hash'lenmelidir.

```
✗ YANLIŞ:
  hash = md5(password)
  hash = sha256(password + 'staticsalt')

✓ DOĞRU:
  hash = bcrypt(password, cost=12)
  hash = argon2id(password, memory=65536, iterations=3, parallelism=4)
```

Kontrol:
- [ ] bcrypt (cost≥12) veya Argon2id kullanılıyor
- [ ] Salt her kullanıcı için benzersiz ve otomatik
- [ ] Eski hash'ler login sırasında yeni algoritmaya migrate ediliyor

## [AUTH-02] Brute Force Koruması — YÜKSEK
Login endpoint'inde rate limiting + genel hata mesajı zorunludur.

```
✗ YANLIŞ:
  if (!auth) return 'Şifre hatalı'   // kullanıcı var mı bilgisi sızar

✓ DOĞRU:
  rateLimiter.check(ip + username)    // 5/dk limit
  if (!auth) return 'Kullanıcı adı veya şifre hatalı'
  // 5 başarısız → 15dk kilit + CAPTCHA
```

Kontrol:
- [ ] Login rate limiting uygulanmış
- [ ] Genel hata mesajı veriliyor (enumeration koruması)
- [ ] Progresif gecikme veya kilit mevcut
- [ ] CAPTCHA veya bot koruması aktif

## [AUTH-03] Multi-Factor Authentication — YÜKSEK
Admin panelleri ve hassas işlemler için MFA zorunludur. TOTP (Google Authenticator) tercih edilmelidir. SMS-based MFA son çaredir (SIM swap riski).

## [AUTH-04] Session Güvenliği — YÜKSEK
Login sonrası session ID yenilenmelidir (fixation koruması). İnaktivite timeout: 30dk, mutlak timeout: 8 saat.

```
✗ YANLIŞ:
  session['user'] = user   // aynı session ID devam ediyor

✓ DOĞRU:
  session.regenerate()
  session['user'] = user
  // Cookie: HttpOnly, Secure, SameSite=Strict
```

Kontrol:
- [ ] Login sonrası session ID yenileniyor
- [ ] Session cookie: HttpOnly, Secure, SameSite
- [ ] İnaktivite ve mutlak timeout uygulanmış
- [ ] Eşzamanlı oturum sayısı sınırlı
