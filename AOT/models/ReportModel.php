<?php


require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/ResourceUsageModel.php';
require_once __DIR__ . '/ActionLogModel.php';
require_once __DIR__ . '/SolarTrackerModel.php';
require_once __DIR__ . '/TariffModel.php';
require_once __DIR__ . '/DeviceModel.php';

class ReportModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── GENERATE ─────────────────────────────────────────────────────────

    /**
     * Build a full report payload for a given period.
     * Saves it to the reports table and returns the assembled data.
     */
    public function generate(int $userId, string $from, string $to, string $type = 'custom'): array
    {
        $usage   = (new ResourceUsageModel());
        $solar   = (new SolarTrackerModel());
        $tariff  = (new TariffModel());
        $log     = (new ActionLogModel());
        $devices = (new DeviceModel());

        // Parse year/month from $from for monthly helpers
        [$year, $month] = array_map('intval', explode('-', $from));

        $usageRows    = $usage->findByRange($userId, $from . ' 00:00:00', $to . ' 23:59:59');
        $solarRows    = $solar->monthlySummary($userId, $year, $month);
        $activityRows = $log->findByRange($userId, $from . ' 00:00:00', $to . ' 23:59:59');
        $deviceList   = $devices->findByUser($userId);

        // Aggregate usage by resource type
        $totals = [];
        foreach ($usageRows as $row) {
            $rt = $row['resource_type'];
            $totals[$rt] = ($totals[$rt] ?? 0) + $row['value'];
        }

        // Calculate cost per resource type
        $costs = [];
        foreach ($totals as $rt => $amount) {
            $costs[$rt] = $tariff->calculateCost($userId, $rt, $amount);
        }

        $data = [
            'period'        => ['from' => $from, 'to' => $to],
            'totals'        => $totals,
            'costs'         => $costs,
            'total_cost'    => array_sum($costs),
            'solar'         => $solarRows,
            'activity_count'=> count($activityRows),
            'devices'       => count($deviceList),
            'generated_at'  => date('Y-m-d H:i:s'),
        ];

        // Persist
        $id = $this->db->execute(
            'INSERT INTO reports (user_id, title, type, period_from, period_to, data_json)
             VALUES (?, ?, ?, ?, ?, ?)',
            'isssss',
            $userId,
            'Report ' . $from . ' – ' . $to,
            $type,
            $from,
            $to,
            json_encode($data)
        );

        return ['id' => $id, 'data' => $data];
    }

    // ── READ ──────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $rows = $this->db->query(
            'SELECT * FROM reports WHERE id = ?',
            'i', $id
        );
        if (!$rows) return null;

        $report = $rows[0];
        $report['data'] = json_decode($report['data_json'] ?? '{}', true);
        return $report;
    }

    public function findByUser(int $userId, int $limit = 20): array
    {
        $rows = $this->db->query(
            'SELECT id, title, type, period_from, period_to, created_at
             FROM reports
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT ?',
            'ii', $userId, $limit
        );
        return $rows;
    }

    // ── DELETE ────────────────────────────────────────────────────────────

    public function delete(int $id): bool
    {
        $this->db->execute('DELETE FROM reports WHERE id = ?', 'i', $id);
        return true;
    }
}
