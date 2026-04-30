<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ClientService;
use App\Helper\ValidationHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator;
use Exception;

class ClientController
{
    private ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function create(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        $v->rule('required', 'client_type');
        $v->rule('in', 'client_type', ['Bireysel', 'Kurumsal']);

        if (isset($data['client_type']) && $data['client_type'] === 'Bireysel') {
            $v->rule('required', ['first_name', 'last_name']);
            if (!empty($data['tc_no'])) {
                $v->rule('length', 'tc_no', 11);
                $v->rule('numeric', 'tc_no');
            }
        }
        elseif (isset($data['client_type']) && $data['client_type'] === 'Kurumsal') {
            $v->rule('required', 'company_name');
            if (!empty($data['tax_no'])) {
                $v->rule('length', 'tax_no', 10);
                $v->rule('numeric', 'tax_no');
            }
        }

        if (!$v->validate()) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Doğrulama hatası',
                'errors' => $v->errors()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // TC/VKN checksum doğrulaması
        $checksumErrors = [];
        if (!empty($data['tc_no']) && !ValidationHelper::validateTcKimlikNo($data['tc_no'])) {
            $checksumErrors['tc_no'] = ['Geçersiz TC Kimlik Numarası. Lütfen kontrol ediniz.'];
        }
        if (!empty($data['tax_no']) && !ValidationHelper::validateVkn($data['tax_no'])) {
            $checksumErrors['tax_no'] = ['Geçersiz Vergi Kimlik Numarası. Lütfen kontrol ediniz.'];
        }
        if (!empty($checksumErrors)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Doğrulama hatası',
                'errors' => $checksumErrors
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $clientId = $this->clientService->createClient($data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Müvekkil başarıyla oluşturuldu.',
                'data' => ['id' => $clientId]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        }
        catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $message = $code >= 500 ? 'Sunucu Hatası: ' . $e->getMessage() : $e->getMessage();

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

        try {
            $result = $this->clientService->getClients($page, $limit, $search);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'meta' => $result['meta']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        }
        catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Müvekkiller listelenirken hata oluştu.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $clientId = (int)$args['id'];

        try {
            $client = $this->clientService->getClientById($clientId);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $client
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
        $clientId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        if (isset($data['client_type'])) {
            $v->rule('in', 'client_type', ['Bireysel', 'Kurumsal']);
            if ($data['client_type'] === 'Bireysel') {
                if (isset($data['tc_no']) && !empty($data['tc_no'])) {
                    $v->rule('length', 'tc_no', 11);
                    $v->rule('numeric', 'tc_no');
                }
            }
            elseif ($data['client_type'] === 'Kurumsal') {
                if (isset($data['tax_no']) && !empty($data['tax_no'])) {
                    $v->rule('length', 'tax_no', 10);
                    $v->rule('numeric', 'tax_no');
                }
            }
        }

        if (!$v->validate()) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Doğrulama hatası',
                'errors' => $v->errors()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // TC/VKN checksum doğrulaması
        $checksumErrors = [];
        if (!empty($data['tc_no']) && !ValidationHelper::validateTcKimlikNo($data['tc_no'])) {
            $checksumErrors['tc_no'] = ['Geçersiz TC Kimlik Numarası. Lütfen kontrol ediniz.'];
        }
        if (!empty($data['tax_no']) && !ValidationHelper::validateVkn($data['tax_no'])) {
            $checksumErrors['tax_no'] = ['Geçersiz Vergi Kimlik Numarası. Lütfen kontrol ediniz.'];
        }
        if (!empty($checksumErrors)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Doğrulama hatası',
                'errors' => $checksumErrors
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->clientService->updateClient($clientId, $data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Müvekkil başarıyla güncellendi.'
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
        $clientId = (int)$args['id'];

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->clientService->deleteClient($clientId, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Müvekkil başarıyla arşivlendi (soft delete).'
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