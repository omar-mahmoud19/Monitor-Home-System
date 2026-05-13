<?php

/**
 * api/usage.php
 * AOT Homes | CS251 Software Engineering
 *
 * Connects: Dashboard (JS) ↔ ResourceUsageModel + SolarTrackerModel
 *
 * Actions (GET/POST):
 *   dashboard  → KPI cards data (today vs yesterday)
 *   chart      → Time-series for Chart.js
 *   record     → Log a new usage reading (from IoT / cron)
 *   solar      → Solar production data
 */

require_once __DIR__ . '/../api/session.php';
require_once __DIR__ . '/../models/ResourceUsageModel.php';
require_once __DIR__ . '/../models/SolarTrackerModel.php';
require_once __DIR__ . '/../models/TariffModel.php';
require_once __DIR__ . '/../models/AlertObserver.php';
require_once __DIR__ . '/../models/ActionLogModel.php';

header('Content-Type: application/json');
checkAuth();

$user   = getCurrentUser();
$uid    = (int) $user['id'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'dashboard';

$usageModel  = new ResourceUsageModel();
$solarModel  = new SolarTrackerModel();
$tariffModel = new TariffModel();

switch ($action) {

    // ── DASHBOARD KPI CARDS ───────────────────────────────────────────────
    case 'dashboard':
        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $kpis  = $usageModel->compareDaily($uid, $today, $yesterday);
        $solar = $solarModel->todayGenerated($uid);

        // Add cost to each resource type
        foreach ($kpis as &$row) {
            $row['cost'] = $tariffModel->calculateCost($uid, $row['resource_type'], $row['total']);
        }

        apiResponse([
            'ok'    => true,
            'kpis'  => $kpis,
            'solar' => [
                'generated_kwh' => $solar,
                'latest'        => $solarModel->latest($uid),
            ],
        ]);

        // ── CHART TIME-SERIES ─────────────────────────────────────────────────
    case 'chart':
        $days  = (int) ($_GET['days'] ?? $_POST['days'] ?? 7);
        $type  = $_GET['type'] ?? $_POST['type'] ?? null;
        $from  = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $to    = date('Y-m-d H:i:s');

        $rows = $usageModel->findByRange($uid, $from, $to, $type ?: null);

        apiResponse(['ok' => true, 'chart_data' => $rows, 'days' => $days]);

        // ── RECORD NEW READING (cron / IoT device) ────────────────────────────
    case 'record':
        $required = ['resource_type', 'value'];
        foreach ($required as $f) {
            if (!isset($_POST[$f])) {
                apiResponse(['ok' => false, 'msg' => "Missing field: $f"], 400);
            }
        }

        $id = $usageModel->record(array_merge(['user_id' => $uid], $_POST));

        // Budget alert check — compare today vs monthly goal
        $value = (float) $_POST['value'];
        if ($value > 30) { // example threshold; in production compare to GoalModel
            $manager = new AlertManager();
            $manager->attach(new DashboardNotifier());
            $manager->trigger([
                'user_id'  => $uid,
                'type'     => 'budget',
                'priority' => 'medium',
                'title'    => 'High ' . ucfirst($_POST['resource_type']) . ' Usage',
                'message'  => 'Single reading of ' . $value . ' ' . ($_POST['unit'] ?? '') . ' detected.',
            ]);
        }

        apiResponse(['ok' => true, 'id' => $id], 201);

        // ── SOLAR ─────────────────────────────────────────────────────────────
    case 'solar':
        $days    = (int) ($_GET['days'] ?? 7);
        $history = $solarModel->history($uid, $days);
        $today   = $solarModel->todayGenerated($uid);
        $life    = $solarModel->lifetime($uid);

        apiResponse([
            'ok'       => true,
            'today'    => $today,
            'history'  => $history,
            'lifetime' => $life,
        ]);

        // ── MONTHLY SUMMARY ───────────────────────────────────────────────────
    case 'monthly':
        $year  = (int) ($_GET['year']  ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        $summary = $usageModel->monthlySummary($uid, $year, $month);
        foreach ($summary as &$row) {
            $row['cost'] = $tariffModel->calculateCost($uid, $row['resource_type'], $row['total']);
        }

        apiResponse(['ok' => true, 'summary' => $summary, 'year' => $year, 'month' => $month]);

        // ── LOG FROM LIVE SENSOR FEED (Dashboard) ─────────────────────────────
    case 'log':
        $elec  = (float) ($_POST['elec']  ?? 0);
        $water = (float) ($_POST['water'] ?? 0);
        $gas   = (float) ($_POST['gas']   ?? 0);
        $solar = (float) ($_POST['solar'] ?? 0);

        $entries = [
            ['electricity', $elec,  'kWh'],
            ['water',       $water, 'L'],
            ['gas',         $gas,   'm3'],
            ['solar',       $solar, 'kWh'],
        ];

        foreach ($entries as [$type, $val, $unit]) {
            if ($val > 0) {
                $usageModel->record([
                    'user_id'       => $uid,
                    'resource_type' => $type,
                    'value'         => $val,
                    'unit'          => $unit,
                ]);
            }
        }

        apiResponse(['ok' => true]);
    default:
        apiResponse(['ok' => false, 'msg' => 'Invalid action'], 400);
}
