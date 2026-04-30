<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\TaskService;
use App\Service\LookupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Valitron\Validator;
use Exception;

class TaskController
{
    private TaskService $taskService;
    private LookupService $lookupService;

    public function __construct(TaskService $taskService, LookupService $lookupService)
    {
        $this->taskService = $taskService;
        $this->lookupService = $lookupService;
    }

    public function create(Request $request, Response $response): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        $v->rule('required', ['title', 'task_type', 'assigned_to']);
        $v->rule('integer', 'assigned_to');
        if (isset($data['client_id']) && $data['client_id'] !== null && $data['client_id'] !== '') {
            $v->rule('integer', 'client_id');
        }
        if (isset($data['case_id']) && $data['case_id'] !== null && $data['case_id'] !== '') {
            $v->rule('integer', 'case_id');
        }
        if (isset($data['parent_task_id']) && $data['parent_task_id'] !== null && $data['parent_task_id'] !== '') {
            $v->rule('integer', 'parent_task_id');
        }
        
        if (isset($data['priority'])) {
            $v->rule('in', 'priority', $this->lookupService->getValuesForValidation('task_priorities'));
        }
        if (isset($data['status'])) {
            $v->rule('in', 'status', ['Taslak', 'Bekliyor', 'Devam Ediyor', 'Beklemede', 'Tamamlandı', 'İptal']);
        }
        if (isset($data['start_date'])) {
            $v->rule('date', 'start_date');
        }
        if (isset($data['due_date'])) {
            $v->rule('date', 'due_date');
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
            $taskId = $this->taskService->createTask($data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Görev başarıyla oluşturuldu.',
                'data' => ['id' => $taskId]
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
        
        $assignedTo = isset($params['assigned_to']) ? (int)$params['assigned_to'] : null;
        $caseId = isset($params['case_id']) ? (int)$params['case_id'] : null;
        $status = $params['status'] ?? null;

        try {
            $result = $this->taskService->getTasks($page, $limit, $assignedTo, $caseId, $status);
            
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'meta' => $result['meta']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Görevler listelenirken hata oluştu.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $taskId = (int)$args['id'];

        try {
            $task = $this->taskService->getTaskById($taskId);
            
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $task
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
        $taskId = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $v = new Validator($data);
        if (isset($data['priority'])) {
            $v->rule('in', 'priority', $this->lookupService->getValuesForValidation('task_priorities'));
        }
        if (isset($data['status'])) {
            $v->rule('in', 'status', ['Taslak', 'Bekliyor', 'Devam Ediyor', 'Beklemede', 'Tamamlandı', 'İptal']);
        }
        if (isset($data['assigned_to'])) {
            $v->rule('integer', 'assigned_to');
        }
        if (isset($data['case_id']) && $data['case_id'] !== null && $data['case_id'] !== '') {
            $v->rule('integer', 'case_id');
        }
        if (isset($data['parent_task_id']) && $data['parent_task_id'] !== null && $data['parent_task_id'] !== '') {
            $v->rule('integer', 'parent_task_id');
        }
        if (isset($data['start_date']) && $data['start_date'] !== '' && $data['start_date'] !== null) {
            $v->rule('date', 'start_date');
        }
        if (isset($data['due_date']) && $data['due_date'] !== '' && $data['due_date'] !== null) {
            $v->rule('date', 'due_date');
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
            $this->taskService->updateTask($taskId, $data, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Görev başarıyla güncellendi.'
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

    public function toggleChecklist(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $taskId = (int)$args['id'];
        $itemId = (int)$args['itemId'];
        $data = (array)$request->getParsedBody();

        if (!isset($data['is_completed'])) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Lütfen is_completed durumu gönderin (0 veya 1).'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->taskService->toggleChecklistItem($taskId, $itemId, (bool)$data['is_completed'], $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Checklist elemanı güncellendi.'
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

    public function delete(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getAttribute('jwt_payload');
        $taskId = (int)$args['id'];

        try {
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1';
            $this->taskService->deleteTask($taskId, $payload->user_id, $ipAddress);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Görev başarıyla iptal edildi (soft delete).'
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
