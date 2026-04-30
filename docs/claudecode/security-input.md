# [INP] Input Validation ve Command Injection Kuralları

## [INP-01] Sunucu Taraflı Doğrulama Zorunluluğu — KRİTİK
Tüm girdiler sunucu tarafında doğrulanmalıdır. İstemci doğrulaması yalnızca UX amaçlıdır.

```
✗ YANLIŞ:
  if (form.email.match(regex)) submit()   // sadece JS doğrulama, sunucuda kontrol yok

✓ DOĞRU:
  validate(input, {
    email: [required, email, maxLength(255)],
    age:   [required, integer, between(0, 150)],
    name:  [required, string, maxLength(100), noHtml]
  })
  // + beklenmeyen alanları reddet (mass assignment koruması)
```

Kontrol:
- [ ] Her endpoint sunucu tarafında doğrulama yapıyor
- [ ] Tip, uzunluk, format, değer aralığı kontrol ediliyor
- [ ] Beklenmeyen alanlar (mass assignment) reddediliyor
- [ ] Content-Type header doğrulanıyor

## [INP-02] OS Command Injection Önleme — KRİTİK
Kullanıcı girdisi asla doğrudan shell komutuna dahil edilmemelidir.

```
✗ YANLIŞ:
  exec("ping " + userInput)          // userInput: "8.8.8.8; rm -rf /"
  system("convert " . $filename)     // PHP

✓ DOĞRU:
  execFile('ping', ['-c', '4', validatedIp], {shell: false})
  // PHP: escapeshellarg($filename) + parametre dizisi
  // Tercih: shell yerine dil kütüphanesi/API kullan
```

Kontrol:
- [ ] Shell komutlarında kullanıcı girdisi yok
- [ ] Zorunlu yerlerde parametre dizisi + shell=false
- [ ] Shell yerine API/kütüphane tercih edilmiş

## [INP-03] NoSQL / LDAP / Template Injection — YÜKSEK
SQL dışındaki sorgu dillerinde de injection riski vardır.

```
✗ YANLIŞ — MongoDB:
  db.users.find({ user: req.body.user, pass: req.body.pass })
  // Saldırı: { "pass": { "$ne": "" } } → her şifreyi eşleştirir

✓ DOĞRU:
  if (typeof req.body.pass !== 'string') reject()
  db.users.find({ user: String(req.body.user), pass: String(req.body.pass) })
```

```
✗ YANLIŞ — Template (SSTI):
  template.render("Hello " + userInput)   // userInput: "{{7*7}}" → 49

✓ DOĞRU:
  template.render("Hello {{ name }}", {name: userInput})
  // Kullanıcı girdisi asla template kodu olarak işlenmemeli
```

Kontrol:
- [ ] MongoDB sorgularında input tip kontrolü yapılıyor
- [ ] $ne, $gt, $regex operatör injection engelleniyor
- [ ] Template engine'de sandbox mode aktif
- [ ] Kullanıcı girdisi template olarak değerlendirilmiyor
