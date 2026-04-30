<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class HearingRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO hearings (
            case_id, hearing_date, hall_name, hearing_type, 
            attending_lawyer_id, status, summary_notes, next_hearing_date
        ) VALUES (
            :case_id, :hearing_date, :hall_name, :hearing_type, 
            :attending_lawyer_id, :status, :summary_notes, :next_hearing_date
        )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int)$this->db->lastInsertId();
    }

    public function findAllByCaseId(int $caseId): array
    {
        $sql = "SELECT h.*, u.first_name as lawyer_first_name, u.last_name as lawyer_last_name 
                FROM hearings h 
                LEFT JOIN users u ON h.attending_lawyer_id = u.id 
                WHERE h.case_id = :case_id AND h.is_deleted = 0 
                ORDER BY h.hearing_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['case_id' => $caseId]);
        return $stmt->fetchAll();
    }

    public function findAll(int $page = 1, int $limit = 15, ?string $status = null, ?string $date = null): array
    {
        $where = ['h.is_deleted = 0'];
        $params = [];

        if ($status) {
            $where[] = 'h.status = :status';
            $params['status'] = $status;
        }
        if ($date) {
            $where[] = 'DATE(h.hearing_date) = :date';
            $params['date'] = $date;
        }

        $whereClause = implode(' AND ', $where);

        // Toplam kayıt sayısı
        $countSql = "SELECT COUNT(*) FROM hearings h WHERE {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = (int)$countStmt->fetchColumn();

        // Sayfalama
        $offset = ($page - 1) * $limit;
        $sql = "SELECT h.*, 
                       u.first_name as lawyer_first_name, u.last_name as lawyer_last_name,
                       c.case_no, c.merits_no as base_no
                FROM hearings h 
                LEFT JOIN users u ON h.attending_lawyer_id = u.id 
                LEFT JOIN cases c ON h.case_id = c.id
                WHERE {$whereClause}
                ORDER BY h.hearing_date DESC
                LIMIT :_limit OFFSET :_offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':_limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':_offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        return [
            'data' => $data,
            'meta' => [
                'total_records' => $totalRecords,
                'current_page' => $page,
                'total_pages' => (int)ceil($totalRecords / $limit),
                'limit' => $limit
            ]
        ];
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT h.*, u.first_name as lawyer_first_name, u.last_name as lawyer_last_name, c.case_no 
                FROM hearings h 
                LEFT JOIN users u ON h.attending_lawyer_id = u.id 
                JOIN cases c ON h.case_id = c.id
                WHERE h.id = :id AND h.is_deleted = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $hearing = $stmt->fetch();

        return $hearing !== false ? $hearing : null;
    }

    /** @var string[] Güncellenmesine izin verilen kolon isimleri */
    private const UPDATABLE_COLUMNS = [
        'hearing_date', 'hall_name', 'hearing_type',
        'attending_lawyer_id', 'status', 'summary_notes', 'next_hearing_date',
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

        $sql = 'UPDATE hearings SET ' . implode(', ', $setParts) . ' WHERE id = :id AND is_deleted = 0';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE hearings SET is_deleted = 1, status = 'İptal' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}