# [CRYPTO] Kriptografi ve Veri Koruma Kuralları

## [CRYPTO-01] Şifreleme Standartları — KRİTİK
Simetrik: AES-256-GCM (authenticated encryption). Asimetrik: RSA-2048+ veya Ed25519.

```
✗ YANLIŞ:
  cipher = DES.encrypt(data, key)          // zayıf algoritma
  cipher = AES.encrypt(data, key, ECB)     // ECB modu pattern sızdırır

✓ DOĞRU:
  cipher = AES_GCM.encrypt(plaintext, key_256bit, nonce_96bit)
  // Nonce her işlemde benzersiz
  // Anahtar: env variable, vault veya KMS — kodda HARDCODE DEĞİL
```

Kontrol:
- [ ] AES-256-GCM veya ChaCha20-Poly1305 kullanılıyor
- [ ] ECB modu kullanılmıyor
- [ ] IV/nonce her işlemde benzersiz
- [ ] Anahtar kodda hardcode değil
- [ ] DES, 3DES, RC4, Blowfish kullanılmıyor

## [CRYPTO-02] TLS Yapılandırması — KRİTİK
TLS 1.2 minimum, TLS 1.3 tercih. HSTS zorunlu.

```
✗ YANLIŞ:
  ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
  ssl_ciphers ALL;

✓ DOĞRU:
  ssl_protocols TLSv1.2 TLSv1.3;
  ssl_ciphers 'ECDHE-AESGCM-AES256:ECDHE-CHACHA20';
  ssl_prefer_server_ciphers on;
  add_header Strict-Transport-Security 'max-age=63072000; includeSubDomains; preload';
```

## [CRYPTO-03] Hassas Veri Şifreleme (At Rest) — YÜKSEK
Veritabanında PII, finansal bilgi uygulama katmanında şifrelenmelidir (envelope encryption).

```
✗ YANLIŞ:
  INSERT INTO users (tc_kimlik) VALUES ('11111111110')   // açık metin

✓ DOĞRU:
  encryptedTcKimlik = AES_GCM.encrypt(tcKimlik, dataKey)
  INSERT INTO users (tc_kimlik_enc) VALUES (encryptedBlob)
  // dataKey → master key (KMS) ile şifreli
  // Gösterimde maskeleme: ***XXXXXX10
```
