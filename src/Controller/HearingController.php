<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\HearingService;
use App\Service\LookupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator;
use Exception;

class HearingController
{
    private HearingService $hearingService;
    private LookupService $lookupService;

    public function __construct(HearingService $hearingService, LookupService $lookupService)
    {
        $this->hearingService = $hearingService;
        $this->lookupService = $lookupService;
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $caseId = (int)$args['caseId'];
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        $v->rule('required', ['hearing_date']);
        if (isset($data['hearing_type'])) {
            $v->rule('in', 'hearing_type', $this->lookupService->getValuesForValidation('hearing_types'));
        }

        if (isset($data['status'])) {
            $v->rule('in', 'status', ['Planlandı', 'Tamamlandı', 'Yapıldı', 'Ertelendi', 'İptal']);
        }
        if (isset($data['attending_lawyer_id'])) {
            $v->rule('integer', 'attending_lawyer_id');
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
            $hearingId = $this->hearingService->createHearing($caseId, $data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Duruşma kaydı başarıyla oluşturuldu.',
                'data' => ['id' => $hearingId]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        }
        catch (\Throwable $e) {
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
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 15);
        $status = $params['status'] ?? null;
        $date = $params['date'] ?? null;

        try {
            $result = $this->hearingService->getAllHearings($page, $limit, $status, $date);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'meta' => $result['meta']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        }
        catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function listByCase(Request $request, Response $response, array $args): Response
    {
        $caseId = (int)$args['caseId'];

        try {
            $hearings = $this->hearingService->getHearingsByCase($caseId);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $hearings
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        }
        catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $hearingId = (int)$args['id'];

        try {
            $hearing = $this->hearingService->getHearingById($hearingId);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $hearing
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        }
        catch (\Throwable $e) {
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
        $hearingId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        if (isset($data['hearing_type'])) {
            $v->rule('in', 'hearing_type', $this->lookupService->getValuesForValidation('hearing_types'));
        }
        if (isset($data['status'])) {
            $v->rule('in', 'status', ['Planlandı', 'Tamamlandı', 'Yapıldı', 'Ertelendi', 'İptal']);
        }
        if (isset($data['attending_lawyer_id'])) {
            $v->rule('integer', 'attending_lawyer_id');
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
            $this->hearingService->updateHearing($hearingId, $data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Duruşma kaydı başarıyla güncellendi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        }
        catch (\Throwable $e) {
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
        $hearingId = (int)$args['id'];

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->hearingService->deleteHearing($hearingId, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Duruşma başarıyla arşivlendi/iptal edildi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        }
        catch (\Throwable $e) {
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