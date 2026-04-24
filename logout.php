<?php
require_once __DIR__ . '/config.php';
start_secure_session();

if (!empty($_SESSION['auth']['user_id'])) {
    try {
        db()->prepare("UPDATE login_sessions SET logged_out_at = NOW() WHERE session_id = ? AND logged_out_at IS NULL")->execute([session_id()]);
    } catch (Exception $e) {
        error_log("Logout DB error: " . $e->getMessage());
    }
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
session_destroy();

json_response(['success' => true, 'message' => 'Logged out']);
