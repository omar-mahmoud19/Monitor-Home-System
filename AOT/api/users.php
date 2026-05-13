<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ActionLogModel.php';

header('Content-Type: application/json');
checkAuth();

$user      = getCurrentUser();
$uid       = (int) $user['id'];
$action    = $_POST['action'] ?? $_GET['action'] ?? '';
$userModel = new UserModel();
$logModel  = new ActionLogModel();

switch ($action) {

    case 'list':
        $users = $userModel->findByHome($uid);
        // Remove password from each user
        foreach ($users as &$u) unset($u['password']);
        apiResponse(['ok' => true, 'users' => $users]);
        break;

    case 'update_role':
        $targetId = (int) ($_POST['id']   ?? 0);
        $role     = $_POST['role'] ?? '';
        if (!$targetId || !$role) {
            apiResponse(['ok' => false, 'msg' => 'Missing id or role'], 400);
        }
        $userModel->updateRole($targetId, $role);
        $logModel->log($uid, 'user_role_update', "Changed user #$targetId role to $role");
        apiResponse(['ok' => true]);
        break;

    case 'delete_account':
        $userModel->softDelete($uid);
        session_destroy();
        apiResponse(['ok' => true]);
        break;

    default:
        apiResponse(['ok' => false, 'msg' => 'Invalid action'], 400);
        break;
}
