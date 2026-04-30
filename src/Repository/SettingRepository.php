<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;
use Exception;

class SettingRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getSettings(): array
    {
        $sql = "SELECT setting_key, setting_value FROM settings";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Varsayılan değerler
        $defaults = [
            'app_name' => 'Avukat Ofis Yönetim Sistemi',
            'company_name' => 'Örnek Hukuk Bürosu',
            'tax_office' => '',
            'tax_number' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
            'currency' => 'TRY',
            'timezone' => 'Europe/Istanbul'
        ];

        return array_merge($defaults, $results ?: []);
    }

    public function updateSettings(array $data): void
    {
        $this->db->beginTransaction();
        
        try {
            $sql = "INSERT INTO settings (setting_key, setting_value) 
                    VALUES (:key, :val) 
                    ON DUPLICATE KEY UPDATE setting_value = :val2";
            
            $stmt = $this->db->prepare($sql);
            
            // Ayarları tek tek dön ve DB'ye yaz
            $allowedKeys = ['app_name', 'company_name', 'tax_office', 'tax_number', 'address', 'phone', 'email', 'currency', 'timezone'];
            
            foreach ($allowedKeys as $key) {
                if (isset($data[$key])) {
                    $stmt->execute([
                        'key' => $key,
                        'val' => $data[$key],
                        'val2' => $data[$key]
                    ]);
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
