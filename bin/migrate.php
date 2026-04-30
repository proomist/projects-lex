<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$dbName = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];

echo "=== Proomist Lex - Veritabanı Migration ===\n\n";

try {
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // 1. Veritabanını oluştur (yoksa)
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[✓] Veritabanı '$dbName' kontrol edildi/oluşturuldu.\n";

    $pdo->exec("USE `$dbName`");

    // 2. Schema tabloları oluştur
    $schemaFile = __DIR__ . '/../database/schema.sql';
    if (!file_exists($schemaFile)) {
        die("[✗] Schema dosyası bulunamadı: $schemaFile\n");
    }

    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);
    echo "[✓] Tablolar başarıyla oluşturuldu.\n";

    // 3. Trigger'ları ayrı ayrı çalıştır (BEGIN...END blokları PDO exec ile sorun yaratabilir)
    $triggerFile = __DIR__ . '/../database/triggers.sql';
    if (file_exists($triggerFile)) {
        $triggerSql = file_get_contents($triggerFile);

        // Her SQL ifadesini ayrı çalıştır: DROP ve CREATE TRIGGER satırlarını ayır
        // Önce DROP komutlarını çalıştır
        preg_match_all('/DROP\s+TRIGGER\s+IF\s+EXISTS\s+\w+;/i', $triggerSql, $drops);
        foreach ($drops[0] as $dropStmt) {
            $pdo->exec($dropStmt);
        }

        // Sonra CREATE TRIGGER bloklarını çalıştır
        preg_match_all('/CREATE\s+TRIGGER.*?END;/is', $triggerSql, $creates);
        foreach ($creates[0] as $createStmt) {
            $pdo->exec($createStmt);
        }

        echo "[✓] Trigger'lar başarıyla oluşturuldu.\n";
    } else {
        echo "[~] Trigger dosyası bulunamadı, atlanıyor.\n";
    }

    // 4. Seed: Varsayılan sistem ayarlarını ekle (yoksa)
    $seedSettings = [
        'app_name'     => 'Proomist Lex',
        'company_name' => '',
        'tax_office'   => '',
        'tax_number'   => '',
        'address'      => '',
        'phone'        => '',
        'email'        => '',
        'currency'     => 'TRY',
        'timezone'     => 'Europe/Istanbul',
    ];

    $stmt = $pdo->prepare(
        "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (:key, :val)"
    );
    foreach ($seedSettings as $key => $val) {
        $stmt->execute(['key' => $key, 'val' => $val]);
    }
    echo "[✓] Varsayılan sistem ayarları yüklendi.\n";

    // 5. Seed: Lookup (Dinamik ENUM) varsayılan değerlerini ekle
    $lookupStmt = $pdo->prepare(
        "INSERT IGNORE INTO lookup_values (group_key, value, label, sort_order, is_active, is_system) VALUES (:gk, :val, :lbl, :so, 1, 1)"
    );

    $lookupSeeds = [
        'user_titles' => [
            ['Sistem Yöneticisi', 'Sistem Yöneticisi', 1],
            ['Kurucu Ortak', 'Kurucu Ortak', 2],
            ['Ortak Avukat', 'Ortak Avukat', 3],
            ['Avukat', 'Avukat', 4],
            ['Stajyer', 'Stajyer', 5],
            ['Sekreter', 'Sekreter / Asistan', 6],
            ['Muhasebeci', 'Muhasebeci', 7],
        ],
        'case_types' => [
            ['Dava', 'Dava', 1],
            ['İcra', 'İcra', 2],
            ['Danışmanlık', 'Danışmanlık', 3],
            ['Arabuluculuk', 'Arabuluculuk', 4],
        ],
        'client_positions' => [
            ['Davacı', 'Davacı', 1],
            ['Davalı', 'Davalı', 2],
            ['Alacaklı', 'Alacaklı', 3],
            ['Borçlu', 'Borçlu', 4],
            ['Şüpheli', 'Şüpheli', 5],
            ['Sanık', 'Sanık', 6],
            ['Mağdur', 'Mağdur', 7],
            ['Katılan', 'Katılan', 8],
            ['Danışan', 'Danışan', 9],
        ],
        'closing_types' => [
            ['Kazanıldı (Tam)', 'Kazanıldı (Tam)', 1],
            ['Kazanıldı (Kısmi)', 'Kazanıldı (Kısmi)', 2],
            ['Kaybedildi', 'Kaybedildi', 3],
            ['Sulh', 'Sulh', 4],
            ['Feragat', 'Feragat', 5],
            ['Kabul', 'Kabul', 6],
            ['Düşme', 'Düşme', 7],
            ['Görevsizlik/Yetkisizlik', 'Görevsizlik / Yetkisizlik', 8],
            ['Birleştirme', 'Birleştirme', 9],
        ],
        'hearing_types' => [
            ['Ön İnceleme', 'Ön İnceleme', 1],
            ['Tahkikat', 'Tahkikat', 2],
            ['Karar', 'Karar', 3],
            ['Keşif', 'Keşif', 4],
            ['Bilirkişi', 'Bilirkişi', 5],
            ['Diğer', 'Diğer', 6],
        ],
        'payment_methods' => [
            ['Nakit', 'Nakit', 1],
            ['Havale/EFT', 'Havale / EFT', 2],
            ['Kredi Kartı', 'Kredi Kartı', 3],
            ['Çek', 'Çek', 4],
            ['Diğer', 'Diğer', 5],
        ],
        'contact_types' => [
            ['Telefon', 'Telefon', 1],
            ['E-posta', 'E-posta', 2],
            ['Adres', 'Adres', 3],
        ],
        'task_priorities' => [
            ['Düşük', 'Düşük', 1],
            ['Normal', 'Normal', 2],
            ['Yüksek', 'Yüksek', 3],
            ['Acil', 'Acil', 4],
        ],
        'document_types' => [
            ['Vekaletname', 'Vekaletname', 1],
            ['Dilekçe', 'Dilekçe', 2],
            ['Sözleşme', 'Sözleşme', 3],
            ['Mahkeme Kararı', 'Mahkeme Kararı', 4],
            ['Bilirkişi Raporu', 'Bilirkişi Raporu', 5],
            ['İcra Emri', 'İcra Emri', 6],
            ['Fatura/Makbuz', 'Fatura / Makbuz', 7],
            ['Kimlik Fotokopisi', 'Kimlik Fotokopisi', 8],
            ['Adres Belgesi', 'Adres Belgesi', 9],
            ['İmza Sirküleri', 'İmza Sirküleri', 10],
            ['Ticaret Sicil Gazetesi', 'Ticaret Sicil Gazetesi', 11],
            ['Diğer', 'Diğer', 12],
        ],
        'financial_categories' => [
            ['Vekalet Ücreti', 'Vekalet Ücreti', 1],
            ['Danışman Ücreti', 'Danışman Ücreti', 2],
            ['Harç', 'Harç', 3],
            ['Bilirkişi Ücreti', 'Bilirkişi Ücreti', 4],
            ['Tebligat', 'Tebligat', 5],
            ['KEP', 'KEP', 6],
            ['Personel Maaşı', 'Personel Maaşı', 7],
            ['Kira', 'Kira', 8],
            ['Fatura', 'Fatura', 9],
            ['Kırtasiye', 'Kırtasiye', 10],
            ['Ulaşım', 'Ulaşım', 11],
            ['Vergi/SGK', 'Vergi / SGK', 12],
            ['Diğer', 'Diğer', 13],
        ],
    ];

    $totalLookups = 0;
    foreach ($lookupSeeds as $group => $values) {
        foreach ($values as [$value, $label, $sortOrder]) {
            $lookupStmt->execute([
                'gk'  => $group,
                'val' => $value,
                'lbl' => $label,
                'so'  => $sortOrder,
            ]);
            $totalLookups++;
        }
    }
    echo "[✓] Lookup tanımları yüklendi ($totalLookups kayıt).\n";

    echo "\n=== Migration tamamlandı! ===\n";
    echo "Sonraki adım: php bin/create_admin.php\n";

} catch (PDOException $e) {
    die("\n[✗] Veritabanı hatası: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("\n[✗] Hata: " . $e->getMessage() . "\n");
}
