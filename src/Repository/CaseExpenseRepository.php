<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class CaseExpenseRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO case_expenses (case_id, expense_type, amount, expense_date, paid_by, charged_to_debtor, description, transaction_id) 
                VALUES (:case_id, :expense_type, :amount, :expense_date, :paid_by, :charged_to_debtor, :description, :transaction_id)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'case_id' => $data['case_id'],
            'expense_type' => $data['expense_type'],
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'],
            'paid_by' => $data['paid_by'],
            'charged_to_debtor' => $data['charged_to_debtor'] ?? 1,
            'description' => $data['description'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getByCaseId(int $caseId): array
    {
        $sql = "SELECT e.*, t.payment_method 
                FROM case_expenses e
                LEFT JOIN financial_transactions t ON e.transaction_id = t.id
                WHERE e.case_id = :case_id AND e.is_deleted = 0 
                ORDER BY e.expense_date DESC, e.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['case_id' => $caseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
