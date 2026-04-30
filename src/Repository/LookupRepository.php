<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;
use Exception;

class LookupRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getByGroup(string $groupKey, bool $activeOnly = true): array
    {
        $sql = "SELECT id, group_key, value, label, sort_order, is_active, is_system
                FROM lookup_values
                WHERE group_key = :group_key";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, label ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['group_key' => $groupKey]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllGroups(): array
    {
        $sql = "SELECT group_key, COUNT(*) as count
                FROM lookup_values
                GROUP BY group_key
                ORDER BY group_key ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM lookup_values WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO lookup_values (group_key, value, label, sort_order, is_active, is_system)
                VALUES (:group_key, :value, :label, :sort_order, :is_active, :is_system)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'group_key'  => $data['group_key'],
            'value'      => $data['value'],
            'label'      => $data['label'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active'  => $data['is_active'] ?? 1,
            'is_system'  => $data['is_system'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['value', 'label', 'sort_order', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return;
        }

        $sql = "UPDATE lookup_values SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $sql = "DELETE FROM lookup_values WHERE id = :id AND is_system = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() === 0) {
            throw new Exception("Sistem tanımları silinemez.", 403);
        }
    }

    public function getValuesForValidation(string $groupKey): array
    {
        $sql = "SELECT value FROM lookup_values WHERE group_key = :group_key AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['group_key' => $groupKey]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
