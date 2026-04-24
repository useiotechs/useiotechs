<?php
require_once __DIR__ . '/config.php';
start_secure_session();

$auth = $_SESSION['auth'] ?? null;

if (!$auth || empty($auth['authenticated']) || empty($auth['user_id'])) {
    json_response(['authenticated' => false, 'email' => null]);
}

$pdo = db();
$stmt = $pdo->prepare("SELECT id, email, tier, status, login_count, created_at, last_login_at FROM users WHERE id = ? AND status = 'active' LIMIT 1");
$stmt->execute([$auth['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    json_response(['authenticated' => false, 'email' => null]);
}

$pdo->prepare("UPDATE login_sessions SET last_activity = NOW() WHERE session_id = ? AND logged_out_at IS NULL")->execute([session_id()]);

json_response([
    'authenticated' => true,
    'email'         => $user['email'],
    'user_id'       => (int)$user['id'],
    'tier'          => $user['tier'],
    'login_count'   => (int)$user['login_count'],
    'created_at'    => $user['created_at'],
    'last_login_at' => $user['last_login_at'],
]);
