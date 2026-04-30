<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

class DashboardRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    /**
     * Dashboard özet istatistikleri — 8 sorguyu 3'e düşüren optimize edilmiş versiyon.
     * MONTH()/YEAR() fonksiyonları yerine tarih aralığı kullanır (index dostu).
     *
     * @return array{
     *   active_cases: int,
     *   this_month_hearings: int,
     *   delayed_tasks: int,
     *   today_tasks: int,
     *   total_receivables: float,
     *   this_month_collections: float,
     *   revenue: float,
     *   office_expense: float,
     *   profit: float,
     *   trust_balance: float
     * }
     */
    public function getSummaryAll(): array
    {
        // Ay başlangıcı ve sonu (index-uyumlu tarih aralığı)
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $today = date('Y-m-d');

        // Sorgu 1: Dosya + Duruşma sayıları (2 subquery, tek round-trip)
        $sql1 = "SELECT
                    (SELECT COUNT(id) FROM cases WHERE is_deleted = 0 AND status IN ('Aktif', 'Karar Aşaması')) as active_cases,
                    (SELECT COUNT(id) FROM hearings WHERE is_deleted = 0 AND status = 'Planlandı'
                        AND hearing_date >= :month_start AND hearing_date <= :month_end) as this_month_hearings";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute(['month_start' => $monthStart, 'month_end' => $monthEnd]);
        $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

        // Sorgu 2: Görev sayıları (tek sorgu, 2 conditional count)
        $sql2 = "SELECT
                    SUM(CASE WHEN due_date < :today THEN 1 ELSE 0 END) as delayed_tasks,
                    SUM(CASE WHEN due_date = :today2 THEN 1 ELSE 0 END) as today_tasks
                FROM tasks
                WHERE is_deleted = 0 AND status NOT IN ('Tamamlandı', 'İptal')";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute(['today' => $today, 'today2' => $today]);
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

        // Sorgu 3: Tüm finansal metrikler (tek sorgu, tüm SUM/CASE birleşik)
        $sql3 = "SELECT
                    SUM(CASE WHEN transaction_type = 'Alacak' THEN total_amount ELSE 0 END) as total_receivables,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'ucret'
                        AND transaction_date >= :ms AND transaction_date <= :me THEN total_amount ELSE 0 END) as this_month_collections,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'ucret'
                        AND transaction_date >= :ms2 AND transaction_date <= :me2 THEN total_amount ELSE 0 END) as revenue,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'genel'
                        AND transaction_date >= :ms3 AND transaction_date <= :me3 THEN total_amount ELSE 0 END) as office_expense,
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'emanet' THEN total_amount ELSE 0 END) as trust_in,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'masraf' THEN total_amount ELSE 0 END) as trust_out
                FROM financial_transactions
                WHERE is_deleted = 0 AND (status IS NULL OR status != 'İptal')";
        $stmt3 = $this->db->prepare($sql3);
        $stmt3->execute([
            'ms' => $monthStart, 'me' => $monthEnd,
            'ms2' => $monthStart, 'me2' => $monthEnd,
            'ms3' => $monthStart, 'me3' => $monthEnd,
        ]);
        $row3 = $stmt3->fetch(PDO::FETCH_ASSOC);

        $revenue = (float)($row3['revenue'] ?? 0);
        $officeExpense = (float)($row3['office_expense'] ?? 0);
        $trustIn = (float)($row3['trust_in'] ?? 0);
        $trustOut = (float)($row3['trust_out'] ?? 0);

        return [
            'active_cases' => (int)($row1['active_cases'] ?? 0),
            'this_month_hearings' => (int)($row1['this_month_hearings'] ?? 0),
            'delayed_tasks' => (int)($row2['delayed_tasks'] ?? 0),
            'today_tasks' => (int)($row2['today_tasks'] ?? 0),
            'total_receivables' => (float)($row3['total_receivables'] ?? 0),
            'this_month_collections' => (float)($row3['this_month_collections'] ?? 0),
            'revenue' => $revenue,
            'office_expense' => $officeExpense,
            'profit' => $revenue - $officeExpense,
            'trust_balance' => $trustIn - $trustOut,
        ];
    }

    // --- Eski metotlar (backward compatibility, getSummaryAll tercih edin) ---

    public function getActiveCaseCount(): int
    {
        $sql = "SELECT COUNT(id) FROM cases WHERE is_deleted = 0 AND status IN ('Aktif', 'Karar Aşaması')";
        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }

    public function getThisMonthHearingCount(): int
    {
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $sql = "SELECT COUNT(id) FROM hearings
                WHERE is_deleted = 0 AND status = 'Planlandı'
                  AND hearing_date >= :ms AND hearing_date <= :me";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ms' => $monthStart, 'me' => $monthEnd]);
        return (int)$stmt->fetchColumn();
    }

    public function getDelayedTaskCount(): int
    {
        $sql = "SELECT COUNT(id) FROM tasks
                WHERE is_deleted = 0
                  AND status NOT IN ('Tamamlandı', 'İptal')
                  AND due_date < CURRENT_DATE()";
        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }

    public function getTodayTaskCount(): int
    {
        $sql = "SELECT COUNT(id) FROM tasks
                WHERE is_deleted = 0
                  AND status NOT IN ('Tamamlandı', 'İptal')
                  AND due_date = CURRENT_DATE()";
        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }

    public function getTotalReceivables(): float
    {
        $sql = "SELECT SUM(total_amount) FROM financial_transactions
                WHERE transaction_type = 'Alacak'
                  AND is_deleted = 0
                  AND (status IS NULL OR status != 'İptal')";
        $stmt = $this->db->query($sql);
        return (float)$stmt->fetchColumn();
    }

    public function getThisMonthCollections(): float
    {
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $sql = "SELECT SUM(total_amount) FROM financial_transactions
                WHERE transaction_type = 'Tahsilat' AND sub_type = 'ucret'
                  AND is_deleted = 0 AND (status IS NULL OR status != 'İptal')
                  AND transaction_date >= :ms AND transaction_date <= :me";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ms' => $monthStart, 'me' => $monthEnd]);
        return (float)$stmt->fetchColumn();
    }

    public function getOfficeProfitThisMonth(): array
    {
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $sql = "SELECT
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'ucret' THEN total_amount ELSE 0 END) as revenue,
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'genel' THEN total_amount ELSE 0 END) as office_expense
                FROM financial_transactions
                WHERE is_deleted = 0 AND (status IS NULL OR status != 'İptal')
                  AND transaction_date >= :ms AND transaction_date <= :me";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ms' => $monthStart, 'me' => $monthEnd]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $revenue = (float)($row['revenue'] ?? 0);
        $officeExpense = (float)($row['office_expense'] ?? 0);
        return ['revenue' => $revenue, 'office_expense' => $officeExpense, 'profit' => $revenue - $officeExpense];
    }

    public function getTotalTrustBalance(): float
    {
        $sql = "SELECT
                    SUM(CASE WHEN transaction_type = 'Tahsilat' AND sub_type = 'emanet' THEN total_amount ELSE 0 END) -
                    SUM(CASE WHEN transaction_type = 'Gider' AND sub_type = 'masraf' THEN total_amount ELSE 0 END)
                FROM financial_transactions
                WHERE is_deleted = 0 AND (status IS NULL OR status != 'İptal')";
        $stmt = $this->db->query($sql);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Yaklaşan duruşmaları getirir (bugünden itibaren, tarih sırasıyla).
     */
    public function getUpcomingHearings(int $limit = 5): array
    {
        $sql = "SELECT h.id, h.hearing_date, h.hall_name, h.hearing_type, h.status,
                       c.case_no, c.court_name
                FROM hearings h
                LEFT JOIN cases c ON h.case_id = c.id
                WHERE h.is_deleted = 0
                  AND h.status = 'Planlandı'
                  AND h.hearing_date >= CURRENT_DATE()
                ORDER BY h.hearing_date ASC
                LIMIT :lmt";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('lmt', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kullanıcıya atanan aktif görevleri getirir.
     */
    public function getMyTasks(int $userId, int $limit = 5): array
    {
        $sql = "SELECT t.id, t.title, t.priority, t.status, t.due_date,
                       c.case_no, cl.first_name as client_first_name, cl.last_name as client_last_name
                FROM tasks t
                LEFT JOIN cases c ON t.case_id = c.id
                LEFT JOIN clients cl ON t.client_id = cl.id
                WHERE t.is_deleted = 0
                  AND t.assigned_to = :uid
                  AND t.status NOT IN ('Tamamlandı', 'İptal')
                ORDER BY FIELD(t.priority, 'Acil', 'Yüksek', 'Normal', 'Düşük'), t.due_date ASC
                LIMIT :lmt";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue('lmt', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kullanıcının bekleyen görev sayısını döner (navbar badge için).
     */
    public function getMyPendingTaskCount(int $userId): int
    {
        $sql = "SELECT COUNT(id) FROM tasks
                WHERE is_deleted = 0
                  AND assigned_to = :uid
                  AND status NOT IN ('Tamamlandı', 'İptal')";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Kullanıcının hatırlatıcılarını getirir.
     */
    public function getMyReminders(int $userId, int $limit = 10): array
    {
        $sql = "SELECT r.id, r.message, r.remind_at, r.is_read,
                       t.title as task_title,
                       c.case_no,
                       h.hearing_date
                FROM reminders r
                LEFT JOIN tasks t ON r.task_id = t.id
                LEFT JOIN cases c ON r.case_id = c.id
                LEFT JOIN hearings h ON r.hearing_id = h.id
                WHERE r.user_id = :uid
                  AND r.is_sent = 1
                ORDER BY r.is_read ASC, r.remind_at DESC
                LIMIT :lmt";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue('lmt', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kullanıcının okunmamış hatırlatıcı sayısını döner.
     */
    public function getUnreadReminderCount(int $userId): int
    {
        $sql = "SELECT COUNT(id) FROM reminders
                WHERE user_id = :uid AND is_read = 0 AND is_sent = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Global arama: müvekkiller, dosyalar, görevler.
     * 3+ karakter: FULLTEXT index kullanır (performans).
     * Kısa sorgular: LIKE fallback.
     */
    public function globalSearch(string $query, int $limit = 10): array
    {
        $results = [];
        $useFulltext = mb_strlen($query) >= 3;

        // Müvekkiller
        if ($useFulltext) {
            $ftQuery = $this->buildFulltextQuery($query);
            $sql = "SELECT id, client_code, client_type, first_name, last_name, company_name
                    FROM clients WHERE is_deleted = 0
                    AND MATCH(first_name, last_name, company_name, client_code) AGAINST(:q IN BOOLEAN MODE)
                    LIMIT :lmt";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('q', $ftQuery);
        } else {
            $like = '%' . $query . '%';
            $sql = "SELECT id, client_code, client_type, first_name, last_name, company_name
                    FROM clients WHERE is_deleted = 0 AND (
                        first_name LIKE :q OR last_name LIKE :q2 OR company_name LIKE :q3 OR client_code LIKE :q4
                    ) LIMIT :lmt";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('q', $like);
            $stmt->bindValue('q2', $like);
            $stmt->bindValue('q3', $like);
            $stmt->bindValue('q4', $like);
        }
        $stmt->bindValue('lmt', $limit, PDO::PARAM_INT);
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $name = $c['client_type'] === 'Kurumsal'
                ? ($c['company_name'] ?? '')
                : (($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
            $results[] = ['type' => 'client', 'id' => $c['id'], 'title' => trim($name), 'subtitle' => $c['client_code'], 'url' => '/clients'];
        }

        // Dosyalar / Davalar
        if ($useFulltext) {
            $ftQuery = $this->buildFulltextQuery($query);
            $sql = "SELECT id, case_no, court_name, subject_summary, status
                    FROM cases WHERE is_deleted = 0
                    AND MATCH(case_no, court_name, subject_summary) AGAINST(:q IN BOOLEAN MODE)
                    LIMIT :lmt";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('q', $ftQuery);
        } else {
            $like = '%' . $query . '%';
            $sql = "SELECT id, case_no, court_name, subject_summary, status
                    FROM cases WHERE is_deleted = 0 AND (
                        case_no LIKE :q OR court_name LIKE :q2 OR subject_summary LIKE :q3
                    ) LIMIT :lmt";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('q', $like);
            $stmt->bindValue('q2', $like);
            $stmt->bindValue('q3', $like);
        }
        $stmt->bindValue('lmt', $limit, PDO::PARAM_INT);
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $cs) {
            $results[] = ['type' => 'case', 'id' => $cs['id'], 'title' => $cs['case_no'], 'subtitle' => $cs['court_name'] ?? $cs['status'], 'url' => '/cases'];
        }

        // Görevler
        if ($useFulltext) {
            $ftQuery = $this->buildFulltextQuery($query);
            $sql = "SELECT id, title, priority, status
                    FROM tasks WHERE is_deleted = 0
                    AND MATCH(title) AGAINST(:q IN BOOLEAN MODE)
                    LIMIT :lmt";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('q', $ftQuery);
        } else {
            $like = '%' . $query . '%';
            $sql = "SELECT id, title, priority, status
                    FROM tasks WHERE is_deleted = 0 AND title LIKE :q LIMIT :lmt";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('q', $like);
        }
        $stmt->bindValue('lmt', $limit, PDO::PARAM_INT);
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
            $results[] = ['type' => 'task', 'id' => $t['id'], 'title' => $t['title'], 'subtitle' => $t['priority'] . ' · ' . $t['status'], 'url' => '/tasks'];
        }

        return $results;
    }

    /**
     * FULLTEXT BOOLEAN MODE için sorgu oluşturur.
     * "mehmet ali" → "+mehmet* +ali*"
     */
    private function buildFulltextQuery(string $query): string
    {
        $words = preg_split('/\s+/', trim($query));
        $parts = [];
        foreach ($words as $word) {
            if (mb_strlen($word) >= 2) {
                $parts[] = '+' . $word . '*';
            }
        }
        return implode(' ', $parts) ?: $query . '*';
    }

    /**
     * Hatırlatıcıyı okundu olarak işaretle.
     */
    public function markReminderAsRead(int $reminderId, int $userId): void
    {
        $sql = "UPDATE reminders SET is_read = 1 WHERE id = :id AND user_id = :uid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $reminderId, 'uid' => $userId]);
    }
}
