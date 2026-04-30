<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Repository\UserRepository;
use App\Repository\Database;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Exception;

/**
 * Unified Auth Middleware
 *
 * Tek kaynak: HttpOnly cookie (legal_session) üzerinden JWT doğrulama.
 * Web ve API istekleri için aynı middleware kullanılır.
 *
 * - Web isteği (HTML) → geçersizse 302 /login
 * - API isteği (/api/v1/*) → geçersizse 401 JSON
 *
 * Güvenlik:
 * 1. JWT decode + signature doğrulama
 * 2. Token blacklist kontrolü (logout sonrası iptal)
 * 3. DB'de kullanıcının hala var ve aktif olduğu doğrulanır
 */
class UnifiedAuthMiddleware
{
    private UserRepository $userRepository;
    private PDO $db;

    public function __construct(UserRepository $userRepository, Database $database)
    {
        $this->userRepository = $userRepository;
        $this->db = $database->getConnection();
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $cookies = $request->getCookieParams();
        $token = $cookies['legal_session'] ?? null;
        $path = $request->getUri()->getPath();

        // Cookie yoksa → yönlendir veya 401
        if (empty($token)) {
            return $this->denyAccess($request, 'Oturum bulunamadı. Lütfen giriş yapın.');
        }

        try {
            $secretKey = $_ENV['JWT_SECRET_KEY'];
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        }
        catch (Exception $e) {
            // Sadece JWT decode hatası → oturum geçersiz
            return $this->denyAccess($request, 'Oturum süresi dolmuş veya geçersiz.');
        }

        // Token blacklist kontrolü (logout edilmiş token'ları reddet)
        $jti = $decoded->jti ?? null;
        if ($jti !== null && $this->isTokenBlacklisted($jti)) {
            return $this->denyAccess($request, 'Oturum sonlandırılmış. Lütfen tekrar giriş yapın.');
        }

        // DB doğrulaması: Kullanıcı hala mevcut ve aktif mi?
        $userId = (int)($decoded->data->user_id ?? 0);
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            return $this->denyAccess($request, 'Kullanıcı hesabı bulunamadı. Oturum sonlandırıldı.');
        }

        if ($user['status'] === 'Pasif') {
            return $this->denyAccess($request, 'Hesabınız pasif durumdadır. Oturum sonlandırıldı.');
        }

        // JWT + DB doğrulama başarılı → kullanıcı bilgilerini ekle ve devam et
        $request = $request->withAttribute('jwt_payload', $decoded->data);
        $request = $request->withAttribute('user_payload', $decoded->data);
        $request = $request->withAttribute('jwt_jti', $jti);
        $request = $request->withAttribute('jwt_exp', $decoded->exp ?? null);

        return $handler->handle($request);
    }

    /**
     * Token'ın blacklist'te olup olmadığını kontrol eder.
     */
    private function isTokenBlacklisted(string $jti): bool
    {
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM token_blacklist WHERE jti = :jti LIMIT 1');
            $stmt->execute(['jti' => $jti]);
            return $stmt->fetchColumn() !== false;
        } catch (\Throwable $e) {
            // Tablo yoksa veya DB hatası → güvenli tarafta kal, geçişe izin ver
            return false;
        }
    }

    /**
     * İstek türüne göre erişimi reddet.
     * API istekleri → 401 JSON yanıt
     * Web istekleri → 302 /login yönlendirme
     */
    private function denyAccess(Request $request, string $message): Response
    {
        $response = new SlimResponse();
        $path = $request->getUri()->getPath();

        // API isteği mi? (/api/ ile başlıyorsa)
        if (str_starts_with($path, '/api/')) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $message
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Web isteği → login'e yönlendir + geçersiz cookie'yi temizle
        return $response
            ->withHeader('Location', '/login')
            ->withAddedHeader('Set-Cookie', 'legal_session=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0')
            ->withStatus(302);
    }
}