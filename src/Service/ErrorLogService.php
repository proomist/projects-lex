<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ErrorLogRepository;

class ErrorLogService
{
    private ErrorLogRepository $errorLogRepository;

    public function __construct(ErrorLogRepository $errorLogRepository)
    {
        $this->errorLogRepository = $errorLogRepository;
    }

    public function logError(array $data): bool
    {
        return $this->errorLogRepository->insert($data);
    }

    public function getLogs(
        int $page = 1,
        int $limit = 20,
        ?string $errorLevel = null,
        ?string $search = null,
        ?string $userName = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $safePage = max(1, $page);
        $safeLimit = max(1, min(100, $limit));
        $offset = ($safePage - 1) * $safeLimit;

        $data = $this->errorLogRepository->findAllPaginated(
            $safeLimit,
            $offset,
            $errorLevel,
            $search,
            $userName,
            $dateFrom,
            $dateTo
        );

        $total = $this->errorLogRepository->countAll(
            $errorLevel,
            $search,
            $userName,
            $dateFrom,
            $dateTo
        );

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $safePage,
                'per_page' => $safeLimit,
                'total_records' => $total,
                'total_pages' => (int)ceil($total / $safeLimit),
            ],
        ];
    }
}
