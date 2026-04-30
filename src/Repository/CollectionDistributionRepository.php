<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class CollectionDistributionRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function createCollection(array $data): int
    {
        $sql = "INSERT INTO case_collections (case_id, source, gross_amount, deductions, net_amount, collection_date, description, status) 
                VALUES (:case_id, :source, :gross_amount, :deductions, :net_amount, :collection_date, :description, :status)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'case_id' => $data['case_id'],
            'source' => $data['source'],
            'gross_amount' => $data['gross_amount'],
            'deductions' => $data['deductions'] ?? 0,
            'net_amount' => $data['net_amount'],
            'collection_date' => $data['collection_date'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'Havuza Alındı'
        ]);

        if (!$result) {
            $err = $stmt->errorInfo();
            throw new \Exception("Veritabanı hatası: " . ($err[2] ?? 'Bilinmeyen SQL hatası'));
        }

        return (int)$this->db->lastInsertId();
    }

    public function getCollectionsByCaseId(int $caseId): array
    {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(d.id) FROM case_distributions d WHERE d.collection_id = c.id) as distribution_count
                FROM case_collections c 
                WHERE c.case_id = :case_id AND c.is_deleted = 0 
                ORDER BY c.collection_date DESC, c.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['case_id' => $caseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createDistribution(array $data): int
    {
        $sql = "INSERT INTO case_distributions (collection_id, distribution_date, opposing_attorney_fee, expense_refund, client_attorney_fee, client_net_payment, description) 
                VALUES (:collection_id, :distribution_date, :opposing_attorney_fee, :expense_refund, :client_attorney_fee, :client_net_payment, :description)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'collection_id' => $data['collection_id'],
            'distribution_date' => $data['distribution_date'],
            'opposing_attorney_fee' => $data['opposing_attorney_fee'] ?? 0,
            'expense_refund' => $data['expense_refund'] ?? 0,
            'client_attorney_fee' => $data['client_attorney_fee'] ?? 0,
            'client_net_payment' => $data['client_net_payment'] ?? 0,
            'description' => $data['description'] ?? null
        ]);

        if (!$result) {
            $err = $stmt->errorInfo();
            throw new \Exception("Dağıtım kaydedilemedi. SQL Hatası: " . ($err[2] ?? 'Bilinmeyen hata'));
        }

        $id = (int)$this->db->lastInsertId();

        // Update collection status
        $updateSql = "UPDATE case_collections SET status = 'Dağıtıldı' WHERE id = :cid";
        $this->db->prepare($updateSql)->execute(['cid' => $data['collection_id']]);

        return $id;
    }

    public function getDistributionsByCaseId(int $caseId): array
    {
        $sql = "SELECT d.*, c.source, c.net_amount as collection_net_amount 
                FROM case_distributions d
                JOIN case_collections c ON d.collection_id = c.id
                WHERE c.case_id = :case_id AND c.is_deleted = 0
                ORDER BY d.distribution_date DESC, d.id DESC";
        $stmt = $this->db->prepare($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteCollection(int $collectionId): bool
    {
        $sql = "UPDATE case_collections SET is_deleted = 1 WHERE id = :id AND status = 'Havuza Alındı'";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(['id' => $collectionId]);
        
        if (!$result) {
            $err = $stmt->errorInfo();
            throw new \Exception("Tahsilat silinemedi. SQL Hatası: " . ($err[2] ?? 'Bilinmeyen hata'));
        }

        return $stmt->rowCount() > 0;
    }
}
