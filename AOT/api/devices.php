<?php

/**
 * api/devices.php
 * AOT Homes | CS251 Software Engineering
 *
 * Connects: Appliances page (JS) ↔ DeviceModel ↔ devices table
 *
 * Actions (POST):
 *   list    → GET all devices for logged-in user
 *   create  → Add new device (uses ApplianceFactory)
 *   update  → Edit device fields
 *   toggle  → Toggle on/off status
 *   delete  → Remove device
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../models/DeviceModel.php';
require_once __DIR__ . '/../models/ApplianceFactory.php';
require_once __DIR__ . '/../models/ActionLogModel.php';
require_once __DIR__ . '/../models/AlertObserver.php';

header('Content-Type: application/json');
checkAuth();

$user   = getCurrentUser();
$uid    = (int) $user['id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$deviceModel = new DeviceModel();
$logModel    = new ActionLogModel();

switch ($action) {

    // ── LIST ─────────────────────────────────────────────────────────────
    case 'list':
        $homeId  = (int)($user['home_id'] ?? 0);
        $devices = $homeId
            ? $deviceModel->findByHome($homeId)
            : $deviceModel->findByUser($uid);

        $db = getDB();
        foreach ($devices as &$d) {
            if ($d['type'] === 'solar_panel') continue;

            // ✅ شيل user_id — خد usage بتاع الـ device بغض النظر عن مين ضغطه
            $stmt = $db->prepare(
                'SELECT COALESCE(SUM(value), 0) AS total
             FROM resource_usage
             WHERE device_id = ?
               AND resource_type = "electricity"
               AND DATE(recorded_at) = CURDATE()'
            );
            $stmt->bind_param('i', $d['id']);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $d['daily_usage_kwh'] = round((float)$row['total'], 3);
        }
        unset($d);

        $totalW = $homeId
            ? $deviceModel->totalWattageByHome($homeId)
            : $deviceModel->totalWattage($uid);

        apiResponse([
            'ok'            => true,
            'devices'       => $devices,
            'total_wattage' => $totalW,
        ]);
        break;
    // ── CREATE ───────────────────────────────────────────────────────────
    case 'create':

        $type = $_POST['type'] ?? 'generic';
        $resources = [];
        foreach (['electricity', 'water', 'gas', 'solar'] as $r) {
            if (!empty($_POST["res_{$r}"])) {
                $resources[] = [
                    'type' => $r,
                    'rate' => (float)($_POST["rate_{$r}"] ?? 0),
                    'unit' => $r === 'water' ? 'L' : ($r === 'gas' ? 'm³' : 'kWh'),
                ];
            }
        }

        // Use Factory to get smart defaults, then override with user input
        $appliance = ApplianceFactory::create($type, array_merge(
            ['user_id' => $uid, 'resources' => $resources],
            array_intersect_key($_POST, array_flip([
                'name',
                'type',
                'category',
                'wattage',
                'location',
                'icon',
                'status'
            ]))
        ));
        $id = $appliance->save();
        // $defaultStatus  = $_POST['status'] ?? 'off';
        // $defaultWattage = (float)($_POST['wattage'] ?? 0);
        // $deviceType     = $_POST['type'] ?? 'generic';

        // if ($defaultStatus === 'on' && $deviceType !== 'solar_panel') {
        //     $dailyKwh = round(($defaultWattage / 1000) * 8, 3);
        //     $deviceModel->update($id, ['daily_usage_kwh' => $dailyKwh]);
        // }

        $logModel->log($uid, 'device_create', 'Added device: ' . ($_POST['name'] ?? $type));


        apiResponse(['ok' => true, 'id' => $id, 'device' => $deviceModel->findById($id)], 201);
        break;
    // ── UPDATE ───────────────────────────────────────────────────────────
    case 'update':
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) apiResponse(['ok' => false, 'msg' => 'Missing device id'], 400);

        $resources = [];
        foreach (['electricity', 'water', 'gas', 'solar'] as $r) {
            if (!empty($_POST["res_{$r}"])) {
                $resources[] = [
                    'type' => $r,
                    'rate' => (float)($_POST["rate_{$r}"] ?? 0),
                    'unit' => $r === 'water' ? 'L' : ($r === 'gas' ? 'm³' : 'kWh'),
                ];
            }
        }
        $deviceModel->update($id, array_merge($_POST, ['resources' => $resources]));
        $logModel->log($uid, 'device_update', 'Updated device #' . $id);

        apiResponse(['ok' => true, 'device' => $deviceModel->findById($id)]);
        break;
    // ── TOGGLE ───────────────────────────────────────────────────────────
    case 'toggle':
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) apiResponse(['ok' => false, 'msg' => 'Missing device id'], 400);

        $newStatus = $deviceModel->toggleStatus($id);
        $device    = $deviceModel->findById($id);

        $logModel->log(
            $uid,
            'device_toggle',
            ($device['name'] ?? 'Device') . ' turned ' . strtoupper($newStatus)
        );
        if ($newStatus === 'on') {
            $db = getDB();
            $resources = $deviceModel->getResources($id);

            foreach ($resources as $r) {
                $rType = $r['resource_type'];
                $rate  = (float)$r['consumption_rate'];
                if ($rate <= 0) continue;

                if ($rType === 'solar') {
                    $s = $db->prepare('INSERT INTO solar_tracker (user_id, generated_kwh, exported_kwh, recorded_at) VALUES (?, ?, 0, NOW())');
                    $s->bind_param('id', $uid, $rate);
                    $s->execute();
                    $s->close();
                }

                // ✅ تأكد مفيش record بالفعل النهارده لنفس الـ device
                $check = $db->prepare(
                    'SELECT id FROM resource_usage 
             WHERE device_id = ? AND resource_type = ? AND DATE(recorded_at) = CURDATE()
             LIMIT 1'
                );
                $check->bind_param('is', $id, $rType);
                $check->execute();
                $exists = $check->get_result()->fetch_assoc();
                $check->close();

                if (!$exists) {
                    $stmt = $db->prepare(
                        'INSERT INTO resource_usage (user_id, device_id, resource_type, value, unit, recorded_at)
                 VALUES (?, ?, ?, ?, ?, NOW())'
                    );
                    $unit = $r['unit'] ?? 'kWh';
                    $stmt->bind_param('iisss', $uid, $id, $rType, $rate, $unit);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        apiResponse(['ok' => true, 'status' => $newStatus, 'device' => $device]);
        break;
    // ── DELETE ───────────────────────────────────────────────────────────
    case 'delete':
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) apiResponse(['ok' => false, 'msg' => 'Missing device id'], 400);

        $device = $deviceModel->findById($id);
        $deviceModel->delete($id);
        $logModel->log($uid, 'device_delete', 'Removed device: ' . ($device['name'] ?? '#' . $id));

        apiResponse(['ok' => true]);
        break;

    default:
        apiResponse(['ok' => false, 'msg' => 'Invalid action'], 400);
}
