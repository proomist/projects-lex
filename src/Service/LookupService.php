<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\LookupRepository;
use Exception;

class LookupService
{
    private LookupRepository $lookupRepository;

    public function __construct(LookupRepository $lookupRepository)
    {
        $this->lookupRepository = $lookupRepository;
    }

    public function getByGroup(string $groupKey, bool $activeOnly = true): array
    {
        return $this->lookupRepository->getByGroup($groupKey, $activeOnly);
    }

    public function getAllGroups(): array
    {
        return $this->lookupRepository->getAllGroups();
    }

    public function create(array $data): array
    {
        $id = $this->lookupRepository->create($data);
        return $this->lookupRepository->getById($id);
    }

    public function update(int $id, array $data): array
    {
        $existing = $this->lookupRepository->getById($id);
        if (!$existing) {
            throw new Exception("Tanım bulunamadı.", 404);
        }

        if ((int) $existing['is_system'] === 1) {
            unset($data['value'], $data['group_key']);
        }

        $this->lookupRepository->update($id, $data);
        return $this->lookupRepository->getById($id);
    }

    public function delete(int $id): void
    {
        $existing = $this->lookupRepository->getById($id);
        if (!$existing) {
            throw new Exception("Tanım bulunamadı.", 404);
        }
        if ((int) $existing['is_system'] === 1) {
            throw new Exception("Sistem tanımları silinemez.", 403);
        }
        $this->lookupRepository->delete($id);
    }

    public function getValuesForValidation(string $groupKey): array
    {
        return $this->lookupRepository->getValuesForValidation($groupKey);
    }
}
