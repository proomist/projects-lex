<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ErrorLogService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ErrorLogController
{
    private ErrorLogService $errorLogService;

    public function __construct(ErrorLogService $errorLogService)
    {
        $this->errorLogService = $errorLogService;
    }

    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $limit = isset($params['limit']) ? max(1, (int)$params['limit']) : 20;

        $errorLevel = $params['error_level'] ?? null;
        $search = $params['search'] ?? null;
        $userName = $params['user_name'] ?? null;
        $dateFrom = $params['date_from'] ?? null;
        $dateTo = $params['date_to'] ?? null;

        try {
            $result = $this->errorLogService->getLogs(
                $page,
                $limit,
                $errorLevel,
                $search,
                $userName,
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
                'message' => 'Hata logları listelenirken sorun oluştu.',
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
