<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class FeeAgreementRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO fee_agreements (client_id, case_id, fee_type, fee_amount, fee_percentage, is_vat_included, notes) 
                VALUES (:client_id, :case_id, :fee_type, :fee_amount, :fee_percentage, :is_vat_included, :notes)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'client_id' => $data['client_id'],
            'case_id' => $data['case_id'] ?? null,
            'fee_type' => $data['fee_type'],
            'fee_amount' => $data['fee_amount'] ?? null,
            'fee_percentage' => $data['fee_percentage'] ?? null,
            'is_vat_included' => $data['is_vat_included'] ?? 1,
            'notes' => $data['notes'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function findByCaseId(int $caseId): ?array
    {
        $sql = "SELECT * FROM fee_agreements WHERE case_id = :case_id AND is_deleted = 0 ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['case_id' => $caseId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function update(int $id, array $data): void
    {
        $setParts = [];
        $params = ['id' => $id];

        $allowedCols = ['fee_type', 'fee_amount', 'fee_percentage', 'is_vat_included', 'notes'];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedCols, true)) {
                $setParts[] = "$key = :$key";
                $params[$key] = $value;
            }
        }

        if (empty($setParts)) {
            return;
        }

        $sql = "UPDATE fee_agreements SET " . implode(', ', $setParts) . " WHERE id = :id AND is_deleted = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }
}
