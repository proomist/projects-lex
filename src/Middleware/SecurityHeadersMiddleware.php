<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Güvenlik HTTP Header Middleware
 *
 * OWASP önerileri doğrultusunda güvenlik header'ları ekler:
 * - Content-Security-Policy (XSS koruması)
 * - X-Frame-Options (Clickjacking koruması)
 * - X-Content-Type-Options (MIME sniffing koruması)
 * - Strict-Transport-Security (SSL zorunluluğu)
 * - Referrer-Policy (Referer sızıntı koruması)
 * - Permissions-Policy (Tarayıcı özellik kısıtlaması)
 * - X-XSS-Protection (Eski tarayıcılar için XSS koruması)
 */
class SecurityHeadersMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';

        // Content-Security-Policy
        // Development: CDN'lere tam erişim gerekli (Tailwind JIT, Lucide, Google Fonts)
        // Production: Local build kullanıldığında CSP daha sıkı yapılabilir
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://unpkg.com https://static.cloudflareinsights.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob:",
            "connect-src 'self' https://cdn.tailwindcss.com https://unpkg.com https://fonts.googleapis.com https://fonts.gstatic.com https://cloudflareinsights.com",
            "worker-src 'self' blob:",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response = $response
            ->withHeader('Content-Security-Policy', $csp)
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-XSS-Protection', '1; mode=block')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // HSTS sadece production'da (HTTPS zorunlu)
        if ($isProduction) {
            $response = $response->withHeader(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
