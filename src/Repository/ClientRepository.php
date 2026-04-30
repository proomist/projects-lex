<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class ClientRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function generateClientCode(): string
    {
        // Örn: M-2025-0001 formatı
        $year = date('Y');
        $prefix = "M-$year-";
        
        $stmt = $this->db->prepare("SELECT client_code FROM clients WHERE client_code LIKE :prefix ORDER BY id DESC LIMIT 1");
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
        $sql = "INSERT INTO clients (
            client_code, client_type, first_name, last_name, 
            national_id_encrypted, birth_date, profession, 
            company_name, tax_number_encrypted, tax_office, 
            trade_registry_no, mersis_no, authorized_person,
            status, default_lawyer_id, notes
        ) VALUES (
            :client_code, :client_type, :first_name, :last_name, 
            :national_id_encrypted, :birth_date, :profession, 
            :company_name, :tax_number_encrypted, :tax_office, 
            :trade_registry_no, :mersis_no, :authorized_person,
            :status, :default_lawyer_id, :notes
        )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int)$this->db->lastInsertId();
    }

    public function addContact(int $clientId, string $type, ?string $subType, string $encryptedValue, bool $isPrimary): void
    {
        $sql = "INSERT INTO client_contacts (client_id, contact_type, sub_type, contact_value_encrypted, is_primary) 
                VALUES (:client_id, :contact_type, :sub_type, :contact_value_encrypted, :is_primary)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'client_id' => $clientId,
            'contact_type' => $type,
            'sub_type' => $subType,
            'contact_value_encrypted' => $encryptedValue,
            'is_primary' => $isPrimary ? 1 : 0
        ]);
    }

    public function findAll(int $limit, int $offset, ?string $search = null): array
    {
        $params = [];
        $whereClause = "WHERE is_deleted = 0";
        
        if ($search) {
            $whereClause .= " AND (
                client_code LIKE :search 
                OR first_name LIKE :search 
                OR last_name LIKE :search 
                OR company_name LIKE :search
            )";
            $params['search'] = "%$search%";
        }

        $sql = "SELECT id, client_code, client_type, first_name, last_name, company_name,
                       national_id_encrypted, tax_number_encrypted, tax_office,
                       status, created_at 
                FROM clients 
                $whereClause 
                ORDER BY created_at DESC
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

    public function countAll(?string $search = null): int
    {
        $params = [];
        $whereClause = "WHERE is_deleted = 0";
        
        if ($search) {
            $whereClause .= " AND (
                client_code LIKE :search 
                OR first_name LIKE :search 
                OR last_name LIKE :search 
                OR company_name LIKE :search
            )";
            $params['search'] = "%$search%";
        }

        $sql = "SELECT COUNT(id) FROM clients $whereClause";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT c.*, u.first_name as lawyer_first_name, u.last_name as lawyer_last_name 
                FROM clients c 
                LEFT JOIN users u ON c.default_lawyer_id = u.id 
                WHERE c.id = :id AND c.is_deleted = 0";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $client = $stmt->fetch();

        return $client !== false ? $client : null;
    }

    public function getContactsByClientId(int $clientId): array
    {
        $sql = "SELECT id, contact_type, sub_type, contact_value_encrypted, is_primary 
                FROM client_contacts 
                WHERE client_id = :id 
                ORDER BY is_primary DESC, id ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $clientId]);
        return $stmt->fetchAll();
    }

    /** @var string[] Güncellenmesine izin verilen kolon isimleri */
    private const UPDATABLE_COLUMNS = [
        'client_type', 'first_name', 'last_name',
        'national_id_encrypted', 'birth_date', 'profession',
        'company_name', 'tax_number_encrypted', 'tax_office',
        'trade_registry_no', 'mersis_no', 'authorized_person',
        'status', 'default_lawyer_id', 'notes',
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

        $sql = 'UPDATE clients SET ' . implode(', ', $setParts) . ' WHERE id = :id AND is_deleted = 0';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }
    
    public function deleteContactsByClientId(int $clientId): void
    {
        $stmt = $this->db->prepare("DELETE FROM client_contacts WHERE client_id = :id");
        $stmt->execute(['id' => $clientId]);
    }

    public function updateContactValue(int $contactId, string $encryptedValue, bool $isPrimary): void
    {
        $sql = "UPDATE client_contacts
                SET contact_value_encrypted = :contact_value_encrypted,
                    is_primary = :is_primary
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $contactId,
            'contact_value_encrypted' => $encryptedValue,
            'is_primary' => $isPrimary ? 1 : 0,
        ]);
    }

    public function deleteContactById(int $contactId): void
    {
        $stmt = $this->db->prepare("DELETE FROM client_contacts WHERE id = :id");
        $stmt->execute(['id' => $contactId]);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE clients SET is_deleted = 1, status = 'Pasif' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
