# [SQLi] SQL Injection Kuralları

## [SQLi-01] Parameterized Query Zorunluluğu — KRİTİK
Tüm SQL sorguları prepared statement / parameterized query kullanmalıdır.

```
✗ YANLIŞ:
  query = "SELECT * FROM users WHERE id = '" + userId + "'"
  $sql = "SELECT * FROM users WHERE name = '$name'"

✓ DOĞRU:
  query = "SELECT * FROM users WHERE id = @userId"
  $stmt = $pdo->prepare("SELECT * FROM users WHERE name = :name")
  context.Users.Where(u => u.Id == userId)
```

Kontrol:
- [ ] Tüm SQL sorguları prepared statement kullanıyor
- [ ] ORM raw query noktaları parameterized
- [ ] String concatenation ile SQL oluşturan kod yok
- [ ] Stored procedure çağrıları da parametrik

## [SQLi-02] Dinamik Tablo/Kolon Adı — KRİTİK
Tablo veya kolon adları parametre olarak bağlanamaz. Beyaz liste (allowlist) zorunludur.

```
✗ YANLIŞ:
  query = "SELECT * FROM " + tableName

✓ DOĞRU:
  allowedTables = ["users", "orders", "products"]
  if tableName not in allowedTables → reject
```

## [SQLi-03] ORM Raw Query Güvenliği — YÜKSEK
`FromSqlRaw`, `DB::raw`, `$wpdb->query` gibi raw query fonksiyonlarında parametre bağlama zorunludur. LIKE sorgularında `%` ve `_` escape edilmelidir.

```
✗ YANLIŞ:
  context.Users.FromSqlRaw($"SELECT * FROM Users WHERE Name = '{name}'")
  DB::select("SELECT * FROM users WHERE name = '$name'")

✓ DOĞRU:
  context.Users.FromSqlRaw("SELECT * FROM Users WHERE Name = {0}", name)
  DB::select("SELECT * FROM users WHERE name = ?", [$name])
```

## [SQLi-04] Hata Mesajı Gizleme — YÜKSEK
Production ortamında SQL hata detayları (tablo adı, kolon bilgisi, syntax hatası) kullanıcıya gösterilmez. Genel mesaj döndürülür, detay sadece log'a yazılır.

```
✗ YANLIŞ:
  catch (ex) { return Response(ex.Message); }

✓ DOĞRU:
  catch (ex) { logger.Error(ex); return Response("Bir hata oluştu.", 500); }
```

Kontrol:
- [ ] Production'da debug mode kapalı
- [ ] Hata yanıtlarında SQL detayı yok
- [ ] `'` girdisi ile test edildi, bilgi sızmıyor
