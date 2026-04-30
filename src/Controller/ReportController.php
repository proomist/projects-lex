<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ReportService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReportController
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function getSummary(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $dateFrom = isset($params['date_from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['date_from']) ? $params['date_from'] : null;
            $dateTo = isset($params['date_to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['date_to']) ? $params['date_to'] : null;

            $data = $this->reportService->getSummaryReport($dateFrom, $dateTo);
            
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $data
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Rapor verileri alınırken hata oluştu.',
                'detail' => $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : null
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }
}
