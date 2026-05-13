<?php
require 'session.php';
$data = json_decode(file_get_contents('php://input'), true);
$_SESSION['user_id'] = $data['userId'];
echo json_encode(['ok' => true]);
