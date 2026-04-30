<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username AND is_deleted = 0');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        return $user !== false ? $user : null;
    }

    public function findByUsernameOrEmail(string $username, string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE (username = :u OR email = :e) AND is_deleted = 0');
        $stmt->execute(['u' => $username, 'e' => $email]);
        $user = $stmt->fetch();
        
        return $user !== false ? $user : null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, username, first_name, last_name, email, phone, title, status, last_login_at, created_at FROM users WHERE id = :id AND is_deleted = 0');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        
        return $user !== false ? $user : null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->prepare('SELECT id, username, first_name, last_name, email, phone, title, status, last_login_at, created_at FROM users WHERE is_deleted = 0 ORDER BY created_at DESC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO users (username, password_hash, first_name, last_name, email, phone, title, status) 
                VALUES (:username, :password_hash, :first_name, :last_name, :email, :phone, :title, :status)';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->db->lastInsertId();
    }

    /** @var string[] Güncellenmesine izin verilen kolon isimleri */
    private const UPDATABLE_COLUMNS = [
        'username', 'password_hash', 'first_name', 'last_name',
        'email', 'phone', 'title', 'status', 'last_login_at',
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

        $sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE id = :id AND is_deleted = 0';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET is_deleted = 1, status = \'Pasif\' WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function logActivity(int $userId, string $actionType, string $module, string $ipAddress, ?string $userAgent = null): void
    {
        $actor = $this->findById($userId);
        
        $actorUsername = $actor['username'] ?? null;
        $actorFullName = null;
        $actorTitle = $actor['title'] ?? null;
        
        if ($actor) {
            $firstName = $actor['first_name'] ?? '';
            $lastName = $actor['last_name'] ?? '';
            $actorFullName = trim($firstName . ' ' . $lastName) ?: null;
        }
        
        $stmt = $this->db->prepare('INSERT INTO activity_logs (
            user_id, 
            actor_user_id, 
            actor_username, 
            actor_full_name, 
            actor_title, 
            action_type, 
            module, 
            ip_address,
            user_agent
        ) VALUES (
            :user_id, 
            :actor_user_id, 
            :actor_username, 
            :actor_full_name, 
            :actor_title, 
            :action_type, 
            :module, 
            :ip_address,
            :user_agent
        )');
        
        $stmt->execute([
            'user_id' => $userId,
            'actor_user_id' => $userId,
            'actor_username' => $actorUsername,
            'actor_full_name' => $actorFullName,
            'actor_title' => $actorTitle,
            'action_type' => $actionType,
            'module' => $module,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);
    }
}
