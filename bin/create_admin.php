<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Repository\Database;
use App\Helper\AuthHelper;

// Load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$dbName = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage() . "\n");
}

echo "Proomist Lex - İlk Sistem Yöneticisi Hesabı Oluşturma Aracı\n";
echo "------------------------------------------------------------------\n";

$username = readline("Kullanıcı Adı (örn: proomist): ");
$password = readline("Şifre: ");
$firstName = readline("Ad: ");
$lastName = readline("Soyad: ");
$email = readline("E-posta: ");

if (empty($username) || empty($password) || empty($firstName) || empty($lastName) || empty($email)) {
    die("Hata: Tüm alanların doldurulması zorunludur.\n");
}

// Check if user already exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = :u OR email = :e');
$stmt->execute(['u' => $username, 'e' => $email]);
if ($stmt->fetch()) {
    die("Hata: Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor.\n");
}

$passwordHash = AuthHelper::hashPassword($password);

try {
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, first_name, last_name, email, title, status) VALUES (:u, :p, :f, :l, :e, :t, :s)');
    $stmt->execute([
        'u' => $username,
        'p' => $passwordHash,
        'f' => $firstName,
        'l' => $lastName,
        'e' => $email,
        't' => 'Sistem Yöneticisi',
        's' => 'Aktif'
    ]);
    
    echo "\nBaşarılı! '$username' adlı Sistem Yöneticisi hesabı oluşturuldu.\n";
} catch (Exception $e) {
    die("\nHata oluştu: " . $e->getMessage() . "\n");
}
