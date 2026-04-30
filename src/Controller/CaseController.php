<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CaseService;
use App\Service\LookupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator;
use Exception;

class CaseController
{
    private const CASE_STATUS_VALUES = ['Taslak', 'Aktif', 'Beklemede', 'Karar Aşaması', 'Kapandı', 'İptal/Düşme', 'Arşiv', 'Sonuçlandı', 'İstinaf/Yargıtay'];

    private CaseService $caseService;
    private LookupService $lookupService;

    public function __construct(CaseService $caseService, LookupService $lookupService)
    {
        $this->caseService = $caseService;
        $this->lookupService = $lookupService;
    }

    public function create(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        if (isset($data['client_role']) && !isset($data['client_position'])) {
            $data['client_position'] = $data['client_role'];
        }

        $v->rule('required', ['case_type', 'client_id', 'client_position', 'lawyer_id']);
        $v->rule('in', 'case_type', $this->lookupService->getValuesForValidation('case_types'));
        $v->rule('in', 'client_position', $this->lookupService->getValuesForValidation('client_positions'));
        $v->rule('integer', ['client_id', 'lawyer_id']);
        if (isset($data['open_date']) && $data['open_date'] !== '') {
            $v->rule('date', 'open_date');
        }
        if (isset($data['status'])) {
            $v->rule('in', 'status', self::CASE_STATUS_VALUES);
        }

        if (!$v->validate()) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Doğrulama hatası',
                'errors' => $v->errors()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $caseId = $this->caseService->createCase($data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Dosya başarıyla oluşturuldu.',
                'data' => ['id' => $caseId]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $message = $code >= 500 ? 'Sunucu tarafında bir hata oluştu.' : $e->getMessage();

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $message
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function list(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = isset($params['page']) ? max(1, (int)$params['page']) : 1;
        $limit = isset($params['limit']) ? max(1, (int)$params['limit']) : 20;
        
        $search = $params['search'] ?? null;
        $clientId = isset($params['client_id']) ? (int)$params['client_id'] : null;
        $lawyerId = isset($params['lawyer_id']) ? (int)$params['lawyer_id'] : null;
        $status = $params['status'] ?? null;
        $type = $params['type'] ?? null;

        try {
            $result = $this->caseService->getCases($page, $limit, $search, $clientId, $lawyerId, $status, $type);
            
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'meta' => $result['meta']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Dosyalar listelenirken hata oluştu.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $caseId = (int)$args['id'];

        try {
            $case = $this->caseService->getCaseById($caseId);
            
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $case
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $caseId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        if (isset($data['client_role']) && !isset($data['client_position'])) {
            $data['client_position'] = $data['client_role'];
        }

        if (isset($data['case_type'])) {
            $v->rule('in', 'case_type', $this->lookupService->getValuesForValidation('case_types'));
        }
        if (isset($data['client_position'])) {
            $v->rule('in', 'client_position', $this->lookupService->getValuesForValidation('client_positions'));
        }
        if (isset($data['status'])) {
            $v->rule('in', 'status', self::CASE_STATUS_VALUES);
        }
        if (isset($data['open_date'])) {
            $v->rule('date', 'open_date');
        }
        if (isset($data['client_id'])) {
            $v->rule('integer', 'client_id');
        }
        if (isset($data['lawyer_id'])) {
            $v->rule('integer', 'lawyer_id');
        }

        if (!$v->validate()) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Doğrulama hatası',
                'errors' => $v->errors()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->caseService->updateCase($caseId, $data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Dosya başarıyla güncellendi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $message = $code >= 500 ? 'Sunucu tarafında bir hata oluştu.' : $e->getMessage();

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $message
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $caseId = (int)$args['id'];

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->caseService->deleteCase($caseId, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Dosya başarıyla arşivlendi (soft delete).'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $message = $code >= 500 ? 'Sunucu tarafında bir hata oluştu.' : $e->getMessage();

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $message
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }
}
