## 1) Clients Smoke Test

### Create
1. `/clients` ekranında yeni kayıt aç.
2. **Bireysel**:
   - `first_name`, `last_name`, `tc_no`, `phone`, `email`, `city`, `district`, `address`
3. Kaydet.
4. Beklenen:
   - Listede isim görünmeli.
   - Telefon/e-posta listede görünmeli.
   - Düzenle açınca alanlar dolu gelmeli.

### Update
1. Kayıt düzenle.
2. `phone/email/city/district/address` değiştir.
3. Kaydet ve modal kapat/aç.
4. Beklenen:
   - Yeni değerler listede ve detayda görünmeli.
   - `tc_no` / `tax_no` boşalmamalı (silmediysen).

### Delete (soft)
1. Kaydı sil.
2. Beklenen:
   - Liste dışına düşmeli.
   - Hard delete olmamalı.

---

## 2) Cases Smoke Test

### Create
1. `/cases` ekranında yeni dosya oluştur:
   - `client_id`, `client_role`, `case_type=Hukuk`, `subject`, `base_no`, `opponent_name`, `opponent_lawyer`, `status=Aktif`
2. Kaydet.
3. Beklenen:
   - Listeye düşmeli.
   - `subject`, `base_no`, `opponent_name` görünmeli.

### Get/Reload
1. Satırı düzenle.
2. Beklenen:
   - `client_role`, `base_no`, `subject`, `status` doğru dolu gelsin.

### Update
1. `case_type` → `İcra`, `status` → `Sonuçlandı`, `opponent_*` değiştir.
2. Kaydet.
3. Beklenen:
   - Liste statüsü/alanları güncellensin.
   - Type filtresiyle doğru süzülsün.

### Delete
1. Sil.
2. Beklenen:
   - Arşivlenmiş (soft delete) davranışı.

---

## 3) Hearings Smoke Test

### Create
1. `/hearings` ekranında:
   - `case_id`, `hearing_date`, `hearing_time`, `description`, `status=Yapıldı`
2. Kaydet.
3. Beklenen:
   - Kayıt listede görünmeli.
   - Status `Yapıldı` olarak görünmeli.

### Update
1. Düzenle, açıklama ve status değiştir.
2. Kaydet.
3. Beklenen:
   - `description` alanı geri yüklemede korunmalı.

### Delete
1. Sil.
2. Beklenen:
   - İptal/soft delete davranışı.

---

## 4) Tasks Smoke Test

### Create
1. `/tasks` ekranında görev oluştur:
   - `title`, `task_type`, `assigned_to`
   - `case_id` boş bırak (bağımsız görev testi)
2. Kaydet.
3. Beklenen:
   - Artık “bağlı olmak zorunda” hatası vermemeli.

### Update
1. Status `Tamamlandı` yap.
2. Sonra `Devam Ediyor` yap.
3. Beklenen:
   - Toggle/update sorunsuz çalışmalı.

### UI Data
1. Sorumlu dropdown’ında kullanıcı yanında title görünmeli.
2. Beklenen:
   - `(u.title)` görünmeli, boş rol problemi olmamalı.

---

## 5) Finance Smoke Test

### Create
1. `/finance` ekranında kayıt oluştur:
   - `transaction_type=Alacak`, `category`, `client_id`, `amount`, `transaction_date`, `due_date`, `status`
2. Kaydet.
3. Beklenen:
   - Listeye düşmeli.

### Update
1. `client_id` veya `case_id` değiştir (mümkünse).
2. Kaydet.
3. Beklenen:
   - Geçersiz case-client eşleşmesinde hata dönmeli.
   - Geçerli eşleşmede güncellenmeli.

### List/Get
1. Listede client type doğru görünmeli (`Bireysel/Kurumsal`).
2. Detay aç.
3. Beklenen:
   - UI type etiketleri uyumlu.

### Delete
1. Sil.
2. Beklenen:
   - İptal/soft delete.

---

## 6) Users Smoke Test

### Create
1. [/users](cci:9://file:///users:0:0-0:0) ekranında yeni kullanıcı:
   - `username`, `email`, `password`, `title`, `status=Askıda`
2. Kaydet.
3. Beklenen:
   - Status create sırasında korunmalı.

### Update
1. Aynı kullanıcıda `username` veya `email` değiştir.
2. Kaydet.
3. Beklenen:
   - Güncellensin.
   - Çakışan username/email’de 409 hatası dönsün.

### Delete
1. Kurucu ortak olmayan kullanıcıyı sil.
2. Beklenen:
   - Soft delete.

