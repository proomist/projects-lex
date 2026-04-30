<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CaseRepository;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use App\Helper\CryptoHelper;
use Exception;

class CaseService
{
    private const STATUS_MAP_TO_STORAGE = [
        'Sonuçlandı' => 'Kapandı',
        'İstinaf/Yargıtay' => 'Beklemede',
    ];

    private const STATUS_MAP_TO_RESPONSE = [
        'Kapandı' => 'Sonuçlandı',
    ];

    private CaseRepository $caseRepository;
    private UserRepository $userRepository;
    private ClientRepository $clientRepository;

    public function __construct(CaseRepository $caseRepository, UserRepository $userRepository, ClientRepository $clientRepository)
    {
        $this->caseRepository = $caseRepository;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
    }

    public function createCase(array $data, int $userId, string $ipAddress): int
    {
        $clientPositionInput = $data['client_position'] ?? ($data['client_role'] ?? null);
        if (empty($clientPositionInput)) {
            throw new Exception('Müvekkil konumu zorunludur.', 400);
        }

        $normalizedCaseType = $this->normalizeCaseTypeForStorage((string)$data['case_type']);
        $normalizedClientPosition = $this->normalizeClientPositionForStorage((string)$clientPositionInput);

        // İlgili müvekkilin varlığını kontrol et
        $client = $this->clientRepository->findById((int)$data['client_id']);
        if (!$client) {
            throw new Exception("Bağlanmaya çalışılan müvekkil bulunamadı.", 404);
        }

        // İlgili avukatın varlığını kontrol et
        $lawyer = $this->userRepository->findById((int)$data['lawyer_id']);
        if (!$lawyer) {
            throw new Exception("Sorumlu avukat bulunamadı.", 404);
        }

        $caseNo = $this->caseRepository->generateCaseNo();
        $openDate = $data['open_date'] ?? date('Y-m-d');
        $resolvedCaseNo = !empty($data['case_no']) ? trim((string)$data['case_no']) : $caseNo;

        $insertData = [
            'case_no' => $resolvedCaseNo,
            'folder_no' => $data['folder_no'] ?? null,
            'case_type' => $normalizedCaseType,
            'case_category' => $data['case_category'] ?? $this->resolveCaseCategoryFromInput((string)$data['case_type']),
            'open_date' => $openDate,
            'client_id' => (int)$data['client_id'],
            'client_position' => $normalizedClientPosition,
            'lawyer_id' => (int)$data['lawyer_id'],
            'court_name' => $data['court_name'] ?? null,
            'court_city' => $data['court_city'] ?? null,
            'court_district' => $data['court_district'] ?? null,
            'merits_no' => $data['merits_no'] ?? ($data['base_no'] ?? null),
            'decision_no' => $data['decision_no'] ?? null,
            'subject_summary' => $data['subject_summary'] ?? ($data['subject'] ?? null),
            'claimed_amount' => !empty($data['claimed_amount']) ? (float)$data['claimed_amount'] : null,
            'currency' => $data['currency'] ?? 'TRY',
            'status' => $this->normalizeCaseStatusForStorage($data['status'] ?? 'Taslak')
        ];

        $caseId = $this->caseRepository->create($insertData);

        $this->syncCaseParties($caseId, $data, false);

        $this->userRepository->logActivity($userId, 'case_created', 'CaseManagement', $ipAddress);

        return $caseId;
    }

    public function getCases(
        int $page = 1,
        int $limit = 20,
        ?string $search = null,
        ?int $clientId = null,
        ?int $lawyerId = null,
        ?string $status = null,
        ?string $type = null
    ): array
    {
        $offset = ($page - 1) * $limit;
        $normalizedStatus = $status !== null ? $this->normalizeCaseStatusForStorage($status) : null;
        $normalizedType = $type;
        
        $cases = $this->caseRepository->findAll($limit, $offset, $search, $clientId, $lawyerId, $normalizedStatus, $normalizedType);
        $total = $this->caseRepository->countAll($search, $clientId, $lawyerId, $normalizedStatus, $normalizedType);

        foreach ($cases as &$caseItem) {
            $this->mapCaseAliases($caseItem, true);
        }
        unset($caseItem);

        return [
            'data' => $cases,
            'meta' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    public function getCaseById(int $id): array
    {
        $case = $this->caseRepository->findById($id);
        
        if (!$case) {
            throw new Exception("Dosya/Dava bulunamadı.", 404);
        }

        // Karşı tarafları getir ve çöz
        $partiesRaw = $this->caseRepository->getPartiesByCaseId($id);
        $parties = [];
        
        foreach ($partiesRaw as $p) {
            $p['full_name'] = CryptoHelper::decrypt($p['full_name_encrypted']);
            
            $p['tc_tax_no'] = !empty($p['tc_tax_no_encrypted']) ? CryptoHelper::decrypt($p['tc_tax_no_encrypted']) : null;
            $p['contact_info'] = !empty($p['contact_info_encrypted']) ? CryptoHelper::decrypt($p['contact_info_encrypted']) : null;
            
            unset($p['full_name_encrypted']);
            unset($p['tc_tax_no_encrypted']);
            unset($p['contact_info_encrypted']);
            
            $parties[] = $p;
        }

        $this->mapCaseAliases($case, false);
        $case['parties'] = $parties;
        $firstParty = $parties[0] ?? null;
        $case['opponent_name'] = $firstParty['full_name'] ?? null;
        $case['opponent_lawyer'] = $firstParty['lawyer_name'] ?? null;

        return $case;
    }

    public function updateCase(int $id, array $data, int $userId, string $ipAddress): void
    {
        $case = $this->caseRepository->findById($id);
        if (!$case) {
            throw new Exception("Dosya/Dava bulunamadı.", 404);
        }

        $updateData = [];

        if (array_key_exists('case_type', $data)) {
            $updateData['case_type'] = $this->normalizeCaseTypeForStorage((string)$data['case_type']);
            if (!array_key_exists('case_category', $data)) {
                $updateData['case_category'] = $this->resolveCaseCategoryFromInput((string)$data['case_type']);
            }
        }

        if (array_key_exists('status', $data)) {
            $updateData['status'] = $this->normalizeCaseStatusForStorage((string)$data['status']);
        }

        if (array_key_exists('client_position', $data) || array_key_exists('client_role', $data)) {
            $position = $data['client_position'] ?? $data['client_role'];
            $updateData['client_position'] = $this->normalizeClientPositionForStorage((string)$position);
        }

        if (array_key_exists('merits_no', $data) || array_key_exists('base_no', $data)) {
            $updateData['merits_no'] = $data['merits_no'] ?? $data['base_no'];
        }

        if (array_key_exists('case_no', $data)) {
            $updateData['case_no'] = $data['case_no'];
        }

        if (array_key_exists('folder_no', $data)) {
            $updateData['folder_no'] = $data['folder_no'];
        }

        if (array_key_exists('subject_summary', $data) || array_key_exists('subject', $data)) {
            $updateData['subject_summary'] = $data['subject_summary'] ?? $data['subject'];
        }

        $fields = [
            'case_category', 'open_date', 'client_id',
            'lawyer_id', 'court_name', 'court_city', 'court_district',
            'decision_no', 'claimed_amount', 'currency', 'closing_type'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (isset($updateData['client_id'])) {
            $client = $this->clientRepository->findById((int)$updateData['client_id']);
            if (!$client) {
                throw new Exception('Bağlanmaya çalışılan müvekkil bulunamadı.', 404);
            }
            $updateData['client_id'] = (int)$updateData['client_id'];
        }

        if (isset($updateData['lawyer_id'])) {
            $lawyer = $this->userRepository->findById((int)$updateData['lawyer_id']);
            if (!$lawyer) {
                throw new Exception('Sorumlu avukat bulunamadı.', 404);
            }
            $updateData['lawyer_id'] = (int)$updateData['lawyer_id'];
        }

        if (!empty($updateData)) {
            $this->caseRepository->update($id, $updateData);
        }

        $this->syncCaseParties($id, $data, true);

        $this->userRepository->logActivity($userId, 'case_updated', 'CaseManagement', $ipAddress);
    }

    private function syncCaseParties(int $caseId, array $data, bool $onlyWhenPayloadExists): void
    {
        $hasPartiesPayload = isset($data['parties']) && is_array($data['parties']);
        $hasSimpleOpponentPayload = array_key_exists('opponent_name', $data) || array_key_exists('opponent_lawyer', $data);

        if ($onlyWhenPayloadExists && !$hasPartiesPayload && !$hasSimpleOpponentPayload) {
            return;
        }

        $partiesPayload = [];
        if ($hasPartiesPayload) {
            $partiesPayload = $data['parties'];
        } elseif ($hasSimpleOpponentPayload) {
            $partiesPayload[] = [
                'full_name' => trim((string)($data['opponent_name'] ?? '')),
                'lawyer_name' => trim((string)($data['opponent_lawyer'] ?? '')),
                'position' => 'Davalı',
            ];
        }

        $this->caseRepository->deletePartiesByCaseId($caseId);

        foreach ($partiesPayload as $party) {
            if (empty($party['full_name'])) {
                continue;
            }

            $encryptedName = CryptoHelper::encrypt($party['full_name']);
            $encryptedTcTax = !empty($party['tc_tax_no']) ? CryptoHelper::encrypt($party['tc_tax_no']) : null;
            $encryptedContact = !empty($party['contact_info']) ? CryptoHelper::encrypt($party['contact_info']) : null;

            $this->caseRepository->addParty(
                $caseId,
                $encryptedName,
                $encryptedTcTax,
                $party['position'] ?? 'Davalı',
                $party['lawyer_name'] ?? null,
                $encryptedContact
            );
        }
    }

    private function normalizeCaseTypeForStorage(string $caseType): string
    {
        if (in_array($caseType, ['Hukuk', 'Ceza', 'İdari', 'Diğer'], true)) {
            return 'Dava';
        }

        return $caseType;
    }

    private function resolveCaseCategoryFromInput(string $caseType): ?string
    {
        if (in_array($caseType, ['Hukuk', 'Ceza', 'İdari', 'Diğer'], true)) {
            return $caseType;
        }

        return null;
    }

    private function normalizeClientPositionForStorage(string $position): string
    {
        return match ($position) {
            'Şüpheli/Sanık' => 'Şüpheli',
            'Müşteki/Katılan' => 'Katılan',
            'Diğer' => 'Danışan',
            default => $position,
        };
    }

    private function normalizeClientPositionForResponse(?string $position): ?string
    {
        return match ($position) {
            'Şüpheli' => 'Şüpheli/Sanık',
            'Katılan' => 'Müşteki/Katılan',
            'Danışan' => 'Diğer',
            default => $position,
        };
    }

    private function normalizeCaseStatusForStorage(string $status): string
    {
        return self::STATUS_MAP_TO_STORAGE[$status] ?? $status;
    }

    private function normalizeCaseStatusForResponse(string $status): string
    {
        return self::STATUS_MAP_TO_RESPONSE[$status] ?? $status;
    }

    private function mapCaseAliases(array &$case, bool $includeOpponent): void
    {
        if (isset($case['status'])) {
            $case['status'] = $this->normalizeCaseStatusForResponse((string)$case['status']);
        }

        if (!empty($case['case_type']) && $case['case_type'] === 'Dava' && !empty($case['case_category'])) {
            $case['case_type'] = $case['case_category'];
        }

        $case['base_no'] = $case['merits_no'] ?? null;
        $case['subject'] = $case['subject_summary'] ?? null;
        $case['client_position'] = $this->normalizeClientPositionForResponse($case['client_position'] ?? null);
        $case['client_role'] = $case['client_position'];

        if (!$includeOpponent || !isset($case['id'])) {
            return;
        }

        $partiesRaw = $this->caseRepository->getPartiesByCaseId((int)$case['id']);
        if (empty($partiesRaw)) {
            return;
        }

        $firstParty = $partiesRaw[0];
        $case['opponent_name'] = !empty($firstParty['full_name_encrypted'])
            ? CryptoHelper::decrypt($firstParty['full_name_encrypted'])
            : null;
        $case['opponent_lawyer'] = $firstParty['lawyer_name'] ?? null;
    }

    public function deleteCase(int $id, int $userId, string $ipAddress): void
    {
        $case = $this->caseRepository->findById($id);
        if (!$case) {
            throw new Exception("Dosya/Dava bulunamadı.", 404);
        }

        $this->caseRepository->softDelete($id);
        $this->userRepository->logActivity($userId, 'case_deleted', 'CaseManagement', $ipAddress);
    }
}
