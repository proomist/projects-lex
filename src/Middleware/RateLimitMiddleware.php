<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Repository\Database;
use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

/**
 * Rate Limit Middleware
 *
 * IP bazlı istek sınırlandırma. Brute-force saldırılarını önler.
 * Veritabanı tabanlı: rate_limits tablosunu kullanır.
 *
 * Varsayılan: 5 deneme / 15 dakika pencere.
 */
class RateLimitMiddleware
{
    private PDO $db;
    private int $maxAttempts;
    private int $windowMinutes;

    public function __construct(Database $database, int $maxAttempts = 5, int $windowMinutes = 15)
    {
        $this->db = $database->getConnection();
        $this->maxAttempts = $maxAttempts;
        $this->windowMinutes = $windowMinutes;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
        $endpoint = $request->getUri()->getPath();

        // Eski kayıtları temizle (pencere dışı)
        $this->cleanup($ip, $endpoint);

        // Mevcut deneme sayısını kontrol et
        $attempts = $this->getAttemptCount($ip, $endpoint);

        if ($attempts >= $this->maxAttempts) {
            $response = new SlimResponse();
            $retryAfter = $this->windowMinutes * 60;

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => "Çok fazla deneme yapıldı. Lütfen {$this->windowMinutes} dakika sonra tekrar deneyin.",
                'retry_after' => $retryAfter
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Retry-After', (string)$retryAfter)
                ->withHeader('X-RateLimit-Limit', (string)$this->maxAttempts)
                ->withHeader('X-RateLimit-Remaining', '0')
                ->withStatus(429);
        }

        // Denemeyi kaydet
        $this->recordAttempt($ip, $endpoint);

        // İsteği işle
        $response = $handler->handle($request);

        // Başarılı giriş (200) → bu IP'nin denemelerini sıfırla
        if ($response->getStatusCode() === 200) {
            $this->clearAttempts($ip, $endpoint);
        }

        // Rate limit header'ları ekle
        $remaining = max(0, $this->maxAttempts - $attempts - 1);
        $response = $response
            ->withHeader('X-RateLimit-Limit', (string)$this->maxAttempts)
            ->withHeader('X-RateLimit-Remaining', (string)$remaining);

        return $response;
    }

    private function getAttemptCount(string $ip, string $endpoint): int
    {
        $sql = "SELECT COUNT(*) FROM rate_limits
                WHERE ip_address = :ip AND endpoint = :endpoint
                AND attempted_at > DATE_SUB(NOW(), INTERVAL :window MINUTE)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('endpoint', $endpoint);
        $stmt->bindValue('window', $this->windowMinutes, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    private function recordAttempt(string $ip, string $endpoint): void
    {
        $sql = "INSERT INTO rate_limits (ip_address, endpoint) VALUES (:ip, :endpoint)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ip' => $ip, 'endpoint' => $endpoint]);
    }

    private function clearAttempts(string $ip, string $endpoint): void
    {
        $sql = "DELETE FROM rate_limits WHERE ip_address = :ip AND endpoint = :endpoint";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ip' => $ip, 'endpoint' => $endpoint]);
    }

    private function cleanup(string $ip, string $endpoint): void
    {
        $sql = "DELETE FROM rate_limits
                WHERE ip_address = :ip AND endpoint = :endpoint
                AND attempted_at <= DATE_SUB(NOW(), INTERVAL :window MINUTE)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('endpoint', $endpoint);
        $stmt->bindValue('window', $this->windowMinutes, PDO::PARAM_INT);
        $stmt->execute();
    }
}
