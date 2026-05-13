<?php
/**
 * api/dashboard.php
 * AOT Homes | CS251 Software Engineering
 *
 * Returns all data needed by the Dashboard page.
 *
 * GET  api/dashboard.php?action=summary   → KPI cards
 * GET  api/dashboard.php?action=chart     → Resource chart (7D / 14D / 30D)
 * GET  api/dashboard.php?action=alerts    → Unread alerts
 * GET  api/dashboard.php?action=all       → Everything in one call (default)
 */

require_once __DIR__ . '/../api/session.php';

header('Content-Type: application/json');
checkAuth();

$user   = getCurrentUser();
$uid    = (int) $user['id'];
$db     = getDB();
$action = $_GET['action'] ?? 'all';

/* ════════════════════════════════════════════════════════════════
   HELPERS
   ════════════════════════════════════════════════════════════════ */

/**
 * Sum a resource for a user over a date range.
 * Returns float.
 */
function sumResource(mysqli $db, int $uid, string $type, string $from, string $to): float
{
    $stmt = $db->prepare(
        'SELECT COALESCE(SUM(value), 0) AS total
           FROM resource_usage
          WHERE user_id = ?
            AND resource_type = ?
            AND recorded_at BETWEEN ? AND ?'
    );
    $stmt->bind_param('isss', $uid, $type, $from, $to);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (float) ($row['total'] ?? 0);
}

/**
 * Get the user's tariff rate for a resource.
 */
function getTariff(mysqli $db, int $uid, string $type): float
{
    $stmt = $db->prepare(
        'SELECT rate FROM tariffs
          WHERE user_id = ? AND resource_type = ?
          ORDER BY effective_from DESC LIMIT 1'
    );
    $stmt->bind_param('is', $uid, $type);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (float) ($row['rate'] ?? 0);
}

/**
 * Get daily totals for a resource over the last N days.
 * Returns array: [ ['date' => 'YYYY-MM-DD', 'total' => float], ... ]
 */
function dailyTotals(mysqli $db, int $uid, string $type, int $days): array
{
    $stmt = $db->prepare(
        'SELECT DATE(recorded_at) AS day, COALESCE(SUM(value), 0) AS total
           FROM resource_usage
          WHERE user_id = ?
            AND resource_type = ?
            AND recorded_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
          GROUP BY DATE(recorded_at)
          ORDER BY day ASC'
    );
    $stmt->bind_param('isi', $uid, $type, $days);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Fill gaps: build a map then iterate over all days
    $map = [];
    foreach ($rows as $r) $map[$r['day']] = (float) $r['total'];

    $result = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date            = date('Y-m-d', strtotime("-{$i} days"));
        $result[]        = ['date' => $date, 'total' => $map[$date] ?? 0];
    }
    return $result;
}

/* ════════════════════════════════════════════════════════════════
   SUMMARY  (KPI cards)
   ════════════════════════════════════════════════════════════════ */
function buildSummary(mysqli $db, int $uid): array
{
    $todayStart     = date('Y-m-d') . ' 00:00:00';
    $todayEnd       = date('Y-m-d') . ' 23:59:59';
    $yesterdayStart = date('Y-m-d', strtotime('-1 day')) . ' 00:00:00';
    $yesterdayEnd   = date('Y-m-d', strtotime('-1 day')) . ' 23:59:59';
    $monthStart     = date('Y-m-01') . ' 00:00:00';
    $monthEnd       = date('Y-m-d') . ' 23:59:59';

    // Today's totals
    $elecToday  = sumResource($db, $uid, 'electricity', $todayStart, $todayEnd);
    $waterToday = sumResource($db, $uid, 'water',       $todayStart, $todayEnd);
    $gasToday   = sumResource($db, $uid, 'gas',         $todayStart, $todayEnd);

    // Yesterday's totals (for % change)
    $elecYest  = sumResource($db, $uid, 'electricity', $yesterdayStart, $yesterdayEnd);
    $waterYest = sumResource($db, $uid, 'water',       $yesterdayStart, $yesterdayEnd);
    $gasYest   = sumResource($db, $uid, 'gas',         $yesterdayStart, $yesterdayEnd);

    // Solar (from solar_tracker if available, fallback to resource_usage)
    $solarStmt = $db->prepare(
        'SELECT COALESCE(SUM(generated_kwh), 0) AS gen,
                COALESCE(SUM(exported_kwh),  0) AS exp
           FROM solar_tracker
          WHERE user_id = ?
            AND DATE(recorded_at) = CURDATE()'
    );
    $solarStmt->bind_param('i', $uid);
    $solarStmt->execute();
    $solarRow = $solarStmt->get_result()->fetch_assoc();
    $solarStmt->close();

    $solarGen = (float) ($solarRow['gen'] ?? 0);
    $solarExp = (float) ($solarRow['exp'] ?? 0);

    // Fallback: solar from resource_usage
    if ($solarGen == 0) {
        $solarGen = sumResource($db, $uid, 'solar', $todayStart, $todayEnd);
    }

    // Tariffs
    $elecRate  = getTariff($db, $uid, 'electricity'); // $/kWh
    $waterRate = getTariff($db, $uid, 'water');        // $/L
    $gasRate   = getTariff($db, $uid, 'gas');          // $/m³

    // Monthly cost estimate
    $elecMonth  = sumResource($db, $uid, 'electricity', $monthStart, $monthEnd);
    $waterMonth = sumResource($db, $uid, 'water',       $monthStart, $monthEnd);
    $gasMonth   = sumResource($db, $uid, 'gas',         $monthStart, $monthEnd);

    $estBill = round(
        $elecMonth  * $elecRate  +
        $waterMonth * $waterRate +
        $gasMonth   * $gasRate,
        2
    );

    // CO₂ footprint today (electricity: ~0.233 kg/kWh, gas: ~2.04 kg/m³)
    $co2 = round($elecToday * 0.233 + $gasToday * 2.04, 2);

    // % change helper
    $pct = fn($now, $prev) => $prev > 0
        ? round((($now - $prev) / $prev) * 100, 1)
        : ($now > 0 ? 100 : 0);

    // Device count & live draw
    $devStmt = $db->prepare(
        'SELECT COUNT(*) AS cnt, COALESCE(SUM(wattage), 0) AS watts
           FROM devices
          WHERE user_id = ? AND status = "on"'
    );
    $devStmt->bind_param('i', $uid);
    $devStmt->execute();
    $devRow = $devStmt->get_result()->fetch_assoc();
    $devStmt->close();

    // Unread alerts count
    $alertStmt = $db->prepare(
        'SELECT COUNT(*) AS cnt FROM alerts WHERE user_id = ? AND is_read = 0'
    );
    $alertStmt->bind_param('i', $uid);
    $alertStmt->execute();
    $alertRow = $alertStmt->get_result()->fetch_assoc();
    $alertStmt->close();

    // Goals progress
    $goalStmt = $db->prepare(
        'SELECT resource_type, target_value, current_value, unit, period, status
           FROM goals
          WHERE user_id = ? AND status = "active"'
    );
    $goalStmt->bind_param('i', $uid);
    $goalStmt->execute();
    $goals = $goalStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $goalStmt->close();

    // Map current usage into goals
    $usageMap = [
        'electricity' => $elecMonth,
        'water'       => $waterMonth,
        'gas'         => $gasMonth,
    ];
    foreach ($goals as &$g) {
        $g['current_value'] = $usageMap[$g['resource_type']] ?? (float) $g['current_value'];
        $g['pct'] = $g['target_value'] > 0
            ? min(100, round(($g['current_value'] / $g['target_value']) * 100, 1))
            : 0;
    }
    unset($g);

    return [
        'electricity' => [
            'today'     => round($elecToday, 2),
            'yesterday' => round($elecYest, 2),
            'pct_change'=> $pct($elecToday, $elecYest),
            'unit'      => 'kWh',
        ],
        'water' => [
            'today'     => round($waterToday, 1),
            'yesterday' => round($waterYest, 1),
            'pct_change'=> $pct($waterToday, $waterYest),
            'unit'      => 'L',
        ],
        'gas' => [
            'today'     => round($gasToday, 2),
            'yesterday' => round($gasYest, 2),
            'pct_change'=> $pct($gasToday, $gasYest),
            'unit'      => 'm³',
        ],
        'solar' => [
            'generated_kwh' => round($solarGen, 2),
            'exported_kwh'  => round($solarExp, 2),
            'net_kwh'       => round($solarGen - $solarExp, 2),
            'unit'          => 'kWh',
        ],
        'est_bill' => [
            'month'    => $estBill,
            'currency' => $user['currency'] ?? 'USD',
            'breakdown'=> [
                'electricity' => round($elecMonth  * $elecRate,  2),
                'water'       => round($waterMonth * $waterRate, 2),
                'gas'         => round($gasMonth   * $gasRate,   2),
            ],
        ],
        'co2' => [
            'today' => $co2,
            'unit'  => 'kg',
        ],
        'devices' => [
            'active_count' => (int) ($devRow['cnt'] ?? 0),
            'live_watt'    => (float) ($devRow['watts'] ?? 0),
            'live_kw'      => round(($devRow['watts'] ?? 0) / 1000, 2),
        ],
        'alerts_unread' => (int) ($alertRow['cnt'] ?? 0),
        'goals'         => $goals,
        'tariffs'       => [
            'electricity' => $elecRate,
            'water'       => $waterRate,
            'gas'         => $gasRate,
        ],
    ];
}

/* ════════════════════════════════════════════════════════════════
   CHART DATA  (Resource Consumption line chart)
   ════════════════════════════════════════════════════════════════ */
function buildChart(mysqli $db, int $uid, int $days = 7): array
{
    $elec  = dailyTotals($db, $uid, 'electricity', $days);
    $water = dailyTotals($db, $uid, 'water',       $days);
    $gas   = dailyTotals($db, $uid, 'gas',         $days);
    $solar = dailyTotals($db, $uid, 'solar',       $days);

    $labels = array_column($elec, 'date');

    return [
        'labels'   => $labels,
        'datasets' => [
            'electricity' => array_column($elec,  'total'),
            'water'       => array_column($water, 'total'),
            'gas'         => array_column($gas,   'total'),
            'solar'       => array_column($solar, 'total'),
        ],
    ];
}

/* ════════════════════════════════════════════════════════════════
   ALERTS
   ════════════════════════════════════════════════════════════════ */
function buildAlerts(mysqli $db, int $uid): array
{
    $stmt = $db->prepare(
        'SELECT a.*, d.name AS device_name
           FROM alerts a
           LEFT JOIN devices d ON d.id = a.device_id
          WHERE a.user_id = ? AND a.is_read = 0
          ORDER BY a.created_at DESC
          LIMIT 10'
    );
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/* ════════════════════════════════════════════════════════════════
   MARK ALERT AS READ
   ════════════════════════════════════════════════════════════════ */
function markAlertRead(mysqli $db, int $uid, int $alertId): void
{
    $stmt = $db->prepare(
        'UPDATE alerts SET is_read = 1 WHERE id = ? AND user_id = ?'
    );
    $stmt->bind_param('ii', $alertId, $uid);
    $stmt->execute();
    $stmt->close();
}

/* ════════════════════════════════════════════════════════════════
   ROUTER
   ════════════════════════════════════════════════════════════════ */
switch ($action) {

    case 'summary':
        apiResponse(['ok' => true, 'data' => buildSummary($db, $uid)]);

    case 'chart':
        $days = in_array((int) ($_GET['days'] ?? 7), [7, 14, 30]) ? (int) $_GET['days'] : 7;
        apiResponse(['ok' => true, 'data' => buildChart($db, $uid, $days)]);

    case 'alerts':
        apiResponse(['ok' => true, 'data' => buildAlerts($db, $uid)]);

    case 'mark_read':
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) apiResponse(['ok' => false, 'msg' => 'Missing alert id'], 400);
        markAlertRead($db, $uid, $id);
        apiResponse(['ok' => true]);

    case 'all':
    default:
        $days = in_array((int) ($_GET['days'] ?? 7), [7, 14, 30]) ? (int) $_GET['days'] : 7;
        apiResponse([
            'ok'      => true,
            'summary' => buildSummary($db, $uid),
            'chart'   => buildChart($db, $uid, $days),
            'alerts'  => buildAlerts($db, $uid),
        ]);
}
