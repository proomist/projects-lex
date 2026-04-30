<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ActivityLogRepository;

class ActivityLogService
{
    private ActivityLogRepository $activityLogRepository;

    public function __construct(ActivityLogRepository $activityLogRepository)
    {
        $this->activityLogRepository = $activityLogRepository;
    }

    public function getLogs(
        int $page = 1,
        int $limit = 20,
        ?string $actionType = null,
        ?string $module = null,
        ?string $actor = null,
        ?string $ipAddress = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $safePage = max(1, $page);
        $safeLimit = max(1, min(100, $limit));
        $offset = ($safePage - 1) * $safeLimit;

        $data = $this->activityLogRepository->findAllPaginated(
            $safeLimit,
            $offset,
            $actionType,
            $module,
            $actor,
            $ipAddress,
            $dateFrom,
            $dateTo
        );

        $total = $this->activityLogRepository->countAll(
            $actionType,
            $module,
            $actor,
            $ipAddress,
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
