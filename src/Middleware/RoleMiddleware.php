<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // UnifiedAuthMiddleware adds both user_payload and jwt_payload
        $payload = $request->getAttribute('user_payload') ?? $request->getAttribute('jwt_payload');
        
        if (!$payload || !isset($payload->role)) {
            return $this->denyAccess($request, 'Yetki bilgisi bulunamadı.');
        }

        if (!in_array($payload->role, $this->allowedRoles)) {
            return $this->denyAccess($request, 'Bu işlemi yapmak veya sayfayı görüntülemek için yetkiniz bulunmamaktadır.');
        }

        return $handler->handle($request);
    }

    private function denyAccess(Request $request, string $message): Response
    {
        $response = new SlimResponse();
        $path = $request->getUri()->getPath();

        // API isteği mi?
        if (str_starts_with($path, '/api/')) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $message
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // Web isteği → dashboard'a yönlendir
        // Kullanıcı giriş yapmış ama yetkisi yoksa login yerine dashboard'a atılır.
        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }
}
