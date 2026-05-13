<?php
require 'api/session.php';
checkAuth();
$user = getCurrentUser();
if ($user) unset($user['password']);
require 'views/dashboard.php';
