<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\HearingRepository;
use App\Repository\CaseRepository;
use App\Repository\UserRepository;
use Exception;

class HearingService
{
    private const STATUS_MAP_TO_STORAGE = [
        'Yapıldı' => 'Tamamlandı',
    ];

    private const STATUS_MAP_TO_RESPONSE = [
        'Tamamlandı' => 'Yapıldı',
    ];

    private HearingRepository $hearingRepository;
    private CaseRepository $caseRepository;
    private UserRepository $userRepository;

    public function __construct(HearingRepository $hearingRepository, CaseRepository $caseRepository, UserRepository $userRepository)
    {
        $this->hearingRepository = $hearingRepository;
        $this->caseRepository = $caseRepository;
        $this->userRepository = $userRepository;
    }

    public function createHearing(int $caseId, array $data, int $userId, string $ipAddress): int
    {
        $case = $this->caseRepository->findById($caseId);
        if (!$case) {
            throw new Exception("Bağlanmaya çalışılan dosya/dava bulunamadı.", 404);
        }

        $insertData = [
            'case_id' => $caseId,
            'hearing_date' => $data['hearing_date'],
            'hall_name' => $data['hall_name'] ?? null,
            'hearing_type' => $data['hearing_type'] ?? 'Diğer',
            'attending_lawyer_id' => !empty($data['attending_lawyer_id']) ? (int)$data['attending_lawyer_id'] : null,
            'status' => $this->normalizeStatusForStorage($data['status'] ?? 'Planlandı'),
            'summary_notes' => $data['summary_notes'] ?? ($data['description'] ?? null),
            'next_hearing_date' => $data['next_hearing_date'] ?? null
        ];

        $hearingId = $this->hearingRepository->create($insertData);
        $this->userRepository->logActivity($userId, 'hearing_created', 'CaseManagement', $ipAddress);

        return $hearingId;
    }

    public function getHearingsByCase(int $caseId): array
    {
        $case = $this->caseRepository->findById($caseId);
        if (!$case) {
            throw new Exception("Dosya/dava bulunamadı.", 404);
        }

        $hearings = $this->hearingRepository->findAllByCaseId($caseId);
        foreach ($hearings as &$hearing) {
            $this->mapHearingAliases($hearing);
        }
        unset($hearing);

        return $hearings;
    }

    public function getAllHearings(int $page = 1, int $limit = 15, ?string $status = null, ?string $date = null): array
    {
        $normalizedStatus = $status !== null ? $this->normalizeStatusForStorage($status) : null;
        $result = $this->hearingRepository->findAll($page, $limit, $normalizedStatus, $date);

        foreach ($result['data'] as &$hearing) {
            $this->mapHearingAliases($hearing);
        }
        unset($hearing);

        return $result;
    }

    public function getHearingById(int $hearingId): array
    {
        $hearing = $this->hearingRepository->findById($hearingId);
        if (!$hearing) {
            throw new Exception("Duruşma kaydı bulunamadı.", 404);
        }

        $this->mapHearingAliases($hearing);

        return $hearing;
    }

    public function updateHearing(int $hearingId, array $data, int $userId, string $ipAddress): void
    {
        $hearing = $this->hearingRepository->findById($hearingId);
        if (!$hearing) {
            throw new Exception("Duruşma kaydı bulunamadı.", 404);
        }

        $updateData = [];
        $fields = [
            'hearing_date', 'hall_name', 'hearing_type',
            'attending_lawyer_id', 'status', 'summary_notes', 'next_hearing_date'
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (array_key_exists('description', $data)) {
            $updateData['summary_notes'] = $data['description'];
        }

        if (array_key_exists('status', $updateData)) {
            $updateData['status'] = $this->normalizeStatusForStorage((string)$updateData['status']);
        }

        if (!empty($updateData)) {
            $this->hearingRepository->update($hearingId, $updateData);
            $this->userRepository->logActivity($userId, 'hearing_updated', 'CaseManagement', $ipAddress);
        }
    }

    public function deleteHearing(int $hearingId, int $userId, string $ipAddress): void
    {
        $hearing = $this->hearingRepository->findById($hearingId);
        if (!$hearing) {
            throw new Exception("Duruşma kaydı bulunamadı.", 404);
        }

        $this->hearingRepository->softDelete($hearingId);
        $this->userRepository->logActivity($userId, 'hearing_deleted', 'CaseManagement', $ipAddress);
    }

    private function normalizeStatusForStorage(string $status): string
    {
        return self::STATUS_MAP_TO_STORAGE[$status] ?? $status;
    }

    private function normalizeStatusForResponse(string $status): string
    {
        return self::STATUS_MAP_TO_RESPONSE[$status] ?? $status;
    }

    private function mapHearingAliases(array &$hearing): void
    {
        if (isset($hearing['status'])) {
            $hearing['status'] = $this->normalizeStatusForResponse((string)$hearing['status']);
        }

        $hearing['description'] = $hearing['summary_notes'] ?? null;
    }
}