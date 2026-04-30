<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class CaseRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function generateCaseNo(): string
    {
        // Örn: D-2025-0001 formatı
        $year = date('Y');
        $prefix = "D-$year-";
        
        $stmt = $this->db->prepare("SELECT case_no FROM cases WHERE case_no LIKE :prefix ORDER BY id DESC LIMIT 1");
        $stmt->execute(['prefix' => "$prefix%"]);
        $lastCode = $stmt->fetchColumn();

        if ($lastCode) {
            $parts = explode('-', $lastCode);
            $nextNum = (int)end($parts) + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO cases (
            case_no, folder_no, case_type, case_category, open_date,
            client_id, client_position, lawyer_id,
            court_name, court_city, court_district, merits_no, decision_no,
            subject_summary, principal_amount, interest_rate, interest_type, execution_date, claimed_amount, currency, status
        ) VALUES (
            :case_no, :folder_no, :case_type, :case_category, :open_date,
            :client_id, :client_position, :lawyer_id,
            :court_name, :court_city, :court_district, :merits_no, :decision_no,
            :subject_summary, :principal_amount, :interest_rate, :interest_type, :execution_date, :claimed_amount, :currency, :status
        )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int)$this->db->lastInsertId();
    }

    public function addParty(int $caseId, string $encryptedName, ?string $encryptedTcTax, string $position, ?string $lawyerName, ?string $encryptedContactInfo): void
    {
        $sql = "INSERT INTO case_parties (case_id, full_name_encrypted, tc_tax_no_encrypted, position, lawyer_name, contact_info_encrypted) 
                VALUES (:case_id, :full_name_encrypted, :tc_tax_no_encrypted, :position, :lawyer_name, :contact_info_encrypted)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'case_id' => $caseId,
            'full_name_encrypted' => $encryptedName,
            'tc_tax_no_encrypted' => $encryptedTcTax,
            'position' => $position,
            'lawyer_name' => $lawyerName,
            'contact_info_encrypted' => $encryptedContactInfo
        ]);
    }

    public function findAll(
        int $limit,
        int $offset,
        ?string $search = null,
        ?int $clientId = null,
        ?int $lawyerId = null,
        ?string $status = null,
        ?string $type = null
    ): array
    {
        $params = [];
        $whereClause = "WHERE c.is_deleted = 0";
        
        if ($search) {
            $whereClause .= " AND (
                c.case_no LIKE :search 
                OR c.folder_no LIKE :search
                OR c.court_name LIKE :search 
                OR c.merits_no LIKE :search
                OR c.subject_summary LIKE :search
                OR cl.first_name LIKE :search
                OR cl.last_name LIKE :search
                OR cl.company_name LIKE :search
            )";
            $params['search'] = "%$search%";
        }

        if ($clientId !== null) {
            $whereClause .= " AND c.client_id = :client_id";
            $params['client_id'] = $clientId;
        }

        if ($lawyerId !== null) {
            $whereClause .= " AND c.lawyer_id = :lawyer_id";
            $params['lawyer_id'] = $lawyerId;
        }

        if ($status !== null) {
            $whereClause .= " AND c.status = :status";
            $params['status'] = $status;
        }

        if ($type !== null) {
            if (in_array($type, ['Hukuk', 'Ceza', 'İdari', 'Diğer'], true)) {
                $whereClause .= " AND c.case_type = 'Dava' AND c.case_category = :case_category_filter";
                $params['case_category_filter'] = $type;
            } else {
                $whereClause .= " AND c.case_type = :case_type";
                $params['case_type'] = $type;
            }
        }

        $sql = "SELECT c.id, c.case_no, c.folder_no, c.case_type, c.case_category, c.open_date, c.status,
                       c.client_id, c.client_position, c.court_name, c.merits_no, c.merits_no as base_no, c.subject_summary,
                       cl.client_type, cl.first_name as client_first_name, cl.last_name as client_last_name, cl.company_name as client_company_name,
                       u.first_name as lawyer_first_name, u.last_name as lawyer_last_name
                FROM cases c 
                JOIN clients cl ON c.client_id = cl.id
                JOIN users u ON c.lawyer_id = u.id
                $whereClause 
                ORDER BY c.created_at DESC
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

    public function countAll(
        ?string $search = null,
        ?int $clientId = null,
        ?int $lawyerId = null,
        ?string $status = null,
        ?string $type = null
    ): int
    {
        $params = [];
        $whereClause = "WHERE c.is_deleted = 0";
        
        if ($search) {
            $whereClause .= " AND (
                c.case_no LIKE :search 
                OR c.folder_no LIKE :search
                OR c.court_name LIKE :search 
                OR c.merits_no LIKE :search
                OR c.subject_summary LIKE :search
                OR cl.first_name LIKE :search
                OR cl.last_name LIKE :search
                OR cl.company_name LIKE :search
            )";
            $params['search'] = "%$search%";
        }

        if ($clientId !== null) {
            $whereClause .= " AND c.client_id = :client_id";
            $params['client_id'] = $clientId;
        }

        if ($lawyerId !== null) {
            $whereClause .= " AND c.lawyer_id = :lawyer_id";
            $params['lawyer_id'] = $lawyerId;
        }

        if ($status !== null) {
            $whereClause .= " AND c.status = :status";
            $params['status'] = $status;
        }

        if ($type !== null) {
            if (in_array($type, ['Hukuk', 'Ceza', 'İdari', 'Diğer'], true)) {
                $whereClause .= " AND c.case_type = 'Dava' AND c.case_category = :case_category_filter";
                $params['case_category_filter'] = $type;
            } else {
                $whereClause .= " AND c.case_type = :case_type";
                $params['case_type'] = $type;
            }
        }

        $sql = "SELECT COUNT(c.id) 
                FROM cases c
                JOIN clients cl ON c.client_id = cl.id
                $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT c.*, c.merits_no as base_no,
                       cl.client_type, cl.first_name as client_first_name, cl.last_name as client_last_name, cl.company_name as client_company_name,
                       u.first_name as lawyer_first_name, u.last_name as lawyer_last_name
                FROM cases c
                JOIN clients cl ON c.client_id = cl.id
                JOIN users u ON c.lawyer_id = u.id
                WHERE c.id = :id AND c.is_deleted = 0";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $case = $stmt->fetch();

        return $case !== false ? $case : null;
    }

    public function getPartiesByCaseId(int $caseId): array
    {
        $sql = "SELECT id, full_name_encrypted, tc_tax_no_encrypted, position, lawyer_name, contact_info_encrypted 
                FROM case_parties 
                WHERE case_id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $caseId]);
        return $stmt->fetchAll();
    }

    /** @var string[] Güncellenmesine izin verilen kolon isimleri */
    private const UPDATABLE_COLUMNS = [
        'case_no', 'folder_no', 'case_type', 'case_category', 'open_date',
        'client_id', 'client_position', 'lawyer_id',
        'court_name', 'court_city', 'court_district', 'merits_no', 'decision_no',
        'subject_summary', 'principal_amount', 'interest_rate', 'interest_type', 'execution_date', 'claimed_amount', 'currency', 'status',
        'close_date', 'closing_type', 'closing_notes',
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

        $sql = 'UPDATE cases SET ' . implode(', ', $setParts) . ' WHERE id = :id AND is_deleted = 0';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }
    
    public function deletePartiesByCaseId(int $caseId): void
    {
        $stmt = $this->db->prepare("DELETE FROM case_parties WHERE case_id = :id");
        $stmt->execute(['id' => $caseId]);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE cases SET is_deleted = 1, status = 'Arşiv' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
