<?php
header('Content-Type: application/json');
require_once 'session.php';
checkRole(['owner', 'tenant']);

$devices = [
    ['id' => 1, 'name' => 'Air Conditioner', 'status' => 'On', 'usage' => 3.2],
    ['id' => 2, 'name' => 'Washing Machine', 'status' => 'Off', 'usage' => 0.4],
    ['id' => 3, 'name' => 'Refrigerator', 'status' => 'On', 'usage' => 1.1]
];

if (isset($_GET['metric']) && $_GET['metric'] === 'usage') {
    echo json_encode(['success' => true, 'labels' => ['Mon', 'Tue', 'Wed'], 'values' => [12, 18, 9]]);
    exit;
}

echo json_encode(['success' => true, 'devices' => $devices]);
