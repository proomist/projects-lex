<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\ErrorLogRepository;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler;
use Slim\Views\Twig;

class CustomErrorHandler extends ErrorHandler
{
    private ?ErrorLogRepository $errorLogRepository = null;
    private string $logFilePath = '';
    private ?Twig $twig = null;

    public function setErrorLogRepository(ErrorLogRepository $repository): void
    {
        $this->errorLogRepository = $repository;
    }

    public function setLogFilePath(string $path): void
    {
        $this->logFilePath = $path;
    }

    public function setTwig(Twig $twig): void
    {
        $this->twig = $twig;
    }

    protected function writeToErrorLog(): void
    {
        $exception = $this->exception;
        $request = $this->request;

        // Statik dosya isteklerini (favicon.ico vb.) loglama
        $uri = $request->getUri()->getPath();
        if ($this->isIgnoredRequest($uri)) {
            return;
        }

        $errorLevel = $this->determineErrorLevel($exception);
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();

        $requestMethod = $request->getMethod();
        $requestUri = (string)$request->getUri();

        $serverParams = $request->getServerParams();
        $ipAddress = $serverParams['REMOTE_ADDR'] ?? null;
        $userAgent = $request->getHeaderLine('User-Agent') ?: null;

        $userId = null;
        $userName = null;
        $jwtPayload = $request->getAttribute('jwt_payload');
        if ($jwtPayload) {
            $payload = is_object($jwtPayload) ? (array)$jwtPayload : $jwtPayload;
            $userId = $payload['user_id'] ?? null;
            $firstName = $payload['first_name'] ?? '';
            $lastName = $payload['last_name'] ?? '';
            $userName = trim($firstName . ' ' . $lastName) ?: ($payload['username'] ?? null);
        }

        $errorData = [
            'error_level' => $errorLevel,
            'error_code' => $exception->getCode() ?: null,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'trace' => $trace,
            'request_method' => $requestMethod,
            'request_uri' => $requestUri,
            'user_id' => $userId,
            'user_name' => $userName,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ];

        // DB'ye kaydet (başarısız olursa sessizce devam et)
        if ($this->errorLogRepository !== null) {
            try {
                $this->errorLogRepository->insert($errorData);
            } catch (\Throwable $dbError) {
                // DB yazılamadıysa dosyaya yaz
                $this->writeToFile("[DB_WRITE_FAILED] " . $dbError->getMessage());
            }
        }

        // Dosyaya yaz (her zaman)
        $this->writeToFile($this->formatLogEntry($errorData));

        // Slim'in kendi log mekanizmasını da çalıştır
        parent::writeToErrorLog();
    }

    protected function respond(): ResponseInterface
    {
        $request = $this->request;

        // API isteği mi yoksa web (tarayıcı) isteği mi?
        if ($this->isApiRequest($request)) {
            return $this->respondJson();
        }

        return $this->respondHtml();
    }

    /**
     * API istekleri için JSON response
     */
    private function respondJson(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($this->statusCode);
        $response = $response->withHeader('Content-Type', 'application/json');

        $error = [
            'status' => 'error',
            'message' => $this->getErrorMessage(),
        ];

        if ($this->displayErrorDetails) {
            $error['detail'] = [
                'type' => get_class($this->exception),
                'code' => $this->exception->getCode(),
                'message' => $this->exception->getMessage(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
            ];
        }

        $response->getBody()->write((string)json_encode($error, JSON_UNESCAPED_UNICODE));

        return $response;
    }

    /**
     * Web istekleri için HTML hata sayfası
     */
    private function respondHtml(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($this->statusCode);
        $response = $response->withHeader('Content-Type', 'text/html');

        $errorConfig = $this->getErrorPageConfig();

        // Twig varsa şablonla render et
        if ($this->twig !== null) {
            try {
                $data = [
                    'error_code' => $this->statusCode,
                    'error_title' => $errorConfig['title'],
                    'error_message' => $errorConfig['message'],
                    'icon' => $errorConfig['icon'],
                    'icon_bg' => $errorConfig['icon_bg'],
                    'icon_color' => $errorConfig['icon_color'],
                    'detail' => null,
                ];

                if ($this->displayErrorDetails) {
                    $data['detail'] = [
                        'type' => get_class($this->exception),
                        'message' => $this->exception->getMessage(),
                        'file' => $this->exception->getFile(),
                        'line' => $this->exception->getLine(),
                    ];
                }

                return $this->twig->render($response, 'pages/error.twig', $data);
            } catch (\Throwable $e) {
                // Twig render başarısız olursa fallback HTML
            }
        }

        // Fallback: Twig yoksa basit HTML
        $response->getBody()->write($this->getFallbackHtml($errorConfig));
        return $response;
    }

    /**
     * Hata koduna göre sayfa yapılandırması
     */
    private function getErrorPageConfig(): array
    {
        $configs = [
            401 => [
                'title' => 'Yetkilendirme Gerekli',
                'message' => 'Bu sayfaya erişmek için giriş yapman gerekiyor. Lütfen tekrar giriş yap.',
                'icon' => 'lock',
                'icon_bg' => 'bg-amber-50 border border-amber-200',
                'icon_color' => 'text-amber-500',
            ],
            403 => [
                'title' => 'Erişim Engellendi',
                'message' => 'Bu sayfaya erişim yetkin bulunmuyor. Farklı bir yetki seviyesi gerekiyor olabilir.',
                'icon' => 'shield-x',
                'icon_bg' => 'bg-rose-50 border border-rose-200',
                'icon_color' => 'text-rose-500',
            ],
            404 => [
                'title' => 'Sayfa Bulunamadı',
                'message' => 'Aradığın sayfa mevcut değil veya taşınmış olabilir. URL adresini kontrol et ya da ana sayfadan devam et.',
                'icon' => 'search-x',
                'icon_bg' => 'bg-slate-100 border border-slate-200',
                'icon_color' => 'text-slate-400',
            ],
            405 => [
                'title' => 'Geçersiz İstek',
                'message' => 'Bu adres için kullandığın yöntem desteklenmiyor. URL adresini kontrol et ya da ana sayfadan devam et.',
                'icon' => 'route-off',
                'icon_bg' => 'bg-orange-50 border border-orange-200',
                'icon_color' => 'text-orange-500',
            ],
            500 => [
                'title' => 'Sunucu Hatası',
                'message' => 'Beklenmeyen bir hata oluştu. Teknik ekip bilgilendirildi. Lütfen birkaç dakika sonra tekrar dene.',
                'icon' => 'server-crash',
                'icon_bg' => 'bg-rose-50 border border-rose-200',
                'icon_color' => 'text-rose-500',
            ],
        ];

        return $configs[$this->statusCode] ?? [
            'title' => 'Bir Hata Oluştu',
            'message' => 'İsteğin işlenirken bir sorun yaşandı. Lütfen tekrar dene veya ana sayfaya dön.',
            'icon' => 'alert-triangle',
            'icon_bg' => 'bg-amber-50 border border-amber-200',
            'icon_color' => 'text-amber-500',
        ];
    }

    /**
     * Twig kullanılamadığında fallback HTML
     */
    private function getFallbackHtml(array $config): string
    {
        $code = $this->statusCode;
        $title = htmlspecialchars($config['title']);
        $message = htmlspecialchars($config['message']);

        return <<<HTML
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$code} — {$title}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; color: #282828; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
                .container { text-align: center; padding: 2rem; }
                .code { font-size: 5rem; font-weight: 700; color: rgba(40,40,40,0.15); margin-bottom: 0.5rem; }
                h1 { font-size: 1.25rem; margin-bottom: 0.75rem; }
                p { color: #64748b; font-size: 0.875rem; max-width: 28rem; margin: 0 auto 2rem; line-height: 1.6; }
                a { display: inline-block; background: #282828; color: #fff; padding: 0.625rem 1.25rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; font-weight: 500; }
                a:hover { background: #333; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="code">{$code}</div>
                <h1>{$title}</h1>
                <p>{$message}</p>
                <a href="/dashboard">Ana Sayfaya D&ouml;n</a>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * İstek API mi yoksa web mi?
     */
    private function isApiRequest($request): bool
    {
        $uri = $request->getUri()->getPath();

        // /api/ ile başlayan istekler API isteğidir
        if (str_starts_with($uri, '/api/')) {
            return true;
        }

        // Accept header'ında application/json varsa API
        $accept = $request->getHeaderLine('Accept');
        if (str_contains($accept, 'application/json') && !str_contains($accept, 'text/html')) {
            return true;
        }

        // XMLHttpRequest (AJAX) ise API
        $xRequestedWith = $request->getHeaderLine('X-Requested-With');
        if (strtolower($xRequestedWith) === 'xmlhttprequest') {
            return true;
        }

        return false;
    }

    private function determineErrorLevel(\Throwable $exception): string
    {
        if ($exception instanceof HttpException) {
            $code = $exception->getCode();
            if ($code >= 500) {
                return 'CRITICAL';
            }
            if ($code >= 400) {
                return 'WARNING';
            }
            return 'NOTICE';
        }

        if ($exception instanceof \Error) {
            return 'CRITICAL';
        }

        return 'ERROR';
    }

    private function getErrorMessage(): string
    {
        if ($this->exception instanceof HttpException) {
            return $this->exception->getMessage() ?: 'Bir hata oluştu.';
        }

        if ($this->displayErrorDetails) {
            return $this->exception->getMessage();
        }

        return 'Sunucu hatası oluştu. Lütfen daha sonra tekrar deneyin.';
    }

    private function isIgnoredRequest(string $uri): bool
    {
        $ignored = ['/favicon.ico', '/robots.txt', '/apple-touch-icon.png'];
        foreach ($ignored as $path) {
            if ($uri === $path) {
                return true;
            }
        }
        return false;
    }

    private function formatLogEntry(array $data): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $level = $data['error_level'] ?? 'ERROR';
        $message = $data['message'] ?? 'Unknown error';
        $file = $data['file'] ?? '-';
        $line = $data['line'] ?? '-';
        $method = $data['request_method'] ?? '-';
        $uri = $data['request_uri'] ?? '-';
        $user = $data['user_name'] ?? 'Anonim';
        $ip = $data['ip_address'] ?? '-';

        return "[{$timestamp}] [{$level}] {$message} | {$file}:{$line} | {$method} {$uri} | User: {$user} | IP: {$ip}";
    }

    private function writeToFile(string $entry): void
    {
        if (empty($this->logFilePath)) {
            return;
        }

        $dir = dirname($this->logFilePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents($this->logFilePath, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
