<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use App\Controller\AuthController;
use App\Controller\UserController;
use App\Middleware\UnifiedAuthMiddleware;

use App\Controller\ClientController;
use App\Controller\CaseController;
use App\Controller\HearingController;
use App\Controller\FinancialController;
use App\Controller\FinancialReportController;
use App\Controller\TaskController;
use App\Controller\DashboardController;
use App\Controller\ReportController;
use App\Controller\SettingController;
use App\Controller\ActivityLogController;
use App\Controller\LookupController;
use App\Controller\DocumentController;
use App\Controller\ErrorLogController;
use App\Controller\CollectionDistributionController;

// WebAuthMiddleware ve AuthMiddleware kaldırıldı → UnifiedAuthMiddleware kullanılıyor

// Web Controllers
use App\Controller\Web\DashboardWebController;
use App\Controller\Web\ClientWebController;
use App\Controller\Web\CaseWebController;
use App\Controller\Web\HearingWebController;
use App\Controller\Web\TaskWebController;
use App\Controller\Web\FinanceWebController;
use App\Controller\Web\ReportWebController;
use App\Controller\Web\SettingWebController;
use App\Controller\Web\LoginWebController;
use App\Controller\Web\UserWebController;
use App\Controller\Web\ActivityLogWebController;
use App\Controller\Web\SystemDefinitionWebController;
use App\Controller\Web\DocumentWebController;
use App\Controller\Web\ErrorLogWebController;
use App\Controller\Web\GuideWebController;

use App\Middleware\RoleMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\TemplateVariablesMiddleware;

return function (App $app) {

    // -------------------------------------------------------------
    // WEB (FRONTEND) ROTALARI
    // -------------------------------------------------------------

    // Açık olan Giriş Sayfası
    $app->get('/login', [LoginWebController::class , 'index']);

    // Korumalı (SSR) Rotalar
    $app->group('', function ($web) {
            $web->get('/', function (Request $request, Response $response) {
                    return $response->withHeader('Location', '/dashboard')->withStatus(302);
                }
            );

            $web->get('/dashboard', [DashboardWebController::class , 'index']);
            $web->get('/clients', [ClientWebController::class , 'index']);
            $web->get('/cases', [CaseWebController::class , 'index']);
            $web->get('/cases/{id}/finance', [CaseWebController::class , 'finance']);
            $web->get('/hearings', [HearingWebController::class , 'index']);
            $web->get('/tasks', [TaskWebController::class , 'index']);
            $web->get('/documents', [DocumentWebController::class , 'index']);
            $web->get('/guide', [GuideWebController::class , 'index']);

            // Sadece belirli rollerin görebileceği sayfalar
            $web->get('/finance', [FinanceWebController::class , 'index'])
                ->add(new RoleMiddleware(['Kurucu Ortak', 'Ortak Avukat', 'Muhasebeci', 'Sistem Yöneticisi']));
                
            $web->get('/reports', [ReportWebController::class , 'index'])
                ->add(new RoleMiddleware(['Kurucu Ortak', 'Ortak Avukat', 'Sistem Yöneticisi']));
                
            $web->get('/settings', [SettingWebController::class , 'index'])
                ->add(new RoleMiddleware(['Kurucu Ortak', 'Sistem Yöneticisi']));
                
            $web->get('/users', [UserWebController::class , 'index'])
                ->add(new RoleMiddleware(['Kurucu Ortak', 'Sistem Yöneticisi']));
                
            $web->get('/activity-logs', [ActivityLogWebController::class , 'index'])
                ->add(new RoleMiddleware(['Sistem Yöneticisi']));

            $web->get('/error-logs', [ErrorLogWebController::class , 'index'])
                ->add(new RoleMiddleware(['Sistem Yöneticisi']));

            $web->get('/system-definitions', [SystemDefinitionWebController::class , 'index'])
                ->add(new RoleMiddleware(['Kurucu Ortak', 'Sistem Yöneticisi']));
        }
    )->add(TemplateVariablesMiddleware::class)->add(UnifiedAuthMiddleware::class);

            // -------------------------------------------------------------
            // API v1 ROTALARI
            // -------------------------------------------------------------
            $app->group('/api/v1', function ($group) {

            // Public Rotalar
            $group->post('/login', [AuthController::class , 'login'])
                ->add(RateLimitMiddleware::class); // Brute-force koruması: 5 deneme / 15 dakika
            $group->post('/logout', [AuthController::class , 'logout']);

            // Korumalı Rotalar (Sadece Yetkili Kullanıcılar)
            $group->group('', function ($protected) {

                    // Dashboard
                    $protected->get('/dashboard/summary', [DashboardController::class , 'getSummary']);
                    $protected->get('/dashboard/badges', [DashboardController::class , 'getBadges']);
                    $protected->get('/dashboard/notifications', [DashboardController::class , 'getNotifications']);
                    $protected->put('/dashboard/notifications/{id}/read', [DashboardController::class , 'markNotificationRead']);
                    $protected->get('/dashboard/search', [DashboardController::class , 'search']);

                    $protected->get('/me', function (Request $request, Response $response) {
                            // Token'dan gelen payload bilgisine erişim
                            $payload = $request->getAttribute('jwt_payload');
                            $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';

                            $response->getBody()->write(json_encode([
                                'status' => 'success',
                                'data' => array_merge((array)$payload, ['client_ip' => $ip])
                            ]));
                            return $response->withHeader('Content-Type', 'application/json');
                        }
                        );

                        // Kullanıcı Yönetimi (Sadece Kurucu Ortak ve Sistem Yöneticisi)
                        $protected->group('/users', function ($users) {
                            $users->post('', [UserController::class , 'create']);
                            $users->get('', [UserController::class , 'list']);
                            $users->put('/{id}', [UserController::class , 'update']);
                            $users->delete('/{id}', [UserController::class , 'delete']);
                        })->add(new RoleMiddleware(['Kurucu Ortak', 'Sistem Yöneticisi']));

                        // Müvekkil Yönetimi
                        $protected->post('/clients', [ClientController::class , 'create']);
                        $protected->get('/clients', [ClientController::class , 'list']);
                        $protected->get('/clients/{id}', [ClientController::class , 'get']);
                        $protected->put('/clients/{id}', [ClientController::class , 'update']);
                        $protected->delete('/clients/{id}', [ClientController::class , 'delete']);
                        
                        // Cari Ekstre / Mutabakat Formu (Sadece Yetkili Roller)
                        $protected->group('/clients/{id}/statement', function ($statement) {
                            $statement->get('', [FinancialReportController::class, 'getStatement']);
                            $statement->get('/pdf', [FinancialReportController::class, 'downloadPdf']);
                            $statement->post('/mail', [FinancialReportController::class, 'mailStatement']);
                        })->add(new RoleMiddleware(['Kurucu Ortak', 'Ortak Avukat', 'Muhasebeci', 'Sistem Yöneticisi']));

                        // Dosya ve Dava Yönetimi
                        $protected->post('/cases', [CaseController::class , 'create']);
                        $protected->get('/cases', [CaseController::class , 'list']);
                        $protected->get('/cases/{id}', [CaseController::class , 'get']);
                        $protected->put('/cases/{id}', [CaseController::class , 'update']);
                        $protected->delete('/cases/{id}', [CaseController::class , 'delete']);

                        // Duruşma Yönetimi
                        // Duruşmalar - Genel Listeleme (case-specific rotalardan ÖNCE tanımlanmalı)
                        $protected->get('/hearings', [HearingController::class , 'list']);
                        
                        $protected->post('/cases/{caseId}/hearings', [HearingController::class , 'create']);
                        $protected->get('/cases/{caseId}/hearings', [HearingController::class , 'listByCase']);
                        $protected->get('/hearings/{id}', [HearingController::class , 'get']);
                        $protected->put('/hearings/{id}', [HearingController::class , 'update']);
                        $protected->delete('/hearings/{id}', [HearingController::class , 'delete']);

                        // Mali Takip (Alacak, Borç, Tahsilat)
                        $protected->group('/financials', function ($financials) {
                            $financials->post('', [FinancialController::class , 'create']);
                            $financials->get('', [FinancialController::class , 'list']);
                            $financials->get('/balance', [FinancialController::class , 'getBalance']);
                            $financials->get('/{id}', [FinancialController::class , 'get']);
                            $financials->put('/{id}', [FinancialController::class , 'update']);
                            $financials->delete('/{id}', [FinancialController::class , 'delete']);
                        })->add(new RoleMiddleware(['Kurucu Ortak', 'Ortak Avukat', 'Muhasebeci', 'Sistem Yöneticisi']));

                        // Mali Takip (Dağıtım / Şelale Modeli)
                        $protected->group('/distributions', function ($dist) {
                            $dist->get('/cases/{case_id}', [CollectionDistributionController::class, 'getDetails']);
                            $dist->post('/collections', [CollectionDistributionController::class, 'createCollection']);
                            $dist->delete('/collections/{id}', [CollectionDistributionController::class, 'deleteCollection']);
                            $dist->post('/distribute', [CollectionDistributionController::class, 'createDistribution']);
                            $dist->post('/fee-agreement', [CollectionDistributionController::class, 'saveFeeAgreement']);
                        })->add(new RoleMiddleware(['Kurucu Ortak', 'Ortak Avukat', 'Muhasebeci', 'Sistem Yöneticisi']));

                        // İş Listesi ve Görevler
                        $protected->post('/tasks', [TaskController::class , 'create']);
                        $protected->get('/tasks', [TaskController::class , 'list']);
                        $protected->get('/tasks/{id}', [TaskController::class , 'get']);
                        $protected->put('/tasks/{id}', [TaskController::class , 'update']);
                        $protected->put('/tasks/{id}/checklist/{itemId}', [TaskController::class , 'toggleChecklist']);
                        $protected->delete('/tasks/{id}', [TaskController::class , 'delete']);

                        // Raporlar
                        $protected->group('/reports', function ($reports) {
                            $reports->get('/summary', [ReportController::class , 'getSummary']);
                        })->add(new RoleMiddleware(['Kurucu Ortak', 'Ortak Avukat', 'Sistem Yöneticisi']));

                        // Ayarlar
                        $protected->group('/settings', function ($settings) {
                            $settings->get('', [SettingController::class , 'getSettings']);
                            $settings->put('', [SettingController::class , 'updateSettings']);
                        })->add(new RoleMiddleware(['Kurucu Ortak', 'Sistem Yöneticisi']));

                        // Sistem Tanımları (Lookup)
                        $protected->get('/lookups', [LookupController::class , 'listGroups']);
                        $protected->get('/lookups/{group}', [LookupController::class , 'getByGroup']);
                        $protected->group('/lookups', function ($lookups) {
                            $lookups->post('', [LookupController::class , 'create']);
                            $lookups->put('/{id}', [LookupController::class , 'update']);
                            $lookups->delete('/{id}', [LookupController::class , 'delete']);
                        })->add(new RoleMiddleware(['Kurucu Ortak', 'Sistem Yöneticisi']));

                        // Evrak Yönetimi
                        $protected->post('/documents', [DocumentController::class , 'upload']);
                        $protected->get('/documents', [DocumentController::class , 'list']);
                        $protected->get('/documents/{id}/download', [DocumentController::class , 'download']);
                        $protected->delete('/documents/{id}', [DocumentController::class , 'delete']);

                        // İşlem Logları (Sadece Sistem Yöneticisi)
                        $protected->group('/activity-logs', function ($logs) {
                            $logs->get('', [ActivityLogController::class , 'list']);
                        })->add(new RoleMiddleware(['Sistem Yöneticisi']));

                        // Hata İzleme (Sadece Sistem Yöneticisi)
                        $protected->group('/error-logs', function ($errorLogs) {
                            $errorLogs->get('', [ErrorLogController::class , 'list']);
                        })->add(new RoleMiddleware(['Sistem Yöneticisi']));

                    }
                    )->add(UnifiedAuthMiddleware::class);

                }
                );
            };