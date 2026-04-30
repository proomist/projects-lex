<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\DashboardRepository;
use App\Repository\UserRepository;
use Exception;

class DashboardService
{
    private DashboardRepository $dashboardRepository;
    private UserRepository $userRepository;

    public function __construct(DashboardRepository $dashboardRepository, UserRepository $userRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
        $this->userRepository = $userRepository;
    }

    public function getSummary(int $userId, string $ipAddress): array
    {
        // Optimize: 8 ayrı sorgu yerine 3 birleşik sorgu (getSummaryAll)
        $stats = $this->dashboardRepository->getSummaryAll();

        $upcomingHearings = $this->dashboardRepository->getUpcomingHearings(5);
        $myTasks = $this->dashboardRepository->getMyTasks($userId, 5);

        $this->userRepository->logActivity($userId, 'dashboard_viewed', 'Dashboard', $ipAddress);

        return [
            'active_cases' => $stats['active_cases'],
            'this_month_hearings' => $stats['this_month_hearings'],
            'delayed_tasks' => $stats['delayed_tasks'],
            'today_tasks' => $stats['today_tasks'],
            'financial' => [
                'total_receivables' => $stats['total_receivables'],
                'this_month_collections' => $stats['this_month_collections'],
                'office_profit' => [
                    'revenue' => $stats['revenue'],
                    'office_expense' => $stats['office_expense'],
                    'profit' => $stats['profit'],
                ],
                'trust_balance' => $stats['trust_balance']
            ],
            'upcoming_hearings' => $upcomingHearings,
            'my_tasks' => $myTasks
        ];
    }

    public function getBadges(int $userId): array
    {
        return [
            'pending_tasks' => $this->dashboardRepository->getMyPendingTaskCount($userId),
            'unread_notifications' => $this->dashboardRepository->getUnreadReminderCount($userId)
        ];
    }

    public function getNotifications(int $userId): array
    {
        $reminders = $this->dashboardRepository->getMyReminders($userId, 10);
        $unreadCount = $this->dashboardRepository->getUnreadReminderCount($userId);

        return [
            'items' => $reminders,
            'unread_count' => $unreadCount
        ];
    }

    public function markNotificationRead(int $reminderId, int $userId): void
    {
        $this->dashboardRepository->markReminderAsRead($reminderId, $userId);
    }

    public function search(string $query): array
    {
        if (mb_strlen(trim($query)) < 2) {
            return [];
        }
        return $this->dashboardRepository->globalSearch(trim($query), 10);
    }
}
