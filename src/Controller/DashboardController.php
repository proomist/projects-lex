<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\DashboardService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class DashboardController
{
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getSummary(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute('jwt_payload');

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $summary = $this->dashboardService->getSummary($payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $summary
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Dashboard verileri alınırken hata oluştu.',
                'detail' => $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : null
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function getBadges(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute('jwt_payload');

        try {
            $badges = $this->dashboardService->getBadges($payload->user_id);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $badges
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Badge verileri alınamadı.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getNotifications(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute('jwt_payload');

        try {
            $notifications = $this->dashboardService->getNotifications($payload->user_id);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $notifications
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Bildirimler alınamadı.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function markNotificationRead(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $reminderId = (int)$args['id'];

        try {
            $this->dashboardService->markNotificationRead($reminderId, $payload->user_id);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Bildirim okundu olarak işaretlendi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'İşlem başarısız.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function search(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query = $params['q'] ?? '';

        try {
            $results = $this->dashboardService->search($query);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $results
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Arama yapılırken hata oluştu.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
