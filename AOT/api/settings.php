<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/TariffModel.php';
require_once __DIR__ . '/../models/ActionLogModel.php';

header('Content-Type: application/json');
require 'api/session.php';
checkRole(['owner', 'tenant']);
$user        = getCurrentUser();
$uid         = (int) $user['id'];
$action      = $_POST['action'] ?? $_GET['action'] ?? '';
$userModel   = new UserModel();
$tariffModel = new TariffModel();
$logModel    = new ActionLogModel();

switch ($action) {

    case 'get_profile':
        $profile = $userModel->findById($uid);
        apiResponse(['ok' => true, 'user' => $profile]);
        break;

    case 'update_profile':
        $userModel->updateProfile($uid, $_POST);
        $updated = $userModel->findById($uid);
        $_SESSION['user'] = $updated;
        $logModel->log($uid, 'settings_update', 'Profile updated');
        apiResponse(['ok' => true, 'user' => $updated]);
        break;

    case 'change_password':
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$current || !$new || !$confirm) {
            apiResponse(['ok' => false, 'msg' => 'All password fields are required'], 400);
        }
        if ($new !== $confirm) {
            apiResponse(['ok' => false, 'msg' => 'New passwords do not match'], 400);
        }

        $fullUser = $userModel->findByEmail($user['email']);
        if (!$fullUser || !password_verify($current, $fullUser['password'])) {
            apiResponse(['ok' => false, 'msg' => 'Current password is incorrect'], 401);
        }

        $userModel->changePassword($uid, $new);
        $logModel->log($uid, 'password_change', 'Password changed');
        apiResponse(['ok' => true, 'msg' => 'Password updated successfully']);
        break;

    case 'get_tariffs':
        $tariffs = $tariffModel->findByUser($uid);
        $types   = ['electricity', 'water', 'gas'];
        $indexed = [];
        foreach ($tariffs as $t) {
            $indexed[$t['resource_type']] = $t;
        }
        $result = [];
        foreach ($types as $type) {
            $result[$type] = $indexed[$type] ?? $tariffModel->getCurrent($uid, $type);
        }
        apiResponse(['ok' => true, 'tariffs' => $result]);
        break;

    case 'set_tariff':
        $type = $_POST['resource_type'] ?? '';
        $rate = (float) ($_POST['rate'] ?? 0);

        if (!$type) {
            apiResponse(['ok' => false, 'msg' => 'Missing resource_type'], 400);
        }

        $existing = $tariffModel->findByUser($uid);
        $found    = null;
        foreach ($existing as $t) {
            if ($t['resource_type'] === $type) {
                $found = $t;
                break;
            }
        }

        $updateData = array_merge(['user_id' => $uid], $_POST);

        if ($found) {
            $tariffModel->update((int) $found['id'], $updateData);
        } else {
            $tariffModel->create($updateData);
        }

        $logModel->log($uid, 'tariff_update', "Set $type rate to $rate");
        apiResponse(['ok' => true, 'tariff' => $tariffModel->getCurrent($uid, $type)]);
        break;

    case 'system_settings':
        $userModel->updateSystemSettings($uid, $_POST);
        $logModel->log($uid, 'system_settings', 'System settings updated');
        apiResponse(['ok' => true]);
        break;
    case 'purge_raw':
        $db   = \Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM resource_usage WHERE user_id = ?');
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $count = $stmt->affected_rows;
        $stmt->close();
        $logModel->log($uid, 'data_purge', "Purged $count raw usage records");
        apiResponse(['ok' => true, 'purged' => $count]);
        break;

    default:
        apiResponse(['ok' => false, 'msg' => 'Invalid action'], 400);
        break;
}
