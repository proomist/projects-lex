<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use App\Helper\AuthHelper;
use Exception;

class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(string $username, string $password, string $ipAddress): array
    {
        // Kullanıcı adı veya e-posta ile giriş yapılabilmesi için güncelledik
        $user = $this->userRepository->findByUsernameOrEmail($username, $username);

        if (!$user) {
            // Log failed attempt
            $this->userRepository->logActivity(0, 'login_failed_user_not_found', 'Auth', $ipAddress);
            throw new Exception('Kullanıcı adı veya şifre hatalı.', 401);
        }

        if (!AuthHelper::verifyPassword($password, $user['password_hash'])) {
            // Log failed attempt
            $this->userRepository->logActivity((int)$user['id'], 'login_failed_wrong_password', 'Auth', $ipAddress);
            throw new Exception('Kullanıcı adı veya şifre hatalı.', 401);
        }

        if ($user['status'] === 'Pasif') {
            throw new Exception('Hesabınız pasif durumdadır. Yöneticinizle iletişime geçin.', 403);
        }

        // Login başarılı
        $this->userRepository->logActivity((int)$user['id'], 'login_success', 'Auth', $ipAddress);

        $token = AuthHelper::createJwt((int)$user['id'], $user['title']);

        return [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'title' => $user['title']
            ]
        ];
    }

    public function createUser(array $data, int $createdByUserId, string $ipAddress): int
    {
        // Check if user exists
        $existing = $this->userRepository->findByUsernameOrEmail($data['username'], $data['email']);
        if ($existing) {
            throw new Exception('Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor.', 409);
        }

        $passwordHash = AuthHelper::hashPassword($data['password']);

        $insertData = [
            'username' => $data['username'],
            'password_hash' => $passwordHash,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'title' => $data['title'],
            'status' => $data['status'] ?? 'Aktif'
        ];

        $userId = $this->userRepository->create($insertData);
        
        $this->userRepository->logActivity($createdByUserId, 'user_created', 'UserManagement', $ipAddress);

        return $userId;
    }

    public function getUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function updateUser(int $id, array $data, int $updatedByUserId, string $ipAddress): void
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new Exception('Kullanıcı bulunamadı.', 404);
        }

        if (isset($data['username']) || isset($data['email'])) {
            $candidateUsername = isset($data['username']) ? (string)$data['username'] : (string)$user['username'];
            $candidateEmail = isset($data['email']) ? (string)$data['email'] : (string)$user['email'];

            $existing = $this->userRepository->findByUsernameOrEmail($candidateUsername, $candidateEmail);
            if ($existing && (int)$existing['id'] !== $id) {
                throw new Exception('Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor.', 409);
            }
        }

        $updateData = [];
        
        if (isset($data['username'])) $updateData['username'] = $data['username'];
        if (isset($data['first_name'])) $updateData['first_name'] = $data['first_name'];
        if (isset($data['last_name'])) $updateData['last_name'] = $data['last_name'];
        if (isset($data['email'])) $updateData['email'] = $data['email'];
        if (isset($data['phone'])) $updateData['phone'] = $data['phone'];
        if (isset($data['title'])) $updateData['title'] = $data['title'];
        if (isset($data['status'])) $updateData['status'] = $data['status'];
        
        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password_hash'] = AuthHelper::hashPassword($data['password']);
        }

        if (empty($updateData)) {
            return; // Değişecek bir şey yok
        }

        $this->userRepository->update($id, $updateData);
        $this->userRepository->logActivity($updatedByUserId, 'user_updated', 'UserManagement', $ipAddress);
    }

    public function deleteUser(int $id, int $deletedByUserId, string $ipAddress): void
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new Exception('Kullanıcı bulunamadı.', 404);
        }

        if ($user['title'] === 'Kurucu Ortak') {
            throw new Exception('Kurucu Ortak hesapları silinemez.', 403);
        }

        $this->userRepository->softDelete($id);
        $this->userRepository->logActivity($deletedByUserId, 'user_deleted', 'UserManagement', $ipAddress);
    }
}
