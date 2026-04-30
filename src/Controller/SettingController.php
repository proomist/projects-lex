<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\SettingService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SettingController
{
    private SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function getSettings(Request $request, Response $response): Response
    {
        try {
            $data = $this->settingService->getSettings();
            
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $data
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Ayarlar alınırken hata oluştu.',
                'detail' => $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : null
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function updateSettings(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $data = (array)$request->getParsedBody();

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->settingService->updateSettings($data, $payload->user_id, $ipAddress);
            
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Ayarlar başarıyla güncellendi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Ayarlar güncellenirken hata oluştu.',
                'detail' => $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : null
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }
}
