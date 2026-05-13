<?php


require_once __DIR__ . '/Database.php';

class SolarTrackerModel
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
            'INSERT INTO solar_tracker
               (user_id, generated_kwh, exported_kwh, panel_count, panel_watt_peak, efficiency_pct, recorded_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            'iddidds',
            $data['user_id'],
            $data['generated_kwh']   ?? 0,
            $data['exported_kwh']    ?? 0,
            $data['panel_count']     ?? 1,
            $data['panel_watt_peak'] ?? 400,
            $data['efficiency_pct']  ?? 100,
            $data['recorded_at']     ?? date('Y-m-d H:i:s')
        );
    }

    // ── READ ──────────────────────────────────────────────────────────────

    /** Today's total generation */
    public function todayGenerated(int $userId): float
    {
        $rows = $this->db->query(
            'SELECT COALESCE(SUM(generated_kwh), 0) AS total
             FROM solar_tracker
             WHERE user_id = ? AND DATE(recorded_at) = CURDATE()',
            'i', $userId
        );
        return (float) ($rows[0]['total'] ?? 0);
    }

    /** Monthly summary for the Reports page */
    public function monthlySummary(int $userId, int $year, int $month): array
    {
        return $this->db->query(
            'SELECT
               DATE(recorded_at)    AS day,
               SUM(generated_kwh)  AS generated,
               SUM(exported_kwh)   AS exported
             FROM solar_tracker
             WHERE user_id = ? AND YEAR(recorded_at) = ? AND MONTH(recorded_at) = ?
             GROUP BY day
             ORDER BY day ASC',
            'iii', $userId, $year, $month
        );
    }

    /** Time-series for Dashboard chart — last N days */
    public function history(int $userId, int $days = 7): array
    {
        return $this->db->query(
            'SELECT
               DATE(recorded_at)    AS day,
               SUM(generated_kwh)  AS generated,
               SUM(exported_kwh)   AS exported,
               AVG(efficiency_pct) AS avg_efficiency
             FROM solar_tracker
             WHERE user_id = ? AND recorded_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY day
             ORDER BY day ASC',
            'ii', $userId, $days
        );
    }

    /** Latest reading row */
    public function latest(int $userId): ?array
    {
        $rows = $this->db->query(
            'SELECT * FROM solar_tracker
             WHERE user_id = ?
             ORDER BY recorded_at DESC
             LIMIT 1',
            'i', $userId
        );
        return $rows[0] ?? null;
    }

    /** Lifetime totals */
    public function lifetime(int $userId): array
    {
        $rows = $this->db->query(
            'SELECT
               COALESCE(SUM(generated_kwh), 0)  AS total_generated,
               COALESCE(SUM(exported_kwh),  0)  AS total_exported,
               COUNT(*)                          AS readings
             FROM solar_tracker WHERE user_id = ?',
            'i', $userId
        );
        return $rows[0] ?? ['total_generated' => 0, 'total_exported' => 0, 'readings' => 0];
    }
}
