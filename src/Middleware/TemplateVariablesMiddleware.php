<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Repository\SettingRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class TemplateVariablesMiddleware
{
    private Twig $twig;
    private SettingRepository $settingRepository;

    public function __construct(Twig $twig, SettingRepository $settingRepository)
    {
        $this->twig = $twig;
        $this->settingRepository = $settingRepository;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // UnifiedAuthMiddleware request'e user_payload ekliyor.
        $payload = $request->getAttribute('user_payload') ?? $request->getAttribute('jwt_payload');

        if ($payload) {
            $this->twig->getEnvironment()->addGlobal('current_user', $payload);
        }

        // Sistem ayarlarını Twig global olarak inject et (app_name vb.)
        try {
            $settings = $this->settingRepository->getSettings();
            $this->twig->getEnvironment()->addGlobal('app_settings', $settings);
        } catch (\Throwable $e) {
            $this->twig->getEnvironment()->addGlobal('app_settings', [
                'app_name' => 'Avukat Ofis Yönetim Sistemi'
            ]);
        }

        return $handler->handle($request);
    }
}
