# [KVKK] Kişisel Veri Koruma Kuralları

## [KVKK-01] Veri Minimizasyonu — KRİTİK
Sadece gerekli kişisel veri toplanmalıdır. Veri envanteri oluşturulmalıdır.

```
✗ YANLIŞ:
  registerForm: ad, soyad, TC, anne kızlık soyadı, kan grubu, boy, kilo
  // Amaç: sadece hesap oluşturma — gereksiz veri toplama

✓ DOĞRU:
  registerForm: ad, email, şifre          // sadece hesap için gerekli
  // VERBİS kaydı güncel
  // Aydınlatma metni her formda
  // Saklama süreleri tanımlanmış + otomatik silme
```

Kontrol:
- [ ] Kişisel veri envanteri oluşturulmuş
- [ ] VERBİS kaydı güncel
- [ ] Aydınlatma metni tüm toplama noktalarında
- [ ] Açık rıza mekanizması uygulanmış
- [ ] Saklama süreleri tanımlı + otomatik silme/anonimleştirme

## [KVKK-02] Veri Sızıntısı Önleme — KRİTİK
API yanıtlarında sadece gerekli alanlar döndürülmelidir (DTO pattern).

```
✗ YANLIŞ:
  return user;   // şifre hash, TC kimlik, adres — her şey dahil

✓ DOĞRU:
  return { id: user.id, name: user.name, role: user.role }
  // TC kimlik gösterimde maskeleme: ***XXXXXX10
  // Error response'larda kişisel veri sızmıyor
```

Kontrol:
- [ ] API yanıtlarında DTO/ViewModel kullanılıyor
- [ ] Hassas alanlar API'da döndürülmüyor
- [ ] PII maskeleme uygulanmış
