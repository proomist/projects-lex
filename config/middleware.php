<?php

declare(strict_types=1);

use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use App\Handler\CustomErrorHandler;
use App\Repository\ErrorLogRepository;
use App\Middleware\SecurityHeadersMiddleware;
use App\Middleware\CsrfMiddleware;
use Slim\Views\Twig;

return function (App $app) {
    // Add Body Parsing Middleware
    $app->addBodyParsingMiddleware();

    // Add Routing Middleware
    $app->addRoutingMiddleware();

    // Güvenlik HTTP Header'ları (OWASP)
    $app->add(new SecurityHeadersMiddleware());

    // CSRF Koruması (Origin/Referer doğrulaması)
    $app->add(new CsrfMiddleware());

    // CORS Middleware
    $app->add(function (Request $request, RequestHandler $handler) {
        $response = $handler->handle($request);
        return $response
            ->withHeader('Access-Control-Allow-Origin', $_ENV['CORS_ORIGIN'] ?? $_ENV['APP_DOMAIN'] ?? 'http://localhost:8000')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    });

    // OPTIONS Request Handler (CORS Preflight)
    $app->options('/{routes:.+}', function (Request $request, Response $response) {
        return $response;
    });

    // Add Error Middleware
    $settings = $app->getContainer()->get('settings');
    $displayErrorDetails = $settings['displayErrorDetails'];
    $logErrors = $settings['logError'];
    $logErrorDetails = $settings['logErrorDetails'];

    // CustomErrorHandler: DB + dosya tabanlı hata loglama
    $callableResolver = $app->getCallableResolver();
    $responseFactory = $app->getResponseFactory();

    $customErrorHandler = new CustomErrorHandler($callableResolver, $responseFactory);
    $customErrorHandler->forceContentType('application/json');

    // Log dosyası yolu
    $logFilePath = dirname(__DIR__) . '/storage/logs/error.log';
    $customErrorHandler->setLogFilePath($logFilePath);

    // Twig + ErrorLogRepository inject (yoksa sessizce devam et)
    try {
        $container = $app->getContainer();
        $errorLogRepository = $container->get(ErrorLogRepository::class);
        $customErrorHandler->setErrorLogRepository($errorLogRepository);

        $twig = $container->get(Twig::class);
        $customErrorHandler->setTwig($twig);
    } catch (\Throwable $e) {
        // DB veya Twig bağlantısı yoksa sessizce devam et
    }

    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
    $errorMiddleware->setDefaultErrorHandler($customErrorHandler);
};
