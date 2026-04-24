<?php
require_once __DIR__ . '/config.php';
start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$code  = trim($input['code'] ?? '');

if (!preg_match('/^\d{' . OTP_LENGTH . '}$/', $code)) {
    json_response(['success' => false, 'error' => 'Format kode tidak valid.'], 400);
}

if (empty($_SESSION['otp_pending'])) {
    json_response(['success' => false, 'error' => 'Tidak ada kode aktif. Minta kode baru.'], 400);
}

$pending = $_SESSION['otp_pending'];
$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM otp_logs WHERE id = ? LIMIT 1");
$stmt->execute([$pending['log_id']]);
$log = $stmt->fetch();

if (!$log || $log['status'] !== 'pending') {
    unset($_SESSION['otp_pending']);
    json_response(['success' => false, 'error' => 'Kode tidak valid. Minta kode baru.'], 400);
}

if (strtotime($log['expires_at']) < time()) {
    $pdo->prepare("UPDATE otp_logs SET status = 'expired' WHERE id = ?")->execute([$log['id']]);
    unset($_SESSION['otp_pending']);
    json_response(['success' => false, 'error' => 'Kode sudah kedaluwarsa. Minta kode baru.'], 400);
}

$attempts = $log['attempts'] + 1;
$pdo->prepare("UPDATE otp_logs SET attempts = ? WHERE id = ?")->execute([$attempts, $log['id']]);

if ($attempts > OTP_MAX_ATTEMPTS) {
    $pdo->prepare("UPDATE otp_logs SET status = 'failed' WHERE id = ?")->execute([$log['id']]);
    unset($_SESSION['otp_pending']);
    json_response(['success' => false, 'error' => 'Terlalu banyak percobaan salah. Minta kode baru.'], 429);
}

if (!password_verify($code, $log['code_hash'])) {
    $rem = OTP_MAX_ATTEMPTS - $attempts;
    json_response(['success' => false, 'error' => "Kode salah. Sisa percobaan: $rem."], 400);
}

// SUCCESS
$pdo->prepare("UPDATE otp_logs SET status = 'verified', verified_at = NOW() WHERE id = ?")->execute([$log['id']]);

$email = $pending['email'];
$mode  = $pending['mode'];
$ip    = get_client_ip();
$ua    = get_user_agent();

// Upsert user
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    $pdo->prepare("UPDATE users SET email_verified = 1, last_login_ip = ?, user_agent = ?, login_count = login_count + 1, last_login_at = NOW() WHERE id = ?")->execute([$ip, $ua, $user['id']]);
    $user_id = $user['id'];
    $is_new = false;
} else {
    $pdo->prepare("INSERT INTO users (email, email_verified, signup_mode, signup_ip, last_login_ip, user_agent, login_count, last_login_at) VALUES (?, 1, ?, ?, ?, ?, 1, NOW())")->execute([$email, $mode, $ip, $ip, $ua]);
    $user_id = $pdo->lastInsertId();
    $is_new = true;
}

session_regenerate_id(true);
$_SESSION['auth'] = ['authenticated' => true, 'user_id' => $user_id, 'email' => $email, 'signed_in_at' => time(), 'mode' => $mode];

$pdo->prepare("INSERT INTO login_sessions (user_id, session_id, ip_address, user_agent) VALUES (?, ?, ?, ?)")->execute([$user_id, session_id(), $ip, $ua]);

unset($_SESSION['otp_pending']);

json_response(['success' => true, 'message' => $is_new ? 'Akun berhasil dibuat.' : 'Login berhasil.', 'redirect' => 'launch-app.html', 'user' => ['email' => $email, 'is_new' => $is_new]]);
