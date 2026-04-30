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

echo "=== Proomist Lex - Veritabanı Sıfırlama ===\n\n";
echo "⚠  DİKKAT: '$dbName' veritabanındaki TÜM veriler silinecektir!\n";

$confirm = readline("Devam etmek istiyor musunuz? (evet/hayır): ");
if (strtolower(trim($confirm)) !== 'evet') {
    die("İşlem iptal edildi.\n");
}

try {
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "\n[1/2] Veritabanı '$dbName' siliniyor...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");

    echo "[2/2] Veritabanı '$dbName' yeniden oluşturuluyor...\n";
    $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    echo "\n[✓] Veritabanı başarıyla sıfırlandı.\n";
    echo "\nŞimdi migration çalıştırın: php bin/migrate.php\n";

} catch (PDOException $e) {
    die("\n[✗] Veritabanı hatası: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("\n[✗] Hata: " . $e->getMessage() . "\n");
}
