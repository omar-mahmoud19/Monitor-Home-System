<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';

session_start();

define('SESSION_TIMEOUT', 3600);

function checkRole(array $allowedRoles)
{
    checkAuth();
    $user = getCurrentUser();
    if (!in_array($user['role'] ?? 'guest', $allowedRoles)) {
        header('Location: /AOT/index.php?error=unauthorized');
        exit;
    }
    return true;
}

function generateHomeCode(): string
{
    return strtoupper(substr(md5(uniqid()), 0, 8));
}
function checkAuth()
{
    if (isset($_SESSION['user']) && isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            session_destroy();
            header('Location: login.php');
            exit;
        }
        $_SESSION['login_time'] = time();
        return true;
    }
    header('Location: login.php');
    exit;
}

function getCurrentUser()
{
    return $_SESSION['user'] ?? null;
}

function loginUser($email, $password)
{
    $db    = getDB();
    $email = strtolower(trim($email));

    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user)
        return ['ok' => false, 'msg' => 'No account found with that email'];

    if (!password_verify($password, $user['password']))
        return ['ok' => false, 'msg' => 'Incorrect password'];

    $_SESSION['user']       = $user;
    $_SESSION['login_time'] = time();

    return ['ok' => true, 'user' => $user];
}

function registerUser($data)
{
    $db = getDB();

    $firstName = trim($data['firstName'] ?? '');
    $lastName  = trim($data['lastName']  ?? '');
    $email     = strtolower(trim($data['email'] ?? ''));
    $password  = $data['password'] ?? '';
    $homeName  = trim($data['homeName']  ?? '');
    $role      = $data['role'] ?? 'owner';

    if ($role === 'owner') {
        if (!$firstName || !$lastName || !$email || !$password || !$homeName)
            return ['ok' => false, 'msg' => 'All fields are required'];
    } else {
        if (!$firstName || !$lastName || !$email || !$password)
            return ['ok' => false, 'msg' => 'All fields are required'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        return ['ok' => false, 'msg' => 'Invalid email format'];

    if (strlen($password) < 6)
        return ['ok' => false, 'msg' => 'Password must be at least 6 characters'];

    // Check email exists
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return ['ok' => false, 'msg' => 'Email already registered'];
    }
    $stmt->close();

    $hash   = password_hash($password, PASSWORD_BCRYPT);
    $avatar = strtoupper($firstName[0] . $lastName[0]);

    if ($role === 'owner') {
        // Create new home
        $homeCode     = generateHomeCode();
        $homePassword = password_hash($data['homePassword'] ?? $homeCode, PASSWORD_BCRYPT);

        $stmt = $db->prepare(
            'INSERT INTO homes (owner_id, name, home_code, home_password) VALUES (NULL, ?, ?, ?)'
        );
        $stmt->bind_param('sss', $homeName, $homeCode, $homePassword);
        $stmt->execute();
        $homeId = $stmt->insert_id;
        $stmt->close();

        // Insert user
        $stmt = $db->prepare(
            'INSERT INTO users (firstName, lastName, email, password, homeName, avatar, role, home_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssssssi', $firstName, $lastName, $email, $hash, $homeName, $avatar, $role, $homeId);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        // Update home owner_id
        $stmt = $db->prepare('UPDATE homes SET owner_id = ? WHERE id = ?');
        $stmt->bind_param('ii', $userId, $homeId);
        $stmt->execute();
        $stmt->close();

        // Fetch user
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $_SESSION['user']       = $user;
        $_SESSION['login_time'] = time();

        return [
            'ok'        => true,
            'user'      => $user,
            'home_code' => $homeCode,
            'home_name' => $homeName,
        ];
    } else {
        // Tenant or Guest — join existing home
        $homeCode     = strtoupper(trim($data['homeCode'] ?? ''));
        $homePassword = trim($data['homePassword'] ?? '');

        if (!$homeCode || !$homePassword)
            return ['ok' => false, 'msg' => 'Home code and password are required'];

        // Find home
        $stmt = $db->prepare('SELECT * FROM homes WHERE home_code = ?');
        $stmt->bind_param('s', $homeCode);
        $stmt->execute();
        $home = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$home)
            return ['ok' => false, 'msg' => 'Invalid home code'];

        if (!password_verify($homePassword, $home['home_password']))
            return ['ok' => false, 'msg' => 'Incorrect home password'];

        // Insert user
        $homeId = $home['id'];
        $stmt   = $db->prepare(
            'INSERT INTO users (firstName, lastName, email, password, homeName, avatar, role, home_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssssssi', $firstName, $lastName, $email, $hash, $home['name'], $avatar, $role, $homeId);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        // Fetch user
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $_SESSION['user']       = $user;
        $_SESSION['login_time'] = time();

        return ['ok' => true, 'user' => $user];
    }
}
function logoutUser()
{
    session_destroy();
    return ['ok' => true];
}

function apiResponse($data, $code = 200)
{
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($data);
    exit;
}
