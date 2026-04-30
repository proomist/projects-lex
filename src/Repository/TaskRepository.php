<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class TaskRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO tasks (
            title, description, task_type, priority, status,
            start_date, due_date, completed_at,
            client_id, case_id, parent_task_id,
            assigned_by, assigned_to
        ) VALUES (
            :title, :description, :task_type, :priority, :status,
            :start_date, :due_date, :completed_at,
            :client_id, :case_id, :parent_task_id,
            :assigned_by, :assigned_to
        )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int)$this->db->lastInsertId();
    }

    public function addChecklistItem(int $taskId, string $title, int $sortOrder): void
    {
        $sql = "INSERT INTO task_checklists (task_id, title, sort_order) VALUES (:task_id, :title, :sort_order)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'task_id' => $taskId,
            'title' => $title,
            'sort_order' => $sortOrder
        ]);
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT t.*, 
                       u1.first_name as assignee_first_name, u1.last_name as assignee_last_name,
                       u2.first_name as assigner_first_name, u2.last_name as assigner_last_name,
                       c.case_no, cl.first_name as client_first_name, cl.last_name as client_last_name, cl.company_name
                FROM tasks t
                LEFT JOIN users u1 ON t.assigned_to = u1.id
                LEFT JOIN users u2 ON t.assigned_by = u2.id
                LEFT JOIN cases c ON t.case_id = c.id
                LEFT JOIN clients cl ON t.client_id = cl.id
                WHERE t.id = :id AND t.is_deleted = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $task = $stmt->fetch();

        return $task !== false ? $task : null;
    }

    public function getChecklistByTaskId(int $taskId): array
    {
        $sql = "SELECT * FROM task_checklists WHERE task_id = :task_id ORDER BY sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    public function getSubTasksByTaskId(int $parentId): array
    {
        $sql = "SELECT id, title, status, priority, due_date, assigned_to 
                FROM tasks 
                WHERE parent_task_id = :parent_id AND is_deleted = 0 
                ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['parent_id' => $parentId]);
        return $stmt->fetchAll();
    }

    public function findAll(int $limit, int $offset, ?int $assignedTo = null, ?int $caseId = null, ?string $status = null): array
    {
        $params = [];
        $whereClause = "WHERE t.is_deleted = 0 AND t.parent_task_id IS NULL"; // Sadece ana görevleri listele
        
        if ($assignedTo !== null) {
            $whereClause .= " AND t.assigned_to = :assigned_to";
            $params['assigned_to'] = $assignedTo;
        }
        
        if ($caseId !== null) {
            $whereClause .= " AND t.case_id = :case_id";
            $params['case_id'] = $caseId;
        }

        if ($status !== null) {
            $whereClause .= " AND t.status = :status";
            $params['status'] = $status;
        }

        $sql = "SELECT t.*, 
                       u.first_name as assignee_first_name, u.last_name as assignee_last_name,
                       c.case_no 
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN cases c ON t.case_id = c.id
                $whereClause 
                ORDER BY t.due_date ASC, t.created_at DESC
                LIMIT :_limit OFFSET :_offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':_limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':_offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(?int $assignedTo = null, ?int $caseId = null, ?string $status = null): int
    {
        $params = [];
        $whereClause = "WHERE is_deleted = 0 AND parent_task_id IS NULL";
        
        if ($assignedTo !== null) {
            $whereClause .= " AND assigned_to = :assigned_to";
            $params['assigned_to'] = $assignedTo;
        }
        
        if ($caseId !== null) {
            $whereClause .= " AND case_id = :case_id";
            $params['case_id'] = $caseId;
        }

        if ($status !== null) {
            $whereClause .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql = "SELECT COUNT(id) FROM tasks $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /** @var string[] Güncellenmesine izin verilen kolon isimleri */
    private const UPDATABLE_COLUMNS = [
        'title', 'description', 'task_type', 'priority', 'status',
        'start_date', 'due_date', 'completed_at',
        'client_id', 'case_id', 'parent_task_id',
        'assigned_by', 'assigned_to',
    ];

    public function update(int $id, array $data): void
    {
        $setParts = [];
        $params = ['id' => $id];

        foreach ($data as $key => $value) {
            if (!in_array($key, self::UPDATABLE_COLUMNS, true)) {
                continue;
            }
            $setParts[] = "$key = :$key";
            $params[$key] = $value;
        }

        if (empty($setParts)) {
            return;
        }

        $sql = 'UPDATE tasks SET ' . implode(', ', $setParts) . ' WHERE id = :id AND is_deleted = 0';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }
    
    public function updateChecklistItemStatus(int $itemId, int $taskId, bool $isCompleted): void
    {
        $sql = "UPDATE task_checklists SET is_completed = :is_completed WHERE id = :id AND task_id = :task_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'is_completed' => $isCompleted ? 1 : 0,
            'id' => $itemId,
            'task_id' => $taskId
        ]);
    }

    public function softDelete(int $id): void
    {
        // Görev iptal edildiğinde alt görevleri de iptal et
        $stmt = $this->db->prepare("UPDATE tasks SET is_deleted = 1, status = 'İptal' WHERE id = :id OR parent_task_id = :id");
        $stmt->execute(['id' => $id]);
    }
}
