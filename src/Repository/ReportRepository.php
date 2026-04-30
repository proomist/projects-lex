<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class ReportRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getCaseStatistics(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM cases WHERE is_deleted = 0 GROUP BY status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getFinancialStatistics(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $params = [];
        $where = "WHERE is_deleted = 0 AND (status IS NULL OR status != 'İptal')";

        if ($dateFrom) {
            $where .= " AND transaction_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $where .= " AND transaction_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $sql = "SELECT transaction_type, sub_type, SUM(total_amount) as total_amount
                FROM financial_transactions $where
                GROUP BY transaction_type, sub_type";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getFinancialByCategory(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $params = [];
        $where = "WHERE is_deleted = 0 AND (status IS NULL OR status != 'İptal')";

        if ($dateFrom) {
            $where .= " AND transaction_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $where .= " AND transaction_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $sql = "SELECT transaction_type, sub_type, category, SUM(total_amount) as total_amount, COUNT(*) as count
                FROM financial_transactions $where
                GROUP BY transaction_type, sub_type, category
                ORDER BY transaction_type, sub_type, total_amount DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getTaskStatistics(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM tasks WHERE is_deleted = 0 GROUP BY status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
