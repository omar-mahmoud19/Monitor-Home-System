<?php

/**
 * api/dashboard.php
 * AOT Homes | CS251 Software Engineering
 *
 * GET ?action=all     → summary + chart + alerts
 * GET ?action=summary → KPI cards
 * GET ?action=chart   → line chart data
 * GET ?action=alerts  → unread alerts
 * GET ?action=mark_read&id=N → dismiss alert
 */

require_once __DIR__ . '/../api/session.php';

header('Content-Type: application/json');
checkAuth();

$user   = getCurrentUser();
$uid    = (int) $user['id'];
$db     = getDB();
$action = $_GET['action'] ?? 'all';

$homeId = (int) ($user['home_id'] ?? 0);

$queryId = $homeId > 0 ? $homeId : $uid;

/* ════════════════════════════════════════════════════════
   HELPER — sum a resource for today / yesterday / month
   ════════════════════════════════════════════════════════ */
function sumRes(mysqli $db, int $homeId, string $type, string $from, string $to): float
{
    $stmt = $db->prepare(
        'SELECT COALESCE(SUM(ru.value),0) AS total
         FROM resource_usage ru
         WHERE ru.user_id IN (SELECT id FROM users WHERE home_id = ?)
         AND ru.resource_type = ?
         AND ru.recorded_at BETWEEN ? AND ?'
    );
    $stmt->bind_param('isss', $homeId, $type, $from, $to);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (float)($r['total'] ?? 0);
}
/* ════════════════════════════════════════════════════════
   HELPER — get tariff rate
   ════════════════════════════════════════════════════════ */
function getRate(mysqli $db, int $uid, string $type): float
{
    // First try user's own tariff
    $stmt = $db->prepare(
        'SELECT rate FROM tariffs
         WHERE user_id = ? AND resource_type = ?
         ORDER BY effective_from DESC LIMIT 1'
    );
    $stmt->bind_param('is', $uid, $type);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($r && $r['rate'] > 0) return (float)$r['rate'];

    // Fallback: use owner's tariff from same home
    $stmt = $db->prepare(
        'SELECT t.rate FROM tariffs t
         JOIN users u ON t.user_id = u.id
         JOIN users me ON me.id = ?
         WHERE u.home_id = me.home_id
         AND t.resource_type = ?
         ORDER BY t.effective_from DESC LIMIT 1'
    );
    $stmt->bind_param('is', $uid, $type);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($r && $r['rate'] > 0) return (float)$r['rate'];

    // Final defaults
    $defaults = ['electricity' => 0.28, 'water' => 0.005, 'gas' => 0.45];
    return $defaults[$type] ?? 0.10;
}
/* ════════════════════════════════════════════════════════
   HELPER — daily totals for chart (fills missing days)
   ════════════════════════════════════════════════════════ */
function dailyTotals(mysqli $db, int $homeId, string $type, int $days): array
{
    $stmt = $db->prepare(
        'SELECT DATE(ru.recorded_at) AS day, COALESCE(SUM(ru.value),0) AS total
         FROM resource_usage ru
         WHERE ru.user_id IN (SELECT id FROM users WHERE home_id = ?)
         AND ru.resource_type = ?
         AND ru.recorded_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
         GROUP BY DATE(ru.recorded_at)
         ORDER BY day ASC'
    );
    $stmt->bind_param('isi', $homeId, $type, $days);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $map = [];
    foreach ($rows as $r) $map[$r['day']] = (float)$r['total'];

    $result = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date     = date('Y-m-d', strtotime("-{$i} days"));
        $result[] = ['date' => $date, 'total' => $map[$date] ?? 0];
    }
    return $result;
}
/* ════════════════════════════════════════════════════════
   BUILD SUMMARY  (KPI cards data)
   ════════════════════════════════════════════════════════ */
function buildSummary(mysqli $db, int $uid, int $homeId): array
{

    $ownerUid = $uid;
    if ($homeId) {
        $ownerRes = $db->query("SELECT owner_id FROM homes WHERE id = $homeId");
        $ownerRow = $ownerRes->fetch_assoc();
        $ownerUid = (int)($ownerRow['owner_id'] ?? $uid);
    }

    $todayS    = date('Y-m-d') . ' 00:00:00';
    $todayE    = date('Y-m-d') . ' 23:59:59';
    $yestS     = date('Y-m-d', strtotime('-1 day')) . ' 00:00:00';
    $yestE     = date('Y-m-d', strtotime('-1 day')) . ' 23:59:59';
    $monthS    = date('Y-m-01') . ' 00:00:00';
    $monthE    = date('Y-m-d') . ' 23:59:59';

    // ── Today ──
    $elecT  = sumRes($db, $homeId, 'electricity', $todayS, $todayE);
    $waterT = sumRes($db, $homeId, 'water',       $todayS, $todayE);
    $gasT   = sumRes($db, $homeId, 'gas',         $todayS, $todayE);
    $solarT = sumRes($db, $homeId, 'solar',       $todayS, $todayE);
    $elecY  = sumRes($db, $homeId, 'electricity', $yestS,  $yestE);
    $waterY = sumRes($db, $homeId, 'water',       $yestS,  $yestE);
    $gasY   = sumRes($db, $homeId, 'gas',         $yestS,  $yestE);
    $solarY = sumRes($db, $homeId, 'solar',       $yestS,  $yestE);
    $elecM  = sumRes($db, $homeId, 'electricity', $monthS, $monthE);
    $waterM = sumRes($db, $homeId, 'water',       $monthS, $monthE);
    $gasM   = sumRes($db, $homeId, 'gas',         $monthS, $monthE);

    $stmt = $db->prepare('SELECT COALESCE(SUM(generated_kwh),0) AS total FROM solar_tracker WHERE user_id=? AND DATE(recorded_at)=CURDATE()');
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $solarFromTracker = (float)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();
    $solarT = max($solarT, $solarFromTracker);

    // ── Yesterday (for % change) ──
    $elecY  = sumRes($db, $uid, 'electricity', $yestS, $yestE);
    $waterY = sumRes($db, $uid, 'water',       $yestS, $yestE);
    $gasY   = sumRes($db, $uid, 'gas',         $yestS, $yestE);
    $solarY = sumRes($db, $uid, 'solar',       $yestS, $yestE);

    // ── Month totals (for cost) ──
    $elecM  = sumRes($db, $uid, 'electricity', $monthS, $monthE);
    $waterM = sumRes($db, $uid, 'water',       $monthS, $monthE);
    $gasM   = sumRes($db, $uid, 'gas',         $monthS, $monthE);

    // ── Tariff rates ──

    $elecRate  = getRate($db, $ownerUid, 'electricity');
    $waterRate = getRate($db, $ownerUid, 'water');
    $gasRate   = getRate($db, $ownerUid, 'gas');

    // ── % change helper ──
    $pct = fn($now, $prev) => $prev > 0
        ? round((($now - $prev) / $prev) * 100, 1)
        : ($now > 0 ? 100 : 0);

    // ── Solar net (generated - exported) ─
    $expStmt = $db->prepare('SELECT COALESCE(SUM(exported_kwh),0) AS total FROM solar_tracker WHERE user_id=? AND DATE(recorded_at)=CURDATE()');
    $expStmt->bind_param('i', $uid);
    $expStmt->execute();
    $solarExported = (float)($expStmt->get_result()->fetch_assoc()['total'] ?? 0);
    $expStmt->close();
    $solarNet = round($solarT - $solarExported, 2);

    // ── CO₂ today (electricity: 0.233 kg/kWh, gas: 2.04 kg/m³) ──
    $co2 = round($elecT * 0.233 + $gasT * 2.04, 2);

    // ── Est. Bill this month ──
    $solarSavings = round($solarT * $elecRate, 2);
    $estBill = round(
        $elecM  * $elecRate  +
            $waterM * $waterRate +
            $gasM   * $gasRate,
        2
    );
    $devStmt = $db->prepare(
        'SELECT COUNT(*) AS cnt
       FROM devices WHERE user_id=? AND status="on"'
    );
    $devStmt->bind_param('i', $uid);
    $devStmt->execute();
    $dev = $devStmt->get_result()->fetch_assoc();
    $devStmt->close();

    // ── Unread alerts count ──
    $aStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM alerts WHERE user_id=? AND is_read=0');
    $aStmt->bind_param('i', $uid);
    $aStmt->execute();
    $aRow = $aStmt->get_result()->fetch_assoc();
    $aStmt->close();

    // ── Active goals + map current usage ──
    $gStmt = $db->prepare(
        'SELECT resource_type, target_value, current_value, unit, period, status
           FROM goals WHERE user_id=? AND status="active"'
    );
    $gStmt->bind_param('i', $uid);
    $gStmt->execute();
    $goals = $gStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $gStmt->close();

    $usageMap = [
        'electricity' => $elecM,
        'water'       => $waterM,
        'gas'         => $gasM,
        'solar'       => $solarT,
    ];
    foreach ($goals as &$g) {
        $g['current_value'] = $usageMap[$g['resource_type']] ?? (float)$g['current_value'];
        $g['pct'] = $g['target_value'] > 0
            ? min(100, round(($g['current_value'] / $g['target_value']) * 100, 1))
            : 0;
    }
    unset($g);

    return [
        'electricity' => [
            'today'      => round($elecT, 2),
            'yesterday'  => round($elecY, 2),
            'pct_change' => $pct($elecT, $elecY),
            'unit'       => 'kWh',
        ],
        'water' => [
            'today'      => round($waterT, 1),
            'yesterday'  => round($waterY, 1),
            'pct_change' => $pct($waterT, $waterY),
            'unit'       => 'L',
        ],
        'gas' => [
            'today'      => round($gasT, 2),
            'yesterday'  => round($gasY, 2),
            'pct_change' => $pct($gasT, $gasY),
            'unit'       => 'm³',
        ],
        // ★ Solar — بيجي من resource_usage زي الكهرباء والمياه تماماً
        'solar' => [
            'today'         => round($solarT, 2),
            'yesterday'     => round($solarY, 2),
            'pct_change'    => $pct($solarT, $solarY),
            'generated_kwh' => round($solarT, 2),
            'exported_kwh'  => $solarExported,
            'net_kwh'       => $solarNet,
            'unit'          => 'kWh',
        ],
        'est_bill' => [
            'month'     => $estBill,
            'currency'  => $uid ? ($db->query("SELECT currency FROM users WHERE id=$uid")->fetch_assoc()['currency'] ?? 'USD') : 'USD',
            'breakdown' => [
                'electricity' => round($elecM * $elecRate, 2),
                'water'       => round($waterM * $waterRate, 2),
                'gas'         => round($gasM * $gasRate, 2),
                'solar'       => -$solarSavings,
            ],
        ],
        'co2' => [
            'today' => $co2,
            'unit'  => 'kg',
        ],
        'devices' => [
            'active_count' => (int)($dev['cnt'] ?? 0),
            'live_watt'    => 0,
            'live_kw'      => 0,
        ],
        'alerts_unread' => (int)($aRow['cnt'] ?? 0),
        'goals'         => $goals,
        'tariffs'       => [
            'electricity' => $elecRate,
            'water'       => $waterRate,
            'gas'         => $gasRate,
        ],
    ];
}

/* ════════════════════════════════════════════════════════
   BUILD CHART  (line chart — all 4 resources including solar)
   ════════════════════════════════════════════════════════ */
function buildChart(mysqli $db, int $uid, int $homeId, int $days): array
{
    $elec  = dailyTotals($db, $homeId, 'electricity', $days);
    $water = dailyTotals($db, $homeId, 'water',       $days);
    $gas   = dailyTotals($db, $homeId, 'gas',         $days);
    $solar = dailyTotals($db, $homeId, 'solar',       $days);
    $labels = array_column($elec, 'date');

    return [
        'labels'   => $labels,
        'datasets' => [
            'electricity' => array_column($elec,  'total'),
            'water'       => array_column($water, 'total'),
            'gas'         => array_column($gas,   'total'),
            'solar'       => array_column($solar, 'total'), // ★
        ],
    ];
}

/* ════════════════════════════════════════════════════════
   BUILD ALERTS
   ════════════════════════════════════════════════════════ */
function buildAlerts(mysqli $db, int $uid): array
{
    $stmt = $db->prepare(
        'SELECT a.*, d.name AS device_name
           FROM alerts a
           LEFT JOIN devices d ON d.id = a.device_id
          WHERE a.user_id=? AND a.is_read=0
          ORDER BY a.created_at DESC LIMIT 10'
    );
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/* ════════════════════════════════════════════════════════
   ROUTER
   ════════════════════════════════════════════════════════ */
switch ($action) {

    case 'summary':
        apiResponse(['ok' => true, 'data' => buildSummary($db, $uid, $homeId)]);
        break;
    case 'chart':
        $days = in_array((int)($_GET['days'] ?? 7), [7, 14, 30]) ? (int)$_GET['days'] : 7;
        apiResponse(['ok' => true, 'data' => buildChart($db, $uid, $homeId, $days)]);
        break;
    case 'alerts':
        apiResponse(['ok' => true, 'data' => buildAlerts($db, $uid)]);
        break;
    case 'mark_read':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) apiResponse(['ok' => false, 'msg' => 'Missing id'], 400);
        $stmt = $db->prepare('UPDATE alerts SET is_read=1 WHERE id=? AND user_id=?');
        $stmt->bind_param('ii', $id, $uid);
        $stmt->execute();
        $stmt->close();
        apiResponse(['ok' => true]);
        break;

    case 'vacation_toggle':
        $stmt = $db->prepare('SELECT vacation_mode FROM users WHERE id=?');
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $current = (int)($stmt->get_result()->fetch_assoc()['vacation_mode'] ?? 0);
        $stmt->close();
        $new = $current ? 0 : 1;
        $stmt = $db->prepare('UPDATE users SET vacation_mode=? WHERE id=?');
        $stmt->bind_param('ii', $new, $uid);
        $stmt->execute();
        $stmt->close();
        apiResponse(['ok' => true, 'vacation_mode' => (bool)$new]);
        break;
    case 'all':
    default:
        $days = in_array((int)($_GET['days'] ?? 7), [7, 14, 30]) ? (int)$_GET['days'] : 7;
        apiResponse([
            'ok'      => true,
            'summary' => buildSummary($db, $uid, $homeId),
            'chart'   => buildChart($db, $uid, $homeId, $days),
            'alerts'  => buildAlerts($db, $uid),
        ]);
        break;
}
