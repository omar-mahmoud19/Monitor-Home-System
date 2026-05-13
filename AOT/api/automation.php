<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../models/AlertModel.php';
require_once __DIR__ . '/../models/ActionLogModel.php';
require_once __DIR__ . '/../models/Database.php';
header('Content-Type: application/json');
checkRole(['owner']);


$user   = getCurrentUser();
$uid    = (int) $user['id'];
$action = $_POST['action'] ?? $_GET['type'] ?? '';
$db     = \Database::getInstance()->getConnection();

switch ($action) {

    case 'rules':
        $stmt = $db->prepare('SELECT * FROM rules WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $rules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        apiResponse(['ok' => true, 'rules' => $rules]);

    case 'create_rule':
        $name      = trim($_POST['rule_name']    ?? '');
        $trigger   = trim($_POST['trigger_type'] ?? '');
        $condition = trim($_POST['condition']    ?? '');
        $threshold = trim($_POST['threshold']    ?? '');
        $actionT   = trim($_POST['action_type']  ?? '');

        if (!$name || !$trigger || !$threshold) {
            apiResponse(['ok' => false, 'msg' => 'Missing required fields'], 400);
        }

        $stmt = $db->prepare(
            'INSERT INTO rules (user_id, name, trigger_type, `condition`, threshold, action_type, is_active)
             VALUES (?, ?, ?, ?, ?, ?, 1)'
        );
        $stmt->bind_param('isssss', $uid, $name, $trigger, $condition, $threshold, $actionT);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        apiResponse(['ok' => true, 'id' => $id]);

    case 'toggle_rule':
        $id     = (int) ($_POST['id']     ?? 0);
        $active = (int) ($_POST['active'] ?? 0);
        $stmt   = $db->prepare('UPDATE rules SET is_active = ? WHERE id = ? AND user_id = ?');
        $stmt->bind_param('iii', $active, $id, $uid);
        $stmt->execute();
        $stmt->close();
        apiResponse(['ok' => true]);

    case 'delete_rule':
        $id   = (int) ($_POST['id'] ?? 0);
        $stmt = $db->prepare('DELETE FROM rules WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $id, $uid);
        $stmt->execute();
        $stmt->close();
        apiResponse(['ok' => true]);

    case 'alerts':
        $alertModel = new AlertModel();
        $alerts     = $alertModel->findByUser($uid, 10);
        apiResponse(['ok' => true, 'alerts' => $alerts]);

    case 'acknowledge_alert':
        $id         = (int) ($_POST['id'] ?? 0);
        $alertModel = new AlertModel();
        $alertModel->markRead($id);
        apiResponse(['ok' => true]);

    case 'sensors':
        // Simulated sensors — replace with real sensor table when available
        $sensors = [
            ['name' => 'Electricity Meter', 'status' => 'online'],
            ['name' => 'Water Flow Sensor', 'status' => 'online'],
            ['name' => 'Gas Meter',         'status' => 'online'],
            ['name' => 'Solar Inverter',    'status' => 'online'],
            ['name' => 'HVAC Thermostat',   'status' => 'offline'],
        ];
        apiResponse(['ok' => true, 'sensors' => $sensors]);

    case 'channels':
        $channels = [
            ['id' => 'dashboard', 'icon' => '📊', 'label' => 'Dashboard',      'description' => 'All alerts',        'enabled' => true],
            ['id' => 'email',     'icon' => '📧', 'label' => 'Email',           'description' => 'High priority only', 'enabled' => true],
            ['id' => 'sms',       'icon' => '📱', 'label' => 'SMS (Simulated)', 'description' => 'Critical only',     'enabled' => false],
        ];
        apiResponse(['ok' => true, 'channels' => $channels]);

    case 'vacation_mode':
        $active = (int) ($_POST['active'] ?? 0);
        $stmt   = $db->prepare('UPDATE users SET vacation_mode = ? WHERE id = ?');
        $stmt->bind_param('ii', $active, $uid);
        $stmt->execute();
        $stmt->close();
        apiResponse(['ok' => true]);

    case 'update_channels':
        // Store in session for now — wire to DB when channels table is ready
        $_SESSION['channels'][$_POST['channel'] ?? ''] = (bool)($_POST['enabled'] ?? false);
        apiResponse(['ok' => true]);

    default:
        apiResponse(['ok' => false, 'msg' => 'Invalid action'], 400);
}
