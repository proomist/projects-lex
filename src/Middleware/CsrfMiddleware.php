<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

/**
 * CSRF Koruması — Origin/Referer Header Doğrulaması
 *
 * State-changing isteklerde (POST, PUT, DELETE, PATCH) Origin veya Referer
 * header'ını kontrol ederek cross-site request forgery saldırılarını engeller.
 *
 * SameSite=Lax cookie ile birlikte çift katmanlı CSRF koruması sağlar.
 */
class CsrfMiddleware
{
    /** @var string[] İzin verilen origin'ler */
    private array $allowedOrigins;

    public function __construct()
    {
        $appDomain = $_ENV['APP_DOMAIN'] ?? 'http://localhost:8000';

        $this->allowedOrigins = [
            $appDomain,
            rtrim($appDomain, '/'),
        ];

        // Development'ta localhost varyantlarını ekle
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            $this->allowedOrigins[] = 'http://localhost:8000';
            $this->allowedOrigins[] = 'http://localhost:8080';
            $this->allowedOrigins[] = 'http://127.0.0.1:8000';
            $this->allowedOrigins[] = 'http://127.0.0.1:8080';
        }

        $this->allowedOrigins = array_unique($this->allowedOrigins);
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $method = strtoupper($request->getMethod());

        // GET, HEAD, OPTIONS → güvenli metotlar, CSRF kontrolü gereksiz
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $handler->handle($request);
        }

        // State-changing request (POST, PUT, DELETE, PATCH)
        $origin = $request->getHeaderLine('Origin');
        $referer = $request->getHeaderLine('Referer');

        // Origin header varsa → doğrula
        if (!empty($origin)) {
            if ($this->isAllowedOrigin($origin)) {
                return $handler->handle($request);
            }
            return $this->reject('Geçersiz Origin header. CSRF koruması isteği reddetti.');
        }

        // Origin yoksa → Referer header'ını kontrol et
        if (!empty($referer)) {
            $refererOrigin = $this->extractOrigin($referer);
            if ($refererOrigin !== null && $this->isAllowedOrigin($refererOrigin)) {
                return $handler->handle($request);
            }
            return $this->reject('Geçersiz Referer header. CSRF koruması isteği reddetti.');
        }

        // Ne Origin ne Referer var → güvenlik açısından reddet
        // Not: Bazı privacy eklentileri bu header'ları kaldırabilir.
        // Bu durumda istemci tarafında X-Requested-With header'ı eklenerek bypass edilebilir.
        $xRequestedWith = $request->getHeaderLine('X-Requested-With');
        if ($xRequestedWith === 'XMLHttpRequest') {
            // AJAX isteği — tarayıcılar cross-origin XHR'da custom header eklemeye izin vermez (CORS preflight gerekir)
            return $handler->handle($request);
        }

        return $this->reject('CSRF doğrulaması başarısız. Origin veya Referer header eksik.');
    }

    private function isAllowedOrigin(string $origin): bool
    {
        $origin = rtrim($origin, '/');
        foreach ($this->allowedOrigins as $allowed) {
            if (strcasecmp($origin, $allowed) === 0) {
                return true;
            }
        }
        return false;
    }

    private function extractOrigin(string $url): ?string
    {
        $parsed = parse_url($url);
        if (!isset($parsed['scheme'], $parsed['host'])) {
            return null;
        }
        $origin = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port'])) {
            $origin .= ':' . $parsed['port'];
        }
        return $origin;
    }

    private function reject(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }
}
