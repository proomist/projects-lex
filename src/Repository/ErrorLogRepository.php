<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;
use App\Repository\Database;

class ErrorLogRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function insert(array $data): bool
    {
        $sql = "INSERT INTO error_logs
                (error_level, error_code, message, file, line, trace, request_method, request_uri, user_id, user_name, ip_address, user_agent)
                VALUES
                (:error_level, :error_code, :message, :file, :line, :trace, :request_method, :request_uri, :user_id, :user_name, :ip_address, :user_agent)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'error_level' => $data['error_level'] ?? 'ERROR',
            'error_code' => $data['error_code'] ?? null,
            'message' => $data['message'] ?? 'Bilinmeyen hata',
            'file' => isset($data['file']) ? mb_substr($data['file'], 0, 500) : null,
            'line' => $data['line'] ?? null,
            'trace' => $data['trace'] ?? null,
            'request_method' => $data['request_method'] ?? null,
            'request_uri' => isset($data['request_uri']) ? mb_substr($data['request_uri'], 0, 2048) : null,
            'user_id' => $data['user_id'] ?? null,
            'user_name' => $data['user_name'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => isset($data['user_agent']) ? mb_substr($data['user_agent'], 0, 500) : null,
        ]);
    }

    public function findAllPaginated(
        int $limit,
        int $offset,
        ?string $errorLevel = null,
        ?string $search = null,
        ?string $userName = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $params = [];
        $where = ['1=1'];

        if (!empty($errorLevel)) {
            $where[] = 'error_level = :error_level';
            $params['error_level'] = $errorLevel;
        }

        if (!empty($search)) {
            $where[] = '(message LIKE :search OR file LIKE :search OR request_uri LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($userName)) {
            $where[] = 'user_name LIKE :user_name';
            $params['user_name'] = '%' . $userName . '%';
        }

        if (!empty($dateFrom)) {
            $where[] = 'DATE(created_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $where[] = 'DATE(created_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }

        $whereClause = implode(' AND ', $where);

        $safeLimit = max(1, min(100, $limit));
        $safeOffset = max(0, $offset);

        $sql = "SELECT id, error_level, error_code, message, file, line, trace,
                       request_method, request_uri, user_id, user_name, ip_address, user_agent, created_at
                FROM error_logs
                WHERE {$whereClause}
                ORDER BY created_at DESC, id DESC
                LIMIT {$safeLimit} OFFSET {$safeOffset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function countAll(
        ?string $errorLevel = null,
        ?string $search = null,
        ?string $userName = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        $params = [];
        $where = ['1=1'];

        if (!empty($errorLevel)) {
            $where[] = 'error_level = :error_level';
            $params['error_level'] = $errorLevel;
        }

        if (!empty($search)) {
            $where[] = '(message LIKE :search OR file LIKE :search OR request_uri LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($userName)) {
            $where[] = 'user_name LIKE :user_name';
            $params['user_name'] = '%' . $userName . '%';
        }

        if (!empty($dateFrom)) {
            $where[] = 'DATE(created_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $where[] = 'DATE(created_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(id) FROM error_logs WHERE {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }
}
