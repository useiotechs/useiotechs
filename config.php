<?php
/**
 * IOTECHS — SMTP, Database & Auth Configuration
 * ─────────────────────────────────────────────────────────────
 * IMPORTANT: Contains secret credentials. NEVER expose publicly.
 * .htaccess already blocks direct access to this file.
 * ─────────────────────────────────────────────────────────────
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Location: ../index.html');
    exit;
}

// ═══════════════════════════════════════════════════════════
// SMTP EMAIL SETTINGS
// ═══════════════════════════════════════════════════════════
define('SMTP_HOST',      'smtp.titan.email');
define('SMTP_PORT',      465);
define('SMTP_ENCRYPTION','ssl');
define('SMTP_USERNAME',  'noreply@iotechs.xyz');
define('SMTP_PASSWORD',  'Indiekencot666?');
define('SMTP_FROM_NAME', 'IOTECHS');

// ═══════════════════════════════════════════════════════════
// DATABASE SETTINGS (MySQL via Hostinger)
// ═══════════════════════════════════════════════════════════
define('DB_HOST',     'localhost');
define('DB_NAME',     'u463942577_iotechs');
define('DB_USER',     'u463942577_iotechs');
define('DB_PASS',     'Indiekencot666?');
define('DB_CHARSET',  'utf8mb4');

// ═══════════════════════════════════════════════════════════
// OTP SETTINGS
// ═══════════════════════════════════════════════════════════
define('OTP_LENGTH',         6);
define('OTP_EXPIRY_SECONDS', 600);
define('OTP_MAX_ATTEMPTS',   5);
define('OTP_RATE_LIMIT',     3);
define('OTP_RATE_WINDOW',    900);

// ═══════════════════════════════════════════════════════════
// SESSION SETTINGS
// ═══════════════════════════════════════════════════════════
define('SESSION_LIFETIME', 7 * 24 * 3600);

// ═══════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function is_valid_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    $blocked = ['/^test@/i', '/^fake@/i', '/@example\./i', '/@test\./i', '/@localhost/i'];
    foreach ($blocked as $p) {
        if (preg_match($p, $email)) return false;
    }
    return true;
}

function get_client_ip() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return 'unknown';
}

function get_user_agent() {
    return substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 500);
}

// ═══════════════════════════════════════════════════════════
// DATABASE CONNECTION (PDO Singleton)
// ═══════════════════════════════════════════════════════════

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("DB connection failed: " . $e->getMessage());
            json_response([
                'success' => false,
                'error'   => 'Database connection failed. Silakan coba lagi.'
            ], 500);
        }
    }
    return $pdo;
}
