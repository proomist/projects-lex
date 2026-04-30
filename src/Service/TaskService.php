<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Repository\CaseRepository;
use App\Repository\ClientRepository;
use Exception;

class TaskService
{
    private TaskRepository $taskRepository;
    private UserRepository $userRepository;
    private CaseRepository $caseRepository;
    private ClientRepository $clientRepository;

    public function __construct(
        TaskRepository $taskRepository,
        UserRepository $userRepository,
        CaseRepository $caseRepository,
        ClientRepository $clientRepository
    ) {
        $this->taskRepository = $taskRepository;
        $this->userRepository = $userRepository;
        $this->caseRepository = $caseRepository;
        $this->clientRepository = $clientRepository;
    }

    public function createTask(array $data, int $userId, string $ipAddress): int
    {
        // Temel Doğrulamalar
        if (!empty($data['case_id'])) {
            $case = $this->caseRepository->findById((int)$data['case_id']);
            if (!$case) throw new Exception("Dosya bulunamadı.", 404);
            $clientId = $case['client_id']; // Dosyaya bağlıysa müvekkil dosyadan gelir
        } else {
            $clientId = !empty($data['client_id']) ? (int)$data['client_id'] : null;
            if ($clientId) {
                $client = $this->clientRepository->findById($clientId);
                if (!$client) throw new Exception("Müvekkil bulunamadı.", 404);
            }
        }

        $assignedTo = (int)$data['assigned_to'];
        $assignee = $this->userRepository->findById($assignedTo);
        if (!$assignee) throw new Exception("Atanacak kullanıcı bulunamadı.", 404);

        // Alt görev kontrolü (Tek seviye kuralı)
        $parentTaskId = !empty($data['parent_task_id']) ? (int)$data['parent_task_id'] : null;
        if ($parentTaskId) {
            $parent = $this->taskRepository->findById($parentTaskId);
            if (!$parent) throw new Exception("Üst görev bulunamadı.", 404);
            if ($parent['parent_task_id'] !== null) {
                throw new Exception("Alt görevin altı oluşturulamaz (Tek seviye kuralı).", 400);
            }
            // Alt görev bağlamı ana görevden devralır
            $clientId = $parent['client_id'];
            $data['case_id'] = $parent['case_id'];
        }

        $insertData = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'task_type' => $data['task_type'],
            'priority' => $data['priority'] ?? 'Normal',
            'status' => $data['status'] ?? 'Taslak',
            'start_date' => $data['start_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'completed_at' => null,
            'client_id' => $clientId,
            'case_id' => !empty($data['case_id']) ? (int)$data['case_id'] : null,
            'parent_task_id' => $parentTaskId,
            'assigned_by' => $userId,
            'assigned_to' => $assignedTo
        ];

        if ($insertData['status'] === 'Tamamlandı') {
            $insertData['completed_at'] = date('Y-m-d H:i:s');
        }

        $taskId = $this->taskRepository->create($insertData);

        // Kontrol Listesi Ekle
        if (!empty($data['checklist']) && is_array($data['checklist'])) {
            $sortOrder = 1;
            foreach ($data['checklist'] as $item) {
                if (empty($item['title'])) continue;
                $this->taskRepository->addChecklistItem($taskId, $item['title'], $sortOrder++);
            }
        }

        $this->userRepository->logActivity($userId, 'task_created', 'TaskManagement', $ipAddress);

        return $taskId;
    }

    public function getTaskById(int $id): array
    {
        $task = $this->taskRepository->findById($id);
        if (!$task) {
            throw new Exception("Görev bulunamadı.", 404);
        }

        $task['checklist'] = $this->taskRepository->getChecklistByTaskId($id);
        
        // Eğer bu bir ana görevse alt görevleri de getir
        if ($task['parent_task_id'] === null) {
            $task['sub_tasks'] = $this->taskRepository->getSubTasksByTaskId($id);
        }

        return $task;
    }

    public function getTasks(int $page = 1, int $limit = 20, ?int $assignedTo = null, ?int $caseId = null, ?string $status = null): array
    {
        $offset = ($page - 1) * $limit;
        
        $tasks = $this->taskRepository->findAll($limit, $offset, $assignedTo, $caseId, $status);
        $total = $this->taskRepository->countAll($assignedTo, $caseId, $status);

        return [
            'data' => $tasks,
            'meta' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    public function updateTask(int $id, array $data, int $userId, string $ipAddress): void
    {
        $task = $this->taskRepository->findById($id);
        if (!$task) {
            throw new Exception("Görev bulunamadı.", 404);
        }

        $updateData = [];
        $fields = ['title', 'description', 'task_type', 'priority', 'status', 'start_date', 'due_date', 'assigned_to'];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (isset($data['status'])) {
            if ($data['status'] === 'Tamamlandı' && $task['status'] !== 'Tamamlandı') {
                $updateData['completed_at'] = date('Y-m-d H:i:s');
            } elseif ($data['status'] !== 'Tamamlandı' && $task['status'] === 'Tamamlandı') {
                $updateData['completed_at'] = null; // Geri alındı
            }
        }

        if (!empty($updateData)) {
            $this->taskRepository->update($id, $updateData);
            $this->userRepository->logActivity($userId, 'task_updated', 'TaskManagement', $ipAddress);
        }
    }

    public function toggleChecklistItem(int $taskId, int $itemId, bool $isCompleted, int $userId, string $ipAddress): void
    {
        // Görevin varlığını teyit et
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new Exception("Görev bulunamadı.", 404);
        }

        $this->taskRepository->updateChecklistItemStatus($itemId, $taskId, $isCompleted);
        $this->userRepository->logActivity($userId, 'task_checklist_toggled', 'TaskManagement', $ipAddress);
    }

    public function deleteTask(int $id, int $userId, string $ipAddress): void
    {
        $task = $this->taskRepository->findById($id);
        if (!$task) {
            throw new Exception("Görev bulunamadı.", 404);
        }

        $this->taskRepository->softDelete($id);
        $this->userRepository->logActivity($userId, 'task_deleted', 'TaskManagement', $ipAddress);
    }
}
