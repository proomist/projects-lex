<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\FinancialService;
use App\Service\SettingService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Slim\Views\Twig;
use Exception;

class FinancialReportController
{
    private FinancialService $financialService;
    private SettingService $settingService;
    private Twig $view;

    public function __construct(FinancialService $financialService, SettingService $settingService, Twig $view)
    {
        $this->financialService = $financialService;
        $this->settingService = $settingService;
        $this->view = $view;
    }

    public function getStatement(Request $request, Response $response, array $args): Response
    {
        $clientId = (int)$args['id'];
        $params = $request->getQueryParams();
        $dateFrom = !empty($params['date_from']) ? $params['date_from'] : null;
        $dateTo = !empty($params['date_to']) ? $params['date_to'] : null;

        try {
            $statement = $this->financialService->getClientStatement($clientId, $dateFrom, $dateTo);
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $statement
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $code >= 500 ? 'Sunucu hatası: ' . $e->getMessage() : $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function downloadPdf(Request $request, Response $response, array $args): Response
    {
        $clientId = (int)$args['id'];
        $params = $request->getQueryParams();
        $dateFrom = !empty($params['date_from']) ? $params['date_from'] : null;
        $dateTo = !empty($params['date_to']) ? $params['date_to'] : null;

        try {
            $pdfContent = $this->generatePdfContent($clientId, $dateFrom, $dateTo);
            
            $filename = "Mutabakat_Ekstre_" . $clientId . "_" . date('Ymd_His') . ".pdf";
            
            $response->getBody()->write($pdfContent);
            return $response
                ->withHeader('Content-Type', 'application/pdf')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->withStatus(200);

        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'PDF oluşturulamadı: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function mailStatement(Request $request, Response $response, array $args): Response
    {
        $clientId = (int)$args['id'];
        $data = (array)$request->getParsedBody();
        $dateFrom = !empty($data['date_from']) ? $data['date_from'] : null;
        $dateTo = !empty($data['date_to']) ? $data['date_to'] : null;
        $toEmail = !empty($data['email']) ? $data['email'] : null;

        if (!$toEmail || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Geçerli bir e-posta adresi giriniz.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $settings = $this->settingService->getSettings();
            $appName = $settings['app_name'] ?? 'Hukuk Bürosu';
            
            $pdfContent = $this->generatePdfContent($clientId, $dateFrom, $dateTo);
            $filename = "Mutabakat_Ekstre_" . $clientId . "_" . date('Y-m-d') . ".pdf";

            // SMTP Setup
            $host = $_ENV['MAIL_HOST'] ?? '';
            $port = (int)($_ENV['MAIL_PORT'] ?? 587);
            $user = $_ENV['MAIL_USER'] ?? '';
            $pass = $_ENV['MAIL_PASS'] ?? '';
            $from = $_ENV['MAIL_FROM'] ?? $user;

            if (empty($host) || empty($user)) {
                throw new Exception("SMTP (E-posta) ayarları sistemde (.env dosyasında) yapılandırılmamış.", 400);
            }

            // Fallback for missing transport parameters
            if (strpos($host, 'smtp') === false && strpos($host, '://') === false) {
                // Determine DSN prefix
                $scheme = 'smtp';
                if ($port === 465) {
                    $scheme = 'smtps';
                }
                $dsn = "{$scheme}://" . urlencode($user) . ":" . urlencode($pass) . "@" . $host . ":" . $port;
            } else {
                $dsn = $host; // If DSN is fully provided in MAIL_HOST
            }

            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);

            $email = (new Email())
                ->from($from)
                ->to($toEmail)
                ->subject($appName . ' - Cari Hesap Mutabakat Formu / Ekstresi')
                ->html("<p>Sayın İlgili,</p><p>Cari hesap mutabakat formunuz ekte yer almaktadır (<strong>{$filename}</strong>).</p><p>Bilgilerinize sunarız.<br><br><strong>$appName</strong></p>")
                ->attach($pdfContent, $filename, 'application/pdf');

            $mailer->send($email);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Mutabakat formu başarıyla e-posta olarak gönderildi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Posta gönderilemedi: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    private function generatePdfContent(int $clientId, ?string $dateFrom, ?string $dateTo): string
    {
        $statement = $this->financialService->getClientStatement($clientId, $dateFrom, $dateTo);
        $settings = $this->settingService->getSettings();

        $html = $this->view->fetch('pdf/statement.twig', [
            'statement' => $statement,
            'settings' => $settings,
            'generated_at' => date('d.m.Y H:i')
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
