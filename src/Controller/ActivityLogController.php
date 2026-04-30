<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ActivityLogService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ActivityLogController
{
    private ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $limit = isset($params['limit']) ? max(1, (int)$params['limit']) : 20;

        $actionType = $params['action_type'] ?? null;
        $module = $params['module'] ?? null;
        $actor = $params['actor'] ?? null;
        $ipAddress = $params['ip_address'] ?? null;
        $dateFrom = $params['date_from'] ?? null;
        $dateTo = $params['date_to'] ?? null;

        try {
            $result = $this->activityLogService->getLogs(
                $page,
                $limit,
                $actionType,
                $module,
                $actor,
                $ipAddress,
                $dateFrom,
                $dateTo
            );

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'meta' => $result['meta'],
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'İşlem logları listelenirken hata oluştu.',
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
