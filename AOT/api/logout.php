<?php
require_once __DIR__ . '/session.php';
logoutUser();
header('Location: /AOT/login.php');
exit;
