<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ReportRepository;
use Exception;

class ReportService
{
    private ReportRepository $reportRepository;

    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    public function getSummaryReport(?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            $caseStats = $this->reportRepository->getCaseStatistics();
            $financialStats = $this->reportRepository->getFinancialStatistics($dateFrom, $dateTo);
            $taskStats = $this->reportRepository->getTaskStatistics();
            $categoryStats = $this->reportRepository->getFinancialByCategory($dateFrom, $dateTo);

            return [
                'cases' => $caseStats,
                'financials' => $financialStats,
                'tasks' => $taskStats,
                'financial_categories' => $categoryStats
            ];
        } catch (Exception $e) {
            throw new Exception("Rapor verileri alınırken hata oluştu: " . $e->getMessage(), 500);
        }
    }
}
