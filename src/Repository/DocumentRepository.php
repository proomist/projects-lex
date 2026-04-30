<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;
use Exception;

class DocumentRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO documents (client_id, case_id, document_type, title, original_filename, stored_filename, file_size, mime_type, uploaded_by, notes)
                VALUES (:client_id, :case_id, :document_type, :title, :original_filename, :stored_filename, :file_size, :mime_type, :uploaded_by, :notes)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'client_id' => $data['client_id'] ?: null,
            'case_id' => $data['case_id'] ?: null,
            'document_type' => $data['document_type'],
            'title' => $data['title'],
            'original_filename' => $data['original_filename'],
            'stored_filename' => $data['stored_filename'],
            'file_size' => $data['file_size'],
            'mime_type' => $data['mime_type'],
            'uploaded_by' => $data['uploaded_by'],
            'notes' => $data['notes'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findAll(?int $clientId, ?int $caseId, int $page, int $limit): array
    {
        $where = ["d.is_deleted = 0"];
        $params = [];

        if ($clientId) {
            $where[] = "d.client_id = :client_id";
            $params['client_id'] = $clientId;
        }
        if ($caseId) {
            $where[] = "d.case_id = :case_id";
            $params['case_id'] = $caseId;
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        // Count
        $countSql = "SELECT COUNT(*) FROM documents d WHERE {$whereStr}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Data
        $sql = "SELECT d.*,
                    u.first_name AS uploader_first_name, u.last_name AS uploader_last_name,
                    c.first_name AS client_first_name, c.last_name AS client_last_name,
                    c.company_name, c.client_type,
                    ca.case_no
                FROM documents d
                LEFT JOIN users u ON d.uploaded_by = u.id
                LEFT JOIN clients c ON d.client_id = c.id
                LEFT JOIN cases ca ON d.case_id = ca.id
                WHERE {$whereStr}
                ORDER BY d.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_INT);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $total,
                'total_pages' => (int)ceil($total / $limit),
            ]
        ];
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT d.*,
                    u.first_name AS uploader_first_name, u.last_name AS uploader_last_name
                FROM documents d
                LEFT JOIN users u ON d.uploaded_by = u.id
                WHERE d.id = :id AND d.is_deleted = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function softDelete(int $id): void
    {
        $sql = "UPDATE documents SET is_deleted = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }
}
