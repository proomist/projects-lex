<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use App\Service\LookupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator;
use Exception;

class UserController
{
    private UserService $userService;
    private LookupService $lookupService;

    public function __construct(UserService $userService, LookupService $lookupService)
    {
        $this->userService = $userService;
        $this->lookupService = $lookupService;
    }

    public function create(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        $v->rule('required', ['username', 'password', 'first_name', 'last_name', 'email', 'title']);
        $v->rule('email', 'email');
        $v->rule('lengthMin', 'password', 8);
        $v->rule('in', 'title', $this->lookupService->getValuesForValidation('user_titles'));
        if (isset($data['status'])) {
            $v->rule('in', 'status', ['Aktif', 'Pasif', 'Askıda']);
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
            $userId = $this->userService->createUser($data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Kullanıcı başarıyla oluşturuldu.',
                'data' => ['id' => $userId]
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
        try {
            $users = $this->userService->getUsers();
            
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $users
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Throwable $e) {
            $code = (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Kullanıcı listesi alınırken hata oluştu.',
                'detail' => $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : null
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwt_payload');

        $userId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        if (isset($data['title'])) {
            $v->rule('in', 'title', $this->lookupService->getValuesForValidation('user_titles'));
        }
        if (isset($data['status'])) {
            $v->rule('in', 'status', ['Aktif', 'Pasif', 'Askıda']);
        }
        if (isset($data['email'])) {
            $v->rule('email', 'email');
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $v->rule('lengthMin', 'password', 8);
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
            $this->userService->updateUser($userId, $data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Kullanıcı başarıyla güncellendi.'
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

        $userId = (int)$args['id'];

        // Kendi kendini silememe kontrolü
        if ($userId === $payload->user_id) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Kendi hesabınızı silemezsiniz.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->userService->deleteUser($userId, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Kullanıcı başarıyla silindi (pasife alındı).'
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
