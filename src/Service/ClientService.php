<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ClientRepository;
use App\Repository\FinancialRepository;
use App\Repository\UserRepository;
use App\Helper\CryptoHelper;
use Exception;

class ClientService
{
    private const CONTACT_FIELD_MAP = [
        'phone' => ['contact_type' => 'Telefon', 'sub_type' => 'Cep', 'is_primary' => true],
        'email' => ['contact_type' => 'E-posta', 'sub_type' => 'Birincil', 'is_primary' => true],
        'city' => ['contact_type' => 'Adres', 'sub_type' => 'Şehir', 'is_primary' => false],
        'district' => ['contact_type' => 'Adres', 'sub_type' => 'İlçe', 'is_primary' => false],
        'address' => ['contact_type' => 'Adres', 'sub_type' => 'Açık Adres', 'is_primary' => true],
    ];

    private ClientRepository $clientRepository;
    private FinancialRepository $financialRepository;
    private UserRepository $userRepository;

    public function __construct(ClientRepository $clientRepository, FinancialRepository $financialRepository, UserRepository $userRepository)
    {
        $this->clientRepository = $clientRepository;
        $this->financialRepository = $financialRepository;
        $this->userRepository = $userRepository;
    }

    public function createClient(array $data, int $userId, string $ipAddress): int
    {
        $clientCode = $this->clientRepository->generateClientCode();

        $clientType = $data['client_type'];
        $isIndividual = ($clientType === 'Bireysel');
        $isCorporate = ($clientType === 'Kurumsal');

        $nationalIdEncrypted = !empty($data['tc_no']) ?CryptoHelper::encrypt($data['tc_no']) : null;
        $taxNumberEncrypted = !empty($data['tax_no']) ?CryptoHelper::encrypt($data['tax_no']) : null;

        $insertData = [
            'client_code' => $clientCode,
            'client_type' => $clientType,
            'first_name' => $isIndividual ? ($data['first_name'] ?? null) : null,
            'last_name' => $isIndividual ? ($data['last_name'] ?? null) : null,
            'national_id_encrypted' => $isIndividual ? $nationalIdEncrypted : null,
            'birth_date' => $isIndividual ? ($data['birth_date'] ?? null) : null,
            'profession' => $isIndividual ? ($data['profession'] ?? null) : null,

            'company_name' => $isCorporate ? ($data['company_name'] ?? null) : null,
            'tax_number_encrypted' => $isCorporate ? $taxNumberEncrypted : null,
            'tax_office' => $isCorporate ? ($data['tax_office'] ?? null) : null,
            'trade_registry_no' => $isCorporate ? ($data['trade_registry_no'] ?? null) : null,
            'mersis_no' => $isCorporate ? ($data['mersis_no'] ?? null) : null,
            'authorized_person' => $isCorporate ? ($data['authorized_person'] ?? null) : null,

            'status' => $data['status'] ?? 'Aktif',
            'default_lawyer_id' => !empty($data['default_lawyer_id']) ? (int)$data['default_lawyer_id'] : null,
            'notes' => $data['notes'] ?? null
        ];

        // DB Transaction başlatılamadı çünkü pure PDO wrapper içinde. Gerekirse eklenebilir. 
        // Şimdilik sırayla ekliyoruz.
        $clientId = $this->clientRepository->create($insertData);

        // İletişim bilgilerini ekle
        if (!empty($data['contacts']) && is_array($data['contacts'])) {
            foreach ($data['contacts'] as $contact) {
                if (empty($contact['value']))
                    continue;

                $encryptedValue = CryptoHelper::encrypt($contact['value']);
                $this->clientRepository->addContact(
                    $clientId,
                    $contact['contact_type'],
                    $contact['sub_type'] ?? null,
                    $encryptedValue,
                    (bool)($contact['is_primary'] ?? false)
                );
            }
        }

        $this->syncFlatContactFields($clientId, $data);

        $this->userRepository->logActivity($userId, 'client_created', 'ClientManagement', $ipAddress);

        return $clientId;
    }

    public function getClients(int $page = 1, int $limit = 20, ?string $search = null): array
    {
        $offset = ($page - 1) * $limit;

        $clients = $this->clientRepository->findAll($limit, $offset, $search);
        $total = $this->clientRepository->countAll($search);

        // Tüm müvekkil ID'lerini topla ve tek sorguda bakiyeleri getir
        $clientIds = array_map(fn($c) => (int)$c['id'], $clients);
        $balances = $this->financialRepository->getClientBalancesBatch($clientIds);

        foreach ($clients as &$client) {
            if (!empty($client['national_id_encrypted'])) {
                $client['tc_no'] = CryptoHelper::decrypt($client['national_id_encrypted']);
            }

            if (!empty($client['tax_number_encrypted'])) {
                $client['tax_no'] = CryptoHelper::decrypt($client['tax_number_encrypted']);
            }

            unset($client['national_id_encrypted'], $client['tax_number_encrypted']);

            $flatContacts = $this->extractFlatContactFields((int)$client['id']);
            foreach ($flatContacts as $field => $value) {
                $client[$field] = $value;
            }

            // Mali bakiye verilerini ekle (5 kalemli)
            $cid = (int)$client['id'];
            $defaultBalance = [
                'total_receivable' => 0, 'total_fee_collection' => 0,
                'total_trust_deposit' => 0, 'total_client_expense' => 0,
                'fee_debt' => 0, 'trust_balance' => 0
            ];
            $balance = $balances[$cid] ?? $defaultBalance;
            $client['total_receivable'] = $balance['total_receivable'];
            $client['total_fee_collection'] = $balance['total_fee_collection'];
            $client['fee_debt'] = $balance['fee_debt'];
            $client['trust_balance'] = $balance['trust_balance'];
        }
        unset($client);

        return [
            'data' => $clients,
            'meta' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    public function getClientById(int $id): array
    {
        $client = $this->clientRepository->findById($id);

        if (!$client) {
            throw new Exception("Müvekkil bulunamadı.", 404);
        }

        // Hassas verileri çöz
        if (!empty($client['national_id_encrypted'])) {
            $client['tc_no'] = CryptoHelper::decrypt($client['national_id_encrypted']);
        }
        if (!empty($client['tax_number_encrypted'])) {
            $client['tax_no'] = CryptoHelper::decrypt($client['tax_number_encrypted']);
        }

        unset($client['national_id_encrypted']);
        unset($client['tax_number_encrypted']);

        // İletişim bilgilerini getir ve çöz
        $contactsRaw = $this->clientRepository->getContactsByClientId($id);
        $client['contacts'] = $this->mapContactsForResponse($contactsRaw, $client);

        return $client;
    }

    public function updateClient(int $id, array $data, int $userId, string $ipAddress): void
    {
        $client = $this->clientRepository->findById($id);
        if (!$client) {
            throw new Exception("Müvekkil bulunamadı.", 404);
        }

        $updateData = [];

        $nextClientType = $client['client_type'];
        if (isset($data['client_type'])) {
            $nextClientType = $data['client_type'];
            $updateData['client_type'] = $nextClientType;
        }

        // Sadece gelen (set edilen) verileri güncelle
        $fields = [
            'first_name', 'last_name', 'birth_date', 'profession',
            'company_name', 'tax_office', 'trade_registry_no', 'mersis_no', 'authorized_person',
            'status', 'default_lawyer_id', 'notes'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                // Tip bazlı null'lama eklenebilir ama şu an basitçe gelen veriyi update edelim
                $updateData[$field] = $data[$field];
            }
        }

        if (array_key_exists('tc_no', $data)) {
            $updateData['national_id_encrypted'] = !empty($data['tc_no']) ?CryptoHelper::encrypt($data['tc_no']) : null;
        }

        if (array_key_exists('tax_no', $data)) {
            $updateData['tax_number_encrypted'] = !empty($data['tax_no']) ?CryptoHelper::encrypt($data['tax_no']) : null;
        }

        if ($this->isIndividualType($nextClientType)) {
            $updateData['company_name'] = null;
            $updateData['tax_office'] = null;
            $updateData['trade_registry_no'] = null;
            $updateData['mersis_no'] = null;
            $updateData['authorized_person'] = null;
            if (!array_key_exists('tax_no', $data)) {
                $updateData['tax_number_encrypted'] = null;
            }
        }

        if ($this->isCorporateType($nextClientType)) {
            $updateData['first_name'] = null;
            $updateData['last_name'] = null;
            $updateData['birth_date'] = null;
            $updateData['profession'] = null;
            if (!array_key_exists('tc_no', $data)) {
                $updateData['national_id_encrypted'] = null;
            }
        }

        if (!empty($updateData)) {
            $this->clientRepository->update($id, $updateData);
        }

        // İletişim bilgileri tamamen gönderilmişse, eskileri silip yenilerini ekle (Basit yaklaşım)
        // Alternatif olarak ID bazlı update yapılabilirdi ama "senkronize et" daha güvenli
        if (isset($data['contacts']) && is_array($data['contacts'])) {
            $this->clientRepository->deleteContactsByClientId($id);

            foreach ($data['contacts'] as $contact) {
                if (empty($contact['value']))
                    continue;

                $encryptedValue = CryptoHelper::encrypt($contact['value']);
                $this->clientRepository->addContact(
                    $id,
                    $contact['contact_type'],
                    $contact['sub_type'] ?? null,
                    $encryptedValue,
                    (bool)($contact['is_primary'] ?? false)
                );
            }
        }

        $this->syncFlatContactFields($id, $data);

        $this->userRepository->logActivity($userId, 'client_updated', 'ClientManagement', $ipAddress);
    }

    private function isIndividualType(?string $clientType): bool
    {
        return $clientType === 'Bireysel';
    }

    private function isCorporateType(?string $clientType): bool
    {
        return $clientType === 'Kurumsal';
    }

    private function mapContactsForResponse(array $contactsRaw, array &$client): array
    {
        $contacts = [];

        foreach ($contactsRaw as $contact) {
            $contact['value'] = CryptoHelper::decrypt($contact['contact_value_encrypted']);
            unset($contact['contact_value_encrypted']);
            $contacts[] = $contact;

            $fieldName = $this->resolveContactFieldName($contact['contact_type'], $contact['sub_type'] ?? null);
            if ($fieldName !== null && empty($client[$fieldName])) {
                $client[$fieldName] = $contact['value'];
            }
        }

        return $contacts;
    }

    private function extractFlatContactFields(int $clientId): array
    {
        $result = [
            'phone' => null,
            'email' => null,
            'city' => null,
            'district' => null,
            'address' => null,
        ];

        $contactsRaw = $this->clientRepository->getContactsByClientId($clientId);
        foreach ($contactsRaw as $contact) {
            $fieldName = $this->resolveContactFieldName($contact['contact_type'], $contact['sub_type'] ?? null);
            if ($fieldName === null || !empty($result[$fieldName])) {
                continue;
            }

            $result[$fieldName] = CryptoHelper::decrypt($contact['contact_value_encrypted']);
        }

        return $result;
    }

    private function resolveContactFieldName(string $contactType, ?string $subType): ?string
    {
        foreach (self::CONTACT_FIELD_MAP as $fieldName => $config) {
            if ($config['contact_type'] === $contactType && ($config['sub_type'] ?? null) === $subType) {
                return $fieldName;
            }
        }

        if ($contactType === 'Telefon') {
            return 'phone';
        }

        if ($contactType === 'E-posta') {
            return 'email';
        }

        if ($contactType === 'Adres') {
            if ($subType === 'Şehir') {
                return 'city';
            }

            if ($subType === 'İlçe') {
                return 'district';
            }

            return 'address';
        }

        return null;
    }

    private function syncFlatContactFields(int $clientId, array $data): void
    {
        $contactsRaw = $this->clientRepository->getContactsByClientId($clientId);
        $indexedContacts = [];

        foreach ($contactsRaw as $contact) {
            $key = $contact['contact_type'] . '|' . ($contact['sub_type'] ?? '');
            $indexedContacts[$key] = $contact;
        }

        foreach (self::CONTACT_FIELD_MAP as $fieldName => $config) {
            if (!array_key_exists($fieldName, $data)) {
                continue;
            }

            $value = trim((string)($data[$fieldName] ?? ''));
            $contactKey = $config['contact_type'] . '|' . ($config['sub_type'] ?? '');
            $existingContact = $indexedContacts[$contactKey] ?? null;

            if ($existingContact === null) {
                foreach ($contactsRaw as $contact) {
                    if (($contact['contact_type'] ?? null) !== $config['contact_type']) {
                        continue;
                    }

                    $existingSubType = $contact['sub_type'] ?? null;
                    if ($config['contact_type'] === 'Adres') {
                        if ($existingSubType === ($config['sub_type'] ?? null)) {
                            $existingContact = $contact;
                            break;
                        }

                        if (($config['sub_type'] ?? null) === 'Açık Adres' && empty($existingSubType)) {
                            $existingContact = $contact;
                            break;
                        }

                        continue;
                    }

                    $existingContact = $contact;
                    break;
                }
            }

            if ($value === '') {
                if ($existingContact) {
                    $this->clientRepository->deleteContactById((int)$existingContact['id']);
                }
                continue;
            }

            $encryptedValue = CryptoHelper::encrypt($value);
            if ($existingContact) {
                $this->clientRepository->updateContactValue(
                    (int)$existingContact['id'],
                    $encryptedValue,
                    (bool)$config['is_primary']
                );
                continue;
            }

            $this->clientRepository->addContact(
                $clientId,
                $config['contact_type'],
                $config['sub_type'] ?? null,
                $encryptedValue,
                (bool)$config['is_primary']
            );
        }
    }

    public function deleteClient(int $id, int $userId, string $ipAddress): void
    {
        $client = $this->clientRepository->findById($id);
        if (!$client) {
            throw new Exception("Müvekkil bulunamadı.", 404);
        }

        $this->clientRepository->softDelete($id);
        $this->userRepository->logActivity($userId, 'client_deleted', 'ClientManagement', $ipAddress);
    }
}