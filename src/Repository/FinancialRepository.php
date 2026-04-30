<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class FinancialRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function createTransaction(array $data): int
    {
        $sql = "INSERT INTO financial_transactions (
            transaction_date, transaction_type, sub_type, category, amount, currency,
            tax_rate, tax_amount, total_amount, client_id, case_id,
            description, document_no, due_date, payment_method, bank_account_info, status
        ) VALUES (
            :transaction_date, :transaction_type, :sub_type, :category, :amount, :currency,
            :tax_rate, :tax_amount, :total_amount, :client_id, :case_id,
            :description, :document_no, :due_date, :payment_method, :bank_account_info, :status
        )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT t.*, c.case_no, cl.first_name, cl.last_name, cl.company_name, cl.client_type
                FROM financial_transactions t
                LEFT JOIN cases c ON t.case_id = c.id
                LEFT JOIN clients cl ON t.client_id = cl.id
                WHERE t.id = :id AND t.is_deleted = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $transaction = $stmt->fetch();

        return $transaction !== false ? $transaction : null;
    }

    /** @var string[] Güncellenmesine izin verilen kolon isimleri */
    private const UPDATABLE_COLUMNS = [
        'transaction_date', 'transaction_type', 'sub_type', 'category',
        'amount', 'currency', 'tax_rate', 'tax_amount', 'total_amount',
        'client_id', 'case_id', 'description', 'document_no',
        'due_date', 'payment_method', 'bank_account_info', 'status',
    ];

    public function updateTransaction(int $id, array $data): void
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

        $sql = 'UPDATE financial_transactions SET ' . implode(', ', $setParts) . ' WHERE id = :id AND is_deleted = 0';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE financial_transactions SET is_deleted = 1, status = 'İptal' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    /**
     * Otomatik FIFO tahsilat eşleştirmesi için, müvekkilin vadesi geçmiş veya vadesi gelmiş,
     * henüz tamamen ödenmemiş alacaklarını vade tarihine göre (en eskiden yeniye) getirir.
     */
    public function getUnpaidReceivablesByClientId(int $clientId): array
    {
        // Kısmi ödenmiş veya bekleyen/geciken alacakları bul
        $sql = "SELECT t.*,
                (SELECT COALESCE(SUM(matched_amount), 0) FROM transaction_matches WHERE receivable_id = t.id) as total_paid
                FROM financial_transactions t
                WHERE t.client_id = :client_id
                  AND t.transaction_type = 'Alacak'
                  AND t.is_deleted = 0
                  AND t.status IN ('Bekliyor', 'Vadesi Geldi', 'Kısmi Ödendi', 'Gecikti')
                HAVING (t.total_amount - total_paid) > 0
                ORDER BY t.due_date ASC, t.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public function addTransactionMatch(int $receivableId, int $collectionId, float $amount): void
    {
        $sql = "INSERT INTO transaction_matches (receivable_id, collection_id, matched_amount)
                VALUES (:receivable_id, :collection_id, :matched_amount)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'receivable_id' => $receivableId,
            'collection_id' => $collectionId,
            'matched_amount' => $amount
        ]);
    }

    /**
     * Bir tahsilata ait tüm eşleşmeleri getirir.
     */
    public function getMatchesByCollectionId(int $collectionId): array
    {
        $sql = "SELECT * FROM transaction_matches WHERE collection_id = :cid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cid' => $collectionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Bir tahsilata ait tüm eşleşmeleri siler.
     */
    public function deleteMatchesByCollectionId(int $collectionId): void
    {
        $sql = "DELETE FROM transaction_matches WHERE collection_id = :cid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cid' => $collectionId]);
    }

    /**
     * Alacağın toplam eşleşmiş tutarını hesaplayarak status'ünü günceller.
     */
    public function recalculateReceivableStatus(int $receivableId): void
    {
        $sql = "SELECT t.total_amount, COALESCE(SUM(m.matched_amount), 0) as total_paid
                FROM financial_transactions t
                LEFT JOIN transaction_matches m ON m.receivable_id = t.id
                WHERE t.id = :rid AND t.is_deleted = 0
                GROUP BY t.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['rid' => $receivableId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return;

        $totalAmount = (float)$row['total_amount'];
        $totalPaid = (float)$row['total_paid'];

        if ($totalPaid <= 0) {
            $newStatus = 'Bekliyor';
        } elseif ($totalPaid >= $totalAmount) {
            $newStatus = 'Ödendi';
        } else {
            $newStatus = 'Kısmi Ödendi';
        }

        $this->updateTransaction($receivableId, ['status' => $newStatus]);
    }

    /**
     * Bakiye Hesaplama - Dosya Bazlı (5 kalemli)
     */
    public function getCaseBalance(int $caseId): array
    {
        $sql = "SELECT
                    SUM(CASE WHEN transaction_type = 'Alacak' THEN total_amount ELSE 0 END) as total_receivable,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'ucret' THEN total_amount ELSE 0 END) as total_fee_collection,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'emanet' THEN total_amount ELSE 0 END) as total_trust_deposit,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'masraf' THEN total_amount ELSE 0 END) as total_client_expense,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'genel' THEN total_amount ELSE 0 END) as total_office_expense
                FROM financial_transactions
                WHERE case_id = :case_id AND is_deleted = 0 AND (status IS NULL OR status != 'İptal')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['case_id' => $caseId]);
        $result = $stmt->fetch();

        $receivable = (float)($result['total_receivable'] ?? 0);
        $feeCollection = (float)($result['total_fee_collection'] ?? 0);
        $trustDeposit = (float)($result['total_trust_deposit'] ?? 0);
        $clientExpense = (float)($result['total_client_expense'] ?? 0);
        $officeExpense = (float)($result['total_office_expense'] ?? 0);

        return [
            'total_receivable' => $receivable,
            'total_fee_collection' => $feeCollection,
            'total_trust_deposit' => $trustDeposit,
            'total_client_expense' => $clientExpense,
            'total_office_expense' => $officeExpense,
            'fee_debt' => $receivable - $feeCollection,
            'trust_balance' => $trustDeposit - $clientExpense,
        ];
    }

    /**
     * Bakiye Hesaplama - Müvekkil Bazlı (Genel, 5 kalemli)
     */
    public function getClientBalance(int $clientId): array
    {
        $sql = "SELECT
                    SUM(CASE WHEN transaction_type = 'Alacak' THEN total_amount ELSE 0 END) as total_receivable,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'ucret' THEN total_amount ELSE 0 END) as total_fee_collection,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'emanet' THEN total_amount ELSE 0 END) as total_trust_deposit,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'masraf' THEN total_amount ELSE 0 END) as total_client_expense,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'genel' THEN total_amount ELSE 0 END) as total_office_expense
                FROM financial_transactions
                WHERE client_id = :client_id AND is_deleted = 0 AND (status IS NULL OR status != 'İptal')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['client_id' => $clientId]);
        $result = $stmt->fetch();

        $receivable = (float)($result['total_receivable'] ?? 0);
        $feeCollection = (float)($result['total_fee_collection'] ?? 0);
        $trustDeposit = (float)($result['total_trust_deposit'] ?? 0);
        $clientExpense = (float)($result['total_client_expense'] ?? 0);
        $officeExpense = (float)($result['total_office_expense'] ?? 0);

        return [
            'total_receivable' => $receivable,
            'total_fee_collection' => $feeCollection,
            'total_trust_deposit' => $trustDeposit,
            'total_client_expense' => $clientExpense,
            'total_office_expense' => $officeExpense,
            'fee_debt' => $receivable - $feeCollection,
            'trust_balance' => $trustDeposit - $clientExpense,
        ];
    }

    /**
     * Birden fazla müvekkilin bakiyelerini tek sorguda getirir (liste sayfası için).
     * @param int[] $clientIds
     * @return array<int, array{total_receivable: float, total_fee_collection: float, total_trust_deposit: float, total_client_expense: float, fee_debt: float, trust_balance: float}>
     */
    public function getClientBalancesBatch(array $clientIds): array
    {
        if (empty($clientIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($clientIds), '?'));

        $sql = "SELECT
                    client_id,
                    SUM(CASE WHEN transaction_type = 'Alacak' THEN total_amount ELSE 0 END) as total_receivable,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'ucret' THEN total_amount ELSE 0 END) as total_fee_collection,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'emanet' THEN total_amount ELSE 0 END) as total_trust_deposit,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'masraf' THEN total_amount ELSE 0 END) as total_client_expense
                FROM financial_transactions
                WHERE client_id IN ($placeholders) AND is_deleted = 0 AND (status IS NULL OR status != 'İptal')
                GROUP BY client_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($clientIds));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $receivable = (float)($row['total_receivable'] ?? 0);
            $feeCollection = (float)($row['total_fee_collection'] ?? 0);
            $trustDeposit = (float)($row['total_trust_deposit'] ?? 0);
            $clientExpense = (float)($row['total_client_expense'] ?? 0);

            $result[(int)$row['client_id']] = [
                'total_receivable' => $receivable,
                'total_fee_collection' => $feeCollection,
                'total_trust_deposit' => $trustDeposit,
                'total_client_expense' => $clientExpense,
                'fee_debt' => $receivable - $feeCollection,
                'trust_balance' => $trustDeposit - $clientExpense,
            ];
        }

        return $result;
    }

    /**
     * Büro genel mali özeti (kâr hesabı, emanet kasası).
     */
    public function getOfficeSummary(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $params = [];
        $where = "WHERE is_deleted = 0 AND (status IS NULL OR status != 'İptal')";

        if ($dateFrom !== null) {
            $where .= " AND transaction_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo !== null) {
            $where .= " AND transaction_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $sql = "SELECT
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'ucret' THEN total_amount ELSE 0 END) as revenue,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'genel' THEN total_amount ELSE 0 END) as office_expense,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'emanet' THEN total_amount ELSE 0 END) as trust_in,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'masraf' THEN total_amount ELSE 0 END) as trust_out,
                    SUM(CASE WHEN transaction_type = 'Alacak' THEN total_amount ELSE 0 END) as total_receivable
                FROM financial_transactions $where";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $revenue = (float)($row['revenue'] ?? 0);
        $officeExpense = (float)($row['office_expense'] ?? 0);
        $trustIn = (float)($row['trust_in'] ?? 0);
        $trustOut = (float)($row['trust_out'] ?? 0);
        $receivable = (float)($row['total_receivable'] ?? 0);

        return [
            'revenue' => $revenue,
            'office_expense' => $officeExpense,
            'profit' => $revenue - $officeExpense,
            'trust_in' => $trustIn,
            'trust_out' => $trustOut,
            'trust_balance' => $trustIn - $trustOut,
            'total_receivable' => $receivable,
        ];
    }

    public function findAll(int $limit, int $offset, ?int $clientId = null, ?int $caseId = null, ?string $type = null, ?string $subType = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $params = [];
        $whereClause = "WHERE t.is_deleted = 0";

        if ($clientId !== null) {
            $whereClause .= " AND t.client_id = :client_id";
            $params['client_id'] = $clientId;
        }

        if ($caseId !== null) {
            $whereClause .= " AND t.case_id = :case_id";
            $params['case_id'] = $caseId;
        }

        if ($type !== null) {
            $whereClause .= " AND t.transaction_type = :type";
            $params['type'] = $type;
        }

        if ($subType !== null) {
            $whereClause .= " AND t.sub_type = :sub_type";
            $params['sub_type'] = $subType;
        }

        if ($dateFrom !== null) {
            $whereClause .= " AND t.transaction_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if ($dateTo !== null) {
            $whereClause .= " AND t.transaction_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $sql = "SELECT t.*, c.case_no, cl.client_type, cl.first_name, cl.last_name, cl.company_name
                FROM financial_transactions t
                LEFT JOIN cases c ON t.case_id = c.id
                LEFT JOIN clients cl ON t.client_id = cl.id
                $whereClause
                ORDER BY t.transaction_date DESC, t.created_at DESC
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

    public function countAll(?int $clientId = null, ?int $caseId = null, ?string $type = null, ?string $subType = null, ?string $dateFrom = null, ?string $dateTo = null): int
    {
        $params = [];
        $whereClause = "WHERE is_deleted = 0";

        if ($clientId !== null) {
            $whereClause .= " AND client_id = :client_id";
            $params['client_id'] = $clientId;
        }

        if ($caseId !== null) {
            $whereClause .= " AND case_id = :case_id";
            $params['case_id'] = $caseId;
        }

        if ($type !== null) {
            $whereClause .= " AND transaction_type = :type";
            $params['type'] = $type;
        }

        if ($subType !== null) {
            $whereClause .= " AND sub_type = :sub_type";
            $params['sub_type'] = $subType;
        }

        if ($dateFrom !== null) {
            $whereClause .= " AND transaction_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if ($dateTo !== null) {
            $whereClause .= " AND transaction_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $sql = "SELECT COUNT(id) FROM financial_transactions $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Belirli bir tarih öncesindeki mutabakat devir bakiyesini / toplamları getirir.
     */
    public function getCarriedForwardBalance(int $clientId, string $dateFrom): array
    {
        $sql = "SELECT
                    SUM(CASE WHEN transaction_type = 'Alacak' THEN total_amount ELSE 0 END) as total_receivable,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'ucret' THEN total_amount ELSE 0 END) as total_fee_collection,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'emanet' THEN total_amount ELSE 0 END) as total_trust_deposit,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'masraf' THEN total_amount ELSE 0 END) as total_client_expense
                FROM financial_transactions
                WHERE client_id = :client_id 
                  AND transaction_date < :date_from 
                  AND is_deleted = 0 
                  AND (status IS NULL OR status != 'İptal')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['client_id' => $clientId, 'date_from' => $dateFrom]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $receivable = (float)($result['total_receivable'] ?? 0);
        $feeCollection = (float)($result['total_fee_collection'] ?? 0);
        $trustDeposit = (float)($result['total_trust_deposit'] ?? 0);
        $clientExpense = (float)($result['total_client_expense'] ?? 0);

        return [
            'total_receivable' => $receivable,
            'total_fee_collection' => $feeCollection,
            'total_trust_deposit' => $trustDeposit,
            'total_client_expense' => $clientExpense,
            'fee_debt' => $receivable - $feeCollection,
            'trust_balance' => $trustDeposit - $clientExpense,
            'net_balance' => ($receivable - $feeCollection) - ($trustDeposit - $clientExpense)
        ];
    }

    /**
     * Mutabakat ekstresi için hareketleri kronolojik sırada getirir.
     */
    public function getStatementTransactions(int $clientId, ?string $dateFrom, ?string $dateTo): array
    {
        $params = ['client_id' => $clientId];
        $whereClause = "WHERE t.client_id = :client_id AND t.is_deleted = 0 AND (t.status IS NULL OR t.status != 'İptal')";

        if ($dateFrom) {
            $whereClause .= " AND t.transaction_date >= :date_from";
            $params['date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $whereClause .= " AND t.transaction_date <= :date_to";
            $params['date_to'] = $dateTo;
        }

        $sql = "SELECT t.*, c.case_no 
                FROM financial_transactions t
                LEFT JOIN cases c ON t.case_id = c.id
                $whereClause
                ORDER BY t.transaction_date ASC, t.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
