## BÖLÜM 3: VERİ MODELİ ÖZETİ

### Ana Tablolar

| Tablo | Açıklama | Temel İlişkiler |
|---|---|---|
| Users | Kullanıcılar | → Roles |
| Roles | Roller | → Permissions |
| Permissions | Yetkiler | ← Roles |
| Clients | Müvekkiller | → Cases, Financials |
| Cases | Dosyalar | → Clients, Hearings, Documents, Tasks |
| Hearings | Duruşmalar | → Cases |
| Documents | Belgeler | → Cases |
| Tasks | Görevler | → Cases, Users |
| TaskAssignments | Görev Atamaları | → Tasks, Users |
| Financials | Mali Hareketler | → Clients, Cases |
| PaymentPlans | Ödeme Planları | → Clients, Financials |

### Ortak Alanlar (Tüm Tablolarda)

- `Id` (Primary Key)
- `CreatedAt` (Oluşturma tarihi)
- `CreatedBy` (Oluşturan kullanıcı)
- `UpdatedAt` (Güncelleme tarihi)
- `UpdatedBy` (Güncelleyen kullanıcı)
- `IsDeleted` (Soft delete)
- `DeletedAt` (Silinme tarihi)
