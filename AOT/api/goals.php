<?php

/**
 * api/goals.php
 * AOT Homes | CS251 Software Engineering
 *
 * Connects: Goals page (JS) ↔ GoalModel ↔ goals table
 *
 * Actions (POST):
 *   list     → All goals for logged-in user
 *   create   → New goal
 *   update   → Edit goal
 *   progress → Update current_value + auto-detect achieved
 *   delete   → Remove goal
 *   budget   → Budget summary (active goals with progress %)
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../models/GoalModel.php';
require_once __DIR__ . '/../models/ActionLogModel.php';
require_once __DIR__ . '/../models/AlertObserver.php';

header('Content-Type: application/json');
checkRole(['owner', 'tenant']);

$user      = getCurrentUser();
$uid       = (int) $user['id'];
$action    = $_POST['action'] ?? $_GET['action'] ?? '';

$goalModel = new GoalModel();
$logModel  = new ActionLogModel();

switch ($action) {

    case 'list':
        $goals = $goalModel->findByUser($uid);
        foreach ($goals as &$g) {
            $g['progress_pct'] = $goalModel->progress((int) $g['id']);
        }
        apiResponse(['ok' => true, 'goals' => $goals]);

    case 'create':
        $required = ['resource_type', 'target_value', 'period'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                apiResponse(['ok' => false, 'msg' => "Missing field: $field"], 400);
            }
        }

        $id = $goalModel->create(array_merge(['user_id' => $uid], $_POST));
        $logModel->log(
            $uid,
            'goal_create',
            'Created ' . $_POST['period'] . ' goal for ' . $_POST['resource_type']
        );

        apiResponse(['ok' => true, 'id' => $id, 'goal' => $goalModel->findById($id)], 201);

    case 'update':
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) apiResponse(['ok' => false, 'msg' => 'Missing goal id'], 400);

        $goalModel->update($id, $_POST);
        $logModel->log($uid, 'goal_update', 'Updated goal #' . $id);
        apiResponse(['ok' => true, 'goal' => $goalModel->findById($id)]);

    case 'progress':
        $id      = (int) ($_POST['id'] ?? 0);
        $current = (float) ($_POST['current_value'] ?? 0);
        if (!$id) apiResponse(['ok' => false, 'msg' => 'Missing goal id'], 400);

        $goalModel->updateProgress($id, $current);
        $goal = $goalModel->findById($id);

        // Fire achievement alert
        if ($goal['status'] === 'achieved') {
            $manager = new AlertManager();
            $manager->attach(new DashboardNotifier());
            $manager->trigger([
                'user_id'  => $uid,
                'type'     => 'goal',
                'priority' => 'medium',
                'title'    => '🎯 Goal Achieved!',
                'message'  => ucfirst($goal['resource_type']) . ' ' . $goal['period'] . ' goal completed.',
            ]);
        }

        apiResponse(['ok' => true, 'goal' => $goal, 'progress_pct' => $goalModel->progress($id)]);

    case 'delete':
        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) apiResponse(['ok' => false, 'msg' => 'Missing goal id'], 400);

        $goalModel->delete($id);
        $logModel->log($uid, 'goal_delete', 'Deleted goal #' . $id);
        apiResponse(['ok' => true]);

    case 'budget':
        // Used by the Goals page budget gauges (type=budget query param)
        $goals = $goalModel->findActive($uid);
        $budget = [];
        foreach ($goals as $g) {
            $budget[] = [
                'resource_type' => $g['resource_type'],
                'period'        => $g['period'],
                'target'        => $g['target_value'],
                'current'       => $g['current_value'],
                'unit'          => $g['unit'],
                'progress_pct'  => $goalModel->progress((int) $g['id']),
                'status'        => $g['status'],
            ];
        }
        apiResponse(['ok' => true, 'budget' => $budget]);

    default:
        apiResponse(['ok' => false, 'msg' => 'Invalid action'], 400);
}
