<?php


require_once __DIR__ . '/Database.php';

class ResourceUsageModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── CREATE ────────────────────────────────────────────────────────────

    public function record(array $data): int
    {
        return $this->db->execute(
            'INSERT INTO resource_usage
               (user_id, device_id, resource_type, value, unit, recorded_at)
             VALUES (?, ?, ?, ?, ?, ?)',
            'iisdss',
            $data['user_id'],
            $data['device_id']     ?? null,
            $data['resource_type'],
            $data['value'],
            $data['unit']          ?? 'kWh',
            $data['recorded_at']   ?? date('Y-m-d H:i:s')
        );
    }

    // ── READ ──────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $rows = $this->db->query(
            'SELECT * FROM resource_usage WHERE id = ?',
            'i', $id
        );
        return $rows[0] ?? null;
    }

    /** Usage for a user filtered by date range */
    public function findByRange(int $userId, string $from, string $to, ?string $type = null): array
    {
        if ($type) {
            return $this->db->query(
                'SELECT * FROM resource_usage
                 WHERE user_id = ? AND resource_type = ?
                   AND recorded_at BETWEEN ? AND ?
                 ORDER BY recorded_at ASC',
                'isss', $userId, $type, $from, $to
            );
        }

        return $this->db->query(
            'SELECT * FROM resource_usage
             WHERE user_id = ? AND recorded_at BETWEEN ? AND ?
             ORDER BY recorded_at ASC',
            'iss', $userId, $from, $to
        );
    }

    /** Daily totals grouped by resource type for dashboard KPIs */
    public function dailySummary(int $userId, string $date): array
    {
        return $this->db->query(
            'SELECT resource_type, SUM(value) AS total, unit
             FROM resource_usage
             WHERE user_id = ? AND DATE(recorded_at) = ?
             GROUP BY resource_type, unit',
            'is', $userId, $date
        );
    }

    /** Monthly totals — used by Reports page */
    public function monthlySummary(int $userId, int $year, int $month): array
    {
        return $this->db->query(
            'SELECT resource_type,
                    SUM(value)      AS total,
                    unit,
                    MIN(recorded_at) AS period_start,
                    MAX(recorded_at) AS period_end
             FROM resource_usage
             WHERE user_id = ? AND YEAR(recorded_at) = ? AND MONTH(recorded_at) = ?
             GROUP BY resource_type, unit',
            'iii', $userId, $year, $month
        );
    }

    /** Last N readings for a specific device */
    public function deviceHistory(int $deviceId, int $limit = 30): array
    {
        return $this->db->query(
            'SELECT * FROM resource_usage
             WHERE device_id = ?
             ORDER BY recorded_at DESC
             LIMIT ?',
            'ii', $deviceId, $limit
        );
    }

    /** Compare this period vs previous (for % change KPIs) */
    public function compareDaily(int $userId, string $today, string $yesterday): array
    {
        $todayData     = $this->dailySummary($userId, $today);
        $yesterdayData = $this->dailySummary($userId, $yesterday);

        $indexed = [];
        foreach ($yesterdayData as $row) {
            $indexed[$row['resource_type']] = $row['total'];
        }

        foreach ($todayData as &$row) {
            $prev = $indexed[$row['resource_type']] ?? 0;
            $row['change_pct'] = $prev > 0
                ? round((($row['total'] - $prev) / $prev) * 100, 1)
                : null;
        }

        return $todayData;
    }

    // ── DELETE ────────────────────────────────────────────────────────────

    /** Purge records older than N days (data retention) */
    public function purgeOlderThan(int $days): int
    {
        return $this->db->execute(
            'DELETE FROM resource_usage WHERE recorded_at < DATE_SUB(NOW(), INTERVAL ? DAY)',
            'i', $days
        );
    }
}
