<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;
use App\Repository\Database;

class ActivityLogRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findAllPaginated(
        int $limit,
        int $offset,
        ?string $actionType = null,
        ?string $module = null,
        ?string $actor = null,
        ?string $ipAddress = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $params = [];
        $where = ['1=1'];

        if (!empty($actionType)) {
            $where[] = 'al.action_type = :action_type';
            $params['action_type'] = $actionType;
        }

        if (!empty($module)) {
            $where[] = 'al.module = :module';
            $params['module'] = $module;
        }

        if (!empty($actor)) {
            $where[] = '(al.actor_username LIKE :actor OR al.actor_full_name LIKE :actor)';
            $params['actor'] = '%' . $actor . '%';
        }

        if (!empty($ipAddress)) {
            $where[] = 'al.ip_address LIKE :ip_address';
            $params['ip_address'] = '%' . $ipAddress . '%';
        }

        if (!empty($dateFrom)) {
            $where[] = 'DATE(al.created_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $where[] = 'DATE(al.created_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }

        $whereClause = implode(' AND ', $where);

        $safeLimit = max(1, min(100, $limit));
        $safeOffset = max(0, $offset);

        $sql = "SELECT
                    al.id,
                    al.user_id,
                    al.actor_user_id,
                    al.actor_username,
                    al.actor_full_name,
                    al.actor_title,
                    al.action_type,
                    al.module,
                    al.ip_address,
                    al.user_agent,
                    al.created_at
                FROM activity_logs al
                WHERE {$whereClause}
                ORDER BY al.created_at DESC, al.id DESC
                LIMIT {$safeLimit} OFFSET {$safeOffset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function countAll(
        ?string $actionType = null,
        ?string $module = null,
        ?string $actor = null,
        ?string $ipAddress = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        $params = [];
        $where = ['1=1'];

        if (!empty($actionType)) {
            $where[] = 'action_type = :action_type';
            $params['action_type'] = $actionType;
        }

        if (!empty($module)) {
            $where[] = 'module = :module';
            $params['module'] = $module;
        }

        if (!empty($actor)) {
            $where[] = '(actor_username LIKE :actor OR actor_full_name LIKE :actor)';
            $params['actor'] = '%' . $actor . '%';
        }

        if (!empty($ipAddress)) {
            $where[] = 'ip_address LIKE :ip_address';
            $params['ip_address'] = '%' . $ipAddress . '%';
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

        $sql = "SELECT COUNT(id) FROM activity_logs WHERE {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }
}
