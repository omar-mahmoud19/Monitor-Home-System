<?php
// امسك كل الـ errors وحولها لـ JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);
set_exception_handler(function ($e) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'msg' => 'Server error: ' . $e->getMessage()]);
    exit;
});

require_once __DIR__ . '/session.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $result = registerUser($_POST);
    apiResponse($result, $result['ok'] ? 200 : 400);
}

if ($action === 'login') {
    $result = loginUser($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($result['ok']) {
        unset($result['user']['password']);
        apiResponse($result);
    } else {
        apiResponse($result, 401);
    }
}

if ($action === 'logout') {
    logoutUser();
    apiResponse(['ok' => true, 'msg' => 'Logged out']);
}

if ($action === 'profile') {
    $user = getCurrentUser();
    if (!$user) apiResponse(['ok' => false, 'msg' => 'Not logged in'], 401);
    apiResponse(['ok' => true, 'user' => $user]);
}

apiResponse(['ok' => false, 'msg' => 'Invalid action'], 400);
