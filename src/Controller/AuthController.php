<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use App\Repository\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Exception;

class AuthController
{
    private UserService $userService;
    private PDO $db;

    public function __construct(UserService $userService, Database $database)
    {
        $this->userService = $userService;
        $this->db = $database->getConnection();
    }

    public function login(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();

        // Validasyon
        $v = new Validator($data);
        $v->rule('required', ['username', 'password']);

        if (!$v->validate()) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Lütfen kullanıcı adı ve şifre alanlarını doldurun.',
                'errors' => $v->errors()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';

        try {
            $result = $this->userService->login($data['username'], $data['password'], $ipAddress);

            // Cookie tanımlama (OWASP standartlarına uygun, HttpOnly)
            $token = $result['token'];
            $secure = $_ENV['APP_ENV'] === 'production' ? ' Secure;' : '';
            $cookieHeader = "legal_session={$token}; Path=/; HttpOnly; SameSite=Lax;{$secure} Max-Age=86400"; // 24 saat

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result
            ]));

            return $response->withHeader('Content-Type', 'application/json')
                ->withAddedHeader('Set-Cookie', $cookieHeader)
                ->withStatus(200);

        }
        catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            // 500 hataları loglanıp gizlenmeli (OWASP kuralı), ancak bu servis katmanından dönen kontrollü istisnalar (401, 403) gösterilebilir
            $message = $code >= 500 ? 'Sunucu tarafında bir hata oluştu.' : $e->getMessage();

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $message
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    /**
     * Güvenli Çıkış – Cookie'yi temizler ve token'ı blacklist'e ekler.
     */
    public function logout(Request $request, Response $response): Response
    {
        // Mevcut token'ı blacklist'e ekle (süre dolana kadar geçersiz kılmak için)
        $this->blacklistCurrentToken($request);

        $secure = ($_ENV['APP_ENV'] ?? '') === 'production' ? ' Secure;' : '';
        $cookieHeader = "legal_session=; Path=/; HttpOnly; SameSite=Lax;{$secure} Max-Age=0";

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Başarıyla çıkış yapıldı.'
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withAddedHeader('Set-Cookie', $cookieHeader)
            ->withStatus(200);
    }

    /**
     * Mevcut JWT token'ını blacklist'e ekler.
     */
    private function blacklistCurrentToken(Request $request): void
    {
        try {
            $cookies = $request->getCookieParams();
            $token = $cookies['legal_session'] ?? null;
            if (empty($token)) {
                return;
            }

            $secretKey = $_ENV['JWT_SECRET_KEY'];
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            $jti = $decoded->jti ?? null;
            $exp = $decoded->exp ?? null;
            $userId = $decoded->data->user_id ?? 0;

            if ($jti === null) {
                return; // Eski format token (jti yok), blacklist'e eklenemez
            }

            $expiresAt = date('Y-m-d H:i:s', (int)$exp);

            $stmt = $this->db->prepare(
                'INSERT IGNORE INTO token_blacklist (jti, user_id, expires_at) VALUES (:jti, :uid, :exp)'
            );
            $stmt->execute(['jti' => $jti, 'uid' => $userId, 'exp' => $expiresAt]);

            // Süresi dolmuş blacklist kayıtlarını temizle (bakım)
            $this->db->exec("DELETE FROM token_blacklist WHERE expires_at < NOW()");
        } catch (\Throwable $e) {
            // Blacklist hatası logout'u engellemememeli
        }
    }
}