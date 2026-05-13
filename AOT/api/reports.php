<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../models/ActionLogModel.php';
require_once __DIR__ . '/../models/Database.php';

header('Content-Type: application/json');
checkRole(['owner', 'tenant']);

$user   = getCurrentUser();
$uid    = (int)($user['id']);
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$format = $_GET['format'] ?? '';
$db     = \Database::getInstance()->getConnection();

// ── Helper: get home member IDs ──
function getHomeMembers($db, $uid, $homeId): string
{
  if (!$homeId) return (string)$uid;
  $r = $db->query("SELECT id FROM users WHERE home_id = $homeId");
  $ids = array_column($r->fetch_all(MYSQLI_ASSOC), 'id');
  return implode(',', $ids ?: [$uid]);
}

$homeId   = (int)($user['home_id'] ?? 0);
$inClause = getHomeMembers($db, $uid, $homeId);

// ── CSV Export ──
if ($format === 'csv') {
  $from = $_GET['from'] ?? date('Y-m-01');
  $to   = $_GET['to']   ?? date('Y-m-d');
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="aot-report-' . date('Y-m-d') . '.csv"');
  $rows = $db->query(
    "SELECT resource_type, SUM(value) as total, unit, DATE(recorded_at) as date
           FROM resource_usage
          WHERE user_id IN ($inClause)
            AND DATE(recorded_at) BETWEEN '$from' AND '$to'
          GROUP BY resource_type, DATE(recorded_at)
          ORDER BY date DESC"
  )->fetch_all(MYSQLI_ASSOC);
  echo "Date,Resource,Total,Unit\n";
  foreach ($rows as $r) {
    echo "{$r['date']},{$r['resource_type']},{$r['total']},{$r['unit']}\n";
  }
  exit;
}

// ── PDF Export ──
if ($format === 'pdf') {
  $from = $_GET['from'] ?? date('Y-m-01');
  $to   = $_GET['to']   ?? date('Y-m-d');
  $rows = $db->query(
    "SELECT resource_type, SUM(value) as total, unit, DATE(recorded_at) as date
           FROM resource_usage
          WHERE user_id IN ($inClause)
            AND DATE(recorded_at) BETWEEN '$from' AND '$to'
          GROUP BY resource_type, DATE(recorded_at)
          ORDER BY date DESC"
  )->fetch_all(MYSQLI_ASSOC);
  header('Content-Type: text/html');
  header('Content-Disposition: attachment; filename="aot-report-' . date('Y-m-d') . '.html"');
  echo '<!DOCTYPE html><html><head><title>AOT Report</title>';
  echo '<style>body{font-family:sans-serif;padding:20px}table{width:100%;border-collapse:collapse}';
  echo 'th,td{border:1px solid #ccc;padding:8px;text-align:left}th{background:#f0f0f0}</style></head><body>';
  echo '<h1>AOT Homes — Energy Report</h1>';
  echo '<p>Period: ' . htmlspecialchars($from) . ' to ' . htmlspecialchars($to) . '</p>';
  echo '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
  echo '<table><thead><tr><th>Date</th><th>Resource</th><th>Total</th><th>Unit</th></tr></thead><tbody>';
  foreach ($rows as $r) {
    echo '<tr><td>' . $r['date'] . '</td><td>' . $r['resource_type'] . '</td>';
    echo '<td>' . round($r['total'], 2) . '</td><td>' . $r['unit'] . '</td></tr>';
  }
  echo '</tbody></table></body></html>';
  exit;
}

// ── Switch ──
switch ($action) {

  case 'generate':
    $from = $_POST['from'] ?? date('Y-m-01');
    $to   = $_POST['to']   ?? date('Y-m-d');
    $type = $_POST['type'] ?? 'custom';

    $stmt = $db->query(
      "SELECT
                DATE_FORMAT(recorded_at, '%b %Y') as label,
                resource_type,
                SUM(value) as total,
                unit
             FROM resource_usage
             WHERE user_id IN ($inClause)
               AND DATE(recorded_at) BETWEEN '$from' AND '$to'
             GROUP BY DATE_FORMAT(recorded_at, '%Y-%m'), resource_type
             ORDER BY MIN(recorded_at) ASC"
    );
    $rows = $stmt->fetch_all(MYSQLI_ASSOC);

    $months = [];
    foreach ($rows as $r) {
      $lbl = $r['label'];
      if (!isset($months[$lbl])) {
        $months[$lbl] = [
          'label'           => $lbl,
          'electricity_kwh' => 0,
          'water_liters'    => 0,
          'gas_m3'          => 0,
          'solar_kwh'       => 0,
          'total_cost'      => 0,
          'co2_kg'          => 0,
          'budget_pct'      => rand(50, 95),
        ];
      }
      if ($r['resource_type'] === 'electricity') {
        $months[$lbl]['electricity_kwh']  = round($r['total'], 2);
        $months[$lbl]['total_cost']       += round($r['total'] * 0.28, 2);
        $months[$lbl]['co2_kg']           += round($r['total'] * 0.233, 2);
      } elseif ($r['resource_type'] === 'water') {
        $months[$lbl]['water_liters']  = round($r['total'], 2);
        $months[$lbl]['total_cost']   += round($r['total'] * 0.005, 2);
      } elseif ($r['resource_type'] === 'gas') {
        $months[$lbl]['gas_m3']       = round($r['total'], 2);
        $months[$lbl]['total_cost']  += round($r['total'] * 0.45, 2);
        $months[$lbl]['co2_kg']      += round($r['total'] * 2.04, 2);
      } elseif ($r['resource_type'] === 'solar') {
        $months[$lbl]['solar_kwh']   = round($r['total'], 2);
        $months[$lbl]['total_cost'] += round($r['total'] * 0.15, 2);
      }
    }

    $dataJson = json_encode([
      'monthly' => array_values($months)
    ]);

    $title = "Report " . date('Y-m-d');

    $stmt = $db->prepare("
    INSERT INTO reports
    (user_id, title, type, period_from, period_to, data_json)
    VALUES (?, ?, ?, ?, ?, ?)
");

    $stmt->bind_param(
      "isssss",
      $uid,
      $title,
      $type,
      $from,
      $to,
      $dataJson
    );

    $stmt->execute();

    $reportId = $stmt->insert_id;

    apiResponse([
      'ok' => true,
      'report' => [
        'id' => $reportId,
        'type' => $type,
        'data_json' => json_decode($dataJson, true),
      ]
    ]);
    break;

  case 'activity':
    $limit = (int)($_GET['limit'] ?? 50);
    $days  = (int)($_GET['days']  ?? 30);

    $rows = $db->query(
      "SELECT a.*, u.firstName as user_name
               FROM action_log a
               LEFT JOIN users u ON a.user_id = u.id
              WHERE a.user_id IN ($inClause)
                AND a.created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)
              ORDER BY a.created_at DESC
              LIMIT $limit"
    )->fetch_all(MYSQLI_ASSOC);

    apiResponse(['ok' => true, 'recent' => $rows]);
    break;

  default:
    apiResponse(['ok' => false, 'msg' => 'Invalid action'], 400);
    break;
}
