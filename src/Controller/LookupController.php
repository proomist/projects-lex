<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\LookupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator;

class LookupController
{
    private LookupService $lookupService;

    public function __construct(LookupService $lookupService)
    {
        $this->lookupService = $lookupService;
    }

    public function listGroups(Request $request, Response $response): Response
    {
        try {
            $groups = $this->lookupService->getAllGroups();
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data'   => $groups,
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Gruplar alınırken hata oluştu.',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getByGroup(Request $request, Response $response, array $args): Response
    {
        try {
            $groupKey = $args['group'] ?? '';
            $params = $request->getQueryParams();
            $activeOnly = !isset($params['all']);

            $values = $this->lookupService->getByGroup($groupKey, $activeOnly);
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data'   => $values,
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Tanımlar alınırken hata oluştu.',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function create(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();

        $v = new Validator($data);
        $v->rule('required', ['group_key', 'value', 'label']);
        $v->rule('lengthMax', 'group_key', 50);
        $v->rule('lengthMax', 'value', 100);
        $v->rule('lengthMax', 'label', 100);

        if (!$v->validate()) {
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => 'Doğrulama hatası.',
                'errors'  => $v->errors(),
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(422);
        }

        try {
            $result = $this->lookupService->create($data);
            $response->getBody()->write(json_encode([
                'status'  => 'success',
                'message' => 'Tanım başarıyla eklendi.',
                'data'    => $result,
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Throwable $e) {
            $code = $this->getErrorCode($e);
            $message = $code === 500 ? 'Tanım eklenirken hata oluştu.' : $e->getMessage();
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => $message,
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) ($args['id'] ?? 0);
        $data = (array) $request->getParsedBody();

        try {
            $result = $this->lookupService->update($id, $data);
            $response->getBody()->write(json_encode([
                'status'  => 'success',
                'message' => 'Tanım güncellendi.',
                'data'    => $result,
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $code = $this->getErrorCode($e);
            $message = $code === 500 ? 'Tanım güncellenirken hata oluştu.' : $e->getMessage();
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => $message,
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) ($args['id'] ?? 0);

        try {
            $this->lookupService->delete($id);
            $response->getBody()->write(json_encode([
                'status'  => 'success',
                'message' => 'Tanım silindi.',
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $code = $this->getErrorCode($e);
            $message = $code === 500 ? 'Tanım silinirken hata oluştu.' : $e->getMessage();
            $response->getBody()->write(json_encode([
                'status'  => 'error',
                'message' => $message,
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    private function getErrorCode(\Throwable $e): int
    {
        $code = $e->getCode();
        return (is_numeric($code) && $code >= 400 && $code < 600) ? (int) $code : 500;
    }
}
