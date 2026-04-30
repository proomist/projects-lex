<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\SettingRepository;
use App\Repository\UserRepository;
use Exception;

class SettingService
{
    private SettingRepository $settingRepository;
    private UserRepository $userRepository;

    public function __construct(SettingRepository $settingRepository, UserRepository $userRepository)
    {
        $this->settingRepository = $settingRepository;
        $this->userRepository = $userRepository;
    }

    public function getSettings(): array
    {
        try {
            return $this->settingRepository->getSettings();
        } catch (Exception $e) {
            throw new Exception("Ayarlar alınırken hata oluştu: " . $e->getMessage(), 500);
        }
    }

    public function updateSettings(array $data, int $userId, string $ipAddress): void
    {
        try {
            $this->settingRepository->updateSettings($data);
            $this->userRepository->logActivity($userId, 'settings_updated', 'Settings', $ipAddress);
        } catch (Exception $e) {
            throw new Exception("Ayarlar güncellenirken hata oluştu: " . $e->getMessage(), 500);
        }
    }
}
